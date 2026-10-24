@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Progress')

@section('content')
@php
    $settings = $school?->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#2563eb';
    $secondaryColor = $settings->secondary_color ?? '#1d4ed8';

    $progressPercent = (float) ($progressData['progress_percentage'] ?? 0);
    $progressPercent = max(0, min(100, $progressPercent));
@endphp

<style>
    .my-progress-container {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .my-progress-header {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 4px solid {{ $primaryColor }};
    }

    .my-progress-title {
        font-size: 2rem;
        color: #111827;
        margin: 0;
        font-weight: 400;
    }

    .my-progress-subtitle {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .my-progress-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        border-radius: 10px;
        background: #f3f4f6;
        color: #374151;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s ease;
    }

    .my-progress-back:hover {
        background: #e5e7eb;
        color: #111827;
    }

    .progress-card {
        background: #fff;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .progress-card-head {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .progress-card-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 600;
        color: #111827;
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
    }

    .stat-item {
        background: #f9fafb;
        border: 1px solid #f3f4f6;
        border-radius: 12px;
        padding: 14px 16px;
    }

    .stat-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .stat-value {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
    }

    .progress-bar-track {
        margin-top: 20px;
        width: 100%;
        height: 10px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 999px;
        background: {{ $primaryColor }};
        transition: width 0.4s ease;
    }

    .progress-columns {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 25px;
    }

    .progress-columns .progress-card { margin-bottom: 0; }

    .entry-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .entry-row {
        padding: 14px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.9rem;
    }

    .entry-row:last-child { border-bottom: none; padding-bottom: 0; }

    .entry-row:first-child { padding-top: 0; }

    .entry-title {
        font-weight: 600;
        color: #111827;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }

    .entry-meta {
        color: #6b7280;
        font-size: 0.82rem;
        margin-top: 4px;
    }

    .state-chip {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .state-progressing { background: #d1fae5; color: #065f46; }
    .state-pending { background: #fef3c7; color: #92400e; }
    .state-rejected { background: #fee2e2; color: #991b1b; }
    .state-neutral { background: #e0e7ff; color: #3730a3; }

    .empty-state {
        margin: 0;
        padding: 6px 0;
        font-size: 0.9rem;
        color: #6b7280;
    }

    @media (max-width: 600px) {
        .my-progress-title { font-size: 1.5rem; }
        .progress-card { padding: 20px; }
    }
</style>

<div class="my-progress-container">
    <div class="my-progress-header">
        <div>
            <h1 class="my-progress-title">My Progress</h1>
            <p class="my-progress-subtitle">Track your active enrollment and completed training history.</p>
        </div>
        <a href="{{ school_route('student.my-course') }}" class="my-progress-back">
            <i class="bi bi-arrow-left"></i> Back to My Course
        </a>
    </div>

    @if($activeEnrollment && $progressData)
        <div class="progress-card">
            <div class="progress-card-head">
                <h2 class="progress-card-title">Active Enrollment</h2>
                <span class="state-chip state-neutral">{{ ucfirst($activeEnrollment->status) }}</span>
            </div>

            <div class="stat-grid">
                <div class="stat-item">
                    <span class="stat-label">Course</span>
                    <span class="stat-value">{{ $progressData['course']->title ?? 'N/A' }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Hours</span>
                    <span class="stat-value">{{ $progressData['hours_completed'] ?? 0 }} / {{ $progressData['hours_required'] ?? 0 }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Progress</span>
                    <span class="stat-value">{{ $progressPercent }}%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Theoretical</span>
                    <span class="stat-value">{{ ($progressData['theoretical_passed'] ?? false) ? 'Passed' : 'Pending' }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Theoretical Sessions</span>
                    <span class="stat-value">{{ $progressData['theoretical_sessions'] ?? 0 }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Practical Sessions</span>
                    <span class="stat-value">{{ $progressData['practical_sessions'] ?? 0 }}</span>
                </div>
            </div>

            <div class="progress-bar-track" role="progressbar" aria-valuenow="{{ $progressPercent }}"
                aria-valuemin="0" aria-valuemax="100">
                <div class="progress-fill" style="width: {{ $progressPercent }}%"></div>
            </div>
        </div>
    @else
        <div class="progress-card">
            <p class="empty-state">No active enrollment progress available yet.</p>
        </div>
    @endif

    <div class="progress-columns">
        <div class="progress-card">
            <div class="progress-card-head">
                <h2 class="progress-card-title">Transition Status</h2>
            </div>
            <ul class="entry-list">
                @forelse($phaseProgressions as $phase)
                    @php
                        $phaseLabel = ($phase->from_phase && $phase->to_phase)
                            ? $phase->getTransitionLabel()
                            : ucfirst($phase->to_phase ?? 'Phase Update');
                    @endphp
                    <li class="entry-row">
                        <div class="entry-title">
                            {{ $phaseLabel }}
                            <span class="state-chip state-{{ $phase->progress_state }}">{{ $phase->progress_state_label }}</span>
                        </div>
                        @if($phase->requested_at)
                            <div class="entry-meta">Submitted {{ $phase->requested_at->format('M d, Y g:i A') }}</div>
                        @endif
                        @if($phase->admin_notes)
                            <div class="entry-meta">Note: {{ $phase->admin_notes }}</div>
                        @endif
                    </li>
                @empty
                    <li class="entry-row">
                        <p class="empty-state">No transition requests yet.</p>
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="progress-card">
            <div class="progress-card-head">
                <h2 class="progress-card-title">Enrollment History</h2>
            </div>
            <ul class="entry-list">
                @forelse($enrollmentHistory as $enrollment)
                    <li class="entry-row">
                        <div class="entry-title">{{ $enrollment->course->title ?? 'Course' }}</div>
                        <div class="entry-meta">Status: {{ ucfirst($enrollment->status) }}</div>
                    </li>
                @empty
                    <li class="entry-row">
                        <p class="empty-state">No enrollment history found.</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
