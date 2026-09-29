@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school?->schoolSetting;
    $primaryColor = blank($settings?->primary_color) ? '#667eea' : $settings->primary_color;
    $secondaryColor = blank($settings?->secondary_color) ? '#764ba2' : $settings->secondary_color;
    $useGradient = $settings?->use_gradient_header ?? true;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
    }
    
    .quick-action-btn {
        padding: 15px 20px;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        text-decoration: none;
        border-radius: 10px;
        text-align: center;
        font-weight: 500;
        transition: all 0.3s ease;
        display: block;
    }
    
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        color: white;
    }
    
    /* Activity List */
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .activity-item {
        padding: 16px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s ease;
        border-radius: 8px;
        margin-bottom: 4px;
    }

    .activity-item:hover {
        background: #f9fafb;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-name {
        font-weight: 600;
        color: #111827;
        margin-bottom: 4px;
    }
    
    .activity-email {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    /* Section Title */
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 15px;
    }
    
    /* Chart container */
    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 15px;
    }
    
    /* View All Link */
    .view-all-link {
        display: block;
        text-align: center;
        padding: 12px;
        margin-top: 16px;
        color: {{ $primaryColor }};
        font-weight: 600;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .view-all-link:hover {
        background: #f9fafb;
        color: {{ $secondaryColor }};
    }
    
    /* Two Column Grid */
    .two-column-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .icon-20 {
        width: 20px;
        height: 20px;
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .stat-card-link {
        text-decoration: none;
        cursor: pointer;
    }

    .growth-indicator {
        font-weight: 600;
    }

    .growth-positive {
        color: #10b981;
    }

    .growth-negative {
        color: #ef4444;
    }

    .content-card-spaced {
        margin-bottom: 20px;
    }

    .trend-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        user-select: none;
    }

    .trend-chevron {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
    }

    .trend-chevron svg {
        width: 20px;
        height: 20px;
    }

    .trend-card-header.collapsed .trend-chevron {
        transform: rotate(-90deg);
    }

    .trend-card-body {
        max-height: 520px;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease, opacity 0.2s ease;
        opacity: 1;
    }

    .trend-card-body.collapsed {
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
        opacity: 0;
    }

    .pending-badge {
        display: inline-block;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        line-height: 22px;
        font-size: 12px;
        margin-left: 6px;
    }
    
    @media (max-width: 860px) {
        .two-column-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome to {{ $schoolName }} Admin Dashboard</p>
        </div>
    </div>

    <!-- Operational Alerts -->
    @if(($pendingEnrollments ?? 0) > 0)
        @php
            $alertClass = ($isAlertCritical ?? false) ? 'alert-danger' : 'alert-warning';
            $alertIcon = ($isAlertCritical ?? false) ? 'exclamation-circle' : 'exclamation-triangle';
        @endphp
        <div class="alert {{ $alertClass }} d-flex align-items-center mb-4 shadow-sm border-0" role="alert" style="border-radius: 12px; padding: 1.25rem;">
            <div class="me-3" style="font-size: 1.5rem;">
                <i class="fas fa-{{ $alertIcon }}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="alert-heading mb-1" style="font-weight: 700; font-size: 1.1rem;">
                            {{ $pendingEnrollments }} Enrollment Requests Awaiting Review
                        </h4>
                        <p class="mb-0 text-dark opacity-75" style="font-size: 0.95rem;">
                            @if($isAlertCritical ?? false)
                                Action Required: Pending requests have exceeded your threshold of {{ $alertThreshold }}.
                            @else
                                Note: You have pending enrollment requests that need to be processed.
                            @endif
                        </p>
                    </div>
                    <a href="{{ school_route('admin.enrollments.index') }}" class="btn btn-sm btn-light border shadow-sm px-4" style="font-weight: 600; border-radius: 8px;">
                        Manage Enrollments
                    </a>
                </div>
            </div>
        </div>
    @endif
    <div class="stats-grid">
        <a href="{{ school_route('admin.userManagement') }}" class="stat-card students stat-card-link" onclick="loadContent(this.href); return false;">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Students</div>
                        <div class="stat-value">{{ $totalStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">
                    <strong>{{ $activeStudents }}</strong> Active · <strong>{{ $inactiveStudents }}</strong> Inactive
                </div>
            </div>
        </a>
        
        <a href="{{ school_route('admin.userManagement') }}" class="stat-card instructors stat-card-link" onclick="loadContent(this.href); return false;">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Instructors</div>
                        <div class="stat-value">{{ $totalInstructors }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">
                    <strong>{{ $availableInstructors }}</strong> Available · <strong>{{ $activeInstructors }}</strong> Active
                </div>
            </div>
        </a>
        
        <a href="{{ school_route('admin.enrollments.index') }}" class="stat-card growth stat-card-link" onclick="loadContent(this.href); return false;">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">New Students</div>
                        <div class="stat-value">{{ $studentsThisMonth ?? 0 }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Registered this month</div>
            </div>
        </a>
        
        <a href="{{ school_route('admin.reports.index') }}" class="stat-card active stat-card-link" onclick="loadContent(this.href); return false;">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active Users</div>
                        <div class="stat-value">{{ $activeStudents + $activeInstructors }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Currently active accounts</div>
            </div>
        </a>

        <a href="{{ school_route('admin.enrollments.index') }}" class="stat-card info stat-card-link" onclick="loadContent(this.href); return false;">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active Enrollments</div>
                        <div class="stat-value">{{ $activeEnrollments }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-detail">Approved training requests</div>
            </div>
        </a>


    </div>

    <!-- Quick Actions -->
    <div class="content-card content-card-spaced">
        <div class="content-card-header">Quick Actions</div>
        <div class="content-card-body">
            <div class="quick-actions">
                <a href="{{ school_route('admin.enrollments.index') }}" class="quick-action-btn" onclick="loadContent(this.href); return false;">
                    Enrollments
                    @if(($pendingEnrollments ?? 0) > 0)
                        <span class="pending-badge">{{ $pendingEnrollments }}</span>
                    @endif
                </a>
                <a href="{{ school_route('admin.schedules') }}" class="quick-action-btn" onclick="loadContent(this.href); return false;">
                    Schedules
                </a>
                <a href="{{ school_route('admin.payments.index') }}" class="quick-action-btn" onclick="loadContent(this.href); return false;">
                    Payments
                </a>
                <a href="{{ school_route('admin.phase-progressions.index') }}" class="quick-action-btn" onclick="loadContent(this.href); return false;">
                    Phase Progressions
                    @if(($pendingProgressions ?? 0) > 0)
                        <span class="pending-badge">{{ $pendingProgressions }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    <!-- Two Column Layout for Recent Activity -->
    <div class="two-column-grid">
        <!-- Recent Students -->
        <div class="content-card">
            <div class="content-card-header">Recent Students</div>
            <div class="content-card-body">
                @if($recentStudents->count() > 0)
                    <ul class="activity-list">
                        @foreach($recentStudents as $student)
                        <li class="activity-item">
                            <div>
                                <div class="activity-name">{{ $student->name }}</div>
                                <div class="activity-email">{{ $student->email }}</div>
                            </div>
                            <div>
                                <span class="badge badge-{{ $student->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ school_route('admin.userManagement') }}" onclick="loadContent(this.href); return false;" class="view-all-link">
                        View All Students →
                    </a>
                @else
                    <div class="empty-state">
                        <div class="empty-state-text">No students yet</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Instructors -->
        <div class="content-card">
            <div class="content-card-header">Recent Instructors</div>
            <div class="content-card-body">
                @if($recentInstructors->count() > 0)
                    <ul class="activity-list">
                        @foreach($recentInstructors as $instructor)
                        <li class="activity-item">
                            <div>
                                <div class="activity-name">{{ $instructor->name }}</div>
                                <div class="activity-email">{{ $instructor->email }}</div>
                            </div>
                            <div>
                                <span class="badge badge-{{ $instructor->availability === 'available' ? 'success' : 'warning' }}">
                                    {{ ucfirst($instructor->availability) }}
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ school_route('admin.userManagement') }}" onclick="loadContent(this.href); return false;" class="view-all-link">
                        View All Instructors →
                    </a>
                @else
                    <div class="empty-state">
                        <div class="empty-state-text">No instructors yet</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Enrollment Trend Chart -->
    <div class="content-card">
        <div
            class="content-card-header trend-card-header"
            id="enrollmentTrendHeader"
            role="button"
            tabindex="0"
            aria-expanded="true"
            aria-controls="enrollmentTrendBody"
        >
            <span>Student Enrollment Trend (Last 12 Months)</span>
            <span class="trend-chevron" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
            </span>
        </div>
        <div class="content-card-body trend-card-body" id="enrollmentTrendBody">
            <div class="chart-container">
                <canvas id="enrollmentChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Enrollment Trend Chart
    const enrollmentData = @json($enrollmentData);
    const labels = enrollmentData.map(item => item.month);
    const data = enrollmentData.map(item => item.count);

    const ctx = document.getElementById('enrollmentChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'New Students',
                data: data,
                borderColor: '{{ $primaryColor }}',
                backgroundColor: '{{ $primaryColor }}20',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    const enrollmentTrendHeader = document.getElementById('enrollmentTrendHeader');
    const enrollmentTrendBody = document.getElementById('enrollmentTrendBody');

    if (enrollmentTrendHeader && enrollmentTrendBody) {
        const toggleEnrollmentTrend = function () {
            enrollmentTrendBody.classList.toggle('collapsed');
            enrollmentTrendHeader.classList.toggle('collapsed');

            const isExpanded = !enrollmentTrendBody.classList.contains('collapsed');
            enrollmentTrendHeader.setAttribute('aria-expanded', isExpanded.toString());
        };

        enrollmentTrendHeader.addEventListener('click', toggleEnrollmentTrend);
        enrollmentTrendHeader.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleEnrollmentTrend();
            }
        });
    }
</script>

@endsection

