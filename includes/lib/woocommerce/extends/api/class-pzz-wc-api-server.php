<?php

/**
 * {@inheritdoc}
 *
 * @since      1.2.0
 * @package    PZZ_WC_API_Server
 * @subpackage WooCommerce/API
 * @author     MJHP <mjavadhpour@gmail.com>
 */

include ABSPATH . 'wp-content/plugins/woocommerce/includes/legacy/api/v3/interface-wc-api-handler.php';
include ABSPATH . 'wp-content/plugins/woocommerce/includes/legacy/api/v3/class-wc-api-json-handler.php';
include ABSPATH . 'wp-content/plugins/woocommerce/includes/legacy/api/v3/class-wc-api-server.php';

class PZZ_WC_API_Server extends WC_API_Server { }