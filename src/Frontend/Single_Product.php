<?php

namespace WC_CGM\Frontend;

defined('ABSPATH') || exit;

class Single_Product {
    public function __construct() {
        add_filter('woocommerce_get_price_html', [$this, 'filter_price_html'], 10, 2);
        add_action('woocommerce_before_add_to_cart_button', [$this, 'render_tier_selector']);
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_cart_item_data'], 10, 3);
    }

    public function filter_price_html(string $price, \WC_Product $product): string {
        if (!wc_cgm_is_marketplace_product($product->get_id())) {
            return $price;
        }

        if (!wc_cgm_tier_pricing_enabled()) {
            return $price;
        }

        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');
        $tiers = $repository->get_tiers_by_product($product->get_id());

        if (empty($tiers)) {
            return $price;
        }

        $hourly_range = $repository->get_price_range($product->get_id(), 'hourly');
        $monthly_range = $repository->get_price_range($product->get_id(), 'monthly');

        if ($hourly_range['min'] > 0 || $monthly_range['min'] > 0) {
            $price_parts = [];

            if ($hourly_range['min'] > 0) {
                if ($hourly_range['min'] === $hourly_range['max']) {
                    $price_parts[] = wc_price($hourly_range['min']) . '/hr';
                } else {
                    $price_parts[] = wc_price($hourly_range['min']) . ' - ' . wc_price($hourly_range['max']) . '/hr';
                }
            }

            if ($monthly_range['min'] > 0) {
                if ($monthly_range['min'] === $monthly_range['max']) {
                    $price_parts[] = wc_price($monthly_range['min']) . '/mo';
                } else {
                    $price_parts[] = wc_price($monthly_range['min']) . ' - ' . wc_price($monthly_range['max']) . '/mo';
                }
            }

            return '<span class="wc-cgm-price-range">' . implode(' <span class="wc-cgm-price-separator">|</span> ', $price_parts) . '</span>';
        }

        return $price;
    }

    public function render_tier_selector(): void {
        global $product;

        if (!$product || !wc_cgm_is_marketplace_product($product->get_id())) {
            return;
        }

        if (!wc_cgm_tier_pricing_enabled()) {
            return;
        }

        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');
        $tiers = $repository->get_tiers_by_product($product->get_id());

        if (empty($tiers)) {
            return;
        }

        $price_types = $repository->get_available_price_types($product->get_id());
        $default_tier = Marketplace::get_default_tier($tiers);

        include WC_CGM_PLUGIN_DIR . 'templates/frontend/tier-selector.php';
    }

    public function add_cart_item_data(array $cart_item_data, int $product_id, int $variation_id): array {
        if (!wc_cgm_is_marketplace_product($product_id)) {
            return $cart_item_data;
        }

        if (!wc_cgm_tier_pricing_enabled()) {
            return $cart_item_data;
        }

        $tier_level = isset($_POST['wc_cgm_tier_level']) ? absint($_POST['wc_cgm_tier_level']) : 0;
        $price_type = isset($_POST['wc_cgm_price_type']) ? sanitize_text_field($_POST['wc_cgm_price_type']) : 'hourly';

        if ($tier_level > 0) {
            $plugin = wc_cgm();
            $repository = $plugin->get_service('repository');
            $tier = $repository->get_tier($product_id, $tier_level);

            if ($tier) {
                $price = $price_type === 'monthly' ? $tier->monthly_price : $tier->hourly_price;

                $cart_item_data['wc_cgm_tier'] = [
                    'level' => $tier_level,
                    'name' => $tier->tier_name,
                    'price' => (float) $price,
                    'price_type' => $price_type,
                ];
            }
        }

        return $cart_item_data;
    }
}
