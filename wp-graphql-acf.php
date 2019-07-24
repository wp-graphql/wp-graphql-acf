<?php
/**
 * Plugin Name:     WPGraphQL for Advanced Custom Fields
 * Plugin URI:      https://wpgraphql.com/acf
 * Description:     Adds Advanced Custom Fields to the WPGraphQL Schema
 * Author:          WPGraphQL, Jason Bahl
 * Author URI:      https://www.wpgraphql.com
 * Text Domain:     wp-graphql-acf
 * Domain Path:     /languages
 * Version:         0.2.0
 *
 * @package         WPGraphQL_ACF
 */

namespace WPGraphQL\ACF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( __DIR__ . '/vendor/autoload.php' );

/**
 * Initialize the plugin
 *
 * @return ACF
 */
function init() {
	/**
	 * If either ACF or WPGraphQL are not active, show the admin notice
	 */
	add_action( 'admin_init', __NAMESPACE__ . '\show_admin_notice' );

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

	$wp_graphql_required_min_version = '0.3.2';

	if ( ! class_exists( 'acf' ) || ! class_exists( 'WPGraphQL' ) || ( defined( 'WPGRAPHQL_VERSION' ) && version_compare( WPGRAPHQL_VERSION, $wp_graphql_required_min_version, 'lt' ) ) ) {

		/**
		 * For users with lower capabilities, don't show the notice
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		add_action(
			'admin_notices',
			function() use ( $wp_graphql_required_min_version ) {
				?>
			<div class="error notice">
				<p><?php _e( sprintf('Both WPGraphQL (v%s+) and Advanced Custom Fields (v5.7+) must be active for "wp-graphql-acf" to work', $wp_graphql_required_min_version ), 'wp-graphiql-acf' ); ?></p>
			</div>
				<?php
			}
		);
		return true;
	}
	return false;
}

