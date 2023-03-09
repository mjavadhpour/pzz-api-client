<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://profiles.wordpress.org/mjavadhpour/
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
 * @author     @mjavadhpour on WordPress.org
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
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * Exception classes.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/exceptions/class-pzz-exception.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-pzz-api-client-loader.php';

        /**
         * TODO: Refactor the controller includes process. @see {Automattic\WooCommerce\RestApi\Server}
         */

        /**
         *  The helper classes.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-pzz-array.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-pzz-datetime.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-pzz-url.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-pzz-mysql.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-pzz-post.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-pzz-json-comments.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-pzz-json-meta.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-pzz-json-meta-posts.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-pzz-json-responseinterface.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-pzz-json-response.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-pzz-json-users.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-pzz-json-media.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/lib/class-pzz-json-taxonomies.php';

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
        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'build_route',
            'component' => [
                'class' => 'PZZ_API_Controller',
                'path' => 'includes/lib/api/class-pzz-api-controller.php',
            ],
            'api_route' => 'posts',
            'api_function_name' => 'get_posts',
            'api_version' => $this->get_api_version('wp'),
            'args' => function () {
                return array();
            },
        ];

        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'build_route',
            'component' => [
                'class' => 'PZZ_API_Controller',
                'path' => 'includes/lib/api/class-pzz-api-controller.php',
            ],
            'api_route' => 'taxonomies/(?P<taxonomy_type>\w+)',
            'api_function_name' => 'get_taxonomies',
            'api_version' => $this->get_api_version('wp'),
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
            'api_method' => 'GET',
            'callback' => 'build_route',
            'component' => [
                'class' => 'PZZ_API_Controller',
                'path' => 'includes/lib/api/class-pzz-api-controller.php',
            ],
            'api_route' => 'posts/(?P<id>\d+)/comments',
            'api_function_name' => 'get_post_comments',
            'api_version' => $this->get_api_version('wp'),
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
            'api_method' => 'GET',
            'callback' => 'build_route',
            'component' => [
                'class' => 'PZZ_API_Controller',
                'path' => 'includes/lib/api/class-pzz-api-controller.php',
            ],
            'api_route' => 'posts/(?P<id>\d+)',
            'api_function_name' => 'get_post',
            'api_version' => $this->get_api_version('wp'),
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
            'api_method' => 'GET',
            'callback' => 'build_route',
            'component' => [
                'class' => 'PZZ_API_Controller',
                'path' => 'includes/lib/api/class-pzz-api-controller.php',
            ],
            'api_route' => 'whoami',
            'api_function_name' => 'get_current_logged_in_user_info',
            'api_version' => $this->get_api_version('wp'),
            'args' => function () {
                return (array(
                    'Authorization' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_string($param);
                        },
                        'description' => 'JWT Bearer token should be placed in the request header.',
                        'type' => 'string'
                    ),
                ));
            },
            'is_secure' => true
        ];

        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'pzz_api_register_routes',
            'component' => [
                'class' => 'PZZ_API_WC_REST_Products_Controller',
                'path' => 'includes/lib/api/class-pzz-api-wc-rest-products-controller.php',
            ],
            'api_route' => 'whoami',
            'api_function_name' => 'get_current_logged_in_user_info',
            'api_version' => $this->get_api_version('wp'),
            'depend_on' => ['woocommerce/woocommerce.php'],
        ];

        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'pzz_api_register_routes',
            'component' => [
                'class' => 'PZZ_API_WC_REST_Product_Categories_Controller',
                'path' => 'includes/lib/api/class-pzz-api-wc-rest-product-categories-controller.php',
            ],
            'api_route' => 'whoami',
            'api_function_name' => 'get_current_logged_in_user_info',
            'api_version' => $this->get_api_version('wp'),
            'depend_on' => ['woocommerce/woocommerce.php'],
        ];

        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'pzz_api_register_routes',
            'component' => [
                'class' => 'PZZ_API_WC_REST_Product_Variations_Controller',
                'path' => 'includes/lib/api/class-pzz-api-wc-rest-product-variations-controller.php',
            ],
            'api_route' => 'whoami',
            'api_function_name' => 'get_current_logged_in_user_info',
            'api_version' => $this->get_api_version('wp'),
            'depend_on' => ['woocommerce/woocommerce.php'],
        ];

        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'pzz_api_register_routes',
            'component' => [
                'class' => 'PZZ_API_WC_REST_Product_Tags_Controller',
                'path' => 'includes/lib/api/class-pzz-api-wc-rest-product-tags-controller.php',
            ],
            'api_route' => 'whoami',
            'api_function_name' => 'get_current_logged_in_user_info',
            'api_version' => $this->get_api_version('wp'),
            'depend_on' => ['woocommerce/woocommerce.php'],
        ];

        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'pzz_api_register_routes',
            'component' => [
                'class' => 'PZZ_API_WC_REST_Product_Attributes_Controller',
                'path' => 'includes/lib/api/class-pzz-api-wc-rest-product-attributes-controller.php',
            ],
            'api_route' => 'whoami',
            'api_function_name' => 'get_current_logged_in_user_info',
            'api_version' => $this->get_api_version('wp'),
            'depend_on' => ['woocommerce/woocommerce.php'],
        ];

        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'pzz_api_register_routes',
            'component' => [
                'class' => 'PZZ_API_WC_REST_Product_Attribute_Terms_Controller',
                'path' => 'includes/lib/api/class-pzz-api-wc-rest-product-attribute-terms-controller.php',
            ],
            'api_route' => 'whoami',
            'api_function_name' => 'get_current_logged_in_user_info',
            'api_version' => $this->get_api_version('wp'),
            'depend_on' => ['woocommerce/woocommerce.php'],
        ];

        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'pzz_api_register_routes',
            'component' => [
                'class' => 'PZZ_API_WC_REST_Orders_Controller',
                'path' => 'includes/lib/api/class-pzz-api-wc-rest-orders-controller.php',
            ],
            'api_route' => 'whoami',
            'api_function_name' => 'get_current_logged_in_user_info',
            'api_version' => $this->get_api_version('wp'),
            'depend_on' => ['woocommerce/woocommerce.php'],
        ];

        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'pzz_api_register_routes',
            'component' => [
                'class' => 'PZZ_API_WC_REST_Product_Reviews_Controller',
                'path' => 'includes/lib/api/class-pzz-api-wc-rest-products-review-controller.php',
            ],
            'api_route' => 'whoami',
            'api_function_name' => 'get_current_logged_in_user_info',
            'api_version' => $this->get_api_version('wp'),
            'depend_on' => ['woocommerce/woocommerce.php'],
        ];

        $routes[] = [
            'api_method' => 'GET',
            'callback' => 'pzz_api_register_routes',
            'component' => [
                'class' => 'PZZ_API_WC_REST_Report_Top_Sellers_Controller',
                'path' => 'includes/lib/api/class-pzz-api-wc-rest-report-top-sellers-controller.php',
            ],
            'api_route' => 'whoami',
            'api_function_name' => 'get_current_logged_in_user_info',
            'api_version' => $this->get_api_version('wp'),
            'depend_on' => ['woocommerce/woocommerce.php'],
        ];

        /**
         * @since    1.2.8 Rename variables
         * @since    1.2.0 Get handler from $routes.
         * @since    1.0.0
         */
        foreach ($routes as $route) {
            $this->loader->add_rest_api_action(
                'rest_api_init',
                $route['component'],
                $route['callback'],
                $route['api_route'],
                $route['api_method'],
                $route['api_function_name'],
                is_callable($route['args'] ?? '') ? $route['args']() : null,
                $route['is_secure'] ?? null,
                $route['depend_on'] ?? [],
            );
        }
    }

    /**
     * Register the default JSON API filters.
     *
     * @since 1.0.0
     *
     * @internal This will live in default-filters.php
     *
     * @global PZZ_JSON_Media      $pzz_json_media
     * @global PZZ_JSON_Taxonomies $pzz_json_taxonomies
     *
     */
    private function json_api_default_filters()
    {
        global $wp_json_pages, $pzz_json_media, $pzz_json_taxonomies;

        /**
         * Users.
         *
         * @since 1.0.0
         */
        $pzz_json_users = new PZZ_JSON_Users();
        $this->loader->add_filter('pzz_prepare_post', $pzz_json_users, 'add_post_author_data', 10, 3);
        $this->loader->add_filter('pzz_prepare_comment', $pzz_json_users, 'add_comment_author_data', 10, 3);

        /**
         * Post meta.
         *
         * @since 1.0.0
         */
        $pzz_json_post_meta = new PZZ_JSON_Meta_Posts();
        $this->loader->add_filter('pzz_prepare_post', $pzz_json_post_meta, 'add_post_meta_data', 10, 3);

        /**
         * Media.
         *
         * @since 1.0.0
         */
        $pzz_json_media = new PZZ_JSON_Media();
        $this->loader->add_filter('pzz_prepare_post', $pzz_json_media, 'add_thumbnail_data', 10, 3);

        /**
         * Posts.
         *
         * @since 1.0.0
         */
        $pzz_json_taxonomies = new PZZ_JSON_Taxonomies();
        $this->loader->add_filter('pzz_prepare_post', $pzz_json_taxonomies, 'add_term_data', 10, 3);
        $this->loader->add_filter('pzz_get_taxonomies', $pzz_json_taxonomies, 'get_items', 10, 1);

        /**
         * Post comments.
         *
         * @since 1.0.0
         */
        $pzz_json_comments = new PZZ_JSON_Comments();
        $this->loader->add_filter('pzz_prepare_post_comments', $pzz_json_comments, 'get_comments', 10, 1);

        /**
         * Post links.
         *
         * @since 1.1.6
         */
        $wp_json_post = new PZZ_Post_Helper();
        $this->loader->add_isolated_filter('the_content', $wp_json_post, 'add_target_blank_to_links', 10, 1);

        add_filter('rest_request_before_callbacks', function ($response, $component, $request) {
            $route = $request->get_route();
            $params = $request->get_params();
            $isFromPzzApp = is_array($params) && isset($params['pzz']) && 'pzz' === $params['pzz'];
            preg_match('/^\/wc\/store\/cart.*/', $route, $cartMathces);
            preg_match('/^\/wc\/store\/checkout.*/', $route, $checkoutMatches);
            preg_match('/^\/wc\/store\/add-item.*/', $route, $addItemMatches);
            if (boolval(count($cartMathces) + count($checkoutMatches) + count($addItemMatches)) && $isFromPzzApp) {
                add_filter('woocommerce_store_api_disable_nonce_check', '__return_true');
            }
            return $response;
        }, 10, 3);

        add_filter('restricted_site_access_user_can_access', function () {
            global $wp;
            $route = $wp->request;
            if (empty(trim($route))) {
                return null;
            }
            preg_match('/^wp\-json\/pzz\/v1.*/', $route, $mathces);
            if (is_array($mathces) && boolval(count($mathces))) {
                return true;
            }
            return null;
        }, 10, 3);
    }

    /**
     * Resolve the API version with the plugin version.
     *
     * @since     1.2.0     Add $group to handle woocommerce endpoints
     *                      inside wordpress endpoints.
     * @since     1.0.0
     * @return    string    The version number of the api.
     */
    private function get_api_version($group = null)
    {
        /**
         * As we should handle API version separately from plugin version,
         * then we should not depend on the plugin version, @since 1.1.1 we
         * hard code the API version instead of resolving it from plugin
         * version.
         */
        switch ($group) {
            case 'wp':
                return '1';
            case 'wc':
                return '1';
            default:
                return '1';
        }
    }
}
