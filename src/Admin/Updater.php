<?php
/**
 * Uses Semantic Versioning to determine whether the plugin should be updated.
 *
 * @package WPGraphQL\ACF\Admin
 */

namespace WPGraphQL\ACF\Admin;

use const WPGraphQL\ACF\WPGRAPHQL_ACF_VERSION;

/**
 * Class - Updater
 */
class Updater {
	const PLUGIN_SLUG          = 'wp-graphql-acf';
	const VERSION_TRANSIENT    = self::PLUGIN_SLUG . '_latest_version';
	const WPORG_DATA_TRANSIENT = self::PLUGIN_SLUG . '_wporg_data';

	/**
	 * The version for the update.
	 *
	 * @var string
	 */
	protected $new_version = '';

	/**
	 * The plugin config used by the installer.
	 *
	 * @var array
	 */
	protected $plugin_config;

	/**
	 * The Plugin data from wordpress.org
	 *
	 * @var array
	 */
	protected $wporg_data;

	/**
	 * The class constructor.
	 */
	public function __construct() {
		// Defining this in the constructor makes things easier to mock/test
		$this->plugin_config = [
			'plugin_file'        => WPGRAPHQL_ACF_PLUGIN_FILE,
			'slug'               => self::PLUGIN_SLUG,
			'proper_folder_name' => self::PLUGIN_SLUG,
			'api_url'            => 'https://api.wordpress.org/plugins/info/1.0/' . self::PLUGIN_SLUG . '.json',
			'repo_url'           => 'https://wordpress.org/plugins/' . self::PLUGIN_SLUG,
		];
	}

