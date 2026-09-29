<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #667eea; padding-bottom: 15px; }
        .header h1 { color: #667eea; margin: 0; font-size: 22px; }
        .header p { color: #666; margin: 5px 0; font-size: 12px; }
        
        .info-box { background: #f3f4f6; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 11px; }
        
        .course-card { border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 20px; overflow: hidden; page-break-inside: avoid; }
        .course-header { background: #667eea; color: white; padding: 10px 15px; font-weight: bold; font-size: 13px; }
        .course-body { padding: 15px; }
        
        .course-type { display: inline-block; padding: 2px 8px; background: #e0e7ff; color: #3730a3; border-radius: 3px; font-size: 9px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase; }
        .course-description { color: #4b5563; font-size: 10.5px; margin-bottom: 15px; line-height: 1.4; }
        
        /* Table-based details row */
        .details-table { width: 100%; border-collapse: collapse; background: #f9fafb; border-radius: 4px; margin-bottom: 15px; }
        .details-table td { padding: 10px; text-align: center; width: 25%; border-right: 1px solid #e5e7eb; }
        .details-table td:last-child { border-right: none; }
        .detail-value { font-size: 13px; font-weight: bold; color: #1f2937; display: block; }
        .detail-label { font-size: 8.5px; color: #6b7280; text-transform: uppercase; margin-top: 2px; }
        
        .packages-section { border-top: 1px solid #e5e7eb; padding-top: 12px; }
        .packages-title { font-weight: bold; color: #374151; margin-bottom: 8px; font-size: 11px; }
        
        .package-item { display: inline-block; padding: 4px 10px; background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 4px; margin-right: 5px; margin-bottom: 5px; }
        .package-name { font-weight: bold; font-size: 10px; color: #1e40af; }
        .package-price { font-size: 10.5px; color: #059669; font-weight: bold; }
        
        .status-active { color: #059669; }
        .status-inactive { color: #dc2626; }
        
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>Driving Courses Catalog</p>
        <p>Generated on {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="info-box">
        <strong>Report Summary:</strong> &nbsp;
        Total Courses: {{ $courses->count() }} &nbsp; | &nbsp;
        Active: {{ $courses->where('status', 'active')->count() }} &nbsp; | &nbsp;
        Inactive: {{ $courses->where('status', '!=', 'active')->count() }}
    </div>

    @foreach($courses as $course)
    <div class="course-card">
        <div class="course-header">
            {{ $course->title }}
        </div>
        <div class="course-body">
            <span class="course-type">{{ ucfirst($course->course_type ?? 'Standard') }}</span>
            
            @if($course->description)
            <div class="course-description">
                {{ $course->description }}
            </div>
            @endif
            
            <table class="details-table">
                <tr>
                    <td>
                        <span class="detail-value">{{ $course->total_sessions ?? 0 }}</span>
                        <span class="detail-label">Sessions</span>
                    </td>
                    <td>
                        <span class="detail-value">{{ $course->hours_per_session ?? 0 }}h</span>
                        <span class="detail-label">Per Session</span>
                    </td>
                    <td>
                        <span class="detail-value">{{ ($course->total_sessions ?? 0) * ($course->hours_per_session ?? 0) }}h</span>
                        <span class="detail-label">Total Hours</span>
                    </td>
                    <td>
                        <span class="detail-value {{ $course->status === 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ strtoupper($course->status ?? 'ACTIVE') }}
                        </span>
                        <span class="detail-label">Status</span>
                    </td>
                </tr>
            </table>
            
            @if($course->packages && $course->packages->count() > 0)
            <div class="packages-section">
                <div class="packages-title">Pricing Packages:</div>
                @foreach($course->packages as $package)
                <div class="package-item">
                    <span class="package-name">{{ $package->name }}</span>
                    <span class="package-price">- ₱{{ number_format($package->price, 2) }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endforeach

    <div class="footer">
        <p>This document is generated by the {{ config('app.name') }} management system.</p>
        <p>&copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.</p>
    </div>
</body>
</html>
