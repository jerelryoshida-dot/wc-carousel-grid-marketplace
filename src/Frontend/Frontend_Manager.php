<?php

namespace WC_CGM\Frontend;

defined('ABSPATH') || exit;

class Frontend_Manager {
    public function __construct() {
        add_action('init', [$this, 'register_shortcodes']);
    }

    public function register_shortcodes(): void {
        add_shortcode('wc_cgm_marketplace', [$this, 'render_marketplace_shortcode']);
    }

    public function render_marketplace_shortcode(array $atts = []): string {
        $defaults = [
            'columns' => (int) get_option('wc_cgm_grid_columns', 3),
            'columns_tablet' => 2,
            'columns_mobile' => 1,
            'category' => '',
            'exclude_category' => '',
            'products' => '',
            'tier' => 0,
            'limit' => (int) get_option('wc_cgm_cards_per_page', 12),
            'orderby' => 'date',
            'order' => 'DESC',
            'show_sidebar' => get_option('wc_cgm_show_sidebar', true) ? 'true' : 'false',
            'show_filter' => get_option('wc_cgm_show_filter_bar', true) ? 'true' : 'false',
            'show_search' => 'false',
            'show_sort' => 'true',
            'layout' => 'grid',
            'mobile_carousel' => get_option('wc_cgm_mobile_carousel', true) ? 'true' : 'false',
            'infinite_scroll' => get_option('wc_cgm_enable_infinite_scroll', false) ? 'true' : 'false',
            'pagination' => 'numbers',
            'popular_only' => 'false',
            'marketplace_only' => 'false',
            'class' => '',
        ];

        $atts = shortcode_atts($defaults, $atts, 'wc_cgm_marketplace');

        $args = [
            'category' => $atts['category'],
            'exclude_category' => $atts['exclude_category'],
            'products' => $atts['products'],
            'tier' => (int) $atts['tier'],
            'limit' => (int) $atts['limit'],
            'orderby' => sanitize_text_field($atts['orderby']),
            'order' => sanitize_text_field($atts['order']),
            'popular_only' => $atts['popular_only'] === 'true',
            'marketplace_only' => $atts['marketplace_only'] === 'true',
        ];

        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');
        $products = $repository->get_marketplace_products($args);
        $categories = $repository->get_categories_with_product_counts();

        $admin_notice = '';
        if (current_user_can('manage_woocommerce') && empty($products)) {
            $admin_notice = $this->get_empty_products_notice($args['marketplace_only']);
        }

        $data = [
            'products' => $products,
            'categories' => $categories,
            'atts' => $atts,
            'repository' => $repository,
            'admin_notice' => $admin_notice,
        ];

        return $this->load_template('marketplace/marketplace.php', $data);
    }

    private function get_empty_products_notice(bool $marketplace_only): string {
        if ($marketplace_only) {
            $message = __('No marketplace products found. Enable products for marketplace in the product editor.', 'wc-carousel-grid-marketplace');
            $link_text = __('Edit Products', 'wc-carousel-grid-marketplace');
            $link_url = admin_url('edit.php?post_type=product');
        } else {
            $message = __('No WooCommerce products found.', 'wc-carousel-grid-marketplace');
            $link_text = __('Add Products', 'wc-carousel-grid-marketplace');
            $link_url = admin_url('post-new.php?post_type=product');
        }

        return sprintf(
            '<div class="wc-cgm-admin-notice" style="background:#fff3cd;border:1px solid #ffc107;padding:12px;margin:10px 0;border-radius:4px;"><p style="margin:0;">%s <a href="%s">%s</a></p></div>',
            esc_html($message),
            esc_url($link_url),
            esc_html($link_text)
        );
    }

    private function load_template(string $template_name, array $data = []): string {
        extract($data);
        ob_start();

        $theme_template = get_stylesheet_directory() . '/wc-carousel-grid-marketplace/' . $template_name;

        if (file_exists($theme_template)) {
            include $theme_template;
        } else {
            include WC_CGM_PLUGIN_DIR . 'templates/' . $template_name;
        }

        return ob_get_clean() ?: '';
    }
}
