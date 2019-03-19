<?php
namespace WPGraphQL\ACF\Type;

use function WPGraphQL\Extensions\ACF\register_graphql_acf_field;

register_graphql_acf_field( 'ACF_Accordion_Field', [
	'description' => __( 'An accordion field', 'wp-graphql-acf' ),
	'fields' => [
		'open' => [
			'type' => 'Boolean',
		],
		'multiExpand' => [
			'type' => 'Boolean',
			'resolve' => function( $field ) {
				return $field['multi_expand'];
			},
		],
		'endpoint' => [
			'type' => 'Boolean',
		],
	]
] );