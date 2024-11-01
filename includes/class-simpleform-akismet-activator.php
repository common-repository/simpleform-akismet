<?php
/**
 * File delegated to the plugin activation.
 *
 * @package    SimpleForm Akismet
 * @subpackage SimpleForm Akismet/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class instantiated during the plugin activation.
 */
class SimpleForm_Akismet_Activator {

	/**
	 * Run default functionality during plugin activation.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param bool $network_wide Whether to enable the plugin for all sites in the network
	 *                           or just the current site. Multisite only. Default false.
	 *
	 * @return void
	 */
	public static function activate( $network_wide ) {

		if ( class_exists( 'SimpleForm' ) ) {

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {

				if ( $network_wide ) {

					global $wpdb;
					$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" ); // phpcs:ignore

					foreach ( $blog_ids as $blog_id ) {
						switch_to_blog( $blog_id );
						self::change_db();
						self::sform_settings();
						restore_current_blog();
					}
				} else {
					self::change_db();
					self::sform_settings();
				}
			} else {
				self::change_db();
				self::sform_settings();
			}
		}
	}

	/**
	 * Modifies the database tables.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Added url field.
	 *
	 * @return void
	 */
	public static function change_db() {

		$current_version   = SIMPLEFORM_AKISMET_DB_VERSION;
		$installed_version = strval( get_option( 'sform_aks_db_version' ) );

		if ( $installed_version !== $current_version ) {

			global $wpdb;
			$submissions_table = $wpdb->prefix . 'sform_submissions';
			$charset_collate   = $wpdb->get_charset_collate();

			$sql_submissions = "CREATE TABLE {$submissions_table} (
				id int(11) NOT NULL AUTO_INCREMENT,
				form int(7) NOT NULL DEFAULT '1',
				moved_from int(7) NOT NULL DEFAULT '0',
				requester_type tinytext NOT NULL,
				requester_id int(15) NOT NULL DEFAULT '0',
				name tinytext NOT NULL,
				lastname tinytext NOT NULL,
				email VARCHAR(200) NOT NULL,
				ip VARCHAR(128) NOT NULL,	
				phone VARCHAR(50) NOT NULL,
				url VARCHAR(255) NOT NULL,
				subject tinytext NOT NULL,
				object text NOT NULL,
				date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				status tinytext NOT NULL,
				previous_status varchar(32) NOT NULL default '',
				trash_date datetime NULL,
				spam_parameters VARCHAR(2048) NOT NULL,
				notes text NULL,
				hidden tinyint(1) NOT NULL DEFAULT '0',
				listable tinyint(1) NOT NULL DEFAULT '1',
				PRIMARY KEY  (id)
			) {$charset_collate};";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			dbDelta( $sql_submissions );
			update_option( 'sform_aks_db_version', $current_version );

		}
	}

	/**
	 * Save initial settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function sform_settings() {

		// Detect the parent plugin activation.
		$main_settings = (array) get_option( 'sform_settings', array() );

		$new_settings = array(
			'akismet'        => false,
			'akismet_action' => 'blocked',
			'spam_mark'      => '***' . __( 'SPAM', 'simpleform-akismet' ) . '***',
			'akismet_error'  => __( 'There was an error trying to send your message. Please try again later!', 'simpleform-akismet' ),
		);

		if ( $main_settings ) {

			$settings = array_merge( $main_settings, $new_settings );
			update_option( 'sform_settings', $settings );

		}

		// Check if other forms have been created.
		global $wpdb;
		$shortcodes_table = $wpdb->prefix . 'sform_shortcodes';

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $shortcodes_table ) ) === $shortcodes_table ) { // phpcs:ignore			

			$forms = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE id != '1'" ); // phpcs:ignore

			foreach ( $forms as $form ) {

				$form_settings = (array) get_option( 'sform_' . $form . '_settings', array() );

				if ( $form_settings ) {

					$settings = array_merge( $form_settings, $new_settings );
					update_option( 'sform_' . $form . '_settings', $settings );

				}
			}
		}
	}

	/**
	 * Create a table whenever a new blog is created in a WordPress Multisite installation.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Site $new_site New site object.
	 *
	 * @return void
	 */
	public static function on_create_blog( $new_site ) {

		if ( is_plugin_active_for_network( 'simpleform-akismet/simpleform-akismet.php' ) ) {

			switch_to_blog( (int) $new_site->blog_id );
			self::change_db();
			self::sform_settings();
			restore_current_blog();

		}
	}
}
