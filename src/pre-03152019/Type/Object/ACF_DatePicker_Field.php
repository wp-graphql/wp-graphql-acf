<?php
namespace WPGraphQL\ACF\Type;

use function WPGraphQL\Extensions\ACF\register_graphql_acf_field;

register_graphql_acf_field( 'ACF_DatePicker_Field', [
	'description' => __( 'Datepicker field', 'wp-graphql-acf' ),
	'fields' => [
		'displayFormat' => [
			'type' => 'String',
			'resolve' => function( $field ) {
				return isset( $field['display_format'] ) ? $field['display_format'] : null;
			},
		],
		'returnFormat' => [
			'type' => 'String',
			'resolve' => function( $field ) {
				return isset( $field['return_format'] ) ? $field['return_format'] : null;
			},
		],
		'firstDay' => [
			'type' => 'Int',
			'resolve' => function( $field ) {
				return isset( $field['first_day'] ) ? $field['first_day'] : null;
			},
		],
	],
]);