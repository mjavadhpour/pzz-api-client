<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linkedin.com/in/mjavadhpour/
 * @since      1.0.0
 *
 * @package    Pzz_Api_Client
 * @subpackage Pzz_Api_Client/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Pzz_Api_Client
 * @subpackage Pzz_Api_Client/includes
 * @author     MJHP <mjavadhpour@gmail.com>
 */
class Pzz_Api_Client
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Pzz_Api_Client_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('PZZ_API_CLIENT_VERSION')) {
            $this->version = PZZ_API_CLIENT_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'pzz-api-client';

        $this->load_dependencies();
        $this->register_apis();
        $this->json_api_default_filters();

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Pzz_Api_Client_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Pzz_Api_Client_Loader. Orchestrates the hooks of the plugin.
     * - Pzz_Api_Client_i18n. Defines internationalization functionality.
     * - Pzz_Api_Client_Admin. Defines all hooks for the admin area.
     * - Pzz_Api_Client_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pzz-api-client-loader.php';

        // require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/class-wp-json-posts.php';
        /**
         * The class responsible for create APIs and response to the API
         * calls.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-json-posts-controller.php';

        /**
         *  The helper classes.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-wp-json-comments.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-wp-json-meta.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-wp-json-meta-posts.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-wp-json-responseinterface.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-wp-json-response.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-wp-json-datetime.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-wp-json-users.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-wp-json-media.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-wp-json-taxonomies.php';

        $this->loader = new Pzz_Api_Client_Loader();

    }

    /**
     * Register all REST APIs routes and their callback functions.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_apis()
    {
        $core = new WP_JSON_Posts_Controller('pzz', $this->get_api_version());

        // TODO: I think that version SHOULD be separated from the plugin version
        // because maybe we want to handle two version of APIs in our latest plugin
        // version. The main idea is define the routes in the version key in the $routes
        // array.
        $routes[] = [
            'method' => 'GET',
            'path' => 'posts',
            'callback' => 'get_posts',
            'args' => function () {
                return array();
            },
        ];

        $routes[] = [
            'method' => 'GET',
            'path' => 'taxonomies/(?P<taxonomy_type>\w+)',
            'callback' => 'get_taxonomies',
            'args' => function () {
                return (array(
                    'taxonomy_type' => array(
                        'enum' => array(
                            'post_tag',
                            'category',
                        ),
                    ),
                ));
            },
        ];

        $routes[] = [
            'method' => 'GET',
            'path' => 'posts/(?P<id>\d+)/comments',
            'callback' => 'get_post_comments',
            'args' => function () {
                return (array(
                    'id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                    ),
                ));
            },
        ];

        $routes[] = [
            'method' => 'GET',
            'path' => 'posts/(?P<id>\d+)',
            'callback' => 'get_post',
            'args' => function () {
                return (array(
                    'id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                    ),
                ));
            },
        ];

        foreach ($routes as $route) {
            $this->loader->add_rest_api_action(
                'rest_api_init',
                $core,
                'build_route',
                $route['path'],
                $route['method'],
                $route['callback'],
                $route['args']()
            );
        }
    }

    /**
     * Register the default JSON API filters.
     *
     * @internal This will live in default-filters.php
     *
     * @global WP_JSON_Pages      $wp_json_pages
     * @global WP_JSON_Media      $wp_json_media
     * @global WP_JSON_Taxonomies $wp_json_taxonomies
     *
     */
    private function json_api_default_filters()
    {
        global $wp_json_pages, $wp_json_media, $wp_json_taxonomies;

        // Users.
        $wp_json_users = new WP_JSON_Users();
        $this->loader->add_filter('json_prepare_post', $wp_json_users, 'add_post_author_data', 10, 3);
        $this->loader->add_filter('json_prepare_comment', $wp_json_users, 'add_comment_author_data', 10, 3);

        // Post meta.
        $wp_json_post_meta = new WP_JSON_Meta_Posts();
        $this->loader->add_filter('json_prepare_post', $wp_json_post_meta, 'add_post_meta_data', 10, 3);

        // Media.
        $wp_json_media = new WP_JSON_Media();
        $this->loader->add_filter('json_prepare_post', $wp_json_media, 'add_thumbnail_data', 10, 3);

        // Posts.
        $wp_json_taxonomies = new WP_JSON_Taxonomies();
        $this->loader->add_filter('json_prepare_post', $wp_json_taxonomies, 'add_term_data', 10, 3);
        $this->loader->add_filter('json_get_taxonomies', $wp_json_taxonomies, 'get_items', 10, 1);

        // Post comments.
        $wp_json_comments = new WP_JSON_Comments();
        $this->loader->add_filter('json_prepare_post_comments', $wp_json_comments, 'get_comments', 10, 1);

        // Post links.
        $wp_json_post = new WP_JSON_Posts_Controller();
        $this->loader->add_filter('json_prepare_post', $wp_json_post, 'add_target_blank_to_links', 10, 3);
    }

    /**
     * Resolve the API version with the plugin version.
     *
     * @since     1.0.0
     * @return    string    The version number of the api.
     */
    private function get_api_version()
    {
        $version_array = explode(".", $this->get_version());

        return isset($version_array[0]) ? $version_array[0] : '1';

        // $resolved_version = '';
        // foreach ($version_array as $version) {
        //     $resolved_version = $resolved_version . $version;
        // }
        // $resolved_version = rtrim($resolved_version, '0');

        // $version_array = str_split($resolved_version);
        // $resolved_version = '';
        // foreach ($version_array as $version) {
        //     $resolved_version = $resolved_version . $version . '.';
        // }

        // // remove latest dot.
        // return substr_replace($resolved_version, "", -1);
    }

}
