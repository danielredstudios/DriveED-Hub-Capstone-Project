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
        .info-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .reason-box { background: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; margin: 15px 0; border-radius: 8px; }
        .detail-row { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-label { font-weight: 600; color: #6b7280; display: inline-block; width: 120px; }
        .next-steps { background: #eff6ff; border-left: 4px solid #667eea; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .next-steps ul { margin: 8px 0; padding-left: 20px; }
        .next-steps li { margin-bottom: 6px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Enrollment Request Update</h1>
        </div>
        <div class="content">
            <p>Dear {{ $enrollment->learner->name }},</p>
            
            @php
                $isPartial = $enrollment->status === 'pending';
                $licenseRejected = $enrollment->learner->student_license_status === 'rejected';
                $paymentRejected = $enrollment->payment_status === 'pending' && !$enrollment->payment_proof_path;
            @endphp

            <div class="info-box" style="{{ $isPartial ? 'background: #fffbeb; border-color: #f59e0b;' : '' }}">
                @if($isPartial)
                    <strong style="color: #92400e;">Action Required: Your enrollment needs revision.</strong>
                    <p style="margin: 5px 0 0 0; color: #78350f;">The following items were not accepted and need to be re-uploaded:</p>
                    <ul style="margin: 10px 0; color: #78350f; font-weight: 600;">
                        @if($licenseRejected) <li>Student Driver's License</li> @endif
                        @if($paymentRejected) <li>Payment Proof (GCash Receipt)</li> @endif
                    </ul>
                @else
                    <strong>Your enrollment request was not approved at this time.</strong> Please review the details below.
                @endif
            </div>

            <h3 style="color: #1f2937; margin-top: 25px;">Request Details:</h3>
            <div class="detail-row">
                <span class="detail-label">Course:</span>
                <span>{{ $enrollment->course->title }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span style="font-weight: 700; color: {{ $isPartial ? '#d97706' : '#ef4444' }}">{{ $isPartial ? 'REVISION REQUIRED' : 'REJECTED' }}</span>
            </div>

            @if($enrollment->remarks)
            <h3 style="color: #1f2937; margin-top: 25px;">Feedback from Administrator:</h3>
            <div class="reason-box">
                <p style="margin: 0; font-size: 14px; color: #4b5563;">{{ $enrollment->remarks }}</p>
            </div>
            @endif

            <div class="next-steps">
                <strong>How to proceed:</strong>
                <ul>
                    @if($isPartial)
                        <li>Log in to your dashboard and re-upload the rejected documents.</li>
                        <li>No need to submit a new enrollment form.</li>
                    @else
                        <li>Review the feedback above and address any issues.</li>
                        <li>You may submit a new enrollment request later.</li>
                    @endif
                    <li>Contact us if you need further clarification.</li>
                </ul>
            </div>

            @if($isPartial)
                <p style="margin-top: 20px; text-align: center;">Click the button below to fix your enrollment information:</p>
                <center>
                    <a href="{{ route('schools.guest.enrollment-requests', ['school' => $school]) }}" class="button" style="background: #f59e0b;">Update Enrollment</a>
                </center>
            @else
                <p style="margin-top: 20px;">Don't be discouraged! You can always submit a new enrollment request once you've addressed the feedback above.</p>
                <center>
                    <a href="{{ route('schools.guest.courses', ['school' => $school]) }}" class="button">Browse Courses</a>
                </center>
            @endif

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
