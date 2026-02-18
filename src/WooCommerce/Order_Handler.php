<?php

namespace WC_CGM\WooCommerce;

defined('ABSPATH') || exit;

class Order_Handler {
    public function __construct() {
        add_action('woocommerce_order_status_processing', [$this, 'process_order_tiers']);
        add_action('woocommerce_order_status_completed', [$this, 'process_order_tiers']);
    }

    public function process_order_tiers(int $order_id): void {
        if (!wc_cgm_tier_pricing_enabled()) {
            return;
        }

        $already_processed = get_post_meta($order_id, '_wc_cgm_tiers_processed', true);

        if ($already_processed) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');

        foreach ($order->get_items() as $item_id => $item) {
            $tier_level = $item->get_meta('_wc_cgm_tier_level');
            $tier_price = $item->get_meta('_wc_cgm_tier_price');
            $tier_price_type = $item->get_meta('_wc_cgm_tier_price_type');
            $tier_name = $item->get_meta(__('Experience Level', 'wc-carousel-grid-marketplace'));

            if ($tier_level && $tier_price) {
                $repository->record_tier_sale($order_id, $item->get_product_id(), [
                    'tier_level' => (int) $tier_level,
                    'tier_name' => $tier_name ?: 'Tier',
                    'price' => (float) $tier_price,
                    'price_type' => $tier_price_type ?: 'hourly',
                    'quantity' => $item->get_quantity(),
                ]);
            }
        }

        update_post_meta($order_id, '_wc_cgm_tiers_processed', true);
    }
}
