@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Performance Reports')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* ── Charts Grid ── */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .chart-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .chart-card h3 {
        margin: 0 0 16px 0;
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        padding-bottom: 10px;
        border-bottom: 2px solid #f3f4f6;
    }

    .chart-container {
        height: 280px;
        position: relative;
    }

    /* ── Monthly comparison ── */
    .monthly-comparison {
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 20px;
    }

    .month-stat {
        text-align: center;
    }

    .month-stat-label {
        font-size: 0.82rem;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .month-stat-value {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .month-stat-sub {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 2px;
    }

    .trend-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.88rem;
        font-weight: 600;
        margin-top: 8px;
    }

    .trend-up { color: #10b981; }
    .trend-down { color: #ef4444; }
    .trend-neutral { color: #f59e0b; }

    /* ── Data Tables ── */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f9fafb;
        padding: 10px 14px;
        text-align: left;
        font-weight: 600;
        font-size: 0.78rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e5e7eb;
    }

    .data-table td {
        padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.88rem;
        color: #374151;
    }

    .data-table tr:hover { background: #fafbfc; }

    .student-avatar-sm {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: {{ $primaryColor }};
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.78rem;
        margin-right: 8px;
    }

    .status-badge {
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .status-completed { background: #d1fae5; color: #065f46; }
    .status-scheduled { background: #dbeafe; color: #1e40af; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .status-no-show { background: #fef3c7; color: #92400e; }

    /* ── Upcoming Lesson Items ── */
    .upcoming-lesson {
        background: #f9fafb;
        padding: 12px 14px;
        border-radius: 8px;
        margin-bottom: 8px;
        border-left: 3px solid {{ $primaryColor }};
    }

    .lesson-time {
        font-size: 0.88rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 3px;
    }

    .lesson-student {
        font-size: 0.8rem;
        color: #6b7280;
    }

    .empty-state {
        text-align: center;
        padding: 32px;
        color: #9ca3af;
        font-size: 0.9rem;
    }

    /* ── Export Button ── */
    .btn-export {
        padding: 10px 16px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-export:hover { background: #059669; color: white; }

    .icon-18 {
        width: 18px;
        height: 18px;
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .chart-card-spaced {
        margin-bottom: 24px;
    }

    .month-stat-primary {
        color: {{ $primaryColor }};
    }

    .month-stat-muted {
        color: #9ca3af;
    }

    .month-arrow {
        color: #d1d5db;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .month-arrow-icon {
        width: 20px;
        height: 20px;
        stroke: currentColor;
        stroke-width: 2.2;
        fill: none;
    }

    .table-student-wrap {
        display: flex;
        align-items: center;
    }

    .upcoming-lessons-scroll {
        max-height: 400px;
        overflow-y: auto;
    }

    .avg-grade-wrap {
        text-align: center;
        padding: 24px;
    }

    .avg-grade-value {
        font-size: 4rem;
        font-weight: 700;
        color: {{ $primaryColor }};
    }

    .avg-grade-scale {
        font-size: 1rem;
        color: #6b7280;
        margin-top: 6px;
    }

    .avg-grade-note {
        margin-top: 16px;
        padding: 12px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .avg-grade-note-text {
        font-size: 0.88rem;
        color: #374151;
    }

    .avg-grade-rating-good {
        color: #f59e0b;
    }

    .avg-grade-rating-excellent {
        color: #10b981;
    }

    .avg-grade-rating-needs {
        color: #ef4444;
    }

    @media (max-width: 1024px) {
        .charts-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Performance Reports</h1>
            <p class="page-subtitle">Overview of your teaching performance and trends</p>
        </div>
        <a href="{{ route('schools.instructor.exports.reports.pdf', $school) }}" class="btn-export">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export PDF
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Lessons</div>
                        <div class="stat-value">{{ $totalLessonsCompleted }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <div class="stat-detail">All time completed</div>
            </div>
        </div>
        <div class="stat-card growth">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Hours</div>
                        <div class="stat-value">{{ $totalHoursTaught }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Teaching time</div>
            </div>
        </div>
        <div class="stat-card students">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Students Taught</div>
                        <div class="stat-value">{{ $totalStudentsTaught }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">{{ $activeStudents }} active now</div>
            </div>
        </div>
        <div class="stat-card active">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Attendance Rate</div>
                        <div class="stat-value">{{ $attendanceRate }}%</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Last 30 days</div>
            </div>
        </div>
    </div>

    <!-- Monthly Comparison -->
    <div class="chart-card chart-card-spaced">
        <h3>Monthly Performance</h3>
        <div class="monthly-comparison">
            <div class="month-stat">
                <div class="month-stat-label">This Month</div>
                <div class="month-stat-value month-stat-primary">{{ $thisMonthLessons }}</div>
                <div class="month-stat-sub">Completed Lessons</div>
            </div>
            <div class="month-arrow" aria-hidden="true">
                <svg class="month-arrow-icon" viewBox="0 0 24 24"><path d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
            </div>
            <div class="month-stat">
                <div class="month-stat-label">Last Month</div>
                <div class="month-stat-value month-stat-muted">{{ $lastMonthLessons }}</div>
                <div class="month-stat-sub">Completed Lessons</div>
            </div>
            <div class="month-stat">
                @php
                    $difference = $thisMonthLessons - $lastMonthLessons;
                    $trend = $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'neutral');
                    $trendIcon = $difference > 0 ? '↑' : ($difference < 0 ? '↓' : '→');
                @endphp
                <div class="month-stat-label">Change</div>
                <div class="trend-indicator trend-{{ $trend }}">
                    {{ $trendIcon }} {{ abs($difference) }} lessons
                </div>
                @if($lastMonthLessons > 0)
                    <div class="month-stat-sub">({{ round(($difference / $lastMonthLessons) * 100, 1) }}%)</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3>Lessons Trend (6 Months)</h3>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Lessons by Status (30 Days)</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Tables -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3>Top Students</h3>
            @if($topStudents->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Completed Lessons</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topStudents as $record)
                            <tr>
                                <td>
                                    <div class="table-student-wrap">
                                        <div class="student-avatar-sm">
                                            {{ strtoupper(substr($record->student->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <span>{{ $record->student->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td><strong>{{ $record->lesson_count }}</strong> lessons</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">No student data available</div>
            @endif
        </div>

        <div class="chart-card">
            <h3>Upcoming Schedule</h3>
            @if($upcomingLessons->count() > 0)
                <div class="upcoming-lessons-scroll">
                    @foreach($upcomingLessons as $lesson)
                        <div class="upcoming-lesson">
                            <div class="lesson-time">
                                {{ $lesson->scheduled_at->format('M d, Y - g:i A') }}
                            </div>
                            <div class="lesson-student">
                                {{ $lesson->student->name ?? 'Unknown' }} 
                                | {{ $lesson->course->title ?? 'N/A' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">No upcoming lessons scheduled</div>
            @endif
        </div>
    </div>

    <!-- Average Grade -->
    @if($avgGrade)
        <div class="chart-card">
            <h3>Average Session Grade</h3>
            <div class="avg-grade-wrap">
                <div class="avg-grade-value">
                    {{ number_format($avgGrade, 1) }}
                </div>
                <div class="avg-grade-scale">out of 100</div>
                <div class="avg-grade-note">
                    <div class="avg-grade-note-text">
                        Performance Rating: 
                        <strong class="{{ $avgGrade >= 90 ? 'avg-grade-rating-excellent' : ($avgGrade >= 75 ? 'avg-grade-rating-good' : 'avg-grade-rating-needs') }}">
                            {{ $avgGrade >= 90 ? 'Excellent' : ($avgGrade >= 75 ? 'Good' : 'Needs Improvement') }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($lessonsByMonth);
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => {
                const [year, month] = item.month.split('-');
                return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Completed Lessons',
                data: monthlyData.map(item => item.count),
                borderColor: '{{ $primaryColor }}',
                backgroundColor: '{{ $primaryColor }}20',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($lessonsByStatus);
    
    const statusColors = {
        'completed': '#10b981',
        'scheduled': '#3b82f6',
        'cancelled': '#ef4444',
        'no-show': '#f59e0b'
    };
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: statusData.map(item => statusColors[item.status] || '#6b7280')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

@endsection