<?php
namespace WPGraphQL\ACF\Type;

use function WPGraphQL\Extensions\ACF\register_graphql_acf_field;

register_graphql_acf_field( 'ACF_Text_Field', [
	'description' => __( 'Text field', 'wp-graphql-acf' ),
	'fields' => [
		'defaultValue' => [
			'type' => 'String',
			'isPrivate' => true,
			'resolve' => function( $field ) {
				return $field['default_value'];
			},
		],
		'placeholder' => [
			'type' => 'String',
		],
		'prepend' => [
			'type' => 'String',
		],
		'append' => [
			'type' => 'String',
		],
		'maxLength' => [
			'type' => 'Integer',
			'resolve' => function( $field ) {
				return absint( $field['max_length'] );
			},
		],
	]
]);
