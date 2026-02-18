<?php

namespace WC_CGM\Database;

defined('ABSPATH') || exit;

class Repository {
    private $wpdb;
    private string $tiers_table;
    private string $sales_table;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tiers_table = $wpdb->prefix . WC_CGM_TABLE_TIERS;
        $this->sales_table = $wpdb->prefix . WC_CGM_TABLE_SALES;
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
            'meta_query' => [
                [
                    'key' => '_wc_cgm_enabled',
                    'value' => 'yes',
                    'compare' => '=',
                ],
            ],
        ];

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

        if ($args['tier'] > 0 && wc_cgm_tier_pricing_enabled()) {
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
        $results = $this->wpdb->get_col($this->wpdb->prepare(
            "SELECT DISTINCT product_id FROM {$this->tiers_table} WHERE tier_level = %d",
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
            WHERE pm.meta_key = '_product_id'
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
                        'key' => '_wc_cgm_enabled',
                        'value' => 'yes',
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

    public function is_marketplace_enabled(int $product_id): bool {
        return get_post_meta($product_id, '_wc_cgm_enabled', true) === 'yes';
    }

    public function get_specialization(int $product_id): string {
        return get_post_meta($product_id, '_wc_cgm_specialization', true) ?: '';
    }

    public function get_tiers_by_product(int $product_id): array {
        if (!wc_cgm_tier_pricing_enabled()) {
            return [];
        }

        $cache_key = "wc_cgm_tiers_{$product_id}";
        $cached = wp_cache_get($cache_key, 'wc_cgm');

        if (false !== $cached) {
            return $cached;
        }

        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->tiers_table} WHERE product_id = %d ORDER BY tier_level ASC",
            $product_id
        ));

        wp_cache_set($cache_key, $results, 'wc_cgm', HOUR_IN_SECONDS);

        return $results;
    }

    public function get_tier(int $product_id, int $tier_level): ?object {
        if (!wc_cgm_tier_pricing_enabled()) {
            return null;
        }

        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->tiers_table} WHERE product_id = %d AND tier_level = %d",
            $product_id,
            $tier_level
        ));
    }

    public function get_price_range(int $product_id, string $price_type = 'hourly'): array {
        if (!wc_cgm_tier_pricing_enabled()) {
            return ['min' => 0, 'max' => 0];
        }

        $column = $price_type === 'monthly' ? 'monthly_price' : 'hourly_price';

        $result = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT MIN({$column}) as min_price, MAX({$column}) as max_price 
            FROM {$this->tiers_table} 
            WHERE product_id = %d AND {$column} IS NOT NULL AND {$column} > 0",
            $product_id
        ));

        return [
            'min' => (float) ($result->min_price ?? 0),
            'max' => (float) ($result->max_price ?? 0),
        ];
    }

    public function get_available_price_types(int $product_id): array {
        if (!wc_cgm_tier_pricing_enabled()) {
            return [];
        }

        $types = [];

        $has_monthly = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->tiers_table} 
            WHERE product_id = %d AND monthly_price IS NOT NULL AND monthly_price > 0",
            $product_id
        ));

        $has_hourly = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->tiers_table} 
            WHERE product_id = %d AND hourly_price IS NOT NULL AND hourly_price > 0",
            $product_id
        ));

        if ($has_monthly) {
            $types[] = 'monthly';
        }
        if ($has_hourly) {
            $types[] = 'hourly';
        }

        return $types;
    }

    public function insert_tiers(int $product_id, array $tiers): bool {
        if (!wc_cgm_tier_pricing_enabled()) {
            return false;
        }

        $this->delete_tiers($product_id);

        foreach ($tiers as $tier) {
            $result = $this->wpdb->insert(
                $this->tiers_table,
                [
                    'product_id' => $product_id,
                    'tier_level' => (int) $tier['tier_level'],
                    'tier_name' => sanitize_text_field($tier['tier_name']),
                    'monthly_price' => isset($tier['monthly_price']) && $tier['monthly_price'] > 0 
                        ? (float) $tier['monthly_price'] 
                        : null,
                    'hourly_price' => isset($tier['hourly_price']) && $tier['hourly_price'] > 0 
                        ? (float) $tier['hourly_price'] 
                        : null,
                    'description' => sanitize_textarea_field($tier['description'] ?? ''),
                ],
                ['%d', '%d', '%s', '%f', '%f', '%s']
            );

            if ($result === false) {
                return false;
            }
        }

        wp_cache_delete("wc_cgm_tiers_{$product_id}", 'wc_cgm');

        return true;
    }

    public function delete_tiers(int $product_id): bool {
        $result = $this->wpdb->delete(
            $this->tiers_table,
            ['product_id' => $product_id],
            ['%d']
        );

        wp_cache_delete("wc_cgm_tiers_{$product_id}", 'wc_cgm');

        return $result !== false;
    }

    public function record_tier_sale(int $order_id, int $product_id, array $tier_data): bool {
        if (!wc_cgm_tier_pricing_enabled()) {
            return false;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        $result = $this->wpdb->insert(
            $this->sales_table,
            [
                'order_id' => $order_id,
                'product_id' => $product_id,
                'tier_level' => (int) $tier_data['tier_level'],
                'tier_name' => sanitize_text_field($tier_data['tier_name']),
                'price' => (float) $tier_data['price'],
                'price_type' => sanitize_text_field($tier_data['price_type']),
                'quantity' => (int) ($tier_data['quantity'] ?? 1),
                'total' => (float) $tier_data['price'] * (int) ($tier_data['quantity'] ?? 1),
                'order_date' => $order->get_date_created() ? $order->get_date_created()->format('Y-m-d H:i:s') : current_time('mysql'),
            ],
            ['%d', '%d', '%d', '%s', '%f', '%s', '%d', '%f', '%s']
        );

        return $result !== false;
    }

    public function get_sales_by_tier(array $args = []): array {
        $defaults = [
            'start_date' => '',
            'end_date' => '',
            'product_id' => 0,
            'group_by_price_type' => false,
        ];

        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $values = [];

        if (!empty($args['start_date'])) {
            $where[] = 'order_date >= %s';
            $values[] = $args['start_date'] . ' 00:00:00';
        }

        if (!empty($args['end_date'])) {
            $where[] = 'order_date <= %s';
            $values[] = $args['end_date'] . ' 23:59:59';
        }

        if ($args['product_id'] > 0) {
            $where[] = 'product_id = %d';
            $values[] = (int) $args['product_id'];
        }

        $group_by = $args['group_by_price_type'] 
            ? 'tier_level, tier_name, price_type' 
            : 'tier_level, tier_name';

        $sql = "SELECT 
            tier_level, 
            tier_name, 
            " . ($args['group_by_price_type'] ? 'price_type, ' : '') . "
            SUM(quantity) as total_quantity, 
            SUM(total) as total_revenue, 
            COUNT(DISTINCT order_id) as total_orders,
            AVG(price) as avg_price
            FROM {$this->sales_table} 
            WHERE " . implode(' AND ', $where) . "
            GROUP BY {$group_by}
            ORDER BY tier_level ASC";

        if (!empty($values)) {
            $sql = $this->wpdb->prepare($sql, $values);
        }

        return $this->wpdb->get_results($sql);
    }

    public function get_total_marketplace_products(): int {
        $query = new \WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => '_wc_cgm_enabled',
                    'value' => 'yes',
                ],
            ],
        ]);

        return $query->found_posts;
    }
}
