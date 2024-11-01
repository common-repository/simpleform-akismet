<?php
/**
 * Main file for the admin functionality of the plugin.
 *
 * @package    SimpleForm Akismet
 * @subpackage SimpleForm Akismet/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Core class used to implement the admin-specific functionality of the plugin.
 */
class SimpleForm_Akismet_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the scripts for the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The hook that was called.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {

		global $sform_settings;

		if ( $hook !== $sform_settings ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name . '-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version, false );
	}

	/**
	 * Add the new fields in the settings page.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $extra_option The value to filter.
	 * @param int    $form         The ID of the form.
	 *
	 * @return string The fields added by addon.
	 */
	public function settings_fields( $extra_option, $form ) {

		$util         = new SimpleForm_Akismet_Util();
		$color        = strval( $util->get_sform_option( 1, 'settings', 'admin_color', 'default' ) );
		$akismet      = $util->get_sform_option( 1, 'settings', 'akismet', false );
		$data_storing = $util->get_sform_option( $form, 'settings', 'data_storing', true );

		// Check if Akismet is installed with the corresponding API key.
		$akismet_key    = get_option( 'wordpress_api_key' );
		$akismet_notes  = function_exists( 'akismet_http_post' ) && ! empty( $akismet_key ) ? '&nbsp;' : '<span class="error">' . __( 'You need to activate Akismet plugin and register a valid API Key', 'simpleform-akismet' ) . '</span>';
		$akismet_action = $util->get_sform_option( 1, 'settings', 'akismet_action', 'blocked' );
		$spam_mark      = $util->get_sform_option( 1, 'settings', 'spam_mark', '***' . __( 'SPAM', 'simpleform-akismet' ) . '***' );

		// Akismet options attributes.
		if ( ! file_exists( WP_PLUGIN_DIR . '/simpleform-contact-form-submissions/simpleform-submissions.php' ) ) {
			$notes = '<span class="error">' . __( 'To report false detections to Akismet, SimpleForm Contact Form Submissions must be installed', 'simpleform-akismet' ) . '</span>';
		} elseif ( ! class_exists( 'SimpleForm_Submissions' ) ) {
			$notes = '<span class="error">' . __( 'To report false detections to Akismet, SimpleForm Contact Form Submissions must be activated', 'simpleform-akismet' ) . '</span>';
		} elseif ( ! $data_storing ) {
			$notes = '<span class="error">' . __( 'To report false detections to Akismet, form data storing must be enabled', 'simpleform-akismet' ) . '</span>';
		} else {
			$notes = '&nbsp;';
		}

		if ( ! $akismet ) {
			$option_position = 'last';
			$option_class    = 'unseen';
		} else {
			$option_position = '';
			$option_class    = '';
		}

		if ( 'blocked' === $akismet_action ) {
			$action_notes    = '&nbsp;';
			$action_class    = 'invisible';
			$action_position = 'last';
			$mark_class      = 'unseen';
		} else {
			$action_notes    = $notes;
			$action_class    = '';
			$action_position = '';
			$mark_class      = '';
		}

		if ( 1 !== $form ) {
			$disabled_class  = 'disabled';
			$disabled_option = ' disabled="disabled"';
			$settings_button = '<a href="' . menu_page_url( 'sform-settings', false ) . '"><span class="dashicons dashicons-edit icon-button admin ' . esc_attr( $color ) . '"></span><span class="settings-page wp-core-ui button admin">' . __( 'Go to main settings for edit', 'simpleform' ) . '</span></a>';
		} else {
			$disabled_class  = '';
			$disabled_option = '';
			$settings_button = '';
		}

		// The HTML markup for the Akismet options.

		$extra_option = '<h2 id="h2-akismet" class="options-heading"><span class="heading" data-section="akismet">' . __( 'Akismet Protection', 'simpleform-akismet' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 akismet"></span></span>' . $settings_button . '</h2><div class="section akismet"><table class="form-table akismet"><tbody>';

		$extra_option .= '<tr><th class="option"><span>' . __( 'Akismet Anti-Spam', 'simpleform-akismet' ) . '</span></th><td id="tdakismet" class="checkbox-switch notes ' . $option_position . '"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="akismet" id="akismet" class="sform-switch" value="' . $akismet . '" ' . checked( $akismet, true, false ) . $disabled_option . '><span></span></label><label for="akismet" class="switch-label ' . $disabled_class . '">' . __( 'Enable Akismet Anti-Spam protection', 'simpleform-akismet' ) . '</label></div><p class="description">' . $akismet_notes . '</p></td></tr>';

		$extra_option .= '<tr class="trakismet ' . $option_class . '"><th class="option"><span>' . __( 'Akismet Action Type', 'simpleform-akismet' ) . '</span></th><td id="tdakismetaction" class="radio notes ' . $action_position . '"><fieldset><label for="blocked-message" class="radio ' . $disabled_class . '"><input type="radio" name="akismet_action" id="blocked-message" value="blocked" ' . checked( $akismet_action, 'blocked', false ) . $disabled_option . ' \>' . __( 'Block the message and display a submission error', 'simpleform-akismet' ) . '</label><label for="flagged-message" class="radio ' . $disabled_class . '"><input type="radio" name="akismet_action" id="flagged-message"  value="flagged" ' . checked( $akismet_action, 'flagged', false ) . $disabled_option . ' \>' . __( 'Send the message marked as spam', 'simpleform-akismet' ) . '</label></fieldset><p id="akismet_action_notes" class="description ' . $action_class . '">' . $action_notes . '</p></td></tr>';

		$extra_option .= '<tr class="trakismet trspammark ' . $mark_class . '" ><th class="option last"><span>' . __( 'Spam Mark', 'simpleform-akismet' ) . '</span></th><td class="text last"><input type="text" name="spam_mark" id="spam_mark" class="sform" value="' . $spam_mark . '"  placeholder="' . esc_attr__( 'Enter a word to be included in the subject of the message to mark it as spam', 'simpleform-akismet' ) . '"' . $disabled_option . ' \></td></tr>';

		$extra_option .= '</tbody></table></div>';

		return $extra_option;
	}

	/**
	 * Add error message for Akismet in the settings page.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $extra_option The value to filter.
	 * @param int    $form         The ID of the form.
	 *
	 * @return string The field added by addon.
	 */
	public function validation_message( $extra_option, $form ) {

		$util            = new SimpleForm_Akismet_Util();
		$akismet         = $util->get_sform_option( 1, 'settings', 'akismet', false );
		$error_message   = $util->get_sform_option( 1, 'settings', 'akismet_error', __( 'There was an error trying to send your message. Please try again later!', 'simpleform-akismet' ) );
		$option_class    = ! $akismet ? 'unseen' : '';
		$disabled_option = 1 !== $form ? ' disabled="disabled"' : '';

		$extra_option = '<tr class="trakismet ' . $option_class . '" ><th class="option"><span>' . __( 'Spam Error', 'simpleform-akismet' ) . '</span></th><td class="text"><input type="text" name="akismet_error" id="akismet_error" class="sform" placeholder="' . esc_attr__( 'Please enter an error message to be displayed in case the message is considered spam', 'simpleform-akismet' ) . '" value="' . $error_message . '"' . $disabled_option . ' \></td></tr>';

		return $extra_option;
	}

	/**
	 * Validate the new fields in the settings page.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $error The value to filter.
	 *
	 * @return string The filtered value after the checks.
	 */
	public function settings_validation( $error ) {

		$util        = new SimpleForm_Akismet_Util();
		$akismet     = $util->sanitized_input( 'akismet', 'tickbox' );
		$akismet_key = get_option( 'wordpress_api_key' );

		// Check if Akismet plugin is installed.
		if ( $akismet && ! function_exists( 'akismet_http_post' ) ) {

			$error = __( 'You need to activate Akismet plugin for enabling Akismet Anti-Spam', 'simpleform-akismet' );

		}

		// Check if Akismet API keys exist before saving settings.
		if ( $akismet && function_exists( 'akismet_http_post' ) && empty( $akismet_key ) ) {

			$error = __( 'You need to register Akismet API Key for enabling Akismet Anti-Spam', 'simpleform-akismet' );

		}

		return $error;
	}

	/**
	 * Add the new settings values in the settings options array.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @return mixed[] The fields values added by addon.
	 */
	public function settings_storing() {

		$util = new SimpleForm_Akismet_Util();
		$form = $util->sanitized_input( 'form_id', 'form' );

		if ( 1 === $form ) {

			$akismet        = $util->sanitized_input( 'akismet', 'tickbox' );
			$akismet_action = $util->sanitized_input( 'akismet_action', 'response' );
			$spam_mark      = $util->sanitized_input( 'spam_mark', 'mark' );
			$akismet_error  = $util->sanitized_input( 'akismet_error', 'text' );

			$new_items = array(
				'akismet'        => $akismet,
				'akismet_action' => $akismet_action,
				'spam_mark'      => $spam_mark,
				'akismet_error'  => $akismet_error,
			);

		} else {

			$new_items = array(
				'akismet'        => $util->get_sform_option( 1, 'settings', 'akismet', false ),
				'akismet_action' => $util->get_sform_option( 1, 'settings', 'akismet_action', 'blocked' ),
				'spam_mark'      => $util->get_sform_option( 1, 'settings', 'spam_mark', '***' . __( 'SPAM', 'simpleform-akismet' ) . '***' ),
				'akismet_error'  => $util->get_sform_option( 1, 'settings', 'akismet_error', __( 'There was an error trying to send your message. Please try again later!', 'simpleform-akismet' ) ),
			);

		}

		return $new_items;
	}

	/**
	 * Validate submitted data with Akismet.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string|mixed[] $errors      The value to filter.
	 * @param int            $form_id     The ID of the form.
	 * @param string         $name        The sanitized name value entered in the form.
	 * @param string         $email       The sanitized email value entered in the form.
	 * @param string         $message     The sanitized message value entered in the form.
	 * @param string         $action_type The type of action to be taken.
	 *
	 * @return string|mixed[] The error found after form submission.
	 */
	public function akismet_spam_detection( $errors, $form_id, $name, $email, $message, $action_type ) {

		$util = new SimpleForm_Akismet_Util();

		if ( SimpleForm_Akismet::is_akismet_protection() ) {

			$akismet_action = $util->get_sform_option( 1, 'settings', 'akismet_action', 'blocked' );
			$spam_mark      = $util->get_sform_option( 1, 'settings', 'spam_mark', '***' . __( 'SPAM', 'simpleform-akismet' ) . '***' );
			$akismet_error  = $util->get_sform_option( 1, 'settings', 'akismet_error', __( 'There was an error trying to send your message. Please try again later!', 'simpleform-akismet' ) );

			$args['name']    = $name;
			$args['email']   = $email;
			$args['message'] = $message;

			// Run Akismet spam detection.
			if ( $this->akismet_message_check( $args ) ) {

				if ( 'blocked' === $akismet_action ) {

					// $errors is an array if ajax enabled.
					if ( is_array( $errors ) ) {

						if ( 'block-spam' === $action_type ) {

							$errors['error']       = true;
							$errors['showerror']   = true;
							$errors['field_focus'] = false;
							$errors['notice']      = $akismet_error;

						} else {

							$errors = '';

						}
					} else {

						$errors = 'block-spam' === $action_type ? $form_id . ';spam;' : '';

					}
				} else {

					$errors = 'block-spam' === $action_type ? '' : $spam_mark . ' ';

				}
			}
		}

		return $errors;
	}

	/**
	 * Run Akismet spam detection.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $args Array of submitted data.
	 *
	 * @return bool True, if spam is detected. False otherwise.
	 */
	protected function akismet_message_check( $args ) {

		$spam = false;

		$query['blog']                 = get_option( 'home' );
		$query['user_ip']              = $_SERVER['REMOTE_ADDR']; // phpcs:ignore
		$query['user_agent']           = $_SERVER['HTTP_USER_AGENT']; // phpcs:ignore
		$query['referrer']             = $_SERVER['HTTP_REFERER']; // phpcs:ignore
		$query['permalink']            = false !== get_permalink() ? get_permalink() : '';
		$query['comment_type']         = 'contact-form';
		$query['comment_author']       = $args['name'];
		$query['comment_author_email'] = $args['email'];
		$query['comment_content']      = $args['message'];
		$query['blog_lang']            = get_locale();
		$query['blog_charset']         = get_option( 'blog_charset' );

		$query_string = build_query( $query );

		// Build a query and make a request to Akismet.
		if ( class_exists( 'Akismet' ) ) {

			if ( function_exists( 'http_post' ) ) {

				$response = Akismet::http_post( $query_string, 'comment-check' );

				if ( 'true' === $response[1] ) {
					$spam = true;
				}
			}
		}

		return $spam;
	}

	/**
	 * Display an error when spam is detected and Ajax is not enabled.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string   $error       The value to filter.
	 * @param string[] $error_class The list of errors found.
	 *
	 * @return string The error found.
	 */
	public function error_detection( $error, $error_class ) {

		if ( ! isset( $error_class['duplicate_form'] ) && ! isset( $error_class['form_honeypot'] ) && isset( $error_class['spam'] ) ) {

			$util          = new SimpleForm_Akismet_Util();
			$akismet_error = strval( $util->get_sform_option( 1, 'settings', 'akismet_error', __( 'There was an error trying to send your message. Please try again later!', 'simpleform-akismet' ) ) );
			$error         = $akismet_error;
		}

		return $error;
	}

	/**
	 * Save data used for detection for report false positive or false negative.
	 * Only if data storing is enabled.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string[] $extra_values The value to filter.
	 * @param int      $form_id      The ID of the form.
	 * @param string   $name         The name entered in the form.
	 * @param string   $email        The email entered in the form.
	 *
	 * @return mixed[] The parameters values used for spam detection.
	 */
	public function save_detection_data( $extra_values, $form_id, $name, $email ) {

		$util         = new SimpleForm_Akismet_Util();
		$data_storing = $util->get_sform_option( $form_id, 'settings', 'data_storing', true );

		if ( SimpleForm_Akismet::is_akismet_protection() && $data_storing ) {

			$values = array(
				'user_ip'              => ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				'user_agent'           => ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				'referrer'             => ! empty( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
				'blog'                 => get_option( 'home' ),
				'blog_lang'            => get_locale(),
				'blog_charset'         => get_option( 'blog_charset' ),
				'permalink'            => false !== get_permalink() ? get_permalink() : '',
				'comment_author'       => $name,
				'comment_author_email' => $email,
			);

			$extra_values = array( 'spam_parameters' => maybe_serialize( $values ) );

		}

		return $extra_values;
	}
}
