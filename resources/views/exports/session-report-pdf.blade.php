<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle ?? 'Schedule Logs' }}</title>
    <style>
        @page { margin: 34px 32px 44px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #243247; line-height: 1.45; }
        h1 { font-size: 22px; margin: 0 0 4px; color:#173c54; }
        .school { font-size: 11px; margin-bottom: 18px; }
        .meta { border-top: 3px solid #173c54; padding-top:10px; margin-bottom:14px; }
        .summary { width:100%; border-collapse:collapse; margin-bottom:16px; }
        .summary td { padding:10px; background:#edf3f7; border:4px solid white; width:25%; }
        .number { font-size:18px; font-weight:bold; color:#173c54; }
        table.records { width:100%; border-collapse:collapse; table-layout:fixed; }
        thead { display:table-header-group; }
        tbody.session-entry { page-break-inside:avoid; }
        th { background:#173c54; color:white; text-align:left; padding:8px 6px; font-size:8px; }
        td { vertical-align:top; overflow-wrap:break-word; word-wrap:break-word; }
        .record td { padding:8px 6px; border-bottom:1px solid #d7e1e7; }
        .details td { padding:8px; background:#f3f6f8; border-bottom:8px solid white; }
        .details p { margin:0 0 4px; white-space:pre-wrap; }
        .muted { color:#526476; }
        .empty { padding:26px; border:1px solid #d7e1e7; text-align:center; }
        footer { position:fixed; bottom:-25px; font-size:8px; color:#526476; }
    </style>
</head>
<body>
    <h1>{{ $reportTitle ?? 'Schedule Logs' }}</h1>
    <div class="school">{{ $school->name }}</div>
    <div class="meta">
        <strong>Prepared for:</strong> {{ $preparedFor }} &nbsp; | &nbsp;
        <strong>Period:</strong> {{ $period }}
        @if(!empty($filterSubtitle))
            <br><strong>Filters:</strong> {{ $filterSubtitle }}
        @endif
        <br>
        <span class="muted">Generated on {{ $generatedAt->timezone(!empty($school->timezone) ? $school->timezone : 'Asia/Manila')->format('M d, Y \a\t g:i A T') }}. Shows scheduled, completed, cancelled, and no-show lessons. Hours are shown separately for each record.</span>
    </div>
    <table class="summary"><tr>
        @foreach($summary as $label => $value)
            <td><div class="number">{{ $value }}</div>{{ $label }}</td>
        @endforeach
    </tr></table>
    @if($rows->isEmpty())
        <div class="empty">No schedule logs found for the selected filters.</div>
    @else
        <table class="records">
            <thead><tr>
                <th style="width:9%;">Date & time</th><th style="width:16%;">Student</th>
                <th style="width:22%;">Course & type</th><th style="width:16%;">Instructor</th>
                <th style="width:7%;">Hours</th><th style="width:12%;">Status</th>
                <th style="width:10%;">Attendance</th><th style="width:8%;">Grade</th>
            </tr></thead>
            @foreach($rows as $row)
                <tbody class="session-entry">
                <tr class="record">
                    <td style="white-space:pre-line;">{{ $row['date'] ?: 'Not recorded' }}</td><td>{{ $row['student'] }}</td>
                    <td>{{ $row['course'] }}<br><span class="muted">{{ $row['type'] }}</span></td>
                    <td>{{ $row['instructor'] }}</td><td>{{ number_format((float) ($row['hours'] ?? 0), 1) }}</td>
                    <td>{{ $row['status'] }}</td><td>{{ $row['attendance'] ?: 'Not recorded' }}</td>
                    <td>{{ $row['grade'] === '' ? 'Not graded' : $row['grade'] }}</td>
                </tr>
                <tr class="details"><td colspan="8">
                    <p class="muted"><strong>{{ $row['source'] }}</strong></p>
                    @foreach(['notes' => 'Session notes', 'instructor_feedback' => 'Instructor feedback', 'student_feedback' => 'Student feedback', 'skills' => 'Skills practiced'] as $key => $label)
                        @if($row[$key] !== '')<p><strong>{{ $label }}:</strong> {{ $row[$key] }}</p>@endif
                    @endforeach
                </td></tr>
                </tbody>
            @endforeach
        </table>
    @endif
    <footer>{{ $school->name }} | {{ $reportTitle ?? 'Schedule Logs' }} | {{ $period }}</footer>
</body>
</html>
