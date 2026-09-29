<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Setup for {{ $school->name }}</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f7fa;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #2563eb;
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-text {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1e293b;
        }
        .credentials {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .credentials p {
            margin: 5px 0;
            font-size: 14px;
        }
        .credential-value {
            font-family: monospace;
            font-weight: bold;
            color: #2563eb;
            background: #eff6ff;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
            text-align: center;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            background-color: #f8fafc;
        }
        .highlight {
            color: #2563eb;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{ $school->name }}</h1>
        </div>
        <div class="content">
            <p class="welcome-text">Hello!</p>
            <p>An account has been created for you at <span class="highlight">{{ $school->name }}</span> as a <span class="highlight">{{ ucfirst(str_replace('_', ' ', $invitation->role)) }}</span>.</p>
            
            <p>To get started, please click the button below to complete your profile and activate your account.</p>

            @if($temporaryPassword)
            <div class="credentials">
                <p><strong>Temporary Email:</strong> <span class="credential-value">{{ $invitation->email }}</span></p>
                <p><strong>Temporary Password:</strong> <span class="credential-value">{{ $temporaryPassword }}</span></p>
                <p style="margin-top: 10px; font-size: 12px; color: #64748b;"><em>Note: You will be asked to change this password upon your first login.</em></p>
            </div>
            @endif

            <div style="text-align: center;">
                <a href="{{ route('schools.onboarding.show', ['school' => $school->slug, 'token' => $invitation->token]) }}" class="btn">Complete Your Setup</a>
            </div>

            <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
                This setup link will expire on {{ $invitation->expires_at->format('M d, Y H:i') }}.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.
        </div>
    </div>
</body>
</html>
