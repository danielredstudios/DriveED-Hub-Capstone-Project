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
        .stats-grid { margin: 20px 0; }
        .stats-row { display: flex; gap: 10px; margin-bottom: 10px; }
        .stat-box { flex: 1; background: #f3f4f6; padding: 15px; border-radius: 5px; text-align: center; border-left: 4px solid #667eea; }
        .stat-box .label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-box .value { font-size: 22px; font-weight: 700; color: #667eea; margin: 5px 0 0 0; }
        .section-title { font-size: 16px; color: #374151; margin: 25px 0 10px 0; padding-bottom: 5px; border-bottom: 2px solid #667eea; }
        .info-box { background: #f3f4f6; padding: 10px 15px; border-radius: 5px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #667eea; color: white; padding: 10px; text-align: left; font-weight: 600; font-size: 11px; }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) { background: #f9fafb; }
        .month-bar { display: inline-block; height: 14px; background: #667eea; border-radius: 2px; min-width: 3px; vertical-align: middle; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>Instructor Performance Report</p>
        <span class="instructor-badge">Instructor: {{ $instructor->name }}</span>
        <p style="margin-top: 8px;">Generated: {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <!-- Overall Statistics -->
    <div class="info-box">
        <strong>Total Lessons:</strong> {{ $totalLessonsCompleted }} |
        <strong>Total Hours:</strong> {{ $totalHoursTaught }} |
        <strong>Students Taught:</strong> {{ $totalStudentsTaught }} |
        <strong>Attendance Rate:</strong> {{ $attendanceRate }}% |
        <strong>Avg Grade:</strong> {{ $avgGrade ? number_format($avgGrade, 1) : 'N/A' }}
    </div>

    <!-- Monthly Lessons Breakdown -->
    @if($lessonsByMonth->count() > 0)
    <h3 class="section-title">Monthly Lessons (Last 6 Months)</h3>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Completed Lessons</th>
                <th style="width: 50%;">Visual</th>
            </tr>
        </thead>
        <tbody>
            @php $maxCount = $lessonsByMonth->max('count') ?: 1; @endphp
            @foreach($lessonsByMonth as $month)
            <tr>
                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('F Y') }}</td>
                <td>{{ $month->count }}</td>
                <td>
                    <span class="month-bar" style="width: {{ ($month->count / $maxCount) * 200 }}px;"></span>
                    {{ $month->count }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Top Students -->
    @if($topStudents->count() > 0)
    <h3 class="section-title">Top Students by Completed Lessons</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Completed Lessons</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topStudents as $index => $entry)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $entry->student->name ?? 'N/A' }}</td>
                <td>{{ $entry->lesson_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>This report is confidential and for internal use only.</p>
        <p>&copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.</p>
    </div>
</body>
</html>
