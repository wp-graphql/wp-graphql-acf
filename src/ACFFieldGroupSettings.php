<?php
namespace WPGraphQL\ACF;

/**
 * Class Settings
 *
 * @package WPGraphQL\ACF
 */
class ACFFieldGroupSettings {

	/**
	 * This adds a setting to the ACF Field groups to activate a field group in GraphQL.
	 *
	 * If a field group is set to active and is set to "show_in_graphql", the fields in the field
	 * group will be exposed to the GraphQL Schema based on the matching location rules.
	 *
	 * @param $field_group
	 */
	public static function add_field_group_settings( $field_group ) {

		/**
		 * Default value for show in GraphQL. If not set, default is false.
		 */
		$value = isset( $field_group['show_in_graphql'] ) ? (bool) $field_group['show_in_graphql'] : false;

		/**
		 * Render a field in the Field Group settings to allow for a Field Group to be shown in GraphQL.
		 */
		acf_render_field_wrap(array(
			'label'			=> __('Show in GraphQL','acf'),
			'instructions'	=> __( 'If the field group is active, and this is set to show, the fields in this group will be available in the WPGraphQL Schema based on the respective Location rules.' ),
			'type'			=> 'true_false',
			'name'			=> 'show_in_graphql',
			'prefix'		=> 'acf_field_group',
			'value'			=> $value,
			'ui'			=> 1,
		));

	}

}

