<?php
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
    } elseif (!verifyToken($token)) {
        $error = 'Invalid access token';
    } else {
        $_SESSION['token'] = $token;
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Dashboard</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
    <div class="w-full max-w-md space-y-8">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-white mb-2">Dashboard Sign In</h1>
            <p class="text-white/60">Enter your access token to continue</p>
        </div>

        <div class="glass-effect rounded-3xl p-8">
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-white/80 mb-2">Access Token</label>
                    <input 
                        type="text" 
                        name="token" 
                        value="<?php echo htmlspecialchars($urlToken); ?>"
                        placeholder="Enter your token..."
                        class="w-full bg-white/5 border border-white/10 focus:border-purple-500 text-white rounded-2xl p-4 outline-none transition-colors"
                        required
                    >
                </div>

                <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4 text-red-400 text-sm">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <button 
                    type="submit"
                    class="w-full bg-white text-black font-bold py-4 rounded-2xl hover:bg-white/90 transition-colors"
                >
                    Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="/" class="text-sm text-white/60 hover:text-white/80 transition-colors">
                    ← Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
