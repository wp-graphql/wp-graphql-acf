<?php
namespace WPGraphQL\ACF\Type;

use function WPGraphQL\Extensions\ACF\register_graphql_acf_field;

register_graphql_acf_field( 'ACF_Checkbox_Field', [
	'description' => __( 'A checkbox field', 'wp-graphql-acf' ),
	'fields' => [
		'layout' => [
			'type' => 'ACF_LayoutEnum',
		],
		'choices' => [
			'type' => [
				'list_of' => 'ACF_FieldChoice',
			],
			'resolve' => function( $field ) {
				$choices = [];
				foreach ( $field['choices'] as $key => $choice ) {
					$choices[] = [
						'key' => (string) $key,
						'value'=> (string) $choice
					];
				}
				return $choices;
			}
		],
		'value' => [
			'type'        => [
				'list_of' => 'string',
			],
			'description' => __( 'Scalar value of the field', 'wp-graphql-acf' ),
			'resolve'     => function ( $field )  {
				$values = [];
				foreach ( $field['value'] as $checked ) {
					$values[] = (string) $checked;
				}
				return $values;
			}
		],
		'defaultValue' => [
			'type' => [
				'list_of' => 'String',
			],
			'resolve' => function( $field ) {
				$defaults = [];
				if ( ! empty( $field['default_value'] ) && is_array( $field['default_value'] ) ) {
					foreach ( $field['default_value'] as $key => $default_value ) {
						$defaults[$key] = (string) $default_value;
					}
				}
				return $defaults;
			}
		],
		'toggle' => [
			'type' => 'Boolean',
		],
		'returnFormat' => [
			'type' => 'String',
			'resolve' => function ( $field ) {
				return ! empty( $field['return_format'] ) ? $field['return_format'] : null;
			},
		],
		'allowCustom' => [
			'type' => 'Boolean',
			'resolve' => function ( $field ) {
				return ! isset( $field['allow_custom'] ) ? $field['allow_custom'] : false;
			},
		],
		'saveCustom' => [
			'type' => 'Boolean',
			'resolve' => function ( $field ) {
				return ! isset( $field['save_custom'] ) ? $field['save_custom'] : false;
			},
		],
	]
]);