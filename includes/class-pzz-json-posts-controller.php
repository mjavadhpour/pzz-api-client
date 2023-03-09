<?php

/**
 * The core functionality of the plugin.
 *
 * @since      1.1.1
 * @package    Pzz_Api_Client
 * @subpackage Pzz_Api_Client/includes
 * @author     @mjavadhpour on WordPress.org
 *
 * TODO: Check for refactor with this link: <a>https://upnrunn.com/blog/2018/04/how-to-extend-wp-rest-api-from-your-custom-plugin-part-3/</a>
 */
class PZZ_JSON_Posts_Controller
{
    /**
     * The namespace of APIs.
     *
     * @since    1.1.1
     * @access   private
     * @var      string    $namespace    The namespace of APIs.
     */
    private $namespace;

    /**
     * The version of the APIs.
     *
     * @since    1.1.1
     * @access   private
     * @var      string    $version    The version of the APIs.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since      1.1.1
     * @param      string                   $plugin_name       The namespace of APIs.
     * @param      string                   $version           The version of the APIs.
     */
    public function __construct($namespace = 'pzz', $version = '1')
    {
        $this->namespace = $namespace;
        $this->version = $version;
    }

    /**
     * Build API route with given arguments. the namespace and the version
     * come from the inner class properties.
     *
     * @since    1.1.1
     * @param    function    $callback    The function that was executed when the endpoint was called.
     * @param    string      $path        The path of the API. You can start path with the "/" character or not; it was optional.
     * @param    string      $method      The API HTTP method.
     */
    public function build_route($callback, $path, $method, $args = array())
    {
        if ($args == null) {
            $args = array();
        }

        register_rest_route($this->namespace . '/' . $this->get_version(), $path, array(
            'methods' => $method,
            'callback' => $callback,
            'args' => $args
        ));
    }

    /**
     * Get list of posts with custom schema.
     *
     * query params: posts_per_page
     *               page
     *               cat
     * 				 orderby
     *               order
     *               tag
     *               tag_id
     *
     * @since    1.1.1
     * @param    WP_REST_Request   $request    Wordpress rest request object; passed by the WordPress.
     */
    public function get_posts($request)
    {
        $filter = $request->get_params();
        $context = 'simple';
        $type = 'post';
        $page = $request->get_param('page') ? $request->get_param('page') : 1;

        $query = array();
        // Validate post types and permissions
        $query['post_type'] = array();
        foreach ((array) $type as $type_name) {
            $post_type = get_post_type_object($type_name);
            if (! ((bool) $post_type) || ! $post_type->show_in_rest) {
                return new WP_Error('json_invalid_post_type', sprintf(__('The post type "%s" is not valid'), $type_name), array( 'status' => 403 ));
            }
            $query['post_type'][] = $post_type->name;
        }
        global $wp;
        // Allow the same as normal WP
        $valid_vars = apply_filters('query_vars', $wp->public_query_vars);
        // If the user has the correct permissions, also allow use of internal
        // query parameters, which are only undesirable on the frontend
        //
        // To disable anyway, use `add_filter('json_private_query_vars', '__return_empty_array');`
        if (current_user_can($post_type->cap->edit_posts)) {
            $private = apply_filters('json_private_query_vars', $wp->private_query_vars);
            $valid_vars = array_merge($valid_vars, $private);
        }
        // Define our own in addition to WP's normal vars
        $json_valid = array( 'posts_per_page', 'tag_id', 'include' );
        $valid_vars = array_merge($valid_vars, $json_valid);
        // Filter and flip for querying
        $valid_vars = apply_filters('json_query_vars', $valid_vars);
        $valid_vars = array_flip($valid_vars);
        // Exclude the post_type query var to avoid dodging the permission
        // check above
        unset($valid_vars['post_type']);
        foreach ($valid_vars as $var => $index) {
            if (isset($filter[ $var ])) {
                if ($var == 'include') {
                    // '1,2,3'
                    $query[ 'post__in' ] = json_decode('['.$filter[ $var ].']', true);
                } else {
                    $query[ $var ] = apply_filters('json_query_var-' . $var, $filter[ $var ]);
                }
            }
        }
        // Special parameter handling
        $query['paged'] = absint($page);
        $post_query = new WP_Query();
        $posts_list = $post_query->query($query);
        $response   = new PZZ_JSON_Response();
        $response->query_navigation_headers($post_query);
        if (! $posts_list) {
            $response->set_data(array());
            return $response;
        }
        // holds all the posts data
        $struct = array();
        $response->header('Last-Modified', mysql2date('D, d M Y H:i:s', get_lastpostmodified('GMT'), 0).' GMT');
        foreach ($posts_list as $post) {
            $post = get_object_vars($post);
            // Do we have permission to read this post?
            if (! PZZ_Post_Helper::check_post_permission($post, 'read')) {
                continue;
            }
            $response->link_header('item', PZZ_URL_Helper::convert_url_to_json_endpoint('/posts/' . $post['ID']), array( 'title' => $post['post_title'] ));
            $post_data = $this->prepare_post($post, $context);
            if (is_wp_error($post_data)) {
                continue;
            }
            $struct[] = $post_data;
        }
        $response->set_data($struct);
        return $response;
    }

