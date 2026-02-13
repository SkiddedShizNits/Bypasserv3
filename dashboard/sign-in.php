<?php
session_start();
$sack = $_GET['token'] ?? '';

if(isset($_SESSION['token'])) {
    header("Location: dashboard.php");
    exit();
}

$token = $_POST['token'] ?? '';
if($token) {
    $tokenFile = "apis/tokens/$token.txt";
    if(file_exists($tokenFile)) {
        $chk = file_get_contents($tokenFile);
        $ex = array_map('trim', explode("|", $chk));
        
        if(count($ex) >= 3) {
            $_SESSION['token'] = $ex[0];
            $_SESSION['web'] = $ex[2];
            $_SESSION['dir'] = $ex[1];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid token format";
        }
    } else {
        $error = "Token not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Bypasser Dashboard</title>
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
<body class="bg-[#02040a] text-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="glass-effect rounded-3xl p-8 shadow-2xl">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 glass-effect">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h1 class="text-3xl font-bold mb-2">Dashboard Access</h1>
                <p class="text-white/60">Enter your access token</p>
            </div>
            
            <?php if(isset($error)): ?>
            <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-3 mb-6 text-red-400 text-sm">
                ⚠️ <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="text-sm font-medium text-white/80 ml-1 block mb-2">Access Token</label>
                    <input 
                        type="text" 
                        name="token" 
                        value="<?php echo htmlspecialchars($sack); ?>"
                        placeholder="Enter your token..." 
                        class="w-full bg-white/5 border border-white/10 focus:border-white/20 text-white rounded-2xl px-4 py-3 outline-none transition-colors glass-effect"
                        required
                    >
                </div>
                
                <button type="submit" class="w-full h-14 bg-white text-black hover:bg-white/90 rounded-2xl text-base font-bold transition-all active:scale-[0.98] flex items-center justify-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>
