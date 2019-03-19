<?php

namespace WPGraphQL\ACF;

use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Post;
use WPGraphQL\TypeRegistry;

class Config {

	/**
	 * Config constructor.
	 */
	public function __construct() {

	}

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
		 * Determine conditions where the GraphQL Schema should NOT be shown in GraphQL
		 */
		if (
			( empty( $field_group['active'] ) || true !== $field_group['active'] ) ||
			( empty( $field_group['location'] ) || ! is_array( $field_group['location'] ) )
		) {
			$show = false;
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
	 * @param string $acf_type The type the ACF
	 *
	 * @return mixed string|null
	 */
	protected function acf_field_type_to_graphql_type( $acf_type, $acf_field, $from_type ) {

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
			case 'page_link':
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
			case 'user':
				$graphql_type = 'User';
				break;
			// If a type can't be determined, set as null. No field
			// will be added to the GraphQL Schema if there's no known
			// GraphQL Type to resolve as
			case 'gallery':
				$graphql_type = [ 'list_of' => 'MediaItem' ];
				break;
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
			case 'oembed':
			case 'output':
			case 'radio':
			case 'range':
			case 'select':
			case 'separator':
			case 'tab':
			case 'flexible_content':
			case 'google_map':
			case 'group':
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
				$acf_fields = acf_get_fields( $field_group['ID'] );

				/**
				 * If there are no fields, bail
				 */
				if ( empty( $acf_fields ) || ! is_array( $acf_fields ) ) {
					return;
				}

//				print_r( $acf_fields );

				/**
				 * Loop over the fields and register them to the Schema
				 */
				foreach ( $acf_fields as $acf_field ) {

					/**
					 * Setup data for register_graphql_field
					 */
					$name = ! empty( $acf_field['name'] ) ? self::camelCase( $acf_field['name'] ) : null;

//					if ( isset( $acf_field['graphql_field_name'] ) && ! empty( $acf_field['graphql_field_name'] ) ) {
//						$name = $acf_field['graphql_field_name'];
//					}

//					if ( $acf_field['type'] === 'gallery' ) {
//
//						register_graphql_connection([
//							'fromType' => $post_type_object->graphql_single_name,
//							'toType' => 'MediaItem',
//							'queryClass'       => 'WP_Query',
//							'connectionFields' => [
//								'postTypeInfo' => [
//									'type'        => 'PostType',
//									'description' => __( 'Information about the type of content being queried', 'wp-graphql' ),
//									'resolve'     => function ( $source, array $args, $context, $info ) {
//										$post_type = $source->post_type;
//										$post_type_object = get_post_type_object( $post_type );
//										return DataSource::resolve_post_type( $post_type_object->name );
//									},
//								],
//							],
//							'resolveNode'      => function( $id, $args, $context, $info ) {
//								return DataSource::resolve_post_object( $id, $context );
//							},
//							'fromFieldName'    => self::camelCase( $acf_field['name'] ),
//							'connectionArgs'   => [],
//							'resolve' => function( $root, $args, $context, $info ) use ( $acf_field ) {
//								$value = get_field( $acf_field['name'], $root->ID );
//								var_dump( $value );
//							}
//						]);
//
//						continue;
//					}

					$type            = ! empty( $acf_field['type'] ) ? $this->acf_field_type_to_graphql_type( $acf_field['type'], $acf_field, $post_type_object->graphql_single_name ) : null;
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
						continue;
					}

					/**
					 * Register the GraphQL Field to the Schema
					 */
					register_graphql_field(
						$post_type_object->graphql_single_name,
						$name,
						[
							'type'            => $type,
							'description'     => $description,
							'post_type'       => $post_type_object->name,
							'acf_field'       => $acf_field,
							'acf_field_group' => $field_group,
							'resolve'         => function ( $post, $args, $context, $info ) use ( $acf_field ) {
								$value = get_field( $acf_field['name'], $post->ID );
//								if ( empty( $value ) ) {
//									return null;
//								}

								switch ( $acf_field['type'] ) {
									case 'user':
										var_dump( $value );
									case 'taxonomy':
										$terms = [];
										if ( ! empty( $value ) && is_array( $value ) ) {
											foreach ( $value as $term ) {
												$terms[] = DataSource::resolve_term_object( (int) $term, $acf_field['taxonomy'] );
											}
										}
										return $terms;
									case 'relationship':
										$relationship = [];
										if ( ! empty( $value ) && is_array( $value ) ) {
											foreach ( $value as $post ) {
												$relationship[] = DataSource::resolve_post_object( $post->ID, $context );
											}
										}

										return $relationship;
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
										$return = DataSource::resolve_post_object( (int) $value['ID'], $context );
										break;
									case 'checkbox':
										if ( is_array( $value ) ) {
											$return = $value;
										} else {
											$return = [];
										}
										break;
									case 'gallery':
										$gallery = [];
										if ( ! empty( $value ) && is_array( $value ) ) {
											foreach ( $value as $image ) {
												$gallery[] = DataSource::resolve_post_object( $image['ID'], $context );
											}
										}

										return $gallery;
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
