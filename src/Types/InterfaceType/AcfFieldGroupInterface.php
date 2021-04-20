<?php
namespace WPGraphQL\ACF\Types\InterfaceType;

use Exception;
use WPGraphQL\ACF\Registry;

class AcfFieldGroupInterface {

	/**
	 * @param Registry $registry
	 *
	 * @throws Exception
	 */
	public static function register_type( Registry $registry ) {

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

		register_graphql_interface_type('AcfFieldGroup',
			[
				'description' => __( 'A Field Group registered by ACF', 'wp-graphql-acf' ),
				'fields' => [
					'fieldGroupName' => [
						'description' => __( 'The name of the ACF Field Group', 'wp-graphql-acf' ),
						'deprecationReason' => __( 'Deprecated in favor of "_fieldGroupConfig"', 'wp-graphql-acf' ),
						'type' => 'String',
					],
					'_fieldGroupConfig' => [
						'type' => 'AcfFieldGroupConfig',
						'description' => __( 'Configuration settings of an ACF Field Group', 'wp-graphql' ),
					]
				],
				'resolveType' => function( $field_group ) use ( $registry ) {
					if ( ! empty( $field_group['_fieldGroupConfig'] ) ) {
						$field_group = $field_group['_fieldGroupConfig'];
						return $registry->get_field_group_type_name( $field_group );
					}
					return null;
				}
			]
		);
	}
}
