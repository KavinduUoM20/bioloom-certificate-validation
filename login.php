<?php
/**
 * Admin login – Bioloom Certificate System
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: register.php');
    exit;
}

$error = isset($_GET['error']) ? 'Invalid username or password.' : '';
$redirect = $_GET['redirect'] ?? 'register.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Certificate Admin</title>
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --accent: #38bdf8; --text: #e2e8f0; --muted: #94a3b8; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; margin: 0; padding: 2rem; display: flex; align-items: center; justify-content: center; }
        .card { background: var(--card); border-radius: 12px; padding: 2rem; width: 100%; max-width: 360px; }
        h1 { font-size: 1.25rem; margin: 0 0 1.5rem; font-weight: 600; }
        label { display: block; font-size: 0.875rem; color: var(--muted); margin-bottom: 0.35rem; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #334155; border-radius: 8px; background: #0f172a; color: var(--text); font-size: 1rem; margin-bottom: 1rem; }
        input:focus { outline: none; border-color: var(--accent); }
        button { width: 100%; padding: 0.65rem; border-radius: 8px; font-size: 1rem; font-weight: 600; background: var(--accent); color: #0f172a; border: none; cursor: pointer; }
        button:hover { filter: brightness(1.1); }
        .msg { padding: 0.6rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
        .msg.error { background: #7f1d1d; color: #fca5a5; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Certificate Admin</h1>
        <?php if ($error): ?><div class="msg error"><?= h($error) ?></div><?php endif; ?>
        <form method="post" action="process-login.php">
            <input type="hidden" name="redirect" value="<?= h($redirect) ?>">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autocomplete="username">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <button type="submit">Log in</button>
        </form>
    </div>
</body>
</html>
