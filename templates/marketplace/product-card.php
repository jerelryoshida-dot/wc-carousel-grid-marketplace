<?php
defined('ABSPATH') || exit;

$product = $product ?? null;
$product_id = $product ? $product->get_id() : 0;
$is_popular = $is_popular ?? false;
$specialization = $specialization ?? '';
$tiers = $tiers ?? [];
$atts = $atts ?? [];
?>

<div class="wc-cgm-card" data-product-id="<?php echo esc_attr($product_id); ?>" <?php echo !empty($tiers) ? 'data-has-tiers="true"' : ''; ?>>
    
    <?php if ($is_popular) : ?>
    <span class="wc-cgm-badge-popular">
        <?php esc_html_e('Popular', 'wc-carousel-grid-marketplace'); ?>
    </span>
    <?php endif; ?>

    <h3 class="wc-cgm-card-title">
        <a href="<?php echo esc_url($product->get_permalink()); ?>">
            <?php echo esc_html($product->get_name()); ?>
        </a>
    </h3>

    <p class="wc-cgm-card-desc">
        <?php 
        $excerpt = $product->get_short_description();
        if ($excerpt) {
            echo esc_html(wp_trim_words($excerpt, 20, '...'));
        } else {
            echo esc_html(wp_trim_words($product->get_description(), 20, '...'));
        }
        ?>
    </p>

    <?php echo \WC_CGM\Frontend\Marketplace::render_pricing_panel($product, $tiers, $atts); ?>
</div>
