<?php

namespace WC_CGM\Elementor;

defined('ABSPATH') || exit;

class Elementor_Manager {
    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init(): void {
        if (!did_action('elementor/loaded')) {
            return;
        }

        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'register_category']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
    }

    public function register_category($elements_manager): void {
        $elements_manager->add_category('wc-carousel-grid', [
            'title' => __('WooCommerce Marketplace', 'wc-carousel-grid-marketplace'),
            'icon' => 'fa fa-shopping-cart',
        ]);
    }

    public function register_widgets($widgets_manager): void {
        require_once WC_CGM_PLUGIN_DIR . 'src/Elementor/Widgets/Marketplace_Widget.php';
        $widgets_manager->register(new Widgets\Marketplace_Widget());
    }

    public function enqueue_styles(): void {
        wp_enqueue_style('wc-cgm-marketplace');
        wp_enqueue_style('wc-cgm-frontend');
    }
}
