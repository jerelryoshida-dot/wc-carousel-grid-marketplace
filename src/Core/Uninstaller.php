<?php

namespace WC_CGM\Core;

defined('ABSPATH') || exit;

class Uninstaller {
    public static function uninstall(): void {
        if (get_option('wc_cgm_remove_data_on_uninstall', 'no') === 'yes') {
            self::delete_options();
            self::delete_post_meta();
        }
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
