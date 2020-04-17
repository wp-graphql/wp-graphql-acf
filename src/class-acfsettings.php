<?php
/**
 * ACF extension for WP-GraphQL
 *
 * @package wp-graphql-acf
 */

namespace WPGraphQL\ACF;

/**
 * Class ACF_Settings
 *
 * @package WPGraphQL\ACF
 */
class ACF_Settings {

	/**
	 * Initialize ACF Settings for the plugin
	 */
	public function init() {

		/**
		 * Creates a field group setting to allow a field group to be
		 * shown in the GraphQL Schema.
		 */
		add_action( 'acf/render_field_group_settings', [ $this, 'add_field_group_settings' ], 10, 1 );

		/**
		 * Add settings to individual fields to allow each field granular control
		 * over how it's shown in the GraphQL Schema
		 */
		add_action( 'acf/render_field_settings', [ $this, 'add_field_settings' ], 10, 1 );

		add_filter( 'acf/location/rule_values/graphql_type', function( $choices ) {

			$allowed_post_types = \WPGraphQL::get_allowed_post_types();
			if ( ! empty( $allowed_post_types ) && is_array( $allowed_post_types ) ) {
				foreach ( $allowed_post_types as $allowed_post_type ) {
					$post_type_object = get_post_type_object( $allowed_post_type );
					$name = ucfirst( $post_type_object->graphql_single_name );
					$choices[$name] = $name;
				}
			}

			$allowed_taxonomies = \WPGraphQL::get_allowed_taxonomies();
			if ( ! empty( $allowed_taxonomies ) && is_array( $allowed_taxonomies ) ) {
				foreach ( $allowed_taxonomies as $allowed_taxonomy ) {
					$tax_object = get_taxonomy( $allowed_taxonomy );
					$name = ucfirst( $tax_object->graphql_single_name );
					$choices[$name] = $name;
				}
			}

			$choices['MenuItem'] = 'MenuItem';
			$choices['Menu'] = 'Menu';

			$choices['User'] = 'User';
			asort( $choices );
			return $choices;

		} );

		add_filter( 'acf/location/rule_operators', function( $choices, $rule ) {


			if ( $rule['param'] === 'graphql_type' ) {
				$choices = [];
				$choices['='] = 'is equal to';
			}

			return $choices;
		}, 10, 2 );


		add_filter( 'acf/location/rule_types', function( $choices ) {

//			$get_types = graphql([
//				'query' => '
//				{
//				  __schema {
//				    types {
//				      name
//				      kind
//				      description
//				    }
//				  }
//				}
//				'
//			]);
//
//			$types = isset( $get_types['data']['__schema']['types'] ) ? $get_types['data']['__schema']['types'] : null;
//			$types = array_filter( $types, function( $type ) {
//				return $type['kind'] === 'OBJECT' ? $type : null;
//			} );
//
//			if ( ! empty( $types ) && is_array( $types ) ) {
//				var_dump( $types );
//			}


//			$allowed_post_types = \WPGraphQL::get_allowed_post_types();
//			if ( ! empty( $allowed_post_types ) && is_array( $allowed_post_types ) ) {
//				foreach ( $allowed_post_types as $allowed_post_type ) {
//					$post_type_object = get_post_type_object( $allowed_post_type );
//					$name = ucfirst( $post_type_object->graphql_single_name );
//					$choices['GraphQL Schema: Post Types'][$name] = $name;
//				}
//			}
//
//			$allowed_taxonomies = \WPGraphQL::get_allowed_taxonomies();
//			if ( ! empty( $allowed_taxonomies ) && is_array( $allowed_taxonomies ) ) {
//				foreach ( $allowed_taxonomies as $allowed_taxonomy ) {
//					$tax_object = get_taxonomy( $allowed_taxonomy );
//					$name = ucfirst( $tax_object->graphql_single_name );
//					$choices['GraphQL Schema: Taxonomies'][$name] = $name;
//				}
//			}
//
//			$choices['GraphQL Schema: User']['User'] = 'User';
//			$choices['GraphQL Schema: Menu']['MenuItem'] = 'MenuItem';
//			$choices['GraphQL Schema: Menu']['Menu'] = 'Menu';
			$choices['GraphQL Schema']['graphql_type'] = 'GraphQL Type';
			return $choices;
		} );

	}

	/**
	 * Add settings to each field to show in GraphQL
	 *
	 * @param array $field The field to add the setting to.
	 */
	public function add_field_settings( $field ) {

		$supported_fields = Config::get_supported_fields();

		/**
		 * If there are no supported fields, or the field is not supported, don't add a setting field.
		 */
		if ( empty( $supported_fields ) || ! is_array( $supported_fields ) || ! in_array( $field['type'], $supported_fields, true ) ) {
			return;
		}

		/**
		 * Render the "show_in_graphql" setting for the field.
		 */
		acf_render_field_setting(
			$field,
			[
				'label'         => __( 'Show in GraphQL', 'wp-graphql-acf' ),
				'instructions'  => __( 'Whether the field should be queryable via GraphQL', 'wp-graphql-acf' ),
				'name'          => 'show_in_graphql',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 1,
				'value'        => isset( $field['show_in_graphql'] ) ? (bool) $field['show_in_graphql'] : true,
			],
			true
		);

	}

	/**
	 * This adds a setting to the ACF Field groups to activate a field group in GraphQL.
	 *
	 * If a field group is set to active and is set to "show_in_graphql", the fields in the field
	 * group will be exposed to the GraphQL Schema based on the matching location rules.
	 *
	 * @param array $field_group The field group to add settings to.
	 */
	public function add_field_group_settings( $field_group ) {

		/**
		 * Render a field in the Field Group settings to allow for a Field Group to be shown in GraphQL.
		 */
		acf_render_field_wrap(
			[
				'label'        => __( 'Show in GraphQL', 'acf' ),
				'instructions' => __( 'If the field group is active, and this is set to show, the fields in this group will be available in the WPGraphQL Schema based on the respective Location rules.' ),
				'type'         => 'true_false',
				'name'         => 'show_in_graphql',
				'prefix'       => 'acf_field_group',
				'value'        => isset( $field_group['show_in_graphql'] ) ? (bool) $field_group['show_in_graphql'] : false,
				'ui'           => 1,
			]
		);

		/**
		 * Render a field in the Field Group settings to allow for a Field Group to be shown in GraphQL.
		 */
		acf_render_field_wrap(
			[
				'label'        => __( 'GraphQL Field Name', 'acf' ),
				'instructions' => __( 'The name of the field group in the GraphQL Schema.', 'wp-graphql-acf' ),
				'type'         => 'text',
				'prefix'       => 'acf_field_group',
				'name'         => 'graphql_field_name',
				'required'     => true,
				'placeholder'  => ! empty( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : null,
				'value'        => ! empty( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : null,
			]
		);

	}

}
