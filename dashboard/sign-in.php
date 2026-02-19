<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['token']) && isset($_SESSION['directory'])) {
    header("Location: dashboard.php");
    exit();
}

require_once '../config.php';
require_once '../functions.php';

$errorMessage = '';
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    $token = trim($token);
    
    // Look for token file using TOKENS_DIR constant
    $tokenFile = TOKENS_DIR . "/$token.txt";
    
    if (file_exists($tokenFile)) {
        $tokenData = file_get_contents($tokenFile);
        $parts = explode('|', $tokenData);
        
        if (count($parts) >= 2) {
            $directory = trim($parts[1]);
            
            // Verify instance exists
            $instanceData = getInstanceData($directory);
            
            if ($instanceData) {
                $_SESSION['token'] = $token;
                $_SESSION['directory'] = $directory;
                
                // Log successful login
                logSecurityEvent('dashboard_login', [
                    'directory' => $directory,
                    'ip' => getUserIP()
                ]);
                
                header("Location: dashboard.php");
                exit();
            } else {
                $errorMessage = 'Instance not found';
            }
        } else {
            $errorMessage = 'Invalid token format';
        }
    } else {
        $errorMessage = 'Invalid token';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Bypasserv3</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: 
                radial-gradient(at 80% 0%, rgba(139, 92, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .auth-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 40px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
        }

        .auth-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .auth-subtitle {
            color: var(--gray);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--gray);
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 16px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--light);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
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
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--error);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--gray);
        }

        .footer-text a {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 500;
        }

        .footer-text a:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your Bypasserv3 dashboard</p>
            </div>

            <?php if ($errorMessage): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($errorMessage); ?></span>
                </div>
            <?php endif; ?>

            <form method="post" id="loginForm">
                <div class="form-group">
                    <label class="form-label">Access Token</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input 
                            type="text" 
                            class="form-input" 
                            name="token" 
                            placeholder="Enter your access token"
                            value="<?php echo htmlspecialchars($token); ?>"
                            required
                            autocomplete="off"
                        >
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Sign In</span>
                </button>
            </form>

            <div class="footer-text">
                Don't have an instance? <a href="../generator/">Create one</a>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Signing in...</span>';
        });

        // Auto-focus input
        document.querySelector('.form-input').focus();
    </script>
</body>
</html>
