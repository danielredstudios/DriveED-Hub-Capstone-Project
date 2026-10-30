@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Session Completions')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#2563eb';
    $secondaryColor = $settings?->secondary_color ?? '#1d4ed8';
    $useGradient = false;
    // ...existing code...
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* ── Log Session Button ── */
    .btn-log-session {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: {{ $primaryColor }};
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-log-session:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.18);
        color: white;
    }

    /* ── Export Dropdown ── */
    .export-dropdown { position: relative; display: inline-block; }

    .btn-export-trigger {
        padding: 10px 16px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-export-trigger:hover { background: #059669; }

    .export-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 4px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        min-width: 180px;
        z-index: 100;
        overflow: hidden;
    }

    .export-menu.show { display: block; }

    .export-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        color: #374151;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: background 0.15s;
    }

    .export-menu a:hover { background: #f3f4f6; }

    /* ── Filter Bar ── */
    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 14px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .filter-bar label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
    }

    .filter-select {
        padding: 8px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #374151;
        background: white;
        cursor: pointer;
        min-width: 150px;
        outline: none;
        transition: border-color 0.2s;
    }

    .filter-select:focus { border-color: {{ $primaryColor }}; }

    /* ── Sessions Table ── */
    .sessions-table-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .table-header {
        padding: 18px 24px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .sessions-table {
        width: 100%;
        border-collapse: collapse;
    }

    .sessions-table thead th {
        background: #f9fafb;
        padding: 12px 20px;
        text-align: left;
        font-size: 0.78rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #e5e7eb;
    }

    .sessions-table tbody td {
        padding: 14px 20px;
        font-size: 0.88rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .sessions-table tbody tr:hover { background: #fafbfc; }
    .sessions-table tbody tr:last-child td { border-bottom: none; }

    /* ── Student Info ── */
    .student-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .student-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: {{ $primaryColor }};
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .student-name { font-weight: 600; color: #1f2937; }

    /* ── Badges ── */
    .type-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 16px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .type-theoretical { background: #dbeafe; color: #1e40af; }
    .type-practical { background: #ede9fe; color: #5b21b6; }

    .hours-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 16px;
        font-size: 0.78rem;
        font-weight: 600;
        background: #d1fae5;
        color: #065f46;
    }

    .date-cell .date { font-weight: 600; color: #1f2937; }
    .date-cell .time { font-size: 0.78rem; color: #9ca3af; }

    /* ── Action Buttons ── */
    .action-btns { display: flex; gap: 6px; }

    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-view { background: #dbeafe; color: #1e40af; }
    .btn-view:hover { background: #bfdbfe; color: #1e40af; }
    .btn-edit { background: #fef3c7; color: #92400e; }
    .btn-edit:hover { background: #fde68a; color: #92400e; }
    .btn-pdf { background: #fee2e2; color: #b91c1c; }
    .btn-pdf:hover { background: #fecaca; color: #991b1b; }
    .btn-excel { background: #dcfce7; color: #15803d; }
    .btn-excel:hover { background: #bbf7d0; color: #166534; }
    .btn-archive { background: #fef2f2; color: #dc2626; }
    .btn-archive:hover { background: #fee2e2; color: #b91c1c; }
    .btn-restore { background: #ecfdf5; color: #059669; }
    .btn-restore:hover { background: #d1fae5; color: #047857; }

    /* ── Tab Bar ── */
    .session-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
    }
    .session-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 0.88rem;
        font-weight: 600;
        text-decoration: none;
        color: #4b5563;
        background: #f3f4f6;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .session-tab-btn:hover {
        background: #e5e7eb;
        color: #1f2937;
    }
    .session-tab-btn.active {
        background: {{ $primaryColor }};
        color: #ffffff;
        box-shadow: 0 2px 8px {{ $primaryColor }}30;
    }
    .session-tab-badge {
        padding: 2px 7px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.25);
    }
    .session-tab-btn:not(.active) .session-tab-badge {
        background: #e5e7eb;
        color: #374151;
    }

    /* ── Empty State ── */
    .empty-state { text-align: center; padding: 48px 20px; }

    .empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #f3f4f6;
        color: #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }

    .empty-state h3 { font-size: 1.05rem; color: #374151; margin: 0 0 6px 0; }
    .empty-state p { font-size: 0.88rem; color: #9ca3af; margin: 0 0 16px 0; }

    .btn-first-session {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: {{ $primaryColor }};
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.88rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-first-session:hover { opacity: 0.9; color: white; }

    .btn-first-session svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        display: inline-block;
        margin: 0;
        vertical-align: middle;
    }

    /* ── Pagination ── */
    .table-footer {
        padding: 14px 24px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pagination-info { font-size: 0.82rem; color: #6b7280; }

    @media (max-width: 768px) {
        .filter-bar { flex-direction: column; align-items: stretch; }
        
        .admin-container { padding: 12px; }
        .page-header { flex-direction: column; gap: 12px; align-items: flex-start; }
        .page-title { font-size: 1.3rem; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .stat-value { font-size: 1.3rem; }
        
        /* Hide table, show cards */
        .table-container { display: none; }
        .session-mobile-card { display: block; }
        
        .btn-action { min-height: 44px; min-width: 44px; padding: 10px; }
    }
    
    @media (max-width: 480px) {
        .admin-container { padding: 8px; }
        .page-title { font-size: 1.1rem; }
        .stats-grid { grid-template-columns: 1fr; }
    }
    
    /* Session mobile cards */
    .session-mobile-card {
        display: none;
        background: white;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        border-left: 4px solid var(--primary-color, #2563eb);
    }
    .session-mobile-card .card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .session-mobile-card .card-row:last-child { border-bottom: none; }
    .session-mobile-card .card-label { color: #6b7280; font-size: 0.8rem; }
    .session-mobile-card .card-val { font-weight: 600; color: #1f2937; font-size: 0.85rem; }
    .session-mobile-card .card-actions {
        display: flex; gap: 8px; margin-top: 10px;
    }
    .session-mobile-card .card-actions a {
        flex: 1; text-align: center; padding: 10px; border-radius: 8px;
        text-decoration: none; font-size: 0.85rem; font-weight: 600; min-height: 44px;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .session-mobile-card .card-actions .btn-view-card { background: #eff6ff; color: #2563eb; }
    .session-mobile-card .card-actions .btn-edit-card { background: #fef3c7; color: #d97706; }

    .header-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .icon-16 { width: 16px; height: 16px; }
    .icon-18 { width: 18px; height: 18px; }
    .icon-24 { width: 24px; height: 24px; }
    .icon-36 { width: 36px; height: 36px; }

    .table-session-count {
        font-size: 0.82rem;
        color: #9ca3af;
    }

    .session-mobile-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
</style>

<div class="admin-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Session Completions</h1>
            <p class="page-subtitle">View and manage verified session completions</p>
        </div>
        <div class="header-actions">
            <div class="export-dropdown">
                <button class="btn-export-trigger" onclick="toggleExportMenu()">
                    <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export
                </button>
                <div class="export-menu" id="exportMenu">
                    <a href="{{ school_route('instructor.session-report', ['format' => 'pdf']) }}" download>
                        <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#ef4444"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Export Whole Sessions (PDF)
                    </a>
                    <a href="{{ school_route('instructor.session-report', ['format' => 'xlsx']) }}" download>
                        <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export Whole Sessions (Excel)
                    </a>
                </div>
            </div>
            <a href="{{ school_route('instructor.sessions.create') }}" class="btn-log-session">
                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Log New Session
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    @php
        $totalSessions = $stats['total_sessions'] ?? 0;
        $totalHours = $stats['total_hours'] ?? 0;
        $theoreticalCount = $stats['theoretical_count'] ?? 0;
        $practicalCount = $stats['practical_count'] ?? 0;
    @endphp
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Sessions</div>
                        <div class="stat-value">{{ $totalSessions }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                </div>
                <div class="stat-detail">All logged sessions</div>
            </div>
        </div>
        <div class="stat-card growth">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Hours Logged</div>
                        <div class="stat-value">{{ number_format($totalHours, 1) }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Total teaching hours</div>
            </div>
        </div>
        <div class="stat-card students">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Theoretical</div>
                        <div class="stat-value">{{ $theoreticalCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Classroom sessions</div>
            </div>
        </div>
        <div class="stat-card instructors">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Practical</div>
                        <div class="stat-value">{{ $practicalCount }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Driving sessions</div>
            </div>
        </div>
    </div>

    <!-- Session Active / Archived Tabs -->
    <div class="session-tabs">
        <a href="{{ school_route('instructor.sessions.index', ['tab' => 'active']) }}" class="session-tab-btn {{ empty($isArchivedView) ? 'active' : '' }}">
            <span>Active Sessions</span>
            <span class="session-tab-badge">{{ $stats['active_count'] ?? 0 }}</span>
        </a>
        <a href="{{ school_route('instructor.sessions.index', ['tab' => 'archived']) }}" class="session-tab-btn {{ !empty($isArchivedView) ? 'active' : '' }}">
            <span>Archived Sessions</span>
            <span class="session-tab-badge">{{ $stats['archived_count'] ?? 0 }}</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <label>Filter by:</label>
        <select class="filter-select" id="filterType" onchange="filterSessions()">
            <option value="">All Types</option>
            <option value="theoretical">Theoretical</option>
            <option value="practical">Practical</option>
        </select>
    </div>

    <!-- Sessions Table -->
    <div class="sessions-table-wrapper">
        <div class="table-header">
            <h2 class="table-title">Session Completions</h2>
            <span class="table-session-count">{{ $totalSessions }} {{ Str::plural('session', $totalSessions) }}</span>
        </div>

        @if($sessions->count() > 0)
            <div class="table-container">
            <table class="sessions-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Type</th>
                        <th>Hours</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                    <tr data-type="{{ $session->session_type }}">
                        <td>
                            <div class="date-cell">
                                <div class="date">{{ $session->session_date->format('M d, Y') }}</div>
                                <div class="time">{{ \Carbon\Carbon::parse($session->session_time)->format('g:i A') }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="student-info">
                                <div class="student-avatar">
                                    {{ strtoupper(substr($session->enrollment->student->name ?? 'N', 0, 1)) }}
                                </div>
                                <span class="student-name">{{ $session->enrollment->student->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>{{ $session->enrollment->course->title ?? 'N/A' }}</td>
                        <td>
                            <span class="type-badge type-{{ $session->session_type }}">
                                {{ ucfirst($session->session_type) }}
                            </span>
                        </td>
                        <td>
                            <span class="hours-badge">{{ number_format($session->hours_completed, 1) }}h</span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ school_route('instructor.sessions.show', ['sessionCompletion' => $session->id]) }}" 
                                   class="btn-action btn-view" title="View Details">
                                    <span>View</span>
                                    <svg class="icon-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if(empty($isArchivedView))
                                <a href="{{ school_route('instructor.sessions.edit', ['sessionCompletion' => $session->id]) }}" 
                                   class="btn-action btn-edit" title="Edit Session">
                                    <span>Edit</span>
                                    <svg class="icon-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <a href="{{ school_route('instructor.session-report', ['format' => 'pdf', 'session_id' => $session->id]) }}" 
                                   class="btn-action btn-pdf" title="Export Session Log (PDF)" download>
                                    <span>PDF</span>
                                    <svg class="icon-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </a>
                                <a href="{{ school_route('instructor.session-report', ['format' => 'xlsx', 'session_id' => $session->id]) }}" 
                                   class="btn-action btn-excel" title="Export Session Log (Excel)" download>
                                    <span>Excel</span>
                                    <svg class="icon-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ school_route('instructor.sessions.archive', ['sessionCompletion' => $session->id]) }}" style="display:inline;" onsubmit="return confirm('Archive this session log?')">
                                    @csrf
                                    <button type="submit" class="btn-action btn-archive" title="Archive Session">
                                        <span>Archive</span>
                                        <svg class="icon-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ school_route('instructor.sessions.unarchive', ['sessionCompletion' => $session->id]) }}" style="display:inline;" onsubmit="return confirm('Restore this session log to active?')">
                                    @csrf
                                    <button type="submit" class="btn-action btn-restore" title="Restore Session">
                                        <span>Restore</span>
                                        <svg class="icon-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            
            {{-- Mobile card view --}}
            @foreach($sessions as $session)
            <div class="session-mobile-card" data-type="{{ $session->session_type }}">
                <div class="session-mobile-card-header">
                    <strong>{{ $session->enrollment->student->name ?? 'N/A' }}</strong>
                    <span class="type-badge type-{{ $session->session_type }}">{{ ucfirst($session->session_type) }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Date</span>
                    <span class="card-val">{{ $session->session_date->format('M d, Y') }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Time</span>
                    <span class="card-val">{{ \Carbon\Carbon::parse($session->session_time)->format('g:i A') }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Course</span>
                    <span class="card-val">{{ $session->enrollment->course->title ?? 'N/A' }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Hours</span>
                    <span class="card-val">{{ number_format($session->hours_completed, 1) }}h</span>
                </div>
                <div class="card-actions">
                    <a href="{{ school_route('instructor.sessions.show', ['sessionCompletion' => $session->id]) }}" class="btn-view-card">View</a>
                    @if(empty($isArchivedView))
                    <a href="{{ school_route('instructor.sessions.edit', ['sessionCompletion' => $session->id]) }}" class="btn-edit-card">Edit</a>
                    <a href="{{ school_route('instructor.session-report', ['format' => 'pdf', 'session_id' => $session->id]) }}" class="btn-view-card" style="background: #fee2e2; color: #b91c1c;" title="Export Session (PDF)" download>PDF</a>
                    <a href="{{ school_route('instructor.session-report', ['format' => 'xlsx', 'session_id' => $session->id]) }}" class="btn-view-card" style="background: #dcfce7; color: #15803d;" title="Export Session (Excel)" download>XLSX</a>
                    <form method="POST" action="{{ school_route('instructor.sessions.archive', ['sessionCompletion' => $session->id]) }}" style="flex: 1;" onsubmit="return confirm('Archive this session log?')">
                        @csrf
                        <button type="submit" class="btn-view-card" style="background: #fef2f2; color: #dc2626; border: none; width: 100%; cursor: pointer;">Archive</button>
                    </form>
                    @else
                    <form method="POST" action="{{ school_route('instructor.sessions.unarchive', ['sessionCompletion' => $session->id]) }}" style="flex: 1;" onsubmit="return confirm('Restore this session log?')">
                        @csrf
                        <button type="submit" class="btn-view-card" style="background: #ecfdf5; color: #059669; border: none; width: 100%; cursor: pointer;">Restore</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach

            @if($sessions->hasPages())
            <div class="table-footer">
                <div class="pagination-info">
                    Showing {{ $sessions->firstItem() }} to {{ $sessions->lastItem() }} of {{ $sessions->total() }} sessions
                </div>
                    {{ $sessions->links() }}
            </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <svg class="icon-36" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <h3>No session completions yet</h3>
                <p>Start logging your training sessions to track teaching progress.</p>
                <a href="{{ school_route('instructor.sessions.create') }}" class="btn-first-session">
                    <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Log First Session
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function toggleExportMenu() {
    document.getElementById('exportMenu').classList.toggle('show');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.export-dropdown')) {
        var menu = document.getElementById('exportMenu');
        if (menu) menu.classList.remove('show');
    }
});

function filterSessions() {
    const filter = document.getElementById('filterType').value.toLowerCase();
    const rows = document.querySelectorAll('.sessions-table tbody tr');

    rows.forEach(row => {
        const type = row.getAttribute('data-type') || '';
        row.style.display = (!filter || type === filter) ? '' : 'none';
    });
}
</script>
@endsection
