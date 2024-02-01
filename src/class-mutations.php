<?php

namespace WPGraphQL\ACF;

use WP_Post;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;

class Mutations
{

	const POST_OBJECT_TYPE = 'Post';
	const TERM_OBJECT_TYPE = 'Term';

	/**
	 * @var TypeRegistry
	 */
	private $type_registry;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * Stores the location rules for back compat
	 * @var array
	 */
	private $location_rules = [];

	/**
	 * Stores the ACF field groups
	 * @var array
	 */
	private $field_groups = [];

	/**
	 * Stores the registered ACF fields
	 * @var array
	 */
	private $registered_fields = [];

    public function init( TypeRegistry $type_registry, Config $config ): void
    {
	    $this->type_registry = $type_registry;
	    $this->config = $config;

	    /**
	     * Get all the field groups
	     */
	    $this->field_groups = acf_get_field_groups();

	    /**
	     * If there are no acf field groups, bail
	     */
	    if ( empty( $this->field_groups ) || ! is_array( $this->field_groups ) ) {
		    return;
	    }

	    /**
	     * Gets the location rules for those fields that do not have "graphql_type".
	     *
	     * This allows for ACF Field Groups that were registered before the "graphql_types" ( backward compatibility )
	     * or registered from code can still work with the old GraphQL Schema rules that mapped
	     * from the ACF Location rules.
	     */
	    $this->location_rules = Config::get_location_rules();

	    /**
	     * Add ACF Fields to GraphQL Mutations
	     */
	    $this->add_acf_fields_to_graphql_types();

		// Use same hook that acf normally used to save its data when post create/update from UI and that to be
		// more compatible with other plugins that waiting the acf to save to do something depending on it like
		// WPML sync feature that sync acf data from master post to its translations.
		add_filter( 'graphql_post_object_insert_post_args', function ( array $insert_post_args, array $input ) {
			self::add_action_once( 'save_post', function ( int $post_id, WP_Post $post ) use ( $input ) {
				// Ignore revision because it's not the post that updated and we don't want to run saving ACF data into it.
				if ( 'revision' === $post->post_type ) {
					// Return false to prevent removing action because it's not the action that we need.
					return false;
				}

				$this->save_registered_fields_data( $post_id, self::POST_OBJECT_TYPE, $input, $this->registered_fields );
			}, 10, 2 );

			return $insert_post_args;
	    }, 10, 2 );

		add_filter( 'graphql_term_object_insert_term_args', function ( array $insert_args, array $input ) {
			self::add_action_once( 'create_term', function ( int $term_id ) use ( $input ) {
			    $this->save_registered_fields_data( $term_id, self::TERM_OBJECT_TYPE, $input, $this->registered_fields );
		    } );
			self::add_action_once( 'edit_term', function ( int $term_id ) use ( $input ) {
				$this->save_registered_fields_data( $term_id, self::TERM_OBJECT_TYPE, $input, $this->registered_fields );
			} );

			return $insert_args;
		}, 10, 2 );
    }

	private function save_registered_fields_data( int $object_id, string $object_type, array $fields_data, array $registered_fields ): void
	{
		foreach ( $fields_data as $key => $value ) {
			if ( ! empty( $registered_fields[$key] ) ) {
				if ( ! empty( $registered_fields[$key]['sub_fields_config'] ) ) {
					$this->save_registered_fields_data( $object_id, $object_type, $value, $registered_fields[$key]['sub_fields_config'] );
				}
				else if (
					! empty( $registered_fields[$key]['mutate'] )
					&& is_callable( $registered_fields[$key]['mutate'] )
				) {
					call_user_func_array( $registered_fields[$key]['mutate'], [ $object_id, $object_type, $value, $registered_fields[$key] ] );
				}
				else {
					$this->update_acf_field_value( $object_id, $object_type, $value, $registered_fields[$key] );
				}
			}
		}
	}

