<?php

namespace WC_CGM\Elementor;

defined('ABSPATH') || exit;

class Elementor_Manager {
    private const MINIMUM_ELEMENTOR_VERSION = '3.5.0';

    public function __construct() {
        add_action('elementor/loaded', [$this, 'init']);
    }

    public function init(): void {
        if (!$this->is_compatible()) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_version']);
            return;
        }

        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'register_category']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
    }

    private function is_compatible(): bool {
        if (!class_exists('\Elementor\Plugin')) {
            return false;
        }

        if (!defined('ELEMENTOR_VERSION')) {
            return false;
        }

        return version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=');
    }

    public function admin_notice_minimum_version(): void {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            esc_html__('"WC Carousel Grid Marketplace" requires Elementor version %s or greater.', 'wc-carousel-grid-marketplace'),
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message);
    }

    public function register_category($elements_manager): void {
        $elements_manager->add_category('yosh-tools', [
            'title' => __('Yosh Tools', 'wc-carousel-grid-marketplace'),
            'icon' => 'eicon-tools',
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
