<?php
// If this file is called directly, the teapot refuses to brew coffee.
defined('ABSPATH') || die(http_response_code(418));

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Chill
 * @subpackage Chill/includes
 * @author     Emric Taylor, CCLS (AceSynapse) <etaylor@cclsny.org>
 */
class Chill_Deactivator {

	/**
	 * Runs Chill deactivation hooks.
	 *
	 * Removes Database CPT
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		unregister_post_type( 'databases' );

	}

}
