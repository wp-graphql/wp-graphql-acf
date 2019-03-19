<?php
namespace WPGraphQL\ACF\Type;

register_graphql_object_type( 'ACF_TextArea_Field', [
	'description' => __( 'Text Area field', 'wp-graphql-acf' ),
	'fields' => [
		'id' => [
			'type' => 'ID',
			'resolve' => function( $field ) {
				return $field['ID'];
			},
		],
		'key' => [
			'type' => 'String',
		],
		'label' => [
			'type' => 'String',
		],
		'name' => [
			'type' => 'String',
		],
		'prefix' => [
			'type' => 'String',
		],
		'value' => [
			'type' => 'String',
		],
		'menuOrder' => [
			'type' => 'Integer',
			'resolve' => function( $field ) {
				return $field['menu_order'];
			},
		],
		'instructions' => [
			'type' => 'String',
		],
		'required' => [
			'type' => 'Boolean',
		],
		'class' => [
			'type' => 'String',
		],
//		'conditionalLogic' => [
//			'type' => [
//				'list_of' => 'ACF_ConditionalLogic',
//			],
//		],
		'parent' => [
			'type' => 'ID',
		],
//		'wrapper' => [
//			'type' => 'ACF_FieldWrapper'
//		],
		'defaultValue' => [
			'type' => 'String',
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
				return absint( $field['maxlength'] );
			},
		],
		'rows' => [
			'type' => 'Integer',
		],
		'newLines' => [
			'type' => 'String',
			'resolve' => function( $field ) {
				return $field['new_lines'];
			},
		],
	]
]);