	/**
	 * Given a field group array, this adds the fields to the specified Type in the Schema
	 *
	 * @param array  $field_group The group to add to the Schema.
	 * @param bool   $layout      Whether or not these fields are part of a Flex Content layout.
	 *
	 * @return array|null
	 */
	private function add_field_group_fields( array $field_group, string $parent_type_name, bool $layout = false ) {

		/**
		 * If the field group has the show_in_graphql setting configured, respect it's setting
		 * otherwise default to true (for nested fields)
		 */
		$field_group['show_in_graphql'] = isset( $field_group['show_in_graphql'] ) ? (boolean) $field_group['show_in_graphql'] : true;

		/**
		 * Determine if the field group should be exposed
		 * to graphql
		 */
		if ( ! $this->config->should_field_group_show_in_graphql( $field_group ) ) {
			return null;
		}

		/**
		 * Get the fields in the group.
		 */
		$acf_fields = ! empty( $field_group['sub_fields'] ) || $layout ? $field_group['sub_fields'] : acf_get_fields( $field_group );

		/**
		 * If there are no fields, bail
		 */
		if ( empty( $acf_fields ) || ! is_array( $acf_fields ) ) {
			return null;
		}

		/**
		 * Stores field keys to prevent duplicate field registration for cloned fields
		 */
		$processed_keys = [];

		$registered_fields = [];
		/**
		 * Loop over the fields and register them to the Schema
		 */
		foreach ( $acf_fields as $acf_field ) {
			if ( in_array( $acf_field['key'], $processed_keys, true ) ) {
				continue;
			} else {
				$processed_keys[] = $acf_field['key'];
			}

			/**
			 * Setup data for register_graphql_field
			 */
			$explicit_name   = ! empty( $acf_field['graphql_field_name'] ) ? $acf_field['graphql_field_name'] : null;
			$name            = empty( $explicit_name ) && ! empty( $acf_field['name'] ) ? Config::camel_case( $acf_field['name'] ) : $explicit_name;
			$show_in_graphql = isset( $acf_field['show_in_graphql'] ) ? (bool) $acf_field['show_in_graphql'] : true;
			$description     = isset( $acf_field['instructions'] ) ? $acf_field['instructions'] : __( 'ACF Field added to the Schema by WPGraphQL ACF' );

			/**
			 * If the field is missing a name or a type,
			 * we can't add it to the Schema.
			 */
			if (
				empty( $name ) ||
				true != $show_in_graphql
			) {

				/**
				 * Uncomment line below to determine what fields are not going to be output
				 * in the Schema.
				 */
				continue;
			}

			$config = [
				'name'            => $name,
				'description'     => $description,
				'acf_field'       => $acf_field,
				'acf_field_group' => $field_group,
			];

			$field_config = $this->register_graphql_field( $config, $parent_type_name );
			if ( ! empty( $field_config ) ) {
				$registered_fields[$name] = $field_config;
			}

		}

		return $registered_fields;
	}

	private function get_field_key( $acf_field ) {
		/**
		 * Check if cloned field and retrieve the key accordingly.
		 */
		if ( ! empty( $acf_field['_clone'] ) ) {
			$key = $acf_field['__key'];
		} else {
			$key = $acf_field['key'];
		}

		return $key;
	}

	private function maybe_filter_value( $field_config, $value ) {
		$original_value = $value;
		$filtered_value = $value;

		if (
			! empty( $field_config['filter_value'] )
			&& is_callable( $field_config['filter_value'] )
		) {
			$filtered_value = call_user_func_array( $field_config['filter_value'], [ $filtered_value ] );
		}

		$acf_type 	= $field_config['acf_field']['type'];
		$field_name = $field_config['name'];

		return apply_filters( 'wpgraphql_acf_filter_mutation_field_value', $filtered_value, $acf_type, $original_value, $field_name, $field_config );
	}

	private function update_acf_field_value( int $object_id, string $object_type, $value, array $field_config, bool $use_add_row = false ) {

		switch ( $object_type ) {
			case self::TERM_OBJECT_TYPE:
				$object_id = 'term_' . $object_id;
				break;
			case self::POST_OBJECT_TYPE:
				// do nothing in this case
				break;
//			case $root instanceof MenuItem:
//				$id = absint( $root->menuItemId );
//				break;
//			case $root instanceof Menu:
//				$id = 'term_' . $root->menuId;
//				break;
//			case $root instanceof User:
//				$id = 'user_' . absint( $root->userId );
//				break;
//			case $root instanceof Comment:
//				$id = 'comment_' . absint( $root->databaseId );
//				break;
//			case is_array( $root ) && ! empty( $root['type'] ) && 'options_page' === $root['type']:
//				$id = $root['post_id'];
//				break;
			default:
				$object_id = null;
				break;
		}

		if ( empty( $object_id ) ) {
			return null;
		}

		$acf_field = $field_config['acf_field'];
		$key = $this->get_field_key( $acf_field );

		$value = $this->maybe_filter_value( $field_config, $value );

		if ( $value !== null ) {
			if ( $use_add_row ) {
				add_row( $key, $value, $object_id );
			}
			else {
				update_field( $key, $value, $object_id );
			}
		}
	}

	private function prepare_input_type_name( string $acf_field_name, string $parent_type_name ): string {
		$prefix = '';
		if ( ! empty( $parent_type_name ) ) {
			$prefix = "{$parent_type_name}_";
		}

		return $prefix . ucfirst( Config::camel_case( $acf_field_name ) ) . 'Input';
	}

