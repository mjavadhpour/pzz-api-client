<?php
/**
 * Post Utils class
 *
 * @since 2.0.0
 * @package WordPress
 * @subpackage JSON API
 */
class PZZ_Post_Helper extends DateTime {
	/**
     * Check if we have permission to interact with the post object.
     *
     * @since 2.0.0
     * @param WP_Post $post Post object.
     * @param string $capability Permission to check.
     * @return boolean Can we interact with it?
     */
    public static function check_post_permission( $post, $capability = 'read' ) {
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

                    if ( self::check_post_permission( $parent, 'read' ) ) {
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
}
