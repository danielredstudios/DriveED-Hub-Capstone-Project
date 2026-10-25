<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver's License Expiration Notice</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f3f4f6;
            margin: 0;
            padding: 24px 12px;
        }
        .container {
            max-width: 620px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
        }
        .header {
            background: {{ $isExpired ? '#b91c1c' : '#2563eb' }};
            color: #ffffff;
            padding: 32px 24px 24px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 6px 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .header p {
            margin: 0;
            font-size: 0.92rem;
            opacity: 0.92;
        }
        .content {
            padding: 28px 28px 36px 28px;
        }
        .status-card {
            background-color: {{ $isExpired ? '#fef2f2' : '#fffbeb' }};
            border-left: 5px solid {{ $isExpired ? '#ef4444' : '#f59e0b' }};
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .status-card-title {
            margin: 0 0 4px 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: {{ $isExpired ? '#991b1b' : '#92400e' }};
        }
        .status-card-desc {
            margin: 0;
            font-size: 0.9rem;
            color: {{ $isExpired ? '#7f1d1d' : '#78350f' }};
            line-height: 1.45;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background: #f9fafb;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .details-table td {
            padding: 10px 16px;
            font-size: 0.88rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .details-label {
            font-weight: 600;
            color: #4b5563;
            width: 40%;
        }
        .details-value {
            color: #111827;
            font-weight: 500;
        }
        .guide-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .guide-title {
            margin: 0 0 12px 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .guide-steps {
            margin: 0;
            padding-left: 20px;
            font-size: 0.88rem;
            color: #334155;
            line-height: 1.6;
        }
        .guide-steps li {
            margin-bottom: 8px;
        }
        .driveed-perk-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 24px;
        }
        .driveed-perk-title {
            margin: 0 0 6px 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e40af;
        }
        .driveed-perk-text {
            margin: 0;
            font-size: 0.86rem;
            color: #1e3a8a;
            line-height: 1.5;
        }
        .btn-container {
            text-align: center;
            margin-top: 24px;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-primary {
            display: inline-block;
            background: #2563eb;
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-secondary {
            display: inline-block;
            background: #ffffff;
            color: #374151 !important;
            border: 1px solid #d1d5db;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .footer {
            background: #f9fafb;
            padding: 20px 24px;
            text-align: center;
            font-size: 0.8rem;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $school->name ?? 'DriveED Hub' }}</h1>
            <p>Official Driver Licensing & Safety Advisory</p>
        </div>

        <div class="content">
            <p style="font-size: 1rem; margin-top: 0;">Hello <strong>{{ $user->name }}</strong>,</p>

            <div class="status-card">
                <div class="status-card-title">
                    @if($isExpired)
                        Driver's License Expired Notice
                    @else
                        Driver's License Expiring Soon
                    @endif
                </div>
                <div class="status-card-desc">
                    @if($isExpired)
                        Your driver's license expired <strong>{{ abs($daysRemaining) }} {{ abs($daysRemaining) === 1 ? 'day' : 'days' }} ago</strong> on <strong>{{ $formattedExpiry }}</strong>. Please process your renewal as driving with an expired license carries penalties and is illegal under LTO regulations.
                    @else
                        Your driver's license will expire in <strong>{{ $daysRemaining }} {{ $daysRemaining === 1 ? 'day' : 'days' }}</strong> on <strong>{{ $formattedExpiry }}</strong>. We encourage renewing early to avoid long queues and penalties.
                    @endif
                </div>
            </div>

            <table class="details-table">
                <tr>
                    <td class="details-label">Account Name:</td>
                    <td class="details-value">{{ $user->name }} ({{ $userRole }})</td>
                </tr>
                @if($licenseNumber)
                    <tr>
                        <td class="details-label">License Number:</td>
                        <td class="details-value">{{ $licenseNumber }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="details-label">Expiration Date:</td>
                    <td class="details-value">{{ $formattedExpiry }}</td>
                </tr>
                <tr>
                    <td class="details-label">Status:</td>
                    <td class="details-value" style="color: {{ $isExpired ? '#dc2626' : '#d97706' }}; font-weight: 700;">
                        {{ $isExpired ? 'Expired (' . abs($daysRemaining) . ' days ago)' : 'Expiring in ' . $daysRemaining . ' days' }}
                    </td>
                </tr>
            </table>

            <div class="guide-box">
                <div class="guide-title">
                    Official LTO LTMS Renewal Steps
                </div>
                <ol class="guide-steps">
                    <li><strong>Online CDE Exam:</strong> Log in to the <a href="https://portal.lto.gov.ph" target="_blank" rel="noopener noreferrer" style="color: #2563eb; text-decoration: underline;">LTO LTMS Portal</a> and take the FREE Comprehensive Driver's Education (CDE) online validation exam to get your Certificate.</li>
                    <li><strong>Medical Examination:</strong> Visit an accredited LTO medical clinic for your medical certificate (clinic automatically transmits results to LTMS).</li>
                    <li><strong>Application Submission:</strong> Submit your renewal application on the LTMS Portal or walk in to your nearest LTO Licensing Center or District Office.</li>
                    <li><strong>Keep Records Updated:</strong> Once renewed, update your license expiration date in your DriveED Hub profile!</li>
                </ol>
            </div>

            <div class="driveed-perk-box">
                <div class="driveed-perk-title">
                    DriveED Advantage — Refresher Training &amp; DL Code Upgrade
                </div>
                <div class="driveed-perk-text">
                    Renewing your license is the best time to upgrade your driving capabilities! Whether you want to add Motorcycle (A/A1) or 4-Wheels (B/B1/B2) to your license, or need a quick confidence refresher driving session, <strong>{{ $school->name ?? 'DriveED Hub' }}</strong> has certified instructors and modern training vehicles ready for you.
                </div>
            </div>

            <div class="btn-container">
                <a href="https://portal.lto.gov.ph" target="_blank" rel="noopener noreferrer" class="btn-primary">
                    Open LTO LTMS Portal
                </a>
                @if($school)
                    <a href="{{ school_route($user instanceof \App\Models\Student ? 'student.profile' : 'instructor.profile', [], $school) }}" class="btn-secondary">
                        Update License in DriveED
                    </a>
                @endif
            </div>
        </div>

        <div class="footer">
            <p><strong>{{ $school->name ?? 'DriveED Hub' }}</strong></p>
            <p>This is an automated advisory notification. Driver's license renewals are officially processed directly through the Land Transportation Office (LTO).</p>
            <p>&copy; {{ date('Y') }} {{ $school->name ?? 'DriveED Hub' }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
