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
class Chill_Admin
{
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
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name;
        $this->version;
    }

    /**
     * Initialize database Custom Post Type
     *
     * @since    1.0.0
     */
     public function register_custom_post_type_databases()
     {
         // Set UI labels
         $labels = array(
                 'name'                => _x('Databases', 'Post Type General Name'),
                 'singular_name'       => _x('Database', 'Post Type Singular Name'),
                 'menu_name'           => __('Databases'),
                 'parent_item_colon'   => __('Parent Database'),
                 'all_items'           => __('All Databases'),
                 'view_item'           => __('View Databases'),
                 'add_new_item'        => __('Add New Database'),
                 'add_new'             => __('Add New'),
                 'edit_item'           => __('Edit Database'),
                 'update_item'         => __('Update Database'),
                 'search_items'        => __('Search Databases'),
                 'not_found'           => __('Not Found'),
                 'not_found_in_trash'  => __('Not found in Trash'),
         );

         // Set other options for Custom Post Type - Databases

         $args = array(
                 'label'               => __('databases'),
                 'description'         => __('Databases for Library Patron Use'),
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
         register_post_type('databases', $args);
     }

    /**
      * Initialize or set database version.
      *
      * @since    1.0.0
      * @return   string
      */
    private function chill_database_version()
    {
        $currentversion = get_option('sp_db_version');
        if (! $currentversion) {
            add_option('sp_db_version', '0.0.0');
        }
        if (!(version_compare($currentversion, '0.0.0', '>='))) {
            update_option('sp_db_version', '0.0.0');
        }
        $currentversion = get_option('sp_db_version');
        return $currentversion;
    }

    /**
     * Compare results vs this version.
     *
     * @since    1.0.0
     * @return   bool
     */
    private function chill_database_compare()
    {
        $currentversion =  $this->chill_database_version();

        $results = version_compare($currentversion, CHILL_VERSION, '<');

        return $results;
    }

    /**
     * Removes existing Open Hours
     *
     * @since    1.0.0
     */
    private function chill_remove_open_hours()
    {
        $ohids = get_posts([ 'fields' => 'ids', 'post_type' => 'op-set', 'name' => 'hours' ]);

        $ohids ? $ohid = $ohids[0] : $ohid = false;

        wp_delete_post($ohid);
    }

    /**
     * Removes existing System Wide Database Posts & Images
     *
     * @since    1.0.0
     */
    private function chill_remove_system_databases()
    {
        $databases = ['novelny', 'libby', 'ancestry-lb', 'nyhp', 'heritage'];

        foreach ($databases as $database) {
            $sdid = get_posts([ 'fields' => 'ids', 'post_type' => 'databases', 'name' => $database ]);

            $sdid ? $sdid = $sdid[0] : $sdid = false;

            wp_delete_attachment(get_post_thumbnail_id($sdid));

            wp_delete_post($sdid);
        }
    }


    /**
     * Download External Images
     *
     * @since    2.0.0
     */
    private function download_external_image($image_url)
    {
        require_once(ABSPATH . 'wp-admin/includes/file.php');

        $temp_file = download_url($image_url);

        if (is_wp_error($temp_file)) {
            return false;
        }

        $file = array(
            'name'     => basename($image_url),
            'type'     => mime_content_type($temp_file),
            'tmp_name' => $temp_file,
            'size'     => filesize($temp_file),
        );
        $sideload = wp_handle_sideload(
            $file,
            array(
                'test_form'   => false
            )
        );

        if (! empty($sideload[ 'error' ])) {
            return false;
        }

        $attachment_id = wp_insert_attachment(
            array(
                'guid'           => $sideload[ 'url' ],
                'post_mime_type' => $sideload[ 'type' ],
                'post_title'     => basename($sideload[ 'file' ]),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ),
            $sideload[ 'file' ]
        );

        if (is_wp_error($attachment_id) || ! $attachment_id) {
            return false;
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');

        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata($attachment_id, $sideload[ 'file' ])
        );

        return $attachment_id;
    }

    /**
     * Inserts updated System Wide Database Posts & Images
     * array format ['image', 'title', 'name', 'link']
     * @since    2.0.0
     */
    private function chill_insert_databases()
    {
        $databases = [
            ['https://www.cclsny.org/wp-content/uploads/2022/06/novel_master.png', 'NovelNY', 'novelny', 'http://www.novelnewyork.org/'],
            ['https://www.cclsny.org/wp-content/uploads/2022/06/libby_master.png', 'Meet Libby.', 'libby', 'https://meet.libbyapp.com/?utm_medium=lightning_banner&utm_source=lightning&utm_campaign=libby'],
            ['https://www.cclsny.org/wp-content/uploads/2022/06/ancestry_master.jpg', 'Ancestry Library Edition', 'ancestry-lb', 'https://www.ancestrylibrary.com/'],
            ['https://www.cclsny.org/wp-content/uploads/2022/06/nyhp_master.png', 'New York Historic Newspapers', 'nyhp', 'http://nyshistoricnewspapers.org/'],
            ['https://www.cclsny.org/wp-content/uploads/2022/06/heritage_master.jpg', 'New York Heritage', 'heritage', 'https://nyheritage.org/']
        ];

        foreach ($databases as list($image, $title, $name, $link)) {
            $image_id = $this->download_external_image($image);
            wp_insert_post(
                array(
                'post_date' => '2022-01-01 00:00:01',
                'post_date_gmt' => '2022-01-01 00:00:01',
                'post_title' => $title,
                'post_status' => 'publish',
                'post_type' => 'databases',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_name' => $name,
                'meta_input' => array(
                    '_links_to' => $link,
                    '_links_to_target' => '_blank',
                    '_thumbnail_id' => $image_id
                ))
            );
        }
    }

    /**
     * Update database version
     *
     * @since    1.0.0
     */
    private function chill_database_update()
    {
        update_option('sp_db_version', CHILL_VERSION);
    }

    /**
     * Update all databases
     *
     * @since    1.0.0
     */
    public function chill_database_complete()
    {
        if ($this->chill_database_compare()) {
            $this->chill_remove_open_hours();
            $this->chill_remove_system_databases();
            $this->chill_insert_databases();
            $this->chill_insert_open_hours();
            $this->chill_database_update();
        }
    }

    /**
     * Insert new Open Hours Post
     *
     * @since    1.0.0
     */
    private function chill_insert_open_hours()
    {
        $urlw = $_SERVER['HTTP_HOST'];
        $urlw = preg_replace("/www\./", "", $urlw);
        switch ($urlw) {
            case
            'ahirahall.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"13:00","timeEnd":"20:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"17:00"},"2":{"weekday":3,"timeStart":"13:00","timeEnd":"20:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"17:00"},"4":{"weekday":5,"timeStart":"13:00","timeEnd":"17:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'alleganylibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"18:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"18:00"},"2":{"weekday":3,"timeStart":"10:00","timeEnd":"18:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"18:00"},"4":{"weekday":5,"timeStart":"10:00","timeEnd":"18:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'andersonleelibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"12:00","timeEnd":"20:00"},"1":{"weekday":2,"timeStart":"12:00","timeEnd":"20:00"},"2":{"weekday":4,"timeStart":"10:00","timeEnd":"20:00"},"3":{"weekday":5,"timeStart":"12:00","timeEnd":"17:00"},"4":{"weekday":6,"timeStart":"10:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'ashvillelibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"09:30","timeEnd":"19:00"},"1":{"weekday":2,"timeStart":"09:30","timeEnd":"19:00"},"2":{"weekday":3,"timeStart":"09:30","timeEnd":"19:00"},"3":{"weekday":4,"timeStart":"09:30","timeEnd":"17:00"},"4":{"weekday":5,"timeStart":"09:30","timeEnd":"17:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'barkerlibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"17:00"},"1":{"weekday":2,"timeStart":"13:00","timeEnd":"18:00"},"2":{"weekday":3,"timeStart":"10:00","timeEnd":"17:00"},"3":{"weekday":4,"timeStart":"13:00","timeEnd":"18:00"},"4":{"weekday":5,"timeStart":"10:00","timeEnd":"17:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'bemuspointlibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"17:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"17:00"},"2":{"weekday":3,"timeStart":"13:00","timeEnd":"17:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"19:00"},"4":{"weekday":5,"timeStart":"10:00","timeEnd":"17:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'cattarauguslibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"11:00","timeEnd":"16:00"},"1":{"weekday":2,"timeStart":"11:00","timeEnd":"18:00"},"2":{"weekday":3,"timeStart":"11:00","timeEnd":"16:00"},"3":{"weekday":4,"timeStart":"11:00","timeEnd":"18:00"},"4":{"weekday":5,"timeStart":"11:00","timeEnd":"16:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'cclsny.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"09:00","timeEnd":"17:00"},"1":{"weekday":2,"timeStart":"09:00","timeEnd":"17:00"},"2":{"weekday":3,"timeStart":"09:00","timeEnd":"17:00"},"3":{"weekday":4,"timeStart":"09:00","timeEnd":"17:00"},"4":{"weekday":5,"timeStart":"09:00","timeEnd":"17:00"}}';
                $libholi = '{"0":{"name":"Closed - Martin Luther King, Jr. Day","dateStart":"2022-01-17","dateEnd":"2022-01-17"},"1":{"name":"Closed - Presidents\u2019 Day","dateStart":"2022-02-21","dateEnd":"2022-02-21"},"2":{"name":"Closed - Good Friday","dateStart":"2022-04-15","dateEnd":"2022-04-15"},"3":{"name":"Closed - Memorial Day","dateStart":"2022-05-30","dateEnd":"2022-05-30"},"4":{"name":"Closed - Juneteenth","dateStart":"2022-06-20","dateEnd":"2022-06-20"},"5":{"name":"Closed - Independence Day","dateStart":"2022-07-04","dateEnd":"2022-07-04"},"6":{"name":"Closed - Labor Day","dateStart":"2022-09-05","dateEnd":"2022-09-05"},"7":{"name":"Closed - Columbus Day","dateStart":"2022-10-10","dateEnd":"2022-10-10"},"8":{"name":"Closed - Veterans Day","dateStart":"2022-11-11","dateEnd":"2022-11-11"},"9":{"name":"Closed - Thanksgiving","dateStart":"2022-11-24","dateEnd":"2022-11-25"},"10":{"name":"Closed - Christmas","dateStart":"2022-12-23","dateEnd":"2022-12-26"},"11":{"name":"Closed - New Years","dateStart":"2022-12-30","dateEnd":"2023-01-02"}}';
                $libir = '{}';
                break;

            case
            'cfclibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"09:00","timeEnd":"12:00"},"1":{"weekday":1,"timeStart":"13:00","timeEnd":"17:00"},"2":{"weekday":3,"timeStart":"10:00","timeEnd":"16:00"},"3":{"weekday":5,"timeStart":"09:00","timeEnd":"13:00"},"4":{"weekday":5,"timeStart":"15:00","timeEnd":"18:00"},"5":{"weekday":6,"timeStart":"09:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'delevanlibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"13:00","timeEnd":"20:00"},"1":{"weekday":2,"timeStart":"09:00","timeEnd":"12:00"},"2":{"weekday":2,"timeStart":"13:00","timeEnd":"20:00"},"3":{"weekday":3,"timeStart":"13:00","timeEnd":"20:00"},"4":{"weekday":4,"timeStart":"13:00","timeEnd":"20:00"},"5":{"weekday":5,"timeStart":"13:00","timeEnd":"18:00"},"6":{"weekday":6,"timeStart":"09:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'dunkirklibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"19:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"19:00"},"2":{"weekday":3,"timeStart":"10:00","timeEnd":"19:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"19:00"},"4":{"weekday":5,"timeStart":"10:00","timeEnd":"15:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'ellicottvillelibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"17:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"20:00"},"2":{"weekday":3,"timeStart":"10:00","timeEnd":"20:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"17:00"},"4":{"weekday":5,"timeStart":"10:00","timeEnd":"17:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"17:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'ellingtonlibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"11:00","timeEnd":"19:00"},"1":{"weekday":2,"timeStart":"11:00","timeEnd":"19:00"},"2":{"weekday":3,"timeStart":"11:00","timeEnd":"19:00"},"3":{"weekday":4,"timeStart":"11:00","timeEnd":"19:00"},"4":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'falconerlibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"09:00","timeEnd":"18:00"},"1":{"weekday":2,"timeStart":"09:00","timeEnd":"18:00"},"2":{"weekday":3,"timeStart":"09:00","timeEnd":"18:00"},"3":{"weekday":4,"timeStart":"09:00","timeEnd":"17:00"},"4":{"weekday":5,"timeStart":"09:00","timeEnd":"17:00"},"5":{"weekday":6,"timeStart":"09:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'findleylibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"09:00","timeEnd":"14:00"},"1":{"weekday":2,"timeStart":"13:00","timeEnd":"19:00"},"2":{"weekday":4,"timeStart":"13:00","timeEnd":"19:00"},"3":{"weekday":6,"timeStart":"09:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'fluvannalibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"18:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"18:00"},"2":{"weekday":3,"timeStart":"10:00","timeEnd":"18:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"18:00"},"4":{"weekday":5,"timeStart":"10:00","timeEnd":"18:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{"0":{"name":"Closed","date":"2023-01-07","timeStart":"00:00","timeEnd":"00:00"},"1":{"name":"Closed","date":"2023-01-14","timeStart":"00:00","timeEnd":"00:00"},"2":{"name":"Closed","date":"2023-01-21","timeStart":"00:00","timeEnd":"00:00"},"3":{"name":"Closed","date":"2023-01-28","timeStart":"00:00","timeEnd":"00:00"},"4":{"name":"Closed","date":"2023-02-04","timeStart":"00:00","timeEnd":"00:00"},"5":{"name":"Closed","date":"2023-02-11","timeStart":"00:00","timeEnd":"00:00"},"6":{"name":"Closed","date":"2023-02-18","timeStart":"00:00","timeEnd":"00:00"},"7":{"name":"Closed","date":"2023-02-25","timeStart":"00:00","timeEnd":"00:00"}}';
                break;

            case
            'franklinvillelibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"09:00","timeEnd":"18:00"},"1":{"weekday":2,"timeStart":"09:00","timeEnd":"16:00"},"2":{"weekday":3,"timeStart":"09:00","timeEnd":"16:00"},"3":{"weekday":4,"timeStart":"09:00","timeEnd":"18:00"},"4":{"weekday":5,"timeStart":"09:00","timeEnd":"16:00"},"5":{"weekday":6,"timeStart":"09:00","timeEnd":"12:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'gowandalibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"18:00"},"1":{"weekday":2,"timeStart":"11:00","timeEnd":"16:00"},"2":{"weekday":3,"timeStart":"11:00","timeEnd":"19:00"},"3":{"weekday":4,"timeStart":"11:00","timeEnd":"16:00"},"4":{"weekday":5,"timeStart":"13:00","timeEnd":"21:00"},"5":{"weekday":6,"timeStart":"11:00","timeEnd":"16:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'hazeltinelibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"13:00","timeEnd":"19:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"17:00"},"2":{"weekday":3,"timeStart":"13:00","timeEnd":"19:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"17:00"},"4":{"weekday":5,"timeStart":"13:00","timeEnd":"19:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'kennedyfreelibrary.org':
                $libhours = '{"0":{"weekday":2,"timeStart":"09:00","timeEnd":"17:00"},"1":{"weekday":3,"timeStart":"09:00","timeEnd":"13:00"},"2":{"weekday":3,"timeStart":"15:00","timeEnd":"19:00"},"3":{"weekday":4,"timeStart":"09:00","timeEnd":"17:00"},"4":{"weekday":5,"timeStart":"09:00","timeEnd":"13:00"},"5":{"weekday":5,"timeStart":"15:00","timeEnd":"19:00"},"6":{"weekday":6,"timeStart":"09:00","timeEnd":"12:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'lakewoodlibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"18:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"18:00"},"2":{"weekday":3,"timeStart":"10:00","timeEnd":"18:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"18:00"},"4":{"weekday":5,"timeStart":"10:00","timeEnd":"18:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'littlevalleylibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"09:00","timeEnd":"19:00"},"1":{"weekday":2,"timeStart":"09:00","timeEnd":"19:00"},"2":{"weekday":4,"timeStart":"09:00","timeEnd":"19:00"},"3":{"weekday":5,"timeStart":"09:00","timeEnd":"17:00"},"4":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'machiaslibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"12:00","timeEnd":"18:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"15:00"},"2":{"weekday":3,"timeStart":"12:00","timeEnd":"18:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"18:00"},"4":{"weekday":5,"timeStart":"09:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'mayvillelibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"19:00"},"1":{"weekday":2,"timeStart":"11:00","timeEnd":"17:00"},"2":{"weekday":3,"timeStart":"10:00","timeEnd":"17:00"},"3":{"weekday":4,"timeStart":"11:00","timeEnd":"19:00"},"4":{"weekday":5,"timeStart":"11:00","timeEnd":"17:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'minervalibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"16:00"},"1":{"weekday":2,"timeStart":"16:00","timeEnd":"20:00"},"2":{"weekday":4,"timeStart":"09:00","timeEnd":"16:00"},"3":{"weekday":4,"timeStart":"18:00","timeEnd":"20:00"},"4":{"weekday":6,"timeStart":"09:00","timeEnd":"12:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'myerslibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"13:00"},"1":{"weekday":1,"timeStart":"15:00","timeEnd":"18:00"},"2":{"weekday":2,"timeStart":"10:00","timeEnd":"13:00"},"3":{"weekday":2,"timeStart":"15:00","timeEnd":"18:00"},"4":{"weekday":3,"timeStart":"10:00","timeEnd":"13:00"},"5":{"weekday":3,"timeStart":"15:00","timeEnd":"18:00"},"6":{"weekday":4,"timeStart":"10:00","timeEnd":"13:00"},"7":{"weekday":4,"timeStart":"15:00","timeEnd":"18:00"},"8":{"weekday":5,"timeStart":"10:00","timeEnd":"13:00"},"9":{"weekday":5,"timeStart":"15:00","timeEnd":"18:00"},"10":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'oleanlibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"09:00","timeEnd":"21:00"},"1":{"weekday":2,"timeStart":"09:00","timeEnd":"21:00"},"2":{"weekday":3,"timeStart":"09:00","timeEnd":"21:00"},"3":{"weekday":4,"timeStart":"09:00","timeEnd":"21:00"},"4":{"weekday":5,"timeStart":"09:00","timeEnd":"18:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"17:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'pattersonlibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"18:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"20:00"},"2":{"weekday":3,"timeStart":"10:00","timeEnd":"18:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"20:00"},"4":{"weekday":5,"timeStart":"10:00","timeEnd":"18:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'portvillelibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"13:00","timeEnd":"20:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"17:00"},"2":{"weekday":4,"timeStart":"13:00","timeEnd":"20:00"},"3":{"weekday":5,"timeStart":"09:00","timeEnd":"17:00"},"4":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'prendergastlibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"09:00","timeEnd":"19:00"},"1":{"weekday":2,"timeStart":"09:00","timeEnd":"19:00"},"2":{"weekday":3,"timeStart":"09:00","timeEnd":"19:00"},"3":{"weekday":4,"timeStart":"09:00","timeEnd":"19:00"},"4":{"weekday":5,"timeStart":"09:00","timeEnd":"19:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"16:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'randolphlibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"12:00","timeEnd":"20:00"},"1":{"weekday":2,"timeStart":"12:00","timeEnd":"20:00"},"2":{"weekday":3,"timeStart":"16:00","timeEnd":"20:00"},"3":{"weekday":4,"timeStart":"12:00","timeEnd":"20:00"},"4":{"weekday":5,"timeStart":"10:00","timeEnd":"17:00"},"5":{"weekday":6,"timeStart":"10:00","timeEnd":"15:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'ripleylibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"10:00","timeEnd":"17:00"},"1":{"weekday":2,"timeStart":"10:00","timeEnd":"19:30"},"2":{"weekday":3,"timeStart":"10:00","timeEnd":"17:00"},"3":{"weekday":4,"timeStart":"10:00","timeEnd":"19:30"},"4":{"weekday":5,"timeStart":"10:00","timeEnd":"17:00"},"5":{"weekday":6,"timeStart":"09:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'salamancalibrary.org':
                $libhours = '{"0":{"weekday":2,"timeStart":"09:00","timeEnd":"20:00"},"1":{"weekday":3,"timeStart":"09:00","timeEnd":"19:00"},"2":{"weekday":4,"timeStart":"09:00","timeEnd":"19:00"},"3":{"weekday":5,"timeStart":"09:00","timeEnd":"16:00"},"4":{"weekday":6,"timeStart":"09:00","timeEnd":"14:30"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'senecalibraries.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"08:00","timeEnd":"17:00"},"1":{"weekday":2,"timeStart":"08:00","timeEnd":"19:00"},"2":{"weekday":3,"timeStart":"08:00","timeEnd":"19:00"},"3":{"weekday":4,"timeStart":"08:00","timeEnd":"17:00"},"4":{"weekday":5,"timeStart":"08:00","timeEnd":"17:00"},"5":{"weekday":6,"timeStart":"09:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'sinclairvillelibrary.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"14:00","timeEnd":"19:00"},"1":{"weekday":3,"timeStart":"09:00","timeEnd":"17:00"},"2":{"weekday":4,"timeStart":"14:00","timeEnd":"20:00"},"3":{"weekday":5,"timeStart":"10:00","timeEnd":"16:00"},"4":{"weekday":6,"timeStart":"10:00","timeEnd":"13:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'stocktonlibraries.org':
                $libhours = '{"0":{"weekday":2,"timeStart":"13:00","timeEnd":"19:00"},"1":{"weekday":4,"timeStart":"13:00","timeEnd":"19:00"},"2":{"weekday":5,"timeStart":"13:00","timeEnd":"17:00"},"3":{"weekday":6,"timeStart":"10:00","timeEnd":"14:00"}}';
                $libholi = '{}';
                $libir = '{}';
                break;

            case
            'wnyls.org':
                $libhours = '{"0":{"weekday":1,"timeStart":"09:00","timeEnd":"17:00"},"1":{"weekday":2,"timeStart":"09:00","timeEnd":"17:00"},"2":{"weekday":3,"timeStart":"09:00","timeEnd":"17:00"},"3":{"weekday":4,"timeStart":"09:00","timeEnd":"17:00"},"4":{"weekday":5,"timeStart":"09:00","timeEnd":"17:00"}}';
                $libholi = '{"0":{"name":"Closed - Martin Luther King, Jr. Day","dateStart":"2022-01-17","dateEnd":"2022-01-17"},"1":{"name":"Closed - Presidents\u2019 Day","dateStart":"2022-02-21","dateEnd":"2022-02-21"},"2":{"name":"Closed - Good Friday","dateStart":"2022-04-15","dateEnd":"2022-04-15"},"3":{"name":"Closed - Memorial Day","dateStart":"2022-05-30","dateEnd":"2022-05-30"},"4":{"name":"Closed - Juneteenth","dateStart":"2022-06-20","dateEnd":"2022-06-20"},"5":{"name":"Closed - Independence Day","dateStart":"2022-07-04","dateEnd":"2022-07-04"},"6":{"name":"Closed - Labor Day","dateStart":"2022-09-05","dateEnd":"2022-09-05"},"7":{"name":"Closed - Columbus Day","dateStart":"2022-10-10","dateEnd":"2022-10-10"},"8":{"name":"Closed - Veterans Day","dateStart":"2022-11-11","dateEnd":"2022-11-11"},"9":{"name":"Closed - Thanksgiving","dateStart":"2022-11-24","dateEnd":"2022-11-25"},"10":{"name":"Closed - Christmas","dateStart":"2022-12-23","dateEnd":"2022-12-26"},"11":{"name":"Closed - New Years","dateStart":"2022-12-30","dateEnd":"2023-01-02"}}';
                $libir = '{}';
                break;

            default:
                $libhours = '{"0":{"weekday":1,"timeStart":"09:00","timeEnd":"17:00"},"1":{"weekday":2,"timeStart":"09:00","timeEnd":"17:00"},"2":{"weekday":3,"timeStart":"09:00","timeEnd":"17:00"},"3":{"weekday":4,"timeStart":"09:00","timeEnd":"17:00"},"4":{"weekday":5,"timeStart":"09:00","timeEnd":"17:00"}}';
                $libholi = '{"0":{"name":"Closed - Martin Luther King, Jr. Day","dateStart":"2022-01-17","dateEnd":"2022-01-17"},"1":{"name":"Closed - Presidents\u2019 Day","dateStart":"2022-02-21","dateEnd":"2022-02-21"},"2":{"name":"Closed - Good Friday","dateStart":"2022-04-15","dateEnd":"2022-04-15"},"3":{"name":"Closed - Memorial Day","dateStart":"2022-05-30","dateEnd":"2022-05-30"},"4":{"name":"Closed - Juneteenth","dateStart":"2022-06-20","dateEnd":"2022-06-20"},"5":{"name":"Closed - Independence Day","dateStart":"2022-07-04","dateEnd":"2022-07-04"},"6":{"name":"Closed - Labor Day","dateStart":"2022-09-05","dateEnd":"2022-09-05"},"7":{"name":"Closed - Columbus Day","dateStart":"2022-10-10","dateEnd":"2022-10-10"},"8":{"name":"Closed - Veterans Day","dateStart":"2022-11-11","dateEnd":"2022-11-11"},"9":{"name":"Closed - Thanksgiving","dateStart":"2022-11-24","dateEnd":"2022-11-25"},"10":{"name":"Closed - Christmas","dateStart":"2022-12-23","dateEnd":"2022-12-26"},"11":{"name":"Closed - New Years","dateStart":"2022-12-30","dateEnd":"2023-01-02"}}';
                $libir = '{}';
                break;
        }

        $libhours = ltrim($libhours, "'");
        $libhours = rtrim($libhours, "'");
        $libholi = ltrim($libholi, "'");
        $libholi = rtrim($libholi, "'");
        $libir = ltrim($libir, "'");
        $libir = rtrim($libir, "'");

        wp_insert_post(
            array(
                'post_date' => '2000-01-01 00:00:01',
              'post_date_gmt' => '2000-01-01 00:00:01',
                'post_title' => 'Hours',
                'post_status' => 'publish',
                'post_type' => 'op-set',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_name' => 'hours',
                'meta_input' => array(
                    '_edit_last' => '1',
                    '_op_set_periods' => json_decode($libhours, true),
                    '_op_set_holidays' => json_decode($libholi, true),
                    '_op_set_irregular_openings' => json_decode($libir, true),
                    '_op_meta_box_set_details_description',
                    '_op_meta_box_set_details_dateStart',
                    '_op_meta_box_set_details_dateEnd',
                    '_op_meta_box_set_details_weekScheme',
                    '_op_meta_box_set_details_alias',
                    '_op_meta_box_set_details_childSetNotice',
                    '_edit_lock'
            ))
        );
    }
}
