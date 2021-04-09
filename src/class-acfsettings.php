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
		// add_action( 'acf/render_field_group_settings', [ $this, 'add_field_group_settings' ], 10, 1 );

		/**
		 * Add settings to individual fields to allow each field granular control
		 * over how it's shown in the GraphQL Schema
		 */
		add_action( 'acf/render_field_settings', [ $this, 'add_field_settings' ], 10, 1 );

		/**
		 * Enqueue scripts to enhance the UI of the ACF Field Group Settings
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_graphql_acf_scripts' ], 10, 1 );

		/**
		 * Register meta boxes for the ACF Field Group Settings
		 */
		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );

		/**
		 * Register an AJAX callback
		 */
		add_action( 'wp_ajax_get_acf_field_group_graphql_types', [ $this, 'ajax_callback' ] );

	}

	public function ajax_callback() {

		if ( isset( $_POST['data' ] ) ) {

			$form_data = [];

			parse_str( $_POST['data'], $form_data );

			if ( empty( $form_data ) || ! isset( $form_data['acf_field_group'] ) ) {
				wp_send_json( __( 'No form data.', 'wp-graphql-acf' ) );
			}

			$field_group = isset( $form_data['acf_field_group'] ) ? $form_data['acf_field_group'] : [];
			$rules = new LocationRules( [ $field_group ] );
			$rules->determine_location_rules();
			wp_send_json( $rules->get_rules() );
		}

		echo __( 'No location rules were found', 'wp-graphql-acf' );
		wp_die();
	}

	public function register_meta_boxes() {
		add_meta_box( 'wpgraphql-acf-meta-box', __( 'GraphQL', 'wp-graphql-acf' ), [ $this, 'display_metabox' ], [ 'acf-field-group' ] );
	}

	public function display_metabox( $field_group_post_object ) {

		global $field_group;

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
		 * Render a field in the Field Group settings to set the GraphQL field name for the field group.
		 */
		acf_render_field_wrap(
			[
				'label'        => __( 'GraphQL Field Name', 'acf' ),
				'instructions' => __( 'The name of the field group in the GraphQL Schema. Names should not include spaces or special characters. Best practice is to use "camelCase".', 'wp-graphql-acf' ),
				'type'         => 'text',
				'prefix'       => 'acf_field_group',
				'name'         => 'graphql_field_name',
				'required'     => isset( $field_group['show_in_graphql'] ) ? (bool) $field_group['show_in_graphql'] : false,
				'placeholder'  => ! empty( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : null,
				'value'        => ! empty( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : null,
			]
		);

		acf_render_field_wrap(
			[
				'label'        => __( 'Use ACF Location Rules to map to the GraphQL Schema', 'acf' ),
				'instructions' => __( 'By default, the ACF Field group will be added to the GraphQL Schema based on the field group\'s location rules. Turning this off will allow you to manually select which GraphQL Types the Field Group should show on in the GraphQL Schema.', 'wp-graphql-acf' ),
				'type'         => 'true_false',
				'name'         => 'manual_type_selection',
				'prefix'       => 'acf_field_group',
				'value'        => isset( $field_group['manual_type_selection'] ) ? (bool) $field_group['manual_type_selection'] : true,
				'ui'           => 1,
			]
		);

		$choices = Config::get_all_graphql_types();
		acf_render_field_wrap(
			[
				'label'        => __( 'GraphQL Types to Show the Field Group On', 'wp-graphql-acf' ),
				'instructions' => __( 'Select the Types in the WPGraphQL Schema to show the fields in this field group on', 'wp-graphql-acf' ),
				'type'         => 'checkbox',
				'prefix'       => 'acf_field_group',
				'name'         => 'graphql_types',
				'value'        => ! empty( $field_group['graphql_types'] ) ? $field_group['graphql_types'] : null,
				'toggle'       => true,
				'choices'      => $choices,
			]
		);

		?>
		<div class="acf-hidden">
			<input type="hidden" name="acf_field_group[key]" value="<?php echo $field_group['key']; ?>" />
		</div>
		<script type="text/javascript">
			if( typeof acf !== 'undefined' ) {
				acf.newPostbox({
					'id': 'wpgraphql-acf-meta-box',
					'label': 'left'
				});
			}
		</script>
		<?php

	}

	/**
	 * Add settings to each field to show in GraphQL
	 *
	 * @param array $field The field to add the setting to.
	 */
	public function add_field_settings( array $field ) {

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
				'value'         => isset( $field['show_in_graphql'] ) ? (bool) $field['show_in_graphql'] : true,
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
	public function add_field_group_settings( array $field_group ) {

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
				'required'     => isset( $field_group['show_in_graphql'] ) ? (bool) $field_group['show_in_graphql'] : false,
				'placeholder'  => ! empty( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : null,
				'value'        => ! empty( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : null,
			]
		);

		$choices = Config::get_all_graphql_types();
		acf_render_field_wrap(
			[
				'label'        => __( 'GraphQL Types to Show the Field Group On', 'wp-graphql-acf' ),
				'instructions' => __( 'Select the Types in the WPGraphQl Schema to show the fields in this field group on', 'wp-graphql-acf' ),
				'type'         => 'checkbox',
				'prefix'       => 'acf_field_group',
				'name'         => 'graphql_types',
				'value'        => ! empty( $field_group['graphql_types'] ) ? $field_group['graphql_types'] : null,
				'toggle'       => true,
				'choices'      => $choices,
			]
		);
	}

	/**
	 * This enqueues admin script.
	 */
	public function enqueue_graphql_acf_scripts( $hook ) {
		global $post;

		if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
			if ( 'acf-field-group' === $post->post_type ) {
				wp_enqueue_script( 'graphql-acf', plugins_url( 'src/js/main.js', dirname( __FILE__ ) ), array( 'jquery', 'acf-input', 'acf-field-group' ) );
			}
		}
	}

}
