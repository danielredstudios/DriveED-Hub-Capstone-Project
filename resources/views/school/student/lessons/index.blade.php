@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Module Lessons')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">{{ $module->title ?? 'Module' }}</h1>
            <p class="lms-subtitle">Course: {{ $course->title ?? 'N/A' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('student.my-course') }}" class="lms-btn lms-btn-muted">Back to My Course</a>
        </div>
    </div>

    @php
        $primaryColor = $school?->schoolSetting?->primary_color ?? '#3b82f6';
        $totalLessons = $lessons->count();
        $student = auth('student')->user();
        $enrollment = $student ? $student->enrollments()->where('course_id', $course->id)->where('status', 'approved')->first() : null;
        $hoursCompleted = $enrollment ? ($enrollment->sessionCompletions ?? collect())->where('status', 'completed')->sum('hours_completed') : 0;
        $hoursRequired = $course->hours_required ?? 15;
        $progressPct = $hoursRequired > 0 ? min(100, round(($hoursCompleted / $hoursRequired) * 100)) : 0;
        $completedLessonIds = $completedLessonIds ?? [];
        $completedLessonsCount = count($completedLessonIds);
    @endphp

    <div class="lms-card" style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-weight: 600; font-size: 0.95rem; color: #1e293b;">Module Progress</span>
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">{{ $completedLessonsCount }}/{{ $totalLessons }} Lessons Complete ({{ $progressPct }}%)</span>
        </div>
        <div style="width: 100%; height: 10px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
            <div style="width: {{ $progressPct }}%; height: 100%; background: {{ $primaryColor }}; border-radius: 999px; transition: width 0.4s ease;"></div>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Lessons Sequence</h2>
            <span class="lms-chip">{{ $lessons->count() }} {{ Str::plural('lesson', $lessons->count()) }}</span>
        </div>

        <ul class="lms-list">
            @forelse($lessons->sortBy('sort_order') as $index => $lesson)
                @php
                    $isDone = in_array($lesson->id, $completedLessonIds, true);
                    $isCurrent = !$isDone && $index === $completedLessonsCount;
                @endphp
                <li class="lms-item" style="@if($isDone) border-left: 4px solid #10b981; background: #f0fdf4; @elseif($isCurrent) border-left: 4px solid {{ $primaryColor }}; background: #eff6ff; @endif">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 34px; height: 34px; border-radius: 50%; @if($isDone) background: #10b981; color: white; @elseif($isCurrent) background: {{ $primaryColor }}; color: white; @else background: #eff6ff; color: #3b82f6; @endif display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                            @if($isDone)
                                ✓
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        <div>
                            <p class="lms-item-title">
                                {{ $lesson->title }}
                                @if($isDone)
                                    <span style="font-size: 0.72rem; color: #065f46; background: #d1fae5; padding: 2px 8px; border-radius: 10px; font-weight: 600; margin-left: 6px;">Completed</span>
                                @elseif($isCurrent)
                                    <span style="font-size: 0.72rem; color: {{ $primaryColor }}; background: #dbeafe; padding: 2px 8px; border-radius: 10px; font-weight: 600; margin-left: 6px;">Up Next</span>
                                @endif
                            </p>
                            <p class="lms-item-meta">
                                @if($lesson->video_url)
                                    <span style="color: #dc2626;">🎬 Video</span> •
                                @endif
                                @if(!empty($lesson->attachments) && count($lesson->attachments) > 0)
                                    <span style="color: #16a34a;">📎 {{ count($lesson->attachments) }} file(s)</span> •
                                @endif
                                Lesson {{ $lesson->sort_order ?? $index + 1 }}
                            </p>
                        </div>
                    </div>
                    <div class="lms-item-links">
                        <a href="{{ school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-open" style="@if($isCurrent) background: {{ $primaryColor }}; color: white; font-weight: 600; @endif">
                            {{ $isDone ? 'Review Lesson' : ($isCurrent ? 'Start Lesson' : 'Open Lesson') }}
                        </a>
                    </div>
                </li>
            @empty
                <li class="lms-empty">No lessons published in this module yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
