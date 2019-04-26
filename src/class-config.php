<?php
/**
 * Config for WPGraphQL ACF
 *
 * @package wp-graphql-acf
 */

namespace WPGraphQL\ACF;

use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Post;
use WPGraphQL\TypeRegistry;
use WPGraphQL\Types;

/**
 * Config class.
 */
class Config {

	/**
	 * Initialize WPGraphQL to ACF
	 */
	public function init() {
		/**
		 * Add ACF Fields to GraphQL Types
		 */
		$this->add_acf_fields_to_post_object_types();
		$this->add_acf_fields_to_term_objects();
		$this->add_acf_fields_to_comments();
		$this->add_acf_fields_to_menu_items();
		$this->add_acf_fields_to_media_items();
	}

	/**
	 * Determines whether a field group should be exposed to the GraphQL Schema. By default, field
	 * groups will not be exposed to GraphQL.
	 *
	 * @param array $field_group Undocumented.
	 *
	 * @return bool
	 */
	protected function should_field_group_show_in_graphql( $field_group ) {

		/**
		 * By default, field groups will not be exposed to GraphQL.
		 */
		$show = false;

		/**
		 * If
		 */
		if ( isset( $field_group['show_in_graphql'] ) && true === (bool) $field_group['show_in_graphql'] ) {
			$show = true;
		}

		/**
		 * Determine conditions where the GraphQL Schema should NOT be shown in GraphQL for
		 * root groups, not nested groups with parent.
		 */
		if ( ! isset( $field_group['parent'] ) ) {
			if (
				( empty( $field_group['active'] ) || true !== $field_group['active'] ) ||
				( empty( $field_group['location'] ) || ! is_array( $field_group['location'] ) )
			) {
				$show = false;
			}
		}

		/**
		 * Whether a field group should show in GraphQL.
		 *
		 * @var boolean $show        Whether the field group should show in the GraphQL Schema
		 * @var array   $field_group The ACF Field Group
		 * @var Config  $this        The Config for the ACF Plugin
		 */
		return apply_filters( 'wpgraphql_acf_should_field_group_show_in_graphql', $show, $field_group, $this );

	}

	/**
	 * Undocumented function
	 *
	 * @todo: This may be a good utility to add to WPGraphQL Core? May even have something already?
	 *
	 * @param string $str      Unknown.
	 * @param array  $no_strip Unknown.
	 *
	 * @return mixed|null|string|string[]
	 */
	public static function camel_case( $str, array $no_strip = [] ) {
		// non-alpha and non-numeric characters become spaces.
		$str = preg_replace( '/[^a-z0-9' . implode( '', $no_strip ) . ']+/i', ' ', $str );
		$str = trim( $str );
		// Lowercase the string
		$str = strtolower( $str );
		// uppercase the first character of each word.
		$str = ucwords( $str );
		// Replace spaces
		$str = str_replace( ' ', '', $str );
		// Lowecase first letter
		$str = lcfirst( $str );

		return $str;
	}

