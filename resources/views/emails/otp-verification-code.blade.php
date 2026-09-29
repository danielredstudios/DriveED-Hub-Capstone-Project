<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1d4ed8; color: #fff; padding: 24px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { border: 1px solid #e5e7eb; border-top: none; padding: 24px; }
        .otp { font-size: 32px; letter-spacing: 8px; font-weight: bold; color: #1d4ed8; text-align: center; margin: 20px 0; }
        .footer { background: #f9fafb; color: #6b7280; font-size: 12px; padding: 16px; text-align: center; border-radius: 0 0 8px 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Email Verification</h1>
        </div>

        <div class="content">
            <p>Hello {{ $student->name }},</p>

            @if($isResend)
                <p>You requested a new verification code for your {{ $school->name }} account.</p>
            @else
                <p>Please verify your email to continue using your {{ $school->name }} account.</p>
            @endif

            <div class="otp">{{ $otpCode }}</div>

            <p>This code expires in 15 minutes.</p>
            <p>If you did not request this code, you can safely ignore this message.</p>
        </div>

        <div class="footer">
            <p style="margin: 4px 0;">&copy; {{ date('Y') }} {{ $school->name }}</p>
            <p style="margin: 4px 0;">Automated message, please do not reply.</p>
        </div>
    </div>
</body>
</html>
