<?php

/**
 * Metadata base class.
 * 
 * @since 1.1.1
 */
abstract class PZZ_JSON_Meta {
	/**
	 * Construct the API handler object.
	 * 
	 * @since 1.1.2
	 */
	public function __construct() {
		if ( empty( $this->type ) ) {
			_doing_it_wrong( 'PZZ_JSON_Meta::__construct', __( 'The object type must be overridden' ), 'WPAPI-1.2' );
			return;
		}
	}

	/**
	 * Check that the object is valid and can be accessed.
	 *
	 * @since 1.1.1
	 * @param mixed $id Object ID (can be any data from the API, will be validated)
	 * @return boolean|WP_Error True if valid and accessible, error otherwise.
	 */
	abstract protected function check_object( $id );

	/**
	 * Get the object (parent) ID column for the relevant table.
	 *
	 * @since 1.1.2
	 * @return string
	 */
	protected function get_parent_column() {
		return ( 'user' === $this->type ) ? 'user_id' : 'post_id';
	}

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

		/**
		 * @since 1.1.2 'pzz_prepare_meta'
		 */
		return apply_filters( 'pzz_prepare_meta', $meta, $id );
	}

	/**
	 * Prepares meta data for return as an object.
	 *
	 * @since 1.1.2
	 * @param int $parent_id Object ID
	 * @param stdClass $data Metadata row from database
	 * @param boolean $is_raw Is the value field still serialized? (False indicates the value has been unserialized)
	 * @return array|WP_Error Meta object data on success, WP_Error otherwise
	 */
	protected function prepare_meta( $parent_id, $data, $is_raw = false ) {
		$ID    = $data->meta_id;
		$key   = $data->meta_key;
		$value = $data->meta_value;

		// Don't expose protected fields.
		if ( is_protected_meta( $key ) ) {
			return new WP_Error( 'pzz_meta_protected', sprintf( __( '%s is marked as a protected field.' ), $key ), array( 'status' => 403 ) );
		}

		// Normalize serialized strings
		if ( $is_raw && is_serialized_string( $value ) ) {
			$value = unserialize( $value );
		}

		// Don't expose serialized data
		if ( is_serialized( $value ) || ! is_string( $value ) ) {
			return new WP_Error( 'pzz_meta_protected', sprintf( __( '%s contains serialized data.' ), $key ), array( 'status' => 403 ) );
		}

		$meta = array(
			'ID'    => (int) $ID,
			'key'   => $key,
			'value' => $value,
		);

		/**
		 * @since 1.1.2 'pzz_prepare_meta_value'
		 */
		return apply_filters( 'pzz_prepare_meta_value', $meta, $parent_id );
	}
	
}
