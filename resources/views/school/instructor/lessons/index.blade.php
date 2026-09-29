@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Module Lessons')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Module Lessons</h1>
            <p class="lms-subtitle">{{ $module->title ?? 'Module' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('instructor.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Back to Module</a>
            <a href="{{ school_route('instructor.courses.modules.lessons.create', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary">Create Lesson</a>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Lessons for {{ $module->title ?? 'Module' }}</h2>
            <span class="lms-chip">{{ $lessons->count() }} lesson(s)</span>
        </div>

        <ul class="lms-list" id="instructorLessonList">
            @forelse($lessons as $lesson)
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
                        <form method="POST" action="{{ school_route('instructor.courses.modules.lessons.destroy', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" onsubmit="return confirm('Delete this lesson? This action cannot be undone.');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="lms-link" style="border: none; color: #b91c1c; background: #fef2f2;">Delete</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="lms-empty">No lessons available.</li>
            @endforelse
        </ul>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('instructorLessonList');
        if (!el) return;

        Sortable.create(el, {
            handle: '.sort-handle',
            animation: 150,
            ghostClass: 'lms-sortable-ghost',
            onEnd: function() {
                const ids = Array.from(el.querySelectorAll('.lms-item')).map(item => item.dataset.lesson_id || item.dataset.lessonId);
                
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
                    if (data.success) {
                        if (typeof showToast === 'function') showToast('Lesson order saved', 'success');
                    } else {
                        if (typeof showToast === 'function') showToast('Failed to save order', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof showToast === 'function') showToast('An error occurred', 'error');
                });
            }
        });
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
