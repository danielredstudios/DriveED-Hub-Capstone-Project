@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Instructor Dashboard')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $useGradient = $settings?->use_gradient_header ?? true;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* ── Dashboard sections ── */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .dashboard-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .dashboard-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 16px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dashboard-card-title svg {
        color: {{ $primaryColor }};
    }

    /* ── Next lesson card ── */
    .next-lesson {
        padding: 16px;
        border-radius: 10px;
        border-left: 4px solid {{ $primaryColor }};
        background: #f9fafb;
    }

    .lesson-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .lesson-date {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .lesson-time {
        font-size: 0.9rem;
        color: #4b5563;
        margin-bottom: 8px;
    }

    .lesson-status {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-scheduled { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    /* ── Bookings list ── */
    .bookings-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .booking-item {
        padding: 12px 14px;
        background: #f9fafb;
        border-radius: 8px;
        border-left: 3px solid {{ $primaryColor }};
    }

    .booking-student {
        font-size: 0.92rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .booking-course {
        font-size: 0.82rem;
        color: #6b7280;
        margin-bottom: 2px;
    }

    .booking-datetime {
        font-size: 0.78rem;
        color: #9ca3af;
        margin-bottom: 4px;
    }

    .booking-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .no-data {
        text-align: center;
        color: #9ca3af;
        font-size: 0.9rem;
        padding: 24px;
    }

    /* ── Quick actions ── */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-top: 20px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 16px;
        @if($useGradient)
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
        background: {{ $primaryColor }};
        @endif
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 500;
        font-size: 0.9rem;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.18);
        color: white;
        text-decoration: none;
    }

    .branch-badge {
        margin-left: 8px;
        padding: 3px 10px;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 12px;
        font-size: 0.85rem;
        color: #374151;
    }

    .branch-icon {
        margin-right: 3px;
    }

    .icon-18 {
        width: 18px;
        height: 18px;
    }

    .icon-20 {
        width: 20px;
        height: 20px;
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .icon-40 {
        width: 40px;
        height: 40px;
    }

    .empty-state-compact {
        padding: 24px 16px;
    }

    @media (max-width: 768px) {
        .dashboard-grid { grid-template-columns: 1fr; }
        .actions-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 480px) {
        .actions-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back — here's your overview for today
                @if($instructor->branch)
                    <span class="branch-badge"><i class="bi bi-building branch-icon"></i>{{ $instructor->branch->name }}</span>
                @endif
            </p>
        </div>
    </div>

    @if($instructor->license_status !== 'verified')
        <div class="alert alert-{{ $instructor->license_status === 'rejected' ? 'danger' : 'warning' }} mb-4 border-0 shadow-sm d-flex align-items-center" style="border-radius: 12px; padding: 16px 20px;">
            <div class="alert-icon-wrapper me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.3); border-radius: 10px;">
                @if($instructor->license_status === 'rejected')
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x-circle-fill text-white" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                    </svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill text-white" viewBox="0 0 16 16">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                @endif
            </div>
            <div class="alert-content flex-grow-1">
                <h5 class="alert-heading mb-1 fw-bold text-white">
                    @if($instructor->license_status === 'none')
                        Accreditation Required
                    @elseif($instructor->license_status === 'pending')
                        Accreditation Pending
                    @elseif($instructor->license_status === 'rejected')
                        Accreditation Rejected
                    @endif
                </h5>
                <p class="mb-0 text-white opacity-90">
                    @if($instructor->license_status === 'none')
                        Please upload your Professional Driver's License to be eligible for session assignments.
                    @elseif($instructor->license_status === 'pending')
                        Your license is currently under review by the administration. You will be notified once verified.
                    @elseif($instructor->license_status === 'rejected')
                        <strong>Reason:</strong> {{ $instructor->license_rejection_reason ?? 'No reason provided.' }} Please re-upload a clear copy of your license.
                    @endif
                </p>
            </div>
            @if($instructor->license_status !== 'pending')
                <div class="alert-actions ms-3">
                    <a href="{{ $schoolRoute('instructor.profile') }}" class="btn btn-light btn-sm fw-bold px-3" style="border-radius: 8px;">Upload Now</a>
                </div>
            @endif
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card active">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Today's Lessons</div>
                        <div class="stat-value">{{ $todaysLessons }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Scheduled for today</div>
            </div>
        </div>
        <div class="stat-card students">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">This Week</div>
                        <div class="stat-value">{{ $weeklyLessons }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Sessions this week</div>
            </div>
        </div>
        <div class="stat-card growth">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Active Students</div>
                        <div class="stat-value">{{ $activeStudents }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Currently assigned to you</div>
            </div>
        </div>
        <div class="stat-card inactive">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Pending Admin Review</div>
                        <div class="stat-value">{{ $pendingBookings }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Lessons you marked as done</div>
            </div>
        </div>
    </div>

    <!-- Schedule Overview + Upcoming -->
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h2 class="dashboard-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Schedule Overview
            </h2>
            @if($nextLesson)
                <div class="next-lesson">
                    <div class="lesson-label">Next Lesson</div>
                    <div class="lesson-date">{{ \Carbon\Carbon::parse($nextLesson->date)->format('l, F j, Y') }}</div>
                    <div class="lesson-time">{{ \Carbon\Carbon::parse($nextLesson->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($nextLesson->end_time)->format('g:i A') }}</div>
                    <div class="lesson-status status-{{ $nextLesson->status }}">{{ ucfirst($nextLesson->status) }}</div>
                </div>
            @else
                <div class="empty-state empty-state-compact">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-40"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="empty-state-title">No upcoming lessons</p>
                    <p class="empty-state-text">Scheduled lessons will appear here</p>
                </div>
            @endif
        </div>

        <div class="dashboard-card">
            <h2 class="dashboard-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Upcoming Sessions
            </h2>
            @if($upcomingBookings->count() > 0)
                <div class="bookings-list">
                    @foreach($upcomingBookings as $booking)
                        <div class="booking-item">
                            <div class="booking-student">{{ $booking->student->name }}</div>
                            <div class="booking-course">{{ $booking->course->title }}</div>
                            <div class="booking-datetime">{{ \Carbon\Carbon::parse($booking->scheduled_at)->format('M j, Y g:i A') }}</div>
                            <div class="booking-status status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state empty-state-compact">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-40"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="empty-state-title">No upcoming schedules</p>
                    <p class="empty-state-text">Your schedule will appear here</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="actions-grid">
        <a href="{{ $schoolRoute('instructor.schedule') }}" class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            My Schedule
        </a>
        <a href="{{ $schoolRoute('instructor.sessions.create') }}" class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Log Session
        </a>
        <a href="{{ $schoolRoute('instructor.grades') }}" class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Grade Students
        </a>
        <a href="{{ $schoolRoute('instructor.students.index') }}" class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            My Students
        </a>
    </div>
</div>
@endsection
