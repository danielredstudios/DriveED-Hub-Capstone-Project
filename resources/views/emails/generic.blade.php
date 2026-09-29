<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { padding: 30px; }
        .footer { padding: 20px; text-align: center; font-size: 0.8rem; color: #888; border-top: 1px solid #eee; }
        .button { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Driving School Update</h1>
        </div>
        <div class="content">
            <h2>{{ $subject }}</h2>
            <p>{{ $messageText }}</p>
            
            @if($actionUrl && $actionText)
                <div style="text-align: center;">
                    <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
                </div>
            @endif
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Rayne Driving School. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
