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
        .info-box { background: #fefce8; border-left: 4px solid #eab308; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .session-card { background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin: 20px 0; }
        .session-detail { display: flex; align-items: flex-start; margin-bottom: 12px; }
        .session-icon { font-size: 20px; margin-right: 12px; flex-shrink: 0; }
        .session-label { font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
        .session-value { font-size: 15px; color: #1f2937; font-weight: 500; }
        .checklist { margin: 20px 0; padding: 0; }
        .checklist li { list-style: none; padding: 6px 0; font-size: 14px; }
        .checklist li::before { content: "☐ "; color: #667eea; font-size: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Session Reminder</h1>
            <p style="margin: 8px 0 0; opacity: 0.9; font-size: 16px;">You have an upcoming driving session!</p>
        </div>
        <div class="content">
            <p>Dear {{ $booking->student->name }},</p>
            
            <div class="info-box">
                <strong>Reminder:</strong> You have a driving session scheduled for 
                <strong>{{ $booking->scheduled_at->format('l, F d, Y') }}</strong>.
            </div>

            <div class="session-card">
                <div class="session-detail">
                    <span class="session-icon"></span>
                    <div>
                        <div class="session-label">Date</div>
                        <div class="session-value">{{ $booking->scheduled_at->format('l, F d, Y') }}</div>
                    </div>
                </div>
                <div class="session-detail">
                    <span class="session-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#667eea" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                    <div>
                        <div class="session-label">Time</div>
                        <div class="session-value">
                            @if($booking->timeSlot)
                                {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('g:i A') }}
                            @else
                                {{ $booking->scheduled_at->format('g:i A') }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="session-detail">
                    <span class="session-icon">&#9733;</span>
                    <div>
                        <div class="session-label">Course</div>
                        <div class="session-value">{{ $booking->course->title ?? 'Driving Session' }}</div>
                    </div>
                </div>
                @if($booking->instructor)
                <div class="session-detail">
                    <span class="session-icon"></span>
                    <div>
                        <div class="session-label">Instructor</div>
                        <div class="session-value">{{ $booking->instructor->name }}</div>
                    </div>
                </div>
                @endif
            </div>

            <h3 style="color: #1f2937; margin-top: 25px;">Preparation Checklist:</h3>
            <ul class="checklist">
                <li>Bring your valid driver's license / student permit</li>
                <li>Arrive 10 minutes before your scheduled time</li>
                <li>Wear comfortable clothing and closed-toe shoes</li>
                <li>Bring a notebook for any notes during the session</li>
            </ul>

            <center>
                <a href="{{ route('schools.student.schedule', ['school' => $school]) }}" class="button">View My Schedule</a>
            </center>

            <p style="color: #6b7280; font-size: 14px; margin-top: 25px;">
                Need to reschedule? Please contact us at {{ $school->schoolSetting->contact_email ?? 'the school office' }} as soon as possible.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.</p>
            <p style="margin: 5px 0;">This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
