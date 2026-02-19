<?php

namespace WC_CGM\Services;

defined('ABSPATH') || exit;

class Email_Service
{
    private string $content_type = 'text/html';

    public function send(
        string $to,
        string $subject,
        string $template_name,
        array $data = [],
        array $attachments = [],
        array $cc = [],
        array $bcc = []
    ): bool {
        if (!is_email($to)) {
            return false;
        }

        $subject = sanitize_text_field($subject);
        $message = $this->render_template($template_name, $data);

        if (empty($message)) {
            return false;
        }

        $headers = ['Content-Type: ' . $this->content_type . '; charset=UTF-8'];

        foreach ($cc as $email) {
            if (is_email($email)) {
                $headers[] = 'Cc: ' . $email;
            }
        }

        foreach ($bcc as $email) {
            if (is_email($email)) {
                $headers[] = 'Bcc: ' . $email;
            }
        }

        $sent = wp_mail($to, $subject, $message, $headers, $attachments);

        $this->log_email($to, $subject, $sent);

        return $sent;
    }

    public function render_template(string $template_name, array $data = []): string
    {
        $template_path = WC_CGM_PLUGIN_DIR . 'templates/emails/' . $template_name . '.php';

        if (!file_exists($template_path)) {
            return '';
        }

        ob_start();
        extract($data, EXTR_SKIP);
        include $template_path;

        return ob_get_clean() ?: '';
    }

    public function get_content_type(): string
    {
        return $this->content_type;
    }

    public function set_content_type(string $type): void
    {
        $this->content_type = $type;
    }

    private function log_email(string $to, string $subject, bool $sent): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[WC_CGM] Email %s: to=%s, subject=%s',
                $sent ? 'sent' : 'failed',
                $to,
                $subject
            ));
        }
    }
}
