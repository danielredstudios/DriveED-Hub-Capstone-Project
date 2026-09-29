@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Progress')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .progress-list { display: flex; flex-direction: column; gap: 14px; }

    .progress-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .student-info h3 { font-size: 1rem; color: #1f2937; margin: 0 0 4px 0; }
    .course-name { color: #6b7280; font-size: 0.82rem; margin: 0; }

    .progress-percent {
        background: {{ $primaryColor }};
        color: white;
        padding: 6px 14px;
        border-radius: 16px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .progress-bar-container {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 14px;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, {{ $primaryColor }}, {{ $secondaryColor }});
        border-radius: 4px;
    }

    .progress-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        padding: 12px 0;
        border-top: 1px solid #f3f4f6;
    }

    .detail-item .label { color: #9ca3af; font-size: 0.72rem; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.3px; }
    .detail-item .value { color: #1f2937; font-weight: 500; font-size: 0.88rem; }

    .progress-notes {
        padding: 12px;
        background: #f9fafb;
        border-radius: 8px;
        margin-top: 12px;
    }

    .progress-notes p { color: #374151; margin: 0; font-size: 0.85rem; }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        background: white;
        border-radius: 12px;
        color: #9ca3af;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    @media (max-width: 768px) {
        .progress-header { flex-direction: column; gap: 8px; }
    }
</style>

<div class="admin-container">
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Student Progress</h1>
            <p class="page-subtitle">Track and manage student learning progress</p>
        </div>
    </div>

    <div class="progress-list">
        @forelse($progresses as $progress)
            <div class="progress-card">
                <div class="progress-header">
                    <div class="student-info">
                        <h3>{{ $progress->student->name ?? 'Unknown Student' }}</h3>
                        <p class="course-name">{{ $progress->course->title ?? 'Unknown Course' }}</p>
                    </div>
                    <div class="progress-percent">
                        {{ $progress->completion_percent }}%
                    </div>
                </div>
                
                <div class="progress-bar-container">
                    <div class="progress-bar" data-progress="{{ $progress->completion_percent }}"></div>
                </div>
                
                <div class="progress-details">
                    <div class="detail-item">
                        <span class="label">Hours Completed</span>
                        <span class="value">{{ $progress->hours_completed ?? 0 }} hrs</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Last Updated</span>
                        <span class="value">{{ $progress->last_updated ? \Carbon\Carbon::parse($progress->last_updated)->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>

                @if($progress->notes)
                <div class="progress-notes">
                    <p>{{ $progress->notes }}</p>
                </div>
                @endif
            </div>
        @empty
            <div class="empty-state">
                <p>No progress records found for your students.</p>
            </div>
        @endforelse
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.progress-bar[data-progress]').forEach(function (bar) {
            const value = parseFloat(bar.getAttribute('data-progress'));
            const width = Number.isFinite(value) ? Math.max(0, Math.min(100, value)) : 0;
            bar.style.width = width + '%';
        });
    });
</script>
@endsection
