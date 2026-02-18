<?php
defined('ABSPATH') || exit;

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

define('WC_CGM_TABLE_TIERS', 'wc_cgm_product_tiers');
define('WC_CGM_TABLE_SALES', 'wc_cgm_order_tier_sales');

require_once plugin_dir_path(__FILE__) . 'src/Core/Uninstaller.php';

\WC_CGM\Core\Uninstaller::uninstall();
