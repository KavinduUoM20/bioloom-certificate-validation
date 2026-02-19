<?php
/**
 * Certificate PDF download by token - Bioloom Islands Pvt Ltd
 * Verifies token, outputs PDF (open in new tab / download).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if ($token === '') {
    http_response_code(400);
    exit('Invalid or missing certificate link.');
}

try {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM certificate_recipients WHERE token = ? AND completion_status = 1 LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch();
} catch (PDOException $e) {
    http_response_code(500);
    exit('Service temporarily unavailable.');
}

if (!$row) {
    http_response_code(404);
    exit('This certificate link is invalid or has expired.');
}

$recipientName = $row['first_name'] . ' ' . $row['last_name'];
require_once __DIR__ . '/includes/CertificatePdf.php';
$generator = new CertificatePdf();
$filename = 'Bioloom-Certificate-' . preg_replace('/[^a-zA-Z0-9\-]/', '-', $recipientName) . '.pdf';
$generator->outputPdf($recipientName, $filename);
