<?php

/**
 * @since 2.0.0
 */
class PZZ_JSON_Comments {
  	/**
	 * Retrieve comments
	 * 
	 * @since 1.0.0
	 * @return array List of Comment entities
	 */
	public function get_comments( $request ) {
		$args = [];
		if (is_int($request)) {
			$args['post_id'] = (int) $request;
		} else if (is_object($request)) {
			$args = array(
				'post_id' => $request->get_url_params()['id'], 
				'number' => $request->get_query_params()['number'],
				'paged' => $request->get_query_params()['paged'] ? $request->get_query_params()['paged'] : 1,
				'status' => 'approve',
				'type' => 'comment',
			);
		}

		$comments = get_comments($args);

		$post = get_post( $args['post_id'], ARRAY_A );

		if ( empty( $post['ID'] ) ) {
			return new WP_Error( 'json_post_invalid_id', __( 'Invalid post ID.' ), array( 'status' => 404 ) );
		}

		if ( ! json_check_post_permission( $post, 'read' ) ) {
			return new WP_Error( 'json_user_cannot_read', __( 'Sorry, you cannot read this post.' ), array( 'status' => 401 ) );
		}

		$struct = array();

		foreach ( $comments as $comment ) {
			$struct[] = $this->prepare_comment( $comment, array( 'comment', 'meta' ), 'collection' );
		}

		return $struct;
    }
    
    /**
	 * Prepares comment data for returning as a JSON response.
	 *
	 * @since 1.0.0
	 * @param stdClass $comment Comment object
	 * @param array $requested_fields Fields to retrieve from the comment
	 * @param string $context Where is the comment being loaded?
	 * @return array Comment data for JSON serialization
	 */
	private function prepare_comment( $comment, $requested_fields = array( 'comment', 'meta' ), $context = 'single' ) {
		$fields = array(
			'ID'   => (int) $comment->comment_ID,
			'post' => (int) $comment->comment_post_ID,
		);

		$post = (array) get_post( $fields['post'] );

		// Content
		$fields['content'] = apply_filters( 'comment_text', $comment->comment_content, $comment );
		// $fields['content_raw'] = $comment->comment_content;

		// Status
		switch ( $comment->comment_approved ) {
			case 'hold':
			case '0':
				$fields['status'] = 'hold';
				break;

			case 'approve':
			case '1':
				$fields['status'] = 'approved';
				break;

			case 'spam':
			case 'trash':
			default:
				$fields['status'] = $comment->comment_approved;
				break;
		}

		// Type
		$fields['type'] = apply_filters( 'get_comment_type', $comment->comment_type );

		if ( empty( $fields['type'] ) ) {
			$fields['type'] = 'comment';
		}

		// Parent
		if ( ( 'single' === $context || 'single-parent' === $context ) && (int) $comment->comment_parent ) {
			$parent_fields = array( 'meta' );

			if ( $context === 'single' ) {
				$parent_fields[] = 'comment';
			}
			$parent = get_comment( $comment->comment_parent );

			$fields['parent'] = $this->prepare_comment( $parent, $parent_fields, 'single-parent' );
		}

		// Parent
		$fields['parent'] = (int) $comment->comment_parent;

		// Author
		if ( (int) $comment->user_id !== 0 ) {
			$fields['author'] = (int) $comment->user_id;
		} else {
			$fields['author'] = array(
				'ID'     => 0,
				'name'   => $comment->comment_author,
				'URL'    => $comment->comment_author_url,
				'avatar' => PZZ_URL_Helper::get_avatar_url( $comment->comment_author_email ),
			);
		}

		// Date
		$timezone     = PZZ_DateTime_Helper::get_timezone();
		$comment_date = PZZ_DateTime_Helper::createFromFormat( 'Y-m-d H:i:s', $comment->comment_date, $timezone );

		$fields['date']     = PZZ_MySql_Helper::mysql_to_rfc3339( $comment->comment_date );
		$fields['date_tz']  = $comment_date->format( 'e' );
		$fields['date_gmt'] = PZZ_MySql_Helper::mysql_to_rfc3339( $comment->comment_date_gmt );

		// Meta
		$meta = array(
			'links' => array(
				'up' => PZZ_URL_Helper::convert_url_to_json_endpoint( sprintf( '/posts/%d', (int) $comment->comment_post_ID ) )
			),
		);

		if ( 0 !== (int) $comment->comment_parent ) {
			$meta['links']['in-reply-to'] = PZZ_URL_Helper::convert_url_to_json_endpoint( sprintf( '/posts/%d/comments/%d', (int) $comment->comment_post_ID, (int) $comment->comment_parent ) );
		}

		if ( 'single' !== $context ) {
			$meta['links']['self'] = PZZ_URL_Helper::convert_url_to_json_endpoint( sprintf( '/posts/%d/comments/%d', (int) $comment->comment_post_ID, (int) $comment->comment_ID ) );
		}

		// Remove unneeded fields
		$data = array();

		if ( in_array( 'comment', $requested_fields ) ) {
			$data = array_merge( $data, $fields );
		}

		if ( in_array( 'meta', $requested_fields ) ) {
			$data['meta'] = $meta;
		}

		return apply_filters( 'pzz_prepare_comment', $data, $comment, $context );
	}
}
