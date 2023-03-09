<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/mjavadhpour/pzz-api-client
 * @since             1.0.0
 * @package           Pzz_Api_Client
 *
 * @wordpress-plugin
 * Plugin Name:       PZZ API Client
 * Plugin URI:        https://github.com/mjavadhpour/pzz-api-client
 * Description:       This plugin provides simple <strong>RESTful API</strong>, developed specifically for <strong>Mobile clients</strong> that want to connect to your WordPress website.
 * Version:           1.2.7
 * Author:            @mjavadhpour on WordPress.org
 * Author URI:        https://profiles.wordpress.org/mjavadhpour/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pzz-api-client
 * Domain Path:       /languages
 */

require_once __DIR__ . '/jwt.php';

function add_cors_http_header()
{
    header("Access-Control-Allow-Origin: *");
}
add_action('init', 'add_cors_http_header');


function base64urlencode($str)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
}

add_filter('digits_rest_token', 'digits_change_token', 10, 2);
function digits_change_token($access_token, $user_id)
{
    $jwt = new JWT();

    global $wpdb;
    $table = $wpdb->prefix . 'options';
    //  $payload, $key, $alg = 'HS256', $keyId = null, $head = null
    $options = $wpdb->get_results('SELECT * FROM ' . $table . ' WHERE option_name = "simple_jwt_login_settings" ', OBJECT);
    $options = json_encode($options);
    $values = json_decode($options, true);
    $values = json_decode($values[0]['option_value'], true);

    $the_user = get_user_by('id', $user_id);


    $payload =  [
        "iat" => 1620906261,
        "exp" => 1652442266,
        "sub" => "jrocket@example.com",
        "UserID" => $user_id,
        "username" => $the_user->user_login,
    ];
    $key = $values['decryption_key'];
    // $access_token = JWT::encode($payload, $key,  $alg = 'HS256' );
    $access_token = $jwt::encode($payload, $key);

    return $access_token;
}

// If this file is called directly, abort.
if (! defined('WPINC')) {
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
define('PZZ_API_CLIENT_VERSION', '1.2.7');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pzz-api-client-activator.php
 *
 * @since 1.0.0
 */
function pzz_api_client_activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-pzz-api-client-activator.php';
    Pzz_Api_Client_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pzz-api-client-deactivator.php
 *
 * @since 1.0.0
 */
function pzz_api_client_deactivate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-pzz-api-client-deactivator.php';
    Pzz_Api_Client_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'pzz_api_client_activate');
register_deactivation_hook(__FILE__, 'pzz_api_client_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 *
 * @since 1.0.0
 */
require plugin_dir_path(__FILE__) . 'includes/class-pzz-api-client.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function pzz_api_client_run()
{
    $plugin = new Pzz_Api_Client();
    $plugin->run();
}

/**
 * @since    1.2.0 Use init event to run plugin after all WordPress
 *                 init process.
 * @since    1.0.0
 */
add_action('init', 'pzz_api_client_run');
