<?php
defined('ABSPATH') || exit;

$post_id = $post->ID ?? 0;
$popular = get_post_meta($post_id, '_wc_cgm_popular', true) === 'yes';
$specialization = get_post_meta($post_id, '_wc_cgm_specialization', true);
?>

<div class="wc-cgm-meta-box">
    <p class="wc-cgm-field">
        <label>
            <input type="checkbox" name="wc_cgm_popular" value="yes" <?php checked($popular); ?>>
            <?php esc_html_e('Mark as Popular', 'wc-carousel-grid-marketplace'); ?>
        </label>
        <span class="description"><?php esc_html_e('Display "Popular" badge on this product.', 'wc-carousel-grid-marketplace'); ?></span>
    </p>

    <p class="wc-cgm-field">
        <label for="wc_cgm_specialization"><?php esc_html_e('Role Specialization', 'wc-carousel-grid-marketplace'); ?></label>
        <input type="text"
               id="wc_cgm_specialization"
               name="wc_cgm_specialization"
               value="<?php echo esc_attr($specialization); ?>"
               class="widefat"
               placeholder="<?php esc_attr_e('e.g., Senior support specialist', 'wc-carousel-grid-marketplace'); ?>">
        <span class="description"><?php esc_html_e('Subtitle shown below tier tag in pricing panel.', 'wc-carousel-grid-marketplace'); ?></span>
    </p>

    <p class="wc-cgm-field description" style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">
        <em><?php esc_html_e('Note: Tier pricing is managed via the "Experience Level Pricing" metabox.', 'wc-carousel-grid-marketplace'); ?></em>
    </p>
</div>
