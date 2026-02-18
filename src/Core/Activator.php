<?php

namespace WC_CGM\Core;

defined('ABSPATH') || exit;

class Activator {
    public function activate(): void {
        $this->create_tables();
        $this->set_default_options();
        $this->schedule_events();
        
        update_option('wc_cgm_version', WC_CGM_VERSION);
        flush_rewrite_rules();
    }

    private function create_tables(): void {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        if (wc_cgm_tier_pricing_enabled() || get_option('wc_cgm_enable_tier_pricing', false)) {
            $this->create_tier_tables($charset_collate);
        }
    }

    private function create_tier_tables(string $charset_collate): void {
        global $wpdb;

        $tiers_table = $wpdb->prefix . WC_CGM_TABLE_TIERS;
        $sales_table = $wpdb->prefix . WC_CGM_TABLE_SALES;

        $sql_tiers = "CREATE TABLE $tiers_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            product_id bigint(20) unsigned NOT NULL,
            tier_level tinyint(1) unsigned NOT NULL,
            tier_name varchar(100) NOT NULL,
            monthly_price decimal(10,2) DEFAULT NULL,
            hourly_price decimal(10,2) DEFAULT NULL,
            description longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY product_tier (product_id, tier_level),
            KEY tier_level (tier_level)
        ) $charset_collate;";

        $sql_sales = "CREATE TABLE $sales_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned NOT NULL,
            product_id bigint(20) unsigned NOT NULL,
            tier_level tinyint(1) unsigned NOT NULL,
            tier_name varchar(100) NOT NULL,
            price decimal(10,2) NOT NULL,
            price_type varchar(20) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            total decimal(10,2) NOT NULL,
            order_date datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY product_id (product_id),
            KEY order_date (order_date),
            KEY tier_level (tier_level)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_tiers);
        dbDelta($sql_sales);
    }

    private function set_default_options(): void {
        $defaults = [
            'wc_cgm_enable_tier_pricing' => 'no',
            'wc_cgm_grid_columns' => 3,
            'wc_cgm_mobile_carousel' => 'yes',
            'wc_cgm_show_sidebar' => 'yes',
            'wc_cgm_show_filter_bar' => 'yes',
            'wc_cgm_cards_per_page' => 12,
            'wc_cgm_enable_infinite_scroll' => 'no',
            'wc_cgm_card_style' => 'default',
            'wc_cgm_popular_method' => 'auto',
            'wc_cgm_popular_threshold' => 5,
            'wc_cgm_popular_days' => 30,
        ];

        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }

    private function schedule_events(): void {
        if (!wp_next_scheduled('wc_cgm_daily_popular_check')) {
            wp_schedule_event(time(), 'daily', 'wc_cgm_daily_popular_check');
        }
    }
}
