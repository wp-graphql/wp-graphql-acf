<?php

namespace WPGraphQL\Extensions\ACF;

use WPGraphQL\Data\DataSource;
use WPGraphQL\Extensions\ACF\Types as ACFTypes;
use WPGraphQL\Extensions\ACF\Utils as ACFUtils;
use WPGraphQL\TypeRegistry;

class Actions {

	/**
	 * This adds the ACF fields to each type, based on ACF Field Group location rules.
	 *
	 * @return mixed
	 */
	public static function acf_add_fields_to_types() {

		/**
		 * Get the registered_field_groups
		 */
		$field_groups = acf_get_field_groups();

		/**
		 * If there are registered $field_groups
		 */
		if ( ! empty( $field_groups ) && is_array( $field_groups ) ) {

			/**
			 * Loop through the field_groups
			 */
			foreach ( $field_groups as $field_group ) {

				/**
				 * Get the field group location rules
				 */
				$locations = $field_group['location'];

				/**
				 * If the field group has location rules defined
				 */
				if ( ! empty( $locations ) && is_array( $locations ) ) {

					/**
					 * Loop through the location rules
					 */
					foreach ( $locations as $location ) {

						/**
						 * Setup the fields for each type, based on the param that's setting them up
						 */
						switch ( $location[0]['param'] ) {

							case 'post_type':
								self::post_object_fields( $location, $field_group );
								break;
							case 'taxonomy':
								self::term_object_fields( $location, $field_group );
								break;
							default:
								break;

						} // End switch().
					}// End foreach().
				}// End if().
			}// End foreach().
		}// End if().
		return;
	}

	protected static function resolve_acf_field( $resolving_object, $acf_field, $type ) {

		$object_id = '';
		if ( $resolving_object instanceof \WP_Post ) {
			$object_id = $resolving_object->ID;
		} elseif ( $resolving_object instanceof \WP_Term ) {
			$object_id = $resolving_object->taxonomy . '_' . $resolving_object->term_id;
		}

		$acf_field['object_id'] = $object_id;
		$get_field              = get_field( $acf_field['key'], $resolving_object->ID, true );
		$acf_field['value']     = $get_field;

		return $acf_field;
	}

	/**
	 *
	 * @param $location
	 * @param $field_group
	 */
	private static function post_object_fields( $location, $field_group ) {

		/**
		 * If the rule is for a post_type that's in the GraphQL allowed_post_types, and
		 */
		if ( '==' === $location[0]['operator'] && in_array( $location[0]['value'], \WPGraphQL::get_allowed_post_types(), true ) ) {

			/**
			 * Get the post_type_object
			 */
			$post_type_object = ! empty( $location[0]['value'] ) ? get_post_type_object( $location[0]['value'] ) : null;

			/**
			 * If the post_type_object has a graphql_single_name defined
			 */
			if ( ! empty( $post_type_object->graphql_single_name ) ) {

				$fields     = [];
				$acf_fields = acf_get_fields( $field_group['ID'] );
				if ( ! empty( $acf_fields ) && is_array( $acf_fields ) ) {
					foreach ( $acf_fields as $acf_field ) {

						if ( isset( $acf_field['type'] ) ) {

							$type = (array) acf_get_field_type( $acf_field['type'] );
							$name = lcfirst( str_replace( ' ', '', ucwords( str_replace( '-', ' ', str_replace( '_', ' ', $acf_field['label'] ) ) ) ) );

							$scalar_type = 'String';

							switch ( $acf_field['type'] ) {
								case 'accordion':
									$scalar_type = null;
									$field_type  = 'ACF_Accordion_Field';
									break;
								case 'button_group':
									$field_type  = 'ACF_ButtonGroup_Field';
									$scalar_type = 'String';
									break;
								case 'checkbox':
									$field_type  = 'ACF_Checkbox_Field';
									$scalar_type = null;
									// Scalar Field
									$fields[ $name ] = [
										'type'        => [
											'list_of' => 'String',
										],
										'description' => __( 'Scalar value of the field', 'wp-graphql-acf' ),
										'resolve'     => function ( $resolving_object ) use ( $acf_field, $type ) {

											$values = [];
											$resolved = self::resolve_acf_field( $resolving_object, $acf_field, $type );
											$checked = ! empty( $resolved['value'] ) && is_array( $resolved['value'] ) ? $resolved['value'] : [];

											foreach ( $checked as $checked ) {
												$values[] = (string) $checked;
											}

											return $values;
										}
									];
									break;
								case 'file':
									$field_type  = 'ACF_File_Field';
									$scalar_type = null;
									// Scalar Field
									$fields[ $name ] = [
										'type'        => 'MediaItem',
										'description' => __( 'Scalar value of the field', 'wp-graphql-acf' ),
										'resolve'     => function ( $resolving_object ) use ( $acf_field, $type ) {

											if ( 'url' === $acf_field['return_format'] ) {
												return null;
											}

											$resolved = get_field( $acf_field['key'], $resolving_object->ID );
											if ( is_array( $resolved ) ) {
												$id = $resolved['id'];
											} else {
												$id = $resolved;
											}

											return isset( $id ) ? DataSource::resolve_post_object( absint( $id ), 'attachment' ) : null;
										}
									];
									break;
								case 'color_picker':
									$scalar_type = 'String';
									$field_type = 'ACF_ColorPicker_Field';
									break;
								case 'date_picker':
									$field_type = 'ACF_DatePicker_Field';
									$scalar_type = 'String';
									break;
								case 'date_time_picker':
									$field_type = 'ACF_DateTimePicker_Field';
									$scalar_type = 'String';
									break;
								case 'email':
									$field_type = 'ACF_Email_Field';
									$scalar_type = 'String';
									break;
//								case 'flexible_content':
//								case 'gallery':
//								case 'google_map':
//								case 'group':
//								case 'image':
//								case 'link':
//								case 'message':
//								case 'number':
//								case 'oembed':
//								case 'output':
//								case 'page_link':
//								case 'password':
//								case 'post_object':
//								case 'radio':
//								case 'range':
//								case 'relationship':
//								case 'select':
//								case 'separator':
//								case 'tab':
//								case 'taxonomy':
//								case 'time_picker':
//								case 'true_false':
//								case 'url':
//								case 'user':
//								case 'wysiwyg':
								case 'textarea':
									$scalar_type = 'String';
									$field_type = 'ACF_TextArea_Field';
									break;
								case 'text':
									$field_type = 'ACF_Text_Field';
									break;
								default:
									$name = null;
									$scalar_type = null;


							}

							if ( ! empty( $scalar_type ) ) {
								$fields[ $name ] = [
									'type'        => $scalar_type,
									'description' => __( 'Scalar value of the field', 'wp-graphql-acf' ),
									'resolve'     => function ( $resolving_object ) use ( $acf_field, $type ) {
										$resolved = self::resolve_acf_field( $resolving_object, $acf_field, $type );

										return $resolved['value'];
									}
								];
							}

							if ( ! empty( $name ) ) {
								$fields[ $name . '_Field' ] = [
									'type'        => $field_type,
									'description' => ! empty( $acf_field['label'] ) ? $acf_field['label'] : __( 'ACF Text Field', 'wp-graphql' ),
									'resolve'     => function ( $resolving_object ) use ( $acf_field, $type ) {
										return self::resolve_acf_field( $resolving_object, $acf_field, $type );
									},
								];
							}
						}
					}
				}

				if ( ! empty( $fields ) ) {
					register_graphql_fields( $post_type_object->graphql_single_name, $fields );
				}

			}
		}

	}

