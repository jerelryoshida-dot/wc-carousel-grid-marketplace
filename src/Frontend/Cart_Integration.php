<?php

namespace WC_CGM\Frontend;

defined('ABSPATH') || exit;

class Cart_Integration {
    public function __construct() {
        add_filter('woocommerce_cart_item_name', [$this, 'display_tier_in_cart'], 10, 3);
        add_filter('woocommerce_cart_item_price', [$this, 'set_tier_price'], 10, 3);
        add_filter('woocommerce_cart_item_subtotal', [$this, 'calculate_tier_subtotal'], 10, 3);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_order_item_meta'], 10, 4);
    }

    public function display_tier_in_cart(string $name, array $cart_item, string $cart_item_key): string {
        if (!isset($cart_item['wc_cgm_tier'])) {
            return $name;
        }

        $tier = $cart_item['wc_cgm_tier'];
        $name .= sprintf(
            '<div class="wc-cgm-cart-tier"><small>%s - %s/%s</small></div>',
            esc_html($tier['name']),
            esc_html(wc_price($tier['price'])),
            esc_html($tier['price_type'] === 'monthly' ? 'mo' : 'hr')
        );

        return $name;
    }

    public function set_tier_price(string $price, array $cart_item, string $cart_item_key): string {
        if (!isset($cart_item['wc_cgm_tier'])) {
            return $price;
        }

        $tier = $cart_item['wc_cgm_tier'];
        return wc_price($tier['price']) . '/' . ($tier['price_type'] === 'monthly' ? 'mo' : 'hr');
    }

    public function calculate_tier_subtotal(string $subtotal, array $cart_item, string $cart_item_key): string {
        if (!isset($cart_item['wc_cgm_tier'])) {
            return $subtotal;
        }

        $tier = $cart_item['wc_cgm_tier'];
        $quantity = $cart_item['quantity'];
        $total = $tier['price'] * $quantity;

        return wc_price($total);
    }

    public function add_order_item_meta(\WC_Order_Item_Product $item, string $cart_item_key, array $cart_item, \WC_Order $order): void {
        if (!isset($cart_item['wc_cgm_tier'])) {
            return;
        }

        $tier = $cart_item['wc_cgm_tier'];

        $item->add_meta_data(__('Experience Level', 'wc-carousel-grid-marketplace'), $tier['name']);
        $item->add_meta_data(__('Price Type', 'wc-carousel-grid-marketplace'), $tier['price_type'] === 'monthly' ? __('Monthly', 'wc-carousel-grid-marketplace') : __('Hourly', 'wc-carousel-grid-marketplace'));
        $item->add_meta_data('_wc_cgm_tier_level', $tier['level']);
        $item->add_meta_data('_wc_cgm_tier_price', $tier['price']);
        $item->add_meta_data('_wc_cgm_tier_price_type', $tier['price_type']);

        $new_price = $tier['price'];
        $item->set_subtotal($new_price * $cart_item['quantity']);
        $item->set_total($new_price * $cart_item['quantity']);
    }
}
