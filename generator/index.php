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
            transition: color 0.2s*

