<?php

/**
 * @since 1.1.1
 */
class PZZ_JSON_Taxonomies
{
    /**
     * Add term data to post data
     *
     * @since 1.1.1
     * @param array $data Post data
     * @param array $post Internal post data
     * @param string $context Post context
     * @return array Filtered data
     */
    public function add_term_data($data, $post, $context)
    {
        if ($context === 'simple') {
            return $data;
        }

        $post_type_taxonomies = get_object_taxonomies($post['post_type']);
        $terms = wp_get_object_terms($post['ID'], $post_type_taxonomies);
        $data['terms'] = array();

        foreach ($terms as $term) {
            $data['terms'][ $term->taxonomy ][] = $this->prepare_taxonomy_term($term);
        }

        return $data;
    }

    /**
     * @since 1.1.1
     */
    public function get_items($request)
    {
        $taxonomy_type = $request->get_url_params()['taxonomy_type'];
        $include_cats = $request->get_query_params()['include'];
        $force_included = $request->get_query_params()['force_included'];
        $fields = array('hide_empty' => true);

        if (!empty(trim($taxonomy_type))) {
            $fields['taxonomy'] = $taxonomy_type;
        }

        if (!empty(trim($include_cats))) {
            $fields['include'] = $include_cats;
        } elseif ($force_included == '1') {
            return [];
        }

        // Check permission
        if (is_wp_error($this->get_items_permissions_check($request))) {
            return new WP_Error('rest_cannot_view', __('Sorry, you are not allowed to manage terms in this taxonomy.'), array( 'status' => rest_authorization_required_code() ));
        }

        // Retrieve the list of registered collection query parameters.
        $registered = $this->get_collection_params();

        $taxonomies = get_terms($fields);

        $data = array();
        foreach ($taxonomies as $tax_type => $value) {
            $tax = $this->prepare_item_for_response($value, $request);
            $data[ $tax_type ] = $tax;
        }

        if (empty($data)) {
            // Response should still be returned as a JSON object when it is empty.
            $data = (object) $data;
        }

        $data = array_values($data);

        return rest_ensure_response($data);
    }

    /**
     * Prepares a taxonomy object for serialization.
     *
     * @since 1.1.1
     * @param stdClass        $taxonomy Taxonomy data.
     * @param WP_REST_Request $request  Full details about the request.
     * @return WP_REST_Response Response object.
     */
    public function prepare_item_for_response($taxonomy, $request)
    {
        $base = ! empty($taxonomy->rest_base) ? $taxonomy->rest_base : $taxonomy->name;

        $fields = [
            'term_id',
            'name'
        ];
        $data   = array();

        if (in_array('name', $fields, true)) {
            $data['ID'] = $taxonomy->term_id;
        }

        if (in_array('name', $fields, true)) {
            $data['name'] = $taxonomy->name;
        }

        return $data;
    }

    /**
     * Prepare term data for serialization
     *
     * @since 1.1.1
     * @param array|object $term The unprepared term data
     * @return array The prepared term data
     */
    protected function prepare_taxonomy_term($term, $context = 'view')
    {
        $base_url = '/taxonomies/' . $term->taxonomy . '/terms';

        $data = array(
            'ID'          => (int) $term->term_taxonomy_id,
            'name'        => $term->name,
        );

        return apply_filters('json_prepare_term', $data, $term, $context);
    }

    /**
     * Checks whether a given request has permission to read taxonomies.
     *
     * @since 1.1.1
     * @param WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check($request)
    {
        if ('edit' === $request['context']) {
            if (! empty($request['type'])) {
                $taxonomies = get_object_taxonomies($request['type'], 'objects');
            } else {
                $taxonomies = get_taxonomies('', 'objects');
            }
            foreach ($taxonomies as $taxonomy) {
                if (! empty($taxonomy->show_in_rest) && current_user_can($taxonomy->cap->assign_terms)) {
                    return true;
                }
            }
            return new WP_Error('rest_cannot_view', __('Sorry, you are not allowed to manage terms in this taxonomy.'), array( 'status' => rest_authorization_required_code() ));
        }
        return true;
    }

    /**
     * Retrieves the query params for collections.
     *
     * @since 1.1.1
     * @return array Collection parameters.
     */
    public function get_collection_params()
    {
        $new_params = array();
        $new_params['context'] = 'view';
        $new_params['type'] = array(
            'description'  => __('Limit results to taxonomies associated with a specific post type.'),
            'type'         => 'string',
        );
        return $new_params;
    }
}
