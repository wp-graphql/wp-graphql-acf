<?php
/**
 * Plugin Name:       WPGraphQL for Advanced Custom Fields
 * Plugin URI:        https://wpgraphql.com/acf
 * Description:       Adds Advanced Custom Fields to the WPGraphQL Schema
 * Author:            WPGraphQL, Jason Bahl
 * Author URI:        https://www.wpgraphql.com
 * Text Domain:       wp-graphql-acf
 * Domain Path:       /languages
 * Version:           0.6.0
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
const WPGRAPHQL_ACF_VERSION = '0.6.0';

/**
 * Initialize the plugin
 *
 * @return mixed|Acf|void
 */
function init() {

	/**
	 * If either Acf or WPGraphQL are not active, show the admin notice and bail
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
	return Acf::instance();
}

add_action( 'init', '\WPGraphQL\Acf\init' );


/**
 * Show admin notice to admins if this plugin is active but either ACF and/or WPGraphQL
 * are not active
 *
 * @return void
 */
function show_admin_notice() {

	/**
	 * For users with lower capabilities, don't show the notice
	 */
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
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
 * Check whether Acf and WPGraphQL are active, and whether the minimum version requirement has been
 * met
 *
 * @return bool
 * @since 0.3
 */
function can_load_plugin() {
	// Is Acf active?
	if ( ! class_exists( 'Acf' ) ) {
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
	// @phpstan-ignore-next-line
	if ( true === version_compare( WPGRAPHQL_VERSION, WPGRAPHQL_REQUIRED_MIN_VERSION, 'lt' ) ) {
		return false;
	}

	return true;
}
