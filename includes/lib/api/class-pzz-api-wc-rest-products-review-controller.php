<?php
/**
 * @since 1.2
 */
defined('ABSPATH') || exit;

class PZZ_API_WC_REST_Product_Reviews_Controller extends WC_REST_Product_Reviews_Controller
{
    protected $namespace = 'pzz/v1';

    protected $rest_base = 'wc/products/(?P<product_id>[\d]+)/reviews';

    public function register_routes()
    {
    }

    /**
     * Register the routes for product reviews.
     */
    public function pzz_api_register_routes()
    {
        register_rest_route(
            $this->namespace,
            $this->rest_base,
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_item' ),
                    'permission_callback' => array( $this, 'create_item_permissions_check' ),
                    'args'                => array_merge(
                        $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
                        array(
                            'review'         => array(
                                'required'    => true,
                                'type'        => 'string',
                                'description' => __('Review content.', 'woocommerce'),
                            ),
                            'reviewer'       => array(
                                'required'    => !is_user_logged_in(),
                                'type'        => 'string',
                                'description' => __('Name of the reviewer.', 'woocommerce'),
                            ),
                            'reviewer_email' => array(
                                'required'    => !is_user_logged_in(),
                                'type'        => 'string',
                                'description' => __('Email of the reviewer.', 'woocommerce'),
                            ),
                        )
                    ),
                ),
                'schema' => array( $this, 'get_public_item_schema' ),
            )
        );
    }

    public function get_items($request)
    {
        $request = $this->add_product_filter_to_request($request, intval($request['product_id']));
        return parent::get_items($request);
    }

    public function create_item($request)
    {
        if (is_user_logged_in()) {
            $userdata = get_userdata(get_current_user_id());
            $request['reviewer'] = $userdata->display_name;
            $request['reviewer_email'] = $userdata->user_email;
        }
        return parent::create_item($request);
    }

    /**
     * Check whether a given request has permission to read webhook deliveries.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_items_permissions_check($request)
    {
        add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
            if ('product_review' === $post_type && 'read' === $context) {
                return true;
            }
            return $permission;
        }, 4, 10);

        if (! wc_rest_check_product_reviews_permissions('read')) {
            return new WP_Error('woocommerce_rest_cannot_view', __('Sorry, you cannot list resources.', 'woocommerce'), array( 'status' => rest_authorization_required_code() ));
        }

        return true;
    }

    /**
     * Check if a given request has access to create a new product review.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function create_item_permissions_check($request)
    {
        add_filter('woocommerce_rest_check_permissions', function ($permission, $context, $object_id, $post_type) {
            if ('product_review' === $post_type && 'create' === $context) {
                return true;
            }
            return $permission;
        }, 4, 10);

        if (! wc_rest_check_product_reviews_permissions('create')) {
            return new WP_Error('woocommerce_rest_cannot_create', __('Sorry, you are not allowed to create resources.', 'woocommerce'), array( 'status' => rest_authorization_required_code() ));
        }

        return true;
    }

    protected function add_product_filter_to_request($request, $product_id)
    {
        $request['product'] = [$product_id];
        $request['status'] = 'approved';
        return $request;
    }
}
