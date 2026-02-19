<?php
/**
 * SMTP / mail configuration – Bioloom Islands Pvt Ltd
 *
 * 1. Copy this file to mail.php:  copy mail.example.php mail.php
 * 2. Edit mail.php with your SMTP details below.
 * 3. Keep mail.php out of version control (it is in .gitignore).
 */

return [
    'enabled'   => true,              // set false to use PHP mail() instead of SMTP
    'host'      => 'smtp.example.com',
    'port'      => 587,
    'secure'    => 'tls',             // 'tls' or 'ssl'
    'username'  => 'your-smtp-username',
    'password'  => 'your-smtp-password',
    'from_email'=> 'noreply@yourdomain.com',
    'from_name' => 'Bioloom Islands Pvt Ltd',
];
