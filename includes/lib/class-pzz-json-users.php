<?php

/**
 *
 * User json helper and modifier.
 *
 * @since 1.1.1
 */
class PZZ_JSON_Users {
	/**
	 *
	 * Prepare a User entity from a WP_User instance.
	 *
	 * @since 1.1.1
	 * @param WP_User $user
	 * @param string $context One of 'view', 'edit', 'embed', 'simple
	 * @return array
	 */
	protected function prepare_user( $user, $context = 'view' ) {
		$user_fields = array(
			'username'    => $user->user_login,
			'first_name'  => $user->first_name,
			'last_name'   => $user->last_name,
			'avatar'      => PZZ_URL_Helper::get_avatar_url( $user->user_email ),
		);

		if ( $context === 'view' || $context === 'edit' ) {
			$user_fields['roles']        = $user->roles;
			$user_fields['capabilities'] = $user->allcaps;
			$user_fields['email']        = false;
		}

		if ( $context === 'edit' ) {
			// The user's specific caps should only be needed if you're editing
			// the user, as allcaps should handle most uses
			$user_fields['email']              = $user->user_email;
			$user_fields['extra_capabilities'] = $user->caps;
		}

		if ($context == 'simple') {

			return apply_filters( 'json_prepare_user', $user_fields, $user, $context );

		}

		$user_fields['ID']          = $user->ID;
		$user_fields['name']        = $user->display_name;
		$user_fields['nickname']    = $user->nickname;
		$user_fields['slug']        = $user->user_nicename;
		$user_fields['URL']         = $user->user_url;
		$user_fields['description'] = $user->description;
		$user_fields['registered']  = date( 'c', strtotime( $user->user_registered ) );

		$user_fields['meta'] = array(
			'links' => array(
				'self' => PZZ_URL_Helper::convert_url_to_json_endpoint( '/users/' . $user->ID ),
				'archives' => PZZ_URL_Helper::convert_url_to_json_endpoint( '/users/' . $user->ID . '/posts' ),
			),
		);

		return apply_filters( 'json_prepare_user', $user_fields, $user, $context );
	}

	/**
	 * Add author data to post data
	 *
	 * @since 1.1.1
	 * @param array $data Post data
	 * @param array $post Internal post data
	 * @param string $context Post context
	 * @return array Filtered data
	 */
	public function add_post_author_data( $data, $post, $context = 'simple' ) {
		$author = get_userdata( $post['post_author'] );

		if ( ! empty( $author ) ) {
			$data['author'] = $this->prepare_user( $author, $context );
		}

		return $data;
	}

	/**
	 * Add author data to comment data
	 * 
	 * @since 1.1.1
	 */
	public function add_comment_author_data( $data, $comment, $context = 'simple' ) {
		$author = get_userdata( $comment->user_id );

		if ( ! empty( $author ) ) {
			$data['author'] = $this->prepare_user( $author, $context );
		}

		return $data;
	}

	/**
	 * Retrieves the avatar urls in various sizes based on a given email address.
	 *
	 * @see get_avatar_url()
	 *
	 * @since 1.1.1
	 * @param string $email Email address.
	 * @return array $urls Gravatar url for each size.
	 */
	public function rest_get_avatar_urls( $email ) {
		$avatar_sizes = rest_get_avatar_sizes();

		$urls = array();
		foreach ( $avatar_sizes as $size ) {
			$urls[ $size ] = get_avatar_url( $email, array( 'size' => $size ) );
		}

		return $urls;
	}

}
