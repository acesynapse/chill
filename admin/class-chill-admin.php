<?php
// If this file is called directly, the teapot refuses to brew coffee.
defined('ABSPATH') || die(http_response_code(418));

/**
 * The admin-specific functionality of the plugin.
 *
 *
 * @package    Chill
 * @subpackage Chill/admin
 * @author     Emric Taylor, CCLS (AceSynapse) <etaylor@cclsny.org>
 */
class Chill_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @param   string    $plugin_name       The name of this plugin.
	 * @param   string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Initialize database CPT.
	 *
	 * @since    1.0.0
	 */
	 public function register_custom_post_type_databases() {

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

	// Set other options for Custom Post Type - Databases

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

	/**
	 * Initialize database taxonomy.
	 *
	 * @since    1.0.0
	 */
	function databases_nonhierarchical_taxonomy() {

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

	/**
	 * Initialize or set database version.
	 *
	 * @since    1.0.0
	 * @return   string
	 */
	private function chill_database_version() {
	  global $wpdb;
	  if (!($wpdb->get_results("SELECT option_name FROM {$wpdb->prefix}options WHERE option_name = 'sp_db_version'"))) {
	    $wpdb->insert( $wpdb->prefix.'options', array('option_name' => 'sp_db_version', 'option_value' => '0.0.0', 'autoload' => 'yes') );
	  }
	  $currentversionobject = $wpdb->get_results("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'sp_db_version'");
	  $currentversionarray = json_decode(json_encode($currentversionobject), true);
	  $currentversion = $currentversionarray[0]['option_value'];
		return $currentversion;
	}

	/**
	 * Compare results vs this version.
	 *
	 * @since    1.0.0
	 * @return   bool
	 */
	private function chill_database_compare() {
	  $currentversion =  $this->chill_database_version();
		$results = ($currentversion == $version)? 'false' : 'true';
		return $results;
	}

	/**
	 * Removes existing Open Hours
	 *
	 * @since    1.0.0
	 */
	private function chill_remove_open_hours() {
		global $wpdb;
		$opidobject = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}posts WHERE post_type = 'op-set'");
	  $opidarray = json_decode(json_encode($opidobject), true);
	  $opid = $opidarray[0]['id'];
	  $objectidobjectoh = $wpdb->get_results("SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = $opid");
	  $objectidarrayoh = json_decode(json_encode($objectidobjectoh), true);
	  $objectidoh = array_column($objectidarrayoh, 'meta_id');
	  foreach ($objectidoh as $x => $val) {
	    $wpdb->delete( $wpdb->prefix.'posts', array( 'id' => $opid ) );
	    $wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_id' => $val ) );
	  }
	}

	/**
	 * Removes existing System Wide Database Posts
	 *
	 * @since    1.0.0
	 */
	private function chill_remove_system_databases() {
		global $wpdb;
		$taxidobject = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = 'systemwide'");
	  $taxidarray = json_decode(json_encode($taxidobject), true);
	  $taxid = $taxidarray[0]['term_id'];
	  $objectidobject = $wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id = $taxid");
	  $objectidarray = json_decode(json_encode($objectidobject), true);
	  $objectid = $objectidarray;
	  foreach ($objectid as $x => $val) {
	    $isolated_val = ($val['object_id']);
	    $wpdb->delete( $wpdb->prefix.'posts', array( 'id' => $isolated_val ) );
	    $wpdb->delete( $wpdb->prefix.'posts', array( 'post_parent' => $isolated_val ) );
	    $wpdb->delete( $wpdb->prefix.'postmeta', array( 'post_id' => $isolated_val ) );
	    $wpdb->delete( $wpdb->prefix.'term_relationships', array( 'object_id' => $isolated_val ) );
	  }
	}

	/**
	 * Removes existing Database Images
	 *
	 * @since    1.0.0
	 */
	private function chill_remove_database_images() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$dbpdobject = $wpdb->get_results("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_sp_database_image'");
	  $dbpidarray = json_decode(json_encode($dbpdobject), true);
	  $dbpid = array_column($dbpidarray, 'post_id');
	  foreach ($dbpid as $x => $val) {
	    $wpdb->delete( $wpdb->prefix.'posts', array( 'id' => $val ) );
	    $wpdb->delete( $wpdb->prefix.'postmeta', array( 'post_id' => $val ) );
	  }
	}

	/**
	 * Insert new Open Hours Post
	 *
	 * @since    1.0.0
	 */
	private function chill_insert_open_hours() {
		global $wpdb;
		$urlw = $_SERVER['HTTP_HOST'];
		$urlw = preg_replace("/www\./", "", $urlw);
		switch ($urlw) {
		  case 'ahirahall.org':
		    $libhours = 'a:6:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"20:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"17:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"20:00";}i:3;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"17:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"17:00";}i:5;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"13:00";}}';
		    $libholi = 'a:9:{i:0;a:3:{s:4:"name";s:36:"Closed - Martin Luther King, Jr. Day";s:9:"dateStart";s:10:"2022-01-17";s:7:"dateEnd";s:10:"2022-01-17";}i:1;a:3:{s:4:"name";s:15:"Closed - Easter";s:9:"dateStart";s:10:"2022-04-15";s:7:"dateEnd";s:10:"2022-04-16";}i:2;a:3:{s:4:"name";s:21:"Closed - Memorial Day";s:9:"dateStart";s:10:"2022-05-28";s:7:"dateEnd";s:10:"2022-05-30";}i:3;a:3:{s:4:"name";s:25:"Closed - Independence Day";s:9:"dateStart";s:10:"2022-07-04";s:7:"dateEnd";s:10:"2022-07-04";}i:4;a:3:{s:4:"name";s:18:"Closed - Labor Day";s:9:"dateStart";s:10:"2022-09-03";s:7:"dateEnd";s:10:"2022-09-05";}i:5;a:3:{s:4:"name";s:21:"Closed - Veterans Day";s:9:"dateStart";s:10:"2022-11-11";s:7:"dateEnd";s:10:"2022-11-11";}i:6;a:3:{s:4:"name";s:21:"Closed - Thanksgiving";s:9:"dateStart";s:10:"2022-11-23";s:7:"dateEnd";s:10:"2022-11-26";}i:7;a:3:{s:4:"name";s:18:"Closed - Christmas";s:9:"dateStart";s:10:"2022-12-24";s:7:"dateEnd";s:10:"2022-12-26";}i:8;a:3:{s:4:"name";s:18:"Closed - New Years";s:9:"dateStart";s:10:"2022-12-31";s:7:"dateEnd";s:10:"2023-01-02";}}';
		    $libir = 'a:0:{}';
		    break;
		  case 'alleganylibrary.org':
		    $libhours = 'a:6:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"18:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"18:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"18:00";}i:3;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"18:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"18:00";}i:5;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"13:00";}}';
		    $libholi = 'a:13:{i:0;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-06-04";s:7:"dateEnd";s:10:"2022-06-05";}i:1;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-06-11";s:7:"dateEnd";s:10:"2022-06-12";}i:2;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-06-18";s:7:"dateEnd";s:10:"2022-06-19";}i:3;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-06-25";s:7:"dateEnd";s:10:"2022-06-26";}i:4;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-02";s:7:"dateEnd";s:10:"2022-07-03";}i:5;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-09";s:7:"dateEnd";s:10:"2022-07-10";}i:6;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-16";s:7:"dateEnd";s:10:"2022-07-17";}i:7;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-23";s:7:"dateEnd";s:10:"2022-07-24";}i:8;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-30";s:7:"dateEnd";s:10:"2022-07-31";}i:9;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-06";s:7:"dateEnd";s:10:"2022-08-07";}i:10;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-13";s:7:"dateEnd";s:10:"2022-08-14";}i:11;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-20";s:7:"dateEnd";s:10:"2022-08-21";}i:12;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-27";s:7:"dateEnd";s:10:"2022-08-28";}}';
		    $libir = 'a:0:{}';
		    break;
		  case 'andersonleelibrary.org':
		    $libhours = 'a:5:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"12:00";s:7:"timeEnd";s:5:"20:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"12:00";s:7:"timeEnd";s:5:"20:00";}i:2;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"20:00";}i:3;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"12:00";s:7:"timeEnd";s:5:"17:00";}i:4;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"14:00";}}';
		    $libholi = 'a:0:{}';
		    $libir = 'a:0:{}';
		    break;
		  case 'ashvillelibrary.com':
		    $libhours = 'a:6:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"09:30";s:7:"timeEnd";s:5:"19:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"09:30";s:7:"timeEnd";s:5:"19:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"09:30";s:7:"timeEnd";s:5:"19:00";}i:3;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"09:30";s:7:"timeEnd";s:5:"17:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"09:30";s:7:"timeEnd";s:5:"17:00";}i:5;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"13:00";}}';
		    $libholi = 'a:0:{}';
		    $libir = 'a:0:{}';
		    break;
		  case 'barkerlibrary.org':
		    $libhours = 'a:6:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"17:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"18:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"17:00";}i:3;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"18:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"17:00";}i:5;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"14:00";}';
		    $libholi = 'a:11:{i:0;a:3:{s:4:"name";s:36:"Closed - Martin Luther King, Jr. Day";s:9:"dateStart";s:10:"2022-01-17";s:7:"dateEnd";s:10:"2022-01-17";}i:1;a:3:{s:4:"name";s:26:"Closed - Presidents’ Day";s:9:"dateStart";s:10:"2022-02-21";s:7:"dateEnd";s:10:"2022-02-21";}i:2;a:3:{s:4:"name";s:20:"Closed - Good Friday";s:9:"dateStart";s:10:"2022-04-15";s:7:"dateEnd";s:10:"2022-04-15";}i:3;a:3:{s:4:"name";s:21:"Closed - Memorial Day";s:9:"dateStart";s:10:"2022-05-30";s:7:"dateEnd";s:10:"2022-05-30";}i:4;a:3:{s:4:"name";s:25:"Closed - Independence Day";s:9:"dateStart";s:10:"2022-07-04";s:7:"dateEnd";s:10:"2022-07-04";}i:5;a:3:{s:4:"name";s:18:"Closed - Labor Day";s:9:"dateStart";s:10:"2022-09-05";s:7:"dateEnd";s:10:"2022-09-05";}i:6;a:3:{s:4:"name";s:21:"Closed - Columbus Day";s:9:"dateStart";s:10:"2022-10-10";s:7:"dateEnd";s:10:"2022-10-10";}i:7;a:3:{s:4:"name";s:21:"Closed - Veterans Day";s:9:"dateStart";s:10:"2022-11-11";s:7:"dateEnd";s:10:"2022-11-11";}i:8;a:3:{s:4:"name";s:21:"Closed - Thanksgiving";s:9:"dateStart";s:10:"2022-11-24";s:7:"dateEnd";s:10:"2022-11-24";}i:9;a:3:{s:4:"name";s:18:"Closed - Christmas";s:9:"dateStart";s:10:"2022-12-24";s:7:"dateEnd";s:10:"2022-12-25";}i:10;a:3:{s:4:"name";s:18:"Closed - New Years";s:9:"dateStart";s:10:"2022-12-31";s:7:"dateEnd";s:10:"2023-01-01";}}';
		    $libir = 'a:0:{}';
		    break;
		  case 'bemuspointlibrary.org':
		    $libhours = 'a:6:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"17:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"17:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"17:00";}i:3;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"19:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"17:00";}i:5;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"14:00";}}';
		    $libholi = 'a:6:{i:0;a:3:{s:4:"name";s:21:"Closed - Memorial Day";s:9:"dateStart";s:10:"2022-05-30";s:7:"dateEnd";s:10:"2022-05-30";}i:1;a:3:{s:4:"name";s:25:"Closed - Independence Day";s:9:"dateStart";s:10:"2022-07-04";s:7:"dateEnd";s:10:"2022-07-04";}i:2;a:3:{s:4:"name";s:18:"Closed - Labor Day";s:9:"dateStart";s:10:"2022-09-05";s:7:"dateEnd";s:10:"2022-09-05";}i:3;a:3:{s:4:"name";s:21:"Closed - Thanksgiving";s:9:"dateStart";s:10:"2022-11-24";s:7:"dateEnd";s:10:"2022-11-24";}i:4;a:3:{s:4:"name";s:18:"Closed - Christmas";s:9:"dateStart";s:10:"2022-12-25";s:7:"dateEnd";s:10:"2022-12-25";}i:5;a:3:{s:4:"name";s:18:"Closed - New Years";s:9:"dateStart";s:10:"2023-01-01";s:7:"dateEnd";s:10:"2023-01-01";}}';
		    $libir = 'a:0:{}';
		    break;
		  case 'cattarauguslibrary.org':
		    $libhours = 'a:5:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"11:00";s:7:"timeEnd";s:5:"16:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"11:00";s:7:"timeEnd";s:5:"18:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"11:00";s:7:"timeEnd";s:5:"16:00";}i:3;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"11:00";s:7:"timeEnd";s:5:"18:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"11:00";s:7:"timeEnd";s:5:"16:00";}}';
		    $libholi = 'a:5:{i:0;a:3:{s:4:"name";s:21:"Closed - Memorial Day";s:9:"dateStart";s:10:"2022-05-30";s:7:"dateEnd";s:10:"2022-05-30";}i:1;a:3:{s:4:"name";s:25:"Closed - Independence Day";s:9:"dateStart";s:10:"2022-07-04";s:7:"dateEnd";s:10:"2022-07-04";}i:2;a:3:{s:4:"name";s:18:"Closed - Labor Day";s:9:"dateStart";s:10:"2022-09-05";s:7:"dateEnd";s:10:"2022-09-05";}i:3;a:3:{s:4:"name";s:21:"Closed - Thanksgiving";s:9:"dateStart";s:10:"2022-11-24";s:7:"dateEnd";s:10:"2022-11-24";}i:4;a:3:{s:4:"name";s:18:"Closed - Christmas";s:9:"dateStart";s:10:"2022-12-24";s:7:"dateEnd";s:10:"2022-12-26";}}';
		    $libir = 'a:0:{}';
		    break;
		  case 'cfclibrary.org':
		    $libhours = 'a:6:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"12:00";}i:1;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"07:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"16:00";}i:3;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"13:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"15:00";s:7:"timeEnd";s:5:"18:00";}i:5;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"14:00";}}';
		    $libholi = 'a:10:{i:0;a:3:{s:4:"name";s:36:"Closed - Martin Luther King, Jr. Day";s:9:"dateStart";s:10:"2022-01-17";s:7:"dateEnd";s:10:"2022-01-17";}i:1;a:3:{s:4:"name";s:26:"Closed - Presidents’ Day";s:9:"dateStart";s:10:"2022-02-21";s:7:"dateEnd";s:10:"2022-02-21";}i:2;a:3:{s:4:"name";s:21:"Closed - Memorial Day";s:9:"dateStart";s:10:"2022-05-30";s:7:"dateEnd";s:10:"2022-05-30";}i:3;a:3:{s:4:"name";s:25:"Closed - Independence Day";s:9:"dateStart";s:10:"2022-07-04";s:7:"dateEnd";s:10:"2022-07-04";}i:4;a:3:{s:4:"name";s:18:"Closed - Labor Day";s:9:"dateStart";s:10:"2022-09-05";s:7:"dateEnd";s:10:"2022-09-05";}i:5;a:3:{s:4:"name";s:21:"Closed - Columbus Day";s:9:"dateStart";s:10:"2022-10-10";s:7:"dateEnd";s:10:"2022-10-10";}i:6;a:3:{s:4:"name";s:21:"Closed - Veterans Day";s:9:"dateStart";s:10:"2022-11-11";s:7:"dateEnd";s:10:"2022-11-11";}i:7;a:3:{s:4:"name";s:21:"Closed - Thanksgiving";s:9:"dateStart";s:10:"2022-11-24";s:7:"dateEnd";s:10:"2022-11-24";}i:8;a:3:{s:4:"name";s:18:"Closed - Christmas";s:9:"dateStart";s:10:"2022-12-25";s:7:"dateEnd";s:10:"2022-12-25";}i:9;a:3:{s:4:"name";s:18:"Closed - New Years";s:9:"dateStart";s:10:"2023-01-01";s:7:"dateEnd";s:10:"2023-01-01";}}';
		    $libir = 'a:0:{}';
		    break;
		  case 'delevanlibrary.org':
		    $libhours = 'a:7:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"20:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"12:00";}i:2;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"20:00";}i:3;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"20:00";}i:4;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"20:00";}i:5;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"13:00";s:7:"timeEnd";s:5:"18:00";}i:6;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"13:00";}}';
		    $libholi = 'a:6:{i:0;a:3:{s:4:"name";s:21:"Closed - Memorial Day";s:9:"dateStart";s:10:"2022-05-28";s:7:"dateEnd";s:10:"2022-05-30";}i:1;a:3:{s:4:"name";s:25:"Closed - Independence Day";s:9:"dateStart";s:10:"2022-07-04";s:7:"dateEnd";s:10:"2022-07-04";}i:2;a:3:{s:4:"name";s:18:"Closed - Labor Day";s:9:"dateStart";s:10:"2022-09-03";s:7:"dateEnd";s:10:"2022-09-05";}i:3;a:3:{s:4:"name";s:21:"Closed - Thanksgiving";s:9:"dateStart";s:10:"2022-11-24";s:7:"dateEnd";s:10:"2022-11-27";}i:4;a:3:{s:4:"name";s:18:"Closed - Christmas";s:9:"dateStart";s:10:"2022-12-24";s:7:"dateEnd";s:10:"2022-12-26";}i:5;a:3:{s:4:"name";s:18:"Closed - New Years";s:9:"dateStart";s:10:"2022-12-31";s:7:"dateEnd";s:10:"2023-01-02";}}';
		    $libir = 'a:0:{}';
		    break;
		  case 'dunkirklibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'ellicottvillelibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'ellingtonlibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'falconerlibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'findleylibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'fluvannalibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'franklinvillelibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'gowandalibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'hazeltinelibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'kennedyfreelibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'lakewoodlibrary.org':
		    $libhours = 'a:6:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"18:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"18:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"18:00";}i:3;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"18:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"18:00";}i:5;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"14:00";}}';
		    $libholi = 'a:4:{i:0;a:3:{s:4:"name";s:21:"Closed - Veterans Day";s:9:"dateStart";s:10:"2022-11-11";s:7:"dateEnd";s:10:"2022-11-11";}i:1;a:3:{s:4:"name";s:21:"Closed - Thanksgiving";s:9:"dateStart";s:10:"2022-11-24";s:7:"dateEnd";s:10:"2022-11-26";}i:2;a:3:{s:4:"name";s:18:"Closed - Christmas";s:9:"dateStart";s:10:"2022-12-24";s:7:"dateEnd";s:10:"2022-12-26";}i:3;a:3:{s:4:"name";s:19:"Closed - New Years";s:9:"dateStart";s:10:"2022-12-31";s:7:"dateEnd";s:10:"2023-01-01";}}';
		    $libir = 'a:1:{i:0;a:4:{s:4:"name";s:9:"Fall Fest";s:4:"date";s:10:"2022-10-08";s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"14:00";}}';
		    break;
		  case 'littlevalleylibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'machiaslibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'mayvillelibrary.com':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'minervalibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'myerslibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'oleanlibrary.org':
		    $libhours = 'a:6:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"21:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"21:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"21:00";}i:3;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"21:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"18:00";}i:5;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"17:00";}}';
		    $libholi = 'a:9:{i:0;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-02";s:7:"dateEnd";s:10:"2022-07-03";}i:1;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-09";s:7:"dateEnd";s:10:"2022-07-10";}i:2;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-16";s:7:"dateEnd";s:10:"2022-07-17";}i:3;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-23";s:7:"dateEnd";s:10:"2022-07-24";}i:4;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-30";s:7:"dateEnd";s:10:"2022-07-31";}i:5;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-06";s:7:"dateEnd";s:10:"2022-08-07";}i:6;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-13";s:7:"dateEnd";s:10:"2022-08-14";}i:7;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-20";s:7:"dateEnd";s:10:"2022-08-21";}i:8;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-27";s:7:"dateEnd";s:10:"2022-08-28";}}';
		    $libir = 'a:0:{}';
		    break;
		  case 'pattersonlibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'portvillelibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'prendergastlibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'randolphlibrary.info':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'ripleylibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'salamancalibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'senecalibraries.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'sinclairvillelibrary.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'stocktonlibraries.org':
		    $libhours = '';
		    $libholi = '';
		    $libir = 'a:0:{}';
		    break;
		  case 'wnyls.org':
		    $libhours = 'a:6:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"21:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"21:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"21:00";}i:3;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"21:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"18:00";}i:5;a:3:{s:7:"weekday";i:6;s:9:"timeStart";s:5:"10:00";s:7:"timeEnd";s:5:"17:00";}}';
		    $libholi = 'a:9:{i:0;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-02";s:7:"dateEnd";s:10:"2022-07-03";}i:1;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-09";s:7:"dateEnd";s:10:"2022-07-10";}i:2;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-16";s:7:"dateEnd";s:10:"2022-07-17";}i:3;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-23";s:7:"dateEnd";s:10:"2022-07-24";}i:4;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-07-30";s:7:"dateEnd";s:10:"2022-07-31";}i:5;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-06";s:7:"dateEnd";s:10:"2022-08-07";}i:6;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-13";s:7:"dateEnd";s:10:"2022-08-14";}i:7;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-20";s:7:"dateEnd";s:10:"2022-08-21";}i:8;a:3:{s:4:"name";s:6:"Closed";s:9:"dateStart";s:10:"2022-08-27";s:7:"dateEnd";s:10:"2022-08-28";}}';
		    $libir = 'a:0:{}';
		    break;
		  default:
		    $libhours = 'a:5:{i:0;a:3:{s:7:"weekday";i:1;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"17:00";}i:1;a:3:{s:7:"weekday";i:2;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"17:00";}i:2;a:3:{s:7:"weekday";i:3;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"17:00";}i:3;a:3:{s:7:"weekday";i:4;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"17:00";}i:4;a:3:{s:7:"weekday";i:5;s:9:"timeStart";s:5:"09:00";s:7:"timeEnd";s:5:"17:00";}}';
		    $libholi = 'a:12:{i:0;a:3:{s:4:"name";s:36:"Closed - Martin Luther King, Jr. Day";s:9:"dateStart";s:10:"2022-01-17";s:7:"dateEnd";s:10:"2022-01-17";}i:1;a:3:{s:4:"name";s:26:"Closed - Presidents’ Day";s:9:"dateStart";s:10:"2022-02-21";s:7:"dateEnd";s:10:"2022-02-21";}i:2;a:3:{s:4:"name";s:20:"Closed - Good Friday";s:9:"dateStart";s:10:"2022-04-15";s:7:"dateEnd";s:10:"2022-04-15";}i:3;a:3:{s:4:"name";s:21:"Closed - Memorial Day";s:9:"dateStart";s:10:"2022-05-30";s:7:"dateEnd";s:10:"2022-05-30";}i:4;a:3:{s:4:"name";s:19:"Closed - Juneteenth";s:9:"dateStart";s:10:"2022-06-20";s:7:"dateEnd";s:10:"2022-06-20";}i:5;a:3:{s:4:"name";s:25:"Closed - Independence Day";s:9:"dateStart";s:10:"2022-07-04";s:7:"dateEnd";s:10:"2022-07-04";}i:6;a:3:{s:4:"name";s:18:"Closed - Labor Day";s:9:"dateStart";s:10:"2022-09-05";s:7:"dateEnd";s:10:"2022-09-05";}i:7;a:3:{s:4:"name";s:21:"Closed - Columbus Day";s:9:"dateStart";s:10:"2022-10-10";s:7:"dateEnd";s:10:"2022-10-10";}i:8;a:3:{s:4:"name";s:21:"Closed - Veterans Day";s:9:"dateStart";s:10:"2022-11-11";s:7:"dateEnd";s:10:"2022-11-11";}i:9;a:3:{s:4:"name";s:21:"Closed - Thanksgiving";s:9:"dateStart";s:10:"2022-11-24";s:7:"dateEnd";s:10:"2022-11-25";}i:10;a:3:{s:4:"name";s:18:"Closed - Christmas";s:9:"dateStart";s:10:"2022-12-23";s:7:"dateEnd";s:10:"2022-12-26";}i:11;a:3:{s:4:"name";s:18:"Closed - New Years";s:9:"dateStart";s:10:"2022-12-30";s:7:"dateEnd";s:10:"2023-01-02";}}';
		    $libir = 'a:0:{}';
		    break;
		}

		$wpdb->insert( $wpdb->prefix.'posts', array(
		  'post_author' => '1',
		  'post_date' => '2000-01-01 00:00:01',
		  'post_date_gmt' => '2000-01-01 00:00:01',
		  'post_title' => 'Hours',
		  'post_status' => 'publish',
		  'comment_status' => 'closed',
		  'ping_status' => 'closed',
		  'post_name' => 'hours',
		  'post_modified' => '2022-01-01 00:00:01',
		  'post_modified_gmt' => '2022-01-01 00:00:01',
		  'menu_order' => '0',
		  'post_type' => 'op-set'
		) );
		$dbpostsobject = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'op-set' ORDER BY ID DESC LIMIT 1");
		$dbpostsarray = json_decode(json_encode($dbpostsobject), true);
		$dbposts = $dbpostsarray[0];
		foreach ($dbposts as $x => $val) {
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_edit_last', 'meta_value' => '1' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_op_set_periods', 'meta_value' => $libhours ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_op_set_holidays', 'meta_value' => $libholi ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_op_set_irregular_openings', 'meta_value' => $libir ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_op_meta_box_set_details_description' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_op_meta_box_set_details_dateStart' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_op_meta_box_set_details_dateEnd' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_op_meta_box_set_details_weekScheme' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_op_meta_box_set_details_alias' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_op_meta_box_set_details_childSetNotice' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_edit_lock' ) );
		    $wpdb->update( $wpdb->prefix.'posts', array( 'guid' => 'https://'.$urlw.'/?post_type=op-set&#038;p='.$val), array( 'post_id' => $val)  );
		}
	}

	/**
	 * Insert NovelNY databse
	 *
	 * @since    1.0.0
	 */
	private function chill_insert_novelny() {
		global $wpdb;
		$taxidobject = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = 'systemwide'");
	  $taxidarray = json_decode(json_encode($taxidobject), true);
	  $taxid = $taxidarray[0]['term_id'];
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$wpdb->insert( $wpdb->prefix.'posts', array(
	    'post_author' => '2257',
	    'post_date' => '2022-01-01 00:00:01',
	    'post_date_gmt' => '2022-01-01 00:00:01',
	    'post_title' => 'NovelNY',
	    'post_status' => 'publish',
	    'comment_status' => 'closed',
	    'ping_status' => 'closed',
	    'post_name' => 'novelny',
	    'post_modified' => '2022-01-01 00:00:01',
	    'post_modified_gmt' => '2022-01-01 00:00:01',
	    'post_type' => 'databases'
	  ) );
	  $dbpostsobject = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'databases' ORDER BY ID DESC LIMIT 1");
	  $dbpostsarray = json_decode(json_encode($dbpostsobject), true);
	  $dbposts = $dbpostsarray[0];
	  foreach ($dbposts as $x => $val) {
		  $imgid = $val + 1;
	      $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_links_to', 'meta_value' => 'http://www.novelnewyork.org/' ) );
	      $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_links_to_target', 'meta_value' => '_blank' ) );
	      $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_thumbnail_id', 'meta_value' => $imgid ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $imgid, 'meta_key' => '_sp_database_image', 'meta_value' => 'true' ) );
	      $wpdb->insert( $wpdb->prefix.'term_relationships', array( 'object_id' => $val, 'term_taxonomy_id' => $taxid, 'term_order' => '0' ) );
	  }
	  $image_url = 'https://www.cclsny.org/wp-content/uploads/2022/06/novel_master.png';
	  $upload_dir = wp_upload_dir();
	  $image_data = file_get_contents( $image_url );
	  $filename = basename( $image_url );
	  if ( wp_mkdir_p( $upload_dir['path'] ) ) {
	    $file = $upload_dir['path'] . '/' . $filename;
	  } else {
	    $file = $upload_dir['basedir'] . '/' . $filename;
	  }
	  file_put_contents( $file, $image_data );
	  $wp_filetype = wp_check_filetype( $filename, null );
	  $attachment = array(
	    'post_mime_type' => $wp_filetype['type'],
	    'post_title' => sanitize_file_name( $filename ),
	    'post_content' => '',
	    'post_status' => 'inherit'
	  );
	  $attach_id = wp_insert_attachment( $attachment, $file );
	  $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	  wp_update_attachment_metadata( $attach_id, $attach_data );
	}

	/**
	 * Insert Libby databse
	 *
	 * @since    1.0.0
	 */
	private function chill_insert_libby() {
		global $wpdb;
		$taxidobject = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = 'systemwide'");
	  $taxidarray = json_decode(json_encode($taxidobject), true);
	  $taxid = $taxidarray[0]['term_id'];
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$wpdb->insert( $wpdb->prefix.'posts', array(
		  'post_author' => '2257',
		  'post_date' => '2022-01-01 00:00:01',
		  'post_date_gmt' => '2022-01-01 00:00:01',
		  'post_title' => 'Meet Libby.',
		  'post_status' => 'publish',
		  'comment_status' => 'closed',
		  'ping_status' => 'closed',
		  'post_name' => 'libby',
		  'post_modified' => '2022-01-01 00:00:01',
		  'post_modified_gmt' => '2022-01-01 00:00:01',
		  'post_type' => 'databases'
		) );
		$dbpostsobject = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'databases' ORDER BY ID DESC LIMIT 1");
		$dbpostsarray = json_decode(json_encode($dbpostsobject), true);
		$dbposts = $dbpostsarray[0];
		foreach ($dbposts as $x => $val) {
		    $imgid = $val + 1;
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_links_to', 'meta_value' => 'https://meet.libbyapp.com/?utm_medium=lightning_banner&utm_source=lightning&utm_campaign=libby' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_links_to_target', 'meta_value' => '_blank' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_thumbnail_id', 'meta_value' => $imgid ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $imgid, 'meta_key' => '_sp_database_image', 'meta_value' => 'true' ) );
		    $wpdb->insert( $wpdb->prefix.'term_relationships', array( 'object_id' => $val, 'term_taxonomy_id' => $taxid, 'term_order' => '0' ) );
		}
		$image_url = 'https://www.cclsny.org/wp-content/uploads/2022/06/libby_master.png';
		$upload_dir = wp_upload_dir();
		$image_data = file_get_contents( $image_url );
		$filename = basename( $image_url );
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
		  $file = $upload_dir['path'] . '/' . $filename;
		} else {
		  $file = $upload_dir['basedir'] . '/' . $filename;
		}
		file_put_contents( $file, $image_data );
		$wp_filetype = wp_check_filetype( $filename, null );
		$attachment = array(
		  'post_mime_type' => $wp_filetype['type'],
		  'post_title' => sanitize_file_name( $filename ),
		  'post_content' => '',
		  'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
	}

	/**
	 * Insert Ancestry databse
	 *
	 * @since    1.0.0
	 */
	private function chill_insert_ancestry() {
		global $wpdb;
		$taxidobject = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = 'systemwide'");
	  $taxidarray = json_decode(json_encode($taxidobject), true);
	  $taxid = $taxidarray[0]['term_id'];
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$wpdb->insert( $wpdb->prefix.'posts', array(
		  'post_author' => '2257',
		  'post_date' => '2022-01-01 00:00:01',
		  'post_date_gmt' => '2022-01-01 00:00:01',
		  'post_title' => 'Ancestry Library Edition',
		  'post_status' => 'publish',
		  'comment_status' => 'closed',
		  'ping_status' => 'closed',
		  'post_name' => 'ancestry-lb',
		  'post_modified' => '2022-01-01 00:00:01',
		  'post_modified_gmt' => '2022-01-01 00:00:01',
		  'post_type' => 'databases'
		) );
		$dbpostsobject = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'databases' ORDER BY ID DESC LIMIT 1");
		$dbpostsarray = json_decode(json_encode($dbpostsobject), true);
		$dbposts = $dbpostsarray[0];
		foreach ($dbposts as $x => $val) {
		  $imgid = $val + 1;
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_links_to', 'meta_value' => 'https://www.ancestrylibrary.com/' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_links_to_target', 'meta_value' => '_blank' ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_thumbnail_id', 'meta_value' => $imgid ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $imgid, 'meta_key' => '_sp_database_image', 'meta_value' => 'true' ) );
		    $wpdb->insert( $wpdb->prefix.'term_relationships', array( 'object_id' => $val, 'term_taxonomy_id' => $taxid, 'term_order' => '0' ) );
		}
		$image_url = 'https://www.cclsny.org/wp-content/uploads/2022/06/ancestry_master.jpg';
		$upload_dir = wp_upload_dir();
		$image_data = file_get_contents( $image_url );
		$filename = basename( $image_url );
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
		  $file = $upload_dir['path'] . '/' . $filename;
		} else {
		  $file = $upload_dir['basedir'] . '/' . $filename;
		}
		file_put_contents( $file, $image_data );
		$wp_filetype = wp_check_filetype( $filename, null );
		$attachment = array(
		  'post_mime_type' => $wp_filetype['type'],
		  'post_title' => sanitize_file_name( $filename ),
		  'post_content' => '',
		  'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
	}

	/**
	 * Insert NYHP databse
	 *
	 * @since    1.0.0
	 */
	private function chill_insert_nyhp() {
		global $wpdb;
		$taxidobject = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = 'systemwide'");
	  $taxidarray = json_decode(json_encode($taxidobject), true);
	  $taxid = $taxidarray[0]['term_id'];
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$wpdb->insert( $wpdb->prefix.'posts', array(
	    'post_author' => '2257',
	    'post_date' => '2022-01-01 00:00:01',
	    'post_date_gmt' => '2022-01-01 00:00:01',
	    'post_title' => 'New York Historic Newspapers',
	    'post_status' => 'publish',
	    'comment_status' => 'closed',
	    'ping_status' => 'closed',
	    'post_name' => 'nyhp',
	    'post_modified' => '2022-01-01 00:00:01',
	    'post_modified_gmt' => '2022-01-01 00:00:01',
	    'post_type' => 'databases'
	  ) );
	  $dbpostsobject = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'databases' ORDER BY ID DESC LIMIT 1");
	  $dbpostsarray = json_decode(json_encode($dbpostsobject), true);
	  $dbposts = $dbpostsarray[0];
	  foreach ($dbposts as $x => $val) {
		  $imgid = $val + 1;
	      $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_links_to', 'meta_value' => 'http://nyshistoricnewspapers.org/' ) );
	      $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_links_to_target', 'meta_value' => '_blank' ) );
	      $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_thumbnail_id', 'meta_value' => $imgid ) );
		    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $imgid, 'meta_key' => '_sp_database_image', 'meta_value' => 'true' ) );
	      $wpdb->insert( $wpdb->prefix.'term_relationships', array( 'object_id' => $val, 'term_taxonomy_id' => $taxid, 'term_order' => '0' ) );
	  }
	  $image_url = 'https://www.cclsny.org/wp-content/uploads/2022/06/nyhp_master.png';
	  $upload_dir = wp_upload_dir();
	  $image_data = file_get_contents( $image_url );
	  $filename = basename( $image_url );
	  if ( wp_mkdir_p( $upload_dir['path'] ) ) {
	    $file = $upload_dir['path'] . '/' . $filename;
	  } else {
	    $file = $upload_dir['basedir'] . '/' . $filename;
	  }
	  file_put_contents( $file, $image_data );
	  $wp_filetype = wp_check_filetype( $filename, null );
	  $attachment = array(
	    'post_mime_type' => $wp_filetype['type'],
	    'post_title' => sanitize_file_name( $filename ),
	    'post_content' => '',
	    'post_status' => 'inherit'
	  );
	  $attach_id = wp_insert_attachment( $attachment, $file );
	  $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	  wp_update_attachment_metadata( $attach_id, $attach_data );
	}

	/**
	 * Insert NY Heritage databse
	 *
	 * @since    1.0.0
	 */
	private function chill_insert_heritage() {
		global $wpdb;
		$taxidobject = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = 'systemwide'");
	  $taxidarray = json_decode(json_encode($taxidobject), true);
	  $taxid = $taxidarray[0]['term_id'];
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$wpdb->insert( $wpdb->prefix.'posts', array(
      'post_author' => '2257',
      'post_date' => '2022-01-01 00:00:01',
      'post_date_gmt' => '2022-01-01 00:00:01',
      'post_title' => 'New York Heritage',
      'post_status' => 'publish',
      'comment_status' => 'closed',
      'ping_status' => 'closed',
      'post_name' => 'heritage',
      'post_modified' => '2022-01-01 00:00:01',
      'post_modified_gmt' => '2022-01-01 00:00:01',
      'post_type' => 'databases'
    ) );
    $dbpostsobject = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'databases' ORDER BY ID DESC LIMIT 1");
    $dbpostsarray = json_decode(json_encode($dbpostsobject), true);
    $dbposts = $dbpostsarray[0];
    foreach ($dbposts as $x => $val) {
  	  $imgid = $val + 1;
        $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_links_to', 'meta_value' => 'https://nyheritage.org/' ) );
        $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_links_to_target', 'meta_value' => '_blank' ) );
        $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $val, 'meta_key' => '_thumbnail_id', 'meta_value' => $imgid ) );
  	    $wpdb->insert( $wpdb->prefix.'postmeta', array( 'post_id' => $imgid, 'meta_key' => '_sp_database_image', 'meta_value' => 'true' ) );
        $wpdb->insert( $wpdb->prefix.'term_relationships', array( 'object_id' => $val, 'term_taxonomy_id' => $taxid, 'term_order' => '0' ) );
    }
    $image_url = 'https://www.cclsny.org/wp-content/uploads/2022/06/heritage_master.jpg';
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents( $image_url );
    $filename = basename( $image_url );
    if ( wp_mkdir_p( $upload_dir['path'] ) ) {
      $file = $upload_dir['path'] . '/' . $filename;
    } else {
      $file = $upload_dir['basedir'] . '/' . $filename;
    }
    file_put_contents( $file, $image_data );
    $wp_filetype = wp_check_filetype( $filename, null );
    $attachment = array(
      'post_mime_type' => $wp_filetype['type'],
      'post_title' => sanitize_file_name( $filename ),
      'post_content' => '',
      'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file );
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    wp_update_attachment_metadata( $attach_id, $attach_data );
	}

	/**
	 * Update database version
	 *
	 * @since    1.0.0
	 */
	private function chill_database_update() {
		global $wpdb;
		$wpdb->update( $wpdb->prefix.'options', array('option_value' => $version) , array('option_name' => 'sp_db_version') );
	}

	/**
	 * Update all databases
	 *
	 * @since    1.0.0
	 */
	public function chill_database_complete() {
		 if ($this->chill_database_compare()) {
			 if ($this->chill_database_version() != '0.0.0') {
				 $this->chill_remove_open_hours();
				 $this->chill_remove_system_databases();
				 $this->chill_remove_database_images();
			 }

			 $this->chill_insert_open_hours();
			 $this->chill_insert_novelny();
			 $this->chill_insert_libby();
			 $this->chill_insert_ancestry();
			 $this->chill_insert_nyhp();
			 $this->chill_insert_heritage();
			 $this->chill_database_update();
		 }
	}

}
