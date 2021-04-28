<?php
/**
 * Config for WPGraphQL ACF
 *
 * @package wp-graphql-acf
 */

namespace WPGraphQL\ACF;

use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Comment;
use WPGraphQL\Model\Menu;
use WPGraphQL\Model\MenuItem;
use WPGraphQL\Model\Post;
use WPGraphQL\Model\Term;
use WPGraphQL\Model\User;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;

/**
 * Config class.
 */
class Config {

	/**
	 * @var TypeRegistry
	 */
	protected $type_registry;

	/**
	 * Stores the location rules for back compat
	 * @var array
	 */
	protected $location_rules = [];

	/**
	 * @var array <string> List of field names registered to the Schema
	 */
	protected $registered_field_names;

	/**
	 * @var array <string> List of options page slugs registered to the Schema
	 */
	protected $registered_options_pages = [];

	/**
	 * Initialize WPGraphQL to ACF
	 *
	 * @param TypeRegistry $type_registry Instance of the WPGraphQL TypeRegistry
	 *
	 * @throws Exception
	 */
	public function init( TypeRegistry $type_registry ) {

		/**
		 * Set the TypeRegistry
		 */
		$this->type_registry = $type_registry;
		$this->register_initial_types();

		/**
		 * Gets the location rules for backward compatibility.
		 *
		 * This allows for ACF Field Groups that were registered before the "graphql_types"
		 * field was respected can still work with the old GraphQL Schema rules that mapped
		 * from the ACF Location rules.
		 */
		$this->location_rules = $this->get_location_rules();

		/**
		 * Add ACF Fields to GraphQL Types
		 */
		$this->add_options_pages_to_schema();
		$this->add_acf_fields_to_graphql_types();

		// This filter tells WPGraphQL to resolve revision meta for ACF fields from the revision's meta, instead
		// of the parent (published post) meta.
		add_filter( 'graphql_resolve_revision_meta_from_parent', function( $should, $object_id, $meta_key, $single ) {

			// Loop through all registered ACF fields that show in GraphQL.
			if ( is_array( $this->registered_field_names ) && ! empty( $this->registered_field_names ) ) {

				$matches = null;

				// Iterate over all field names
				foreach ( $this->registered_field_names as $field_name ) {

					// If the field name is an exact match with the $meta_key, the ACF field should
					// resolve from the revision meta, so we can return false here, so that meta can
					// resolve from the revision instead of the parent
					if ( $field_name === $meta_key ) {
						return false;
					}

					// For flex fields/repeaters, the meta keys are structured a bit funky.
					// This checks to see if the $meta_key starts with the same string as one of the
					// acf fields (a flex/repeater field) and then checks if it's preceeded by an underscore and a number.
					if ( $field_name === substr( $meta_key, 0, strlen( $field_name ) ) ) {
						// match any string that starts with the field name, followed by an underscore, followed by a number, followed by another string
						// ex my_flex_field_0_text_field or some_repeater_field_12_25MostPopularDogToys
						$pattern = '/' . $field_name . '_\d+_\w+/m';
						preg_match( $pattern, $meta_key, $matches );
					}

					// If the meta key matches the pattern, treat it as a sub-field of an ACF Field Group
					if ( null !== $matches ) {
						return false;
					}

				}

			}

			return $should;
		}, 10, 4 );
	}

	/**
	 * Registers initial Types for use with ACF Fields
	 *
	 * @throws Exception
	 */
	public function register_initial_types() {

		$this->type_registry->register_interface_type(
			'AcfFieldGroup',
			[
				'description' => __( 'A Field Group registered by ACF', 'wp-graphql-acf' ),
				'fields' => [
					'fieldGroupName' => [
						'description' => __( 'The name of the ACF Field Group', 'wp-graphql-acf' ),
						'type' => 'String',
					],
				]
			]
		);

		$this->type_registry->register_object_type(
			'AcfLink',
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

	}



	/**
	 * Gets the location rules
	 * @return array
	 */
	protected function get_location_rules() {

		$field_groups = acf_get_field_groups();
		if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
			return [];
		}

		$rules = [];

		// Each field group that doesn't have GraphQL Types explicitly set should get the location
		// rules interpreted.
		foreach ( $field_groups as $field_group ) {
			if ( ! isset( $field_group['graphql_types'] ) || ! is_array( $field_group['graphql_types'] ) ) {
				$rules[] = $field_group;
			}
		}

		if ( empty( $rules ) ) {
			return [];
		}

		// If there are field groups with no graphql_types field set, inherit the rules from
		// ACF Location Rules
		$rules = new LocationRules();
		$rules->determine_location_rules();
		return $rules->get_rules();
	}

