<?php
/**
 * Bypasserv3 - Dashboard Sign In
 */

session_start();
require_once '../config.php';
require_once '../functions.php';

// If already logged in, redirect to dashboard
if (!empty($_SESSION['token'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Check for URL token
$urlToken = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');
    
    if (empty($token)) {
        $error = 'Please enter your access token';
    } else {
        // Verify and get token data
        $tokenData = verifyToken($token);
        
        if (!$tokenData) {
            $error = 'Invalid or expired access token';
        } else {
            // Store in session
            $_SESSION['token'] = $token;
            $_SESSION['directory'] = $tokenData['directory'];
            $_SESSION['webhook'] = $tokenData['webhook'];
            
            // Redirect to dashboard
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Dashboard</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #02040a 0%, #0a0e27 50%, #02040a 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
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
<body class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-2xl glass-effect mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Dashboard Sign In</h1>
            <p class="text-white/60">Enter your access token to continue</p>
        </div>

        <div class="glass-effect rounded-2xl p-8">
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-white/80 mb-2">Access Token</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white/40">
                            <i class="fas fa-key"></i>
                        </span>
                        <input 
                            type="text" 
                            name="token" 
                            value="<?php echo htmlspecialchars($urlToken); ?>"
                            placeholder="Enter your token..." 
                            class="w-full pl-12 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/20 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all"
                            required
                            autocomplete="off"
                        >
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition-all hover:scale-105">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In to Dashboard
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="/" class="text-sm text-white/60 hover:text-white transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
