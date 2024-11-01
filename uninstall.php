<?php
/**
 * File delegated to the uninstalling the plugin.
 *
 * @package SimpleForm Akismet
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Detect the simpleform plugin installation.
$plugin_file = defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR . '/simpleform/simpleform.php' : '';

if ( file_exists( $plugin_file ) ) {

	global $wpdb;

	if ( ! is_multisite() ) {

		$settings = (array) get_option( 'sform_settings', array() );

		// Detect the parent plugin activation.
		if ( $settings ) {

			if ( isset( $settings['deletion_data'] ) && $settings['deletion_data'] ) {

				$submissions_table = $wpdb->prefix . 'sform_submissions';
				$wpdb->query( "ALTER TABLE {$submissions_table} DROP COLUMN spam_parameters" ); // phpcs:ignore

			}

			$addon_settings = array(
				'akismet'        => $settings['akismet'],
				'akismet_action' => $settings['akismet_action'],
				'spam_mark'      => $settings['spam_mark'],
				'akismet_error'  => $settings['akismet_error'],
			);

			$new_settings = array_diff_key( $settings, $addon_settings );
			update_option( 'sform_settings', $new_settings );

			$shortcodes_table = $wpdb->prefix . 'sform_shortcodes';

			if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $shortcodes_table ) ) === $shortcodes_table ) { // phpcs:ignore			

				$forms = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE id != '1'" ); // phpcs:ignore

				foreach ( $forms as $form ) {

					$form_settings = (array) get_option( 'sform_' . $form . '_settings', array() );

					if ( $form_settings ) {

						$addon_settings = array(
							'akismet'        => $form_settings['akismet'],
							'akismet_action' => $form_settings['akismet_action'],
							'spam_mark'      => $form_settings['spam_mark'],
							'akismet_error'  => $form_settings['akismet_error'],
						);

						$new_form_settings = array_diff_key( $form_settings, $addon_settings );
						update_option( 'sform_' . $form . '_settings', $new_form_settings );

					}
				}
			}
		}

		delete_option( 'sform_aks_db_version' );

	} else {

		$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ); // phpcs:ignore
		$original_blog_id = get_current_blog_id();

		foreach ( $blog_ids as $blogid ) {

			switch_to_blog( $blogid );
			$settings = (array) get_option( 'sform_settings', array() );

			// Detect the parent plugin activation.
			if ( $settings ) {

				if ( isset( $settings['deletion_data'] ) && $settings['deletion_data'] ) {

					$submissions_table = $wpdb->prefix . 'sform_submissions';
					$wpdb->query( "ALTER TABLE {$submissions_table} DROP COLUMN spam_parameters" ); // phpcs:ignore

				}

				$addon_settings = array(
					'akismet'        => $settings['akismet'],
					'akismet_action' => $settings['akismet_action'],
					'spam_mark'      => $settings['spam_mark'],
					'akismet_error'  => $settings['akismet_error'],
				);

				$new_settings = array_diff_key( $settings, $addon_settings );
				update_option( 'sform_settings', $new_settings );

				$shortcodes_table = $wpdb->prefix . 'sform_shortcodes';

				if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $shortcodes_table ) ) === $shortcodes_table ) { // phpcs:ignore			

					$forms = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE id != '1'" ); // phpcs:ignore

					foreach ( $forms as $form ) {

						$form_settings = (array) get_option( 'sform_' . $form . '_settings', array() );

						if ( $form_settings ) {

							$addon_settings = array(
								'akismet'        => $form_settings['akismet'],
								'akismet_action' => $form_settings['akismet_action'],
								'spam_mark'      => $form_settings['spam_mark'],
								'akismet_error'  => $form_settings['akismet_error'],
							);

							$new_form_settings = array_diff_key( $form_settings, $addon_settings );
							update_option( 'sform_' . $form . '_settings', $new_form_settings );

						}
					}
				}
			}

			delete_option( 'sform_aks_db_version' );

		}

		switch_to_blog( $original_blog_id );

	}
} else {

	global $wpdb;
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_submissions' ); // phpcs:ignore
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_shortcodes' ); // phpcs:ignore
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\_%'" ); // phpcs:ignore
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\-%'" ); // phpcs:ignore
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%\_sform\_%'" ); // phpcs:ignore

}
