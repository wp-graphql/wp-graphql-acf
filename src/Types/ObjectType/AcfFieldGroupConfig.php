<?php
namespace WPGraphQL\ACF\Types\ObjectType;

/**
 * Class AcfFieldGroupConfig
 *
 * @package WPGraphQL\ACF\Types\ObjectType
 */
class AcfFieldGroupConfig {

	/**
	 * Registers the AcfFieldGroupConfig Type to expose config settings
	 * for ACF Field Groups
	 *
	 * @return void
	 */
	public static function register_type() {

		register_graphql_object_type( 'AcfFieldGroupConfig', [
			'description' => __( 'Configuration settings of an ACF Field Group.', 'wp-graphql' ),
			'fields' => [
				'databaseId' => [
					'type' => 'Int',
					'resolve' => function( $field_group ) {
						return $field_group['ID'];
					}
				],
				'key' => [
					'type' => 'String',
				],
			],
		] );

	}

}
