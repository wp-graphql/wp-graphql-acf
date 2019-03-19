<?php
namespace WPGraphQL\ACF\Type;

register_graphql_object_type( 'ACF_FieldWrapper', [
	'description' => __( 'ACF Field Wrapper', 'wp-graphql' ),
	'fields' => [
		'width' => [
			'type' => 'Integer',
			'description' => __( 'The width of the field (percentage)', 'wp-graphql-acf' ),
			'resolve' => function ( $wrapper ) {
				return ! empty( $wrapper['width'] ) ? absint( $wrapper['width'] ) : null;
			},
		],
		'class' => [
			'type' => 'String',
			'description' => __( 'The class to apply to the field', 'wp-graphql-acf' ),
		],
		'id' => [
			'type' => 'String',
			'description' => __( 'The ID to apply to the field', 'wp-graphql' ),
		],
	],
]);