	/**
	 * Add ACF Fields to Post Object Types.
	 *
	 * This gets the Post Types that are configured to show_in_graphql and iterates
	 * over them to expose ACF Fields to their Type in the GraphQL Schema.
	 */
	protected function add_acf_fields_to_post_object_types() {

		/**
		 * Get a list of post types that have been registered to show in graphql
		 */
		$graphql_post_types = \WPGraphQL::$allowed_post_types;

		/**
		 * If there are no post types exposed to GraphQL, bail
		 */
		if ( empty( $graphql_post_types ) || ! is_array( $graphql_post_types ) ) {
			return;
		}

		/**
		 * Loop over the post types exposed to GraphQL
		 */
		foreach ( $graphql_post_types as $post_type ) {

			/**
			 * Get the field groups associated with the post type
			 */
			$field_groups = acf_get_field_groups(
				[
					'post_type' => $post_type,
				]
			);

			/**
			 * If there are no field groups for this post type, move on to the next one.
			 */
			if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
				continue;
			}

			/**
			 * Get the post_type_object
			 */
			$post_type_object = get_post_type_object( $post_type );

			/**
			 * Loop over the field groups for this post type
			 */
			foreach ( $field_groups as $field_group ) {

				$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );

				$field_group['type'] = 'group';
				$field_group['name'] = $field_name;
				$config              = [
					'name'            => $field_name,
					'description'     => $field_group['description'],
					'acf_field'       => $field_group,
					'acf_field_group' => null,
					'resolve'         => function( $root ) use ( $field_group ) {
						return isset( $root ) ? $root : null;
					}
				];

				$this->register_graphql_field( $post_type_object->graphql_single_name, $field_name, $config );
			}
		}

	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $root Undocumented.
	 * @param [type] $acf_field Undocumented.
	 *
	 * @return mixed
	 */
	protected function get_acf_field_value( $root, $acf_field ) {
		$value = null;
		if ( is_array( $root ) ) {
			if ( isset( $root[ $acf_field['key'] ] ) ) {
				$value = $root[ $acf_field['key'] ];
			}
		} else {
			$field_value = get_field( $acf_field['key'], $root->ID, false );
			$value       = ! empty( $field_value ) ? $field_value : null;
		}

		return $value;

	}

	/**
	 * Get a list of supported fields that WPGraphQL for ACF supports.
	 *
	 * This is helpful for determining whether UI should be output for the field, and whether
	 * the field should be added to the Schema.
	 *
	 * Some fields, such as "Accordion" are not supported currently.
	 *
	 * @return array
	 */
	public static function get_supported_fields() {
		$supported_fields = [
			'text',
			'textarea',
			'number',
			'range',
			'email',
			'url',
			'password',
			'image',
			'file',
			'wysiwyg',
			'oembed',
			'gallery',
			'select',
			'checkbox',
			'radio_button',
			'button_group',
			'true_false',
			'link',
			'post_object',
			'page_link',
			'relationship',
			'taxonomy',
			'user',
			'google_map',
			'date_picker',
			'date_time_picker',
			'time_picker',
			'color_picker',
			'group',
			'repeater',
			'flexible_content'
		];

		/**
		 * filter the supported fields
		 *
		 * @param array $supported_fields
		 */
		return apply_filters( 'wpgraphql_acf_supported_fields', $supported_fields );
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $type_name Undocumented.
	 * @param [type] $field_name Undocumented.
	 * @param [type] $config Undocumented.
	 *
	 * @return mixed
	 */
	protected function register_graphql_field( $type_name, $field_name, $config ) {

		$acf_field = isset( $config['acf_field'] ) ? $config['acf_field'] : null;
		$acf_type  = isset( $acf_field['type'] ) ? $acf_field['type'] : null;

		if ( empty( $acf_type ) ) {
			return false;
		}

		$field_config = [
			'type'    => null,
			'resolve' => isset( $config['resolve'] ) && is_callable( $config['resolve'] ) ? $config['resolve'] : function( $root, $args, $context, $info ) use ( $acf_field ) {
				$value = $this->get_acf_field_value( $root, $acf_field );

				return ! empty( $value ) ? $value : null;
			},
		];

		switch ( $acf_type ) {
			case 'button_group':
			case 'color_picker':
			case 'email':
			case 'textarea':
			case 'text':
			case 'message':
			case 'oembed':
			case 'password':
			case 'url':
			case 'wysiwyg':
				// Even though Selects and Radios in ACF can _technically_ be an integer
				// we're chosing to always cast as a string because with
				// GraphQL we can't return different types
			case 'select':
			case 'radio':
				$field_config['type'] = 'String';
				break;
			case 'range':
				$field_config['type'] = 'Integer';
				break;
			case 'number':
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
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						return isset( $root->ID ) ? get_field( $acf_field['key'], $root->ID, true ) : null;
					},
				];
				break;
			case 'relationship':

				if ( isset( $acf_field['post_type'] ) && is_array( $acf_field['post_type'] ) ) {
					$field_type_name = $type_name . '_' . ucfirst( self::camel_case( $acf_field['name'] ) );
					if ( TypeRegistry::get_type( $field_type_name ) == $field_type_name ) {
						$type = $field_type_name;
					} else {
						$type_names = [];
						foreach ( $acf_field['post_type'] as $post_type ) {
							if ( in_array( $post_type, \WPGraphQL::$allowed_post_types, true ) ) {
								$type_names[ $post_type ] = get_post_type_object( $post_type )->graphql_single_name;
							}
						}

						if ( empty( $type_names ) ) {
							$field_config['type'] = null;
							break;
						}

						register_graphql_union_type( $field_type_name, [
							'typeNames'   => $type_names,
							'resolveType' => function( $value ) use ( $type_names ) {
								return ! empty( $value->post_type ) ? Types::post_object( $value->post_type ) : null;
							}
						] );

						$type = $field_type_name;
					}
				} else {
					$type = 'PostObjectUnion';
				}

				$field_config = [
					'type'    => [ 'list_of' => $type ],
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						$relationship = [];
						$value        = $this->get_acf_field_value( $root, $acf_field );
						if ( ! empty( $value ) && is_array( $value ) ) {
							foreach ( $value as $post_id ) {
								$relationship[] = DataSource::resolve_post_object( (int) $post_id, $context );
							}
						}

						return isset( $value ) ? $relationship : null;
					},
				];
				break;
			case 'page_link':
			case 'post_object':

				if ( isset( $acf_field['post_type'] ) && is_array( $acf_field['post_type'] ) ) {
					$field_type_name = $type_name . '_' . ucfirst( self::camel_case( $acf_field['name'] ) );
					if ( TypeRegistry::get_type( $field_type_name ) == $field_type_name ) {
						$type = $field_type_name;
					} else {
						$type_names = [];
						foreach ( $acf_field['post_type'] as $post_type ) {
							if ( in_array( $post_type, \WPGraphQL::$allowed_post_types, true ) ) {
								$type_names[ $post_type ] = get_post_type_object( $post_type )->graphql_single_name;
							}
						}

						if ( empty( $type_names ) ) {
							$field_config['type'] = null;
							break;
						}

						register_graphql_union_type( $field_type_name, [
							'typeNames'   => $type_names,
							'resolveType' => function( $value ) use ( $type_names ) {
								return ! empty( $value->post_type ) ? Types::post_object( $value->post_type ) : null;
							}
						] );

						$type = $field_type_name;
					}
				} else {
					$type = 'PostObjectUnion';
				}

				$field_config = [
					'type'    => $type,
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );
						if ( $value instanceof \WP_Post ) {
							return new Post( $value );
						}

						return absint( $value ) ? DataSource::resolve_post_object( (int) $value, $context ) : null;

					},
				];
				break;
			case 'link':

				$field_type_name = 'ACF_Link';
				if ( TypeRegistry::get_type( $field_type_name ) == $field_type_name ) {
					$field_config['type'] = $field_type_name;
					break;
				}

				register_graphql_object_type(
					$field_type_name,
					[
						'description' => __( 'ACF Link field', 'wp-graphql-acf' ),
						'fields'      => [
							'url'    => [
								'type'        => 'String',
								'description' => __( 'The url of the link', 'wp-graphql-acf' ),
							],
							'title'  => [
								'type'        => 'String',
								'description' => __( 'The title of the link', 'wp-graphql-acf' ),
							],
							'target' => [
								'type'        => 'String',
								'description' => __( 'The target of the link (_blank, etc)', 'wp-graphql-acf' ),
							],
						],
					]
				);
				$field_config['type'] = $field_type_name;
				break;
			case 'image':
			case 'file':
				$field_config = [
					'type'    => 'MediaItem',
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );

						return DataSource::resolve_post_object( (int) $value, $context );
					},
				];
				break;
			case 'checkbox':
				$field_config = [
					'type'    => [ 'list_of' => 'String' ],
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );

						return is_array( $value ) ? $value : null;
					},
				];
				break;
			case 'gallery':
				$field_config = [
					'type'    => [ 'list_of' => 'MediaItem' ],
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value   = $this->get_acf_field_value( $root, $acf_field );
						$gallery = [];
						if ( ! empty( $value ) && is_array( $value ) ) {
							foreach ( $value as $image ) {
								$gallery[] = DataSource::resolve_post_object( (int) $image, $context );
							}
						}

						return isset( $value ) ? $gallery : null;
					},
				];
				break;
			case 'user':
				$field_config = [
					'type'    => 'User',
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );

						return DataSource::resolve_user( (int) $value, $context );
					},
				];
				break;
			case 'taxonomy':
				$field_config = [
					'type'    => [ 'list_of' => 'TermObjectUnion' ],
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );
						$terms = [];
						if ( ! empty( $value ) && is_array( $value ) ) {
							foreach ( $value as $term ) {
								$terms[] = DataSource::resolve_term_object( (int) $term, $context );
							}
						}

						return $terms;
					},
				];
				break;

			// Accordions are not represented in the GraphQL Schema.
			case 'accordion':
				$field_config = null;
				break;
			case 'group':
				$field_type_name = $type_name . '_' . ucfirst( self::camel_case( $acf_field['name'] ) );
				if ( TypeRegistry::get_type( $field_type_name ) ) {
					$field_config['type'] = $field_type_name;
					break;
				}

				register_graphql_object_type(
					$field_type_name,
					[
						'description' => __( 'Field Group', 'wp-graphql-acf' ),
						'fields'      => [
							'fieldGroupName' => [
								'type'    => 'String',
								'resolve' => function( $source ) use ( $acf_field ) {
									return ! empty( $acf_field['name'] ) ? $acf_field['name'] : null;
								},
							],
						],
					]
				);


				$this->add_field_group_fields( $acf_field, $field_type_name );

				$field_config['type'] = $field_type_name;
				break;

			case 'google_map':
				$field_type_name = 'ACF_GoogleMap';
				if ( TypeRegistry::get_type( $field_type_name ) == $field_type_name ) {
					$field_config['type'] = $field_type_name;
					break;
				}

				register_graphql_object_type(
					$field_type_name,
					[
						'description' => __( 'Google Map field', 'wp-graphql-acf' ),
						'fields'      => [
							'streetAddress' => [
								'type'        => 'String',
								'description' => __( 'The street address associated with the map', 'wp-graphql-acf' ),
								'resolve'     => function( $root ) {
									return isset( $root['address'] ) ? $root['address'] : null;
								},
							],
							'latitude'      => [
								'type'        => 'Float',
								'description' => __( 'The latitude associated with the map', 'wp-graphql-acf' ),
								'resolve'     => function( $root ) {
									return isset( $root['lat'] ) ? $root['lat'] : null;
								},
							],
							'longitude'     => [
								'type'        => 'Float',
								'description' => __( 'The longitude associated with the map', 'wp-graphql-acf' ),
								'resolve'     => function( $root ) {
									return isset( $root['lng'] ) ? $root['lng'] : null;
								},
							],
						],
					]
				);
				$field_config['type'] = $field_type_name;
				break;
			case 'repeater':
				$field_type_name = $type_name . '_' . self::camel_case( $acf_field['name'] );

				if ( TypeRegistry::get_type( $field_type_name ) ) {
					$field_config['type'] = $field_type_name;
					break;
				}

				register_graphql_object_type(
					$field_type_name,
					[
						'description' => __( 'Field Group', 'wp-graphql-acf' ),
						'fields'      => [
							'fieldGroupName' => [
								'type'    => 'String',
								'resolve' => function( $source ) use ( $acf_field ) {
									return ! empty( $acf_field['name'] ) ? $acf_field['name'] : null;
								},
							],
						],
						'resolve'     => function( $source ) use ( $acf_field ) {
							return $this->get_acf_field_value( $source, $acf_field );
						},
					]
				);

				$this->add_field_group_fields( $acf_field, $field_type_name );

				$field_config['type'] = [ 'list_of' => $field_type_name ];
				break;

			/**
			 * Flexible content fields should return a Union of the Layouts that can be configured.
			 *
			 *
			 * Example Query of a flex field with the name "flex_field" and 2 groups
			 *
			 * {
			 *   post {
			 *      flexField {
			 *         ...on GroupOne {
			 *           textField
			 *           textAreaField
			 *         }
			 *         ...on GroupTwo {
			 *           imageField {
			 *             id
			 *             title
			 *           }
			 *         }
			 *      }
			 *   }
			 * }
			 *
			 */
			case 'flexible_content':

				$field_config    = null;
				$field_type_name = $type_name . '_' . ucfirst( self::camel_case( $acf_field['name'] ) );
				if ( TypeRegistry::get_type( $field_type_name ) ) {
					$field_config['type'] = $field_type_name;
					break;
				}

				if ( ! empty( $acf_field['layouts'] ) && is_array( $acf_field['layouts'] ) ) {

					$union_types = [];
					foreach ( $acf_field['layouts'] as $layout ) {

						$flex_field_layout_name = ! empty( $layout['name'] ) ? ucfirst( self::camel_case( $layout['name'] ) ) : null;
						$flex_field_layout_name = ! empty( $flex_field_layout_name ) ? $field_type_name . '_' . $flex_field_layout_name : null;
						$layout_type            = TypeRegistry::get_type( $flex_field_layout_name );

						if ( $layout_type ) {
							$union_types[ $layout['name'] ] = $layout_type;
						} else {
							register_graphql_object_type( $flex_field_layout_name, [
								'description' => __( 'Group within the flex field', 'wp-graphql-acf' ),
								'fields'      => [
									'fieldGroupName' => [
										'type'    => 'String',
										'resolve' => function( $source ) use ( $flex_field_layout_name ) {
											return ! empty( $flex_field_layout_name ) ? $flex_field_layout_name : null;
										},
									],
								],
							] );
							$layout_type                    = TypeRegistry::get_type( $flex_field_layout_name );
							$union_types[ $layout['name'] ] = $layout_type;

							$layout['parent']          = $acf_field;
							$layout['show_in_graphql'] = isset( $acf_field['show_in_graphql'] ) ? (bool) $acf_field['show_in_graphql'] : true;
							$this->add_field_group_fields( $layout, $flex_field_layout_name );
						}
					}

					register_graphql_union_type( $field_type_name, [
						'types'       => $union_types,
						'resolveType' => function( $value ) use ( $union_types ) {
							return isset( $union_types[ $value['acf_fc_layout'] ] ) ? $union_types[ $value['acf_fc_layout'] ] : null;
						}
					] );

					$field_config['type']    = [ 'list_of' => $field_type_name ];
					$field_config['resolve'] = function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );

						return $value;
					};
				}
				break;
			default:
				break;
		}

		if ( empty( $field_config ) || empty( $field_config['type'] ) ) {
			return null;
		}

		$config = array_merge( $config, $field_config );

		return register_graphql_field( $type_name, $field_name, $config );
	}

	/**
	 * Given a field group array, this adds the fields to the specified Type in the Schema
	 *
	 * @param array  $field_group The group to add to the Schema.
	 * @param string $type_name   The Type name in the GraphQL Schema to add fields to.
	 */
	protected function add_field_group_fields( $field_group, $type_name ) {

		/**
		 * If the field group has the show_in_graphql setting configured, respect it's setting
		 * otherwise default to true (for nested fields)
		 */
		$field_group['show_in_graphql'] = isset( $field_group['show_in_graphql'] ) ? (boolean) $field_group['show_in_graphql'] : true;

		/**
		 * Determine if the field group should be exposed
		 * to graphql
		 */
		if ( ! $this->should_field_group_show_in_graphql( $field_group ) ) {
			return;
		}

		/**
		 * Get the fields in the group.
		 */
		$acf_fields = ! empty( $field_group['sub_fields'] ) ? $field_group['sub_fields'] : acf_get_fields( $field_group );


		/**
		 * If there are no fields, bail
		 */
		if ( empty( $acf_fields ) || ! is_array( $acf_fields ) ) {
			return;
		}

		/**
		 * Loop over the fields and register them to the Schema
		 */
		foreach ( $acf_fields as $acf_field ) {

			/**
			 * Setup data for register_graphql_field
			 */
			$name            = ! empty( $acf_field['name'] ) ? self::camel_case( $acf_field['name'] ) : null;
			$show_in_graphql = isset( $acf_field['show_in_graphql'] ) && true !== (bool) $acf_field['show_in_graphql'] ? false : true;
			$description     = isset( $acf_field['instructions'] ) ? $acf_field['instructions'] : __( 'ACF Field added to the Schema by WPGraphQL ACF' );

			/**
			 * If the field is missing a name or a type,
			 * we can't add it to the Schema.
			 */
			if (
				empty( $name ) ||
				true !== $show_in_graphql
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

			$this->register_graphql_field( $type_name, $name, $config );

		}

	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function add_acf_fields_to_term_objects() {
		// @todo: Coming soon
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function add_acf_fields_to_comments() {
		// @todo: Coming soon
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function add_acf_fields_to_menu_items() {
		// @todo: Coming soon
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function add_acf_fields_to_media_items() {
		// @todo: Coming soon
	}

}
