<?php
/**
 * Process registration and send certificate email if completed - Bioloom Islands Pvt Ltd
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$completion = isset($_POST['completion_status']) ? 1 : 0;

if ($first_name === '' || $last_name === '' || $email === '') {
    header('Location: register.php?error=missing');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php?error=email');
    exit;
}

try {
    $check = $pdo->prepare("SELECT id FROM certificate_recipients WHERE email = ? LIMIT 1");
    $check->execute([$email]);
    if ($check->fetch()) {
        header('Location: register.php?error=duplicate');
        exit;
    }
} catch (PDOException $e) {
    header('Location: register.php?error=db');
    exit;
}

$uuid = generate_uuid();
$token = null;
$cert_issued = 0;

if ($completion === 1) {
    $token = generate_cert_token();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO certificate_recipients (uuid, first_name, last_name, email, completion_status, cert_issued, token)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$uuid, $first_name, $last_name, $email, $completion, $cert_issued, $token]);
} catch (PDOException $e) {
    header('Location: register.php?error=db');
    exit;
}

$email_sent = 0;
if ($completion === 1 && $token !== null) {
    $cert_url = base_url() . '/view-certificate.php?token=' . urlencode($token);

    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $fromEmail = 'noreply@example.com';
            $fromName  = 'Bioloom Islands Pvt Ltd';
            $useSmtp   = false;
            if (is_file(__DIR__ . '/config/mail.php')) {
                $mailConfig = require __DIR__ . '/config/mail.php';
                $fromEmail  = $mailConfig['from_email'] ?? $fromEmail;
                $fromName   = $mailConfig['from_name'] ?? $fromName;
                $useSmtp    = !empty($mailConfig['enabled']) && !empty($mailConfig['host']);
                if ($useSmtp) {
                    $mail->isSMTP();
                    $mail->Host       = $mailConfig['host'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $mailConfig['username'];
                    $mail->Password   = $mailConfig['password'];
                    $mail->SMTPSecure = ($mailConfig['secure'] ?? 'tls') === 'ssl' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = (int) ($mailConfig['port'] ?? 587);
                }
            }
            if (!$useSmtp && getenv('SMTP_HOST')) {
                $mail->isSMTP();
                $mail->Host       = getenv('SMTP_HOST');
                $mail->SMTPAuth   = true;
                $mail->Username   = getenv('SMTP_USER');
                $mail->Password   = getenv('SMTP_PASS');
                $mail->SMTPSecure = getenv('SMTP_SECURE') ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = (int) (getenv('SMTP_PORT') ?: 587);
                $useSmtp = true;
                if (getenv('MAIL_FROM')) { $fromEmail = getenv('MAIL_FROM'); }
            }

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($email, $first_name . ' ' . $last_name);
            $mail->Subject = 'Your certificate – Bioloom Islands Pvt Ltd';

            $footerImgUrl = base_url() . '/template/signature-footer.png';
            $mail->Body = "
                <div style=\"font-family: sans-serif; max-width: 600px; margin: 0 auto;\">
                    <header style=\"padding: 16px 0; border-bottom: 1px solid #e5e7eb;\">
                        <strong style=\"font-size: 18px; color: #1a1a1a;\">" . h($fromName) . "</strong>
                    </header>
                    <div style=\"padding: 24px 0;\">
                        <p>Hello " . h($first_name) . ",</p>
                        <p>Your certificate is ready. Click the link below to view and download it.</p>
                        <p><a href=\"" . h($cert_url) . "\" style=\"color:#38bdf8;\">View my certificate</a></p>
                        <p>If the link does not work, copy and paste this URL into your browser:</p>
                        <p style=\"word-break:break-all;color:#94a3b8;\">" . h($cert_url) . "</p>
                        <p>This link is unique to you. Do not share it.</p>
                    </div>
                    <footer style=\"padding: 24px 0; border-top: 1px solid #e5e7eb;\">
                        <img src=\"" . h($footerImgUrl) . "\" alt=\"\" style=\"display: block; max-width: 100%; height: auto;\" width=\"400\">
                    </footer>
                </div>
            ";
            $mail->AltBody = "Hello {$first_name},\n\nYour certificate: {$cert_url}\n\n— Bioloom Islands Pvt Ltd";

            $mail->send();
            $email_sent = 1;
            $pdo->prepare("UPDATE certificate_recipients SET cert_issued = 1 WHERE token = ?")->execute([$token]);
        } catch (\Exception $e) {
            // Log $e->getMessage(); still redirect with saved=1
        }
    } else {
        // Fallback: PHP mail() if PHPMailer not installed
        $subject = 'Your certificate – Bioloom Islands Pvt Ltd';
        $body = "Hello {$first_name},\n\nView your certificate: {$cert_url}\n\n— Bioloom Islands Pvt Ltd";
        $headers = "From: noreply@example.com\r\nReply-To: noreply@example.com\r\nContent-Type: text/plain; charset=UTF-8";
        if (@mail($email, $subject, $body, $headers)) {
            $email_sent = 1;
            $pdo->prepare("UPDATE certificate_recipients SET cert_issued = 1 WHERE token = ?")->execute([$token]);
        }
    }
}

header('Location: register.php?saved=1&email_sent=' . $email_sent);
exit;
