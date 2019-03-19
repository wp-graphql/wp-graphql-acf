<?php
namespace WPGraphQL\ACF\Interfaces;

use GraphQL\Type\Definition\InterfaceType;

function get_acf_field_interface() {
	return new InterfaceType([
		'name' => __( 'ACF Field', 'wp-graphql-acf' ),
		'description' => __( 'A Field provided by Advanced Custom Fields', 'wp-graphql-acf' ),
		'fields' => [
			'label' => [
				'type' => 'String',
				'description' => __( 'Label for the field.', 'wp-graphql-acf' ),
			],
			'name' => [
				'type' => 'String',
				'descriptipn' => __( 'The name of the field. Single word, no spaces. Underscores and dashes allowed' ),
			],
			'defaultValue' => [
				'type' => 'String',
				'description' => __( 'Default value for this field', 'wp-graphql-acf' ),
			],
			'placeholder' => [
				'type' => 'String',
				'description' => __( 'Appears within input when no value exists', 'wp-graphql-acf' ),
			],
			'isRequired' => [
				'type' => 'Boolean',
				'description' => __( 'Whether the field is required', 'wp-graphql-acf' ),
			],
			'value' => [
				'type' => 'String',
				'description' => __( 'The value of the field', 'wp-graphql-acf' ),
			],
			'instructions' => [
				'type' => 'String',
				'description' => __( 'Instructions for users to interact with the field when submitting data', 'wp-graphql-acf' ),
			],
		],
	]);
}