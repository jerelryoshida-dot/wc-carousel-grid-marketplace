<?php

namespace WC_CGM\Core;

defined('ABSPATH') || exit;

class Uninstaller {
    public static function uninstall(): void {
        global $wpdb;

        if (get_option('wc_cgm_remove_data_on_uninstall', 'no') === 'yes') {
            self::drop_tables();
            self::delete_options();
            self::delete_post_meta();
        }
    }

    private static function drop_tables(): void {
        global $wpdb;

        $tiers_table = $wpdb->prefix . WC_CGM_TABLE_TIERS;
        $sales_table = $wpdb->prefix . WC_CGM_TABLE_SALES;

        $wpdb->query("DROP TABLE IF EXISTS $tiers_table");
        $wpdb->query("DROP TABLE IF EXISTS $sales_table");
    }

    private static function delete_options(): void {
        global $wpdb;

        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wc_cgm_%'"
        );
    }

    private static function delete_post_meta(): void {
        global $wpdb;

        $wpdb->query(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_wc_cgm_%'"
        );
    }
}
