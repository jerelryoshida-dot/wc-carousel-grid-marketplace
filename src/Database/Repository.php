<?php

namespace WC_CGM\Database;

defined('ABSPATH') || exit;

class Repository {
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    private function get_welp_repository(): ?object {
        if (!class_exists('WELP\Core\Plugin')) {
            return null;
        }
        return \WELP\Core\Plugin::get_instance()->get_service('repository');
    }

    public function get_marketplace_products(array $args = []): array {
        $defaults = [
            'category' => '',
            'exclude_category' => '',
            'products' => '',
            'tier' => 0,
            'limit' => 12,
            'offset' => 0,
            'orderby' => 'date',
            'order' => 'DESC',
            'popular_only' => false,
            'search' => '',
            'marketplace_only' => false,
        ];

        $args = wp_parse_args($args, $defaults);
        $cache_key = 'wc_cgm_products_' . md5(wp_json_encode($args));
        $cached = wp_cache_get($cache_key, 'wc_cgm');

        if (false !== $cached) {
            return $cached;
        }

        $query_args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => (int) $args['limit'],
            'offset' => (int) $args['offset'],
            'orderby' => $args['orderby'],
            'order' => $args['order'],
        ];

        if (!empty($args['marketplace_only'])) {
            $query_args['meta_query'] = [
                [
                    'key' => '_welp_enabled',
                    'value' => 'yes',
                    'compare' => '=',
                ],
            ];
        }

