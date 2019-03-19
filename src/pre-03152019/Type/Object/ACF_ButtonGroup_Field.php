<?php
namespace WPGraphQL\ACF\Type;

use function WPGraphQL\Extensions\ACF\register_graphql_acf_field;

register_graphql_object_type( 'ACF_ConditionalLogic', [
	'description' => __( 'Conditional logic rules applied to the field', 'wp-graphql' ),
	'fields' => [
		'goo' => [
			'type' => 'String',
		],
	],
]);



register_graphql_acf_field( 'ACF_ButtonGroup_Field', [
	'description' => __( 'An accordion field', 'wp-graphql-acf' ),
	'fields' => [
		'choices' => [
			'description' => __( 'List of choices. Each choice has a key and value. Represented as strings.', 'wp-graphql-acf' ),
			'type' => [
				'list_of' => 'ACF_FieldChoice',
			],
			'resolve' => function( $field ) {
				$choices = [];
				if ( ! empty( $field['choices'] ) && is_array( $field['choices'] ) ) {
					foreach ( $field['choices'] as $key => $choice ) {
						$choices[] = [
							'key' => $key,
							'value' => $choice
						];
					}
				}
				return $choices;
			},
		],
		'allowNull' => [
			'type' => 'Boolean',
			'resolve' => function( $field ) {
				return $field['allow_null'];
			},
		],
		'defaultValue' => [
			'type' => 'String',
			'resolve' => function( $field ) {
				return $field['default_value'];
			},
		],
		'layout' => [
			'type' => 'ACF_LayoutEnum',
		],
		'returnFormat' => [
			'type' => 'String',
			'resolve' => function( $field ) {
				return $field['return_format'];
			},
		],
	],
] );
