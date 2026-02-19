<?php
/**
 * Helper functions - Bioloom Certificate System
 */

if (!function_exists('generate_uuid')) {
    function generate_uuid(): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('generate_cert_token')) {
    function generate_cert_token(): string {
        return bin2hex(random_bytes(32));
    }
}

if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    function base_url(): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        return rtrim($scheme . '://' . $host . $script, '/');
    }
}

if (!function_exists('format_certificate_date')) {
    /** Format date for certificate text, e.g. "21st of February 2026" */
    function format_certificate_date(string $dateStr): string {
        $t = strtotime($dateStr);
        $day = (int) date('j', $t);
        $suffix = match ($day) {
            1, 21, 31 => 'st',
            2, 22 => 'nd',
            3, 23 => 'rd',
            default => 'th',
        };
        return $day . $suffix . ' of ' . date('F Y', $t);
    }
}
