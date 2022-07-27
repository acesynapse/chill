<?php
// If this file is called directly, the teapot refuses to brew coffee.
defined('ABSPATH') || die(http_response_code(418));

/**
* Plugin Name: SnowStack Activation
* Description: Selection of required and recommended plugins for SnowPage.
* Version: 1.0.0
* Author: Emric Taylor, CCLS
* License: GPLv3 or later
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'snowpage_required_plugins' );

function snowpage_required_plugins() {

	$plugins = array(

		array(
			'name'               => 'Charitable – Donation Plugin',
			'slug'               => 'charitable',
			'required'           => false,
		),

		array(
			'name'               => 'Elementor Website Builder',
			'slug'               => 'elementor',
			'required'           => false,
		),

		array(
			'name'               => 'Forminator – Contact Form, Payment Form & Custom Form Builder',
			'slug'               => 'forminator',
			'required'           => false,
		),

		array(
			'name'               => 'Gantry 5 Framework',
			'slug'               => 'gantry5',
			'required'           => true,
		),

		array(
			'name'               => 'Translate WordPress – Google Language Translator',
			'slug'               => 'google-language-translator',
			'required'           => true,
		),

		array(
			'name'               => 'Library Bookshelves',
			'slug'               => 'library-bookshelves',
			'required'           => true,
		),

		array(
			'name'               => 'Open Modern Events Calendar',
			'slug'               => 'open-modern-events-calendar',
			'source'             => dirname (__FILE__) . '/plugins/open-modern-events-calendar.zip',
			'required'           => true,
		),

		array(
			'name'               => 'WP Pusher',
			'slug'               => 'wppusher',
			'source'             => dirname (__FILE__) . '/plugins/wppusher.zip',
			'required'           => true,
		),

		array(
			'name'               => 'Opening Hours',
			'slug'               => 'wp-opening-hours',
			'required'           => true,
		),

		array(
			'name'               => 'Simple History – user activity log, audit tool',
			'slug'               => 'simple-history',
			'required'           => true,
		),

		array(
			'name'               => 'Tainacan',
			'slug'               => 'tainacan',
			'required'           => true,
		),

		array(
			'name'               => 'Wordfence Security – Firewall & Malware Scan',
			'slug'               => 'wordfence',
			'required'           => true,
		),

		array(
			'name'               => 'WP Accessibility',
			'slug'               => 'wp-accessibility',
			'required'           => true,
		),

		array(
			'name'               => 'Yoast SEO',
			'slug'               => 'wordpress-seo',
			'required'           => false,
		),

		array(
			'name'               => 'UpdraftPlus WordPress Backup Plugin',
			'slug'               => 'updraftplus',
			'required'           => true,
		),

		array(
			'name'               => 'Page Links To',
			'slug'               => 'page-links-to',
			'required'           => true,
		),

		array(
			'name'               => 'Logo Scheduler – Great for holidays, events, and more',
			'slug'               => 'logo-scheduler-great-for-holidays-events-and-more',
			'required'           => false,
		),

		array(
			'name'               => 'Breadcrumb NavXT',
			'slug'               => 'breadcrumb-navxt',
			'required'           => true,
		),

	);

	$config = array(
		'id'           => 'tgmpa',
		'default_path' => '',
		'menu'         => 'tgmpa-install-plugins',
		'parent_slug'  => 'plugins.php',
		'capability'   => 'edit_theme_options',
		'has_notices'  => true,
		'dismissable'  => true,
		'dismiss_msg'  => '',
		'is_automatic' => true,
		'message'      => '',
	);

	tgmpa( $plugins, $config );
}


snowpage_required_plugins();
