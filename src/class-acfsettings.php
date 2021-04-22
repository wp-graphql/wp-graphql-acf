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
		 * Register an AJAX action and callback for converting ACF Location rules to GraphQL Types
		 */
		add_action( 'wp_ajax_get_acf_field_group_graphql_types', [ $this, 'ajax_callback' ] );

	}

	/**
	 * Handle the AJAX callback for converting ACF Location settings to GraphQL Types
	 *
	 * @return void
	 */
	public function ajax_callback() {

		if ( isset( $_POST['data'] ) ) {

			$form_data = [];

			parse_str( $_POST['data'], $form_data );

			if ( empty( $form_data ) || ! isset( $form_data['acf_field_group'] ) ) {
				wp_send_json( __( 'No form data.', 'wp-graphql-acf' ) );
			}

			$field_group = isset( $form_data['acf_field_group'] ) ? $form_data['acf_field_group'] : [];
			$rules       = new LocationRules( [ $field_group ] );
			$rules->determine_location_rules();

			$group_name = isset( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : $field_group['title'];
			$group_name = $rules->format_field_name( $group_name );

			$all_rules = $rules->get_rules();
			if ( isset( $all_rules[ $group_name ] ) ) {
				wp_send_json( [ 'graphql_types' => array_values( $all_rules[ $group_name ] ) ] );
			}
			wp_send_json( [ 'graphql_types' => null ] );
		}

		echo __( 'No location rules were found', 'wp-graphql-acf' );
		wp_die();
	}

	/**
	 * Register the GraphQL Settings metabox for the ACF Field Group post type
	 *
	 * @return void
	 */
	public function register_meta_boxes() {
		add_meta_box( 'wpgraphql-acf-meta-box', __( 'GraphQL', 'wp-graphql-acf' ), [
			$this,
			'display_metabox'
		], [ 'acf-field-group' ] );
	}

	/**
	 * Display the GraphQL Settings Metabox on the Field Group admin page
	 *
	 * @param $field_group_post_object
	 */
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
				'label'        => __( 'Manually Set GraphQL Types for Field Group', 'acf' ),
				'instructions' => __( 'By default, ACF Field groups are added to the GraphQL Schema based on the field group\'s location rules. Checking this box will let you manually control the GraphQL Types the field group should be shown on in the GraphQL Schema using the checkboxes below, and the Location Rules will no longer effect the GraphQL Types.', 'wp-graphql-acf' ),
				'type'         => 'true_false',
				'name'         => 'map_graphql_types_from_location_rules',
				'prefix'       => 'acf_field_group',
				'value'        => isset( $field_group['map_graphql_types_from_location_rules'] ) ? (bool) $field_group['map_graphql_types_from_location_rules'] : false,
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
				'value'        => ! empty( $field_group['graphql_types'] ) ? $field_group['graphql_types'] : [],
				'toggle'       => true,
				'choices'      => $choices,
			]
		);

		?>
		<div class="acf-hidden">
			<input type="hidden" name="acf_field_group[key]"
				   value="<?php echo $field_group['key']; ?>"/>
		</div>
		<script type="text/javascript">
			if (typeof acf !== 'undefined') {
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
	 * This enqueues admin script.
	 *
	 * @param string $screen The screen that scripts are being enqueued to
	 *
	 * @return void
	 */
	public function enqueue_graphql_acf_scripts( string $screen ) {
		global $post;

		if ( $screen == 'post-new.php' || $screen == 'post.php' ) {
			if ( 'acf-field-group' === $post->post_type ) {
				wp_enqueue_script( 'graphql-acf', plugins_url( 'src/js/main.js', dirname( __FILE__ ) ), array(
					'jquery',
					'acf-input',
					'acf-field-group'
				) );
			}
		}
	}

}
