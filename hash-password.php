<?php
/**
 * Generate password hash for config/auth.php
 * Run from command line: php hash-password.php "YourPassword"
 */
$password = $argv[1] ?? '';
if ($password === '') {
    echo "Usage: php hash-password.php \"YourPassword\"\n";
    exit(1);
}
echo password_hash($password, PASSWORD_DEFAULT) . "\n";
