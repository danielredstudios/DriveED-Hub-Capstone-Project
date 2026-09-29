<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #667eea; padding-bottom: 15px; }
        .header h1 { color: #667eea; margin: 5px 0; font-size: 24px; }
        .header p { color: #666; margin: 3px 0; }
        .header .instructor-badge { display: inline-block; background: #667eea; color: white; padding: 3px 10px; border-radius: 3px; font-size: 11px; margin-top: 5px; }
        .info-box { background: #f3f4f6; padding: 10px 15px; border-radius: 5px; margin: 15px 0; }
        .info-box strong { color: #374151; }
        .stats-grid { display: flex; gap: 15px; margin: 15px 0; }
        .stat-item { flex: 1; background: #f3f4f6; padding: 10px 15px; border-radius: 5px; text-align: center; }
        .stat-item .label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-item .value { font-size: 18px; font-weight: 700; color: #667eea; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #667eea; color: white; padding: 10px; text-align: left; font-weight: 600; font-size: 11px; }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) { background: #f9fafb; }
        .type-badge { padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: 600; }
        .type-theoretical { background: #dbeafe; color: #1e40af; }
        .type-practical { background: #fce7f3; color: #9d174d; }
        .status-badge { padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: 600; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>Session Logs Report</p>
        <span class="instructor-badge">Instructor: {{ $instructor->name }}</span>
        <p style="margin-top: 8px;">Generated: {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="info-box">
        <strong>Total Sessions:</strong> {{ $sessions->count() }} |
        <strong>Total Hours:</strong> {{ number_format($totalHours, 1) }} |
        <strong>Theoretical:</strong> {{ $theoreticalCount }} |
        <strong>Practical:</strong> {{ $practicalCount }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Time</th>
                <th>Student</th>
                <th>Course</th>
                <th>Type</th>
                <th>Hours</th>
                <th>Status</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $index => $session)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $session->session_date ? $session->session_date->format('M d, Y') : 'N/A' }}</td>
                <td>{{ $session->session_time ? \Carbon\Carbon::parse($session->session_time)->format('h:i A') : 'N/A' }}</td>
                <td>{{ $session->enrollment->student->name ?? $session->enrollment->learner->name ?? 'N/A' }}</td>
                <td>{{ $session->enrollment->course->title ?? 'N/A' }}</td>
                <td>
                    <span class="type-badge type-{{ $session->session_type }}">
                        {{ ucfirst($session->session_type) }}
                    </span>
                </td>
                <td>{{ number_format($session->hours_completed, 1) }}</td>
                <td>
                    <span class="status-badge status-{{ $session->status ?? 'completed' }}">
                        {{ ucfirst($session->status ?? 'completed') }}
                    </span>
                </td>
                <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis;">{{ \Illuminate\Support\Str::limit($session->notes, 50) ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report is confidential and for internal use only.</p>
        <p>&copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.</p>
    </div>
</body>
</html>
