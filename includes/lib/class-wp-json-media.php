<?php

class WP_JSON_Media extends WP_JSON_Posts_Controller {
	/**
	 * Add the featured image data to the post data
	 *
	 * @since 1.0.0
	 * @param array $data Post data
	 * @param array $post Raw post data from the database
	 * @param string $context Display context
	 * @return array Filtered post data
	 */
	public function add_thumbnail_data( $data, $post, $context ) {
		if ( ! post_type_supports( $post['post_type'], 'thumbnail' ) ) {
			return $data;
		}

		// TODO: Don't embed too deeply?

		// Thumbnail
		$data['featured_image'] = null;
		$thumbnail_id = get_post_thumbnail_id( $post['ID'] );

		if ( $thumbnail_id ) {
			$data['featured_image'] = $this->prepare_item_for_response($this->get_post( $thumbnail_id, 'media' ));
		}

		return $data;
	}

	/**
	 * Retrieve a attachment
	 *
	 * @see WP_JSON_Posts_Controller::get_post()
	 * 
	 * @since 1.0.0
	 */
	public function get_post( $id, $context = 'view' ) {
		$id = (int) $id;

		if ( empty( $id ) ) {
			return new WP_Error( 'json_post_invalid_id', __( 'Invalid post ID.' ), array( 'status' => 404 ) );
		}

		$post = get_post( $id, ARRAY_A );

		if ( $post['post_type'] !== 'attachment' ) {
			return new WP_Error( 'json_post_invalid_type', __( 'Invalid post type' ), array( 'status' => 400 ) );
		}

		return parent::get_post( $id, $context );
	}

	/**
	 * Prepares a single attachment output for response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_JSON_Response  $post    Attachment object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $post ) {
		$data     = $post->data;
		$id = $post->data['ID'];

		$data['alt_text'] = get_post_meta( $id, '_wp_attachment_image_alt', true );
		$data['media_type'] = wp_attachment_is_image( $id ) ? 'image' : 'file';
		$data['mime_type'] = $post->data['post_mime_type'];

		$data['media_details'] = wp_get_attachment_metadata( $id );

		// Remove redundant key.
		if (is_array($data['media_details'])) {
			if (is_array($data['media_details']['image_meta'])) {
				unset($data['media_details']['image_meta']);
			}
		}

		// Ensure empty details is an empty object.
		if ( empty( $data['media_details'] ) ) {
			$data['media_details'] = new stdClass;
		} elseif ( ! empty( $data['media_details']['sizes'] ) ) {

			foreach ( $data['media_details']['sizes'] as $size => &$size_data ) {

				if ( isset( $size_data['mime-type'] ) ) {
					$size_data['mime_type'] = $size_data['mime-type'];
					unset( $size_data['mime-type'] );
				}

				// Use the same method image_downsize() does.
				$image_src = wp_get_attachment_image_src( $id, $size );
				if ( ! $image_src ) {
					continue;
				}

				$size_data['source_url'] = $image_src[0];
			}

			$full_src = wp_get_attachment_image_src( $id, 'full' );

			if ( ! empty( $full_src ) ) {
				$data['media_details']['sizes']['full'] = array(
					'width'      => $full_src[1],
					'height'     => $full_src[2],
					'mime_type'  => $post->post_mime_type,
					'source_url' => $full_src[0],
				);
			}
		} else {
			$data['media_details']['sizes'] = new stdClass;
		}

		$data['source_url'] = wp_get_attachment_url( $id );

		return $data;
	}
	
}
