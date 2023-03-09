<?php
/**
 * @since 1.2
 */
defined('ABSPATH') || exit;

class PZZ_API_WC_REST_Product_Attributes_Controller extends WC_REST_Product_Attributes_Controller
{
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'pzz/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'wc/products/attributes';

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
                'args' => array(
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
                        'context'         => $this->get_context_param(array( 'default' => 'view' )),
                    ),
                ),
                'schema' => array( $this, 'get_public_item_schema' ),
            )
        );
    }

    /**
     * Check if a given request has access to read the attributes.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_items_permissions_check($request)
    {
        add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
            if ('attributes' === $post_type && 'read' === $context) {
                return true;
            }
            return $permission;
        }, 10, 4);
        return parent::get_items_permissions_check($request);
    }

    /**
     * Check if a given request has access to read a attribute.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_item_permissions_check($request)
    {
        add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
            if ('attributes' === $post_type && 'read' === $context) {
                return true;
            }
            return $permission;
        }, 10, 4);
        return parent::get_item_permissions_check($request);
    }
}
