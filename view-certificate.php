<?php
/**
 * Certificate landing page by token - Bioloom Islands Pvt Ltd
 * Web-rendered certificate content, View PDF button, Add to LinkedIn Profile.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if ($token === '') {
    http_response_code(400);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invalid link</title></head><body>';
    echo '<p>Invalid or missing certificate link. Please use the link from your email.</p>';
    echo '</body></html>';
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, date_created FROM certificate_recipients WHERE token = ? AND completion_status = 1 LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(500);
    exit('Service temporarily unavailable.');
}

if (!$row) {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Certificate not found</title></head><body>';
    echo '<p>This certificate link is invalid or has expired. Please contact Bioloom Islands Pvt Ltd.</p>';
    echo '</body></html>';
    exit;
}

$recipientName = $row['first_name'] . ' ' . $row['last_name'];
$dateCreated   = $row['date_created'];
$certDateText  = format_certificate_date($dateCreated);
$issueYear     = (int) date('Y', strtotime($dateCreated));
$issueMonth    = (int) date('n', strtotime($dateCreated));

$certTitle     = 'Certificate of Participation';
$eventName     = 'CRIPR WEBINAR';
$orgName       = 'BioLoom Islands';
$orgPlace      = 'Sri Lanka';

// LinkedIn "Add to Profile" – title and issuer shown on LinkedIn only (change these two here)
$linkedInCertTitle = 'CRIPR Webinar Completion';           // e.g. "CRIPR Webinar Completion", "AI Foundations"
$linkedInOrgName   = 'Bioloom Islands Pvt Ltd';             // issuer organization name on LinkedIn

$pageUrl       = base_url() . '/view-certificate.php?token=' . urlencode($token);
$badgeImageUrl = base_url() . '/assets/badge.png';
$downloadUrl   = base_url() . '/download-certificate.php?token=' . urlencode($token);

$linkedInUrl = 'https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME'
    . '&name=' . urlencode($linkedInCertTitle)
    . '&organizationName=' . urlencode($linkedInOrgName)
    . '&issueYear=' . $issueYear
    . '&issueMonth=' . $issueMonth
    . '&certUrl=' . urlencode($pageUrl);

$ogTitle       = h($recipientName . ' earned the ' . $certTitle . '!');
$ogDescription = 'Awarded by ' . $orgName . ', ' . $orgPlace;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ogTitle ?></title>
    <meta property="og:title" content="<?= $ogTitle ?>">
    <meta property="og:description" content="<?= h($ogDescription) ?>">
    <meta property="og:image" content="<?= h($badgeImageUrl) ?>">
    <meta property="og:url" content="<?= h($pageUrl) ?>">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1a1a1a;
            --ink-muted: #4a4a4a;
            --border: #d4d4d4;
            --border-accent: #b8860b;
            --bg-paper: #fafaf9;
            --white: #ffffff;
            --btn-pdf: #1a1a1a;
            --btn-linkedin: #0a66c2;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--white);
            color: var(--ink);
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 1rem;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        .page {
            max-width: 42rem;
            margin: 0 auto;
            padding: clamp(1.25rem, 4vw, 2.5rem);
        }
        .certificate {
            background: var(--white);
            border: 2px solid var(--border);
            border-radius: 2px;
            padding: clamp(2rem, 6vw, 3.5rem);
            margin-bottom: 2rem;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .certificate::before {
            content: '';
            position: absolute;
            inset: 12px;
            border: 1px solid var(--border);
            border-radius: 1px;
            pointer-events: none;
        }
        .certificate-heading {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(1.5rem, 4vw, 1.875rem);
            font-weight: 600;
            text-align: center;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--ink);
            margin: 0 0 1.75rem;
            line-height: 1.3;
        }
        .certificate-body {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(1.125rem, 2.5vw, 1.35rem);
            font-weight: 400;
            text-align: center;
            color: var(--ink);
            margin: 0;
            line-height: 1.7;
        }
        .certificate-body .name {
            font-weight: 600;
            font-style: italic;
            display: block;
            margin: 0.5rem 0 0.75rem;
            font-size: 1.15em;
        }
        .certificate-body .meta {
            margin-top: 1.5rem;
            font-size: 0.95em;
            color: var(--ink-muted);
        }
        .certificate-org {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--ink);
        }
        .actions {
            text-align: center;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-family: 'Outfit', sans-serif;
            font-size: 0.9375rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        }
        .btn-pdf {
            background: var(--btn-pdf);
            color: var(--white);
        }
        .btn-pdf:hover {
            background: #333;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-pdf svg {
            width: 1.125rem;
            height: 1.125rem;
            flex-shrink: 0;
        }
        .share-section {
            border-top: 1px solid var(--border);
            padding-top: 1.75rem;
            text-align: center;
        }
        .share-section .label {
            font-size: 0.875rem;
            color: var(--ink-muted);
            margin: 0 0 0.75rem;
        }
        .btn-linkedin {
            background: var(--btn-linkedin);
            color: var(--white);
        }
        .btn-linkedin:hover {
            background: #004182;
            box-shadow: 0 4px 12px rgba(10, 102, 194, 0.3);
        }
        .share-section .hint {
            font-size: 0.8125rem;
            color: var(--ink-muted);
            margin: 0.75rem 0 0;
            max-width: 28rem;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="certificate">
            <h1 class="certificate-heading"><?= h($certTitle) ?></h1>
            <p class="certificate-body">
                This certificate is proudly presented to
                <span class="name"><?= h($recipientName) ?></span>
                for attending <strong><?= h($eventName) ?></strong> organized by <span class="certificate-org"><?= h($orgName) ?></span>, <?= h($orgPlace) ?> on <?= h($certDateText) ?>.
            </p>
            <p class="certificate-body meta"><?= h($orgName) ?> — <?= h($orgPlace) ?></p>
        </div>

        <div class="actions">
            <a href="<?= h($downloadUrl) ?>" target="_blank" rel="noopener" class="btn btn-pdf">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                View PDF
            </a>
        </div>

        <div class="share-section">
            <p class="label">Share this achievement</p>
            <a href="<?= h($linkedInUrl) ?>" target="_blank" rel="noopener" class="btn btn-linkedin">Add to LinkedIn Profile</a>
            <p class="hint">Opens LinkedIn’s Licenses & Certifications form with this certificate pre-filled. Click Save to add it to your profile.</p>
        </div>
    </div>
</body>
</html>
