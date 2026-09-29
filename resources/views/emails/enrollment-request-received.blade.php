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
        .info-box { background: #eff6ff; border-left: 4px solid #667eea; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .detail-row { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-label { font-weight: 600; color: #6b7280; display: inline-block; width: 120px; }
        .timeline { margin: 20px 0; }
        .timeline-step { display: flex; align-items: flex-start; margin-bottom: 12px; }
        .timeline-dot { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; margin-right: 12px; }
        .timeline-dot.done { background: #10b981; color: white; }
        .timeline-dot.active { background: #667eea; color: white; }
        .timeline-dot.pending { background: #e5e7eb; color: #9ca3af; }
        .timeline-text { font-size: 14px; padding-top: 2px; }
        .timeline-text.active { font-weight: 600; color: #667eea; }
        .timeline-text.pending { color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Enrollment Request Received</h1>
        </div>
        <div class="content">
            <p>Dear {{ $enrollment->learner->name }},</p>
            
            <div class="info-box">
                <strong>We've received your enrollment request!</strong> Our team will review it shortly and get back to you.
            </div>

            <h3 style="color: #1f2937; margin-top: 25px;">Request Details:</h3>
            <div class="detail-row">
                <span class="detail-label">Course:</span>
                <span>{{ $enrollment->course->title }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Type:</span>
                <span>{{ $enrollment->course->course_type === 'theoretical' ? 'TDC (Theoretical)' : 'PDC (Practical)' }}</span>
            </div>
            @if($enrollment->package)
            <div class="detail-row">
                <span class="detail-label">Package:</span>
                <span>{{ $enrollment->package->name }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="detail-label">Submitted:</span>
                <span>{{ $enrollment->created_at->format('F d, Y h:i A') }}</span>
            </div>

            <h3 style="color: #1f2937; margin-top: 25px;">What's Next?</h3>
            <div class="timeline">
                <div class="timeline-step">
                    <div class="timeline-dot done"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                    <div class="timeline-text">Request submitted</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-dot active">●</div>
                    <div class="timeline-text active">Under review by our team</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-dot pending">○</div>
                    <div class="timeline-text pending">Approval & account activation</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-dot pending">○</div>
                    <div class="timeline-text pending">Start your learning journey!</div>
                </div>
            </div>

            <p style="margin-top: 20px;">You'll receive an email notification once your request has been reviewed. You can also check the status of your request by logging in to your account.</p>

            <center>
                <a href="{{ route('schools.login', ['school' => $school]) }}" class="button">Check Your Status</a>
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
