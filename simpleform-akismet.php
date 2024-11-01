<?php
/**
 *
 * Plugin Name:       SimpleForm Akismet
 * Description:       Do you get junk emails through your form? This SimpleForm addon helps prevent spam submission. To work properly you need an Akismet API Key.
 * Version:           1.2.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            SimpleForm Team
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simpleform-akismet
 * Requires Plugins:  simpleform
 *
 * @package           SimpleForm Akismet
 */

defined( 'WPINC' ) || exit;

/**
 * Plugin constants.
 *
 * @since 1.0.0
 */

define( 'SIMPLEFORM_AKISMET_NAME', 'SimpleForm Akismet' );
define( 'SIMPLEFORM_AKISMET_VERSION', '1.2.0' );
define( 'SIMPLEFORM_AKISMET_DB_VERSION', '1.2.0' );
define( 'SIMPLEFORM_AKISMET_BASENAME', plugin_basename( __FILE__ ) );
define( 'SIMPLEFORM_AKISMET_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'SIMPLEFORM_VERSION_REQUIRED' ) ) {
	define( 'SIMPLEFORM_VERSION_REQUIRED', '2.2.0' );
}

/**
 * The code that runs during plugin activation.
 *
 * @since 1.0.0
 *
 * @param bool $network_wide Whether to enable the plugin for all sites in the network
 *                           or just the current site. Multisite only. Default false.
 *
 * @return void
 */
function activate_simpleform_akismet( $network_wide ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpleform-akismet-activator.php';
	SimpleForm_Akismet_Activator::activate( $network_wide );
}

/**
 * Edit settings when a new site into a network is created.
 *
 * @since 1.0.0
 *
 * @param WP_Site $new_site New site object.
 *
 * @return void
 */
function simpleform_akismet_network( $new_site ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpleform-akismet-activator.php';
	SimpleForm_Akismet_Activator::on_create_blog( $new_site );
}

add_action( 'wp_insert_site', 'simpleform_akismet_network' );

/**
 * The code that runs during plugin deactivation.
 *
 * @since 1.0.0
 *
 * @return void
 */
function deactivate_simpleform_akismet() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpleform-akismet-deactivator.php';
	SimpleForm_Akismet_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_simpleform_akismet' );
register_deactivation_hook( __FILE__, 'deactivate_simpleform_akismet' );

/**
 * The core plugin class.
 *
 * @since 1.0.0
 */

require plugin_dir_path( __FILE__ ) . '/includes/class-simpleform-akismet.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 *
 * @return void
 */
function run_simpleform_akismet() {

	$plugin = new SimpleForm_Akismet();
	$plugin->run();
}

run_simpleform_akismet();
