<?php
/**
 * Plugin Name:     WPGraphQL ACF
 * Plugin URI:      https://github.com/tonimain/wp-graphql-acf
 * Description:     Adds Advanced Custom Fields to the WPGraphQL Schema
 * Author:          WPGraphQL, Jason Bahl
 * Author URI:      https://www.wpgraphql.com
 * Text Domain:     wp-graphql-acf
 * Domain Path:     /languages
 * Version:         0.0.1
 *
 * @package         WPGraphQL_ACF
 */

namespace WPGraphQL\ACF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure Freemius SDK
 */
if ( ! function_exists( 'wga_fs' ) ) {
	// Create a helper function for easy SDK access.
	function wga_fs() {
		global $wga_fs;

		if ( ! isset( $wga_fs ) ) {

			require_once dirname( __FILE__ ) . '/freemius/start.php';

			try {
				$wga_fs = fs_dynamic_init( array(
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
				) );
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

if ( ! class_exists( '\WPGraphQL\ACF' ) ) :

	final class ACF {

		/**
		 * Stores the instance of the WPGraphQL\ACF class
		 *
		 * @var ACF The one true WPGraphQL\Extensions\ACF
		 * @access private
		 */
		private static $instance;

		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof ACF ) ) {
				self::$instance = new ACF();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->actions();
				self::$instance->filters();
				self::$instance->init();
			}

			/**
			 * Fire off init action
			 *
			 * @param ACF $instance The instance of the WPGraphQL\ACF class
			 */
			do_action( 'graphql_acf_init', self::$instance );

			/**
			 * Return the WPGraphQL Instance
			 */
			return self::$instance;
		}

		/**
		 * Throw error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single object
		 * therefore, we don't want the object to be cloned.
		 *
		 * @access public
		 * @return void
		 */
		public function __clone() {

			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'The \WPGraphQL\ACF class should not be cloned.', 'wp-graphql-acf' ), '0.0.1' );

		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {

			// De-serializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the \WPGraphQL\ACF class is not allowed', 'wp-graphql-acf' ), '0.0.1' );

		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @return void
		 */
		private function setup_constants() {

			// Plugin version.
			if ( ! defined( 'WPGRAPHQL_ACF_VERSION' ) ) {
				define( 'WPGRAPHQL_ACF_VERSION', '0.0.1' );
			}

			// Plugin Folder Path.
			if ( ! defined( 'WPGRAPHQL_ACF_PLUGIN_DIR' ) ) {
				define( 'WPGRAPHQL_ACF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'WPGRAPHQL_ACF_PLUGIN_URL' ) ) {
				define( 'WPGRAPHQL_ACF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'WPGRAPHQL_ACF_PLUGIN_FILE' ) ) {
				define( 'WPGRAPHQL_ACF_PLUGIN_FILE', __FILE__ );
			}

		}

		/**
		 * Include required files.
		 * Uses composer's autoload
		 *
		 * @access private
		 * @return void
		 */
		private function includes() {

			// Autoload Required Classes
			require_once( WPGRAPHQL_ACF_PLUGIN_DIR . 'vendor/autoload.php' );

		}

		/**
		 * Sets up actions to run at certain spots throughout WordPress and the WPGraphQL execution cycle
		 */
		private function actions() {

			/**
			 * Creates a field group setting to allow a field group to be
			 * shown in the GraphQL Schema.
			 */
			add_action( 'acf/render_field_group_settings', [
				'\WPGraphQL\ACF\ACFFieldGroupSettings',
				'add_field_group_settings'
			], 10, 1 );

			/**
			 * Add settings to individual fields to allow each field granular control
			 * over how it's shown in the GraphQL Schema
			 */
			add_action( 'acf/render_field_settings', [
				'\WPGraphQL\ACF\ACFFieldSettings',
				'add_field_settings'
			], 10, 1 );

		}

		/**
		 * Setup filters
		 */
		private function filters() {

		}

		/**
		 * Initialize
		 */
		private function init() {

			$config = new Config();
			add_action( 'graphql_register_types', [ $config, 'init' ] );

		}

	}

endif;



function init() {

	if ( ! class_exists( 'acf' ) ) {
		add_action( 'admin_notices', function () {
			?>
			<div class="error notice">
				<p><?php _e( 'Advanced custom fields must be active for wp-graphql-acf to work', 'wp-graphiql-acf' ); ?></p>
			</div>
			<?php
		} );

		return false;
	}

	return ACF::instance();
}

add_action( 'graphql_init', '\WPGraphQL\ACF\init' );