    /**
     * Retrieve a post.
     *
     * @since 1.1.1
     * @uses get_post()
     * @param int|WP_REST_Request $id Post ID or WP_REST_Request object.
     * @param string $context The context; 'view' (default) or 'edit'.
     * @return array Post entity
     */
    public function get_post($idOrRequest, $context = 'embed')
    {
        if (is_int($idOrRequest)) {
            $id = (int) $idOrRequest;
        } elseif (is_object($idOrRequest)) {
            $id = (int) $idOrRequest->get_url_params()['id'];
        }

        $post = get_post($id, ARRAY_A);

        if (empty($id) || empty($post['ID'])) {
            return new WP_Error('json_post_invalid_id', __('Invalid post ID.'), array( 'status' => 404 ));
        }

        $checked_permission = 'read';
        if ('inherit' === $post['post_status'] && $post['post_parent'] > 0) {
            $checked_post = get_post($post['post_parent'], ARRAY_A);
            if ('revision' === $post['post_type']) {
                $checked_permission = 'edit';
            }
        } else {
            $checked_post = $post;
        }

        if (! PZZ_Post_Helper::check_post_permission($checked_post, $checked_permission)) {
            return new WP_Error('json_user_cannot_read', __('Sorry, you cannot read this post.'), array( 'status' => 401 ));
        }

        // Link headers (see RFC 5988)

        $response = new PZZ_JSON_Response();
        $response->header('Last-Modified', mysql2date('D, d M Y H:i:s', $post['post_modified_gmt']) . 'GMT');

        $post = $this->prepare_post($post, $context);

        if (is_wp_error($post)) {
            return $post;
        }

        if ($context !== 'media') {
            foreach ($post['meta']['links'] as $rel => $url) {
                $response->link_header($rel, $url);
            }
        }

        $response->link_header('alternate', get_permalink($id), array( 'type' => 'text/html' ));
        $response->set_data($post);

        return $response;
    }

    /**
     * Retrieve the post comments.
     *
     * @since 1.1.1
     * @uses get_post_comments()
     * @param int|WP_REST_Request $id Post ID or WP_REST_Request object.
     * @return array Comment entity
     */
    public function get_post_comments($idOrRequest)
    {
        return apply_filters('pzz_prepare_post_comments', $idOrRequest);
    }

    /**
     * @since 1.1.1
     */
    public function get_taxonomies($request)
    {
        return apply_filters('pzz_get_taxonomies', $request);
    }

    /**
     * Prepares post data for return in an XML-RPC object.
     *
     * @since 1.1.1
     * @access private
     * @param array $post The unprepared post data
     * @param string $context The context for the prepared post. (view|view-revision|edit|embed|single-parent)
     * @return array The prepared post data
     */
    private function prepare_post($post, $context = 'view')
    {
        // Holds the data for this post.
        $_post = array( 'ID' => (int) $post['ID'] );

        $post_type = get_post_type_object($post['post_type']);

        if (! PZZ_Post_Helper::check_post_permission($post, 'read')) {
            return new WP_Error('json_user_cannot_read', __('Sorry, you cannot read this post.'), array( 'status' => 401 ));
        }

        $previous_post = null;
        if (! empty($GLOBALS['post'])) {
            $previous_post = $GLOBALS['post'];
        }
        $post_obj = get_post($post['ID']);

        // Don't allow unauthenticated users to read password-protected posts
        if (! empty($post['post_password'])) {
            if (! PZZ_Post_Helper::check_post_permission($post, 'edit')) {
                return new WP_Error('json_user_cannot_read', __('Sorry, you cannot read this post.'), array( 'status' => 403 ));
            }

            // Fake the correct cookie to fool post_password_required().
            // Without this, get_the_content() will give a password form.
            require_once ABSPATH . 'wp-includes/class-phpass.php';
            $hasher = new PasswordHash(8, true);
            $value = $hasher->HashPassword($post['post_password']);
            $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] = wp_slash($value);
        }

        $GLOBALS['post'] = $post_obj;
        setup_postdata($post_obj);

        /**
         * prepare common post fields
         */

        $post_fields = array(
            'title'           => get_the_title($post['ID']),
            'status'          => $post['post_status'],
        );

        if ($context === 'media') {
            $post_fields['post_mime_type'] = $post['post_mime_type'];
        } else {
            $post_fields['author'] = (int) $post['post_author'];
        }

        if ($context === 'embed') {
            $post_fields['content'] = $this->prepare_content($post['post_content']);
        }

        if ($context === 'embed') {
            $post_fields['link'] = get_permalink($post['ID']);
        }

        $post_fields_extended = array(
            'guid'           => apply_filters('get_the_guid', $post['guid']),
        );

        if ($context !== 'media') {
            $post_fields_extended['excerpt'] = $this->prepare_excerpt($post['post_excerpt']);
            $post_fields_extended['comment_status'] = $post['comment_status'];
        }

