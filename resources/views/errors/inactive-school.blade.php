<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Portal Unavailable - {{ $school->name ?? 'Driving School' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --bg: #f3f4f6;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
            text-align: center;
            background: white;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.4);
        }

        .icon-wrapper svg {
            width: 40px;
            height: 40px;
        }

        h1 {
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #111827;
        }

        .school-name {
            color: var(--primary);
            display: block;
            margin-bottom: 20px;
            font-weight: 600;
        }

        p {
            font-size: 1.1rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 35px;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.4);
        }

        .footer {
            margin-top: 30px;
            font-size: 0.875rem;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h1>Portal Unavailable</h1>
        <span class="school-name">{{ $school->name ?? 'Driving School' }}</span>
        <p>This school portal is currently inactive. If you believe this is an error, please contact the school administration or system support.</p>
        <a href="{{ url('/') }}" class="btn">Return to Home</a>
        <div class="footer">
            &copy; {{ date('Y') }} Driving School Management System
        </div>
    </div>
</body>
</html>
