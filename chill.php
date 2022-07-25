<?php

/**
 * @link              http://www.cclsny.org/snowpage
 * @since             0.0.1
 * @package           chill
 *
 * @wordpress-plugin
 * Plugin Name:       chill
 * Plugin URI:        http://www.cclsny.org/snowpage
 * Description:       Companion plugin for SnowPage.
 * Version:           0.0.1
 * Author:            Emric Taylor, CCLS (AceSynapse)
 * Author URI:        http://protemstudios.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       chill
 */

defined('ABSPATH') || die(http_response_code(418));

define( 'CHILL_VERSION', '0.0.1' );

require plugin_dir_path( __FILE__ ) . 'includes/snowpage-activation.php';
require plugin_dir_path( __FILE__ ) . 'includes/databases.php';

/*
* Databases Custom Post Type
*/

function custom_post_databases() {

// Set UI labels
    $labels = array(
        'name'                => _x( 'Databases', 'Post Type General Name'),
        'singular_name'       => _x( 'Database', 'Post Type Singular Name'),
        'menu_name'           => __( 'Databases'),
        'parent_item_colon'   => __( 'Parent Database'),
        'all_items'           => __( 'All Databases'),
        'view_item'           => __( 'View Databases'),
        'add_new_item'        => __( 'Add New Database'),
        'add_new'             => __( 'Add New'),
        'edit_item'           => __( 'Edit Database'),
        'update_item'         => __( 'Update Database'),
        'search_items'        => __( 'Search Databases'),
        'not_found'           => __( 'Not Found'),
        'not_found_in_trash'  => __( 'Not found in Trash'),
    );

// Set other options for Custom Post Type

    $args = array(
        'label'               => __( 'databases'),
        'description'         => __( 'Databases for Library Patron Use'),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        'taxonomies'          => array( 'systemwide' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest'        => true,

    );

    // Registering
    register_post_type( 'databases', $args );

}

add_action( 'init', 'custom_post_databases', 0 );

add_action( 'init', 'custom_nonhierarchical_taxonomy', 0 );

function custom_nonhierarchical_taxonomy() {

// Labels part for the GUI

  $labels = array(
    'name' => _x( 'System Wide', 'taxonomy general name' ),
    'singular_name' => _x( 'System Wide', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search System Wide' ),
    'all_items' => __( 'All System Wide' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit System Wide' ),
    'update_item' => __( 'Update System Wide' ),
    'add_new_item' => __( 'Add New System Wide' ),
    'new_item_name' => __( 'New System Wide Name' ),
    'separate_items_with_commas' => __( 'Separate System Wide with commas' ),
    'add_or_remove_items' => __( 'Add or remove System Wide' ),
    'choose_from_most_used' => __( 'Choose from the most used System Wide' ),
    'menu_name' => __( 'System Wide' ),
  );

// Now register the non-hierarchical taxonomy like tag

  register_taxonomy('systemwide','databases',array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 'slug' => 'systemwide' ),
  ));
}