        if ($context === 'media') {
            return array_merge($_post, array_merge($post_fields, $post_fields_extended));
        }

        $post_fields_raw = array(
            'title_raw'   => $post['post_title'],
            'content_raw' => $post['post_content'],
            'excerpt_raw' => $post['post_excerpt'],
            'guid_raw'    => $post['guid'],
            'post_meta'   => $this->handle_get_post_meta($post['ID']),
        );

        // Dates
        $timezone = PZZ_DateTime_Helper::get_timezone();

        if ($post['post_date_gmt'] === '0000-00-00 00:00:00') {
            $post_fields['date']              = null;
            $post_fields_extended['date_tz']  = null;
            $post_fields_extended['date_gmt'] = null;
        } else {
            $post_date                        = PZZ_DateTime_Helper::createFromFormat('Y-m-d H:i:s', $post['post_date'], $timezone);
            $post_fields['date']              = PZZ_MySql_Helper::mysql_to_rfc3339($post['post_date']);
            $post_fields_extended['date_tz']  = $post_date->format('e');
            $post_fields_extended['date_gmt'] = PZZ_MySql_Helper::mysql_to_rfc3339($post['post_date_gmt']);
        }

        if ($post['post_modified_gmt'] === '0000-00-00 00:00:00') {
            $post_fields['modified']              = null;
            $post_fields_extended['modified_tz']  = null;
            $post_fields_extended['modified_gmt'] = null;
        } else {
            $modified_date                        = PZZ_DateTime_Helper::createFromFormat('Y-m-d H:i:s', $post['post_modified'], $timezone);
            $post_fields['modified']              = PZZ_MySql_Helper::mysql_to_rfc3339($post['post_modified']);
            $post_fields_extended['modified_tz']  = $modified_date->format('e');
            $post_fields_extended['modified_gmt'] = PZZ_MySql_Helper::mysql_to_rfc3339($post['post_modified_gmt']);
        }

        // Consider future posts as published
        if ($post_fields['status'] === 'future') {
            $post_fields['status'] = 'publish';
        }

        // Fill in blank post format
        $post_fields['format'] = get_post_format($post['ID']);

        if (empty($post_fields['format'])) {
            $post_fields['format'] = 'standard';
        }

        // Merge requested $post_fields fields into $_post
        $_post = array_merge($_post, $post_fields);

        // Include extended fields. We might come back to this.
        $_post = array_merge($_post, $post_fields_extended);

        if ('view-revision' == $context) {
            if (PZZ_Post_Helper::check_post_permission($post, 'edit')) {
                $_post = array_merge($_post, $post_fields_raw);
            } else {
                $GLOBALS['post'] = $previous_post;
                if ($previous_post) {
                    setup_postdata($previous_post);
                }
                return new WP_Error('json_cannot_view', __('Sorry, you cannot view this revision'), array( 'status' => 403 ));
            }
        }

        // Entity meta
        $links = array(
            'self'       => PZZ_URL_Helper::convert_url_to_json_endpoint('/posts/' . $post['ID']),
        );

        if ('view-revision' != $context) {
            $links['replies'] = PZZ_URL_Helper::convert_url_to_json_endpoint('/posts/' . $post['ID'] . '/comments');
        }

        $_post['meta'] = array( 'links' => $links );

        if (! empty($post['post_parent'])) {
            $_post['meta']['links']['up'] = PZZ_URL_Helper::convert_url_to_json_endpoint('/posts/' . (int) $post['post_parent']);
        }

        $GLOBALS['post'] = $previous_post;
        if ($previous_post) {
            setup_postdata($previous_post);
        }

        return apply_filters('pzz_prepare_post', $_post, $post, $context);
    }

    /**
     * Retrieve the post excerpt.
     *
     * @since 1.1.1
     * @return string
     */
    private function prepare_excerpt($excerpt)
    {
        if (post_password_required()) {
            return __('There is no excerpt because this is a protected post.');
        }

        $excerpt = apply_filters('the_excerpt', apply_filters('get_the_excerpt', $excerpt));

        if (empty($excerpt)) {
            return null;
        }

        return $excerpt;
    }

    /**
     * Retrive the post content if available
     *
     * @since 1.1.1
     */
    private function prepare_content($content)
    {
        if (post_password_required()) {
            return __('There is no content because this is a protected post.');
        }

        return apply_filters('the_content', $content);
    }

    /**
     * Retrieve all meta for a post.
     *
     * @since 1.1.1
     * @param int $post_id Post ID
     * @return (array[]|WP_Error) List of meta object data on success, WP_Error otherwise
     */
    private function handle_get_post_meta($post_id)
    {
        $handler = new PZZ_JSON_Meta_Posts();

        return $handler->get_all_meta($post_id);
    }

    /**
     * Get version of API.
     *
     * @since    1.1.1
     * @return   string    The API version.
     */
    private function get_version()
    {
        return 'v' . $this->version;
    }
}
