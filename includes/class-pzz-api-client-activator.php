<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Pzz_Api_Client
 * @subpackage Pzz_Api_Client/includes
 * @author     MJHP <mjavadhpour@gmail.com>
 */
class Pzz_Api_Client_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		/**
		 * Detect plugin. For use in Admin area only.
		 */
		if ( is_plugin_active( 'reactor-core/reactor-core.php' ) ) {
			wp_die(
				'<h1>Oops! 🤦‍♂️</h1>' .
				'<p>' . sprintf(
					__( 'Our plugin has a conflict with <a href="%s">Reactor: Core</a>. <b>please remove or deactivate it and try again.</b>' ),
					'https://wordpress.org/plugins/reactor-core/'
				) . '</p>'
				. '<p>' .
					__( 'This is because some of our functions and APIs have an internal conflict with this plugin, we sorry about that and this will be fixed later.' )
				. '</p>' 
				. '<p>' .
				sprintf(
					__('<a href="%s">Back</a>'),
					self_admin_url( "plugins.php" )
				)
				. '</p>' 
			);
		} 

	}

}
