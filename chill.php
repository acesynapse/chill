<?php
// If this file is called directly, the teapot refuses to brew coffee.
defined('ABSPATH' && 'WPINC') || die(http_response_code(418));

/**
 *
 * @since             1.0.0
 * @package           Chill
 *
 * @wordpress-plugin
 * Plugin Name:       Chill
 * Plugin URI:        https://www.cclsny.org/snowpage
 * Description:       Chill is the side-along plugin for SnowPage.
 * Version:           1.0.0
 * Author:            Emric Taylor, CCLS (AceSynapse)
 * Author URI:        https://protemstudios.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       chill
 * Domain Path:       /languages
 */

/**
 * Current plugin version.
 */
define( 'CHILL_VERSION', '0.0.5' );

/**
 * The code that runs during plugin activation.
 */
function activate_chill() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-chill-activator.php';
	Chill_Activator::activate();
	flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_chill() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-chill-deactivator.php';
	Chill_Deactivator::deactivate();
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'activate_chill' );
register_deactivation_hook( __FILE__, 'deactivate_chill' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-chill.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_chill() {

	$plugin = new Chill();
	$plugin->run();

}
run_chill();
