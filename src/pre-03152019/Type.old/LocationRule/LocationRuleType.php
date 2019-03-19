<?php
namespace WPGraphQL\Extensions\ACF\Type\LocationRule;

use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

class LocationRuleType extends WPObjectType {

	private static $type_name;
	private static $fields;

	public function __construct() {

		self::$type_name = 'locationRule';

		$config = [
			'name' => self::$type_name,
			'description' => __( 'Rule for where ACF Fields are registered to display', 'wp-graphql-acf' ),
			'fields' => self::fields(),
		];

		parent::__construct( $config );

	}

	public function fields() {

		if ( null === self::$fields ) {

			$fields = [
				// @todo: convert to enum
				'param' => [
					'type' => Types::string(),
					'description' => __( 'The admin context the rule applies to', 'wp-graphql-acf' ),
				],
				// @todo: convert to enum
				'operator' => [
					'type' => Types::string(),
					'description' => __( 'The operator used to compare the param and value', 'wp-graphql-acf' ),
				],
				// @todo: convert to enum
				'value' => [
					'type' => Types::string(),
					'description' => __( 'The value the param and operator applies to', 'wp-graphql-acf' ),
				],
			 ];

			self::$fields = self::prepare_fields( $fields, self::$type_name );

		}

		return self::$fields;

	}

}