<?php
/**
 * File delegated to deactivate the plugin.
 *
 * @package    SimpleForm Akismet
 * @subpackage SimpleForm Akismet/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class instantiated during the plugin's deactivation.
 */
class SimpleForm_Akismet_Deactivator {

	/**
	 * Run during plugin deactivation.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Refactoring of code.
	 *
	 * @return void
	 */
	public static function deactivate() {

		// Detect the parent plugin activation.
		$settings = (array) get_option( 'sform_settings', array() );
		global $wpdb;
		$shortcodes_table = $wpdb->prefix . 'sform_shortcodes';

		if ( $settings ) {

			$settings['akismet'] = false;
			update_option( 'sform_settings', $settings );

		}

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $shortcodes_table ) ) === $shortcodes_table ) { // phpcs:ignore			

			// Check if other forms have been created.
			$forms = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE id != '1'" ); // phpcs:ignore

			foreach ( $forms as $form ) {

				$form_settings = (array) get_option( 'sform_' . $form . '_settings', array() );

				if ( $form_settings ) {

					$form_settings['akismet'] = false;
					update_option( 'sform_' . $form . '_settings', $form_settings );

				}
			}
		}
	}
}
