<?php

namespace WC_CGM\WooCommerce;

defined('ABSPATH') || exit;

class WooCommerce_Hooks {
    public function __construct() {
        add_action('woocommerce_before_calculate_totals', [$this, 'update_cart_item_price']);
        add_filter('woocommerce_get_item_data', [$this, 'display_tier_in_cart_checkout'], 10, 2);
        add_action('woocommerce_payment_complete', [$this, 'record_sale']);
        add_action('woocommerce_order_status_processing', [$this, 'record_sale']);
        add_action('woocommerce_order_status_completed', [$this, 'record_sale']);

        add_action('wp_ajax_wc_cgm_filter_products', [$this, 'ajax_filter_products']);
        add_action('wp_ajax_nopriv_wc_cgm_filter_products', [$this, 'ajax_filter_products']);
        add_action('wp_ajax_wc_cgm_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_nopriv_wc_cgm_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_wc_cgm_load_more', [$this, 'ajax_load_more']);
        add_action('wp_ajax_nopriv_wc_cgm_load_more', [$this, 'ajax_load_more']);
        add_action('wp_ajax_wc_cgm_search_products', [$this, 'ajax_search_products']);
        add_action('wp_ajax_nopriv_wc_cgm_search_products', [$this, 'ajax_search_products']);
    }

