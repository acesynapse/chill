<?php
// If this file is called directly, the teapot refuses to brew coffee.
defined('ABSPATH') || die(http_response_code(418));

/**
 *
 * @since             1.0.0
 * @package           Chill
 *
 * @wordpress-plugin
 * Plugin Name:       Chill
 * Plugin URI:        https://www.cclsny.org/snowpage
 * Description:       Chill is the side-along plugin for SnowPage.
 * Version:           2.0.0
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
defined( 'CHILL_VERSION' ) || define ( 'CHILL_VERSION', '2.0.0' );

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