	/**
	 * Undocumented function
	 *
	 * @param array $config The GraphQL configuration of the field.
	 *
	 * @return array|null
	 */
	private function register_graphql_field( array $config, string $parent_type_name = '' ) {
		$acf_field = isset( $config['acf_field'] ) ? $config['acf_field'] : null;
		$acf_type  = isset( $acf_field['type'] ) ? $acf_field['type'] : null;

		if ( empty( $acf_type ) ) {
			return null;
		}

		$field_config = [
			'type'    => null,
		];

		switch ( $acf_type ) {
			case 'button_group':
			case 'color_picker':
			case 'email':
			case 'text':
			case 'message':
			case 'oembed':
			case 'password':
			case 'wysiwyg':
			case 'url':
			case 'textarea':
				$field_config['type'] = 'String';
				break;
			case 'radio':
				$field_type           = $this->config->register_choices_of_acf_fields_as_enum_type( $acf_field );
				$field_config['type'] = $field_type;
				break;
			case 'select':

				/**
				 * If the select field is configured to not allow multiple values
				 * the field will accept a string, but if it is configured to allow
				 * multiple values it will accept a list of strings
				 */
				$field_type = $this->config->register_choices_of_acf_fields_as_enum_type( $acf_field );
				if ( empty( $acf_field['multiple'] ) ) {
					$field_config['type'] = $field_type;
				} else {
					$field_config['type'] = [ 'list_of' => $field_type ];
				}
				break;
			case 'number':
			case 'range':
				$field_config['type'] = 'Float';
				break;
			case 'true_false':
				$field_config['type'] = 'Boolean';
				break;
			case 'date_picker':
			case 'time_picker':
			case 'date_time_picker':
				$field_config = [
					'type'    => 'String',
					'filter_value' => function( $value ) use ( $acf_type ) {
						$timestamp = strtotime( $value );
						if ( $timestamp !== false ) {
							switch ( $acf_type ) {
								case 'time_picker':
									$value = gmdate( 'H:i:s', $timestamp );
									break;
								case 'date_picker':
									$value = gmdate( 'Y-m-d', $timestamp );
									break;
								default:// 'date_time_picker'
									$value = gmdate( 'Y-m-d H:i:s', $timestamp );
									break;
							}

							return $value;
						}
						return null;
					}
				];
				break;
			case 'relationship':
				$field_config['type'] = [ 'list_of' => 'ID' ];
				break;
			case 'image':
			case 'file':
				$field_config = [
					'type'    => 'String',
					'filter_value' => function( $value ) {
						$attachment_url = $value;
						if ( ! empty( $attachment_url ) ) {
							$attach_id = attachment_url_to_postid( $attachment_url );

							if ( ! empty( $attach_id ) ) {
								return $attach_id;
							}
						}
						return null;
					},
				];
				break;
			case 'checkbox':
				$field_config['type'] = [ 'list_of' => 'String' ];
				break;
			case 'taxonomy':
				$is_multiple = isset( $acf_field['field_type'] ) && in_array( $acf_field['field_type'], [ 'checkbox', 'multi_select' ] );

				$field_config['type'] = $is_multiple ? [ 'list_of' => 'ID' ] : 'ID';
				break;
			// Accordions are not represented in the GraphQL Schema.
			case 'accordion':
				$field_config = null;
				break;
			case 'group':

				$field_type_name = $this->prepare_input_type_name( $acf_field['name'], $parent_type_name );

				$sub_fields_config = $this->add_field_group_fields( $acf_field, $field_type_name );

				if ( ! empty( $sub_fields_config ) ) {
					$this->type_registry->register_input_type(
						$field_type_name,
						[
							'description' => __( 'Field Group', 'wp-graphql-acf' ),
							'fields'      => $sub_fields_config,
						]
					);

					$field_config = [
						'type' => $field_type_name,
						'sub_fields_config' => $sub_fields_config,
					];
				}
				break;
			case 'repeater':

				$field_type_name = $this->prepare_input_type_name( $acf_field['name'], $parent_type_name );

				$sub_fields_config = $this->add_field_group_fields( $acf_field, $field_type_name );

				if ( ! empty( $sub_fields_config ) ) {
					$this->type_registry->register_input_type(
						$field_type_name,
						[
							'description' => __( 'Field Group', 'wp-graphql-acf' ),
							'fields'      => $sub_fields_config,
						]
					);

					$field_config = [
						'type' => [ 'list_of' => $field_type_name ],
						'repeater_sub_fields_config' => $sub_fields_config,
						'mutate' => function( $object_id, $object_type, $rows, $field_config ) use ( $sub_fields_config ) {
							if ( ! empty( $rows ) && is_array( $rows ) ) {
								foreach ( $rows as $row ) {
									$row_value = [];
									foreach ( $row as $sub_field_key => $sub_field_value ) {
										$sub_field_config = $sub_fields_config[$sub_field_key];
										if ( ! empty( $sub_field_config ) ) {
											$sub_field_value = $this->maybe_filter_value( $sub_field_config, $sub_field_value );

											if ( $sub_field_value !== null ) {
												$acf_key = $this->get_field_key( $sub_field_config['acf_field'] );
												$row_value[$acf_key] = $sub_field_value;
											}
										}
									}

									if ( ! empty( $row_value ) ) {
										$this->update_acf_field_value( $object_id, $object_type, $row_value, $field_config, true );
									}
								}
							}
						},
					];
				}
				break;
//			case 'page_link':
//			case 'post_object':
//			case 'link':
//			case 'gallery':
//			case 'user':
//			case 'google_map':
//			case 'flexible_content':
//				break;
			default:
				break;
		}

		if ( empty( $field_config ) || empty( $field_config['type'] ) ) {
			return null;
		}

		return array_merge( $config, $field_config );
	}

