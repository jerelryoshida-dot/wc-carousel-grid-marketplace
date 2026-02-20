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

        add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_tier_to_order_item'], 10, 4);

        add_action('wp_ajax_wc_cgm_filter_products', [$this, 'ajax_filter_products']);
        add_action('wp_ajax_nopriv_wc_cgm_filter_products', [$this, 'ajax_filter_products']);
        add_action('wp_ajax_wc_cgm_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_nopriv_wc_cgm_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_wc_cgm_load_more', [$this, 'ajax_load_more']);
        add_action('wp_ajax_nopriv_wc_cgm_load_more', [$this, 'ajax_load_more']);
        add_action('wp_ajax_wc_cgm_search_products', [$this, 'ajax_search_products']);
        add_action('wp_ajax_nopriv_wc_cgm_search_products', [$this, 'ajax_search_products']);
    }

    private function log(string $message, array $context = []): void {
        if (function_exists('wc_cgm_log')) {
            wc_cgm_log($message, $context);
        } else {
            error_log('[WC_CGM] ' . $message . ' | ' . wp_json_encode($context));
        }
    }

    public function update_cart_item_price(\WC_Cart $cart): void {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['welp_tier'])) {
                $tier = $cart_item['welp_tier'];
                $tier_price = (float) ($tier['price'] ?? 0);
                if ($tier_price > 0) {
                    $cart_item['data']->set_price($tier_price);
                }
            }
        }
    }

    public function display_tier_in_cart_checkout(array $item_data, array $cart_item): array {
        if (!isset($cart_item['welp_tier'])) {
            return $item_data;
        }

        $tier = $cart_item['welp_tier'];

        $item_data[] = [
            'key' => __('Experience Level', 'wc-carousel-grid-marketplace'),
            'value' => $tier['name'] ?? '',
        ];

        $price = (float) ($tier['price'] ?? 0);
        $price_type = $tier['price_type'] ?? 'monthly';
        $suffix = $price_type === 'hourly' ? '/hr' : '/mo';

        $item_data[] = [
            'key' => __('Pricing', 'wc-carousel-grid-marketplace'),
            'value' => wc_price($price) . $suffix,
        ];

        return $item_data;
    }

    public function record_sale(int $order_id): void {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $welp_repo = \welp_get_instance()->get_service('repository');
        if (!$welp_repo) {
            return;
        }

        foreach ($order->get_items() as $item_id => $item) {
            $tier_level = $item->get_meta('_welp_tier_level');
            $tier_price = $item->get_meta('_welp_tier_price');
            $tier_price_type = $item->get_meta('_welp_price_type');
            $tier_name = $item->get_meta('_welp_tier_name');

            if ($tier_level && $tier_price) {
                $welp_repo->record_tier_sale(
                    $order_id,
                    $item->get_product_id(),
                    (int) $tier_level,
                    $tier_name ?: 'Tier',
                    (float) $tier_price,
                    $tier_price_type ?: 'monthly',
                    $item->get_quantity()
                );
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
        check_ajax_referer('wc_cgm_frontend_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
        $tier_level = isset($_POST['tier_level']) ? absint($_POST['tier_level']) : 0;
        $price_type = isset($_POST['price_type']) ? sanitize_text_field($_POST['price_type']) : 'monthly';

        $this->log('AJAX add_to_cart START', [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'tier_level' => $tier_level,
            'price_type' => $price_type,
            'is_marketplace_product' => function_exists('wc_cgm_is_marketplace_product') ? wc_cgm_is_marketplace_product($product_id) : 'function not exists',
            'welp_enabled' => function_exists('welp_is_enabled') ? welp_is_enabled($product_id) : 'function not exists',
        ]);

        if ($product_id <= 0) {
            $this->log('ERROR: Invalid product_id', ['product_id' => $product_id]);
            wp_send_json_error(['message' => __('Invalid product.', 'wc-carousel-grid-marketplace')]);
            return;
        }

        if (!WC()->cart) {
            $this->log('ERROR: Cart not available');
            wp_send_json_error(['message' => __('Cart not available.', 'wc-carousel-grid-marketplace')]);
            return;
        }

        $cart_item_data = [];

        if (wc_cgm_is_marketplace_product($product_id)) {
            $this->log('Product is marketplace product', ['product_id' => $product_id, 'tier_level' => $tier_level]);

            if ($tier_level <= 0) {
                $this->log('ERROR: No tier level selected', ['product_id' => $product_id]);
                wp_send_json_error([
                    'message' => __('Please select an experience level before adding to cart.', 'wc-carousel-grid-marketplace')
                ]);
                return;
            }

            $plugin = wc_cgm();
            $repository = $plugin->get_service('repository');
            $tier = $repository->get_tier($product_id, $tier_level);

            $this->log('Tier lookup result', [
                'product_id' => $product_id,
                'tier_level' => $tier_level,
                'tier_found' => $tier ? 'yes' : 'no',
            ]);

            if (!$tier) {
                $this->log('ERROR: Tier not found', ['product_id' => $product_id, 'tier_level' => $tier_level]);
                wp_send_json_error([
                    'message' => __('Selected experience level is not available for this product.', 'wc-carousel-grid-marketplace')
                ]);
                return;
            }

            $price = $price_type === 'monthly' ? $tier->monthly_price : $tier->hourly_price;

            if ($price <= 0) {
                $this->log('ERROR: Price is 0 or negative', ['price' => $price, 'price_type' => $price_type]);
                wp_send_json_error([
                    'message' => __('Selected pricing option is not available for this experience level.', 'wc-carousel-grid-marketplace')
                ]);
                return;
            }

            $cart_item_data['welp_tier'] = [
                'level' => $tier_level,
                'name' => $tier->tier_name,
                'price' => (float) $price,
                'price_type' => $price_type,
            ];

            $this->log('Set welp_tier in cart_item_data', [
                'tier_level' => $tier_level,
                'tier_name' => $tier->tier_name,
                'tier_price' => $price,
                'price_type' => $price_type,
            ]);
        }

        $product = wc_get_product($product_id);
        $this->log('Product state before add_to_cart', [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'cart_item_data' => $cart_item_data,
            'product_exists' => $product ? 'yes' : 'no',
            'product_type' => $product ? $product->get_type() : 'N/A',
            'is_purchasable' => $product ? ($product->is_purchasable() ? 'yes' : 'no') : 'N/A',
            'is_in_stock' => $product ? ($product->is_in_stock() ? 'yes' : 'no') : 'N/A',
            'stock_status' => $product ? $product->get_stock_status() : 'N/A',
            'stock_quantity' => $product ? $product->get_stock_quantity() : 'N/A',
            'manage_stock' => $product ? ($product->managing_stock() ? 'yes' : 'no') : 'N/A',
            'regular_price' => $product ? $product->get_regular_price() : 'N/A',
            'price' => $product ? $product->get_price() : 'N/A',
            'status' => $product ? $product->get_status() : 'N/A',
            'wc_session_exists' => WC()->session ? 'yes' : 'no',
            'wc_customer_exists' => WC()->customer ? 'yes' : 'no',
        ]);

        try {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, [], $cart_item_data);

            $wc_notices = function_exists('wc_get_notices') ? wc_get_notices() : [];
            $this->log('add_to_cart result', [
                'cart_item_key' => $cart_item_key,
                'is_wp_error' => is_wp_error($cart_item_key),
                'is_false' => $cart_item_key === false,
                'wc_notices' => $wc_notices,
                'cart_contents_count' => WC()->cart->get_cart_contents_count(),
            ]);

            if (is_wp_error($cart_item_key)) {
                $this->log('ERROR: WP_Error returned', ['error' => $cart_item_key->get_error_message()]);
                wp_send_json_error(['message' => $cart_item_key->get_error_message()]);
                return;
            }

            if (!$cart_item_key) {
                $error_notices = function_exists('wc_get_notices') ? wc_get_notices('error') : [];
                $all_notices = function_exists('wc_get_notices') ? wc_get_notices() : [];
                
                $this->log('ERROR: add_to_cart returned false', [
                    'error_notices' => $error_notices,
                    'all_notices' => $all_notices,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'product_details' => [
                        'exists' => $product ? 'yes' : 'no',
                        'type' => $product ? $product->get_type() : 'N/A',
                        'purchasable' => $product ? $product->is_purchasable() : 'N/A',
                        'in_stock' => $product ? $product->is_in_stock() : 'N/A',
                        'status' => $product ? $product->get_status() : 'N/A',
                    ],
                    'cart_item_data_passed' => $cart_item_data,
                ]);

                $error_message = __('Could not add to cart.', 'wc-carousel-grid-marketplace');
                if (!empty($error_notices)) {
                    $notice_messages = [];
                    foreach ($error_notices as $notice) {
                        if (is_array($notice) && isset($notice['notice'])) {
                            $notice_messages[] = $notice['notice'];
                        } elseif (is_string($notice)) {
                            $notice_messages[] = $notice;
                        }
                    }
                    if (!empty($notice_messages)) {
                        $error_message = implode(' | ', $notice_messages);
                    }
                }

                if (function_exists('wc_clear_notices')) {
                    wc_clear_notices();
                }

                wp_send_json_error(['message' => $error_message]);
                return;
            }

            WC()->cart->calculate_totals();

            $cart_count = WC()->cart->get_cart_contents_count();
            $cart_total = WC()->cart->get_cart_total();

            $this->log('SUCCESS: Product added to cart', [
                'cart_item_key' => $cart_item_key,
                'cart_count' => $cart_count,
            ]);

            wp_send_json_success([
                'message' => __('Product added to cart!', 'wc-carousel-grid-marketplace'),
                'cart_item_key' => $cart_item_key,
                'cart_count' => $cart_count,
                'cart_total' => $cart_total,
                'cart_hash' => WC()->cart->get_cart_hash(),
            ]);
        } catch (\Exception $e) {
            $this->log('EXCEPTION in add_to_cart', ['exception' => $e->getMessage()]);
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function add_tier_to_order_item(\WC_Order_Item_Product $item, string $cart_item_key, array $cart_item, \WC_Order $order): void {
        if (!isset($cart_item['welp_tier'])) {
            return;
        }

        $tier = $cart_item['welp_tier'];

        $item->add_meta_data('_wc_cgm_tier_level', $tier['level'] ?? 1);
        $item->add_meta_data('_wc_cgm_tier_price', $tier['price'] ?? 0);
        $item->add_meta_data('_wc_cgm_tier_price_type', $tier['price_type'] ?? 'monthly');
        $item->add_meta_data('_wc_cgm_tier_name', $tier['name'] ?? '');
        $item->add_meta_data('Experience Level', $tier['name'] ?? '');

        $this->log('Tier meta added to order item from welp_tier', [
            'item_id' => $item->get_id(),
            'tier_level' => $tier['level'] ?? null,
            'tier_name' => $tier['name'] ?? null,
        ]);
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
