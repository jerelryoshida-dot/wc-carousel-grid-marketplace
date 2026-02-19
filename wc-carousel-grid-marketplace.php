<?php
/**
 * Plugin Name: WooCommerce Carousel/Grid Marketplace
 * Plugin URI: https://github.com/Jerel-R-Yoshida/wc-carousel-grid-marketplace
 * Description: Service marketplace with carousel/grid layout, optional tiered pricing, and Elementor compatibility.
 * Version: 1.0.19
 * Author: Jerel Yoshida
 * Author URI: https://github.com/Jerel-R-Yoshida
 * Text Domain: wc-carousel-grid-marketplace
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

// Enable WordPress debug logging for this plugin
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);  // Logs to /wp-content/debug.log
}
if (!defined('WP_DEBUG_DISPLAY')) {
    define('WP_DEBUG_DISPLAY', false);  // Don't show errors on screen
}

define('WC_CGM_VERSION', '1.0.19');
define('WC_CGM_PLUGIN_FILE', __FILE__);
define('WC_CGM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_CGM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_CGM_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WC_CGM_TABLE_TIERS', 'welp_product_tiers');
define('WC_CGM_TABLE_SALES', 'welp_order_tier_sales');

if (!function_exists('wc_cgm_autoloader')) {
    function wc_cgm_autoloader($class) {
        $prefix = 'WC_CGM\\';
        $base_dir = WC_CGM_PLUGIN_DIR . 'src/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    }

    spl_autoload_register('wc_cgm_autoloader');
}

function wc_cgm(): WC_CGM\Core\Plugin {
    return WC_CGM\Core\Plugin::get_instance();
}

function wc_cgm_tier_pricing_enabled(): bool {
    return (bool) get_option('welp_enable_tier_pricing', false);
}

function wc_cgm_is_marketplace_product(int $product_id): bool {
    return get_post_meta($product_id, '_welp_enabled', true) === 'yes';
}

function wc_cgm_is_popular(int $product_id): bool {
    $method = get_option('welp_popular_method', 'auto');
    
    if ($method === 'manual' || $method === 'both') {
        if (get_post_meta($product_id, '_welp_popular', true) === 'yes') {
            return true;
        }
    }
    
    if ($method === 'auto' || $method === 'both') {
        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');
        if ($repository) {
            return $repository->is_popular_auto($product_id);
        }
    }
    
    return false;
}

function wc_cgm_get_tiers(int $product_id): array {
    if (!wc_cgm_tier_pricing_enabled()) {
        return [];
    }
    
    $plugin = wc_cgm();
    $repository = $plugin->get_service('repository');
    if ($repository) {
        return $repository->get_tiers_by_product($product_id);
    }
    
    return [];
}

function wc_cgm_format_price(float $price, string $type = ''): string {
    $formatted = wc_price($price);
    
    if ($type === 'monthly') {
        return $formatted . '<span class="wc-cgm-price-period">/mo</span>';
    } elseif ($type === 'hourly') {
        return $formatted . '<span class="wc-cgm-price-period">/hr</span>';
    }
    
    return $formatted;
}

function wc_cgm_log(string $message, array $context = []): void {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $logger = WC_CGM\Core\Debug_Logger::get_instance();
        $logger->debug($message, $context);
    }
}

add_action('plugins_loaded', 'wc_cgm_init', 11);

function wc_cgm_init() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>';
            echo '<strong>WooCommerce Carousel/Grid Marketplace</strong> requires WooCommerce to be installed and active.';
            echo '</p></div>';
        });
        return;
    }
    
    wc_cgm();
}

register_activation_hook(__FILE__, function() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(WC_CGM_PLUGIN_BASENAME);
        wp_die('WooCommerce Carousel/Grid Marketplace requires WooCommerce to be installed and active.');
    }
    
    $activator = new WC_CGM\Core\Activator();
    $activator->activate();
});

register_deactivation_hook(__FILE__, function() {
    $deactivator = new WC_CGM\Core\Deactivator();
    $deactivator->deactivate();
});
