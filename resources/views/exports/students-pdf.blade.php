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
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>Students List Report</p>
        <p>Branch: {{ $branchFilterLabel ?? 'All Branches' }}</p>
        <p>Generated: {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="info-box">
        <strong>Total Students:</strong> {{ $students->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Active Enrollments</th>
                <th>Enrollment Date</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            @php
                $activeEnrollments = $student->enrollments->where('status', 'approved')->count();
                $status = $student->status ?? ($activeEnrollments > 0 ? 'active' : 'inactive');
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->email }}</td>
                <td>{{ $student->contact ?? 'N/A' }}</td>
                <td>
                    <span class="status-badge status-{{ $status }}">
                        {{ ucfirst($status) }}
                    </span>
                </td>
                <td>{{ $activeEnrollments }}</td>
                <td>{{ $student->enrollment_date ? \Carbon\Carbon::parse($student->enrollment_date)->format('M d, Y') : 'N/A' }}</td>
                <td>{{ $student->created_at->format('M d, Y') }}</td>
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
