<?php

namespace WPGraphQL\ACF;

use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Post;
use WPGraphQL\TypeRegistry;

class Config {

	/**
	 * Initialize WPGraphQL to ACF
	 */
	public function init() {
		/**
		 * Add ACF Fields to GraphQL Types
		 */
		$this->register_acf_types();
		$this->add_acf_fields_to_post_object_types();
		$this->add_acf_fields_to_term_objects();
		$this->add_acf_fields_to_comments();
		$this->add_acf_fields_to_menu_items();
		$this->add_acf_fields_to_media_items();
	}

	protected function register_acf_types() {

		// @todo
	}

	/**
	 * Determines whether a field group should be exposed to the GraphQL Schema. By default, field
	 * groups will not be exposed to GraphQL.
	 *
	 * @param $field_group
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
		return apply_filters( 'WPGraphQL\ACF\should_field_group_show_in_graphql', $show, $field_group, $this );

	}

	/**
	 * @todo: This may be a good utility to add to WPGraphQL Core? May even have something already?
	 *
	 * @param       $str
	 * @param array $noStrip
	 *
	 * @return mixed|null|string|string[]
	 */
	public static function camelCase( $str, array $noStrip = [] ) {
		// non-alpha and non-numeric characters become spaces
		$str = preg_replace( '/[^a-z0-9' . implode( "", $noStrip ) . ']+/i', ' ', $str );
		$str = trim( $str );
		// uppercase the first character of each word
		$str = ucwords( $str );
		$str = str_replace( " ", "", $str );
		$str = lcfirst( $str );

		return $str;
	}

	/**
	 * Given the type of an ACF Field, return the GraphQL Type
	 * it should resolve to in the GraphQL Schema
	 *
	 * @param string $acf_type  The type the ACF
	 * @param array  $acf_field The Field config for the ACF Field
	 *
	 * @return mixed string|null
	 */
	protected function acf_field_type_to_graphql_type( $acf_type, $acf_field ) {

		/**
		 * Map the ACF type to a GraphQL Type
		 */
		switch ( $acf_type ) {
			case 'button_group':
			case 'color_picker':
			case 'date_picker':
			case 'date_time_picker':
			case 'email':
			case 'textarea':
			case 'text':
			case 'link':
			case 'message':
			case 'oembed':
			case 'password':
			case 'time_picker':
			case 'url':
			case 'wysiwyg':
				$graphql_type = 'String';
				break;
			// Accordions are not represented in the GraphQL Schema
			case 'accordion':
				$graphql_type = null;
				break;
			case 'checkbox':
				$graphql_type = [ 'list_of' => 'String' ];
				break;
			case 'file':
				$graphql_type = 'MediaItem';
				break;
			case 'image':
				$graphql_type = 'MediaItem';
				break;
			case 'number':
				$graphql_type = 'float';
				break;
			case 'true_false':
				$graphql_type = 'Boolean';
				break;
			// If a type can't be determined, set as null. No field
			// will be added to the GraphQL Schema if there's no known
			// GraphQL Type to resolve as
			case 'gallery':
				$graphql_type = [ 'list_of' => 'MediaItem' ];
				break;
			case 'page_link':
			case 'post_object':
				$graphql_type = 'PostObjectUnion';
				break;
			case 'relationship':
				$graphql_type = [ 'list_of' => 'PostObjectUnion' ];
				break;
			case 'taxonomy':
				$graphql_type = [ 'list_of' => 'TermObjectUnion' ];
				break;
			case 'user':
				$graphql_type = 'User';
				break;
			case 'group':

				$type_name = self::camelCase( $acf_field['name'] ) . 'FieldGroup';
				if ( TypeRegistry::get_type( $type_name ) ) {
					$graphql_type = $type_name;
					break;
				}

				register_graphql_object_type( $type_name, [
					'description' => __( 'Field Group', 'wp-graphql-acf' ),
					'fields'      => [
						'fieldGroupName' => [
							'type'    => 'String',
							'resolve' => function( $source ) use ( $acf_field ) {
								return ! empty( $acf_field['name'] ) ? $acf_field['name'] : null;
							}
						],
					],
				] );

				$this->add_field_group_fields( $acf_field, $type_name );

				$graphql_type = $type_name;
				break;
			case 'repeater':

				$type_name = self::camelCase( $acf_field['name'] ) . 'Repeater';
				if ( TypeRegistry::get_type( $type_name ) ) {
					$graphql_type = $type_name;
					break;
				}

				register_graphql_object_type( $type_name, [
					'description' => __( 'Field Group', 'wp-graphql-acf' ),
					'fields'      => [
						'fieldGroupName' => [
							'type'    => 'String',
							'resolve' => function( $source ) use ( $acf_field ) {
								return ! empty( $acf_field['name'] ) ? $acf_field['name'] : null;
							}
						],
					],
				] );

				$this->add_field_group_fields( $acf_field, $type_name );

				$graphql_type = [ 'list_of' => $type_name ];
				break;
			case 'google_map':

				$type_name = 'ACFGoogleMap';
				if ( $type = TypeRegistry::get_type( $type_name ) ) {
					$graphql_type = $type_name;
					break;
				}

				register_graphql_object_type( $type_name, [
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
				] );
				$graphql_type = $type_name;
				break;
			case 'flexible_content':

				var_dump( $acf_field );

				$type_name = self::camelCase( $acf_field['name'] ) . 'FlexField';
				if ( TypeRegistry::get_type( $type_name ) ) {
					$graphql_type = $type_name;
					break;
				}

				register_graphql_object_type( $type_name, [
					'description' => __( 'Field Group', 'wp-graphql-acf' ),
					'fields'      => [
						'fieldGroupName' => [
							'type'    => 'String',
							'resolve' => function( $source ) use ( $acf_field ) {
								return ! empty( $acf_field['name'] ) ? $acf_field['name'] : null;
							}
						],
					],
				] );

				$this->add_field_group_fields( $acf_field, $type_name );

				$graphql_type = [ 'list_of' => $type_name ];
				break;
			case 'output':
			case 'radio':
			case 'range':
			case 'select':
			case 'separator':
			case 'tab':
			default:
				$graphql_type = null;
				break;
		}

		/**
		 * Filter the GraphQL Type that an ACF field should translate to when being added to the
		 * GraphQL Schema
		 */
		return apply_filters( 'acf_field_type_to_graphql_type', $graphql_type, $acf_type );

	}

