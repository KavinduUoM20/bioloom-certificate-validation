<?php
/**
 * Auth helper – session and login check for admin area
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return !empty($_SESSION['auth_ok']);
}

function require_login(): void {
    if (!is_logged_in()) {
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? 'register.php');
        header('Location: login.php?redirect=' . $redirect);
        exit;
    }
}

function log_in_user(): void {
    $_SESSION['auth_ok'] = true;
}

function log_out_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
