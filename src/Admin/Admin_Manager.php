<?php

namespace WC_CGM\Admin;

defined('ABSPATH') || exit;

class Admin_Manager {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_admin_menu(): void {
        add_submenu_page(
            'woocommerce',
            __('Marketplace Settings', 'wc-carousel-grid-marketplace'),
            __('Marketplace', 'wc-carousel-grid-marketplace'),
            'manage_woocommerce',
            'wc-carousel-grid-marketplace',
            [$this, 'render_settings_page']
        );
    }

    public function enqueue_assets(string $hook): void {
        if ($hook !== 'woocommerce_page_wc-carousel-grid-marketplace') {
            return;
        }

        wp_enqueue_style(
            'wc-cgm-admin',
            WC_CGM_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WC_CGM_VERSION
        );

        wp_enqueue_script(
            'wc-cgm-admin-settings',
            WC_CGM_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WC_CGM_VERSION,
            true
        );
    }

    public function render_settings_page(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wc-carousel-grid-marketplace'));
        }

        if (isset($_POST['wc_cgm_save_settings']) && check_admin_referer('wc_cgm_settings_nonce')) {
            $this->save_settings();
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'wc-carousel-grid-marketplace') . '</p></div>';
        }

        $this->render_settings_form();
    }

    private function save_settings(): void {
        $settings = [
            'wc_cgm_enable_tier_pricing' => isset($_POST['wc_cgm_enable_tier_pricing']),
            'wc_cgm_grid_columns' => absint($_POST['wc_cgm_grid_columns'] ?? 3),
            'wc_cgm_mobile_carousel' => isset($_POST['wc_cgm_mobile_carousel']),
            'wc_cgm_show_sidebar' => isset($_POST['wc_cgm_show_sidebar']),
            'wc_cgm_show_filter_bar' => isset($_POST['wc_cgm_show_filter_bar']),
            'wc_cgm_cards_per_page' => absint($_POST['wc_cgm_cards_per_page'] ?? 12),
            'wc_cgm_enable_infinite_scroll' => isset($_POST['wc_cgm_enable_infinite_scroll']),
            'wc_cgm_card_style' => sanitize_text_field($_POST['wc_cgm_card_style'] ?? 'default'),
            'wc_cgm_popular_method' => sanitize_text_field($_POST['wc_cgm_popular_method'] ?? 'auto'),
            'wc_cgm_popular_threshold' => absint($_POST['wc_cgm_popular_threshold'] ?? 5),
            'wc_cgm_popular_days' => absint($_POST['wc_cgm_popular_days'] ?? 30),
            'wc_cgm_remove_data_on_uninstall' => isset($_POST['wc_cgm_remove_data_on_uninstall']),
        ];

        foreach ($settings as $option => $value) {
            update_option($option, $value);
        }

        if ($settings['wc_cgm_enable_tier_pricing']) {
            $activator = new \WC_CGM\Core\Activator();
            $activator->activate();
        }
    }

    private function render_settings_form(): void {
        ?>
        <div class="wrap wc-cgm-admin-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="wc-cgm-admin-header">
                <p class="description">
                    <?php esc_html_e('Configure the WooCommerce Carousel/Grid Marketplace settings.', 'wc-carousel-grid-marketplace'); ?>
                </p>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('wc_cgm_settings_nonce'); ?>

                <div class="wc-cgm-settings-section">
                    <h2><?php esc_html_e('General Settings', 'wc-carousel-grid-marketplace'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Grid Columns', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <input type="number" name="wc_cgm_grid_columns" value="<?php echo esc_attr(get_option('wc_cgm_grid_columns', 3)); ?>" min="1" max="6">
                                <p class="description"><?php esc_html_e('Number of columns in the grid layout (1-6).', 'wc-carousel-grid-marketplace'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Products per Page', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <input type="number" name="wc_cgm_cards_per_page" value="<?php echo esc_attr(get_option('wc_cgm_cards_per_page', 12)); ?>" min="1" max="100">
                                <p class="description"><?php esc_html_e('Maximum number of products to display per page.', 'wc-carousel-grid-marketplace'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Mobile Carousel', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wc_cgm_mobile_carousel" value="1" <?php checked(get_option('wc_cgm_mobile_carousel', true)); ?>>
                                    <?php esc_html_e('Enable carousel on mobile devices', 'wc-carousel-grid-marketplace'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Show Sidebar', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wc_cgm_show_sidebar" value="1" <?php checked(get_option('wc_cgm_show_sidebar', true)); ?>>
                                    <?php esc_html_e('Display category sidebar', 'wc-carousel-grid-marketplace'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Show Filter Bar', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wc_cgm_show_filter_bar" value="1" <?php checked(get_option('wc_cgm_show_filter_bar', true)); ?>>
                                    <?php esc_html_e('Display tier filter bar (requires tier pricing)', 'wc-carousel-grid-marketplace'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Infinite Scroll', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wc_cgm_enable_infinite_scroll" value="1" <?php checked(get_option('wc_cgm_enable_infinite_scroll', false)); ?>>
                                    <?php esc_html_e('Enable infinite scroll instead of pagination', 'wc-carousel-grid-marketplace'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Card Style', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <select name="wc_cgm_card_style">
                                    <option value="default" <?php selected(get_option('wc_cgm_card_style', 'default'), 'default'); ?>><?php esc_html_e('Default', 'wc-carousel-grid-marketplace'); ?></option>
                                    <option value="compact" <?php selected(get_option('wc_cgm_card_style'), 'compact'); ?>><?php esc_html_e('Compact', 'wc-carousel-grid-marketplace'); ?></option>
                                    <option value="detailed" <?php selected(get_option('wc_cgm_card_style'), 'detailed'); ?>><?php esc_html_e('Detailed', 'wc-carousel-grid-marketplace'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="wc-cgm-settings-section">
                    <h2><?php esc_html_e('Tier Pricing', 'wc-carousel-grid-marketplace'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Enable Tier Pricing', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wc_cgm_enable_tier_pricing" value="1" <?php checked(get_option('wc_cgm_enable_tier_pricing', false)); ?>>
                                    <?php esc_html_e('Enable Entry/Mid/Expert tier pricing system', 'wc-carousel-grid-marketplace'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('When enabled, products can have multiple price tiers for different experience levels.', 'wc-carousel-grid-marketplace'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="wc-cgm-settings-section">
                    <h2><?php esc_html_e('Popular Badge', 'wc-carousel-grid-marketplace'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Popular Method', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <select name="wc_cgm_popular_method">
                                    <option value="auto" <?php selected(get_option('wc_cgm_popular_method', 'auto'), 'auto'); ?>><?php esc_html_e('Automatic (based on sales)', 'wc-carousel-grid-marketplace'); ?></option>
                                    <option value="manual" <?php selected(get_option('wc_cgm_popular_method'), 'manual'); ?>><?php esc_html_e('Manual (set per product)', 'wc-carousel-grid-marketplace'); ?></option>
                                    <option value="both" <?php selected(get_option('wc_cgm_popular_method'), 'both'); ?>><?php esc_html_e('Both (auto + manual)', 'wc-carousel-grid-marketplace'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Sales Threshold', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <input type="number" name="wc_cgm_popular_threshold" value="<?php echo esc_attr(get_option('wc_cgm_popular_threshold', 5)); ?>" min="1" max="100">
                                <p class="description"><?php esc_html_e('Minimum sales required for auto-popular badge.', 'wc-carousel-grid-marketplace'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Lookback Period (Days)', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <input type="number" name="wc_cgm_popular_days" value="<?php echo esc_attr(get_option('wc_cgm_popular_days', 30)); ?>" min="1" max="365">
                                <p class="description"><?php esc_html_e('Number of days to look back for sales count.', 'wc-carousel-grid-marketplace'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="wc-cgm-settings-section">
                    <h2><?php esc_html_e('Shortcode Usage', 'wc-carousel-grid-marketplace'); ?></h2>
                    
                    <div class="wc-cgm-shortcode-info">
                        <p><strong><?php esc_html_e('Basic Usage:', 'wc-carousel-grid-marketplace'); ?></strong></p>
                        <code>[wc_cgm_marketplace]</code>
                        
                        <p class="wc-cgm-mt-2"><strong><?php esc_html_e('With Parameters:', 'wc-carousel-grid-marketplace'); ?></strong></p>
                        <code>[wc_cgm_marketplace columns="4" category="15" limit="8" show_sidebar="false"]</code>
                        
                        <p class="wc-cgm-mt-2"><strong><?php esc_html_e('Elementor:', 'wc-carousel-grid-marketplace'); ?></strong></p>
                        <p><?php esc_html_e('Use the native "WC Marketplace" widget in Elementor for full visual controls.', 'wc-carousel-grid-marketplace'); ?></p>
                    </div>
                </div>

                <div class="wc-cgm-settings-section">
                    <h2><?php esc_html_e('Data Management', 'wc-carousel-grid-marketplace'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Remove Data on Uninstall', 'wc-carousel-grid-marketplace'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wc_cgm_remove_data_on_uninstall" value="1" <?php checked(get_option('wc_cgm_remove_data_on_uninstall', false)); ?>>
                                    <?php esc_html_e('Delete all plugin data when uninstalling', 'wc-carousel-grid-marketplace'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Warning: This will permanently delete all tier pricing and sales data.', 'wc-carousel-grid-marketplace'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <p class="submit">
                    <input type="submit" name="wc_cgm_save_settings" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'wc-carousel-grid-marketplace'); ?>">
                </p>
            </form>
        </div>
        <?php
    }
}
