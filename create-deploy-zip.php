<?php
/**
 * Create a deployment zip of the project – Bioloom Islands Pvt Ltd
 * Run from project root: php create-deploy-zip.php
 * Output: bioloom-cert-verification.zip (ready to upload to server)
 */
$root = __DIR__;
$outZip = $root . DIRECTORY_SEPARATOR . 'bioloom-cert-verification.zip';

$excludeDirs = ['.git', '.cursor', 'agent-transcripts', 'terminals', '.vscode', 'node_modules'];
$excludeFiles = ['.env', '.env.local', 'create-deploy-zip.php', 'bioloom-cert-verification.zip'];
$excludeExtensions = ['log'];

$zip = new ZipArchive();
if ($zip->open($outZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Cannot create zip: $outZip\n");
}

$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS),
    RecursiveIteratorIterator::SELF_FIRST
);

$added = 0;
foreach ($iter as $path) {
    $relative = substr($path->getPathname(), strlen($root) + 1);
    if ($relative === '') continue;

    $parts = explode(DIRECTORY_SEPARATOR, $relative);
    $skip = false;
    foreach ($parts as $p) {
        if (in_array($p, $excludeDirs, true)) { $skip = true; break; }
    }
    if ($skip) continue;

    $basename = $path->getFilename();
    if (in_array($basename, $excludeFiles, true)) continue;
    if (in_array(strtolower(pathinfo($basename, PATHINFO_EXTENSION)), $excludeExtensions, true)) continue;

    $localPath = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    if ($path->isDir()) {
        $zip->addEmptyDir($localPath . '/');
    } else {
        $zip->addFile($path->getPathname(), $localPath);
        $added++;
    }
}

$zip->close();
echo "Created: $outZip\n";
echo "Files added: $added\n";
echo "\nNext: upload this zip to your server, unzip, then follow DEPLOY.md.\n";
