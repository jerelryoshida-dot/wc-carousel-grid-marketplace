<?php
defined('ABSPATH') || exit;

$atts = $atts ?? [];
$default_tier = (int) ($atts['tier'] ?? 0);
?>

<div class="wc-cgm-filter-bar">
    <span class="wc-cgm-filter-label"><?php esc_html_e('Experience Level:', 'wc-carousel-grid-marketplace'); ?></span>
    
    <div class="wc-cgm-tier-filters">
        <button type="button" 
                class="wc-cgm-tier-btn <?php echo $default_tier === 0 ? 'active' : ''; ?>" 
                data-tier="0">
            <?php esc_html_e('All', 'wc-carousel-grid-marketplace'); ?>
        </button>
        
        <button type="button" 
                class="wc-cgm-tier-btn wc-cgm-tier-entry <?php echo $default_tier === 1 ? 'active' : ''; ?>" 
                data-tier="1">
            <?php esc_html_e('Entry', 'wc-carousel-grid-marketplace'); ?>
        </button>
        
        <button type="button" 
                class="wc-cgm-tier-btn wc-cgm-tier-mid <?php echo $default_tier === 2 ? 'active' : ''; ?>" 
                data-tier="2">
            <?php esc_html_e('Mid', 'wc-carousel-grid-marketplace'); ?>
        </button>
        
        <button type="button" 
                class="wc-cgm-tier-btn wc-cgm-tier-expert <?php echo $default_tier === 3 ? 'active' : ''; ?>" 
                data-tier="3">
            <?php esc_html_e('Expert', 'wc-carousel-grid-marketplace'); ?>
        </button>
    </div>
</div>
