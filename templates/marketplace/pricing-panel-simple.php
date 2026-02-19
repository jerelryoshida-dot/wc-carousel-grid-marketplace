<?php
defined('ABSPATH') || exit;

$product = $product ?? null;
$product_id = $product ? $product->get_id() : 0;

$plugin = wc_cgm();
$repository = $plugin ? $plugin->get_service('repository') : null;
$tiers = $repository ? $repository->get_tiers_by_product($product_id) : [];

$default_tier = null;
foreach ($tiers as $tier) {
    if ($tier->hourly_price > 0 || $tier->monthly_price > 0) {
        $default_tier = $tier;
        break;
    }
}

$hourly_price = $default_tier->hourly_price ?? 0;
$monthly_price = $default_tier->monthly_price ?? 0;

$price_types = [];
if ($monthly_price > 0) $price_types['monthly'] = true;
if ($hourly_price > 0) $price_types['hourly'] = true;
$price_types = array_keys($price_types);

$default_price_type = in_array('monthly', $price_types) ? 'monthly' : ($price_types[0] ?? 'hourly');
$default_price = $default_price_type === 'monthly' ? $monthly_price : $hourly_price;
?>

<div class="wc-cgm-pricing-panel wc-cgm-simple" 
     data-product-id="<?php echo esc_attr($product_id); ?>"
     data-default-tier="<?php echo esc_attr($default_tier->tier_level ?? 1); ?>"
     data-default-price-type="<?php echo esc_attr($default_price_type); ?>"
     <?php foreach ([1, 2, 3] as $level) :
        $tier_hourly = 0;
        $tier_monthly = 0;
        foreach ($tiers as $t) {
            if ($t->tier_level == $level) {
                $tier_hourly = $t->hourly_price ?? 0;
                $tier_monthly = $t->monthly_price ?? 0;
                break;
            }
        }
     ?>
     data-tier-<?php echo esc_attr($level); ?>-hourly="<?php echo esc_attr($tier_hourly); ?>"
     data-tier-<?php echo esc_attr($level); ?>-monthly="<?php echo esc_attr($tier_monthly); ?>"
     <?php endforeach; ?>>

    <?php if (count($price_types) > 1) : ?>
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
            <?php if ($default_price_type === 'monthly' && $hourly_price > 0) : ?>
                <?php echo wc_price(number_format($hourly_price, 2, '.', '')); ?>/hr
            <?php elseif ($default_price_type === 'hourly' && $monthly_price > 0) : ?>
                <?php echo wc_price(number_format($monthly_price, 2, '.', '')); ?>/mo
            <?php endif; ?>
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
              data-total="<?php echo esc_attr(number_format($default_price, 2, '.', '')); ?>"
              data-monthly-price="<?php echo esc_attr(number_format($monthly_price, 2, '.', '')); ?>">
            <?php echo wc_price(number_format($default_price, 2, '.', '')); ?>/<?php echo $default_price_type === 'monthly' ? 'mo' : 'hr'; ?>
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
