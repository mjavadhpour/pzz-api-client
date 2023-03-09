<?php

/**
 * Extend exception to safely throw inside the plugin.
 *
 * @since      1.2.0
 * @package    Pzz_Api_Client
 * @author     @mjavadhpour on WordPress.org
 */

class PZZ_API_Exception extends Exception
{
    /** @var string sanitized error code */
    protected $error_code;

    /**
     * Setup exception, requires 3 params:
     *
     * error code - machine-readable, e.g. `woocommerce_invalid_product_id`
     * error message - friendly message, e.g. 'Product ID is invalid'
     * http status code - proper HTTP status code to respond with, e.g. 400
     *
     * @since 1.2.0
     * @param string $error_code
     * @param string $error_message user-friendly translated error message
     * @param int $http_status_code HTTP status code to respond with
     */
    public function __construct($error_code, $error_message, $http_status_code)
    {
        $this->error_code = $error_code;
        parent::__construct($error_message, $http_status_code);
    }

    /**
     * Returns the error code
     *
     * @since 1.2.0
     * @return string
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }
}
