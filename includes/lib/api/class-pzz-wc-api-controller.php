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
		 * @since    1.2.0 Add permission callback.
		 * @since    1.0.0
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
	 * Get orders of current logged in user
	 *
	 * @since    1.2.0
	 * @param    WP_REST_Request   $request      Wordpress rest request object; passed by the WordPress.
	 * @param    WP_User           $current_user Current logged in user.
	 */
	public function get_current_user_orders( $request, WP_User $user )
	{		
		$filter = [
			'customer_id' => $user->ID
		];

		$orders_api = new PZZ_WC_API_Orders(new PZZ_WC_API_Server('/orders'));
        $orders = $orders_api->get_orders($fields, $filter, null, $page);

		$response   = new PZZ_JSON_Response();
		$response->set_data( $orders );
        return $response;
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
