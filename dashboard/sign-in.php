<?php
session_start();

if(isset($_SESSION['token'])) {
    header("Location: dashboard.php");
    exit();
}

$token = $_POST['token'] ?? $_GET['token'] ?? '';
$error = '';

if($token) {
    $token = str_replace('"', '', trim($token));
    $tokenFile = "../tokens/$token.txt";
    
    if(file_exists($tokenFile)) {
        $contents = file_get_contents($tokenFile);
        $data = array_map('trim', explode("|", $contents));
        
        if(count($data) >= 3) {
            $_SESSION['token'] = $data[0];
            $_SESSION['directory'] = $data[1];
            $_SESSION['webhook'] = $data[2];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Invalid token format!';
        }
    } else {
        $error = 'Token not found!';
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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --dark: #0f172a;
            --darker: #020617;
            --light: #f8fafc;
            --gray: #94a3b8;
            --glass: rgba(30, 41, 59, 0.45);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: var(--darker);
            color: var(--light);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: 
                radial-gradient(at 80% 0%, rgba(139, 92, 246, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(139, 92, 246, 0.1) 0px, transparent 50%);
        }

        .auth-card {
            width: 380px;
            background: var(--glass);
            border-radius: 14px;
            padding: 40px;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.25);
        }

        .auth-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            text-align: center;
        }

        .auth-subtitle {
            color: var(--gray);
            margin-bottom: 30px;
            text-align: center;
            font-size: 15px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 16px;
        }

        .form-input {
            width: 100%;
            padding: 14px 20px 14px 45px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            color: var(--light);
            font-size: 15px;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .form-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .auth-btn {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .auth-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
            color: var(--gray);
            font-size: 14px;
        }

        .back-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <h1 class="auth-title">Sign In</h1>
        <p class="auth-subtitle">Access your bypass dashboard</p>
        
        <form method="post">
            <div class="input-group">
                <i class="fas fa-key input-icon"></i>
                <input type="text" class="form-input" placeholder="Enter Token" name="token" value="<?php echo htmlspecialchars($token); ?>" autocomplete="off" required>
            </div>
            <button type="submit" class="auth-btn">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>
        </form>

        <div class="back-link">
            Don't have a token? <a href="/generator/">Create Instance</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if($error): ?>
    <script>
        Swal.fire({
            title: 'Error',
            text: '<?php echo $error; ?>',
            icon: 'error',
            background: '#0f172a',
            color: '#f8fafc',
            confirmButtonColor: '#8b5cf6'
        });
    </script>
    <?php endif; ?>
</body>
</html>
