@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $module->title ?? 'Module Details')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">{{ $module->title ?? 'Module' }}</h1>
            <p class="lms-subtitle">{{ $module->description ?? 'No module description available.' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('instructor.courses.modules.index', ['course' => $course->id]) }}" class="lms-btn lms-btn-muted">Back</a>
            <a href="{{ school_route('instructor.courses.modules.edit', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-warn">Edit Module</a>
            @if(($module->module_type ?? 'lesson') === 'assessment' || ($module->questions ?? collect())->count() > 0)
                <a href="{{ school_route('instructor.courses.modules.assessments.manage', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary">Manage Questions</a>
            @endif
            
            @if(($module->module_type ?? 'lesson') !== 'assessment')
                <a href="{{ school_route('instructor.courses.modules.lessons.create', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary">Create Lesson</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8">
        {{-- Lessons Section --}}
        @if(($module->module_type ?? 'lesson') !== 'assessment')
            <div class="lms-card">
                <div class="lms-card-head">
                    <h2 class="lms-card-title">Lessons</h2>
                    <span class="lms-chip">{{ ($module->lessons ?? collect())->count() }} total</span>
                </div>

                <ul class="lms-list" id="instructorLessonList">
                    @forelse(($module->lessons ?? collect()) as $lesson)
                        <li class="lms-item" data-lesson-id="{{ $lesson->id }}">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="sort-handle" style="cursor: grab; color: #9ca3af; padding: 0.5rem 0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                                </div>
                                <div>
                                    <p class="lms-item-title">{{ $lesson->title }}</p>
                                    <p class="lms-item-meta">Sort #{{ $lesson->sort_order ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="lms-item-links">
                                <a href="{{ school_route('instructor.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-open">View</a>
                                <a href="{{ school_route('instructor.courses.modules.lessons.edit', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-edit">Edit</a>
                            </div>
                        </li>
                    @empty
                        <li class="lms-empty">No lessons available.</li>
                    @endforelse
                </ul>
            </div>
        @endif

        {{-- Questions Section --}}
        @if(($module->module_type ?? 'lesson') === 'assessment' || ($module->questions ?? collect())->count() > 0)
            <div class="lms-card">
                <div class="lms-card-head">
                    <h2 class="lms-card-title">Exam Questions</h2>
                    <span class="lms-chip">{{ ($module->questions ?? collect())->count() }} total</span>
                </div>

                <ul class="lms-list" id="instructorQuestionList">
                    @forelse(($module->questions ?? collect()) as $question)
                        <li class="lms-item" data-question-id="{{ $question->id }}">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="sort-handle" style="cursor: grab; color: #9ca3af; padding: 0.5rem 0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                                </div>
                                <div>
                                    <p class="lms-item-title">{{ Str::limit($question->question_text, 100) }}</p>
                                    <p class="lms-item-meta">
                                        <span class="lms-chip" style="font-size: 0.7rem;">{{ str_replace('_', ' ', ucfirst($question->question_type)) }}</span>
                                        | Points: {{ $question->pivot->points ?? $question->default_points }}
                                    </p>
                                </div>
                            </div>
                            <div class="lms-item-links">
                                <span class="lms-chip">Sort #{{ $question->pivot->sort_order }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="lms-empty">No questions added yet.</li>
                    @endforelse
                </ul>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lesson Sorting
        const lessonEl = document.getElementById('instructorLessonList');
        if (lessonEl) {
            Sortable.create(lessonEl, {
                handle: '.sort-handle',
                animation: 150,
                ghostClass: 'lms-sortable-ghost',
                onEnd: function() {
                    const ids = Array.from(lessonEl.querySelectorAll('.lms-item')).map(item => item.dataset.lessonId);
                    fetch("{{ school_route('instructor.courses.modules.lessons.reorder', ['course' => $course->id, 'module' => $module->id]) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ lesson_ids: ids })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && typeof showToast === 'function') showToast('Lesson order saved', 'success');
                    });
                }
            });
        }

        // Question Sorting
        const questionEl = document.getElementById('instructorQuestionList');
        if (questionEl) {
            Sortable.create(questionEl, {
                handle: '.sort-handle',
                animation: 150,
                ghostClass: 'lms-sortable-ghost',
                onEnd: function() {
                    const ids = Array.from(questionEl.querySelectorAll('.lms-item')).map(item => item.dataset.questionId);
                    fetch("{{ school_route('instructor.courses.modules.assessments.reorder', ['course' => $course->id, 'module' => $module->id]) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ question_ids: ids })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && typeof showToast === 'function') showToast('Question order saved', 'success');
                    });
                }
            });
        }
    });
</script>

<style>
    .lms-sortable-ghost {
        opacity: 0.4;
        background: #f3f4f6 !important;
        border: 2px dashed #3b82f6 !important;
    }
    .sort-handle:active {
        cursor: grabbing;
    }
</style>
@endsection
