<?php
namespace WPGraphQL\ACF\Types\ObjectType;

/**
 * Class AcfLink
 *
 * @package WPGraphQL\ACF\Types\ObjectType
 */
class AcfLink {

	/**
	 * Register the AcfLink Type to the Schema for use with the
	 * ACF `link` field type
	 *
	 * @return void
	 */
	public static function register_type() {
		register_graphql_object_type( 'AcfLink', [
			'description' => __( 'ACF Link field', 'wp-graphql-acf' ),
			'fields'      => [
				'url'    => [
					'type'        => 'String',
					'description' => __( 'The url of the link', 'wp-graphql-acf' ),
				],
				'title'  => [
					'type'        => 'String',
					'description' => __( 'The title of the link', 'wp-graphql-acf' ),
				],
				'target' => [
					'type'        => 'String',
					'description' => __( 'The target of the link (_blank, etc)', 'wp-graphql-acf' ),
				],
			],
		] );
	}

}
