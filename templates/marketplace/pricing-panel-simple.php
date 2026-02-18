<?php
defined('ABSPATH') || exit;

$product = $product ?? null;
$product_id = $product ? $product->get_id() : 0;
$price = $product ? $product->get_price() : 0;
?>

<div class="wc-cgm-pricing-panel wc-cgm-simple" data-product-id="<?php echo esc_attr($product_id); ?>">
    
    <div class="wc-cgm-pricing-amount">
        <span class="wc-cgm-price-main" data-price="<?php echo esc_attr($price); ?>">
            <?php echo wc_price($price); ?>
        </span>
        <span class="wc-cgm-price-sub">/<?php esc_html_e('item', 'wc-carousel-grid-marketplace'); ?></span>
    </div>

    <div class="wc-cgm-headcount">
        <span class="wc-cgm-headcount-label"><?php esc_html_e('Quantity:', 'wc-carousel-grid-marketplace'); ?></span>
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
        <span class="wc-cgm-total-price" data-total="<?php echo esc_attr($price); ?>">
            <?php echo wc_price($price); ?>
        </span>
    </div>

    <button type="button" 
            class="wc-cgm-add-to-cart" 
            data-product-id="<?php echo esc_attr($product_id); ?>">
        <span class="dashicons dashicons-cart"></span>
        <span class="wc-cgm-btn-text"><?php esc_html_e('Add to Cart', 'wc-carousel-grid-marketplace'); ?></span>
    </button>
</div>
