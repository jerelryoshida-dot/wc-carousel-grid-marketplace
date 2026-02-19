<?php
defined('ABSPATH') || exit;

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'src/Core/Uninstaller.php';

\WC_CGM\Core\Uninstaller::uninstall();
