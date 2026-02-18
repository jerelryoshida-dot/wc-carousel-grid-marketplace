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
$default_price_type = in_array('hourly', $price_types) ? 'hourly' : ($price_types[0] ?? 'hourly');

$default_price = $default_tier ? ($default_price_type === 'monthly' ? $default_tier->monthly_price : $default_tier->hourly_price) : 0;
?>

<div class="wc-cgm-pricing-panel" 
     data-product-id="<?php echo esc_attr($product_id); ?>"
     data-default-tier="<?php echo esc_attr($default_tier->tier_level ?? 1); ?>"
     data-default-price-type="<?php echo esc_attr($default_price_type); ?>">
    
    <div class="wc-cgm-pricing-header">
        <?php if ($default_tier) : ?>
        <span class="wc-cgm-tier-tag <?php echo esc_attr(\WC_CGM\Frontend\Marketplace::get_tier_color_class($default_tier->tier_level)); ?>">
            <?php echo esc_html($default_tier->tier_name); ?>
        </span>
        <?php endif; ?>
        
        <?php if ($specialization) : ?>
        <span class="wc-cgm-specialization"><?php echo esc_html($specialization); ?></span>
        <?php endif; ?>
    </div>

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
            <?php if ($default_price_type === 'monthly') : ?>
                <?php esc_html_e('/month', 'wc-carousel-grid-marketplace'); ?>
            <?php else : ?>
                <?php 
                $hourly = $default_price;
                $monthly = $hourly * 160;
                echo wc_price($monthly) . '/mo';
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
                <?php echo esc_html($tier->tier_name); ?> - <?php echo wc_price($show_price); ?>/<?php echo $default_price_type === 'monthly' ? 'mo' : 'hr'; ?>
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
        <span class="wc-cgm-total-price" data-total="<?php echo esc_attr($default_price); ?>">
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
