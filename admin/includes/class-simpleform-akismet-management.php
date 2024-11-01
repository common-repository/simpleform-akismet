<?php
/**
 * File delegated to manage the plugin.
 *
 * @package    SimpleForm Akismet
 * @subpackage SimpleForm Akismet/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the plugin management.
 */
class SimpleForm_Akismet_Management {

	/**
	 * Add message in the plugin meta row if core plugin is missing
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $plugin_meta Array of the plugin's metadata.
	 * @param string   $file        Path to the plugin file relative to the plugins directory.
	 *
	 * @return string[] Array of the plugin's metadata.
	 */
	public function plugin_meta( $plugin_meta, $file ) {

		if ( ! file_exists( WP_PLUGIN_DIR . '/simpleform/simpleform.php' ) && strpos( $file, 'simpleform-akismet/simpleform-akismet.php' ) !== false ) {

			$plugin_url    = __( 'https://wordpress.org/plugins/simpleform/' );
			$message       = '<a href="' . esc_url( $plugin_url ) . '" target="_blank" style="color: orangered !important;">' . __( 'Install the SimpleForm plugin to allow this addon to work', 'simpleform-akismet' ) . '</a>';
			$plugin_meta[] = $message;

		}

		return $plugin_meta;
	}

	/**
	 * Fallback for database table updating if plugin is already active.
	 *
	 * @since 1.0.0
	 *
	 * @return void.
	 */
	public function version_checking() {

		$current_db_version = SIMPLEFORM_AKISMET_DB_VERSION;
		$installed_version  = get_option( 'sform_aks_db_version' );

		require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/class-simpleform-akismet-activator.php';

		if ( $installed_version !== $current_db_version ) {
			SimpleForm_Akismet_Activator::change_db();
			SimpleForm_Akismet_Activator::sform_settings();
		}
	}

	/**
	 * Add action links in the plugin meta row
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $plugin_actions Array of plugin action links.
	 * @param string   $plugin_file    Path to the plugin file relative to the plugins directory.
	 *
	 * @return string[] Array of plugin action links.
	 */
	public function plugin_links( $plugin_actions, $plugin_file ) {

		$new_actions = array();

		if ( 'simpleform-akismet/simpleform-akismet.php' === $plugin_file ) {

			$new_actions['sform_settings'] = '<a href="' . menu_page_url( 'sform-settings', false ) . '">' . __( 'Settings', 'simpleform' ) . '</a>';

		}

		return array_merge( $new_actions, $plugin_actions );
	}

	/**
	 * Add support links in the plugin meta row
	 *
	 * @since 1.2.0
	 *
	 * @param string[] $plugin_meta Array of the plugin's metadata.
	 * @param string   $file        Path to the plugin file relative to the plugins directory.
	 *
	 * @return string[] Array of the plugin's metadata.
	 */
	public function support_link( $plugin_meta, $file ) {

		if ( strpos( $file, 'simpleform-akismet/simpleform-akismet.php' ) !== false ) {

			$plugin_meta[] = '<a href="https://wordpress.org/support/plugin/simpleform-akismet/" target="_blank">' . __( 'Support', 'simpleform-akismet' ) . '</a>';

		}

		return $plugin_meta;
	}

	/**
	 * When user is on a SimpleForm related admin page, display footer text.
	 *
	 * @since 1.2.0
	 *
	 * @param string $text The current text that is displayed.
	 *
	 * @return string The text to be displayed.
	 */
	public function admin_footer( $text ) {

		$util          = new SimpleForm_Akismet_Util();
		$admin_notices = $util->get_sform_option( 1, 'settings', 'admin_notices', false );

		if ( ! $admin_notices && ! class_exists( 'SimpleForm_Submissions' ) && ! class_exists( 'SimpleForm_reCaptcha' ) ) {

			global $current_screen;
			global $wpdb;

			$count_all = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}sform_submissions" ); // phpcs:ignore

			if ( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'sform' ) !== false && $count_all > 0 ) {

				$plugin = '<strong>SimpleForm</strong>';
				$url1   = '<a href="https://wordpress.org/support/plugin/simpleform/reviews/?filter=5#new-post" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a>';
				$url2   = '<a href="https://wordpress.org/support/plugin/simpleform/reviews/?filter=5#new-post" target="_blank" rel="noopener noreferrer">WordPress.org</a>';
				$url3   = '<a href="https://wordpress.org/support/plugin/simpleform/" target="_blank" rel="noopener noreferrer">Forum</a>';
				/* translators: $1$s: SimpleForm plugin name; $2$s: WordPress.org review link; $3$s: WordPress.org review link; $4$s: WordPress.org support forum link. */
				$text = '<span id="footer-thankyou">' . sprintf( __( 'Please support the further development of %1$s by leaving us a %2$s rating on %3$s. Found an issue or have a feature suggestion, please tell on %4$s. Thanks in advance!', 'simpleform-akismet' ), $plugin, $url1, $url2, $url3 ) . '</span>';

			}
		}

		return $text;
	}

	/**
	 * Add an update notice for SimpleForm.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed[] $plugin_data An array of plugin metadata.
	 * @param object  $new_data    An object of metadata about the available plugin update.
	 *
	 * @return void.
	 */
	public function upgrade_notification( $plugin_data, $new_data ) {

		if ( isset( $plugin_data['update'] ) && $plugin_data['update'] && isset( $new_data->upgrade_notice ) && file_exists( WP_PLUGIN_DIR . '/simpleform/simpleform.php' ) ) {

			$simpleform_data = get_plugin_data( WP_PLUGIN_DIR . '/simpleform/simpleform.php' );
			$version         = '<b>' . SIMPLEFORM_VERSION_REQUIRED . '</b>';
			/* translators: %s: The required SimpleForm version. */
			$message = sprintf( __( 'The new version requires SimpleForm version %s or greater installed. Please also update SimpleForm to make it work properly!', 'simpleform-akismet' ), $version );

			// Check if current version of SimpleForm plugin is obsolete.
			if ( version_compare( $simpleform_data['Version'], SIMPLEFORM_VERSION_REQUIRED, '<' ) ) {
				echo '<br><span style="margin-left:26px"><b>' . esc_html__( 'Upgrade Notice', 'simpleform-akismet' ) . ':</b> ' . wp_kses_post( $message ) . '</span>';
			}
		}
	}
}
