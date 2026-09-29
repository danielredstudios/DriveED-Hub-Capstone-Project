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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #667eea; color: white; padding: 10px; text-align: left; font-weight: 600; font-size: 11px; }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) { background: #f9fafb; }
        .grade-badge { padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: 600; display: inline-block; }
        .grade-high { background: #d1fae5; color: #065f46; }
        .grade-mid { background: #fef3c7; color: #92400e; }
        .grade-low { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>Grades Report</p>
        <span class="instructor-badge">Instructor: {{ $instructor->name }}</span>
        <p style="margin-top: 8px;">Generated: {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="info-box">
        <strong>Total Students:</strong> {{ $students->count() }} |
        <strong>Graded Sessions:</strong> {{ $gradedSessions }} |
        <strong>Average Grade:</strong> {{ number_format($averageGrade, 1) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Email</th>
                <th>Total Sessions</th>
                <th>Graded</th>
                <th>Avg Grade</th>
                <th>Last Session</th>
                <th>Last Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            @php
                $totalSessions = $student->bookings->count();
                $gradedBookings = $student->bookings->whereNotNull('session_grade');
                $gradedCount = $gradedBookings->count();
                $avgGrade = $gradedBookings->avg('session_grade');
                $lastBooking = $student->bookings->first();
                $lastGrade = $lastBooking ? $lastBooking->session_grade : null;
                $gradeClass = $avgGrade >= 80 ? 'grade-high' : ($avgGrade >= 60 ? 'grade-mid' : 'grade-low');
                $lastGradeClass = $lastGrade >= 80 ? 'grade-high' : ($lastGrade >= 60 ? 'grade-mid' : 'grade-low');
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->email }}</td>
                <td>{{ $totalSessions }}</td>
                <td>{{ $gradedCount }}</td>
                <td>
                    @if($avgGrade)
                        <span class="grade-badge {{ $gradeClass }}">{{ number_format($avgGrade, 1) }}</span>
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $lastBooking && $lastBooking->scheduled_at ? $lastBooking->scheduled_at->format('M d, Y') : 'N/A' }}</td>
                <td>
                    @if($lastGrade)
                        <span class="grade-badge {{ $lastGradeClass }}">{{ number_format($lastGrade, 1) }}</span>
                    @else
                        N/A
                    @endif
                </td>
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
