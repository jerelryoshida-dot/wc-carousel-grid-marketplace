<?php

namespace WC_CGM\Frontend;

defined('ABSPATH') || exit;

class Marketplace {
    public static function render_sidebar(array $categories, array $atts): string {
        ob_start();
        include WC_CGM_PLUGIN_DIR . 'templates/marketplace/sidebar.php';
        return ob_get_clean() ?: '';
    }

    public static function render_filter_bar(array $atts): string {
        if (!wc_cgm_tier_pricing_enabled()) {
            return '';
        }

        ob_start();
        include WC_CGM_PLUGIN_DIR . 'templates/marketplace/filter-bar.php';
        return ob_get_clean() ?: '';
    }

    public static function render_product_card(\WC_Product $product, array $atts, $repository): string {
        $product_id = $product->get_id();
        $is_popular = wc_cgm_is_popular($product_id);
        $specialization = get_post_meta($product_id, '_wc_cgm_specialization', true);
        $tiers = wc_cgm_tier_pricing_enabled() ? $repository->get_tiers_by_product($product_id) : [];

        ob_start();
        include WC_CGM_PLUGIN_DIR . 'templates/marketplace/product-card.php';
        return ob_get_clean() ?: '';
    }

    public static function render_pricing_panel(\WC_Product $product, array $tiers, array $atts): string {
        $product_id = $product->get_id();
        $specialization = get_post_meta($product_id, '_wc_cgm_specialization', true);

        if (wc_cgm_tier_pricing_enabled() && !empty($tiers)) {
            ob_start();
            include WC_CGM_PLUGIN_DIR . 'templates/marketplace/pricing-panel.php';
            return ob_get_clean() ?: '';
        }

        ob_start();
        include WC_CGM_PLUGIN_DIR . 'templates/marketplace/pricing-panel-simple.php';
        return ob_get_clean() ?: '';
    }

    public static function get_tier_color_class(int $tier_level): string {
        $colors = [
            1 => 'wc-cgm-tier-entry',
            2 => 'wc-cgm-tier-mid',
            3 => 'wc-cgm-tier-expert',
        ];

        return $colors[$tier_level] ?? 'wc-cgm-tier-default';
    }

    public static function calculate_tier_total(float $price, int $quantity, string $price_type): float {
        return $price * $quantity;
    }

    public static function get_default_tier(array $tiers): ?object {
        foreach ($tiers as $tier) {
            if ($tier->monthly_price > 0 || $tier->hourly_price > 0) {
                return $tier;
            }
        }
        return !empty($tiers) ? $tiers[0] : null;
    }
}
