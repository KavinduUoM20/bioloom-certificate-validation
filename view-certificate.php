<?php
/**
 * Certificate landing page by token - Bioloom Islands Pvt Ltd
 * Web-rendered certificate content, Download certificate button, Add to LinkedIn Profile.
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

// Template image for web view (same as PDF); name position from config
$certConfig    = is_file(__DIR__ . '/config/certificate.php') ? require __DIR__ . '/config/certificate.php' : [];
$templateUrl   = base_url() . '/template/certificate-template.png';
$pageW         = (float) ($certConfig['page_width_mm'] ?? 210);
$pageH         = (float) ($certConfig['page_height_mm'] ?? 148);
$nameArea      = $certConfig['name_area'] ?? ['left_mm' => 27, 'top_mm' => 50, 'width_mm' => 131, 'height_mm' => 14];
$nameLeftPct   = round(100 * ($nameArea['left_mm'] ?? 27) / $pageW, 2);
$nameTopPct    = round(100 * ($nameArea['top_mm'] ?? 50) / $pageH, 2);
$nameWidthPct  = round(100 * ($nameArea['width_mm'] ?? 131) / $pageW, 2);
$nameHeightPct = round(100 * ($nameArea['height_mm'] ?? 14) / $pageH, 2);
$nameFontSize  = (int) ($certConfig['name_font_size'] ?? 18);

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
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
            margin-bottom: 2rem;
            position: relative;
            max-width: 100%;
            width: 100%;
            aspect-ratio: 210 / 148;
            border: 2px solid var(--border);
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            background: var(--bg-paper);
        }
        .certificate-image {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
        }
        .certificate-name-overlay {
            position: absolute;
            left: <?= $nameLeftPct ?>%;
            top: <?= $nameTopPct ?>%;
            width: <?= $nameWidthPct ?>%;
            height: <?= $nameHeightPct ?>%;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            font-family: 'Poppins', sans-serif;
            font-size: clamp(0.75rem, 2.2vw, <?= max(14, $nameFontSize) ?>px);
            font-weight: 600;
            color: var(--ink);
            text-align: left;
            pointer-events: none;
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
            font-family: 'Poppins', sans-serif;
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
            <img class="certificate-image" src="<?= h($templateUrl) ?>" alt="Certificate" width="210" height="148">
            <div class="certificate-name-overlay" aria-hidden="true"><?= h($recipientName) ?></div>
        </div>

        <div class="actions">
            <a href="<?= h($downloadUrl) ?>" target="_blank" rel="noopener" class="btn btn-pdf">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download certificate
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
