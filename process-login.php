<?php
/**
 * Process login – Bioloom Certificate System
 */
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$redirect = trim($_POST['redirect'] ?? 'register.php');
if ($redirect === '' || strpos($redirect, '//') !== false) {
    $redirect = 'register.php';
}

$valid = false;
$configPath = __DIR__ . '/config/auth.php';
if ($username !== '' && $password !== '' && is_file($configPath)) {
    $auth = require $configPath;
    $expectedUser = $auth['username'] ?? '';
    $expectedHash = $auth['password_hash'] ?? '';
    if ($expectedUser !== '' && $expectedHash !== '' && $username === $expectedUser && password_verify($password, $expectedHash)) {
        $valid = true;
    }
}

if ($valid) {
    log_in_user();
    header('Location: ' . $redirect);
    exit;
}

header('Location: login.php?error=1&redirect=' . urlencode($redirect));
exit;