        if (!empty($args['category'])) {
            $categories = is_array($args['category']) ? $args['category'] : explode(',', $args['category']);
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => array_map('intval', $categories),
                ],
            ];
        }

        if (!empty($args['exclude_category'])) {
            $exclude = is_array($args['exclude_category']) ? $args['exclude_category'] : explode(',', $args['exclude_category']);
            $query_args['tax_query']['relation'] = 'AND';
            $query_args['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => array_map('intval', $exclude),
                'operator' => 'NOT IN',
            ];
        }

        if (!empty($args['products'])) {
            $product_ids = is_array($args['products']) ? $args['products'] : explode(',', $args['products']);
            $query_args['post__in'] = array_map('intval', $product_ids);
            $query_args['orderby'] = 'post__in';
        }

        if (!empty($args['search'])) {
            $query_args['s'] = sanitize_text_field($args['search']);
        }

        if ($args['orderby'] === 'popularity') {
            $query_args['meta_key'] = 'total_sales';
            $query_args['orderby'] = 'meta_value_num';
        } elseif ($args['orderby'] === 'price') {
            $query_args['meta_key'] = '_price';
            $query_args['orderby'] = 'meta_value_num';
        }

        if ($args['popular_only']) {
            $method = get_option('wc_cgm_popular_method', 'auto');
            if ($method === 'manual' || $method === 'both') {
                $query_args['meta_query'][] = [
                    'key' => '_wc_cgm_popular',
                    'value' => 'yes',
                    'compare' => '=',
                ];
            } else {
                $popular_ids = $this->get_popular_product_ids(
                    (int) get_option('wc_cgm_popular_threshold', 5),
                    (int) get_option('wc_cgm_popular_days', 30)
                );
                if (!empty($popular_ids)) {
                    $query_args['post__in'] = isset($query_args['post__in'])
                        ? array_intersect($query_args['post__in'], $popular_ids)
                        : $popular_ids;
                } else {
                    return [];
                }
            }
        }

        if ($args['tier'] > 0) {
            $product_ids = $this->get_products_with_tier((int) $args['tier']);
            if (!empty($product_ids)) {
                $query_args['post__in'] = isset($query_args['post__in'])
                    ? array_intersect($query_args['post__in'] ?? [], $product_ids)
                    : $product_ids;
            } else {
                return [];
            }
        }

        $query = new \WP_Query($query_args);
        $products = $query->posts;

        wp_cache_set($cache_key, $products, 'wc_cgm', HOUR_IN_SECONDS);

        return $products;
    }

    public function get_products_by_category(int $category_id, array $args = []): array {
        $args['category'] = $category_id;
        return $this->get_marketplace_products($args);
    }

    public function get_products_by_tier(int $tier, array $args = []): array {
        $args['tier'] = $tier;
        return $this->get_marketplace_products($args);
    }

    public function get_products_with_tier(int $tier_level): array {
        $welp_repo = $this->get_welp_repository();
        if (!$welp_repo) {
            return [];
        }

        $results = $this->wpdb->get_col($this->wpdb->prepare(
            "SELECT DISTINCT product_id FROM {$this->wpdb->prefix}welp_product_tiers WHERE tier_level = %d",
            $tier_level
        ));

        return array_map('intval', $results);
    }

    public function get_popular_products(int $limit = 10, int $days = 30): array {
        $method = get_option('wc_cgm_popular_method', 'auto');
        $products = [];

        if ($method === 'auto' || $method === 'both') {
            $products = $this->get_popular_product_ids(
                (int) get_option('wc_cgm_popular_threshold', 5),
                $days
            );
        }

        if ($method === 'manual' || $method === 'both') {
            $manual_ids = get_posts([
                'post_type' => 'product',
                'posts_per_page' => $limit,
                'meta_query' => [
                    [
                        'key' => '_wc_cgm_popular',
                        'value' => 'yes',
                        'compare' => '=',
                    ],
                ],
                'fields' => 'ids',
            ]);

            $products = array_unique(array_merge($products, $manual_ids));
        }

        return array_slice($products, 0, $limit);
    }

    public function get_popular_product_ids(int $threshold = 5, int $days = 30): array {
        $date_from = gmdate('Y-m-d H:i:s', strtotime("-{$days} days"));

        $results = $this->wpdb->get_col($this->wpdb->prepare(
            "SELECT pm.post_id
            FROM {$this->wpdb->postmeta} pm
            INNER JOIN {$this->wpdb->prefix}woocommerce_order_items oi ON oi.order_item_name = pm.meta_value
            INNER JOIN {$this->wpdb->prefix}posts p ON p.ID = oi.order_id
            WHERE pm.meta_key = '_welp_product_id'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND p.post_date >= %s
            GROUP BY pm.post_id
            HAVING COUNT(*) >= %d
            ORDER BY COUNT(*) DESC",
            $date_from,
            $threshold
        ));

        return array_map('intval', $results);
    }

    public function is_popular_auto(int $product_id): bool {
        $threshold = (int) get_option('wc_cgm_popular_threshold', 5);
        $days = (int) get_option('wc_cgm_popular_days', 30);
        $popular_ids = $this->get_popular_product_ids($threshold, $days);

        return in_array($product_id, $popular_ids, true);
    }

    public function get_categories_with_product_counts(): array {
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        if (is_wp_error($categories)) {
            return [];
        }

        $result = [];
        $total_count = 0;

        foreach ($categories as $category) {
            $args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'tax_query' => [
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $category->term_id,
                    ],
                ],
                'meta_query' => [
                    [
                        'key' => '_welp_enabled',
                        'value' => 'yes',
                        'compare' => '=',
                    ],
                ],
            ];

            $query = new \WP_Query($args);
            $count = $query->found_posts;

            if ($count > 0) {
                $result[] = [
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $count,
                    'icon' => get_term_meta($category->term_id, 'wc_cgm_icon', true) ?: '',
                ];
                $total_count += $count;
            }
        }

        array_unshift($result, [
            'id' => 0,
            'name' => __('All Services', 'wc-carousel-grid-marketplace'),
            'slug' => 'all',
            'count' => $total_count,
            'icon' => 'grid',
        ]);

        return $result;
    }

    public function search_products(string $query, array $args = []): array {
        $args['search'] = $query;
        return $this->get_marketplace_products($args);
    }

    public function get_specialization(int $product_id): string {
        return get_post_meta($product_id, '_wc_cgm_specialization', true) ?: '';
    }

    public function get_total_marketplace_products(): int {
        $query = new \WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => '_welp_enabled',
                    'value' => 'yes',
                ],
            ],
        ]);

        return $query->found_posts;
    }

    public function get_tiers_by_product(int $product_id): array {
        $welp_repo = $this->get_welp_repository();
        return $welp_repo ? $welp_repo->get_tiers_by_product($product_id) : [];
    }

    public function get_tier(int $product_id, int $tier_level): ?object {
        $welp_repo = $this->get_welp_repository();
        return $welp_repo ? $welp_repo->get_tier($product_id, $tier_level) : null;
    }

    public function get_price_range(int $product_id, string $price_type = 'monthly'): array {
        $welp_repo = $this->get_welp_repository();
        return $welp_repo ? $welp_repo->get_price_range($product_id, $price_type) : ['min' => 0, 'max' => 0];
    }

    public function get_available_price_types(int $product_id): array {
        $welp_repo = $this->get_welp_repository();
        return $welp_repo ? $welp_repo->get_available_price_types($product_id) : [];
    }
}
