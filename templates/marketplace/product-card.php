<?php
defined('ABSPATH') || exit;

$product = $product ?? null;
$product_id = $product ? $product->get_id() : 0;
$is_popular = $is_popular ?? false;
$specialization = $specialization ?? '';
$tiers = $tiers ?? [];
$atts = $atts ?? [];

$selected_tier = isset($atts['selected_tier']) ? (int) $atts['selected_tier'] : 1;
$tier_badges = [1 => 'Entry', 2 => 'Mid', 3 => 'Expert'];
$tier_classes = [1 => 'entry', 2 => 'mid', 3 => 'expert'];

$default_tier = !empty($tiers) ? $tiers[0] : null;
foreach ($tiers as $tier) {
    if ($tier->hourly_price > 0 || $tier->monthly_price > 0) {
        $default_tier = $tier;
        break;
    }
}
?>

<div class="wc-cgm-card" data-product-id="<?php echo esc_attr($product_id); ?>" <?php echo !empty($tiers) ? 'data-has-tiers="true"' : ''; ?>>
    
    <?php if ($is_popular) : ?>
    <span class="wc-cgm-badge-popular">
        <?php esc_html_e('Popular', 'wc-carousel-grid-marketplace'); ?>
    </span>
    <?php endif; ?>

    <h3 class="wc-cgm-card-title">
        <a href="<?php echo esc_url($product->get_permalink()); ?>">
            <?php if (!empty($tiers) && $default_tier && isset($tier_badges[$default_tier->tier_level])) : ?>
                <?php echo esc_html($tier_badges[$default_tier->tier_level]); ?>
            <span class="wc-cgm-tier-badge <?php echo esc_attr($tier_classes[$default_tier->tier_level] ?? 'default'); ?>">      
            </span>
            <?php endif; ?>
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
