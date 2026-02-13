<?php
/**
 * Instance Creation Endpoint
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
session_start();

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Validate required fields
$directory = sanitizeInput($data['directory'] ?? '');
$webhook = sanitizeInput($data['webhook'] ?? '');

if (empty($directory) || empty($webhook)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory and webhook are required']);
    exit;
}

// Validate directory name
if (!preg_match('/^[a-zA-Z0-9_-]{3,32}$/', $directory)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Directory name must be 3-32 alphanumeric characters, hyphens, or underscores']);
    exit;
}

// Validate webhook
if (!validateWebhook($webhook)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid Discord webhook URL']);
    exit;
}

// Rate limiting
$clientIP = getClientIP();
if (!checkRateLimit($clientIP, 10, 3600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Rate limit exceeded. Please try again later.']);
    exit;
}

// Create instance
$result = createInstance($directory, $webhook);

if ($result['success']) {
    // Log creation
    securityLog('INSTANCE_CREATED', [
        'directory' => $directory,
        'token' => substr($result['token'], 0, 8) . '...'
    ]);
    
    // Send notification to webhook
    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $domain;
    
    $webhookData = [
        'username' => 'Bypasserv3',
        'avatar_url' => 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png',
        'embeds' => [
            [
                'title' => '🎉 Instance Created Successfully!',
                'description' => "Your Bypasserv3 instance has been created and is ready to use.",
                'color' => hexdec('00BFFF'),
                'fields' => [
                    [
                        'name' => '🔗 Instance URL',
                        'value' => "```\n{$baseUrl}/public/?dir={$directory}\n```",
                        'inline' => false
                    ],
                    [
                        'name' => '📊 Dashboard',
                        'value' => "```\n{$baseUrl}/dashboard/?token={$result['token']}\n```",
                        'inline' => false
                    ],
                    [
                        'name' => '🔑 Access Token',
                        'value' => "```\n{$result['token']}\n```",
                        'inline' => false
                    ],
                    [
                        'name' => '📁 Directory',
                        'value' => "`{$directory}`",
                        'inline' => true
                    ],
                    [
                        'name' => '⏰ Created',
                        'value' => date('Y-m-d H:i:s'),
                        'inline' => true
                    ]
                ],
                'footer' => [
                    'text' => 'Bypasserv3 | Keep your token safe!'
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
        'token' => $result['token'],
        'directory' => $directory,
        'instanceUrl' => $baseUrl . '/public/?dir=' . $directory,
        'dashboardUrl' => $baseUrl . '/dashboard/?token=' . $result['token']
    ]);
    
} else {
    http_response_code(400);
    echo json_encode($result);
}

?>
