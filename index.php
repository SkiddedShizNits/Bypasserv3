<?php
require_once 'config.php';
require_once 'functions.php';

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');

if (empty($path) || $path === 'index.php') {
    header('Location: /generator/');
    exit;
}

if (preg_match('/^(generator|public|api|dashboard)/', $path)) {
    return false;
}

$parts = explode('/', $path);
$instance = sanitizeDirectory($parts[0]);

if (empty($instance)) {
    header('Location: /generator/');
    exit;
}

$instanceData = getInstanceData($instance);

if (!$instanceData) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Not Found</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, sans-serif;
                background: #0a0f1e;
                color: white;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
            }
            h1 { font-size: 120px; margin-bottom: 20px; }
            p { font-size: 24px; color: #94a3b8; }
        </style>
    </head>
    <body>
        <div>
            <h1>404</h1>
            <p>Instance not found</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$instanceData['stats']['totalVisits']++;
updateDailyStats($instance, 'visits', 1);
saveInstanceData($instance, $instanceData);

logActivity("Visit to instance: {$instance} from IP: " . getClientIP());

$WEBHOOK_URL = $instanceData['webhook'];
$USER_WEBHOOK = $instanceData['userWebhook'];
$INSTANCE_NAME = $instanceData['directory'];

$templateFile = TEMPLATE_PATH . 'index.php';

if (!file_exists($templateFile)) {
    die('Template not found. Please add template/index.php');
}

include $templateFile;
?>