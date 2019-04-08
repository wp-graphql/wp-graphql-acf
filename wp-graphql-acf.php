<?php
/**
 * Plugin Name:     WPGraphQL ACF
 * Plugin URI:      https://github.com/tonimain/wp-graphql-acf
 * Description:     Adds Advanced Custom Fields to the WPGraphQL Schema
 * Author:          WPGraphQL, Jason Bahl
 * Author URI:      https://www.wpgraphql.com
 * Text Domain:     wp-graphql-acf
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         WPGraphQL_ACF
 */

namespace WPGraphQL\ACF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( __DIR__ . '/vendor/autoload.php' );

/**
 * Configure Freemius SDK
 */
if ( ! function_exists( 'wga_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 *
	 * @return mixed|WP_Error|freemius
	 */
	function wga_fs() {
		global $wga_fs;

		if ( ! isset( $wga_fs ) ) {

			require_once dirname( __FILE__ ) . '/freemius/start.php';

			try {
				$wga_fs = fs_dynamic_init(
					array(
						'id'               => '3289',
						'slug'             => 'wp-graphql-acf',
						'premium_slug'     => 'wp-graphql-acf',
						'type'             => 'plugin',
						'public_key'       => 'pk_66dc1cb99818841a8fa76276565cd',
						'is_premium'       => true,
						'is_premium_only'  => true,
						'has_addons'       => false,
						'has_paid_plans'   => true,
						'is_org_compliant' => false,
						'menu'             => array(
							'first-path' => 'plugins.php',
							'support'    => false,
						),
					)
				);
			} catch ( \Freemius_Exception $e ) {
				return new \WP_Error( $e->getMessage() );
			}
		}

		return $wga_fs;
	}

	// Init Freemius.
	wga_fs();
	// Signal that SDK was initiated.
	do_action( 'wga_fs_loaded' );
}



/**
 * Initialize the plugin
 *
 * @return ACF
 */
function init() {
	/**
	 * If either ACF or WPGraphQL are not active, show the admin notice
	 */
	if ( ! class_exists( 'acf' ) || ! class_exists( 'WPGraphQL' ) ) {
		add_action( 'admin_init', __NAMESPACE__ . '\show_admin_notice' );

		return null;
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
	if ( ! class_exists( 'acf' ) || ! class_exists( 'WPGraphQL' ) ) {
		/**
		 * For users with lower priveleges, don't show the notice
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		add_action(
			'admin_notices',
			function() {
				?>
			<div class="error notice">
				<p><?php _e( 'Both WPGraphQL (v0.2.3+) and Advanced Custom Fields (v5.7+) must be active for "wp-graphql-acf" to work', 'wp-graphiql-acf' ); ?></p>
			</div>
				<?php
			}
		);

		return false;
	}
}