	/**
	 * Adds acf field groups to GraphQL Mutations.
	 */
	private function add_acf_fields_to_graphql_types() {
		/**
		 * Loop over all the field groups
		 */
		foreach ( $this->field_groups as $field_group ) {

			$field_group_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : $field_group['title'];
			$field_group_name = Utils::format_field_name( $field_group_name );

			$manually_set_graphql_types = isset( $field_group['map_graphql_types_from_location_rules'] ) ? (bool) $field_group['map_graphql_types_from_location_rules'] : false;

			if ( false === $manually_set_graphql_types ) {
				if ( ! isset( $field_group['graphql_types'] ) || empty( $field_group['graphql_types'] ) ) {
					$field_group['graphql_types'] = [];
					if ( isset( $this->location_rules[ $field_group_name ] ) ) {
						$field_group['graphql_types'] = $this->location_rules[ $field_group_name ];
					}
				}
			}

			if ( ! is_array( $field_group['graphql_types'] ) || empty( $field_group['graphql_types'] ) ) {
				continue;
			}

			/**
			 * Determine if the field group should be exposed
			 * to graphql
			 */
			if ( ! $this->config->should_field_group_show_in_graphql( $field_group ) ) {
				continue;
			}

			$graphql_types = array_unique( $field_group['graphql_types'] );
			$graphql_types = array_filter( $graphql_types );

			/**
			 * Prepare default info
			 */
			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );
			$field_group['type'] = 'group';
			$field_group['name'] = $field_name;
			$config              = [
				'name'            => $field_name,
				'acf_field'       => $field_group,
				'acf_field_group' => null,
			];

			$qualifier =  sprintf( __( 'Added to the GraphQL Schema because the ACF Field Group "%1$s" was set to Show in GraphQL.', 'wp-graphql-acf' ), $field_group['title'] );
			$config['description'] = $field_group['description'] ? $field_group['description'] . ' | ' . $qualifier : $qualifier;

			$field_config = $this->register_graphql_field( $config );
			if ( ! empty( $field_config ) ) {
				/**
				 * Loop over the GraphQL types for this field group on
				 */
				foreach ( $graphql_types as $graphql_type ) {
					$this->type_registry->register_field( "Create{$graphql_type}Input", $field_name, $field_config );
					$this->type_registry->register_field( "Update{$graphql_type}Input", $field_name, $field_config );
				}

				$this->registered_fields[$field_name] = $field_config;
			}
		}
	}

	/**
	 * Register an action to run exactly one time.
	 *
	 * The arguments match that of add_action(), but this function will also register a second
	 * callback designed to remove the first immediately after it runs.
	 *
	 * @param string   $hook_name       The name of the action to add the callback to.
	 * @param callable $callback        The callback to be run when the action is called.
	 * @param int      $priority        Optional. Used to specify the order in which the functions
	 *                                  associated with a particular action are executed.
	 *                                  Lower numbers correspond with earlier execution,
	 *                                  and functions with the same priority are executed
	 *                                  in the order in which they were added to the action. Default 10.
	 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
	 * @return bool Like add_action(), this function always returns true.
	 */
	public static function add_action_once( string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1 ): bool {
		$singular = function () use ( $hook_name, $callback, $priority, $accepted_args, &$singular ) {
			$should_be_removed = call_user_func_array( $callback, func_get_args() );
			if ( false !== $should_be_removed ) {
				remove_action( $hook_name, $singular, $priority );
			}
		};

		return add_action( $hook_name, $singular, $priority, $accepted_args );
	}
}
