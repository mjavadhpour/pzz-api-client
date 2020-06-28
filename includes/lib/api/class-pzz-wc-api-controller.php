<?php

/**
 * The woocommerce core functionality of the plugin.
 *
 * @since      1.2.0
 * @package    PZZ_WC_API_Controller
 * @author     MJHP <mjavadhpour@gmail.com>
 * 
 * TODO: Check for refactor with this link: <a>https://upnrunn.com/blog/2018/04/how-to-extend-wp-rest-api-from-your-custom-plugin-part-3/</a>
 */
class PZZ_WC_API_Controller {

	/**
	 * The namespace of APIs.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      string    $namespace    The namespace of APIs.
	 */
	private $namespace;

	/**
	 * The version of the APIs.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      string    $version    The version of the APIs.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.2.0
	 * @param      string                   $plugin_name       The namespace of APIs.
	 * @param      string                   $version           The version of the APIs.
	 */
	public function __construct( $namespace = 'pzz', $version = '1' ) {

		define( 'DOING_AJAX', true );
		$this->namespace = $namespace;
		$this->version = $version;

	}

	/**
	 * Build API route with given arguments. the namespace and the version
	 * come from the inner class properties.
	 *
	 * @since    1.2.0
	 * @param    function    $callback    The function that was executed when the endpoint was called.
	 * @param    string      $path        The path of the API. You can start path with the "/" character or not; it was optional.
	 * @param    string      $method      The API HTTP method.
	 */
	public function build_route( $callback, $path, $method, $args = array(), $current_user, $is_secure = false ) {

		if ($args == null) {
			$args = array();
		}

		/**
		 * @since    1.2.0
		 */
		register_rest_route( $this->namespace . '/' . $this->get_version(), $path, array(
			'methods' => $method,
			'callback' => $callback,
			'args' => $args,
			'permission_callback' => function ( $test ) use ( $is_secure, $current_user ) {
				return !$is_secure || ( $is_secure && ( is_a( $current_user, 'WP_User' ) && $current_user->ID > 0 ) );
			}
		));

	}

	/**
	 * Get orders of current logged in user.
	 *
	 * @since    1.2.0
	 * @param    WP_REST_Request   $request      Wordpress rest request object; passed by the WordPress.
	 * @param    WP_User           $current_user Current logged in user.
	 */
	public function get_current_user_orders( WP_REST_Request $request, WP_User $user ) {		
		$filter = [
			'customer_id' => $user->ID
		];
		$fields = [];
		$page = 1;

		$orders_api = new PZZ_WC_API_Orders( new PZZ_WC_API_Server('/orders') );
        $orders = $orders_api->get_orders( $fields, $filter, null, $page );

		$this->send_success_response( $orders );
	}

	/**
	 * Checkout.
	 *
	 * @since    1.2.0
	 * @param    WP_REST_Request   $request      Wordpress rest request object; passed by the WordPress.
	 * @param    WP_User           $current_user Current logged in user.
	 */
	public function checkout_order( WP_REST_Request $request, WP_User $user ) {		
		define( 'WOOCOMMERCE_CHECKOUT', true );
		/**
		 * If requested data was null, just create a fake array to action, function properly.
		 */
		$data = $request->get_json_params() ?? ['checkout' => ['post_data' => ''], 'products' => []];
		/**
		 * Convert array to \stdClass|null
		 */
		$data_std = json_decode( json_encode( $data ) );
		$this->setup_cart_session( $data_std ?? new \stdClass );
		$this->virtually_fill_submit_data( wp_create_nonce('woocommerce-process_checkout'), '_wpnonce' );

		/**
		 * Map request data to $_POST required by WC_Checkout
		 */
		foreach ( $data['checkout'] as $key => $value ) {
			$this->virtually_fill_submit_data( $value, "billing_$key" );
			$this->virtually_fill_submit_data( $value, "shipping_$key" );
		}
		$this->virtually_fill_submit_data( '', 'order_comments' );
		if ( isset( $data_std->checkout->payment_method ) ) {
			$this->virtually_fill_submit_data( $data_std->checkout->payment_method, 'payment_method' );
		}
		if ( isset( $data_std->checkout->shipping_method ) ) {
			$this->virtually_fill_submit_data( $data_std->checkout->shipping_method, 'shipping_method' );
		}
		if ( isset( $data_std->checkout->terms ) && $data_std->checkout->terms ) {
			$this->virtually_fill_submit_data( 'on', 'terms' );
			$this->virtually_fill_submit_data( 1, 'terms-field' );
		}
		if (
			isset( $data_std->checkout->order_comments ) && 
			!empty( $data_std->checkout->order_comments )
		) {
			$this->virtually_fill_submit_data( $data_std->checkout->order_comments, 'order_comments' );
		}

		$checkout_service = new PZZ_WC_Checkout();
		$order_id = $checkout_service->process_checkout();

		if ( $order_id ) {
			$order = wc_get_order( $order_id );
			$result['order']['id'] = $order->get_id();
			$result['order']['order_number'] = $order->get_order_number();
			$result['order']['needs_payment'] = $order->needs_payment();
			$pay_url = $this->get_order_pay_url( $order );
			$result['order']['pay_url'] = $pay_url;
			$result['order']['received_url'] = $order->get_checkout_order_received_url();

			$this->send_success_response( $result );
		}

		$errors = wc_notice_count( 'error' ) ? wc_get_notices( 'error' ) : __( 'Error in process checkout' );
		$this->send_error_response( $errors );
	}

