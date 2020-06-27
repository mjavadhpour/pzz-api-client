<?php

/**
 * {@inheritdoc}
 *
 * @since      1.2.0
 * @package    PZZ_WC_Checkout
 * @subpackage WooCommerce/Classes
 * @author     MJHP <mjavadhpour@gmail.com>
 */

include ABSPATH . 'wp-content/plugins/woocommerce/includes/class-wc-checkout.php';

class PZZ_WC_Checkout extends WC_Checkout {
    /**
     * {@inheritdoc}
     *
     */
    public function process_checkout() {
        if (empty($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'woocommerce-process_checkout')) {
            WC()->session->set('refresh_totals', true);
            throw new Exception(__('We were unable to process your order, please try again.', 'woocommerce'));
        }

        $_REQUEST['_wpnonce'] = $_POST['_wpnonce'];

        return parent::process_checkout();
    }
 }