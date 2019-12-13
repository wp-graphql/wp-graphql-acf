<?php
/**
 * Config for WPGraphQL ACF
 *
 * @package wp-graphql-acf
 */

namespace WPGraphQL\ACF;

use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Comment;
use WPGraphQL\Model\Menu;
use WPGraphQL\Model\MenuItem;
use WPGraphQL\Model\Post;
use WPGraphQL\Model\Term;
use WPGraphQL\Model\User;

/**
 * Config class.
 */
class Config {

	protected $type_registry;

	/**
	 * Initialize WPGraphQL to ACF
	 *
	 * @param \WPGraphQL\Registry\TypeRegistry $type_registry Instance of the WPGraphQL TypeRegistry
	 */
	public function init( \WPGraphQL\Registry\TypeRegistry $type_registry ) {

		/**
		 * Set the TypeRegistry
		 */
		$this->type_registry = $type_registry;

		/**
		 * Add ACF Fields to GraphQL Types
		 */
		$this->add_acf_fields_to_post_object_types();
		$this->add_acf_fields_to_term_objects();
		$this->add_acf_fields_to_comments();
		$this->add_acf_fields_to_menus();
		$this->add_acf_fields_to_menu_items();
		$this->add_acf_fields_to_media_items();
		$this->add_acf_fields_to_individual_posts();
		$this->add_acf_fields_to_users();
		$this->add_acf_fields_to_options_pages();
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
	 * Add ACF Fields to Post Object Types.
	 *
	 * This gets the Post Types that are configured to show_in_graphql and iterates
	 * over them to expose ACF Fields to their Type in the GraphQL Schema.
	 */
	protected function add_acf_fields_to_post_object_types() {

		/**
		 * Get a list of post types that have been registered to show in graphql
		 */
		$graphql_post_types = get_post_types( [ 'show_in_graphql' => true ] );

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
		$id = null;

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
					$id = acf_get_term_post_id( $root->taxonomyName, $root->term_id );
					break;
				case $root instanceof Post:
					$id = absint( $root->ID );
					break;
				case $root instanceof MenuItem:
					$id = absint( $root->menuItemId );
					break;
				case $root instanceof Menu:
					$id = acf_get_term_post_id( 'nav_menu', $root->menuId );
					break;
				case $root instanceof User:
					$id = 'user_' . absint( $root->userId );
					break;
				case $root instanceof Comment:
					$id = 'comment_' . absint( $root->comment_ID );
					break;
				case is_array( $root ) && ! empty( $root['type'] ) && 'options_page' === $root['type']:
					$id = $root['post_id'];
					break;
				default:
					$id = null;
					break;
			}

			/**
			 * Filters the root ID, allowing additional Models the ability to provide a way to resolve their ID
			 *
			 * @param int   $id    The ID of the object. Default null
			 * @param mixed $root  The Root object being resolved. The ID is typically a property of this object.
			 */
			$id = apply_filters( 'graphql_acf_get_root_id', $id, $root );

			if ( empty( $id ) ) {
				return null;
			}

			$format = false;

			if ( 'wysiwyg' === $acf_field['type'] ) {
				$format = true;
			}

			$field_value = get_field( $acf_field['key'], $id, $format );

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
				if ( 0 === $acf_field['multiple'] ) {
					$field_config['type'] = 'String';
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
						if ( isset( $root->ID ) ) {
							return get_field( $acf_field['key'], $root->ID, true );
						}
						//handle sub fields
						if ( isset( $root[ $acf_field['key'] ] ) ) {
							$value     = $root[ $acf_field['key'] ];
							$timestamp = strtotime( $value );

							return date( $acf_field['return_format'], $timestamp );
						}

						return null;
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
							if ( in_array( $post_type, \get_post_types( [ 'show_in_graphql' => true ]), true ) ) {
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

				$field_config = [
					'type'    => $type,
					'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
						$value = $this->get_acf_field_value( $root, $acf_field );
						if ( $value instanceof \WP_Post ) {
							return new Post( $value );
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
							absint( $value ) ? DataSource::resolve_post_object( (int) $value, $context ) : null,
							$value,
							$context,
							$info
						);

					},
				];
				break;
			case 'link':

				$field_type_name = 'ACF_Link';
				if ( $this->type_registry->get_type( $field_type_name ) == $field_type_name ) {
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
				if ( $this->type_registry->get_type( $field_type_name ) ) {
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
				if (\acf_version_compare(acf_get_db_version(), '>=', '5.8.6')) {
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

				register_graphql_object_type(
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

							$union_types[ $layout['name'] ] = $flex_field_layout_name;


							$layout['parent']          = $acf_field;
							$layout['show_in_graphql'] = isset( $acf_field['show_in_graphql'] ) ? (bool) $acf_field['show_in_graphql'] : true;
							$this->add_field_group_fields( $layout, $flex_field_layout_name );
						}
					}

					register_graphql_union_type( $field_type_name, [
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

		return $this->type_registry->register_field( $type_name, $field_name, $config );
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
	 * Add field groups to Taxonomies
	 *
	 * @return void
	 */
	protected function add_acf_fields_to_term_objects() {

		/**
		 * Get a list of taxonomies that have been registered to show in graphql
		 */
		$graphql_taxonomies = \WPGraphQL::get_allowed_taxonomies();

		/**
		 * If there are no taxonomies exposed to GraphQL, bail
		 */
		if ( empty( $graphql_taxonomies ) || ! is_array( $graphql_taxonomies ) ) {
			return;
		}

		/**
		 * Loop over the taxonomies exposed to GraphQL
		 */
		foreach ( $graphql_taxonomies as $taxonomy ) {

			/**
			 * Get the field groups associated with the taxonomy
			 */
			$field_groups = acf_get_field_groups(
				[
					'taxonomy' => $taxonomy,
				]
			);

			/**
			 * If there are no field groups for this taxonomy, move on to the next one.
			 */
			if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
				continue;
			}

			/**
			 * Get the Taxonomy object
			 */
			$tax_object = get_taxonomy( $taxonomy );

			if ( empty( $tax_object ) || ! isset( $tax_object->graphql_single_name ) ) {
				return;
			}

			/**
			 * Loop over the field groups for this post type
			 */
			foreach ( $field_groups as $field_group ) {

				$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );

				$field_group['type'] = 'group';
				$field_group['name'] = $field_name;
				$description         = $field_group['description'] ? $field_group['description'] . ' | ' : '';
				$config              = [
					'name'            => $field_name,
					'description'     => $description . sprintf( __( 'Added to the GraphQL Schema because the ACF Field Group "%1$s" was assigned to the "%2$s" taxonomy', 'wp-graphql-acf' ), $field_group['title'], $tax_object->name ),
					'acf_field'       => $field_group,
					'acf_field_group' => null,
					'resolve'         => function( $root ) use ( $field_group ) {
						return isset( $root ) ? $root : null;
					}
				];

				$this->register_graphql_field( $tax_object->graphql_single_name, $field_name, $config );
			}
		}
	}

	/**
	 * Add ACF Fields to comments
	 *
	 * @return void
	 */
	protected function add_acf_fields_to_comments() {

		$comment_field_groups = [];

		/**
		 * Get the field groups associated with the taxonomy
		 */
		$field_groups = acf_get_field_groups();

		foreach ( $field_groups as $field_group ) {
			if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {
				foreach ( $field_group['location'] as $locations ) {
					if ( ! empty( $locations ) && is_array( $locations ) ) {
						foreach ( $locations as $location ) {
							if ( 'comment' === $location['param'] && '!=' === $location['operator'] ) {
								continue;
							}
							if ( 'comment' === $location['param'] && '==' === $location['operator'] ) {
								$comment_field_groups[] = $field_group;
							}
						}
					}
				}
			}
		}

		if ( empty( $comment_field_groups ) ) {
			return;
		}

		/**
		 * Loop over the field groups for this post type
		 */
		foreach ( $comment_field_groups as $field_group ) {

			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );

			$field_group['type'] = 'group';
			$field_group['name'] = $field_name;
			$description         = $field_group['description'] ? $field_group['description'] . ' | ' : '';
			$config              = [
				'name'            => $field_name,
				'description'     => $description . sprintf( __( 'Added to the GraphQL Schema because the ACF Field Group "%s" was assigned to Comments', 'wp-graphql-acf' ), $field_group['title'] ),
				'acf_field'       => $field_group,
				'acf_field_group' => null,
				'resolve'         => function( $root ) use ( $field_group ) {
					return isset( $root ) ? $root : null;
				}
			];

			$this->register_graphql_field( 'Comment', $field_name, $config );

		}

	}

	/**
	 * Add Fields to Menus in the GraphQL Schema
	 *
	 * @return void
	 */
	protected function add_acf_fields_to_menus() {

		$menu_field_groups = [];

		/**
		 * Get the field groups associated with the taxonomy
		 */
		$field_groups = acf_get_field_groups();

		foreach ( $field_groups as $field_group ) {
			if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {
				foreach ( $field_group['location'] as $locations ) {
					if ( ! empty( $locations ) && is_array( $locations ) ) {
						foreach ( $locations as $location ) {
							if ( 'nav_menu' === $location['param'] && '!=' === $location['operator'] ) {
								continue;
							}
							if ( 'nav_menu' === $location['param'] && '==' === $location['operator'] ) {
								$menu_field_groups[] = $field_group;
								break;
							}
						}
					}
				}
			}
		}

		if ( empty( $menu_field_groups ) ) {
			return;
		}

		/**
		 * Loop over the field groups for this post type
		 */
		foreach ( $menu_field_groups as $field_group ) {

			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );

			$field_group['type'] = 'group';
			$field_group['name'] = $field_name;
			$description         = $field_group['description'] ? $field_group['description'] . ' | ' : '';
			$config              = [
				'name'            => $field_name,
				'description'     => $description . sprintf( __( 'Added to the GraphQL Schema because the ACF Field Group "%s" was assigned to Menus', 'wp-graphql-acf' ), $field_group['title'] ),
				'acf_field'       => $field_group,
				'acf_field_group' => null,
				'resolve'         => function( $root ) use ( $field_group ) {
					return isset( $root ) ? $root : null;
				}
			];

			$this->register_graphql_field( 'Menu', $field_name, $config );

		}

	}

	/**
	 * Add ACF Field Groups to Menu Items
	 *
	 * @return void
	 */
	protected function add_acf_fields_to_menu_items() {

		$menu_item_field_groups = [];

		/**
		 * Get the field groups associated with the taxonomy
		 */
		$field_groups = acf_get_field_groups();
		foreach ( $field_groups as $field_group ) {
			if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {
				foreach ( $field_group['location'] as $locations ) {
					if ( ! empty( $locations ) && is_array( $locations ) ) {
						foreach ( $locations as $location ) {
							if ( 'nav_menu_item' === $location['param'] && '!=' === $location['operator'] ) {
								continue;
							}
							if ( 'nav_menu_item' === $location['param'] && '==' === $location['operator'] ) {
								$menu_item_field_groups[] = $field_group;
							}
						}
					}
				}
			}
		}

		if ( empty( $menu_item_field_groups ) ) {
			return;
		}

		/**
		 * Loop over the field groups for this post type
		 */
		foreach ( $menu_item_field_groups as $field_group ) {

			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );

			$field_group['type'] = 'group';
			$field_group['name'] = $field_name;
			$description         = $field_group['description'] ? $field_group['description'] . ' | ' : '';
			$config              = [
				'name'            => $field_name,
				'description'     => $description . sprintf( __( 'Added to the GraphQL Schema because the ACF Field Group "%s" was assigned to Menu Items', 'wp-graphql-acf' ), $field_group['title'] ),
				'acf_field'       => $field_group,
				'acf_field_group' => null,
				'resolve'         => function( $root ) use ( $field_group ) {
					return isset( $root ) ? $root : null;
				}
			];

			$this->register_graphql_field( 'MenuItem', $field_name, $config );

		}
	}

	/**
	 * Add ACF Field Groups to Media Items (attachments)
	 *
	 * @return void
	 */
	protected function add_acf_fields_to_media_items() {

		$media_item_field_groups = [];

		/**
		 * Get the field groups associated with the taxonomy
		 */
		$field_groups = acf_get_field_groups();

		foreach ( $field_groups as $field_group ) {
			if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {
				foreach ( $field_group['location'] as $locations ) {
					if ( ! empty( $locations ) && is_array( $locations ) ) {
						foreach ( $locations as $location ) {
							if ( 'attachment' === $location['param'] && '!=' === $location['operator'] ) {
								continue;
							}
							if ( 'attachment' === $location['param'] && '==' === $location['operator'] ) {
								$media_item_field_groups[] = $field_group;
							}
						}
					}
				}
			}
		}

		if ( empty( $media_item_field_groups ) ) {
			return;
		}

		/**
		 * Loop over the field groups for this post type
		 */
		foreach ( $media_item_field_groups as $field_group ) {

			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );

			$field_group['type'] = 'group';
			$field_group['name'] = $field_name;
			$description         = $field_group['description'] ? $field_group['description'] . ' | ' : '';
			$config              = [
				'name'            => $field_name,
				'description'     => $description . sprintf( __( 'Added to the GraphQL Schema because the ACF Field Group "%s" was assigned to attachments', 'wp-graphql-acf' ), $field_group['title'] ),
				'acf_field'       => $field_group,
				'acf_field_group' => null,
				'resolve'         => function( $root ) use ( $field_group ) {
					return isset( $root ) ? $root : null;
				}
			];

			$this->register_graphql_field( 'MediaItem', $field_name, $config );

		}
	}

