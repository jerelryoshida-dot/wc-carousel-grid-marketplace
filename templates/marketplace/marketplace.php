<?php
defined('ABSPATH') || exit;

$products = $products ?? [];
$categories = $categories ?? [];
$atts = $atts ?? [];
$repository = $repository ?? null;

$columns = (int) ($atts['columns'] ?? 3);
$show_sidebar = ($atts['show_sidebar'] ?? 'true') === 'true';
$show_filter = ($atts['show_filter'] ?? 'true') === 'true';
$show_search = ($atts['show_search'] ?? 'false') === 'true';
$layout = $atts['layout'] ?? 'grid';
$mobile_carousel = ($atts['mobile_carousel'] ?? 'true') === 'true';
$class = $atts['class'] ?? '';
?>

<div class="wc-cgm-marketplace <?php echo esc_attr($class); ?>" 
     data-columns="<?php echo esc_attr($columns); ?>"
     data-layout="<?php echo esc_attr($layout); ?>"
     data-mobile-carousel="<?php echo esc_attr($mobile_carousel ? 'true' : 'false'); ?>">
    
    <?php if ($show_sidebar && !empty($categories)) : ?>
    <aside class="wc-cgm-sidebar">
        <div class="wc-cgm-sidebar-header">
            <h3><?php esc_html_e('Service Categories', 'wc-carousel-grid-marketplace'); ?></h3>
            <p><?php esc_html_e('Browse by expertise area', 'wc-carousel-grid-marketplace'); ?></p>
        </div>
        
        <?php echo \WC_CGM\Frontend\Marketplace::render_sidebar($categories, $atts); ?>
    </aside>
    <?php endif; ?>

    <main class="wc-cgm-content">
        <?php if ($show_search) : ?>
        <div class="wc-cgm-search-bar">
            <input type="search" 
                   class="wc-cgm-search-input" 
                   placeholder="<?php esc_attr_e('Search services...', 'wc-carousel-grid-marketplace'); ?>"
                   aria-label="<?php esc_attr_e('Search services', 'wc-carousel-grid-marketplace'); ?>">
            <button type="button" class="wc-cgm-search-btn">
                <span class="dashicons dashicons-search"></span>
            </button>
        </div>
        <?php endif; ?>

        <?php if ($show_filter && wc_cgm_tier_pricing_enabled()) : ?>
        <?php echo \WC_CGM\Frontend\Marketplace::render_filter_bar($atts); ?>
        <?php endif; ?>

        <div class="wc-cgm-section-header">
            <h2 class="wc-cgm-section-title"><?php esc_html_e('Available Services', 'wc-carousel-grid-marketplace'); ?></h2>
            <p class="wc-cgm-section-count">
                <?php 
                printf(
                    _n('%s role available', '%s roles available', count($products), 'wc-carousel-grid-marketplace'),
                    number_format_i18n(count($products))
                ); 
                ?>
            </p>
        </div>

        <div class="wc-cgm-grid <?php echo $layout === 'hybrid' ? 'wc-cgm-hybrid' : ''; ?>" 
             data-current-category="0"
             data-current-tier="0">
            
            <?php foreach ($products as $product_id) : 
                $product = wc_get_product($product_id);
                if (!$product) continue;
                echo \WC_CGM\Frontend\Marketplace::render_product_card($product, $atts, $repository);
            endforeach; ?>
            
        </div>

        <?php if (empty($products)) : ?>
        <div class="wc-cgm-no-products">
            <p><?php esc_html_e('No services found matching your criteria.', 'wc-carousel-grid-marketplace'); ?></p>
        </div>
        <?php endif; ?>

        <?php if (($atts['infinite_scroll'] ?? 'false') !== 'true' && count($products) >= (int) $atts['limit']) : ?>
        <div class="wc-cgm-load-more-wrap">
            <button type="button" class="wc-cgm-load-more" data-offset="<?php echo esc_attr(count($products)); ?>">
                <?php esc_html_e('Load More', 'wc-carousel-grid-marketplace'); ?>
            </button>
        </div>
        <?php endif; ?>
    </main>
</div>
