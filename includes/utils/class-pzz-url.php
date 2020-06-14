<?php
/**
 * URL Helper class
 *
 * @since 1.1.1
 * @package WordPress
 * @subpackage JSON API
 */
class PZZ_URL_Helper {
	/**
	 * Retrieve the avatar url for a user who provided a user ID or email address.
	 *
	 * {@see get_avatar()} doesn't return just the URL, so we have to
	 * extract it here.
	 * 
	 * @since 1.1.1
	 * @param string $email Email address.
	 * @return string URL for the user's avatar, empty string otherwise.
	*/
	public static function get_avatar_url( $email ) {
		$avatar_html = get_avatar( $email );

		// Strip the avatar url from the get_avatar img tag.
		preg_match('/src=["|\'](.+)[\&|"|\']/U', $avatar_html, $matches);

		if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
			return esc_url_raw( $matches[1] );
		}

		return '';
	}

	/**
	 * Get URL to a JSON endpoint.
	 *
	 * @since 1.1.1
	 * @param string $path   Optional. JSON route. Default empty.
	 * @param string $scheme Optional. Sanitization scheme. Default 'json'.
	 * @return string Full URL to the endpoint.
	 */
	public static function convert_url_to_json_endpoint( $path = '', $scheme = 'json' ) {
		return self::get_json_url( null, $path, $scheme );
	}

	/**
	 * Get URL to a JSON endpoint on a site.
	 *
	 * @since 1.1.1
	 * @todo Check if this is even necessary
	 * @param int    $blog_id Blog ID.
	 * @param string $path    Optional. JSON route. Default empty.
	 * @param string $scheme  Optional. Sanitization scheme. Default 'json'.
	 * @return string Full URL to the endpoint.
	 */
	private static function get_json_url( $blog_id = null, $path = '', $scheme = 'json' ) {
		if ( get_option( 'permalink_structure' ) ) {
			$url = get_home_url( $blog_id, self::json_get_url_prefix(), $scheme );

			if ( ! empty( $path ) && is_string( $path ) && strpos( $path, '..' ) === false )
				$url .= '/pzz/v1/' . ltrim( $path, '/' );
		} else {
			$url = trailingslashit( get_home_url( $blog_id, '', $scheme ) );

			if ( empty( $path ) ) {
				$path = '/';
			} else {
				$path = '/' . ltrim( $path, '/' );
			}

			$url = add_query_arg( 'json_route', $path, $url );
		}

		/**
		 * Filter the JSON URL.
		 *
		 * @since 1.1.1
		 * @param string $url     JSON URL.
		 * @param string $path    JSON route.
		 * @param int    $blod_ig Blog ID.
		 * @param string $scheme  Sanitization scheme.
		 */
		return apply_filters( 'pzz_json_url', $url, $path, $blog_id, $scheme );
	}

	/**
	 * Get the URL prefix for any API resource.
	 *
	 * @since 1.1.1
	 * @return string Prefix.
	 */
	private static function json_get_url_prefix() {
		/**
		 * Filter the JSON URL prefix.
		 *
		 * @since 1.1.1
		 * @param string $prefix URL prefix. Default 'wp-json'.
		 */
		return apply_filters( 'pzz_json_url_prefix', 'wp-json' );
	}
}
