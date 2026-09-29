@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Course Materials')

@section('content')
@include('school.partials.lms-shared-styles')

<style>
    .materials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 14px;
        margin-top: 14px;
    }

    .materials-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
        padding: 16px;
        display: grid;
        gap: 10px;
    }

    .materials-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
    }

    .materials-meta {
        margin: 0;
        font-size: 0.86rem;
        color: #6b7280;
    }

    .materials-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .materials-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 9px;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .materials-chip-practical {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #bbf7d0;
    }

    .materials-chip-theoretical {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .materials-chip-neutral {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .materials-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .materials-banner {
        border: 1px solid #fde68a;
        background: #fffbeb;
        border-radius: 12px;
        padding: 12px 14px;
        color: #92400e;
        font-size: 0.9rem;
        margin-top: 12px;
    }

    .materials-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
</style>

<div class="lms-page">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Course Materials</h1>
            <p class="lms-subtitle">Browse modules and lessons by course.</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('instructor.dashboard') }}" class="lms-btn lms-btn-muted">Back to Dashboard</a>
        </div>
    </div>

    <div class="materials-stats">
        <span class="materials-chip materials-chip-practical">PDC/Combo: {{ $practicalCount }}</span>
        <span class="materials-chip materials-chip-theoretical">TDC: {{ $theoreticalCount }}</span>
        <span class="materials-chip materials-chip-neutral">Total: {{ $courses->count() }}</span>
    </div>

    @if($practicalCount === 0)
        <div class="materials-banner">
            No PDC course is tagged as practical right now. Ask admin to check course type settings.
        </div>
    @endif

    @if($courses->isNotEmpty())
        <div class="materials-grid">
            @foreach($courses as $course)
                @php
                    $effectiveType = $course->effective_course_type ?? $course->course_type;
                    $isPdcLike = in_array($effectiveType, ['practical', 'combo'], true);
                    $typeLabel = $effectiveType ? strtoupper($effectiveType) : 'UNSET';
                @endphp
                <article class="materials-card">
                    <h2 class="materials-title">{{ $course->title }}</h2>
                    <p class="materials-meta">
                        {{ $course->modules_count }} module(s)
                        @if(!empty($course->hours_required))
                            | {{ rtrim(rtrim(number_format((float) $course->hours_required, 2), '0'), '.') }} hr required
                        @endif
                    </p>

                    <div class="materials-chip-row">
                        <span class="materials-chip {{ $isPdcLike ? 'materials-chip-practical' : 'materials-chip-theoretical' }}">{{ $typeLabel }}</span>
                    </div>

                    <div class="materials-actions">
                        <a href="{{ school_route('instructor.courses.modules.index', ['course' => $course->id]) }}" class="lms-btn lms-btn-primary">Open Materials</a>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="lms-card" style="margin-top: 14px;">
            <div class="lms-empty">No courses found for this school yet.</div>
        </div>
    @endif
</div>
@endsection
