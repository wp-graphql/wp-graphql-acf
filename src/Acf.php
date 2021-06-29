<?php
/**
 * ACF
 *
 * @package wp-graphql-acf
 */

namespace WPGraphQL\ACF;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Final class Acf
 */
final class Acf {

	/**
	 * Stores the instance of the WPGraphQL\Acf class
	 *
	 * @var Acf The one true WPGraphQL\Acf
	 * @access private
	 */
	private static $instance;

	/**
	 * Get the singleton.
	 *
	 * @return Acf
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Acf ) ) {
			self::$instance = new Acf();
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->actions();
			self::$instance->filters();
			self::$instance->init();
		}

		/**
		 * Fire off init action
		 *
		 * @param Acf $instance The instance of the WPGraphQL\Acf class
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

		// Plugin Folder Path.
		if ( ! defined( 'WPGRAPHQL_ACF_PLUGIN_DIR' ) ) {
			define( 'WPGRAPHQL_ACF_PLUGIN_DIR', plugin_dir_path( __FILE__ . '/..' ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'WPGRAPHQL_ACF_PLUGIN_URL' ) ) {
			define( 'WPGRAPHQL_ACF_PLUGIN_URL', plugin_dir_url( __FILE__ . '/..' ) );
		}

		// Plugin Root File.
		if ( ! defined( 'WPGRAPHQL_ACF_PLUGIN_FILE' ) ) {
			define( 'WPGRAPHQL_ACF_PLUGIN_FILE', __FILE__ . '/..' );
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

		// Autoload Required Classes.
	}

	/**
	 * Sets up actions to run at certain spots throughout WordPress and the WPGraphQL execution
	 * cycle
	 *
	 * @return void
	 */
	private function actions() {

	}

	/**
	 * Setup filters
	 *
	 * @return void
	 */
	private function filters() {

		/**
		 * This filters any field that returns the `ContentTemplate` type
		 * to pass the source node down to the template for added context
		 */
		add_filter( 'graphql_resolve_field', function( $result, $source, $args, $context, ResolveInfo $info, $type_name, $field_key, $field, $field_resolver ) {
			if ( isset( $info->returnType ) && strtolower( 'ContentTemplate' ) === strtolower( $info->returnType ) ) {
				if ( is_array( $result ) && ! isset( $result['node'] ) && ! empty( $source ) ) {
					$result['node'] = $source;
				}
			}
			return $result;
		}, 10, 9 );

	}

	/**
	 * Initialize
	 *
	 * @return void
	 */
	private function init() {

		$registry = new Registry();
		add_action( 'graphql_register_types', [ $registry, 'init' ], 10, 1 );

		$acf_settings = new AcfSettings();
		$acf_settings->init();

	}

}
