@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Details')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .back-btn {
        background: white;
        border: 1px solid #e5e7eb;
        padding: 9px 16px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        color: #374151;
        margin-bottom: 20px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .back-btn:hover {
        background: {{ $primaryColor }};
        color: white;
        border-color: {{ $primaryColor }};
    }

    .student-header-card {
        background: white;
        padding: 28px;
        border-radius: 12px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border-left: 4px solid {{ $primaryColor }};
    }

    .student-header-content {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 24px;
    }

    .student-main-info { flex: 1; }

    .student-main-info h1 {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 12px 0;
    }

    .student-main-info p {
        color: #6b7280;
        margin: 8px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }

    .student-main-info p strong {
        color: #374151;
        min-width: 80px;
    }

    .progress-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        min-width: 280px;
    }

    .progress-item {
        text-align: center;
        background: #f9fafb;
        padding: 14px 10px;
        border-radius: 10px;
    }

    .progress-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: {{ $primaryColor }};
        display: block;
    }

    .progress-label {
        font-size: 0.75rem;
        color: #6b7280;
        display: block;
        margin-top: 4px;
        font-weight: 600;
    }

    .section-header {
        margin-bottom: 16px;
    }

    .section-header h2 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .section-subtitle {
        font-size: 0.82rem;
        color: #9ca3af;
        margin: 6px 0 0 0;
    }

    .sessions-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .sessions-list { display: grid; gap: 12px; }

    .session-card {
        background: #fafbfc;
        padding: 18px;
        border-radius: 10px;
        border-left: 3px solid #d1d5db;
    }

    .session-card.my-session {
        background: white;
        border-left: 3px solid {{ $primaryColor }};
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }

    .session-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 12px;
    }

    .session-date { font-size: 0.95rem; font-weight: 600; color: #1f2937; }
    .session-course { font-size: 0.82rem; color: {{ $primaryColor }}; margin-top: 3px; font-weight: 600; }
    .session-instructor { font-size: 0.78rem; color: #6b7280; margin-top: 4px; display: flex; align-items: center; gap: 5px; }

    .session-badges { display: flex; flex-direction: column; align-items: flex-end; gap: 5px; }

    .my-session-badge {
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.65rem;
        font-weight: 600;
        background: {{ $primaryColor }}18;
        color: {{ $primaryColor }};
        text-transform: uppercase;
    }

    .session-status {
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-completed { background: #d1fae5; color: #065f46; }
    .status-scheduled { background: #dbeafe; color: #1e40af; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .status-no-show { background: #fef3c7; color: #92400e; }

    .session-notes {
        padding: 12px;
        background: #fffbeb;
        border-left: 3px solid #f59e0b;
        border-radius: 6px;
        font-size: 0.85rem;
        color: #92400e;
    }

    .no-notes { color: #9ca3af; font-style: italic; }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
    }

    .icon-13 {
        width: 13px;
        height: 13px;
    }

    .icon-14 {
        width: 14px;
        height: 14px;
    }

    .icon-16 {
        width: 16px;
        height: 16px;
    }

    .icon-18 {
        width: 18px;
        height: 18px;
    }

    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .status-dot-active {
        background: #10b981;
    }

    .status-dot-inactive {
        background: #ef4444;
    }

    @media (max-width: 768px) {
        .student-header-content { flex-direction: column; gap: 16px; }
        .student-main-info h1 { font-size: 1.3rem; }
        .progress-summary { min-width: auto; }
        .session-header { flex-direction: column; gap: 8px; }
    }
</style>

<div class="admin-container">
    <!-- Back Button -->
    <button class="back-btn" onclick="goBack()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Students
    </button>

    <!-- Student Header -->
    <div class="student-header-card">
        <div class="student-header-content">
            <div class="student-main-info">
                <h1>{{ $student->name }}</h1>
                @if($student->contact)
                    <p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $student->contact }}
                    </p>
                @endif
                <p>
                    <span class="status-dot {{ $student->status === 'active' ? 'status-dot-active' : 'status-dot-inactive' }}"></span>
                    {{ ucfirst($student->status) }} Student
                </p>
            </div>

            <div class="progress-summary">
                <div class="progress-item">
                    <span class="progress-value">{{ $myCompletedCount }}</span>
                    <span class="progress-label">Sessions with You</span>
                </div>
                <div class="progress-item">
                    <span class="progress-value">{{ $myUpcomingCount }}</span>
                    <span class="progress-label">Your Upcoming</span>
                </div>
                <div class="progress-item">
                    <span class="progress-value">{{ $sessions->count() }}</span>
                    <span class="progress-label">Total Histories</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Session History Header -->
    <div class="section-header">
        <h2>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Session Schedule & Logs for {{ $student->name }}
        </h2>
        <p class="section-subtitle">View notes from all instructors to maintain teaching continuity</p>
    </div>

    <!-- Sessions List -->
    <div class="sessions-container">
        @if($sessions->count() > 0)
            <div class="sessions-list">
                @foreach($sessions as $session)
                    <div class="session-card {{ $session['is_mine'] ? 'my-session' : '' }}">
                        <div class="session-header">
                            <div>
                                <div class="session-date">{{ \Carbon\Carbon::parse($session['date'])->format('l, M d, Y - g:i A') }}</div>
                                <div class="session-course">{{ $session['course'] }}</div>
                                <div class="session-instructor">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-13"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $session['is_mine'] ? 'You' : $session['instructor_name'] }}
                                </div>
                            </div>
                            <div class="session-badges">
                                @if($session['is_mine'])
                                    <span class="my-session-badge">Your Session</span>
                                @endif
                                <span class="session-status status-{{ $session['status'] === 'done' ? 'no-show' : $session['status'] }}">
                                    {{ $session['status'] === 'done' ? 'Pending Admin Review' : ucfirst($session['status']) }}
                                </span>
                            </div>
                        </div>
                        @if($session['notes'])
                            <div class="session-notes">
                                <strong>Notes:</strong><br>
                                {{ $session['notes'] }}
                            </div>
                        @else
                            <div class="session-notes no-notes">
                                No notes recorded for this session
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <p>No schedules or session completions recorded with this student yet</p>
            </div>
        @endif
    </div>
</div>
</div>

<script>
function goBack() {
    const url = `{{ url($school->slug . '/instructor/students') }}`;
    if (typeof loadContent === 'function') {
        loadContent(url);
    } else {
        window.location.href = url;
    }
}
</script>

@endsection
