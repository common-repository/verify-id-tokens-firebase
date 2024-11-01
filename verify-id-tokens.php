<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://bengal-studio.com/
 * @since             1.0.0
 * @package           Bengal_Studio_Verify_Id_Tokens
 *
 * @wordpress-plugin
 * Plugin Name:       Verify ID Tokens | Firebase
 * Plugin URI:        http://wordpress.org/plugins/verify-id-tokens/
 * Description:       A plugin to work with Firebase tokens.
 * Version:           1.0.0
 * Author:            Mithun Biswas
 * Author URI:        http://bengal-studio.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       verify-id-tokens
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BENGAL_STUDIO_VERIFY_ID_TOKENS_VERSION', '1.0.0' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bengal-studio-verify-id-tokens.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function bengal_studio_run_verify_id_tokens() {

	$plugin = new Bengal_Studio_Verify_Id_Tokens();
	$plugin->run();

}
bengal_studio_run_verify_id_tokens();
