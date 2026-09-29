@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Theoretical Training')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';

    // ...existing code...
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .theoretical-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1600px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 15px;
        border-bottom: 3px solid {{ $primaryColor }};
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    /* ── Clickable stat cards ── */
    .stats-grid .stat-card.clickable {
        cursor: pointer;
    }

    /* ── Tab content ── */
    .tab-panel {
        display: none;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        overflow: hidden;
    }

    .tab-panel.active { display: block; }

    /* ── Table ── */
    .table-wrapper { overflow-x: auto; }

    table { width: 100%; border-collapse: collapse; }

    thead {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
    }

    th {
        padding: 13px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }

    td {
        padding: 13px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.88rem;
    }

    tbody tr { transition: background-color 0.15s; }
    tbody tr:hover { background-color: #f8fafc; }
    tbody tr:last-child td { border-bottom: none; }

    /* User cell */
    .user-cell { display: flex; align-items: center; gap: 10px; }

    .user-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});
        color: white; display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.95rem; flex-shrink: 0;
    }

    .user-info { display: flex; flex-direction: column; gap: 1px; }
    .user-name { font-weight: 600; color: #1f2937; font-size: 0.88rem; }
    .user-email { font-size: 0.73rem; color: #6b7280; }

    .course-title { color: #1f2937; font-weight: 600; font-size: 0.88rem; }
    .course-license { font-size: 0.73rem; color: #6b7280; margin-top: 1px; }

    .hours-display { display: flex; flex-direction: column; gap: 1px; }
    .hours-value { font-weight: 600; color: #1f2937; }
    .hours-required { font-size: 0.73rem; color: #6b7280; }

    /* Progress bar */
    .progress-wrapper { display: flex; align-items: center; gap: 8px; }

    .progress-bar-container {
        width: 80px; height: 7px; background: #e5e7eb;
        border-radius: 10px; overflow: hidden; flex-shrink: 0;
    }

    .progress-bar-fill { height: 100%; border-radius: 10px; }
    .progress-bar-fill.high { background: linear-gradient(90deg,#10b981,#059669); }
    .progress-bar-fill.mid  { background: linear-gradient(90deg,#f59e0b,#eab308); }
    .progress-bar-fill.low  { background: linear-gradient(90deg,#ef4444,#f97316); }

    .icon-13 { width: 13px; height: 13px; }
    .icon-15 { width: 15px; height: 15px; }
    .icon-18 { width: 18px; height: 18px; }
    .icon-24 { width: 24px; height: 24px; }
    .icon-28 { width: 28px; height: 28px; }

    .section-header-top-border { border-top: 1px solid #e5e7eb; }

    .progress-text { font-size:0.78rem; font-weight:600; white-space:nowrap; }
    .progress-text.high { color:#10b981; }
    .progress-text.mid  { color:#f59e0b; }
    .progress-text.low  { color:#ef4444; }

    /* Badges */
    .badge-custom {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 6px;
        font-size: 0.75rem; font-weight: 600;
    }

    .badge-success { background:#d1fae5; color:#065f46; }
    .badge-info    { background:#dbeafe; color:#1e40af; }
    .badge-ready   { background:#d1fae5; color:#065f46; animation: pulseReady 2s infinite; }
    .badge-progress{ background:#fef3c7; color:#92400e; }
    .badge-started { background:#e5e7eb; color:#6b7280; }

    @keyframes pulseReady {
        0%,100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.3); }
        50% { box-shadow: 0 0 0 4px rgba(16,185,129,0); }
    }

    /* Action buttons */
    .btn-action {
        padding: 6px 13px; border: none; border-radius: 8px; cursor: pointer;
        font-size: 0.78rem; font-weight: 600; transition: all 0.2s;
        text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
    }

    .btn-mark-passed {
        background: linear-gradient(135deg,#10b981,#059669); color: white;
    }
    .btn-mark-passed:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16,185,129,0.35);
        color: white;
    }

    .btn-view { background:#0ea5e9; color:white; }
    .btn-view:hover { background:#0284c7; transform:translateY(-1px); box-shadow:0 3px 10px rgba(14,165,233,0.3); color:white; }

    /* Section header inside tab */
    .section-header {
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e5e7eb;
    }

    .section-header.ready-bg {
        background: linear-gradient(135deg,#f0fdf4,#dcfce7);
        border-bottom-color:#a7f3d0;
    }

    .section-header.pending-bg { background:#f9fafb; }

    .section-header h3 {
        font-size: 0.95rem; font-weight: 600; color: #1f2937; margin: 0;
        display: flex; align-items: center; gap: 8px;
    }

    .count-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 22px; height: 22px; padding: 0 7px;
        border-radius: 11px; font-size: 0.72rem; font-weight: 700;
    }

    .count-badge.green { background:#d1fae5; color:#065f46; }
    .count-badge.gray  { background:#e5e7eb; color:#6b7280; }

    /* Empty state */
    .empty-state { padding: 48px 30px; text-align: center; }

    .empty-state-icon {
        width: 60px; height: 60px; margin: 0 auto 14px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }

    .empty-state-icon.green  { background: linear-gradient(135deg,#d1fae5,#a7f3d0); color:#065f46; }
    .empty-state-icon.muted  { background:#f3f4f6; color:#9ca3af; }

    .empty-state-text { font-size:0.95rem; color:#6b7280; line-height:1.5; }
    .empty-state-sub  { font-size:0.82rem; color:#9ca3af; margin-top:4px; }

    /* Search box inside passed tab */
    .search-box {
        display: flex; align-items: center; gap: 8px;
        background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 6px 12px;
    }

    .search-box svg { color:#9ca3af; flex-shrink:0; }

    .search-box input {
        border:none; outline:none; font-size:0.82rem; color:#1f2937;
        min-width:160px; background:transparent;
    }

    .search-box input::placeholder { color:#9ca3af; }

    .date-text { color:#374151; font-weight:500; }

    /* Confirm Modal */
    .modal-overlay {
        display:none; position:fixed; top:0; left:0; right:0; bottom:0;
        background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;
    }

    .modal-overlay.active { display:flex; }

    .modal-box {
        background:white; border-radius:16px; padding:30px;
        max-width:450px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3); text-align:center;
    }

    .modal-icon {
        width:56px; height:56px; margin:0 auto 16px;
        background:linear-gradient(135deg,#d1fae5,#a7f3d0); border-radius:50%;
        display:flex; align-items:center; justify-content:center; color:#065f46;
    }

    .modal-title { font-size:1.1rem; font-weight:600; color:#1f2937; margin-bottom:8px; }
    .modal-text  { font-size:0.9rem; color:#6b7280; margin-bottom:24px; line-height:1.5; }
    .modal-student { font-weight:600; color:#1f2937; }

    .modal-actions { display:flex; gap:12px; justify-content:center; }

    .modal-btn {
        padding:10px 24px; border-radius:8px; font-size:0.9rem; font-weight:600;
        border:none; cursor:pointer; transition:all 0.2s;
    }

    .modal-btn-cancel { background:#f3f4f6; color:#6b7280; }
    .modal-btn-cancel:hover { background:#e5e7eb; }

    .modal-btn-confirm { background:linear-gradient(135deg,#10b981,#059669); color:white; }
    .modal-btn-confirm:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(16,185,129,0.35); }

    @media (max-width: 768px) {
        .theoretical-container { padding: 15px; }
        .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        .stats-grid { grid-template-columns: 1fr; }
        table { min-width: 700px; }
    }
</style>

<div class="theoretical-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Theoretical Training</h1>
            <p class="page-subtitle">Track student progress, mark completions, and view passed students</p>
        </div>
    </div>



    <!-- Stats Cards (clickable - serve as tab navigation) -->
    <div class="stats-grid">
        <div class="stat-card students clickable" onclick="switchTab('training', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">In Training</div>
                        <div class="stat-value">{{ $totalInTraining }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Students currently in theoretical training</div>
            </div>
        </div>
        <div class="stat-card growth clickable" onclick="switchTab('completion', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Ready to Pass</div>
                        <div class="stat-value">{{ $readyToPass }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Completed hours, ready to mark as passed</div>
            </div>
        </div>
        <div class="stat-card instructors clickable" onclick="switchTab('passed', this)">
            <div class="stat-content">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Passed</div>
                        <div class="stat-value">{{ $totalPassed }}</div>
                    </div>
                    <div class="stat-icon">
                        <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                </div>
                <div class="stat-detail">Students who passed theoretical training</div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         TAB 1 — In Training
         ════════════════════════════════════════════════════ --}}
    <div class="tab-panel active" id="tab-training">
        <div class="table-wrapper">
            @if($activeEnrollments->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Enrolled</th>
                            <th>Hours</th>
                            <th>Progress</th>
                            <th>Sessions</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeEnrollments as $enrollment)
                            @php
                                $pClass = $enrollment->progress >= 100 ? 'high' : ($enrollment->progress >= 50 ? 'mid' : 'low');
                            @endphp
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ substr($enrollment->student->name ?? 'N', 0, 1) }}</div>
                                        <div class="user-info">
                                            <div class="user-name">{{ $enrollment->student->name ?? 'N/A' }}</div>
                                            <div class="user-email">{{ $enrollment->student->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="course-title">{{ $enrollment->course->title }}</div>
                                    <div class="course-license">{{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}</div>
                                </td>
                                <td><span class="date-text">{{ $enrollment->enrolled_at?->format('M d, Y') ?? 'N/A' }}</span></td>
                                <td>
                                    <div class="hours-display">
                                        <span class="hours-value">{{ number_format($enrollment->total_hours, 1) }} hrs</span>
                                        <span class="hours-required">of {{ number_format($enrollment->required_hours, 1) }} req</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress-wrapper">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill {{ $pClass }}" data-progress="{{ $enrollment->progress }}"></div>
                                        </div>
                                        <span class="progress-text {{ $pClass }}">{{ $enrollment->progress }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="hours-display">
                                        <span class="hours-value">{{ $enrollment->session_count }}</span>
                                        <span class="hours-required">{{ $enrollment->last_session ? $enrollment->last_session->session_date->format('M d') : '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($enrollment->progress >= 100)
                                        <span class="badge-custom badge-ready">Ready to Pass</span>
                                    @elseif($enrollment->progress > 0)
                                        <span class="badge-custom badge-progress">In Progress</span>
                                    @else
                                        <span class="badge-custom badge-started">Just Started</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('schools.instructor.theoretical.show', ['school' => $school->slug, 'enrollment' => $enrollment->id]) }}" class="btn-action btn-view">Review</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon muted">
                        <svg class="icon-28" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div class="empty-state-text">No students currently in theoretical training</div>
                    <div class="empty-state-sub">Students enrolled in theoretical courses will appear here</div>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         TAB 2 — Mark Completion
         ════════════════════════════════════════════════════ --}}
    <div class="tab-panel" id="tab-completion">
        {{-- Ready to Pass section --}}
        <div class="section-header ready-bg">
            <h3>
                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Ready to Pass
                <span class="count-badge green">{{ $readyEnrollments->count() }}</span>
            </h3>
        </div>
        <div class="table-wrapper">
            @if($readyEnrollments->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Hours Completed</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($readyEnrollments as $enrollment)
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ substr($enrollment->student->name ?? 'N', 0, 1) }}</div>
                                        <div class="user-info">
                                            <div class="user-name">{{ $enrollment->student->name ?? 'N/A' }}</div>
                                            <div class="user-email">{{ $enrollment->student->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="course-title">{{ $enrollment->course->title }}</div>
                                    <div class="course-license">{{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}</div>
                                </td>
                                <td>
                                    <div class="hours-display">
                                        <span class="hours-value">{{ number_format($enrollment->total_hours, 1) }} hrs</span>
                                        <span class="hours-required">of {{ number_format($enrollment->required_hours, 1) }} required</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress-wrapper">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill high" data-progress="100"></div>
                                        </div>
                                        <span class="progress-text high">{{ $enrollment->progress }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn-action btn-mark-passed"
                                            onclick="confirmMarkPassed('{{ $enrollment->student->name }}', {{ $enrollment->id }})">
                                        <svg class="icon-13" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Mark Passed
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon green">
                        <svg class="icon-28" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="empty-state-text">No students ready to be marked as passed yet</div>
                    <div class="empty-state-sub">Students will appear here once they complete all required hours</div>
                </div>
            @endif
        </div>

        {{-- Not Ready Yet section --}}
        @if($notReadyEnrollments->count() > 0)
            <div class="section-header pending-bg section-header-top-border">
                <h3>
                    <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#9ca3af"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Not Ready Yet
                    <span class="count-badge gray">{{ $notReadyEnrollments->count() }}</span>
                </h3>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Hours Completed</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notReadyEnrollments as $enrollment)
                            @php $pClass = $enrollment->progress >= 50 ? 'mid' : 'low'; @endphp
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ substr($enrollment->student->name ?? 'N', 0, 1) }}</div>
                                        <div class="user-info">
                                            <div class="user-name">{{ $enrollment->student->name ?? 'N/A' }}</div>
                                            <div class="user-email">{{ $enrollment->student->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="course-title">{{ $enrollment->course->title }}</div>
                                    <div class="course-license">{{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}</div>
                                </td>
                                <td>
                                    <div class="hours-display">
                                        <span class="hours-value">{{ number_format($enrollment->total_hours, 1) }} hrs</span>
                                        <span class="hours-required">of {{ number_format($enrollment->required_hours, 1) }} required</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress-wrapper">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill {{ $pClass }}" data-progress="{{ $enrollment->progress }}"></div>
                                        </div>
                                        <span class="progress-text {{ $pClass }}">{{ $enrollment->progress }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('schools.instructor.theoretical.show', ['school' => $school->slug, 'enrollment' => $enrollment->id]) }}" class="btn-action btn-view">Review</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════
         TAB 3 — Passed Students
         ════════════════════════════════════════════════════ --}}
    <div class="tab-panel" id="tab-passed">
        <div class="section-header">
            <h3>
                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                Passed Students
            </h3>
            <div class="search-box">
                <svg class="icon-15" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="searchPassed" placeholder="Search students...">
            </div>
        </div>
        <div class="table-wrapper">
            @if($passedStudents->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Total Hours</th>
                            <th>Date Passed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="passedTableBody">
                        @foreach($passedStudents as $student)
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ strtoupper(substr($student->name ?? 'N', 0, 1)) }}</div>
                                        <div class="user-info">
                                            <div class="user-name">{{ $student->name ?? 'N/A' }}</div>
                                            <div class="user-email">{{ $student->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-custom badge-info">
                                        <svg class="icon-13" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $student->total_theoretical_hours ?? 0 }} hrs
                                    </span>
                                </td>
                                <td><span class="date-text">{{ optional($student->theoretical_passed_at)->format('M d, Y') ?? 'N/A' }}</span></td>
                                <td>
                                    <span class="badge-custom badge-success">
                                        <svg class="icon-13" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Passed
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ school_route('instructor.students.show', ['id' => $student->id]) }}" class="btn-action btn-view" title="View Student Profile">
                                        <svg class="icon-13" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon muted">
                        <svg class="icon-28" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                    </div>
                    <div class="empty-state-text">No students have passed theoretical training yet</div>
                    <div class="empty-state-sub">Students will appear here after being marked as passed</div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <div class="modal-icon">
            <svg class="icon-28" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="modal-title">Mark as Passed?</div>
        <div class="modal-text">
            Are you sure you want to mark <span class="modal-student" id="modalStudentName"></span> as passed theoretical training?
            This will unlock practical course enrollment.
        </div>
        <form id="markPassedForm" method="POST" action="{{ school_route('instructor.theoretical.markAsPassed') }}">
            @csrf
            <input type="hidden" name="enrollment_id" id="modalEnrollmentId">
            <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-confirm">Yes, Mark as Passed</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.progress-bar-fill[data-progress]').forEach(function (bar) {
    const value = parseFloat(bar.getAttribute('data-progress'));
    const width = Number.isFinite(value) ? Math.max(0, Math.min(100, value)) : 0;
    bar.style.width = width + '%';
});

function switchTab(tabId, card) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
}

function confirmMarkPassed(studentName, enrollmentId) {
    document.getElementById('modalStudentName').textContent = studentName;
    document.getElementById('modalEnrollmentId').value = enrollmentId;
    document.getElementById('confirmModal').classList.add('active');
}

function closeModal() {
    document.getElementById('confirmModal').classList.remove('active');
}

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

// Search for passed students tab
document.getElementById('searchPassed')?.addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#passedTableBody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>
@endpush

@endsection