	protected function add_options_pages_to_schema() {

		global $acf_options_page;

		if ( ! isset( $acf_options_page ) ) {
			return ;
		}

		/**
		 * Get a list of post types that have been registered to show in graphql
		 */
		$graphql_options_pages = acf_get_options_pages();

		/**
		 * If there are no post types exposed to GraphQL, bail
		 */
		if ( empty( $graphql_options_pages ) || ! is_array( $graphql_options_pages ) ) {
			return;
		}

		$options_pages_to_register = [];

		/**
		 * Loop over the post types exposed to GraphQL
		 */
		foreach ( $graphql_options_pages as $options_page_key => $options_page ) {
			if ( ! isset( $options_page['show_in_graphql'] ) || false === (bool) $options_page['show_in_graphql'] ) {
				continue;
			}

			/**
			 * Get options page properties.
			 */
			$page_title = $options_page['page_title'];
			$page_slug  = $options_page['menu_slug'];
			$type_name = isset( $options_page['graphql_field_name'] ) ? Utils::format_type_name( $options_page['graphql_field_name'] ) : Utils::format_type_name( $options_page['menu_slug'] );

			$options_pages_to_register[ $type_name ] = [
				'title' => $page_title,
				'slug' => $page_slug,
				'type_name' => $type_name,
				'options_page' => $options_page,
			];

		}

		if ( is_array( $options_pages_to_register ) && ! empty( $options_pages_to_register ) ) {

			foreach ( $options_pages_to_register as $page_to_register ) {

				$page_title = $page_to_register['title'];
				$page_slug  = $page_to_register['slug'];
				$type_name = isset( $page_to_register['type_name'] ) ? Utils::format_type_name( $page_to_register['type_name'] ) : Utils::format_type_name( $page_to_register['slug'] );
				$options_page = $page_to_register['options_page'];

				$this->type_registry->register_object_type( $type_name, [
					'description' => sprintf( __( '%s options.', 'wp-graphql-acf' ), $page_title ),
					'fields' => [
						'pageTitle' => [
							'type'    => 'String',
							'resolve' => function( $source ) use ( $page_title ) {
								return ! empty( $page_title ) ? $page_title : null;
							},
						],
						'pageSlug' => [
							'type'    => 'String',
							'resolve' => function( $source ) use ( $page_slug ) {
								return ! empty( $page_slug ) ? $page_slug : null;
							},
						],
					],
				] );

				$field_name = Utils::format_field_name( $type_name );

				$options_page['type'] = 'options_page';
				$this->type_registry->register_field(
					'RootQuery',
					$field_name,
					[
						'type' => $type_name,
						'description' => sprintf( __( '%s options.', 'wp-graphql-acf' ), $page_title ),
						'resolve' => function() use ( $options_page ) {
							return ! empty( $options_page ) ? $options_page : null;
						}
					]
				);

			}
		}

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
				( isset( $field_group['active'] ) && true != $field_group['active'] ) ||
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
	 * Undocumented function
	 *
	 * @param [type] $root Undocumented.
	 * @param [type] $acf_field Undocumented.
	 * @param boolean $format Whether ACF should apply formatting to the field. Default false.
	 *
	 * @return mixed
	 */
	protected function get_acf_field_value( $root, $acf_field, $format = false ) {

		$value = null;
		$id = null;

		if ( is_array( $root ) && isset( $root['node'] ) ) {
			$id = $root['node']->ID;
		}

		if ( is_array( $root ) && ! ( ! empty( $root['type'] ) && 'options_page' === $root['type'] ) ) {

			if ( isset( $root[ $acf_field['key'] ] ) ) {
				$value = $root[ $acf_field['key'] ];

				if ( 'wysiwyg' === $acf_field['type'] ) {
					$value = apply_filters( 'the_content', $value );
				}

			}
		} else {

			switch ( true ) {

				case $root instanceof Term:
					$id = 'term_' . $root->term_id;
					break;
				case $root instanceof Post:
					$id = absint( $root->databaseId );
					break;
				case $root instanceof MenuItem:
					$id = absint( $root->menuItemId );
					break;
				case $root instanceof Menu:
					$id = 'term_' . $root->menuId;
					break;
				case $root instanceof User:
					$id = 'user_' . absint( $root->userId );
					break;
				case $root instanceof Comment:
					$id = 'comment_' . absint( $root->databaseId );
					break;
				case is_array( $root ) && ! empty( $root['type'] ) && 'options_page' === $root['type']:
					$id = $root['post_id'];
					break;
				default:
					$id = null;
					break;
			}
		}

		if ( empty( $value ) ) {

			/**
			 * Filters the root ID, allowing additional Models the ability to provide a way to resolve their ID
			 *
			 * @param int   $id   The ID of the object. Default null
			 * @param mixed $root The Root object being resolved. The ID is typically a property of this object.
			 */
			$id = apply_filters( 'graphql_acf_get_root_id', $id, $root );

			if ( empty( $id ) ) {
				return null;
			}

			$format = false;

			if ( 'wysiwyg' === $acf_field['type'] ) {
				$format = true;
			}

			if ( 'select' === $acf_field['type'] ) {
				$format = true;
			}

			/**
			 * Check if cloned field and retrieve the key accordingly.
			 */
			if ( ! empty( $acf_field['_clone'] ) ) {
				$key = $acf_field['__key'];
			} else {
				$key = $acf_field['key'];
			}

			$field_value = get_field( $key, $id, $format );

			$value = ! empty( $field_value ) ? $field_value : null;

		}

		/**
		 * Filters the returned ACF field value
		 *
		 * @param mixed $value     The resolved ACF field value
		 * @param array $acf_field The ACF field config
		 * @param mixed $root      The Root object being resolved. The ID is typically a property of this object.
		 * @param int   $id        The ID of the object
		 */
		return apply_filters( 'graphql_acf_field_value', $value, $acf_field, $root, $id );

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
			'radio',
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
	 * @param string $type_name The name of the GraphQL Type to add the field to.
	 * @param string $field_name The name of the field to add to the GraphQL Type.
	 * @param array $config The GraphQL configuration of the field.
	 *
	 * @return mixed
	 */
	protected function register_graphql_field( string $type_name, string $field_name, array $config ) {
		$acf_field = isset( $config['acf_field'] ) ? $config['acf_field'] : null;
		$acf_type  = isset( $acf_field['type'] ) ? $acf_field['type'] : null;

		if ( empty( $acf_type ) ) {
			return false;
		}

		/**
		 * filter the field config for custom field types
		 *
		 * @param array $field_config
		 */
		$field_config = apply_filters( 'wpgraphql_acf_register_graphql_field', [
			'type'    => null,
			'resolve' => isset( $config['resolve'] ) && is_callable( $config['resolve'] ) ? $config['resolve'] : function( $root, $args, $context, $info ) use ( $acf_field ) {
				$value = $this->get_acf_field_value( $root, $acf_field );
				return ! empty( $value ) ? $value : null;
			},
		], $type_name, $field_name, $config );

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
				// Even though Selects and Radios in ACF can _technically_ be an integer
				// we're choosing to always cast as a string because with
				// GraphQL we can't return different types
				$field_config['type'] = 'String';
				break;
			case 'textarea':
				$field_config['type'] = 'String';
				$field_config['resolve'] = function( $root ) use ( $acf_field ) {
					$value = $this->get_acf_field_value( $root, $acf_field );

					if ( ! empty( $acf_field['new_lines'] ) ) {
						if ( 'wpautop' === $acf_field['new_lines'] ) {
							$value = wpautop( $value );
						}
						if ( 'br' === $acf_field['new_lines'] ) {
							$value = nl2br( $value );
						}
					}
					return $value;


				};
				break;
			case 'select':

				/**
				 * If the select field is configured to not allow multiple values
				 * the field will return a string, but if it is configured to allow
				 * multiple values it will return a list of strings, and an empty array
				 * if no values are set.
				 *
				 * @see: https://github.com/wp-graphql/wp-graphql-acf/issues/25
				 */
				if ( empty( $acf_field['multiple'] ) ) {
					if('array' === $acf_field['return_format'] ){
						$field_config['type'] = [ 'list_of' => 'String' ];
						$field_config['resolve'] = function( $root ) use ( $acf_field) {
							$value = $this->get_acf_field_value( $root, $acf_field, true);

							return ! empty( $value ) && is_array( $value ) ? $value : [];
						};
					}else{
						$field_config['type'] = 'String';
					}
				} else {
					$field_config['type']    = [ 'list_of' => 'String' ];
					$field_config['resolve'] = function( $root ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );

						return ! empty( $value ) && is_array( $value ) ? $value : [];
					};
				}
				break;
			case 'radio':
				$field_config['type'] = 'String';
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
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {

						$value = $this->get_acf_field_value( $root, $acf_field, true );

						if ( ! empty( $value ) && ! empty( $acf_field['return_format'] ) ) {
							$value = date( $acf_field['return_format'], strtotime( $value ) );
						}
						return ! empty( $value ) ? $value : null;
					},
				];
				break;
			case 'relationship':

				if ( isset( $acf_field['post_type'] ) && is_array( $acf_field['post_type'] ) ) {

					$field_type_name = $type_name . '_' . ucfirst( self::camel_case( $acf_field['name'] ) );

					if ( $this->type_registry->get_type( $field_type_name ) == $field_type_name ) {
						$type = $field_type_name;
					} else {
						$type_names = [];
						foreach ( $acf_field['post_type'] as $post_type ) {
							if ( in_array( $post_type, get_post_types([ 'show_in_graphql' => true ]), true ) ) {
								$type_names[ $post_type ] = get_post_type_object( $post_type )->graphql_single_name;
							}
						}

						if ( empty( $type_names ) ) {
							$type = 'PostObjectUnion';
						} else {
							register_graphql_union_type( $field_type_name, [
								'typeNames'   => $type_names,
								'resolveType' => function( $value ) use ( $type_names ) {
									$post_type_object = get_post_type_object( $value->post_type );
									return ! empty( $post_type_object->graphql_single_name ) ? $this->type_registry->get_type( $post_type_object->graphql_single_name ) : null;
								}
							] );

							$type = $field_type_name;
						}


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
								$post_object = get_post( $post_id );
								if ( $post_object instanceof \WP_Post ) {
									$post_model     = new Post( $post_object );
									$relationship[] = $post_model;
								}
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
					if ( $this->type_registry->get_type( $field_type_name ) == $field_type_name ) {
						$type = $field_type_name;
					} else {
						$type_names = [];
						foreach ( $acf_field['post_type'] as $post_type ) {
							if ( in_array( $post_type, \get_post_types( [ 'show_in_graphql' => true ] ), true ) ) {
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
								$post_type_object = get_post_type_object( $value->post_type );
								return ! empty( $post_type_object->graphql_single_name ) ? $this->type_registry->get_type( $post_type_object->graphql_single_name ) : null;
							}
						] );

						$type = $field_type_name;
					}
				} else {
					$type = 'PostObjectUnion';
				}

				// If the field is allowed to be a multi select
				if ( 0 !== $acf_field['multiple'] ) {
					$type = [ 'list_of' => $type ];
				}

				$field_config = [
					'type'    => $type,
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );

						$return = [];
						if ( ! empty( $value ) ) {
							if ( is_array( $value ) ) {
								foreach ( $value as $id ) {
									$post = get_post( $id );
									if ( ! empty( $post ) ) {
										$return[] = new Post( $post );
									}
								}
							} else {
								$post = get_post( absint( $value ) );
								if ( ! empty( $post ) ) {
									$return[] = new Post( $post );
								}
							}
						}

						// If the field is allowed to be a multi select
						if ( 0 !== $acf_field['multiple'] ) {
							$return = ! empty( $return ) ? $return : null;
						} else {
							$return = ! empty( $return[0] ) ? $return[0] : null;
						}

						/**
						 * This hooks allows for filtering of the post object source. In case an non-core defined
						 * post-type is being targeted.
						 *
						 * @param mixed|null  $source  GraphQL Type source.
						 * @param mixed|null  $value   Root ACF field value.
						 * @param AppContext  $context AppContext instance.
						 * @param ResolveInfo $info    ResolveInfo instance.
						 */
						return apply_filters(
							'graphql_acf_post_object_source',
							$return,
							$value,
							$context,
							$info
						);

					},
				];
				break;
			case 'link':
				$field_config['type'] = 'AcfLink';
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
								$post_object = get_post( (int) $image );
								if ( $post_object instanceof \WP_Post ) {
									$post_model = new Post( $post_object );
									$gallery[]  = $post_model;
								}
							}
						}

						return isset( $value ) ? $gallery : null;
					},
				];
				break;
			case 'user':

				$type = 'User';

				if ( isset( $acf_field['multiple'] ) &&  1 === $acf_field['multiple'] ) {
					$type = [ 'list_of' => $type ];
				}

				$field_config = [
					'type'    => $type,
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );

						$return = [];
						if ( ! empty( $value ) ) {
							if ( is_array( $value ) ) {
								foreach ( $value as $id ) {
									$user = get_user_by( 'id', $id );
									if ( ! empty( $user ) ) {
										$user = new User( $user );
										if ( 'private' !== $user->get_visibility() ) {
											$return[] = $user;
										}
									}
								}
							} else {
								$user = get_user_by( 'id', absint( $value ) );
								if ( ! empty( $user ) ) {
									$user = new User( $user );
									if ( 'private' !== $user->get_visibility() ) {
										$return[] = $user;
									}
								}
							}
						}

						// If the field is allowed to be a multi select
						if ( 0 !== $acf_field['multiple'] ) {
							$return = ! empty( $return ) ? $return : null;
						} else {
							$return = ! empty( $return[0] ) ? $return[0] : null;
						}

						return $return;
					},
				];
				break;
			case 'taxonomy':

				$type = 'TermObjectUnion';

				if ( isset( $acf_field['taxonomy'] ) ) {
					$tax_object = get_taxonomy( $acf_field['taxonomy'] );
					if ( isset( $tax_object->graphql_single_name ) ) {
						$type = $tax_object->graphql_single_name;
					}
				}

				$is_multiple = isset( $acf_field['field_type'] ) && in_array( $acf_field['field_type'], array( 'checkbox', 'multi_select' ) );

				$field_config = [
					'type'    => $is_multiple ? ['list_of' => $type ] : $type,
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field, $is_multiple ) {
						$value = $this->get_acf_field_value( $root, $acf_field );
						/**
						 * If this is multiple, the value will most likely always be an array.
						 * If it isn't, we want to return a single term id.
						 */
						if ( ! empty( $value ) && is_array( $value ) ) {
							foreach ( $value as $term ) {
								$terms[] = DataSource::resolve_term_object( (int) $term, $context );
							}
							return $terms;
						} else {
							return DataSource::resolve_term_object( (int) $value, $context );
						}
					},
				];
				break;

			// Accordions are not represented in the GraphQL Schema.
			case 'accordion':
				$field_config = null;
				break;
			case 'group':

				$field_type_name = $type_name . '_' . ucfirst( self::camel_case( $acf_field['name'] ) );
				if ( null !== $this->type_registry->get_type( $field_type_name ) ) {
					$field_config['type'] = $field_type_name;
					break;
				}

				$this->type_registry->register_object_type(
					$field_type_name,
					[
						'description' => __( 'Field Group', 'wp-graphql-acf' ),
						'interfaces' => [ 'AcfFieldGroup' ],
						'fields'      => [
							'fieldGroupName' => [
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
				if ( $this->type_registry->get_type( $field_type_name ) == $field_type_name ) {
					$field_config['type'] = $field_type_name;
					break;
				}

				$fields = [
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
				];

				// ACF 5.8.6 added more data to Google Maps field value
				// https://www.advancedcustomfields.com/changelog/
				if ( \acf_version_compare(acf_get_db_version(), '>=', '5.8.6' ) ) {
                    $fields += [
                        'streetName' => [
							'type'        => 'String',
							'description' => __( 'The street name associated with the map', 'wp-graphql-acf' ),
							'resolve'     => function( $root ) {
								return isset( $root['street_name'] ) ? $root['street_name'] : null;
							},
                        ],
                        'streetNumber' => [
							'type'        => 'String',
							'description' => __( 'The street number associated with the map', 'wp-graphql-acf' ),
							'resolve'     => function( $root ) {
								return isset( $root['street_number'] ) ? $root['street_number'] : null;
							},
                        ],
                        'city' => [
							'type'        => 'String',
							'description' => __( 'The city associated with the map', 'wp-graphql-acf' ),
							'resolve'     => function( $root ) {
								return isset( $root['city'] ) ? $root['city'] : null;
							},
                        ],
                        'state' => [
							'type'        => 'String',
							'description' => __( 'The state associated with the map', 'wp-graphql-acf' ),
							'resolve'     => function( $root ) {
								return isset( $root['state'] ) ? $root['state'] : null;
							},
                        ],
                        'stateShort' => [
							'type'        => 'String',
							'description' => __( 'The state abbreviation associated with the map', 'wp-graphql-acf' ),
							'resolve'     => function( $root ) {
								return isset( $root['state_short'] ) ? $root['state_short'] : null;
							},
                        ],
                        'postCode' => [
							'type'        => 'String',
							'description' => __( 'The post code associated with the map', 'wp-graphql-acf' ),
							'resolve'     => function( $root ) {
								return isset( $root['post_code'] ) ? $root['post_code'] : null;
							},
                        ],
                        'country' => [
							'type'        => 'String',
							'description' => __( 'The country associated with the map', 'wp-graphql-acf' ),
							'resolve'     => function( $root ) {
								return isset( $root['country'] ) ? $root['country'] : null;
							},
                        ],
                        'countryShort' => [
							'type'        => 'String',
							'description' => __( 'The country abbreviation associated with the map', 'wp-graphql-acf' ),
							'resolve'     => function( $root ) {
								return isset( $root['country_short'] ) ? $root['country_short'] : null;
							},
                        ],
                        'placeId' => [
							'type'        => 'String',
							'description' => __( 'The country associated with the map', 'wp-graphql-acf' ),
							'resolve'     => function( $root ) {
								return isset( $root['place_id'] ) ? $root['place_id'] : null;
							},
                        ],
                        'zoom' => [
							'type'        => 'String',
							'description' => __( 'The zoom defined with the map', 'wp-graphql-acf' ),
							'resolve'     => function( $root ) {
								return isset( $root['zoom'] ) ? $root['zoom'] : null;
							},
                        ],
                    ];
                }

				$this->type_registry->register_object_type(
					$field_type_name,
					[
						'description' => __( 'Google Map field', 'wp-graphql-acf' ),
						'fields'      => $fields,
					]
				);
				$field_config['type'] = $field_type_name;
				break;
			case 'repeater':
				$field_type_name = $type_name . '_' . self::camel_case( $acf_field['name'] );

				if ( $this->type_registry->get_type( $field_type_name ) ) {
					$field_config['type'] = $field_type_name;
					break;
				}

				$this->type_registry->register_object_type(
					$field_type_name,
					[
						'description' => __( 'Field Group', 'wp-graphql-acf' ),
						'interfaces' => [ 'AcfFieldGroup' ],
						'fields'      => [
							'fieldGroupName' => [
								'resolve' => function( $source ) use ( $acf_field ) {
									return ! empty( $acf_field['name'] ) ? $acf_field['name'] : null;
								},
							],
						],
						'resolve'     => function( $source ) use ( $acf_field ) {
							$repeater = $this->get_acf_field_value( $source, $acf_field );

							return ! empty( $repeater ) ? $repeater : [];
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
				if ( $this->type_registry->get_type( $field_type_name ) ) {
					$field_config['type'] = $field_type_name;
					break;
				}

				if ( ! empty( $acf_field['layouts'] ) && is_array( $acf_field['layouts'] ) ) {

					$union_types = [];
					foreach ( $acf_field['layouts'] as $layout ) {

						$flex_field_layout_name = ! empty( $layout['name'] ) ? ucfirst( self::camel_case( $layout['name'] ) ) : null;
						$flex_field_layout_name = ! empty( $flex_field_layout_name ) ? $field_type_name . '_' . $flex_field_layout_name : null;

						/**
						 * If there are no layouts defined for the Flex Field
						 */
						if ( empty( $flex_field_layout_name ) ) {
							continue;
						}

						$layout_type            = $this->type_registry->get_type( $flex_field_layout_name );

						if ( $layout_type ) {
							$union_types[ $layout['name'] ] = $layout_type;
						} else {


							$this->type_registry->register_object_type( $flex_field_layout_name, [
								'description' => __( 'Group within the flex field', 'wp-graphql-acf' ),
								'interfaces' => [ 'AcfFieldGroup' ],
								'fields'      => [
									'fieldGroupName' => [
										'resolve' => function( $source ) use ( $flex_field_layout_name ) {
											return ! empty( $flex_field_layout_name ) ? $flex_field_layout_name : null;
										},
									],
								],
							] );

							$union_types[ $layout['name'] ] = $flex_field_layout_name;


							$layout['parent']          = $acf_field;
							$layout['show_in_graphql'] = isset( $acf_field['show_in_graphql'] ) ? (bool) $acf_field['show_in_graphql'] : true;
							$this->add_field_group_fields( $layout, $flex_field_layout_name, true );
						}
					}

					$this->type_registry->register_union_type( $field_type_name, [
						'typeNames'       => $union_types,
						'resolveType' => function( $value ) use ( $union_types ) {
							return isset( $union_types[ $value['acf_fc_layout'] ] ) ? $this->type_registry->get_type( $union_types[ $value['acf_fc_layout'] ] ) : null;
						}
					] );

					$field_config['type']    = [ 'list_of' => $field_type_name ];
					$field_config['resolve'] = function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );

						return ! empty( $value ) ? $value : [];
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
		$this->registered_field_names[] = $acf_field['name'];
		return $this->type_registry->register_field( $type_name, $field_name, $config );
	}

	/**
	 * Given a field group array, this adds the fields to the specified Type in the Schema
	 *
	 * @param array  $field_group The group to add to the Schema.
	 * @param string $type_name   The Type name in the GraphQL Schema to add fields to.
	 * @param bool   $layout      Whether or not these fields are part of a Flex Content layout.
	 */
	protected function add_field_group_fields( array $field_group, string $type_name, $layout = false ) {

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
		$acf_fields = ! empty( $field_group['sub_fields'] ) || $layout ? $field_group['sub_fields'] : acf_get_fields( $field_group );

		/**
		 * If there are no fields, bail
		 */
		if ( empty( $acf_fields ) || ! is_array( $acf_fields ) ) {
			return;
		}

		/**
		 * Stores field keys to prevent duplicate field registration for cloned fields
		 */
		$processed_keys = [];

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
			$name            = empty( $explicit_name ) && ! empty( $acf_field['name'] ) ? self::camel_case( $acf_field['name'] ) : $explicit_name;
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

			$this->register_graphql_field( $type_name, $name, $config );

		}

	}

	/**
	 * Returns all available GraphQL Types
	 *
	 * @return array
	 */
	public static function get_all_graphql_types() {
		$graphql_types = array();

		// Use GraphQL to get the Interface and the Types that implement them
		$query = '
		query GetPossibleTypes($name:String!){
			__type(name:$name){
				name
				description
				possibleTypes {
					name
					description
				}
			}
		}
		';

		$interfaces = [
			'ContentNode' => [
				'label' => __( 'Post Type', 'wp-graphql-acf' ),
				'plural_label' => __( 'All Post Types', 'wp-graphql-acf' ),
			],
			'TermNode' => [
				'label' => __( 'Taxonomy', 'wp-graphql-acf' ),
				'plural_label' => __( 'All Taxonomies', 'wp-graphql-acf' ),
			],
			'ContentTemplate' => [
				'label' => __( 'Page Template', 'wp-graphql-acf' ),
				'plural_label' => __( 'All Templates Assignable to Content', 'wp-graphql-acf' ),
			]
		];

		foreach ( $interfaces as $interface_name => $config ) {

			$interface_query = graphql([
				'query' => $query,
				'variables' => [
					'name' => $interface_name
				]
			]);

			$possible_types = $interface_query['data']['__type']['possibleTypes'];
			asort( $possible_types );

			if ( ! empty( $possible_types ) && is_array( $possible_types ) ) {

				// Intentionally not translating "ContentNode Interface" as this is part of the GraphQL Schema and should not be translated.
				$graphql_types[ $interface_name ] = '<span data-interface="'. $interface_name .'">' . $interface_name . ' Interface (' . $config['plural_label'] . ')</span>';
				$label = '<span data-implements="'. $interface_name .'"> (' . $config['label'] . ')</span>';
				foreach ( $possible_types as $type ) {
					$type_label = $type['name'] . $label;
					$type_key = $type['name'];

					$graphql_types[ $type_key ] = $type_label;
				}
			}

		}

		/**
		 * Add comment to GraphQL types
		 */
		$graphql_types['Comment'] = __( 'Comment', 'wp-graphql-acf' );

		/**
		 * Add menu to GraphQL types
		 */
		$graphql_types['Menu'] = __( 'Menu', 'wp-graphql-acf' );

		/**
		 * Add menu items to GraphQL types
		 */
		$graphql_types['MenuItem'] = __( 'Menu Item', 'wp-graphql-acf' );

		/**
		 * Add users to GraphQL types
		 */
		$graphql_types['User'] = __( 'User', 'wp-graphql-acf' );


		/**
		 * Add options pages to GraphQL types
		 */
		global $acf_options_page;

		if ( isset( $acf_options_page ) ) {
			/**
			 * Get a list of post types that have been registered to show in graphql
			 */
			$graphql_options_pages = acf_get_options_pages();

			/**
			 * If there are no post types exposed to GraphQL, bail
			 */
			if ( ! empty( $graphql_options_pages ) && is_array( $graphql_options_pages ) ) {

				/**
				 * Prepare type key prefix and label surfix
				 */
				$label = '<span class="options-page"> (' . __( 'ACF Options Page', 'wp-graphql-acf' ) . ')</span>';

				/**
				 * Loop over the post types exposed to GraphQL
				 */
				foreach ( $graphql_options_pages as $options_page_key => $options_page ) {
					if ( ! isset( $options_page['show_in_graphql'] ) || false === (bool) $options_page['show_in_graphql'] ) {
						continue;
					}

					/**
					 * Get options page properties.
					 */
					$page_title = $options_page['page_title'];
					$type_label = $page_title . $label;
					$type_name = isset( $options_page['graphql_field_name'] ) ? Utils::format_type_name( $options_page['graphql_field_name'] ) : Utils::format_type_name( $options_page['menu_slug'] );

					$graphql_types[ $type_name ] = $type_label;
				}
			}
		}

		return $graphql_types;
	}

	/**
	 * Adds acf field groups to GraphQL types.
	 */
	protected function add_acf_fields_to_graphql_types() {
		/**
		 * Get all the field groups
		 */
		$field_groups = acf_get_field_groups();

		/**
		 * If there are no acf field groups, bail
		 */
		if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
			return;
		}

		/**
		 * Loop over all the field groups
		 */
		foreach ( $field_groups as $field_group ) {

			$field_group_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : $field_group['title'];
			$field_group_name = Utils::format_field_name( $field_group_name );

			$manually_set_graphql_types = isset( $field_group['map_graphql_types_from_location_rules'] ) ? (bool) $field_group['map_graphql_types_from_location_rules'] : false;

			if ( false === $manually_set_graphql_types ) {
				if ( ! isset( $field_group['graphql_types'] ) || empty( $field_group['graphql_types'] ) ) {
					$field_group['graphql_types'] = [];
					$location_rules               = $this->get_location_rules();
					if ( isset( $location_rules[ $field_group_name ] ) ) {
						$field_group['graphql_types'] = $location_rules[ $field_group_name ];
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
			if ( ! $this->should_field_group_show_in_graphql( $field_group ) ) {
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
				'resolve'         => function ( $root ) use ( $field_group ) {
					return isset( $root ) ? $root : null;
				}
			];

			$qualifier =  sprintf( __( 'Added to the GraphQL Schema because the ACF Field Group "%1$s" was set to Show in GraphQL.', 'wp-graphql-acf' ), $field_group['title'] );
			$config['description'] = $field_group['description'] ? $field_group['description'] . ' | ' . $qualifier : $qualifier;

			/**
			 * Loop over the GraphQL types for this field group on
			 */
			foreach ( $graphql_types as $graphql_type ) {
				$this->register_graphql_field( $graphql_type, $field_name, $config );
			}
		}

	}

}
