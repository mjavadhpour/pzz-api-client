<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/mjhpour
 * @since             1.0.0
 * @package           Pzz_Api_Client
 *
 * @wordpress-plugin
 * Plugin Name:       Puzzley WordPress Simple API
 * Plugin URI:        https://puzzley.ir/1398/01/27/%D8%A7%D9%81%D8%B2%D9%88%D9%86%D9%87-%D9%88%D8%B1%D8%AF%D9%BE%D8%B1%D8%B3/
 * Description:       این افزونه ارائه دهنده <strong>رابط برنامه‌نویسی وب</strong> به صورت ساده شده برای استفاده در <a href="https://puzzley.ir" target="_blank">اپلیکیشن‌ساز آنلاین پازلی</a> است
 * Version:           2.0.0
 * Author:            MJavad Hpour
 * Author URI:        https://www.linkedin.com/in/mjavadhpour/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pzz-api-client
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	echo 'Read this note from deep inside!  I\'m just a plugin, direct access can hurt me, leave me alone in the darkness.';
	exit;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Update it as you release new versions.
 * 
 * @since 1.0.0
 */
define( 'PZZ_API_CLIENT_VERSION', '2.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pzz-api-client-activator.php
 * 
 * @since 1.0.0
 */
function activate_pzz_api_client() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pzz-api-client-activator.php';
	Pzz_Api_Client_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pzz-api-client-deactivator.php
 * 
 * @since 1.0.0
 */
function deactivate_pzz_api_client() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pzz-api-client-deactivator.php';
	Pzz_Api_Client_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pzz_api_client' );
register_deactivation_hook( __FILE__, 'deactivate_pzz_api_client' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 * 
 * @since 1.0.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pzz-api-client.php';

/**
 * Check if we have permission to interact with the post object.
 *
 * @since 1.0.0
 * @param WP_Post $post Post object.
 * @param string $capability Permission to check.
 * @return boolean Can we interact with it?
 */
function json_check_post_permission( $post, $capability = 'read' ) {
	$permission = false;
	$post_type = get_post_type_object( $post['post_type'] );

	switch ( $capability ) {
		case 'read' :
			if ( ! $post_type->show_in_rest ) {
				return false;
			}

			if ( 'publish' === $post['post_status'] || current_user_can( $post_type->cap->read_post, $post['ID'] ) ) {
				$permission = true;
			}

			// Can we read the parent if we're inheriting?
			if ( 'inherit' === $post['post_status'] && $post['post_parent'] > 0 ) {
				$parent = get_post( $post['post_parent'], ARRAY_A );

				if ( json_check_post_permission( $parent, 'read' ) ) {
					$permission = true;
				}
			}

			// If we don't have a parent, but the status is set to inherit, assume
			// it's published (as per get_post_status())
			if ( 'inherit' === $post['post_status'] ) {
				$permission = true;
			}
			break;

		case 'edit' :
			if ( current_user_can( $post_type->cap->edit_post, $post['ID'] ) ) {
				$permission = true;
			}
			break;

		case 'create' :
			if ( current_user_can( $post_type->cap->create_posts ) || current_user_can( $post_type->cap->edit_posts ) ) {
				$permission = true;
			}
			break;

		case 'delete' :
			if ( current_user_can( $post_type->cap->delete_post, $post['ID'] ) ) {
				$permission = true;
			}
			break;

		default :
			if ( current_user_can( $post_type->cap->$capability ) ) {
				$permission = true;
			}
	}

	return apply_filters( "json_check_post_{$capability}_permission", $permission, $post );
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pzz_api_client() {

	$plugin = new Pzz_Api_Client();
	$plugin->run();

}

run_pzz_api_client();
