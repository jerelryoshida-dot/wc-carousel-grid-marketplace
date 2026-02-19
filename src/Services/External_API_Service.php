<?php

namespace WC_CGM\Services;

defined('ABSPATH') || exit;

class External_API_Service
{
    private int $timeout = 30;
    private array $headers = [];
    private int $cache_ttl = 3600;
    private int $max_retries = 3;
    private string $cache_prefix = 'wc_cgm_api_';

    public function get(string $url, array $args = []): array
    {
        $args = array_merge($this->get_default_args(), $args);
        $response = wp_remote_get($url, $args);

        return $this->parse_response($response, $url);
    }

    public function post(string $url, array $body = [], array $args = []): array
    {
        $args = array_merge($this->get_default_args(), [
            'body' => $body,
        ], $args);

        $response = wp_remote_post($url, $args);

        return $this->parse_response($response, $url);
    }

    public function get_cached(string $url, array $args = []): array
    {
        $cache_key = $this->get_cache_key($url);
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $response = $this->get($url, $args);

        if ($response['success']) {
            set_transient($cache_key, $response, $this->cache_ttl);
        }

        return $response;
    }

    public function set_headers(array $headers): void
    {
        $this->headers = $headers;
    }

    public function set_timeout(int $seconds): void
    {
        $this->timeout = $seconds;
    }

    public function set_cache_ttl(int $seconds): void
    {
        $this->cache_ttl = $seconds;
    }

    public function set_max_retries(int $count): void
    {
        $this->max_retries = $count;
    }

    public function clear_cache(string $url = ''): void
    {
        if ($url) {
            delete_transient($this->get_cache_key($url));
        } else {
            global $wpdb;
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_' . $this->cache_prefix) . '%'
            ));
        }
    }

    public function validate_response(array $response): bool
    {
        return isset($response['success']) && $response['success'] === true;
    }

    private function get_default_args(): array
    {
        return [
            'timeout' => $this->timeout,
            'sslverify' => true,
            'headers' => $this->headers,
        ];
    }

    private function parse_response($response, string $url): array
    {
        if (is_wp_error($response)) {
            $this->log_error($url, $response->get_error_message());

            return [
                'success' => false,
                'error' => $response->get_error_message(),
                'code' => 0,
                'data' => null,
            ];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return [
            'success' => $code >= 200 && $code < 300,
            'code' => $code,
            'data' => $data,
            'raw' => $body,
        ];
    }

    private function get_cache_key(string $url): string
    {
        return $this->cache_prefix . md5($url);
    }

    private function log_error(string $url, string $error): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[WC_CGM] API Error: url=%s, error=%s',
                $url,
                $error
            ));
        }
    }
}
