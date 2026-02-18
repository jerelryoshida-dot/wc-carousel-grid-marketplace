<?php

namespace WC_CGM\Helpers;

defined('ABSPATH') || exit;

function wc_cgm(): \WC_CGM\Core\Plugin {
    return \WC_CGM\Core\Plugin::get_instance();
}

function wc_cgm_tier_pricing_enabled(): bool {
    return (bool) get_option('wc_cgm_enable_tier_pricing', false);
}

function wc_cgm_is_marketplace_product(int $product_id): bool {
    return get_post_meta($product_id, '_wc_cgm_enabled', true) === 'yes';
}

function wc_cgm_is_popular(int $product_id): bool {
    $method = get_option('wc_cgm_popular_method', 'auto');

    if ($method === 'manual' || $method === 'both') {
        if (get_post_meta($product_id, '_wc_cgm_popular', true) === 'yes') {
            return true;
        }
    }

    if ($method === 'auto' || $method === 'both') {
        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');
        if ($repository) {
            return $repository->is_popular_auto($product_id);
        }
    }

    return false;
}

function wc_cgm_get_tiers(int $product_id): array {
    if (!wc_cgm_tier_pricing_enabled()) {
        return [];
    }

    $plugin = wc_cgm();
    $repository = $plugin->get_service('repository');
    if ($repository) {
        return $repository->get_tiers_by_product($product_id);
    }

    return [];
}

function wc_cgm_format_price(float $price, string $type = ''): string {
    $formatted = wc_price($price);

    if ($type === 'monthly') {
        return $formatted . '<span class="wc-cgm-price-period">/mo</span>';
    } elseif ($type === 'hourly') {
        return $formatted . '<span class="wc-cgm-price-period">/hr</span>';
    }

    return $formatted;
}

function wc_cgm_log(string $message, array $context = []): void {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $logger = \WC_CGM\Core\Debug_Logger::get_instance();
        $logger->debug($message, $context);
    }
}

function wc_cgm_get_shortcode_defaults(): array {
    return [
        'columns' => (int) get_option('wc_cgm_grid_columns', 3),
        'columns_tablet' => 2,
        'columns_mobile' => 1,
        'category' => '',
        'exclude_category' => '',
        'products' => '',
        'tier' => 0,
        'limit' => (int) get_option('wc_cgm_cards_per_page', 12),
        'orderby' => 'date',
        'order' => 'DESC',
        'show_sidebar' => get_option('wc_cgm_show_sidebar', true) ? 'true' : 'false',
        'show_filter' => get_option('wc_cgm_show_filter_bar', true) ? 'true' : 'false',
        'show_search' => 'false',
        'show_sort' => 'true',
        'layout' => 'grid',
        'mobile_carousel' => get_option('wc_cgm_mobile_carousel', true) ? 'true' : 'false',
        'infinite_scroll' => get_option('wc_cgm_enable_infinite_scroll', false) ? 'true' : 'false',
        'pagination' => 'numbers',
        'popular_only' => 'false',
        'class' => '',
    ];
}

function wc_cgm_get_tier_badge_class(int $tier_level): string {
    $classes = [
        1 => 'wc-cgm-tier-entry',
        2 => 'wc-cgm-tier-mid',
        3 => 'wc-cgm-tier-expert',
    ];

    return $classes[$tier_level] ?? 'wc-cgm-tier-default';
}

function wc_cgm_get_tier_color(int $tier_level): string {
    $colors = [
        1 => '#22c55e',
        2 => '#3b82f6',
        3 => '#a855f7',
    ];

    return $colors[$tier_level] ?? '#6b7280';
}

function wc_cgm_calculate_monthly_from_hourly(float $hourly_rate, int $hours_per_month = 160): float {
    return $hourly_rate * $hours_per_month;
}

function wc_cgm_get_category_icon(int $category_id): string {
    $icons = [
        'default' => 'grid',
        'support' => 'headset',
        'admin' => 'clipboard',
        'creative' => 'palette',
        'healthcare' => 'heart',
        'technical' => 'code',
        'finance' => 'dollar-sign',
    ];

    $slug = get_term($category_id)?->slug ?? 'default';

    return $icons[$slug] ?? $icons['default'];
}
