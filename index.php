<?php
/**
 * Bypasserv3 - Homepage
 * Modern Dark Grey/Black Design
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$stats = getGlobalStats();
$leaderboard = getLeaderboard(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bypasserv3 - Roblox Cookie Refresher</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #0f1419 100%);
            color: #f3f4f6;
            min-height: 100vh;
        }
        
        .navbar {
            background: #1f2937;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.2);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
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
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Space Grotesk', sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 20px;
        }
        
        .hero {
            text-align: center;
            margin-bottom: 80px;
        }
        
        .hero-title {
            font-size: 64px;
            font-weight: 700;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Space Grotesk', sans-serif;
        }
        
        .hero-subtitle {
            font-size: 20px;
            color: #9ca3af;
            margin-bottom: 32px;
        }
        
        .cta-button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 80px;
            background: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 16px;
            border: 1px solid rgba(59, 130, 246, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 8px;
            font-family: 'Space Grotesk', sans-serif;
        }
        
        .stat-label {
            font-size: 14px;
            color: #9ca3af;
            font-weight: 500;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 80px;
        }
        
        .feature-card {
            background: rgba(31, 41, 55, 0.6);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 32px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(59, 130, 246, 0.2);
            border-color: rgba(59, 130, 246, 0.3);
            background: rgba(31, 41, 55, 0.8);
        }
        
        .feature-icon {
            font-size: 40px;
            margin-bottom: 16px;
        }
        
        .feature-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #f3f4f6;
        }
        
        .feature-desc {
            font-size: 14px;
            color: #9ca3af;
            line-height: 1.6;
        }
        
        .leaderboard-section {
            background: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(59, 130, 246, 0.1);
            margin-bottom: 80px;
        }
        
        .section-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 32px;
            color: #f3f4f6;
            text-align: center;
        }
        
        .leaderboard-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.2s ease;
        }
        
        .leaderboard-item:hover {
            background: rgba(59, 130, 246, 0.05);
            border-radius: 8px;
            margin: 0 -16px;
            padding: 16px;
        }
        
        .leaderboard-item:last-child {
            border-bottom: none;
        }
        
        .rank-badge {
            font-weight: 700;
            color: #3b82f6;
            min-width: 40px;
            font-size: 18px;
        }
        
        .rank-name {
            color: #f3f4f6;
            font-weight: 600;
            font-size: 16px;
        }
        
        .rank-cookies {
            color: #10b981;
            font-weight: 600;
            font-size: 16px;
        }
        
        .footer {
            text-align: center;
            padding: 40px 20px;
            border-top: 1px solid rgba(59, 130, 246, 0.1);
            color: #6b7280;
            font-size: 14px;
        }
        
        .empty-leaderboard {
            text-align: center;
            color: #6b7280;
            padding: 40px 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">Bypasserv3</div>
            <a href="/generator/" class="cta-button">Create Site</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Hero -->
        <div class="hero">
            <h1 class="hero-title">Roblox Cookie Refresher</h1>
            <p class="hero-subtitle">Fast • Secure • Efficient</p>
            <a href="/generator/" class="cta-button">⚡ Create Site Now</a>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['totalInstances'] ?? 0 ?></div>
                <div class="stat-label">Sites Created</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['totalCookies'] ?? 0 ?></div>
                <div class="stat-label">Cookies Processed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['totalVisits'] ?? 0 ?></div>
                <div class="stat-label">Total Visits</div>
            </div>
        </div>

        <!-- Features -->
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Lightning Fast</div>
                <div class="feature-desc">Generate your site in seconds</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <div class="feature-title">Secure & Safe</div>
                <div class="feature-desc">All cookies handled securely</div>
            </div>


        <!-- Leaderboard -->
        <?php if (!empty($leaderboard)): ?>
        <div class="leaderboard-section">
            <h2 class="section-title">🏆 Top Sites</h2>
            <?php foreach ($leaderboard as $index => $site): ?>
            <div class="leaderboard-item">
                <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                    <span class="rank-badge">#<?= $index + 1 ?></span>
                    <span class="rank-name"><?= htmlspecialchars($site['directory']) ?></span>
                </div>
                <span class="rank-cookies"><?= number_format($site['totalCookies']) ?> cookies</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="leaderboard-section">
            <h2 class="section-title">🏆 Top Sites</h2>
            <div class="empty-leaderboard">No sites created yet. Be the first!</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Bypasserv3 © 2024 | Powered by Railway</p>
    </div>
</body>
</html>
?>
