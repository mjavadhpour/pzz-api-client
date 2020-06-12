<?php

class WP_JSON_Response implements WP_JSON_ResponseInterface {
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param mixed $data Response data
	 * @param integer $status HTTP status code
	 * @param array $headers HTTP header map
	 */
	public function __construct($data = null, $status = 200, $headers = array()) {
		$this->data = $data;
		$this->set_status( $status );
		$this->set_headers( $headers );
	}

	/**
	 * Get headers associated with the response
	 *
	 * @since 1.0.0
	 * @return array Map of header name to header value
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * Set all header values
	 *
	 * @since 1.0.0
	 * @param array $headers Map of header name to header value
	 */
	public function set_headers( $headers ) {
		$this->headers = $headers;
	}

	/**
	 * Set a single HTTP header
	 *
	 * @since 1.0.0
	 * @param string $key Header name
	 * @param string $value Header value
	 * @param boolean $replace Replace an existing header of the same name?
	 */
	public function header( $key, $value, $replace = true ) {
		if ( $replace || ! isset( $this->headers[ $key ] ) ) {
			$this->headers[ $key ] = $value;
		} else {
			$this->headers[ $key ] .= ', ' . $value;
		}
	}

	/**
	 * Set a single link header
	 *
	 * @internal The $rel parameter is first, as this looks nicer when sending multiple
	 *
	 * @link http://tools.ietf.org/html/rfc5988
	 * @link http://www.iana.org/assignments/link-relations/link-relations.xml
	 *
	 * @since 1.0.0
	 * @param string $rel Link relation. Either an IANA registered type, or an absolute URL
	 * @param string $link Target IRI for the link
	 * @param array $other Other parameters to send, as an assocative array
	 */
	public function link_header( $rel, $link, $other = array() ) {
		$header = '<' . $link . '>; rel="' . $rel . '"';

		foreach ( $other as $key => $value ) {
			if ( 'title' == $key ) {
				$value = '"' . $value . '"';
			}
			$header .= '; ' . $key . '=' . $value;
		}
		return $this->header( 'Link', $header, false );
	}

	/**
	 * Send navigation-related headers for post collections
	 *
	 * @since 1.0.0
	 * @param WP_Query $query
	 */
	public function query_navigation_headers( $query ) {
		$max_page = $query->max_num_pages;
		$paged    = $query->get('paged');

		if ( ! $paged ) {
			$paged = 1;
		}

		$nextpage = intval($paged) + 1;

		if ( ! $query->is_single() ) {
			if ( $paged > 1 ) {
				$request = remove_query_arg( 'page' );
				$request = add_query_arg( 'page', $paged - 1, $request );
				$this->link_header( 'prev', $request );
			}

			if ( $nextpage <= $max_page ) {
				$request = remove_query_arg( 'page' );
				$request = add_query_arg( 'page', $nextpage, $request );
				$this->link_header( 'next', $request );
			}
		}

		$this->header( 'X-WP-Total', $query->found_posts );
		$this->header( 'X-WP-TotalPages', $max_page );

		do_action('json_query_navigation_headers', $this, $query);
	}

	/**
	 * Get the HTTP return code for the response
	 *
	 * @since 1.0.0
	 * @return integer 3-digit HTTP status code
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set the HTTP status code
	 *
	 * @since 1.0.0
	 * @param int $code HTTP status
	 */
	public function set_status( $code ) {
		$this->status = absint( $code );
	}

	/**
	 * Get the response data
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Set the response data
	 *
	 * @since 1.0.0
	 * @param mixed $data
	 */
	public function set_data( $data ) {
		$this->data = $data;
	}

	/**
	 * Get the response data for JSON serialization
	 *
	 * It is expected that in most implementations, this will return the same as
	 * {@see get_data()}, however this may be different if you want to do custom
	 * JSON data handling.
	 *
	 * @since 1.0.0
	 * @return mixed Any JSON-serializable value
	 */
	public function jsonSerialize() {
		return $this->get_data();
	}
}
