@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Session Completion Details')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';

    $student = $sessionCompletion->enrollment->student ?? $sessionCompletion->enrollment->learner ?? null;
    $course = $sessionCompletion->enrollment->course ?? null;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .page-wrap {
        max-width: 980px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        border-bottom: 3px solid {{ $primaryColor }};
        padding-bottom: 12px;
    }

    .title {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
    }

    .subtitle {
        margin-top: 5px;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .back-link {
        text-decoration: none;
        color: #374151;
        background: #f3f4f6;
        padding: 8px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0;
    }

    .field {
        padding: 14px 16px;
        border-bottom: 1px solid #f3f4f6;
        border-right: 1px solid #f3f4f6;
    }

    .field:nth-child(2n) {
        border-right: none;
    }

    .label {
        color: #6b7280;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 6px;
        font-weight: 700;
    }

    .value {
        color: #111827;
        font-size: 0.95rem;
        font-weight: 600;
        word-break: break-word;
    }

    .notes {
        padding: 16px;
    }

    .notes-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        color: #374151;
        line-height: 1.45;
        white-space: pre-wrap;
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-theoretical { background: #dbeafe; color: #1d4ed8; }
    .badge-practical { background: #ede9fe; color: #6d28d9; }
</style>

<div class="page-wrap">
    <div class="header">
        <div>
            <h1 class="title">Session Completion Details</h1>
            <div class="subtitle">Review instructor-submitted session information</div>
        </div>
        <a class="back-link" href="{{ route('schools.admin.sessions.index', ['school' => $school->slug]) }}">Back to Sessions</a>
    </div>

    <div class="card">
        <div class="grid">
            <div class="field">
                <div class="label">Student</div>
                <div class="value">{{ $student->name ?? 'N/A' }}</div>
            </div>

            <div class="field">
                <div class="label">Course</div>
                <div class="value">{{ $course->title ?? 'N/A' }}</div>
            </div>

            <div class="field">
                <div class="label">Instructor</div>
                <div class="value">{{ $sessionCompletion->instructor->name ?? 'N/A' }}</div>
            </div>

            <div class="field">
                <div class="label">Session Type</div>
                <div class="value">
                    <span class="badge {{ $sessionCompletion->session_type === 'theoretical' ? 'badge-theoretical' : 'badge-practical' }}">
                        {{ ucfirst($sessionCompletion->session_type ?? 'N/A') }}
                    </span>
                </div>
            </div>

            <div class="field">
                <div class="label">Session Date</div>
                <div class="value">{{ optional($sessionCompletion->session_date)->format('M d, Y') ?? 'N/A' }}</div>
            </div>

            <div class="field">
                <div class="label">Session Time</div>
                <div class="value">{{ $sessionCompletion->session_time ? \Carbon\Carbon::parse($sessionCompletion->session_time)->format('h:i A') : 'N/A' }}</div>
            </div>

            <div class="field">
                <div class="label">Hours Completed</div>
                <div class="value">{{ number_format((float) ($sessionCompletion->hours_completed ?? 0), 1) }}</div>
            </div>

            <div class="field">
                <div class="label">Status</div>
                <div class="value">{{ ucfirst($sessionCompletion->status ?? 'completed') }}</div>
            </div>
        </div>

        <div class="notes">
            <div class="label">Notes</div>
            <div class="notes-box">{{ $sessionCompletion->notes ?: 'No notes provided.' }}</div>
        </div>
    </div>
</div>
@endsection
