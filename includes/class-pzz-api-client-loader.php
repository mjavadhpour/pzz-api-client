<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://www.linkedin.com/in/mjavadhpour/
 * @since      1.0.0
 *
 * @package    Pzz_Api_Client
 * @subpackage Pzz_Api_Client/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Pzz_Api_Client
 * @subpackage Pzz_Api_Client/includes
 * @author     MJHP <mjavadhpour@gmail.com>
 */
class Pzz_Api_Client_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new route api
	 *
	 * @since    1.0.0
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    string               $namespace        The route namespase for API.
	 * @param    string               $version          The API version.
	 * @param    string               $path             The API url, when called, we execute the callback function.
	 * @param    string               $method           The API HTTP method (GET, POST, PUT, DELETE).
	 * @param    function             $args             The fucntion that return array of arguments.
	 */
	public function add_rest_api_action( $hook, $component, $callback, $api_route, $api_method, $api_function_name, $args ) {
		$this->actions = $this->add_rest_route( $this->actions, $hook, $component, $callback, $api_route, $api_method, $api_function_name, $args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		global $current_user;
		
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {

			if ($hook['type'] === 'rest_api_init') {

				add_action( 'rest_api_init', function() use ( $hook ) { 
					// Resolved as: new Pzz_Api_Client_Controller()->build_route()
					$hook['component']->{$hook['callback']}( 
						/**
						 * This function called as a callback function when API was called; and will returned
						 * the registered function to handle the request.
						 * 
						 * @param    WP_REST_Request    $request    Wordpress rest request object; 
						 *                                          passed by WordPress.
						 */
						function ( $request ) use ( $hook ) {

							// Resolved as: new Pzz_Api_Client_Controller()->api_function_name()
							return $hook['component']->{$hook['api_function_name']}( $request );
						}, 
						$hook['api_route'], 
						$hook['api_method'],
						$hook['args']
					); 
				});

				continue;

			}

			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

	}
	
	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args, $type = 'common' ) {
		
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
			'type'          => $type
		);

		return $hooks;

	}

	/**
	 * A utility function that is used to register the rest API route action into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks                The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook                 The name of the WordPress filter that is being registered.
	 * @param    object               $component            A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback             The name of the function definition on the $component.
	 * @param    string               $api_route            The path of API; SHOULD defined withoud verison and the namespace, just path.
	 * @param    string               $api_method           The HTTP method of API.
	 * @param    string               $api_function_name    The function name in the $component that was responsible to handle registered API.
	 * @param    function             $args                 The fucntion that return array of arguments.
	 * @return   array                                      The collection of actions and filters registered with WordPress.
	 */
	private function add_rest_route( $hooks, $hook, $component, $callback, $api_route, $api_method, $api_function_name, $args ) {
		
		$hooks[] = array(
			'hook'              => $hook,
			'component'         => $component,
			'callback'          => $callback,
			'api_route'         => $api_route,
			'api_method'        => $api_method,
			'api_function_name' => $api_function_name,
			'args'              => $args,
			'type'              => 'rest_api_init'
		);

		return $hooks;

	}

}
