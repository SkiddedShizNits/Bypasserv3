<?php
session_start();

if (isset($_SESSION['token'])) {
    header('Location: index.php');
    exit;
}

require_once '../config.php';
require_once '../functions.php';

$error = '';
$tokenParam = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($tokenParam)) {
    $token = !empty($tokenParam) ? $tokenParam : ($_POST['token'] ?? '');
    
    if (empty($token)) {
        $error = 'Please enter a token';
    } else {
        $tokenData = getTokenData($token);
        
        if ($tokenData) {
            $_SESSION['token'] = $token;
            $_SESSION['directory'] = $tokenData['directory'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid token';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #3b82f6;
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--text-primary);
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
            background: rgba(26, 35, 50, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(100, 150, 255, 0.15);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin: 0 auto 20px;
        }
        h1 {
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 30px;
            font-size: 15px;
        }
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 18px;
        }
        input {
            width: 100%;
            padding: 16px 20px 16px 50px;
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
        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), #2563eb);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }
        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: var(--text-secondary);
        }
        .footer a {
            color: var(--primary);
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="logo">🔐</div>
        <h1>Dashboard Login</h1>
        <p class="subtitle">Enter your access token to continue</p>
        
        <?php if ($error): ?>
        <div class="error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <i class="fas fa-key input-icon"></i>
                <input type="text" name="token" placeholder="Enter your token" value="<?php echo htmlspecialchars($tokenParam); ?>" required autofocus>
            </div>
            
            <button type="submit">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>
        </form>
        
        <div class="footer">
            Don't have a site? <a href="../generator/">Create one here</a>
        </div>
    </div>
</body>
</html>