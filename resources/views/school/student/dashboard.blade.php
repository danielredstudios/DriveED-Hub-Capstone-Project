@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Dashboard')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
    $borderRadius = $settings->border_radius ?? 8;
@endphp

<style>
    .student-dashboard {
        padding: 20px;
        margin: 0 auto;
        max-width: 1400px;
    }

    .page-header {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 4px solid {{ $primaryColor }};
    }

    .page-title {
        font-size: 2rem;
        color: #111827;
        margin: 0;
        font-weight: 400;
    }

    /* Desktop Layout - 3 column grid */
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    /* Mobile layouts - hidden by default on desktop */
    .mobile-two-col,
    .mobile-upcoming {
        display: none;
    }

    .info-card {
        background: white;
        border-radius: {{ $borderRadius }}px;
        border: 2px solid {{ $primaryColor }};
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .card-header {
        background: {{ $primaryColor }};
        color: white;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 0.9rem;
        text-align: center;
    }

    .card-body {
        padding: 16px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #6b7280;
    }

    .info-value {
        color: #111827;
        font-weight: 600;
    }

    .progress-bar-wrapper {
        margin-top: 8px;
    }

    .progress-bar-container {
        width: 100%;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: #10b981;
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    /* Radial Progress Ring */
    .progress-visual {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 12px;
    }

    .progress-ring-wrapper {
        position: relative;
        flex-shrink: 0;
    }

    .progress-ring {
        transform: rotate(-90deg);
    }

    .progress-ring-bg {
        fill: none;
        stroke: #e5e7eb;
        stroke-width: 8;
    }

    .progress-ring-fill {
        fill: none;
        stroke-width: 8;
        stroke-linecap: round;
        transition: stroke-dashoffset 1s ease;
    }

    .progress-ring-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .progress-ring-percent {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }

    .progress-ring-label {
        font-size: 0.6rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .progress-stats {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .progress-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.8rem;
    }

    .progress-stat:last-child {
        border-bottom: none;
    }

    .progress-stat-label {
        color: #6b7280;
    }

    .progress-stat-value {
        font-weight: 600;
        color: #111827;
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .stat-badge.passed {
        background: #d1fae5;
        color: #065f46;
    }

    .stat-badge.in-progress {
        background: #fef3c7;
        color: #92400e;
    }

    /* Quick Actions Section */
    .quick-actions-section {
        background: {{ $primaryColor }};
        border-radius: {{ $borderRadius }}px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .quick-actions-title {
        color: white;
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 16px 0;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .action-btn {
        background: white;
        color: {{ $primaryColor }};
        padding: 14px 16px;
        border-radius: 20px;
        text-align: center;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        display: block;
        border: 2px solid white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .action-btn:hover {
        background: {{ $secondaryColor }};
        color: {{ $primaryColor }};
        border-color: {{ $secondaryColor }};
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        text-decoration: none;
    }

    /* Today's Lesson Section */
    .todays-lesson-section {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $primaryColor }}dd 100%);
        border-radius: {{ $borderRadius }}px {{ $borderRadius }}px 0 0;
        padding: 18px 20px;
        color: white;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .todays-lesson-icon {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .todays-lesson-icon svg {
        width: 26px;
        height: 26px;
        fill: {{ $primaryColor }};
    }

    .todays-lesson-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin: 0;
        letter-spacing: 0.3px;
    }

    .todays-lesson-container {
        background: white;
        border-radius: 0 0 {{ $borderRadius }}px {{ $borderRadius }}px;
        border: 2px solid {{ $primaryColor }};
        border-top: none;
        overflow: hidden;
    }

    .empty-state {
        text-align: center;
        padding: 40px 24px;
        color: #9ca3af;
        background: white;
    }

    .empty-state-icon {
        margin-bottom: 12px;
    }

    .empty-state-icon svg {
        width: 48px;
        height: 48px;
        fill: #d1d5db;
    }

    .empty-state-text {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 500;
        color: #6b7280;
    }

    /* Lesson Card Item Styles */
    .lesson-card-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s ease;
    }

    .lesson-card-item:last-child {
        border-bottom: none;
    }

    .lesson-card-item:hover {
        background: #fafafa;
    }

    .lesson-time-badge {
        background: {{ $primaryColor }};
        color: white;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
        min-width: 80px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .lesson-details {
        flex: 1;
    }

    .lesson-course-name {
        font-weight: 600;
        font-size: 1rem;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .lesson-instructor-name {
        display: flex;
        align-items: center;
        font-size: 0.85rem;
        color: #6b7280;
    }

    /* ============================================
       MOBILE RESPONSIVE STYLES 
       ============================================ */
    
    /* Tablets and small desktops - keep desktop layout but adjust grid */
    @media screen and (max-width: 1200px) {
        .dashboard-cards {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* Mobile devices - switch to mobile layout */
    @media screen and (max-width: 768px) {
        .student-dashboard {
            padding: 12px;
        }
        
        .page-header {
            margin-bottom: 16px;
            padding-bottom: 8px;
        }
        
        .page-title {
            font-size: 1.5rem;
        }

        /* HIDE desktop 3-column grid */
        .dashboard-cards {
            display: none !important;
        }

        /* SHOW mobile Upcoming Lessons (full width) */
        .mobile-upcoming {
            display: block !important;
            margin-bottom: 12px;
        }

        /* SHOW mobile two-column layout */
        .mobile-two-col {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 12px !important;
            margin-bottom: 16px;
        }

        .info-card {
            border-width: 2px;
        }

        .card-header {
            padding: 10px 12px;
            font-size: 0.75rem;
        }

        .card-body {
            padding: 12px;
        }

        .info-row {
            padding: 6px 0;
            font-size: 0.7rem;
        }

        .quick-actions-section {
            padding: 16px;
            margin-bottom: 16px;
        }

        .quick-actions-title {
            font-size: 0.9rem;
            margin-bottom: 12px;
        }

        /* Quick Actions - 2x2 grid on mobile */
        .quick-actions-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 10px !important;
        }

        .action-btn {
            padding: 12px 8px;
            font-size: 0.75rem;
            border-radius: 20px;
        }

        .todays-lesson-section {
            padding: 14px 16px;
        }

        .todays-lesson-title {
            font-size: 0.9rem;
        }

        .lesson-card-item {
            padding: 12px 16px;
            gap: 12px;
        }

        .lesson-time-badge {
            padding: 6px 10px;
            font-size: 0.75rem;
            min-width: 70px;
        }

        .lesson-course-name {
            font-size: 0.9rem;
        }

        .lesson-instructor-name {
            font-size: 0.8rem;
        }

        .empty-state {
            padding: 30px 16px;
        }

        .empty-state-text {
            font-size: 0.85rem;
        }
    }

    /* Very small mobile devices */
    @media screen and (max-width: 480px) {
        .student-dashboard {
            padding: 10px;
        }

        .page-title {
            font-size: 1.25rem;
        }

        .card-header {
            font-size: 0.7rem;
            padding: 8px 10px;
        }

        .info-row {
            font-size: 0.65rem;
            padding: 5px 0;
        }

        .card-body {
            padding: 10px;
        }

        .action-btn {
            padding: 10px 6px;
            font-size: 0.7rem;
        }
    }

    /* Recent Feedback Section */
    .recent-feedback-section {
        margin-bottom: 20px;
    }

    .feedback-card {
        background: white;
        border-radius: {{ $borderRadius }}px;
        border: 2px solid {{ $primaryColor }};
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .feedback-item {
        padding: 14px 16px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .feedback-item:last-child {
        border-bottom: none;
    }

    .feedback-details {
        flex: 1;
        min-width: 0;
    }

    .feedback-course {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
        margin-bottom: 2px;
    }

    .feedback-meta {
        font-size: 0.8rem;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .feedback-text {
        font-size: 0.85rem;
        color: #374151;
        font-style: italic;
        line-height: 1.4;
    }

    .header-profile-row {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .header-avatar-img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid {{ $primaryColor }};
    }

    .header-avatar-fallback {
        width: 60px;
        height: 60px;
        background: {{ $primaryColor }};
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        font-weight: bold;
    }

    .header-title-tight {
        margin-bottom: 4px;
    }

    .header-email {
        margin: 0;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .header-branch {
        margin: 2px 0 0;
        color: #6b7280;
        font-size: 0.85rem;
    }

    .header-branch-icon {
        margin-right: 4px;
    }

    .progress-fill-dynamic {
        height: 100%;
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    .theory-status-passed {
        color: #10b981;
    }

    .theory-status-progress {
        color: #f59e0b;
    }

    .mobile-progress-center {
        text-align: center;
        margin-bottom: 8px;
    }

    .mobile-progress-ring-inline {
        display: inline-block;
    }

    .mobile-progress-percent {
        font-size: 1rem;
    }

    .feedback-card-body {
        padding: 0;
    }

    .feedback-empty {
        padding: 30px;
        text-align: center;
        color: #9ca3af;
    }

    .feedback-empty-icon {
        width: 40px;
        height: 40px;
        fill: #d1d5db;
        margin: 0 auto 10px;
    }

    .feedback-empty-text {
        margin: 0;
        font-size: 0.9rem;
    }

    .lesson-instructor-icon {
        fill: currentColor;
        margin-right: 4px;
    }

    .grade-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .grade-excellent { background: #d1fae5; color: #065f46; }
    .grade-good { background: #dbeafe; color: #1e40af; }
    .grade-average { background: #fef3c7; color: #92400e; }
    .grade-poor { background: #fee2e2; color: #991b1b; }

    @media screen and (max-width: 768px) {
        .feedback-item {
            padding: 12px;
        }
        .feedback-course {
            font-size: 0.8rem;
        }
        .feedback-meta {
            font-size: 0.75rem;
        }
        .feedback-text {
            font-size: 0.8rem;
        }
    }
</style>

<div class="student-dashboard">
    <div class="page-header">
        <div class="header-profile-row">
            @if($student->profile_picture)
                <img src="{{ asset('storage/' . $student->profile_picture) }}" alt="Profile" class="header-avatar-img">
            @else
                <div class="header-avatar-fallback">
                    {{ strtoupper(substr($student->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h1 class="page-title header-title-tight">Welcome, {{ $student->name }}</h1>
                <p class="header-email">{{ $student->email }}</p>
                @if($student->branchRelation)
                    <p class="header-branch"><i class="bi bi-building header-branch-icon"></i>{{ $student->branchRelation->name }}</p>
                @endif
            </div>
        </div>
    </div>

    @include('school.student.partials.license-guide', ['student' => $student, 'primaryEnrollment' => $primaryEnrollment ?? null, 'activeEnrollments' => $activeEnrollments ?? collect()])

    <!-- Desktop: 3 column layout -->
    <div class="dashboard-cards">
        @php
            $ringRadius = 40;
            $ringCircumference = 2 * 3.14159 * $ringRadius;
            $ringOffset = $ringCircumference - ($progressPercentage / 100) * $ringCircumference;
            $ringColor = $progressPercentage >= 100 ? '#10b981' : ($progressPercentage >= 50 ? $primaryColor : '#f59e0b');
        @endphp
        <div class="info-card">
            <div class="card-header">Learning Progress</div>
            <div class="card-body">
                <div class="progress-visual">
                    <div class="progress-ring-wrapper">
                        <svg class="progress-ring" width="96" height="96" viewBox="0 0 96 96">
                            <circle class="progress-ring-bg" cx="48" cy="48" r="{{ $ringRadius }}"></circle>
                            <circle class="progress-ring-fill" cx="48" cy="48" r="{{ $ringRadius }}"
                                stroke="{{ $ringColor }}"
                                stroke-dasharray="{{ $ringCircumference }}"
                                stroke-dashoffset="{{ $ringOffset }}"></circle>
                        </svg>
                        <div class="progress-ring-text">
                            <div class="progress-ring-percent">{{ $progressPercentage }}%</div>
                            <div class="progress-ring-label">Complete</div>
                        </div>
                    </div>
                    <div class="progress-stats">
                        <div class="progress-stat">
                            <span class="progress-stat-label">Sessions</span>
                            <span class="progress-stat-value">{{ $sessionsCompleted }}/{{ $totalScheduledSessions > 0 ? $totalScheduledSessions : '—' }}</span>
                        </div>
                        <div class="progress-stat">
                            <span class="progress-stat-label">Hours</span>
                            <span class="progress-stat-value">{{ $hoursCompleted }}/{{ $requiredHours ?? $course->hours_required ?? 0 }} hrs</span>
                        </div>
                        <div class="progress-stat">
                            <span class="progress-stat-label">
                                @if(strtolower($enrolledCourseType) === 'practical')
                                    PDC Status
                                @else
                                    TDC Status
                                @endif
                            </span>
                            @if(strtolower($enrolledCourseType) === 'practical')
                                <span class="stat-badge {{ $progressPercentage >= 100 ? 'passed' : 'in-progress' }}">
                                    {{ $progressPercentage >= 100 ? 'Completed' : 'In Progress' }}
                                </span>
                            @else
                                <span class="stat-badge {{ $hasPassedTheoretical ? 'passed' : 'in-progress' }}">
                                    {{ $hasPassedTheoretical ? 'Passed' : 'In Progress' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill progress-fill-dynamic" data-progress="{{ $progressPercentage }}" data-fill="{{ $ringColor }}"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="card-header">Upcoming Lessons</div>
            <div class="card-body">
                @if($nextLessons && count($nextLessons) > 0)
                    @php $nextLesson = $nextLessons->first(); @endphp
                    <div class="info-row">
                        <span class="info-label">Next Lesson</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($nextLesson->date)->format('M d, Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">{{ $nextLesson->instructor->name ?? 'TBA' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Lesson Type</span>
                        <span class="info-value">{{ $nextLesson->course->title ?? 'Driving Lesson' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">This Week</span>
                        <span class="info-value">{{ $upcomingLessons }} lessons</span>
                    </div>
                @else
                    <div class="info-row">
                        <span class="info-label">Next Lesson</span>
                        <span class="info-value">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Lesson Type</span>
                        <span class="info-value">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">This Week</span>
                        <span class="info-value">0 lessons</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="info-card">
            <div class="card-header">Enrollment Status</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Enrolled Course</span>
                    <span class="info-value">{{ $enrolledCourseName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Course Type</span>
                    <span class="info-value">{{ $enrolledCourseType }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">
                        @if(strtolower($enrolledCourseType) === 'practical')
                            Practical Status
                        @else
                            Theoretical Status
                        @endif
                    </span>
                    @if(strtolower($enrolledCourseType) === 'practical')
                        <span class="info-value {{ $progressPercentage >= 100 ? 'theory-status-passed' : 'theory-status-progress' }}">
                            {{ $progressPercentage >= 100 ? 'Completed' : 'In Progress' }}
                        </span>
                    @else
                        <span class="info-value {{ $hasPassedTheoretical ? 'theory-status-passed' : 'theory-status-progress' }}">
                            {{ $hasPassedTheoretical ? 'Passed' : 'In Progress' }}
                        </span>
                    @endif
                </div>
                <div class="info-row">
                    <span class="info-label">Active Enrollments</span>
                    <span class="info-value">{{ $activeEnrollments->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile: Upcoming Lessons full width first -->
    <div class="mobile-upcoming">
        <div class="info-card">
            <div class="card-header">Upcoming Lessons</div>
            <div class="card-body">
                @if($nextLessons && count($nextLessons) > 0)
                    @php $nextLesson = $nextLessons->first(); @endphp
                    <div class="info-row">
                        <span class="info-label">Next Lesson</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($nextLesson->date)->format('M d, Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">{{ $nextLesson->instructor->name ?? 'TBA' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Lesson Type</span>
                        <span class="info-value">{{ $nextLesson->course->title ?? 'Driving Lesson' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">This Week</span>
                        <span class="info-value">{{ $upcomingLessons }} lessons</span>
                    </div>
                @else
                    <div class="info-row">
                        <span class="info-label">Next Lesson</span>
                        <span class="info-value">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Lesson Type</span>
                        <span class="info-value">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">This Week</span>
                        <span class="info-value">0 lessons</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Mobile: Learning Progress & Enrollment Status side by side -->
    <div class="mobile-two-col">
        <div class="info-card">
            <div class="card-header">Learning Progress</div>
            <div class="card-body">
                <div class="mobile-progress-center">
                    <div class="progress-ring-wrapper mobile-progress-ring-inline">
                        <svg class="progress-ring" width="80" height="80" viewBox="0 0 96 96">
                            <circle class="progress-ring-bg" cx="48" cy="48" r="{{ $ringRadius }}"></circle>
                            <circle class="progress-ring-fill" cx="48" cy="48" r="{{ $ringRadius }}"
                                stroke="{{ $ringColor }}"
                                stroke-dasharray="{{ $ringCircumference }}"
                                stroke-dashoffset="{{ $ringOffset }}"></circle>
                        </svg>
                        <div class="progress-ring-text">
                            <div class="progress-ring-percent mobile-progress-percent">{{ $progressPercentage }}%</div>
                            <div class="progress-ring-label">Done</div>
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Sessions</span>
                    <span class="info-value">{{ $sessionsCompleted }}/{{ $totalScheduledSessions > 0 ? $totalScheduledSessions : '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Hours</span>
                    <span class="info-value">{{ $hoursCompleted }}/{{ $requiredHours ?? $course->hours_required ?? 0 }} hrs</span>
                </div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill progress-fill-dynamic" data-progress="{{ $progressPercentage }}" data-fill="{{ $ringColor }}"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="card-header">Enrollment Status</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Enrolled Course</span>
                    <span class="info-value">{{ $enrolledCourseName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Course Type</span>
                    <span class="info-value">{{ $enrolledCourseType }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">
                        @if(strtolower($enrolledCourseType) === 'practical')
                            Practical Status
                        @else
                            Theoretical Status
                        @endif
                    </span>
                    @if(strtolower($enrolledCourseType) === 'practical')
                        <span class="info-value {{ $progressPercentage >= 100 ? 'theory-status-passed' : 'theory-status-progress' }}">
                            {{ $progressPercentage >= 100 ? 'Completed' : 'In Progress' }}
                        </span>
                    @else
                        <span class="info-value {{ $hasPassedTheoretical ? 'theory-status-passed' : 'theory-status-progress' }}">
                            {{ $hasPassedTheoretical ? 'Passed' : 'In Progress' }}
                        </span>
                    @endif
                </div>
                <div class="info-row">
                    <span class="info-label">Active Enrollments</span>
                    <span class="info-value">{{ $activeEnrollments->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Feedback Section -->
    <div class="recent-feedback-section">
        <div class="feedback-card">
            <div class="card-header">Recent Grades & Feedback</div>
            <div class="card-body feedback-card-body">
                @if(($recentGrades ?? collect())->count() > 0)
                    @foreach($recentGrades as $grade)
                        <div class="feedback-item">
                            <div class="feedback-details">
                                <div class="feedback-course">{{ $grade->course->title ?? 'Driving Session' }}</div>
                                <div class="feedback-meta">
                                    {{ $grade->instructor->name ?? 'Instructor' }} &middot; {{ \Carbon\Carbon::parse($grade->updated_at)->format('M d, Y') }}
                                </div>
                                @if($grade->instructor_feedback)
                                    <div class="feedback-text">"{{ $grade->instructor_feedback }}"</div>
                                @endif
                            </div>
                            @php
                                $gradeVal = strtolower($grade->session_grade ?? '');
                                $gradeClass = match(true) {
                                    in_array($gradeVal, ['a', 'excellent', 'passed']) => 'grade-excellent',
                                    in_array($gradeVal, ['b', 'good', 'satisfactory']) => 'grade-good',
                                    in_array($gradeVal, ['c', 'average', 'needs improvement']) => 'grade-average',
                                    default => 'grade-poor',
                                };
                            @endphp
                            <span class="grade-badge {{ $gradeClass }}">{{ ucfirst($grade->session_grade) }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="feedback-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="feedback-empty-icon">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <p class="feedback-empty-text">No graded sessions yet. Your grades and instructor feedback will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Today's Lesson Section -->
    <div class="todays-lesson-section">
        <div class="todays-lesson-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/>
            </svg>
        </div>
        <h3 class="todays-lesson-title">Today's Lesson</h3>
    </div>
    <div class="todays-lesson-container">
        @php
            $todayLessons = $nextLessons ? $nextLessons->filter(function($lesson) {
                return \Carbon\Carbon::parse($lesson->date)->isToday();
            }) : collect([]);
        @endphp

        @if($todayLessons->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/>
                </svg>
            </div>
            <p class="empty-state-text">No Lessons Today</p>
        </div>
        @else
            @foreach($todayLessons as $lesson)
            <div class="lesson-card-item">
                <div class="lesson-time-badge">
                    @if($lesson->timeSlot)
                        {{ \Carbon\Carbon::parse($lesson->timeSlot->start_time)->format('g:i A') }}
                    @else
                        TBD
                    @endif
                </div>
                <div class="lesson-details">
                    <div class="lesson-course-name">{{ $lesson->course->title ?? 'Driving Lesson' }}</div>
                    <div class="lesson-instructor-name">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" class="lesson-instructor-icon">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        {{ $lesson->instructor->name ?? 'Instructor TBA' }}
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.progress-fill-dynamic').forEach(function (bar) {
        const value = Number(bar.dataset.progress || 0);
        const clamped = Math.max(0, Math.min(100, value));
        bar.style.width = clamped + '%';

        const fill = bar.dataset.fill;
        if (fill) {
            bar.style.background = fill;
        }
    });
});
</script>
@endsection