	/**
	 * Create new customer.
	 *
	 * @since    1.2.0
	 * @param    WP_REST_Request   $request      Wordpress rest request object; passed by the WordPress.
	 * @param    WP_User           $current_user Current logged in user.
	 */
	public function create_new_customer( WP_REST_Request $request, WP_User $user ) {
		add_filter('wp_recaptcha_required', function () { return false; }, 10);
		$data = $request->get_json_params();
		$request_uri = '/wp-json'.$request->get_route();
		$this->virtually_fill_submit_data( wp_create_nonce( 'woocommerce-register' ), 'woocommerce-register-nonce' );
		$this->virtually_fill_submit_data( esc_attr( wp_unslash( $request_uri ) ), '_wp_http_referer' );
		
		$customers_api = new PZZ_WC_API_Customers(new PZZ_WC_API_Server("/customers"));
		$result = $customers_api->create_customer($data);

		if ( is_wp_error( $result ) ) {
			$this->send_error_response( $result->get_error_message() );
		}
		
		$this->send_success_response( $result );
	}

	/**
	 * Create a conventional error response body.
	 * 
	 * @since  1.2.0
	 * @param  array|mixed $errors 
	 * @param  int         $status Error status code.
	 * @return array
	 */
	private function send_error_response( $errors, $status = 400 )
	{
		wp_send_json_error( array( 'error' => $errors ), $status );
	}

	/**
	 * Create a conventional success response body.
	 * 
	 * @since  1.2.0
	 * @param  array|mixed $data 
	 * @return array
	 */
	private function send_success_response( $data )
	{
		wp_send_json_success( $data );
	}

	/**
	 * As we use woocommerce classes sometimes we need to virtually
	 * fill the $_POST data to woocommerce function properly.
	 * 
	 * @since    1.2.0
	 * @param    mixed  $value
	 * @param    string $key
	 * @return   void
	 */
	private function virtually_fill_submit_data( $value, $key ) {
		$_POST[$key] = $value;
	}

	/**
	 * Get pay url for given order.
	 * 
	 * @since    1.2.0
	 * @param  WC_Order|bool $order
	 * @return string
	 */
	private function get_order_pay_url( $order ) {
        $pay_url = $order->get_checkout_payment_url();
        $pay_url = str_replace('pay_for_order=true&', '', $pay_url);
        return $pay_url;
	}
	
	/**
	 * Virtually create a cart session.
	 * 
	 * @since    1.2.0
	 * @param    \stdClass  $entity_body
	 * @return   void
	 */
    private function setup_cart_session( \stdClass $entity_body ) {
        WC()->cart->empty_cart( true );

        if (isset( $entity_body->products ) && is_array( $entity_body->products ) ) {
            foreach ( $entity_body->products as $product ) {
                $what_happened = PZZ_WC_Cart_Handler::add_to_cart_action( false, ( array ) $product );
            }
        }

        if ( isset( $entity_body->coupon_code ) && $entity_body->coupon_code ) {
            WC()->cart->add_discount(sanitize_text_field( $entity_body->coupon_code ) );
        } else {
            WC()->cart->remove_coupons();
        }

        WC()->cart->set_session();
        WC()->cart->calculate_totals();

		if ( isset( $entity_body->checkout->post_data ) ) {
			do_action( 'woocommerce_checkout_update_order_review', $entity_body->checkout->post_data );
		}

		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
		if ( isset( $entity_body->checkout->shipping_method ) && is_array( $entity_body->checkout->shipping_method )) {
			foreach ( $entity_body->checkout->shipping_method as $key => $value ) {
				$chosen_shipping_methods[$key] = wc_clean( $value );
			}
		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
		WC()->session->set( 
			'chosen_payment_method', 
			( isset( $entity_body->checkout->payment_method ) && $entity_body->checkout->payment_method ) ? 
				$entity_body->checkout->payment_method : '' 
		);

		if (isset($entity_body->checkout->country)) {
			WC()->customer->set_country($entity_body->checkout->country);
			WC()->customer->set_shipping_country($entity_body->checkout->country);
			WC()->customer->calculated_shipping(true);
		}

		foreach (['state', 'postcode', 'city', 'address', 'address_2'] as $value) {
			if (isset($entity_body->checkout->{$value})) {
				WC()->customer->{'set_'.$value}( $entity_body->checkout->{$value} );
				WC()->customer->{'set_shipping_'.$value}( $entity_body->checkout->{$value} );
			}
		}

		WC()->cart->calculate_totals();
    }

	/**
	 * Get version of API.
	 * 
	 * @since    1.2.0
	 * @return   string    The API version.
	 */
	private function get_version() {
		return 'v' . $this->version;
	}
}
