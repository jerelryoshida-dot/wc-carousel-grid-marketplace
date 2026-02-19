<?php
defined('ABSPATH') || exit;

$product = $product ?? null;
$product_id = $product ? $product->get_id() : 0;
$specialization = $specialization ?? '';
$tiers = $tiers ?? [];

$default_tier = !empty($tiers) ? $tiers[0] : null;
foreach ($tiers as $tier) {
    if ($tier->hourly_price > 0 || $tier->monthly_price > 0) {
        $default_tier = $tier;
        break;
    }
}

$price_types = [];
foreach ($tiers as $tier) {
    if ($tier->hourly_price > 0) $price_types['hourly'] = true;
    if ($tier->monthly_price > 0) $price_types['monthly'] = true;
}
$price_types = array_keys($price_types);
$default_price_type = 'monthly';

$default_price = $default_tier ? $default_tier->monthly_price : 0;
?>

<div class="wc-cgm-pricing-panel"
     data-product-id="<?php echo esc_attr($product_id); ?>"
     data-default-tier="<?php echo esc_attr($default_tier->tier_level ?? 1); ?>"
     data-default-price-type="<?php echo esc_attr($default_price_type); ?>"
     <?php foreach ($tiers as $tier) : ?>
     data-tier-<?php echo esc_attr($tier->tier_level); ?>-hourly="<?php echo esc_attr($tier->hourly_price ?? 0); ?>"
     data-tier-<?php echo esc_attr($tier->tier_level); ?>-monthly="<?php echo esc_attr($tier->monthly_price ?? 0); ?>"
     data-tier-<?php echo esc_attr($tier->tier_level); ?>-name="<?php echo esc_attr($tier->tier_name ?? ''); ?>"
     <?php endforeach; ?>>

    <?php if (count($price_types) > 1) : ?>
    <div class="wc-cgm-price-type-selector">
        <?php foreach ($price_types as $type) : ?>
        <button type="button"
                class="wc-cgm-price-type-btn <?php echo $type === $default_price_type ? 'active' : ''; ?>"
                data-price-type="<?php echo esc_attr($type); ?>">
            <?php echo esc_html($type === 'monthly' ? __('Monthly', 'wc-carousel-grid-marketplace') : __('Hourly', 'wc-carousel-grid-marketplace')); ?>
        </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="wc-cgm-pricing-amount">
        <span class="wc-cgm-price-main" data-price="<?php echo esc_attr($default_price); ?>">
            <?php echo wc_price($default_price); ?>
        </span>
        <span class="wc-cgm-price-sub">
                <?php
                $monthly_equiv = $default_price * 160;
                echo wc_price($monthly_equiv) . '/mo';
                ?>
        </span>
    </div>

    <div class="wc-cgm-headcount">
        <span class="wc-cgm-headcount-label"><?php esc_html_e('Headcount:', 'wc-carousel-grid-marketplace'); ?></span>
        <button type="button" class="wc-cgm-headcount-btn wc-cgm-btn-minus" data-action="decrease">-</button>
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
        <span class="wc-cgm-total-price"
              data-total="<?php echo esc_attr($default_price); ?>"
              data-monthly-price="<?php echo esc_attr($default_tier->monthly_price ?? 0); ?>">
            <?php echo wc_price($default_price); ?>/mo
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
