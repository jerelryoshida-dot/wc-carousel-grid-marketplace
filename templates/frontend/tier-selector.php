<?php
defined('ABSPATH') || exit;

$tiers = $tiers ?? [];
$price_types = $price_types ?? ['hourly'];
$default_tier = $default_tier ?? null;
?>

<div class="wc-cgm-tier-selector" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
    <?php if (count($price_types) > 1) : ?>
    <div class="wc-cgm-price-type-toggle">
        <label class="wc-cgm-toggle-label">
            <input type="radio" name="wc_cgm_price_type" value="hourly" checked>
            <?php esc_html_e('Hourly', 'wc-carousel-grid-marketplace'); ?>
        </label>
        <label class="wc-cgm-toggle-label">
            <input type="radio" name="wc_cgm_price_type" value="monthly">
            <?php esc_html_e('Monthly', 'wc-carousel-grid-marketplace'); ?>
        </label>
    </div>
    <?php else : ?>
    <input type="hidden" name="wc_cgm_price_type" value="<?php echo esc_attr($price_types[0]); ?>">
    <?php endif; ?>

    <div class="wc-cgm-tier-options">
        <?php foreach ($tiers as $tier) : 
            $has_hourly = !empty($tier->hourly_price) && $tier->hourly_price > 0;
            $has_monthly = !empty($tier->monthly_price) && $tier->monthly_price > 0;
            $is_default = $default_tier && $default_tier->tier_level === $tier->tier_level;
        ?>
        <label class="wc-cgm-tier-option <?php echo esc_attr(\WC_CGM\Frontend\Marketplace::get_tier_color_class($tier->tier_level)); ?> <?php echo $is_default ? 'selected' : ''; ?>">
            <input type="radio" 
                   name="wc_cgm_tier_level" 
                   value="<?php echo esc_attr($tier->tier_level); ?>"
                   data-tier-name="<?php echo esc_attr($tier->tier_name); ?>"
                   data-hourly-price="<?php echo esc_attr($tier->hourly_price); ?>"
                   data-monthly-price="<?php echo esc_attr($tier->monthly_price); ?>"
                   <?php checked($is_default); ?>>
            
            <div class="wc-cgm-tier-option-content">
                <span class="wc-cgm-tier-radio"></span>
                <div class="wc-cgm-tier-info">
                    <span class="wc-cgm-tier-name"><?php echo esc_html($tier->tier_name); ?></span>
                    <div class="wc-cgm-tier-prices">
                        <?php if ($has_hourly) : ?>
                        <span class="wc-cgm-tier-price wc-cgm-hourly">
                            <?php echo wc_price($tier->hourly_price); ?>/hr
                        </span>
                        <?php endif; ?>
                        <?php if ($has_monthly) : ?>
                        <span class="wc-cgm-tier-price wc-cgm-monthly">
                            <?php echo wc_price($tier->monthly_price); ?>/mo
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($tier->description)) : ?>
            <p class="wc-cgm-tier-desc"><?php echo esc_html($tier->description); ?></p>
            <?php endif; ?>
        </label>
        <?php endforeach; ?>
    </div>
</div>
