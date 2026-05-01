<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended | NACOC Logistics</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=JetBrains+Mono:wght@100..800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --danger: #ef4444;
            --bg: #f8fafc;
            --text-heading: #0f172a;
            --text-main: #475569;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(79, 70, 229, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(239, 68, 68, 0.05) 0%, transparent 50%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            overflow: hidden;
        }

        .suspended-card {
            background: white;
            padding: 4rem;
            border-radius: 40px;
            box-shadow: 0 40px 100px rgba(15, 23, 42, 0.08);
            max-width: 550px;
            width: 90%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .icon-circle {
            width: 100px;
            height: 100px;
            background: #fef2f2;
            color: var(--danger);
            border-radius: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2.5rem auto;
            position: relative;
            box-shadow: 0 20px 40px rgba(239, 68, 68, 0.15);
        }

        .icon-circle::after {
            content: '';
            position: absolute;
            inset: -10px;
            border: 2px dashed rgba(239, 68, 68, 0.2);
            border-radius: 42px;
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        h1 {
            color: var(--text-heading);
            font-size: 2.25rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            margin-bottom: 1rem;
        }

        p {
            font-size: 1.15rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
            font-weight: 500;
        }

        .btn-return {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: var(--text-heading);
            color: white;
            padding: 1.25rem 2.5rem;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 800;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);
        }

        .btn-return:hover {
            transform: translateY(-4px);
            background: var(--primary);
            box-shadow: 0 15px 40px rgba(79, 70, 229, 0.3);
        }

        .system-footer {
            margin-top: 3rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="suspended-card">
        <div class="icon-circle">
            <i data-lucide="shield-alert" style="width: 48px; height: 48px;"></i>
        </div>
        <h1>Access Restricted</h1>
        <p>Your account has been deactivated. Please contact your Administrator to resolve this issue and restore your operational credentials.</p>
        
        <a href="{{ route('login') }}" class="btn-return">
            <i data-lucide="arrow-left" style="width: 20px;"></i>
            Return to Terminal
        </a>

        <div class="system-footer">
            NACOC Logistics Oversight System &bull; Security Protocol 403-D
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
