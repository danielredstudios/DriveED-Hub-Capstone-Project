<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #667eea; padding-bottom: 15px; }
        .header h1 { color: #667eea; margin: 5px 0; font-size: 24px; }
        .header p { color: #666; margin: 3px 0; }
        .info-box { background: #f3f4f6; padding: 10px; border-radius: 5px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #667eea; color: white; padding: 10px; text-align: left; font-weight: 600; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .status-badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; font-weight: 600; }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }
        .availability-available { background: #dbeafe; color: #1e40af; }
        .availability-unavailable { background: #fef3c7; color: #92400e; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>Instructors List Report</p>
        <p>Generated: {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="info-box">
        <strong>Total Instructors:</strong> {{ $instructors->count() }} |
        <strong>Active:</strong> {{ $instructors->where('status', 'active')->count() }} |
        <strong>Inactive:</strong> {{ $instructors->where('status', 'inactive')->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>License #</th>
                <th>Status</th>
                <th>Availability</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            @foreach($instructors as $index => $instructor)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $instructor->name }}</td>
                <td>{{ $instructor->email }}</td>
                <td>{{ $instructor->contact ?? 'N/A' }}</td>
                <td>{{ $instructor->license_number ?? 'N/A' }}</td>
                <td>
                    @php
                        $status = $instructor->status ?? 'active';
                    @endphp
                    <span class="status-badge status-{{ $status }}">
                        {{ ucfirst($status) }}
                    </span>
                </td>
                <td>
                    <span class="status-badge availability-{{ $instructor->availability ?? 'unavailable' }}">
                        {{ ucfirst($instructor->availability ?? 'N/A') }}
                    </span>
                </td>
                <td>{{ $instructor->created_at->format('M d, Y') }}</td>
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
