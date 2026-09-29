<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 - Page Session Expired</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 40px 32px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .code {
            font-size: 72px;
            font-weight: 800;
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 12px;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #f1f5f9;
        }
        p {
            color: #94a3b8;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .btn-primary {
            background: #3b82f6;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #334155;
            color: #e2e8f0;
        }
        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">419</div>
        <h1>Page Session Expired</h1>
        <p>Your session timed out due to inactivity or a new login was detected in another tab. Please refresh to continue safely.</p>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="window.history.back()">Go Back &amp; Retry</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Refresh Page</button>
        </div>
    </div>
</body>
</html>

