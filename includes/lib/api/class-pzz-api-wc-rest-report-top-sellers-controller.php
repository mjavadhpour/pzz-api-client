<?php
/**
 * @since 1.2
 */
defined('ABSPATH') || exit;

class PZZ_API_WC_REST_Report_Top_Sellers_Controller extends WC_REST_Report_Top_Sellers_Controller
{
    protected $namespace = 'pzz/v1';

    protected $rest_base = 'wc/reports/top_sellers';

    public function register_routes()
    {
    }

    public function pzz_api_register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'                => $this->get_collection_params(),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ));
    }

    /**
     * Check whether a given request has permission to read report.
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
     * Get sales reports.
     *
     * @param WP_REST_Request $request
     * @return array|WP_Error
     */
    public function get_items($request)
    {
        add_filter('woocommerce_rest_prepare_report_top_sellers', function ($response, $top_seller, $request) {
            if (!empty($response)) {
                $response->remove_link('about');
            }
            return $response;
        }, 10, 3);
        return parent::get_items($request);
    }
}
