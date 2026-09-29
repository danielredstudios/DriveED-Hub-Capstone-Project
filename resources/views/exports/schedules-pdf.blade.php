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
        .date-section { background: #f0f4ff; padding: 10px; margin: 20px 0 10px 0; border-left: 4px solid #667eea; font-weight: 600; }
        .type-badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; font-weight: 600; }
        .type-open { background: #d1fae5; color: #065f46; }
        .type-assigned { background: #dbeafe; color: #1e40af; }
        .instructor-list { font-size: 11px; color: #4b5563; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>Schedules Report</p>
        <p>Generated: {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="info-box">
        <strong>Total Schedules:</strong> {{ $schedules->count() }} |
        <strong>Open Slots:</strong> {{ $schedules->where('type', 'open')->count() }} |
        <strong>Assigned Slots:</strong> {{ $schedules->where('type', 'assigned')->count() }}
    </div>

    @php
        $groupedSchedules = $schedules->groupBy(function($item) {
            return $item->date->format('Y-m-d');
        });
    @endphp

    @foreach($groupedSchedules as $date => $daySchedules)
    <div class="date-section">
        {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Type</th>
                <th>Slots</th>
                <th>Instructors</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daySchedules as $schedule)
            <tr>
                <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                <td>
                    <span class="type-badge type-{{ $schedule->type }}">
                        {{ ucfirst($schedule->type) }}
                    </span>
                </td>
                <td>{{ $schedule->slots ?? 'N/A' }}</td>
                <td class="instructor-list">
                    @if($schedule->instructors && $schedule->instructors->count() > 0)
                        {{ $schedule->instructors->pluck('name')->join(', ') }}
                    @else
                        <em>No instructors assigned</em>
                    @endif
                </td>
                <td>{{ $schedule->notes ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach

    <div class="footer">
        <p>This report is confidential and for internal use only.</p>
        <p>&copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.</p>
    </div>
</body>
</html>
