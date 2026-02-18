<?php

namespace WC_CGM\Admin;

defined('ABSPATH') || exit;

class Settings {
    private string $option_group = 'wc_cgm_settings';
    private string $option_name = 'wc_cgm_options';

    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings(): void {
        register_setting($this->option_group, 'wc_cgm_enable_tier_pricing', [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        register_setting($this->option_group, 'wc_cgm_grid_columns', [
            'type' => 'integer',
            'default' => 3,
            'sanitize_callback' => 'absint',
        ]);

        register_setting($this->option_group, 'wc_cgm_mobile_carousel', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        register_setting($this->option_group, 'wc_cgm_show_sidebar', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        register_setting($this->option_group, 'wc_cgm_show_filter_bar', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        register_setting($this->option_group, 'wc_cgm_cards_per_page', [
            'type' => 'integer',
            'default' => 12,
            'sanitize_callback' => 'absint',
        ]);

        register_setting($this->option_group, 'wc_cgm_enable_infinite_scroll', [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        register_setting($this->option_group, 'wc_cgm_card_style', [
            'type' => 'string',
            'default' => 'default',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        register_setting($this->option_group, 'wc_cgm_popular_method', [
            'type' => 'string',
            'default' => 'auto',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        register_setting($this->option_group, 'wc_cgm_popular_threshold', [
            'type' => 'integer',
            'default' => 5,
            'sanitize_callback' => 'absint',
        ]);

        register_setting($this->option_group, 'wc_cgm_popular_days', [
            'type' => 'integer',
            'default' => 30,
            'sanitize_callback' => 'absint',
        ]);

        register_setting($this->option_group, 'wc_cgm_remove_data_on_uninstall', [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
    }

    public static function get(string $key, $default = null) {
        return get_option('wc_cgm_' . $key, $default);
    }

    public static function is_tier_pricing_enabled(): bool {
        return (bool) get_option('wc_cgm_enable_tier_pricing', false);
    }

    public static function get_grid_columns(): int {
        return (int) get_option('wc_cgm_grid_columns', 3);
    }

    public static function is_mobile_carousel(): bool {
        return (bool) get_option('wc_cgm_mobile_carousel', true);
    }

    public static function show_sidebar(): bool {
        return (bool) get_option('wc_cgm_show_sidebar', true);
    }

    public static function show_filter_bar(): bool {
        return (bool) get_option('wc_cgm_show_filter_bar', true);
    }

    public static function get_cards_per_page(): int {
        return (int) get_option('wc_cgm_cards_per_page', 12);
    }

    public static function get_popular_method(): string {
        return get_option('wc_cgm_popular_method', 'auto');
    }

    public static function get_popular_threshold(): int {
        return (int) get_option('wc_cgm_popular_threshold', 5);
    }

    public static function get_popular_days(): int {
        return (int) get_option('wc_cgm_popular_days', 30);
    }
}
