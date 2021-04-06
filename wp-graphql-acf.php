<?php
/**
 * Plugin Name:       WPGraphQL for Advanced Custom Fields
 * Plugin URI:        https://wpgraphql.com/acf
 * Description:       Adds Advanced Custom Fields to the WPGraphQL Schema
 * Author:            WPGraphQL, Jason Bahl
 * Author URI:        https://www.wpgraphql.com
 * Text Domain:       wp-graphql-acf
 * Domain Path:       /languages
 * Version:           0.5.0
 * Requires PHP:      7.0
 * GitHub Plugin URI: https://github.com/wp-graphql/wp-graphql-acf
 *
 * @package         WPGraphQL_ACF
 */

namespace WPGraphQL\ACF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( __DIR__ . '/vendor/autoload.php' );

/**
 * Define constants
 */
const WPGRAPHQL_REQUIRED_MIN_VERSION = '0.4.0';
const WPGRAPHQL_ACF_VERSION = '0.5.1';

/**
 * Initialize the plugin
 *
 * @return ACF|void
 */
function init() {

	/**
	 * If either ACF or WPGraphQL are not active, show the admin notice and bail
	 */
	if ( false === can_load_plugin() ) {
		// Show the admin notice
		add_action( 'admin_init', __NAMESPACE__ . '\show_admin_notice' );

		// Bail
		return;
	}

	/**
	 * Return the instance of WPGraphQL\ACF
	 */
	return ACF::instance();
}

add_action( 'init', '\WPGraphQL\ACF\init' );

/**
 * Show admin notice to admins if this plugin is active but either ACF and/or WPGraphQL
 * are not active
 *
 * @return bool
 */
function show_admin_notice() {

	/**
	 * For users with lower capabilities, don't show the notice
	 */
	if ( ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	add_action(
		'admin_notices',
		function() {
			?>
			<div class="error notice">
				<p><?php esc_html_e( sprintf( 'Both WPGraphQL (v%s+) and Advanced Custom Fields (v5.7+) must be active for "wp-graphql-acf" to work', WPGRAPHQL_REQUIRED_MIN_VERSION ), 'wp-graphiql-acf' ); ?></p>
			</div>
			<?php
		}
	);
}


/**
 * Check whether ACF and WPGraphQL are active, and whether the minimum version requirement has been
 * met
 *
 * @return bool
 * @since 0.3
 */
function can_load_plugin() {
	// Is ACF active?
	if ( ! class_exists( 'ACF' ) ) {
		return false;
	}

	// Is WPGraphQL active?
	if ( ! class_exists( 'WPGraphQL' ) ) {
		return false;
	}

	// Do we have a WPGraphQL version to check against?
	if ( empty( defined( 'WPGRAPHQL_VERSION' ) ) ) {
		return false;
	}

	// Have we met the minimum version requirement?
	if ( true === version_compare( WPGRAPHQL_VERSION, WPGRAPHQL_REQUIRED_MIN_VERSION, 'lt' ) ) {
		return false;
	}

	return true;
}

//if( function_exists('acf_add_local_field_group') ):
//
//	acf_add_local_field_group(array(
//		'key' => 'group_606b61869135f',
//		'title' => 'Tag Fields',
//		'fields' => array(
//			array(
//				'key' => 'field_606b618b48b6e',
//				'label' => 'test',
//				'name' => 'test',
//				'type' => 'text',
//				'instructions' => '',
//				'required' => 0,
//				'conditional_logic' => 0,
//				'wrapper' => array(
//					'width' => '',
//					'class' => '',
//					'id' => '',
//				),
//				'show_in_graphql' => 1,
//				'default_value' => '',
//				'placeholder' => '',
//				'prepend' => '',
//				'append' => '',
//				'maxlength' => '',
//			),
//		),
//		'location' => array(
//			array(
//				array(
//					'param' => 'taxonomy',
//					'operator' => '==',
//					'value' => 'post_tag',
//				),
//			),
//		),
//		'menu_order' => 0,
//		'position' => 'normal',
//		'style' => 'default',
//		'label_placement' => 'top',
//		'instruction_placement' => 'label',
//		'hide_on_screen' => '',
//		'active' => true,
//		'description' => '',
//		'show_in_graphql' => 1,
//		'graphql_field_name' => 'exampleTagFields',
//	));
//
//	acf_add_local_field_group(array(
//		'key' => 'group_606b6b8ad82b4',
//		'title' => 'Comment Fields',
//		'fields' => array(
//			array(
//				'key' => 'group_606b6b8ad82b4',
//				'label' => 'test',
//				'name' => 'test',
//				'type' => 'text',
//				'instructions' => '',
//				'required' => 0,
//				'conditional_logic' => 0,
//				'wrapper' => array(
//					'width' => '',
//					'class' => '',
//					'id' => '',
//				),
//				'show_in_graphql' => 1,
//				'default_value' => '',
//				'placeholder' => '',
//				'prepend' => '',
//				'append' => '',
//				'maxlength' => '',
//			),
//		),
//		'location' => array(
//			array(
//				array(
//					'param' => 'comment',
//					'operator' => '==',
//					'value' => 'post',
//				),
//			),
//		),
//		'menu_order' => 0,
//		'position' => 'normal',
//		'style' => 'default',
//		'label_placement' => 'top',
//		'instruction_placement' => 'label',
//		'hide_on_screen' => '',
//		'active' => true,
//		'description' => '',
//		'show_in_graphql' => 1,
//		'graphql_field_name' => 'exampleCommentFields',
//	));
//
//	acf_add_local_field_group(array(
//		'key' => 'group_606b6b8ad82b4_menu',
//		'title' => 'Menu Fields',
//		'fields' => array(
//			array(
//				'key' => 'group_606b6b8ad82b4_menu',
//				'label' => 'test',
//				'name' => 'test',
//				'type' => 'text',
//				'instructions' => '',
//				'required' => 0,
//				'conditional_logic' => 0,
//				'wrapper' => array(
//					'width' => '',
//					'class' => '',
//					'id' => '',
//				),
//				'show_in_graphql' => 1,
//				'default_value' => '',
//				'placeholder' => '',
//				'prepend' => '',
//				'append' => '',
//				'maxlength' => '',
//			),
//		),
//		'location' => array(
//			array(
//				array(
//					'param' => 'nav_menu',
//					'operator' => '==',
//					'value' => 'any',
//				),
//			),
//		),
//		'menu_order' => 0,
//		'position' => 'normal',
//		'style' => 'default',
//		'label_placement' => 'top',
//		'instruction_placement' => 'label',
//		'hide_on_screen' => '',
//		'active' => true,
//		'description' => '',
//		'show_in_graphql' => 1,
//		'graphql_field_name' => 'exampleMenuFields',
//	));
//
//endif;

add_filter( 'theme_page_templates', function( $templates ) {
	$templates['test'] = 'Test Template';
	return $templates;
});
