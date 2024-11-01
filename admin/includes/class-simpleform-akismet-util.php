<?php
/**
 * File delegated to list the most commonly used functions.
 *
 * @package    SimpleForm Akismet
 * @subpackage SimpleForm Akismet/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the the general utilities class.
 */
class SimpleForm_Akismet_Util {

	/**
	 * Retrieve the option value.
	 *
	 * @since 1.2.0
	 *
	 * @param int                      $form_id The ID of the form.
	 * @param string                   $type    The type of the option.
	 * @param string                   $key     The key of the option.
	 * @param bool|string|int|string[] $preset  The default value to return if the option does not exist.
	 *
	 * @return mixed The value to return.
	 */
	public function get_sform_option( $form_id, $type, $key, $preset ) {

		if ( 1 === (int) $form_id ) {
			$option = (array) get_option( 'sform_' . $type );
		} else {
			$option = false !== get_option( 'sform_' . $form_id . '_' . $type ) ? (array) get_option( 'sform_' . $form_id . '_' . $type ) : (array) get_option( 'sform_' . $type );
		}

		if ( $key ) {
			if ( isset( $option[ $key ] ) ) {
				if ( is_bool( $option[ $key ] ) ) {
					$value = $option[ $key ] ? true : false;
				} else {
					$value = ! empty( $option[ $key ] ) ? $option[ $key ] : $preset;
				}
			} else {
				$value = $preset;
			}
		} else {
			$value = $option;
		}

		return $value;
	}

	/**
	 * Sanitize form data
	 *
	 * @since 1.2.0
	 *
	 * @param string $field The ID of input field.
	 * @param string $type  The type of input field.
	 *
	 * @return mixed The sanitized value.
	 */
	public function sanitized_input( $field, $type ) {

		if ( isset( $_POST[ $field ] ) && isset( $_POST['simpleform_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['simpleform_nonce'] ), 'simpleform_backend_update' ) ) {

			$sanitized_value = array(
				'form'     => absint( $_POST[ $field ] ),
				'tickbox'  => true,
				'text'     => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'response' => sanitize_key( $_POST[ $field ] ),
				'mark'     => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),

			);

			$value = $sanitized_value[ $type ];

		} else {

			$default_value = array(
				'form'     => 1,
				'tickbox'  => false,
				'text'     => '',
				'response' => 'blocked',
				'mark'     => '***' . __( 'SPAM', 'simpleform-akismet' ) . '***',
			);

			$value = $default_value[ $type ];

		}

		return $value;
	}

	/**
	 * Retrieve the entry value.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $entry_id The ID of the entry.
	 * @param string $type     The type of data.
	 *
	 * @return mixed The entry value to return.
	 */
	public function entry_value( $entry_id, $type ) {

		$entry_data = wp_cache_get( 'entry_data_' . $entry_id );

		// Do a database query and save it to the cache if the there is no cache data with this key.
		if ( false === $entry_data ) {
			global $wpdb;
			$entry_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}sform_submissions WHERE id = %d", $entry_id ) ); // phpcs:ignore.
			wp_cache_set( 'entry_data_' . $entry_id, $entry_data );
		}

		$spam_parameters = array(
			'user_ip',
			'user_agent',
			'referrer',
			'blog',
			'blog_lang',
			'blog_charset',
			'permalink',
			'comment_type',
			'comment_author',
			'comment_author_email',
			'comment_content',
		);

		if ( isset( $entry_data->spam_parameters ) && ! empty( $entry_data->spam_parameters ) && in_array( $type, $spam_parameters, true ) ) {

			$deserilized_value = (object) maybe_unserialize( strval( $entry_data->spam_parameters ) );
			$entry_value       = isset( $deserilized_value->$type ) ? $deserilized_value->$type : '';

		} else {

			$entry_value = isset( $entry_data->$type ) ? $entry_data->$type : '';

		}

		return $entry_value;
	}
}
