<?php
/**
 * @since 1.2
 */
defined('ABSPATH') || exit;

class PZZ_API_WC_REST_Product_Attribute_Terms_Controller extends WC_REST_Product_Attribute_Terms_Controller
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
    protected $rest_base = 'wc/products/attributes/(?P<attribute_id>[\d]+)/terms';

    public function pzz_api_register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                'args' => array(
                    'attribute_id' => array(
                        'description' => __('Unique identifier for the attribute of the terms.', 'woocommerce'),
                        'type'        => 'integer',
                    ),
                ),
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
                    'attribute_id' => array(
                        'description' => __('Unique identifier for the attribute of the terms.', 'woocommerce'),
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
     * Check permissions.
     *
     * @param WP_REST_Request $request Full details about the request.
     * @param string          $context Request context.
     * @return bool|WP_Error
     */
    protected function check_permissions($request, $context = 'read')
    {
        add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
            if ('read' === $context) {
                return true;
            }
            return $permission;
        }, 10, 4);
        return parent::check_permissions($request, $context);
    }
}
