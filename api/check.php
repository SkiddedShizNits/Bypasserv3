<?php
/**
 * Bypasserv3 - Cookie Check Endpoint
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cookie = trim($input['cookie'] ?? '');

if (empty($cookie)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cookie required']);
    exit;
}

if (!validateCookie($cookie)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid cookie format']);
    exit;
}

// Check if cookie is valid
$headers = ["Cookie: .ROBLOSECURITY=$cookie", "User-Agent: Mozilla/5.0"];
$ch = curl_init("https://www.roblox.com/my/settings/json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['UserId'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'error' => 'Invalid or expired cookie'
    ]);
    exit;
}

// Get basic user info
$userId = $data['UserId'];
$username = $data['Name'] ?? 'Unknown';

echo json_encode([
    'success' => true,
    'valid' => true,
    'userId' => $userId,
    'username' => $username
]);
?> 
