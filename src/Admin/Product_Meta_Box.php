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
            'normal',
            'high'
        );

        if (wc_cgm_tier_pricing_enabled()) {
            add_meta_box(
                'wc_cgm_tier_pricing',
                __('Tier Pricing', 'wc-carousel-grid-marketplace'),
                [$this, 'render_tier_meta_box'],
                'product',
                'normal',
                'high'
            );
        }
    }

    public function render_meta_box(\WP_Post $post): void {
        wp_nonce_field('wc_cgm_product_meta', 'wc_cgm_product_meta_nonce');

        $enabled = get_post_meta($post->ID, '_wc_cgm_enabled', true) === 'yes';
        $popular = get_post_meta($post->ID, '_wc_cgm_popular', true) === 'yes';
        $specialization = get_post_meta($post->ID, '_wc_cgm_specialization', true);

        include WC_CGM_PLUGIN_DIR . 'templates/admin/tier-meta-box.php';
    }

    public function render_tier_meta_box(\WP_Post $post): void {
        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');
        
        $tiers = $repository->get_tiers_by_product($post->ID);

        if (empty($tiers)) {
            $tiers = [
                (object) ['tier_level' => 1, 'tier_name' => 'Entry', 'monthly_price' => '', 'hourly_price' => '', 'description' => ''],
                (object) ['tier_level' => 2, 'tier_name' => 'Mid', 'monthly_price' => '', 'hourly_price' => '', 'description' => ''],
                (object) ['tier_level' => 3, 'tier_name' => 'Expert', 'monthly_price' => '', 'hourly_price' => '', 'description' => ''],
            ];
        }

        ?>
        <div class="wc-cgm-tiers-wrap">
            <p class="description" style="margin-bottom: 15px;">
                <?php esc_html_e('Set pricing for each experience level. Leave prices empty if not applicable.', 'wc-carousel-grid-marketplace'); ?>
            </p>
            
            <?php foreach ($tiers as $tier) : ?>
            <div class="wc-cgm-tier-row">
                <h4 class="wc-cgm-tier-heading">
                    <?php echo esc_html($tier->tier_name); ?> 
                    <span class="wc-cgm-tier-level">(Level <?php echo esc_html($tier->tier_level); ?>)</span>
                </h4>
                
                <div class="wc-cgm-tier-fields">
                    <input type="hidden" name="wc_cgm_tiers[<?php echo esc_attr($tier->tier_level); ?>][tier_level]" value="<?php echo esc_attr($tier->tier_level); ?>">
                    
                    <p class="wc-cgm-field">
                        <label><?php esc_html_e('Tier Name', 'wc-carousel-grid-marketplace'); ?></label>
                        <input type="text" 
                               name="wc_cgm_tiers[<?php echo esc_attr($tier->tier_level); ?>][tier_name]" 
                               value="<?php echo esc_attr($tier->tier_name); ?>"
                               placeholder="<?php esc_attr_e('e.g., Entry, Mid, Expert', 'wc-carousel-grid-marketplace'); ?>">
                    </p>
                    
                    <p class="wc-cgm-field wc-cgm-field-half">
                        <label><?php esc_html_e('Monthly Price', 'wc-carousel-grid-marketplace'); ?></label>
                        <input type="number" 
                               name="wc_cgm_tiers[<?php echo esc_attr($tier->tier_level); ?>][monthly_price]" 
                               value="<?php echo esc_attr($tier->monthly_price); ?>"
                               step="0.01" min="0"
                               placeholder="0.00">
                    </p>
                    
                    <p class="wc-cgm-field wc-cgm-field-half">
                        <label><?php esc_html_e('Hourly Price', 'wc-carousel-grid-marketplace'); ?></label>
                        <input type="number" 
                               name="wc_cgm_tiers[<?php echo esc_attr($tier->tier_level); ?>][hourly_price]" 
                               value="<?php echo esc_attr($tier->hourly_price); ?>"
                               step="0.01" min="0"
                               placeholder="0.00">
                    </p>
                    
                    <p class="wc-cgm-field">
                        <label><?php esc_html_e('Description', 'wc-carousel-grid-marketplace'); ?></label>
                        <textarea name="wc_cgm_tiers[<?php echo esc_attr($tier->tier_level); ?>][description]" 
                                  rows="2"
                                  placeholder="<?php esc_attr_e('Brief description of this tier', 'wc-carousel-grid-marketplace'); ?>"><?php echo esc_textarea($tier->description); ?></textarea>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
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

        $enabled = isset($_POST['wc_cgm_enabled']) && $_POST['wc_cgm_enabled'] === 'yes';
        update_post_meta($post_id, '_wc_cgm_enabled', $enabled ? 'yes' : 'no');

        $popular = isset($_POST['wc_cgm_popular']) && $_POST['wc_cgm_popular'] === 'yes';
        update_post_meta($post_id, '_wc_cgm_popular', $popular ? 'yes' : 'no');

        if (isset($_POST['wc_cgm_specialization'])) {
            update_post_meta($post_id, '_wc_cgm_specialization', sanitize_text_field($_POST['wc_cgm_specialization']));
        }

        if (wc_cgm_tier_pricing_enabled() && isset($_POST['wc_cgm_tiers']) && is_array($_POST['wc_cgm_tiers'])) {
            $tiers = [];
            foreach ($_POST['wc_cgm_tiers'] as $tier_data) {
                $tiers[] = [
                    'tier_level' => absint($tier_data['tier_level'] ?? 0),
                    'tier_name' => sanitize_text_field($tier_data['tier_name'] ?? ''),
                    'monthly_price' => !empty($tier_data['monthly_price']) ? (float) $tier_data['monthly_price'] : 0,
                    'hourly_price' => !empty($tier_data['hourly_price']) ? (float) $tier_data['hourly_price'] : 0,
                    'description' => sanitize_textarea_field($tier_data['description'] ?? ''),
                ];
            }

            $plugin = wc_cgm();
            $repository = $plugin->get_service('repository');
            $repository->insert_tiers($post_id, $tiers);
        }
    }
}