	/**
	 * Sets up the hooks.
	 */
	public function init() : void {
		add_filter( 'auto_update_plugin', [ $this, 'disable_autoupdate' ], 10, 2 );
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'api_check' ] );
		add_filter( 'plugins_api_result', [ $this, 'api_result' ], 10, 3 );
		add_filter( 'upgrader_source_selection', [ $this, 'upgrader_source_selection' ], 10, 2 );
	}

	/**
	 * Disable auto updates for major releases of this plugin.
	 *
	 * @param bool|null $update Whether to update.
	 * @param object    $item   The plugin object.
	 *
	 * @return bool|null
	 */
	public function disable_autoupdate( $update, $item ) {
		// Return early if this is not our plugin.
		if ( $item->slug !== $this->plugin_config['slug'] && $item->plugin !== $this->plugin_config['slug'] .'/' .$this->plugin_config['slug'] .'.php') {
			return $update;
		}

		// Bail if there's no new version.
		if ( empty( $item->new_version ) ) {
			return $update;
		}

		// Get the update type.
		$update_type = self::get_semver_update_type( $item->new_version, WPGRAPHQL_ACF_VERSION );

		// Non-'major' updates are allowed.
		if ( 'major' !== $update_type ) {
			return $update;
		}

		// Major updates should never happen automatically.
		return false;
	}

	/**
	 * Hooks into the plugin upgrader to get the correct plugin version we want to install.
	 *
	 * @param object $transient The plugin upgrader transient.
	 *
	 * @return object
	 */
	public function api_check( $transient ) {
		// Clear the transient.
		delete_site_transient( self::VERSION_TRANSIENT );

		// Get the latest version we allow to be installed from this version.
		// In the new codebase, we'll just do this on semver-autoupdates.
		$plugin_data = $this->get_plugin_data();
		$version     = $plugin_data['Version'];
		$new_version = $this->get_latest_version();

		// Check if this is a version update.
		$is_update = version_compare( $new_version, $version, '>' );

		if ( ! $is_update ) {
			return $transient;
		}

		// Get the download URL for reuse.
		$download_url = $this->get_download_url( $new_version );

		// Populate the transient data.
		if ( ! isset( $transient->response[ WPGRAPHQL_ACF_PLUGIN_FILE ] ) ) {
			$transient->response[ WPGRAPHQL_ACF_PLUGIN_FILE ] = (object) $this->plugin_config;
		}

		$transient->response[ WPGRAPHQL_ACF_PLUGIN_FILE ]->new_version = $new_version;
		$transient->response[ WPGRAPHQL_ACF_PLUGIN_FILE ]->package     = $download_url;
		$transient->response[ WPGRAPHQL_ACF_PLUGIN_FILE ]->zip_url     = $download_url;


		return $transient;
	}

	/**
	 * Filters the Installation API response result
	 *
	 * @param object|\WP_Error $response The API response object.
	 * @param string           $action The type of information being requested from the Plugin Installation API.
	 * @param object           $args Plugin API arguments.
	 *
	 * @return object|\WP_Error
	 */
	public function api_result( $response, $action, $args ) {
		// Bail if this is not checking our plugin.
		if ( ! isset( $args->slug ) || $args->slug !== $this->plugin_config['slug'] ) {
			return $response;
		}

		// Get the latest version.
		$new_version = $this->get_latest_version();

		// Bail if the version is not newer.
		if ( version_compare( $new_version, $args->version, '<=' ) ) {
			return $response;
		}

		// If we're returning a different version than the latest from WP.org, override the response.
		$response->version       = $new_version;
		$response->download_link = $this->get_download_url( $new_version );

		// If this is a major update, add a warning.
		$update_type = self::get_semver_update_type( $new_version, WPGRAPHQL_ACF_VERSION );
		$warning     = '';

		if ( 'major' === $update_type ) {
			$warning = sprintf(
				/* translators: %s: version number. */
				__( '<h1><span>&#9888;</span>%s</h1>', 'wp-graphql-acf' ),
				self::get_breaking_change_message( $new_version )
			);
		}

		// If there is a warning, append it to each section.
		if ( '' !== $warning ) {
			foreach ( $response->sections as $key => $section ) {
				$response->sections[ $key ] = $warning . $section;
			}
		}

		return $response;
	}

	/**
	 * Rename the downloaded zip
	 *
	 * @param string $source        File source location.
	 * @param string $remote_source Remote file source location.
	 *
	 * @return string|\WP_Error
	 */
	public function upgrader_source_selection( $source, $remote_source ) {
		global $wp_filesystem;

		if ( strstr( $source, '/wp-graphql-acf' ) ) {
			$corrected_source = trailingslashit( $remote_source ) . trailingslashit( $this->plugin_config['proper_folder_name'] );

			if ( $wp_filesystem->move( $source, $corrected_source, true ) ) {
				return $corrected_source;
			} else {
				return new \WP_Error();
			}
		}

		return $source;
	}

	/**
	 * Returns the notice to display inline on the plugins page.
	 *
	 * @param string $upgrade_type The type of upgrade.
	 * @param string $message      The message to display.
	 */
	public function get_inline_notice( string $upgrade_type, string $message, ) : string {
		ob_start();
		?>
			<div class="wpgraphql_acf_plugin_upgrade_notice extensions_warning <?php echo esc_attr( $upgrade_type ); ?>">
				<p><?php echo wp_kses_post( $message ); ?></p>
			</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Returns the notice modal to displace when trying to upgrade.
	 *
	 * @param string $upgrade_type The type of upgrade.
	 * @param string $message      The message to display.
	 */
	public function get_modal_notice( string $upgrade_type, string $message ) : string {
		ob_start();
		?>
		<div id="wpgraphql_acf_plugin_upgrade_modal">
			<div class="wpgraphql_acf_plugin_upgrade_modal--content">
				<h1><?php esc_html_e( "Are you sure you're ready to upgrade?", 'wp-graphql-acf' ); ?></h1>
				<div class="wpgraphql_acf_plugin_upgra extensions_warning">
					<div class="wpgraphql_acf_plugin_upgrade_notice extensions_warning <?php echo esc_attr( $upgrade_type ); ?>">
						<p><?php echo wp_kses_post( $message ); ?></p>
					</div>

					<?php if ( current_user_can( 'update_plugins' ) ) : ?>
						<div class="actions">
							<a href="#" class="button button-secondary cancel"><?php esc_html_e( 'Cancel', 'wp-graphql-acf' ); ?></a>
							<a class="button button-primary accept" href="#"><?php esc_html_e( 'Update now', 'wp-graphql-acf' ); ?></a>
						</div>
					<?php endif ?>
				</div>
			</div>
		</div>

		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Gets the plugin data from the headers.
	 *
	 * @return array
	 */
	protected function get_plugin_data() : array {
		return get_plugin_data( WPGRAPHQL_ACF_PLUGIN_FILE );
	}

	/**
	 * Gets the latest (installable) version of the plugin.
	 *
	 * On versions below 1.0.0, we only allow updates up to 1.0.0.
	 */
	protected function get_latest_version() : string {
		$latest_version = get_site_transient( self::VERSION_TRANSIENT );

		if ( empty( $latest_version ) ) {
			$data = $this->get_wporg_data();

			/** @var string $latest_version */
			$latest_version = $data['version'] ?? '';

			/** @var array<string,string> $versions */
			$versions = $data['versions'] ?? [];


			foreach ( $versions as $version => $download_url ) {
				// Skip trunk.
				if ( 'trunk' === $version ) {
					continue;
				}

				// If the current version is < 1.0.0, but this version is >= 2, skip it.
				// @phpstan-ignore-next-line
				if ( version_compare( WPGRAPHQL_ACF_VERSION, '1.0.0', '<' ) && version_compare( $version, '2.0.0', '>=' ) ) {
					continue;
				}
				
				// Return the first matching version.
				$latest_version = $version;
			}

			if ( ! empty( $latest_version ) ) {
				set_site_transient( self::VERSION_TRANSIENT, $latest_version, 6 * HOUR_IN_SECONDS );
			}
		}

		return $latest_version;
	}

	/**
	 * Gets the data from the WordPress plugin directory.
	 *
	 * @return array<string,mixed>
	 */
	protected function get_wporg_data() : array {
		// Return cached data if available.
		if ( ! empty( $this->wporg_data ) ) {
			return $this->wporg_data;
		}

		// Get data from transient.
		$data = get_site_transient( self::WPORG_DATA_TRANSIENT );

		if ( empty( $data ) || ! is_array( $data ) ) {
			$data = wp_remote_get( $this->plugin_config['api_url'] ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get

			$body = wp_remote_retrieve_body( $data );

			// Bail because we couldn't parse the body.
			if ( empty( $body ) ) {
				return [];
			}

			$data = json_decode( $body, true );

			// Bail because we couldn't decode the body.
			if ( empty( $data ) || ! is_array( $data ) ) {
				return [];
			}

			// Refresh every 6 hours.
			set_site_transient( self::WPORG_DATA_TRANSIENT, $data, 6 * HOUR_IN_SECONDS );
		}

		// Stash for reuse.
		$this->wporg_data = $data;

		return $data;
	}

	/**
	 * Gets the download URL for a specific version.
	 *
	 * @param string $version Version number.
	 *
	 * @return string|false
	 */
	protected function get_download_url( string $version ) {
		$data = $this->get_wporg_data();

		if ( empty( $data['versions'][ $version ] ) ) {
			return false;
		}

		return $data['versions'][ $version ];
	}

	/**
	 * Returns the SemVer type of update based on the new version.
	 *
	 * @param string $new_version The SemVer-compliant version number (x.y.z)
	 * @param string $current_version The SemVer-compliant version number (x.y.z)
	 * 
	 * @return string{major|minor|patch} The type of update (major, minor, patch).
	 */
	protected static function get_semver_update_type( string $new_version, string $current_version ) : string {
		$current = explode( '.', $current_version );
		$new     = explode( '.', $new_version );

		// If the first digit is 0, we need to compare the next digit.
		if ( '0' === $new[0] && '0' !== $current[0] ) {
			return self::get_semver_update_type(
				implode( '.', array_slice( $new, 1 ) ),
				implode( '.', array_slice( $current, 1 ) )
			);
		}

		// If the major version is different, this is a major update.
		if ( $current[0] !== $new[0] ) {
			return 'major';
		}

		// If the minor version is different, this is a minor update.
		if ( $current[1] !== $new[1] ) {
			return 'minor';
		}

		return 'patch';
	}

	/**
	 * Gets the message to display for a breaking change.
	 *
	 * @param string $version The version number.
	 */
	protected static function get_breaking_change_message( string $version ) : string {
		return sprintf(
			/* translators: %s: version number. */
			__( 'Version %s of WPGraphQL for ACF is a major update and may contain breaking changes. Please review the changelog and test before updating on a production site.', 'wp-graphql-acf' ),
			$version
		);
	}

}
