<?php
namespace WPGraphQL\ACF\Type;

use function WPGraphQL\Extensions\ACF\register_graphql_acf_field;

register_graphql_acf_field( 'ACF_ColorPicker_Field', [
	'description' => __( 'A colorpicker field', 'wp-graphql-acf' ),
	'fields' => [
		'defaultValue' => [
			'type' => 'String',
			'resolve' => function( $field ) {
				return ! empty( $field ) ? $field['default_value'] : null;
			},
		],
	],
]);