<?php
/**
 * @since 1.2
 */
defined('ABSPATH') || exit;

class PZZ_API_WC_REST_Products_Controller extends WC_REST_Products_Controller
{
    protected $namespace = 'pzz/v1';

    protected $rest_base = 'wc/products';

    public function pzz_api_register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array( $this, 'get_items' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args' => $this->get_collection_params(),
                ),
                'schema' => array( $this, 'get_public_item_schema' ),
            )
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_item' ),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                    'args'                => array(
                        'context' => $this->get_context_param(
                            array(
                                'default' => 'view',
                            )
                        ),
                    ),
                ),
                'args'   => array(
                    'id' => array(
                        'description' => __('Unique identifier for the resource.', 'woocommerce'),
                        'type'        => 'integer',
                    ),
                ),
                'schema' => array( $this, 'get_public_item_schema' ),
            )
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/favourites/(?P<id>[\d]+)',
            array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'add_favourite' ),
                    'permission_callback' => array( $this, 'add_favourite_permissions_check' ),
                    'args' => [
                        'id' => [
                            'description' => __('Unique identifier for the resource.', 'woocommerce'),
                            'type'        => 'integer',
                        ],
                    ],
                ),
                array(
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => array( $this, 'remove_favourite' ),
                    'permission_callback' => array( $this, 'remove_favourite_permissions_check' ),
                    'args' => [
                        'id' => [
                            'description' => __('Unique identifier for the resource.', 'woocommerce'),
                            'type'        => 'integer',
                        ],
                    ],
                ),
            )
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/favourites',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array( $this, 'get_favourites' ),
                    'permission_callback' => array( $this, 'get_favourites_permissions_check' ),
                ),
            )
        );
    }

    public function add_favourite_permissions_check($request)
    {
        return 0 !== get_current_user_id();
    }

    public function remove_favourite_permissions_check($request)
    {
        return 0 !== get_current_user_id();
    }

    public function get_favourites_permissions_check($request)
    {
        return $this->get_items_permissions_check($request);
    }

    /**
     * Check if a given request has access to read items.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_items_permissions_check($request)
    {
        add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
            if ('product' === $post_type && 'read' === $context) {
                return true;
            }
            return $permission;
        }, 10, 4);

        return parent::get_items_permissions_check($request);
    }

    /**
     * Check if a given request has access to read item.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_item_permissions_check($request)
    {
        add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
            if ('product' === $post_type && 'read' === $context) {
                return true;
            }
            return $permission;
        }, 10, 4);

        return parent::get_item_permissions_check($request);
    }

    public function add_favourite($request)
    {
        $prod_id        = sanitize_text_field($request['id']);
        $favourites     = $this->do_get_favourites();
        if (in_array($prod_id, $favourites)) {
            return new WP_REST_Response(['success' => false, 'error' => 'it was added']);
        }
        $this->pzz_do_add_favourite($prod_id, $favourites, true);
        return new WP_REST_Response(['success' => true]);

        // new WP_Error( 'cant-update', __( 'message', 'text-domain' ), array( 'status' => 500 ) );
    }

    public function remove_favourite($request)
    {
        $prod_id = (int)sanitize_text_field($request['id']);
        $this->pzz_do_remove_favourite($prod_id);
        return new WP_REST_Response(['success' => true]);
    }

    /**
     * Get a collection of posts.
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_favourites($request)
    {
        $request['include'] = array_values($this->do_get_favourites());

        if (empty($request['include'])) {
            return [];
        }
        $request['context'] = 'view';
        $request['status'] = 'publish';
        return parent::get_items($request);
    }

    public function get_items($request)
    {
        $request['context'] = 'view';
        $request['status'] = 'publish';
        return parent::get_items($request);
    }

    public function get_item($request)
    {
        $request['context'] = 'view';
        $request['status'] = 'publish';
        return parent::get_item($request);
    }

    private function do_get_favourites()
    {
        $user_id = get_current_user_id();
        $favourites = get_user_meta($user_id, '_pzz_favourites_string', true);

        if (empty($favourites)) {
            $favourites = [];
        }
        $favourites = $this->pzz_check_favourites_products($favourites);
        return $favourites;
    }

    private function pzz_check_favourites_products($favourites)
    {
        $flag = false;
        foreach ($favourites as $key => $id) {
            if (false === get_post_status($id) || 'trash' === get_post_status($id)) {
                unset($favourites[$key]);
                $flag = true;
            }
        }
        if ($flag) {
            $this->pzz_update_favourites($favourites);
        }
        return $favourites;
    }

    private function pzz_do_add_favourite($product_id, $favourites, $update = false)
    {
        if (! in_array($product_id, $favourites)) {
            array_push($favourites, $product_id);
            if ($update) {
                $this->pzz_update_favourites($favourites);
            }
        }
        return $favourites;
    }

    private function pzz_do_remove_favourite($product_id)
    {
        $user_id    = get_current_user_id();
        $favourites = get_user_meta($user_id, '_pzz_favourites_string', true);
        if (($key = array_search($product_id, $favourites)) !== false) {
            unset($favourites[$key]);
        }
        $this->pzz_update_favourites($favourites, $user_id);
    }

    private function pzz_update_favourites($favourites, $user_id = false)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        update_user_meta($user_id, '_pzz_favourites_string', $favourites);
    }
}
