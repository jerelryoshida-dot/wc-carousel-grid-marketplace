<?php

namespace WC_CGM\Admin;

defined('ABSPATH') || exit;

class Product_Meta_Box {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post_product', [$this, 'save_meta_box'], 10, 2);
    }

    public function add_meta_box(): void {
        add_meta_box(
            'wc_cgm_marketplace',
            __('Marketplace Settings', 'wc-carousel-grid-marketplace'),
            [$this, 'render_meta_box'],
            'product',
            'side',
            'default'
        );
    }

    public function render_meta_box(\WP_Post $post): void {
        wp_nonce_field('wc_cgm_product_meta', 'wc_cgm_product_meta_nonce');

        $popular = get_post_meta($post->ID, '_wc_cgm_popular', true) === 'yes';
        $specialization = get_post_meta($post->ID, '_wc_cgm_specialization', true);

        include WC_CGM_PLUGIN_DIR . 'templates/admin/tier-meta-box.php';
    }

    public function save_meta_box(int $post_id, \WP_Post $post): void {
        if (!isset($_POST['wc_cgm_product_meta_nonce']) || !wp_verify_nonce($_POST['wc_cgm_product_meta_nonce'], 'wc_cgm_product_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $popular = isset($_POST['wc_cgm_popular']) && $_POST['wc_cgm_popular'] === 'yes';
        update_post_meta($post_id, '_wc_cgm_popular', $popular ? 'yes' : 'no');

        if (isset($_POST['wc_cgm_specialization'])) {
            update_post_meta($post_id, '_wc_cgm_specialization', sanitize_text_field($_POST['wc_cgm_specialization']));
        }
    }
}
