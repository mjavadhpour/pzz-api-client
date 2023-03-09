<?php

/**
 * @since 1.1.1
 */
interface PZZ_JSON_ResponseInterface extends JsonSerializable
{
    /**
     * Get headers associated with the response
     *
     * @since 1.1.1
     * @return array Map of header name to header value
     */
    public function get_headers();

    /**
     * Get the HTTP return code for the response
     *
     * @since 1.1.1
     * @return integer 3-digit HTTP status code
     */
    public function get_status();

    /**
     * Get the response data
     *
     * @since 1.1.1
     * @return mixed
     */
    public function get_data();

    /**
     * Get the response data for JSON serialization
     *
     * It is expected that in most implementations, this will return the same as
     * {@see get_data()}, however this may be different if you want to do custom
     * JSON data handling.
     *
     * @since 1.1.1
     * @return mixed Any JSON-serializable value
     */
    // public function jsonSerialize();
}
