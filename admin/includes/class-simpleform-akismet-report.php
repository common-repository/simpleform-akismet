<?php
/**
 * Main file for sending rectification reports to Akismet.
 *
 * @package    SimpleForm Akismet
 * @subpackage SimpleForm Akismet/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the rectification reports.
 */
class SimpleForm_Akismet_Report {

	/**
	 * Class constructor.
	 *
	 * @since  1.2.0
	 */
	public function __construct() {

		// Send to Akismet a false negative report.
		add_filter( 'akismet_submit_spam', array( $this, 'submit_false_negative' ), 10, 4 );
		// Send to Akismet a false positive report.
		add_filter( 'akismet_submit_ham', array( $this, 'submit_false_positive' ), 10, 4 );
	}

	/**
	 * Send to Akismet a positive report.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $msg            The value to filter.
	 * @param int[]  $entry          The ID of the entry.
	 * @param string $current_status The current status of the entry.
	 * @param string $status         The new status of the entry.
	 *
	 * @return string The message to show.
	 */
	public function submit_false_negative( $msg, $entry, $current_status, $status ) {

		$msg = '';

		if ( SimpleForm_Akismet::is_akismet_protection() ) {

			if ( 'trash' !== $current_status && 'spam' === $status ) {

				if ( ! is_array( $entry ) ) {

					$this->submit_report( $entry, 'submit-spam' );

				} else {

					foreach ( $entry as $entry_id ) {

						$this->submit_report( $entry_id, 'submit-spam' );

					}
				}

				$msg = '&nbsp;' . __( '(False negative report sent to Akismet)', 'simpleform-contact-form-submissions' );

			}
		}

		return $msg;
	}

	/**
	 * Send to Akismet a false report.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $msg            The value to filter.
	 * @param int[]  $entry          The ID of the entry.
	 * @param string $current_status The current status of the entry.
	 * @param string $status         The new status of the entry.
	 *
	 * @return string The message to show.
	 */
	public function submit_false_positive( $msg, $entry, $current_status, $status ) {

		$msg    = '';
		$report = false;

		if ( SimpleForm_Akismet::is_akismet_protection() ) {

			if ( 'spam' === $current_status && 'trash' !== $status ) {

				if ( ! is_array( $entry ) ) {

					$report = $this->submit_report( $entry, 'submit-ham' );

				} else {

					foreach ( $entry as $entry_id ) {

						$report = $this->submit_report( $entry_id, 'submit-ham' );

					}
				}

				$msg = $report ? '&nbsp;' . __( '(False positive report sent to Akismet)', 'simpleform-contact-form-submissions' ) : '';

			}
		}

		return $msg;
	}

	/**
	 * Report a false negative or a false positive.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $entry_id    The ID of the entry.
	 * @param string $report_type The type of report to submit.
	 *
	 * @return bool True, if report is sent. False otherwise.
	 */
	protected function submit_report( $entry_id, $report_type ) {

		$util   = new SimpleForm_Akismet_Util();
		$data   = maybe_unserialize( strval( $util->entry_value( $entry_id, 'spam_parameters' ) ) );
		$report = false;

		if ( is_array( $data ) && ! empty( $data ) ) {

			$query                         = array();
			$query['user_ip']              = strval( $util->entry_value( $entry_id, 'user_ip' ) );
			$query['user_agent']           = strval( $util->entry_value( $entry_id, 'user_agent' ) );
			$query['referrer']             = strval( $util->entry_value( $entry_id, 'referrer' ) );
			$query['blog']                 = strval( $util->entry_value( $entry_id, 'blog' ) );
			$query['blog_lang']            = strval( $util->entry_value( $entry_id, 'blog_lang' ) );
			$query['blog_charset']         = strval( $util->entry_value( $entry_id, 'blog_charset' ) );
			$query['permalink']            = strval( $util->entry_value( $entry_id, 'permalink' ) );
			$query['comment_type']         = 'contact-form';
			$query['comment_author']       = strval( $util->entry_value( $entry_id, 'comment_author' ) );
			$query['comment_author_email'] = strval( $util->entry_value( $entry_id, 'comment_author_email' ) );
			$query['comment_content']      = strval( $util->entry_value( $entry_id, 'object' ) );

			$query_string = build_query( $query );

			if ( class_exists( 'Akismet' ) ) {

				if ( function_exists( 'http_post' ) ) {

					$response = Akismet::http_post( $query_string, $report_type );

					if ( isset( $response[1] ) && 'Thanks for making the web a better place.' === $response[1] ) {
						$report = true;
					}
				}
			}
		}

		return $report;
	}
}
