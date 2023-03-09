<?php
/**
 * 
 * */
require_once __DIR__ . '/JWT.php';

function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *");
}
add_action('init','add_cors_http_header');

//generate token
function signed_token($secretKey, $pay_load) {
   $header = [ 'typ' => 'JWT', 'alg' => $alg,  ];
   $header = json_encode(["typ" => "JWT", "alg" => "HS256"]);
   $base64UrlHeader = base64urlencode($header);

   $payload = json_encode($pay_load);
   $base64UrlPayload = base64urlencode($payload);

   $signature = hash_hmac("HS256", $base64UrlHeader . "." . $base64UrlPayload, $secretKey, true);
   $base64UrlSignature = base64urlencode($signature);

   return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}


function base64urlencode($str) {
   return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
}


add_filter('digits_rest_token', 'digits_change_token', 10, 2);
function digits_change_token($access_token, $user_id){
	global $wpdb;
	$table = $wpdb->prefix . 'options';
	//  $payload, $key, $alg = 'HS256', $keyId = null, $head = null 
	  $options = $wpdb->get_results('SELECT * FROM ' . $table . ' WHERE option_name = "simple_jwt_login_settings" ', OBJECT);
	  $options = json_encode($options);
	  $values = json_decode($options, true);
	  $values = json_decode($values[0]['option_value'], true);
	
	$payload =  [
		"iss" => "Online JWT Builder",
		"iat" => 1620838287,
		"exp" => 1652425079,
		"sub" => "1234567890",
		"name" => "John Doe",
		"UserID" => $user_id,
	];
	$key = $values['decryption_key'];
	 // $access_token = JWT::encode($payload, $key,  $alg = 'HS256' );
	$access_token = signed_token($key, $payload);

    return $access_token;
}


?>