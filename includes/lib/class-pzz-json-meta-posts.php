<?php

/**
 * @since 1.1.1
 */
class PZZ_JSON_Meta_Posts extends PZZ_JSON_Meta
{
    /**
     * Associated object type.
     *
     * @since 1.1.2
     * @var string Type slug ("post" or "user")
     */
    protected $type = 'post';

    /**
     * Check that the object can be accessed.
     *
     * @since 1.1.1
     * @param mixed $id Object ID
     * @return boolean|WP_Error
     */
    protected function check_object($id)
    {
        $id = (int) $id;

        $post = get_post($id, ARRAY_A);

        if (empty($id) || empty($post['ID'])) {
            return new WP_Error('json_post_invalid_id', __('Invalid post ID.'), array( 'status' => 404 ));
        }

        if (! PZZ_Post_Helper::check_post_permission($post, 'edit')) {
            return new WP_Error('json_cannot_edit', __('Sorry, you cannot edit this post'), array( 'status' => 403 ));
        }

        return true;
    }

    /**
     * Add post meta to post responses.
     *
     * Adds meta to post responses for the 'edit' context.
     *
     * @since 1.1.1
     * @param WP_Error|array $data Post response data (or error)
     * @param array $post Post data
     * @param string $context Context for the prepared post.
     * @return WP_Error|array Filtered data
     */
    public function add_post_meta_data($data, $post, $context)
    {
        if ($context !== 'edit' || is_wp_error($data)) {
            return $data;
        }

        // Permissions have already been checked at this point, so no need to
        // check again
        $data['post_meta'] = $this->get_all_meta($post['ID']);
        if (is_wp_error($data['post_meta'])) {
            return $data['post_meta'];
        }

        return $data;
    }
}
