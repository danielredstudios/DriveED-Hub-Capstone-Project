<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0f766e; color: #fff; padding: 24px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { border: 1px solid #e5e7eb; border-top: none; padding: 24px; }
        .message-box { background: #f0fdfa; border-left: 4px solid #0f766e; padding: 14px; margin: 16px 0; }
        .button { display: inline-block; padding: 12px 22px; background: #0f766e; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; }
        .footer { background: #f9fafb; color: #6b7280; font-size: 12px; padding: 16px; text-align: center; border-radius: 0 0 8px 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">{{ $title }}</h1>
        </div>

        <div class="content">
            <p>Hello {{ $recipientName }},</p>

            <div class="message-box">
                {{ $message }}
            </div>

            @if($actionUrl && $actionLabel)
                <p style="margin: 24px 0; text-align: center;">
                    <a href="{{ $actionUrl }}" class="button">{{ $actionLabel }}</a>
                </p>
            @endif

            <p>If you have questions, please contact {{ $school->name }} support.</p>
        </div>

        <div class="footer">
            <p style="margin: 4px 0;">&copy; {{ date('Y') }} {{ $school->name }}</p>
            <p style="margin: 4px 0;">Automated message, please do not reply.</p>
        </div>
    </div>
</body>
</html>
