<?php

namespace WC_CGM\Core;

defined('ABSPATH') || exit;

class Plugin {
    private static ?Plugin $instance = null;
    private array $services = [];
    private string $version = WC_CGM_VERSION;
    private bool $woocommerce_services_loaded = false;

    public static function get_instance(): Plugin {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->register_core_services();
        $this->init_hooks();
        $this->schedule_woocommerce_services();
    }

    private function register_core_services(): void {
        $this->services = [
            'logger' => Debug_Logger::get_instance(),
            'repository' => new \WC_CGM\Database\Repository(),
            'settings' => new \WC_CGM\Admin\Settings(),
            'elementor' => new \WC_CGM\Elementor\Elementor_Manager(),
        ];
    }

    private function schedule_woocommerce_services(): void {
        if (class_exists('WooCommerce', false)) {
            $this->register_woocommerce_services();
        } else {
            add_action('woocommerce_loaded', [$this, 'register_woocommerce_services']);
            add_action('plugins_loaded', [$this, 'register_woocommerce_services_fallback'], 20);
        }
    }

    public function register_woocommerce_services(): void {
        if ($this->woocommerce_services_loaded) {
            return;
        }

        $this->woocommerce_services_loaded = true;

        $this->services['admin'] = new \WC_CGM\Admin\Admin_Manager();
        $this->services['frontend'] = new \WC_CGM\Frontend\Frontend_Manager();
        $this->services['woocommerce'] = new \WC_CGM\WooCommerce\WooCommerce_Hooks();
        $this->services['product_meta_box'] = new \WC_CGM\Admin\Product_Meta_Box();

        if (wc_cgm_tier_pricing_enabled()) {
            $this->services['single_product'] = new \WC_CGM\Frontend\Single_Product();
            $this->services['cart_integration'] = new \WC_CGM\Frontend\Cart_Integration();
            $this->services['order_handler'] = new \WC_CGM\WooCommerce\Order_Handler();
        }
    }

    public function register_woocommerce_services_fallback(): void {
        if (!class_exists('WooCommerce', false)) {
            return;
        }
        $this->register_woocommerce_services();
    }

    private function init_hooks(): void {
        add_action('init', [$this, 'load_textdomain']);
        add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_frontend']);
        add_action('admin_enqueue_scripts', [$this, 'maybe_enqueue_admin']);
    }

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'wc-carousel-grid-marketplace',
            false,
            dirname(WC_CGM_PLUGIN_BASENAME) . '/languages'
        );
    }

    public function maybe_enqueue_frontend(): void {
        if (is_admin()) {
            return;
        }
        $this->enqueue_frontend_assets();
    }

    public function maybe_enqueue_admin(string $hook): void {
        $this->enqueue_admin_assets($hook);
    }

    private function enqueue_frontend_assets(): void {
        wp_enqueue_style(
            'wc-cgm-marketplace',
            WC_CGM_PLUGIN_URL . 'assets/css/marketplace.css',
            [],
            WC_CGM_VERSION
        );

        wp_enqueue_style(
            'wc-cgm-frontend',
            WC_CGM_PLUGIN_URL . 'assets/css/frontend.css',
            ['wc-cgm-marketplace'],
            WC_CGM_VERSION
        );

        wp_enqueue_script(
            'wc-cgm-marketplace',
            WC_CGM_PLUGIN_URL . 'assets/js/marketplace.js',
            ['jquery'],
            WC_CGM_VERSION,
            true
        );

        wp_localize_script('wc-cgm-marketplace', 'wc_cgm_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc_cgm_frontend_nonce'),
            'tier_pricing_enabled' => wc_cgm_tier_pricing_enabled(),
            'i18n' => [
                'added_to_cart' => __('Added to cart!', 'wc-carousel-grid-marketplace'),
                'error' => __('An error occurred. Please try again.', 'wc-carousel-grid-marketplace'),
            ],
        ]);

        wp_enqueue_script(
            'wc-cgm-frontend',
            WC_CGM_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery', 'wc-cgm-marketplace'],
            WC_CGM_VERSION,
            true
        );
    }

    private function enqueue_admin_assets(string $hook): void {
        $screen = get_current_screen();
        
        if ($screen && $screen->post_type === 'product') {
            wp_enqueue_style(
                'wc-cgm-admin',
                WC_CGM_PLUGIN_URL . 'assets/css/admin.css',
                [],
                WC_CGM_VERSION
            );

            wp_enqueue_script(
                'wc-cgm-admin',
                WC_CGM_PLUGIN_URL . 'assets/js/admin.js',
                ['jquery'],
                WC_CGM_VERSION,
                true
            );

            wp_localize_script('wc-cgm-admin', 'wc_cgm_admin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_cgm_admin_nonce'),
            ]);
        }

        if (strpos($hook, 'wc-carousel-grid-marketplace') !== false) {
            wp_enqueue_style('wc-cgm-admin', WC_CGM_PLUGIN_URL . 'assets/css/admin.css', [], WC_CGM_VERSION);
        }
    }

    public function get_service(string $name): ?object {
        return $this->services[$name] ?? null;
    }

    public function get_version(): string {
        return $this->version;
    }

    public function is_woocommerce_loaded(): bool {
        return $this->woocommerce_services_loaded;
    }
}
