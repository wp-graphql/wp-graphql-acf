<?php
namespace WPGraphQL\Type;

register_graphql_enum_type( 'ACF_LayoutEnum', [
	'description' => __( 'Options for layout', 'wp-graphql' ),
	'values' => [
		'horizontal' => [
			'key' => 'HORIZONTAL',
			'value' => 'horizontal',
		],
		'vertical' => [
			'key' => 'VERTICAL',
			'value' => 'vertical',
		],
	],
]);