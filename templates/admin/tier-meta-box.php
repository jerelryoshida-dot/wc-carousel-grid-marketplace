<?php
defined('ABSPATH') || exit;

$post_id = $post->ID ?? 0;
$enabled = get_post_meta($post_id, '_wc_cgm_enabled', true) === 'yes';
$popular = get_post_meta($post_id, '_wc_cgm_popular', true) === 'yes';
$specialization = get_post_meta($post_id, '_wc_cgm_specialization', true);
?>

<div class="wc-cgm-meta-box">
    <p class="wc-cgm-field">
        <label>
            <input type="checkbox" name="wc_cgm_enabled" value="yes" <?php checked($enabled); ?>>
            <?php esc_html_e('Enable for Marketplace', 'wc-carousel-grid-marketplace'); ?>
        </label>
        <span class="description"><?php esc_html_e('Show this product in the marketplace carousel/grid.', 'wc-carousel-grid-marketplace'); ?></span>
    </p>

    <p class="wc-cgm-field">
        <label>
            <input type="checkbox" name="wc_cgm_popular" value="yes" <?php checked($popular); ?>>
            <?php esc_html_e('Mark as Popular', 'wc-carousel-grid-marketplace'); ?>
        </label>
        <span class="description"><?php esc_html_e('Display "Popular" badge on this product (manual override).', 'wc-carousel-grid-marketplace'); ?></span>
    </p>

    <p class="wc-cgm-field">
        <label for="wc_cgm_specialization"><?php esc_html_e('Role Specialization', 'wc-carousel-grid-marketplace'); ?></label>
        <input type="text" 
               id="wc_cgm_specialization" 
               name="wc_cgm_specialization" 
               value="<?php echo esc_attr($specialization); ?>"
               class="regular-text"
               placeholder="<?php esc_attr_e('e.g., Senior support specialist', 'wc-carousel-grid-marketplace'); ?>">
        <span class="description"><?php esc_html_e('Optional subtitle shown below the tier tag in the pricing panel.', 'wc-carousel-grid-marketplace'); ?></span>
    </p>
</div>
