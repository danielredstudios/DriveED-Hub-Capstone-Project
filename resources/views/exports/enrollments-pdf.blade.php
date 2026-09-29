<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #667eea; padding-bottom: 15px; }
        .header h1 { color: #667eea; margin: 5px 0; font-size: 24px; }
        .header p { color: #666; margin: 3px 0; }
        .info-box { background: #f3f4f6; padding: 10px; border-radius: 5px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; }
        th { background: #667eea; color: white; padding: 8px 5px; text-align: left; font-weight: 600; }
        td { padding: 6px 5px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .status-badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #e5e7eb; color: #374151; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>Enrollment Requests Report</p>
        <p>Status: {{ ucfirst($status) }} | Generated: {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="info-box">
        <strong>Total Requests:</strong> {{ $enrollments->count() }} | 
        <strong>Pending:</strong> {{ $enrollments->where('status', 'pending')->count() }} | 
        <strong>Approved:</strong> {{ $enrollments->where('status', 'approved')->count() }} | 
        <strong>Rejected:</strong> {{ $enrollments->where('status', 'rejected')->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Course</th>
                <th>Type</th>
                <th>Status</th>
                <th>Requested Date</th>
                <th>Approved Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $index => $enrollment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $enrollment->learner->name }}</td>
                <td>{{ $enrollment->course->title }}</td>
                <td>{{ ucfirst($enrollment->course->type) }}</td>
                <td>
                    <span class="status-badge status-{{ $enrollment->status }}">
                        {{ ucfirst($enrollment->status) }}
                    </span>
                </td>
                <td>{{ $enrollment->requested_at?->format('M d, Y') ?? 'N/A' }}</td>
                <td>{{ $enrollment->approved_at?->format('M d, Y') ?? '-' }}</td>
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
