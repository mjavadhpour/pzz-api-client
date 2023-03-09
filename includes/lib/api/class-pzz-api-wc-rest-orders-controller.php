<?php
/**
 * REST API Orders controller
 *
 * Handles requests to the /wc/orders endpoint.
 *
 * @since    1.2.0
 */

defined('ABSPATH') || exit;

/**
 * REST API Orders controller class.
 *
 * @extends WC_REST_Orders_Controller
 */
class PZZ_API_WC_REST_Orders_Controller extends WC_REST_Orders_Controller
{
    /**
     * Endpoint namespace.
     * @var string
     */
    protected $namespace = 'pzz/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'wc/orders';

    /**
     * @since 1.2.0
     */
    public function pzz_api_register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
                'schema' => array( $this, 'get_public_item_schema' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            array(
                'args'   => array(
                    'id' => array(
                        'description' => __('Unique identifier for the resource.', 'woocommerce'),
                        'type'        => 'integer',
                    ),
                ),
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_item' ),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                    'args'                => array(
                        'context' => $this->get_context_param(array( 'default' => 'view' )),
                    ),
                ),
                'schema' => array( $this, 'get_public_item_schema' ),
            )
        );
    }

    // shop_order

    /**
     * Check if a given request has access to read an item.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_item_permissions_check($request)
    {
        add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
            if ('read' === $context) {
                $order_customer_id = $this->pzz_get_order_customer_id($object_id);
                $current_user_id = get_current_user_id();

                return intval($order_customer_id) === intval($current_user_id);
                // return current_user_can('read_order', $object_id);
            }
            return $permission;
        }, 10, 4);

        return parent::get_item_permissions_check($request);
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
            if ('read' === $context) {
                return true;
            }
            return $permission;
        }, 10, 4);

        return parent::get_items_permissions_check($request);
    }

    /**
     * Prepare objects query.
     *
     * @since  3.0.0
     * @param  WP_REST_Request $request Full details about the request.
     * @return array
     */
    protected function prepare_objects_query($request)
    {
        $request['customer'] = get_current_user_id();

        $args = parent::prepare_objects_query($request);

        return $args;
    }

    private function pzz_get_order_customer_id($order_id)
    {
        $order = wc_get_order($order_id);
        return $order->get_customer_id();
    }
}