    public function update_cart_item_price(\WC_Cart $cart): void {
        if (!wc_cgm_tier_pricing_enabled()) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['wc_cgm_tier'])) {
                $tier = $cart_item['wc_cgm_tier'];
                $cart_item['data']->set_price($tier['price']);
            }
        }
    }

    public function display_tier_in_cart_checkout(array $item_data, array $cart_item): array {
        if (!isset($cart_item['wc_cgm_tier'])) {
            return $item_data;
        }

        $tier = $cart_item['wc_cgm_tier'];

        $item_data[] = [
            'key' => __('Experience Level', 'wc-carousel-grid-marketplace'),
            'value' => $tier['name'],
        ];

        $item_data[] = [
            'key' => __('Pricing', 'wc-carousel-grid-marketplace'),
            'value' => wc_price($tier['price']) . '/' . ($tier['price_type'] === 'monthly' ? 'mo' : 'hr'),
        ];

        return $item_data;
    }

    public function record_sale(int $order_id): void {
        if (!wc_cgm_tier_pricing_enabled()) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');

        foreach ($order->get_items() as $item_id => $item) {
            $tier_level = $item->get_meta('_wc_cgm_tier_level');
            $tier_price = $item->get_meta('_wc_cgm_tier_price');
            $tier_price_type = $item->get_meta('_wc_cgm_tier_price_type');

            if ($tier_level && $tier_price) {
                $repository->record_tier_sale($order_id, $item->get_product_id(), [
                    'tier_level' => (int) $tier_level,
                    'tier_name' => $item->get_meta(__('Experience Level', 'wc-carousel-grid-marketplace')) ?: 'Tier',
                    'price' => (float) $tier_price,
                    'price_type' => $tier_price_type ?: 'hourly',
                    'quantity' => $item->get_quantity(),
                ]);
            }
        }
    }

    public function ajax_filter_products(): void {
        check_ajax_referer('wc_cgm_frontend_nonce', 'nonce');

        $category = isset($_POST['category']) ? absint($_POST['category']) : 0;
        $tier = isset($_POST['tier']) ? absint($_POST['tier']) : 0;
        $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
        $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC';
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 12;
        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;

        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');

        $args = [
            'category' => $category > 0 ? $category : '',
            'tier' => $tier,
            'orderby' => $orderby,
            'order' => $order,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $products = $repository->get_marketplace_products($args);

        ob_start();
        foreach ($products as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                echo \WC_CGM\Frontend\Marketplace::render_product_card($product, [], $repository);
            }
        }
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html, 'count' => count($products)]);
    }

    public function ajax_add_to_cart(): void {
        wc_cgm_log('WC_CGM: AJAX add_to_cart called');
        wc_cgm_log('WC_CGM: POST data:', $_POST);

        check_ajax_referer('wc_cgm_frontend_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
        $tier_level = isset($_POST['tier_level']) ? absint($_POST['tier_level']) : 0;
        $price_type = isset($_POST['price_type']) ? sanitize_text_field($_POST['price_type']) : 'simple';

        if ($product_id <= 0) {
            wp_send_json_error(['message' => __('Invalid product.', 'wc-carousel-grid-marketplace')]);
        }

        if (!WC()->cart) {
            wp_send_json_error(['message' => __('Cart not available.', 'wc-carousel-grid-marketplace')]);
        }

        wc_cgm_log('WC_CGM: Adding product ID: ' . $product_id);
        wc_cgm_log('WC_CGM: Tier level: ' . $tier_level);
        wc_cgm_log('WC_CGM: Price type: ' . $price_type);
        wc_cgm_log('WC_CGM: Quantity: ' . $quantity);

        $cart_item_data = [];

        if (wc_cgm_tier_pricing_enabled() && $tier_level > 0) {
            $plugin = wc_cgm();
            $repository = $plugin->get_service('repository');
            $tier = $repository->get_tier($product_id, $tier_level);

            if ($tier) {
                $price = $price_type === 'monthly' ? $tier->monthly_price : $tier->hourly_price;

                $cart_item_data['wc_cgm_tier'] = [
                    'level' => $tier_level,
                    'name' => $tier->tier_name,
                    'price' => (float) $price,
                    'price_type' => $price_type,
                ];
            }
        }

        wc_cgm_log('WC_CGM: Cart item data:', $cart_item_data);

        try {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, [], $cart_item_data);

            if (is_wp_error($cart_item_key)) {
                wc_cgm_log('WC_CGM: add_to_cart WP Error: ' . $cart_item_key->get_error_message());
                wp_send_json_error(['message' => $cart_item_key->get_error_message()]);
            }

            if (!$cart_item_key) {
                wc_cgm_log('WC_CGM: add_to_cart returned false');
                wp_send_json_error(['message' => __('Could not add to cart.', 'wc-carousel-grid-marketplace')]);
            }

            WC()->cart->calculate_totals();

            wc_cgm_log('WC_CGM: Product added successfully. Cart item key: ' . $cart_item_key);

            $cart_count = WC()->cart->get_cart_contents_count();
            $cart_total = WC()->cart->get_cart_total();

            wp_send_json_success([
                'message' => __('Product added to cart!', 'wc-carousel-grid-marketplace'),
                'cart_item_key' => $cart_item_key,
                'cart_count' => $cart_count,
                'cart_total' => $cart_total,
                'cart_hash' => WC()->cart->get_cart_hash(),
            ]);
        } catch (Exception $e) {
            wc_cgm_log('WC_CGM: add_to_cart Exception: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_load_more(): void {
        check_ajax_referer('wc_cgm_frontend_nonce', 'nonce');

        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;
        $category = isset($_POST['category']) ? absint($_POST['category']) : 0;
        $tier = isset($_POST['tier']) ? absint($_POST['tier']) : 0;
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 12;

        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');

        $args = [
            'category' => $category > 0 ? $category : '',
            'tier' => $tier,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $products = $repository->get_marketplace_products($args);

        ob_start();
        foreach ($products as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                echo \WC_CGM\Frontend\Marketplace::render_product_card($product, [], $repository);
            }
        }
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'count' => count($products),
            'has_more' => count($products) === $limit,
        ]);
    }

    public function ajax_search_products(): void {
        check_ajax_referer('wc_cgm_frontend_nonce', 'nonce');

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 12;

        if (strlen($search) < 2) {
            wp_send_json_success(['html' => '', 'count' => 0]);
        }

        $plugin = wc_cgm();
        $repository = $plugin->get_service('repository');

        $products = $repository->search_products($search, ['limit' => $limit]);

        ob_start();
        foreach ($products as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                echo \WC_CGM\Frontend\Marketplace::render_product_card($product, [], $repository);
            }
        }
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html, 'count' => count($products)]);
    }
}
