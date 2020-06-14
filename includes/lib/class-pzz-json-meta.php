<?php

/**
 * Metadata base class.
 * 
 * @since 1.1.1
 */
abstract class PZZ_JSON_Meta {
	/**
	 * Check that the object is valid and can be accessed.
	 *
	 * @since 1.1.1
	 * @param mixed $id Object ID (can be any data from the API, will be validated)
	 * @return boolean|WP_Error True if valid and accessible, error otherwise.
	 */
	abstract protected function check_object( $id );

	/**
	 * Retrieve custom fields for object.
	 *
	 * @since 1.1.1
	 * @param int $id Object ID
	 * @return (array[]|WP_Error) List of meta object data on success, WP_Error otherwise
	 */
	public function get_all_meta( $id ) {
		$check = $this->check_object( $id );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		global $wpdb;
		$table = _get_meta_table( $this->type );
		$parent_column = $this->get_parent_column();

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_key, meta_value FROM $table WHERE $parent_column = %d", $id ) );

		$meta = array();

		foreach ( $results as $row ) {
			$value = $this->prepare_meta( $id, $row, true );

			if ( is_wp_error( $value ) ) {
				continue;
			}

			$meta[] = $value;
		}

		return apply_filters( 'json_prepare_meta', $meta, $id );
	}
	
}
