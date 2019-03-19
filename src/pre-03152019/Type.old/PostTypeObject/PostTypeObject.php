<?php
namespace WPGraphQL\Extensions\ACF\Type\PostTypeObject;

class PostTypeObject {

	public static function filter_fields( $fields ) {

		$acf_fields = acf_get_fields( $field_group['ID'] );

		if ( ! empty( $acf_fields ) && is_array( $acf_fields ) ) {

			foreach ( $acf_fields as $acf_field ) {

				if ( ! empty( $acf_field['graphql_label'] ) ) {

					$type = (array) acf_get_field_type( $acf_field['type'] );
					$type['graphql_label'] = self::_graphql_label( $type['name'] );

					$fields[ $acf_field['graphql_label'] ] = [
						'type'        => Types::field_type( $type ),
						'description' => sprintf( __( 'The %1$s field', 'wp-graphql-acf' ), $acf_field['label'] ),
						'resolve'     => function( \WP_Post $post ) use ( $acf_field, $type ) {
							$acf_field['object_id'] = $post->ID;
							return $acf_field;
						},
					];
				}
			}
		}

		return $fields;

	}

}