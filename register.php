<?php
/**
 * Frontend: Register certificate recipients - Bioloom Islands Pvt Ltd
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

// Optional: load recipients for listing
$recipients = [];
try {
    $stmt = $pdo->query("SELECT id, uuid, first_name, last_name, email, completion_status, cert_issued, date_created 
                         FROM certificate_recipients ORDER BY date_created DESC LIMIT 50");
    $recipients = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table might not exist yet
}

$message = '';
$error   = '';
if (isset($_GET['saved'])) {
    $message = 'Recipient registered successfully.';
    if (isset($_GET['email_sent']) && $_GET['email_sent'] === '1') {
        $message .= ' Certificate link email has been sent.';
    }
}
if (isset($_GET['error'])) {
    $error = match($_GET['error']) {
        'missing'   => 'Please fill first name, last name and email.',
        'email'     => 'Invalid email address.',
        'duplicate' => 'A recipient with this email is already registered.',
        'db'        => 'Database error. Please try again.',
        default     => 'An error occurred.',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Registration – Bioloom Islands Pvt Ltd</title>
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --accent: #38bdf8; --text: #e2e8f0; --muted: #94a3b8; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; margin: 0; padding: 2rem; }
        .wrap { max-width: 720px; margin: 0 auto; }
        h1 { font-size: 1.5rem; font-weight: 600; margin-bottom: 0.25rem; }
        .sub { color: var(--muted); font-size: 0.9rem; margin-bottom: 1.5rem; }
        .card { background: var(--card); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 520px) { .form-row { grid-template-columns: 1fr; } }
        label { display: block; font-size: 0.85rem; color: var(--muted); margin-bottom: 0.35rem; }
        input[type="text"], input[type="email"] { width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #334155; border-radius: 8px; background: #0f172a; color: var(--text); font-size: 1rem; }
        input:focus { outline: none; border-color: var(--accent); }
        .checkbox-wrap { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; }
        .checkbox-wrap input { width: auto; }
        button { background: var(--accent); color: var(--bg); border: none; padding: 0.65rem 1.25rem; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; }
        button:hover { filter: brightness(1.1); }
        .msg { padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; }
        .msg.success { background: #064e3b; color: #6ee7b7; }
        .msg.error { background: #7f1d1d; color: #fca5a5; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { text-align: left; padding: 0.6rem 0.75rem; border-bottom: 1px solid #334155; }
        th { color: var(--muted); font-weight: 500; }
        .badge { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.75rem; }
        .badge.yes { background: #064e3b; color: #6ee7b7; }
        .badge.no { background: #334155; color: var(--muted); }
    </style>
</head>
<body>
    <div class="wrap">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h1>Bioloom Islands Pvt Ltd</h1>
                <p class="sub">Certificate &amp; badge registration</p>
            </div>
            <a href="logout.php" style="color: var(--muted); font-size: 0.9rem;">Log out</a>
        </div>

        <?php if ($message): ?>
            <div class="msg success"><?= h($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="msg error"><?= h($error) ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="post" action="process-register.php">
                <div class="form-row">
                    <div>
                        <label for="first_name">First name *</label>
                        <input type="text" id="first_name" name="first_name" required maxlength="100" value="">
                    </div>
                    <div>
                        <label for="last_name">Last name *</label>
                        <input type="text" id="last_name" name="last_name" required maxlength="100" value="">
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required maxlength="255" value="">
                </div>
                <div class="checkbox-wrap">
                    <input type="checkbox" id="completion_status" name="completion_status" value="1">
                    <label for="completion_status">Completed (if checked, certificate link will be emailed)</label>
                </div>
                <div style="margin-top: 1.25rem;">
                    <button type="submit">Register recipient</button>
                </div>
            </form>
        </div>

        <?php if (!empty($recipients)): ?>
        <div class="card">
            <h2 style="font-size: 1.1rem; margin-top: 0;">Recent recipients</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Completed</th>
                        <th>Cert sent</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recipients as $r): ?>
                    <tr>
                        <td><?= h($r['first_name'] . ' ' . $r['last_name']) ?></td>
                        <td><?= h($r['email']) ?></td>
                        <td><span class="badge <?= $r['completion_status'] ? 'yes' : 'no' ?>"><?= $r['completion_status'] ? 'Yes' : 'No' ?></span></td>
                        <td><span class="badge <?= $r['cert_issued'] ? 'yes' : 'no' ?>"><?= $r['cert_issued'] ? 'Yes' : 'No' ?></span></td>
                        <td><?= h(date('M j, Y', strtotime($r['date_created']))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
