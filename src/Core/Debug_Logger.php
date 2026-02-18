<?php

namespace WC_CGM\Core;

defined('ABSPATH') || exit;

class Debug_Logger {
    private static ?Debug_Logger $instance = null;
    private bool $enabled;
    private string $log_file;

    public static function get_instance(): Debug_Logger {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->enabled = defined('WP_DEBUG') && WP_DEBUG;
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/wc-cgm-debug.log';
    }

    public function debug(string $message, array $context = []): void {
        $this->log('DEBUG', $message, $context);
    }

    public function info(string $message, array $context = []): void {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void {
        $this->log('ERROR', $message, $context);
        if ($this->enabled) {
            error_log('[WC-CGM ERROR] ' . $message . ' | Context: ' . wp_json_encode($context));
        }
    }

    private function log(string $level, string $message, array $context = []): void {
        if (!$this->enabled) {
            return;
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $context_str = !empty($context) ? ' | ' . wp_json_encode($context) : '';
        $log_entry = "[{$timestamp}] [{$level}] {$message}{$context_str}\n";

        if (is_writable(dirname($this->log_file))) {
            file_put_contents($this->log_file, $log_entry, FILE_APPEND);
        }
    }

    public function get_log_contents(int $lines = 100): string {
        if (!file_exists($this->log_file)) {
            return '';
        }

        $content = file_get_contents($this->log_file);
        $all_lines = explode("\n", $content);
        $recent_lines = array_slice($all_lines, -$lines);
        
        return implode("\n", $recent_lines);
    }

    public function clear_log(): bool {
        if (file_exists($this->log_file)) {
            return file_put_contents($this->log_file, '') !== false;
        }
        return true;
    }
}
