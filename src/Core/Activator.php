<?php

namespace WC_CGM\Core;

defined('ABSPATH') || exit;

class Activator {
    public function activate(): void {
        $this->set_default_options();
        $this->schedule_events();

        update_option('wc_cgm_version', WC_CGM_VERSION);
        flush_rewrite_rules();
    }

    private function set_default_options(): void {
        $defaults = [
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
