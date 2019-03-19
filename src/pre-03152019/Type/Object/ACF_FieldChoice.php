<?php
namespace WPGraphQL\ACF\Type;

register_graphql_object_type( 'ACF_FieldChoice', [
	'description' => __( 'Choice for an ACF Field', 'wp-graphql-acf' ),
	'fields' => [
		'key' => [
			'type' => 'String',
			'description' => __( 'The key for the field choice', 'wp-graphql-acf' ),
		],
		'value' => [
			'type' => 'String',
			'description' => __( 'The value for the field choice', 'wp-graphql-acf' ),
		],
	]
] );