	private static function term_object_fields( $location, $field_group ) {

		if ( '==' === $location[0]['operator'] && in_array( $location[0]['value'], \WPGraphQL::get_allowed_taxonomies(), true ) ) {
			$taxonomy_object = ! empty( $location[0]['value'] ) ? get_taxonomy( $location[0]['value'] ) : null;

			if ( ! empty( $taxonomy_object->graphql_single_name ) ) {


				add_filter( "graphql_{$taxonomy_object->graphql_single_name}_fields", function ( $fields ) use ( $field_group ) {
					return self::filter_object_fields( $fields, $field_group );
				}, 10, 1 );

			}
		}

	}

	public static function filter_object_fields( $fields, $field_group ) {

		/**
		 * Get the fields for the specified field_group
		 */
		$acf_fields = acf_get_fields( $field_group['ID'] );

		/**
		 * Check to see if there are fields defined for the field group
		 */
		if ( ! empty( $acf_fields ) && is_array( $acf_fields ) ) {

			/**
			 * Loop through the configured fields
			 */
			foreach ( $acf_fields as $acf_field ) {

				/**
				 * If the field has a label we can safely add to the schema
				 */
				if ( ! empty( $acf_field['graphql_label'] ) ) {

					/**
					 * Get the field_type
					 */
					$type = (array) acf_get_field_type( $acf_field['type'] );

					/**
					 * Add a "graphql_label" to the field type for use in instantiating the
					 */
					$type['graphql_label'] = ACFUtils::_graphql_label( $type['name'] );

					$fields[ $acf_field['graphql_label'] ] = [
						'type'        => ACFTypes::field_type( $type ),
						// Translators: The placeholder is the type of object (post_type, taxonomy, etc) being filtered
						'description' => sprintf( __( 'The %1$s field', 'wp-graphql-acf' ), $acf_field['label'] ),
						'resolve'     => function ( $resolving_object ) use ( $acf_field, $type ) {

							$object_id = '';
							if ( $resolving_object instanceof \WP_Post ) {
								$object_id = $resolving_object->ID;
							} elseif ( $resolving_object instanceof \WP_Term ) {
								$object_id = $resolving_object->taxonomy . '_' . $resolving_object->term_id;
							}

							$acf_field['object_id'] = $object_id;

							return $acf_field;
						},
					];
				}
			}
		}

		return $fields;

	}

}
