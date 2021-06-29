<?php
namespace WPGraphQL\ACF\Types\ObjectType;

/**
 * Class AcfGoogleMap
 *
 * @package WPGraphQL\ACF\Types\ObjectType
 */
class AcfGoogleMap {

	/**
	 * Registers the AcfGoogleMap Type to the GraphQL Schema for
	 * use by the `google_map` ACF Field Type
	 *
	 * @return void
	 */
	public static function register_type() {

		register_graphql_object_type( 'AcfGoogleMap', [
			'description' => __( 'A group of fields representing a Google Map', 'wp-graphql-acf' ),
			'fields' => [
				'streetAddress' => [
					'type'        => 'String',
					'description' => __( 'The street address associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['address'] ) ? $root['address'] : null;
					},
				],
				'latitude'      => [
					'type'        => 'Float',
					'description' => __( 'The latitude associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['lat'] ) ? $root['lat'] : null;
					},
				],
				'longitude'     => [
					'type'        => 'Float',
					'description' => __( 'The longitude associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['lng'] ) ? $root['lng'] : null;
					},
				],
				'streetName' => [
					'type'        => 'String',
					'description' => __( 'The street name associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['street_name'] ) ? $root['street_name'] : null;
					},
				],
				'streetNumber' => [
					'type'        => 'String',
					'description' => __( 'The street number associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['street_number'] ) ? $root['street_number'] : null;
					},
				],
				'city' => [
					'type'        => 'String',
					'description' => __( 'The city associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['city'] ) ? $root['city'] : null;
					},
				],
				'state' => [
					'type'        => 'String',
					'description' => __( 'The state associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['state'] ) ? $root['state'] : null;
					},
				],
				'stateShort' => [
					'type'        => 'String',
					'description' => __( 'The state abbreviation associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['state_short'] ) ? $root['state_short'] : null;
					},
				],
				'postCode' => [
					'type'        => 'String',
					'description' => __( 'The post code associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['post_code'] ) ? $root['post_code'] : null;
					},
				],
				'country' => [
					'type'        => 'String',
					'description' => __( 'The country associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['country'] ) ? $root['country'] : null;
					},
				],
				'countryShort' => [
					'type'        => 'String',
					'description' => __( 'The country abbreviation associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['country_short'] ) ? $root['country_short'] : null;
					},
				],
				'placeId' => [
					'type'        => 'String',
					'description' => __( 'The country associated with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['place_id'] ) ? $root['place_id'] : null;
					},
				],
				'zoom' => [
					'type'        => 'String',
					'description' => __( 'The zoom defined with the map', 'wp-graphql-acf' ),
					'resolve'     => function( $root ) {
						return isset( $root['zoom'] ) ? $root['zoom'] : null;
					},
				],
			]
		] );

	}

}
