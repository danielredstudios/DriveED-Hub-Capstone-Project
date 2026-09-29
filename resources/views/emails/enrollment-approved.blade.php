<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; margin: 15px 0; }
        .info-box { background: #f0fdf4; border-left: 4px solid #10b981; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .detail-row { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-label { font-weight: 600; color: #6b7280; display: inline-block; width: 120px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#ffffff" style="width:32px;height:32px;vertical-align:middle;margin-right:8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>Enrollment Approved!</h1>
        </div>
        <div class="content">
            <p>Dear {{ $enrollment->learner->name }},</p>
            
            <div class="info-box">
                <strong>Great news!</strong> Your enrollment request has been approved by {{ $school->name }}.
            </div>

            <h3 style="color: #1f2937; margin-top: 25px;">Enrollment Details:</h3>
            <div class="detail-row">
                <span class="detail-label">Course:</span>
                <span>{{ $enrollment->course->title }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Type:</span>
                <span>{{ $enrollment->course->course_type === 'theoretical' ? 'TDC (Theoretical)' : 'PDC (Practical)' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Duration:</span>
                <span>{{ $enrollment->course->hours_required }} hours</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Approved At:</span>
                <span>{{ $enrollment->approved_at->format('F d, Y h:i A') }}</span>
            </div>
            @if($enrollment->learner->hasVerifiedLicense())
            <div class="detail-row">
                <span class="detail-label">License:</span>
                <span style="color: #10b981; font-weight: 600;">Verified ✓</span>
            </div>
            @endif

            <p style="margin-top: 25px;">You can now start scheduling your sessions. Login to your account to view available time slots and book your lessons.</p>

            <center>
                <a href="{{ route('schools.login', ['school' => $school]) }}" class="button">Login to Your Account</a>
            </center>

            <p style="color: #6b7280; font-size: 14px; margin-top: 25px;">
                If you have any questions, please don't hesitate to contact us at {{ $school->schoolSetting->contact_email ?? 'the school office' }}.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.</p>
            <p style="margin: 5px 0;">This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
