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
        ob_start();
        include WC_CGM_PLUGIN_DIR . 'templates/marketplace/filter-bar.php';
        return ob_get_clean() ?: '';
    }

    public static function render_product_card(\WC_Product $product, array $atts, $repository): string {
        $product_id = $product->get_id();
        $is_popular = wc_cgm_is_popular($product_id);
        $specialization = $repository->get_specialization($product_id);
        $tiers = $repository->get_tiers_by_product($product_id);

        ob_start();
        include WC_CGM_PLUGIN_DIR . 'templates/marketplace/product-card.php';
        return ob_get_clean() ?: '';
    }

    public static function render_pricing_panel(\WC_Product $product, array $tiers, array $atts): string {
        $product_id = $product->get_id();
        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');
        $specialization = $repository ? $repository->get_specialization($product_id) : '';
        $tiers = $tiers ?? [];

        $tier_data = [
            1 => ['hourly' => 0, 'monthly' => 0, 'name' => 'Entry', 'description' => ''],
            2 => ['hourly' => 0, 'monthly' => 0, 'name' => 'Mid', 'description' => ''],
            3 => ['hourly' => 0, 'monthly' => 0, 'name' => 'Expert', 'description' => ''],
        ];

        foreach ($tiers as $tier) {
            $tier_data[$tier->tier_level] = [
                'hourly' => (float) ($tier->hourly_price ?? 0),
                'monthly' => (float) ($tier->monthly_price ?? 0),
                'name' => $tier->tier_name ?? '',
                'description' => $tier->description ?? '',
            ];
        }

        $default_tier = null;
        foreach ($tiers as $tier) {
            if ($tier->hourly_price > 0 || $tier->monthly_price > 0) {
                $default_tier = $tier;
                break;
            }
        }
        if (!$default_tier && !empty($tiers)) {
            $default_tier = $tiers[0];
        }

        $price_types = [];
        foreach ($tiers as $tier) {
            if ($tier->monthly_price > 0) $price_types['monthly'] = true;
            if ($tier->hourly_price > 0) $price_types['hourly'] = true;
        }
        $price_types = array_keys($price_types);
        $default_price_type = in_array('monthly', $price_types) ? 'monthly' : ($price_types[0] ?? 'monthly');

        $default_tier_description = $default_tier ? ($default_tier->description ?? '') : '';

        $default_price = $default_tier ? ($default_price_type === 'monthly' ? $default_tier->monthly_price : $default_tier->hourly_price) : 0;

        ob_start();
?>
        <div class="wc-cgm-pricing-panel"
             data-product-id="<?php echo esc_attr($product_id); ?>"
             data-default-tier="<?php echo esc_attr($default_tier->tier_level ?? 0); ?>"
             data-default-price-type="<?php echo esc_attr($default_price_type); ?>"
             data-has-tiers="<?php echo !empty($tiers) ? 'true' : 'false'; ?>"
             <?php foreach ([1, 2, 3] as $level) : ?>
             data-tier-<?php echo esc_attr($level); ?>-hourly="<?php echo esc_attr($tier_data[$level]['hourly']); ?>"
             data-tier-<?php echo esc_attr($level); ?>-monthly="<?php echo esc_attr($tier_data[$level]['monthly']); ?>"
             data-tier-<?php echo esc_attr($level); ?>-name="<?php echo esc_attr($tier_data[$level]['name']); ?>"
             data-tier-<?php echo esc_attr($level); ?>-description="<?php echo esc_attr($tier_data[$level]['description'] ?? ''); ?>"
             <?php endforeach; ?>>

            <?php if (count($price_types) > 1) : ?>
            <h4 class="wc-cgm-tier-description"><?php echo esc_html($default_tier_description); ?></h4>
            <div class="wc-cgm-price-type-switch">
                <span class="wc-cgm-switch-label <?php echo $default_price_type === 'monthly' ? 'active' : ''; ?>">
                    <?php esc_html_e('Monthly', 'wc-carousel-grid-marketplace'); ?>
                </span>
                <label class="wc-cgm-switch">
                    <input type="checkbox" class="wc-cgm-switch-input" <?php checked($default_price_type, 'hourly'); ?>>
                    <span class="wc-cgm-switch-slider"></span>
                </label>
                <span class="wc-cgm-switch-label <?php echo $default_price_type === 'hourly' ? 'active' : ''; ?>">
                    <?php esc_html_e('Hourly', 'wc-carousel-grid-marketplace'); ?>
                </span>
            </div>
            <?php endif; ?>

            <div class="wc-cgm-pricing-amount">
                <span class="wc-cgm-price-main" data-price="<?php echo esc_attr(number_format($default_price, 2, '.', '')); ?>">
                    <?php echo wc_price(number_format($default_price, 2, '.', '')); ?>
                </span>
                <span class="wc-cgm-price-sub">
                    <?php if ($default_price_type === 'monthly') : ?>
                        <?php
                        $hourly_price = $default_tier->hourly_price ?? 0;
                        echo wc_price(number_format($hourly_price, 2, '.', '')) . '/hr';
                        ?>
                    <?php else : ?>
                        <?php
                        $monthly_price = $default_tier->monthly_price ?? 0;
                        echo wc_price(number_format($monthly_price, 2, '.', '')) . '/mo';
                        ?>
                    <?php endif; ?>
                </span>
            </div>

            <div class="wc-cgm-tier-selector-mini">
                <select class="wc-cgm-tier-select" name="wc_cgm_tier_level">
                    <?php foreach ($tiers as $tier) :
                        $hourly = $tier->hourly_price ?? 0;
                        $monthly = $tier->monthly_price ?? 0;
                        $show_price = $default_price_type === 'monthly' ? $monthly : $hourly;
                        if ($show_price <= 0) continue;
                    ?>
                    <option value="<?php echo esc_attr($tier->tier_level); ?>"
                        data-tier-name="<?php echo esc_attr($tier->tier_name); ?>"
                        data-hourly="<?php echo esc_attr($hourly); ?>"
                        data-monthly="<?php echo esc_attr($monthly); ?>"
                        <?php selected($tier->tier_level, $default_tier->tier_level ?? 1); ?>>
                        <?php echo esc_html($tier->tier_name); ?> - <?php echo wc_price(number_format($show_price, 2, '.', '')); ?>/<?php echo $default_price_type === 'monthly' ? 'mo' : 'hr'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="wc-cgm-headcount">
                <span class="wc-cgm-headcount-label"><?php esc_html_e('Headcount:', 'wc-carousel-grid-marketplace'); ?></span>
                <button type="button" class="wc-cgm-headcount-btn wc-cgm-btn-minus" data-action="decrease">−</button>
                <input type="number"
                       class="wc-cgm-quantity-input"
                       name="quantity"
                       value="1"
                       min="1"
                       max="99"
                       aria-label="<?php esc_attr_e('Quantity', 'wc-carousel-grid-marketplace'); ?>">
                <button type="button" class="wc-cgm-headcount-btn wc-cgm-btn-plus" data-action="increase">+</button>
            </div>

            <div class="wc-cgm-total">
                <span class="wc-cgm-total-label"><?php esc_html_e('Total', 'wc-carousel-grid-marketplace'); ?></span>
                <span class="wc-cgm-total-price" data-total="<?php echo esc_attr(number_format($default_price, 2, '.', '')); ?>">
                    <?php echo wc_price(number_format($default_price, 2, '.', '')); ?>/mo
                </span>
            </div>

            <button type="button"
                    class="wc-cgm-add-to-cart"
                    data-product-id="<?php echo esc_attr($product_id); ?>"
                    data-tier-level="<?php echo esc_attr($default_tier->tier_level ?? 1); ?>"
                    data-price-type="<?php echo esc_attr($default_price_type); ?>">
                <span class="dashicons dashicons-cart"></span>
                <span class="wc-cgm-btn-text"><?php esc_html_e('Add to Cart', 'wc-carousel-grid-marketplace'); ?></span>
            </button>
        </div>
<?php
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