	/**
	 * Add ACF Fields to Post Object Types.
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
			$field_groups = acf_get_field_groups( [
				'post_type' => $post_type,
			] );

			/**
			 * If there are no field groups for this post type, bail early
			 */
			if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
				return;
			}

			/**
			 * Get the post_type_object
			 */
			$post_type_object = get_post_type_object( $post_type );

			/**
			 * Loop over the field groups for this post type
			 */
			foreach ( $field_groups as $field_group ) {
				$this->add_field_group_fields( $field_group, $post_type_object->graphql_single_name );
			}

		}

	}

	protected function add_field_group_fields( $field_group, $add_to_type ) {

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
		$acf_fields = ! empty( $field_group['sub_fields'] ) ? $field_group['sub_fields'] : acf_get_fields( $field_group['ID'] );

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
			$name            = ! empty( $acf_field['name'] ) ? self::camelCase( $acf_field['name'] ) : null;
			$type            = ! empty( $acf_field['type'] ) ? $this->acf_field_type_to_graphql_type( $acf_field['type'], $acf_field ) : null;
			$show_in_graphql = isset( $acf_field['show_in_graphql'] ) && true !== (bool) $acf_field['show_in_graphql'] ? false : true;
			$description     = isset( $acf_field['instructions'] ) ? $acf_field['instructions'] : __( 'ACF Field added to the Schema by WPGraphQL ACF' );

			/**
			 * If the field is missing a name or a type,
			 * we can't add it to the Schema.
			 */
			if (
				empty( $name ) ||
				empty( $type ) ||
				true !== $show_in_graphql
			) {
				/**
				 * Uncomment line below to determine what fields are not going to be output
				 * in the Schema.
				 */
				// var_dump( $acf_field );
				continue;
			}

			/**
			 * Register the GraphQL Field to the Schema
			 */
			register_graphql_field(
				$add_to_type,
				$name,
				[
					'type'            => $type,
					'description'     => $description,
					'acf_field'       => $acf_field,
					'acf_field_group' => $field_group,
					'resolve'         => function( $root, $args, $context, $info ) use ( $acf_field ) {

						$value = null;
						if ( is_array( $root ) ) {
							if ( isset( $root[ $acf_field['key'] ] ) ) {
								$value = $root[ $acf_field['key'] ];
							}
						} else {
							$field_value = get_field( $acf_field['key'], $root->ID, false );
							$value       = ! empty( $field_value ) ? $field_value : null;
						}

						switch ( $acf_field['type'] ) {
							case 'date_picker':
							case 'time_picker':
							case 'date_time_picker':
								return isset( $root->ID ) ? get_field( $acf_field['key'], $root->ID, true ) : null;
							case 'user':
								return DataSource::resolve_user( (int) $value, $context );
							case 'taxonomy':
								$terms = [];
								if ( ! empty( $value ) && is_array( $value ) ) {
									foreach ( $value as $term ) {
										$terms[] = DataSource::resolve_term_object( (int) $term, $context );
									}
								}
								return $terms;
							case 'relationship':
								$relationship = [];
								if ( ! empty( $value ) && is_array( $value ) ) {
									foreach ( $value as $post_id ) {
										$relationship[] = DataSource::resolve_post_object( (int) $post_id, $context );
									}
								}

								return isset( $value ) ? $relationship : null;
							case 'page_link':
							case 'post_object':
								if ( $value instanceof \WP_Post ) {
									$return = new Post( $value );
								} else {
									$return = DataSource::resolve_post_object( (int) $value, $context );
								}
								break;
							case  'link':
								return isset( $value['url'] ) ? $value['url'] : null;
							case 'image':
							case 'file':
								$return = DataSource::resolve_post_object( (int) $value, $context );
								break;
							case 'checkbox':
								if ( is_array( $value ) ) {
									$return = $value;
								} else {
									$return = null;
								}
								break;
							case 'gallery':
								$gallery = [];
								if ( ! empty( $value ) && is_array( $value ) ) {
									foreach ( $value as $image ) {
										$gallery[] = DataSource::resolve_post_object( (int) $image, $context );
									}
								}

								return isset( $value ) ? $gallery : null;
								break;
							default:
								$return = $value;
						}

						return $return;


					}
				]
			);

		}

	}

	protected function add_acf_fields_to_term_objects() {

	}

	protected function add_acf_fields_to_comments() {

	}

	protected function add_acf_fields_to_menu_items() {

	}

	protected function add_acf_fields_to_media_items() {

	}

	/**
	 * Add ACF Fields to GraphQL Types
	 */
	protected function add_acf_fields_to_graphql_types() {


		return;

		foreach ( $this->field_groups as $field_group ) {


			/**
			 * Iterate over the location rules to determine where the fields should be added to the
			 * GraphQL Schema
			 */
			foreach ( $field_group['location'] as $location_rules ) {
				foreach ( $location_rules as $rule ) {

					if ( isset( $rule['param'] ) && '==' === $rule['operator'] ) {

						switch ( $rule['param'] ) {
							case 'post_type':

						}

						$context      = base64_decode( $rule['value'] );
						$decoded      = json_decode( $context, true );
						$wp_type      = isset( $decoded['wp_type'] ) ? $decoded['wp_type'] : null;
						$graphql_type = isset( $decoded['graphql_type'] ) ? $decoded['graphql_type'] : null;
						if ( ! empty( $wp_type ) && ! empty( $graphql_type ) ) {
							register_graphql_fields( $graphql_type, [
								'acfField' => [
									'type' => 'String',
								],
							] );
						}
					}
				}
			}

		}

	}

	protected function add_acf_fields_to_post_object_type( $field_group, $post_type ) {

	}
}
