<?php
/**
 * Plugin Name:       WPGraphQL for Advanced Custom Fields
 * Plugin URI:        https://wpgraphql.com/acf
 * Description:       Adds Advanced Custom Fields to the WPGraphQL Schema
 * Author:            WPGraphQL, Jason Bahl
 * Author URI:        https://www.wpgraphql.com
 * Text Domain:       wp-graphql-acf
 * Domain Path:       /languages
 * Version:           0.5.2
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
const WPGRAPHQL_ACF_VERSION = '0.5.2';

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