	protected function add_acf_fields_to_individual_posts() {

		$post_field_groups = [];

		/**
		 * Get the field groups associated with the taxonomy
		 */
		$field_groups = acf_get_field_groups();

		$allowed_post_types = get_post_types( [
			'show_ui'         => true,
			'show_in_graphql' => true
		] );

		/**
		 * Remove the `attachment` post_type, as it's treated special and we don't
		 * want to add field groups in the same way we do for other post types
		 */
		unset( $allowed_post_types['attachment'] );


		foreach ( $field_groups as $field_group ) {
			if ( ! empty( $field_group['location'] ) && is_array( $field_group['location'] ) ) {
				foreach ( $field_group['location'] as $locations ) {
					if ( ! empty( $locations ) && is_array( $locations ) ) {
						foreach ( $locations as $location ) {

							/**
							 * If the operator is not equal to, we don't need to do anything,
							 * so we can just continue
							 */
							if ( '!=' === $location['operator'] ) {
								continue;
							}

							/**
							 * If the param (the post_type) is in the array of allowed_post_types
							 */
							if ( in_array( $location['param'], $allowed_post_types, true ) && '==' === $location['operator'] ) {

								$post_field_groups[] = [
									'type'        => $location['param'],
									'field_group' => $field_group,
									'post_id'     => $location['value']
								];
							}
						}
					}
				}
			}
		}


		/**
		 * If no field groups are assigned to a specific post, we don't need to modify the Schema
		 */
		if ( empty( $post_field_groups ) ) {
			return;
		}

		/**
		 * Loop over the field groups assigned to a specific post
		 * and register them to the Schema
		 */
		foreach ( $post_field_groups as $key => $group ) {

			if ( empty( $group['field_group'] ) || ! is_array( $group['field_group'] ) ) {
				continue;
			}

			$post_object = get_post( (int) $group['post_id'] );

			$allowed_post_types = get_post_types( [ 'show_in_graphql' => true ] );
			if ( ! $post_object instanceof \WP_Post || ! in_array( $post_object->post_type, $allowed_post_types, true ) ) {
				continue;
			}

			$field_group      = $group['field_group'];
			$post_type_object = get_post_type_object( $post_object->post_type );


			$field_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );

			$field_group['type'] = 'group';
			$field_group['name'] = $field_name;
			$description         = $field_group['description'] ? $field_group['description'] . ' | ' : '';
			$config              = [
				'name'            => $field_name,
				'description'     => $description . sprintf( __( 'Added to the GraphQL Schema because the ACF Field Group "%1$s" was assigned to an individual post of the post_type: "%2$s". The group will be present in the Schema for the "%3$s" Type, but will only resolve if the entity has content saved.', 'wp-graphql-acf' ), $field_group['title'], $post_type_object->name, $post_type_object->graphql_plural_name ),
				'acf_field'       => $field_group,
				'acf_field_group' => null,
				'resolve'         => function( $root ) use ( $field_group ) {
					return isset( $root ) ? $root : null;
				}
			];

			$this->register_graphql_field( $post_type_object->graphql_single_name, $field_name, $config );

		}

	}

	/**
	 * Add field groups to users when assigned to user edit/register screens
	 */
	protected function add_acf_fields_to_users() {

		/**
		 * Get the field groups associated with the User edit form
		 */
		$user_edit_field_groups = acf_get_field_groups( [
			'user_form' => 'edit',
		] );

		/**
		 * Get the field groups associated with the User register form
		 */
		$user_register_field_groups = acf_get_field_groups( [
			'user_form' => 'register',
		] );

		/**
		 * Get a unique list of groups that match the register and edit user location rules
		 */
		$field_groups = array_merge( $user_edit_field_groups, $user_register_field_groups );
		$field_groups = array_intersect_key( $field_groups, array_unique( array_map( 'serialize', $field_groups ) ) );


		foreach ( $field_groups as $field_group ) {

			$field_name          = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : Config::camel_case( $field_group['title'] );
			$field_group['type'] = 'group';
			$field_group['name'] = $field_name;
			$description         = $field_group['description'] ? $field_group['description'] . ' | ' : '';
			$config              = [
				'name'            => $field_name,
				'description'     => $description . sprintf( __( 'Added to the GraphQL Schema because the ACF Field Group "%1$s" was assigned to Users edit or register form', 'wp-graphql-acf' ), $field_group['title'] ),
				'acf_field'       => $field_group,
				'acf_field_group' => null,
				'resolve'         => function( $root ) use ( $field_group ) {
					return isset( $root ) ? $root : null;
				}
			];

			$this->register_graphql_field( 'User', $field_name, $config );


		}

	}

	/**
	 * Adds options pages and options page field groups to the schema.
	 */
	protected function add_acf_fields_to_options_pages() {
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

		/**
		 * Loop over the post types exposed to GraphQL
		 */
		foreach ( $graphql_options_pages as $options_page_key => $options_page ) {
			if ( empty( $options_page['show_in_graphql'] ) ) {
				continue;
			}

			/**
			 * Get options page properties.
			 */
			$page_title = $options_page['page_title'];
			$page_slug  = $options_page['menu_slug'];

			/**
			 * Get the field groups associated with the options page
			 */
			$field_groups = acf_get_field_groups(
				[
					'options_page' => $options_page['menu_slug'],
				]
			);

			/**
			 * If there are no field groups for this options page, move on to the next one.
			 */
			if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
				continue;
			}

			/**
			 * Loop over the field groups for this options page.
			 */
			$options_page_fields = array();
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

				$options_page_fields[ $field_name ] = $config;

			}

			/**
			 * Continue if no options to show in GraphQL
			 */
			if ( empty( $options_page_fields ) ) {
				continue;
			}

			/**
			 * Create type name
			 */
			$type_name = ucfirst( Config::camel_case( $page_title ) );

			/**
			 * Register options page type to schema.
			 */
			register_graphql_object_type(
				$type_name,
				[
					'description' => sprintf( __( '%s options', 'wp-graphql-acf' ), $page_title ),
					'fields'      => [
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
				]
			);

			/**
			 * Register options page type to the "RootQuery"
			 */
			$options_page['type'] = 'options_page';
			register_graphql_field(
				'RootQuery',
				Config::camel_case( $page_title ),
				[
					'type'        => $type_name,
					'description' => sprintf( __( '%s options', 'wp-graphql-acf' ), $options_page['page_title'] ),
					'resolve'     => function() use ( $options_page ) {
						return ! empty( $options_page ) ? $options_page : null;
					}
				]
			);

			/**
			 * Register option page fields to the option page type.
			 */
			foreach ( $options_page_fields as $name => $config ) {
				$this->register_graphql_field( $type_name, $name, $config );
			}
		}
	}

}
