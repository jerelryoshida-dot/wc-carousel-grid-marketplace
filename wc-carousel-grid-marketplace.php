<?php
/**
 * Plugin Name: WooCommerce Carousel/Grid Marketplace
 * Plugin URI: https://github.com/Jerel-R-Yoshida/wc-carousel-grid-marketplace
 * Description: Service marketplace with carousel/grid layout, tiered pricing via WELP, and Elementor compatibility.
 * Version: 1.0.48
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
 *
 * Requires Plugin: wc-experience-level-pricing
 */

defined('ABSPATH') || exit;

define('WC_CGM_VERSION', '1.0.48');
define('WC_CGM_PLUGIN_FILE', __FILE__);
define('WC_CGM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_CGM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_CGM_PLUGIN_BASENAME', plugin_basename(__FILE__));

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

/**
 * Check if Elementor is active
 *
 * @return bool
 */
function wc_cgm_check_elementor(): bool {
    $active_plugins = (array) get_option('active_plugins', []);
    $network_active_plugins = (array) get_site_option('active_sitewide_plugins', []);
    
    $all_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    
    return in_array('elementor/elementor.php', $all_plugins, true)
        || array_key_exists('elementor/elementor.php', $network_active_plugins);
}

function wc_cgm(): WC_CGM\Core\Plugin {
    return WC_CGM\Core\Plugin::get_instance();
}

function wc_cgm_is_marketplace_product(int $product_id): bool {
    if (function_exists('welp_is_enabled')) {
        return \welp_is_enabled($product_id);
    }
    return false;
}

function wc_cgm_is_popular(int $product_id): bool {
    $method = get_option('wc_cgm_popular_method', 'auto');

    if ($method === 'manual' || $method === 'both') {
        if (get_post_meta($product_id, '_wc_cgm_popular', true) === 'yes') {
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
    if (function_exists('welp_get_instance')) {
        $repo = \welp_get_instance()->get_service('repository');
        return $repo ? $repo->get_tiers_by_product($product_id) : [];
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
    if (function_exists('welp_debug')) {
        \welp_debug('[CGM] ' . $message, $context);
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

    if (!class_exists('WELP\Core\Plugin')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>';
            echo '<strong>WooCommerce Carousel/Grid Marketplace</strong> requires ';
            echo '<a href="https://github.com/jerelryoshida-dot/wc-experience-level-pricing" target="_blank">';
            echo 'WooCommerce Experience Level Pricing</a> to be installed and active.';
            echo '</p></div>';
        });
        return;
    }

    wc_cgm();
    
    // Initialize Elementor widgets if Elementor is active
    if (wc_cgm_check_elementor()) {
        add_action('elementor/elements/categories_registered', 'wc_cgm_register_elementor_category');
        add_action('elementor/widgets/register', 'wc_cgm_register_elementor_widgets');
    }
}

/**
 * Register custom Elementor category
 *
 * @param object $elements_manager Elementor elements manager
 */
function wc_cgm_register_elementor_category($elements_manager): void {
    $elements_manager->add_category(
        'yosh-tools',
        [
            'title' => __('Yosh Tools', 'wc-carousel-grid-marketplace'),
            'icon'  => 'fa fa-plug',
        ]
    );
}

/**
 * Register Elementor widgets
 *
 * @param object $widgets_manager Elementor widgets manager
 */
function wc_cgm_register_elementor_widgets($widgets_manager): void {
    if (!class_exists('\Elementor\Widget_Base')) {
        return;
    }
    
    require_once WC_CGM_PLUGIN_DIR . 'src/Elementor/Widgets/Marketplace_Widget.php';
    $widgets_manager->register(new \WC_CGM\Elementor\Widgets\Marketplace_Widget());
}

register_activation_hook(__FILE__, function() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(WC_CGM_PLUGIN_BASENAME);
        wp_die('WooCommerce Carousel/Grid Marketplace requires WooCommerce to be installed and active.');
    }

    if (!class_exists('WELP\Core\Plugin')) {
        deactivate_plugins(WC_CGM_PLUGIN_BASENAME);
        wp_die('WooCommerce Carousel/Grid Marketplace requires WooCommerce Experience Level Pricing to be installed and active.');
    }

    $activator = new WC_CGM\Core\Activator();
    $activator->activate();
});

register_deactivation_hook(__FILE__, function() {
    $deactivator = new WC_CGM\Core\Deactivator();
    $deactivator->deactivate();
});
