<?php

namespace WPGraphQL\ACF;

/**
 * Class Settings
 *
 * @package WPGraphQL\ACF
 */
class ACFFieldSettings {

	/**
	 * Add settings to each field to show in GraphQL
	 */
	public static function add_field_settings( $field ) {

		/**
		 * Render the "show_in_graphql" setting for the field.
		 */
		acf_render_field_setting( $field, array(
			'label'         => __( 'Show in GraphQL', 'wp-graphql-acf' ),
			'instructions'  => __( 'Whether the field should be queryable via GraphQL', 'wp-graphql-acf' ),
			'name'          => 'show_in_graphql',
			'type'          => 'true_false',
			'ui'            => 1,
			'default_value' => 1,
		), true );

		/**
		 * Provide setting to override the GraphQL Field name
		 *
		 * @todo: Make this a bit more intentional so folks don't override all willy-nilly and break
		 *      their Schema? Like a popConfirm or something to have them confirm that they
		 *      know what they're changing?
		 */
		acf_render_field_setting( $field, array(
			'label'        => __( 'GraphQL Field Name', 'wp-graphql-acf' ),
			'instructions' => __( 'The field name exposed to the GraphQL Schema. Default is camelCase of the field name. This field will override the default. NOTE: CHANGING THIS CAN CAUSE BREAKING CHANGES TO CONSUMERS OF YOUR GRAPHQL API IF THEY ARE ALREADY CONSUMING THIS FIELD.', 'wp-graphql-acf' ),
			'name'         => 'graphql_field_name',
			'type'         => 'text',
			'ui'           => 1,
		), true );

	}

}
