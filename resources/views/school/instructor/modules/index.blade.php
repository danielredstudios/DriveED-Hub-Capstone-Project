@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Course Modules')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Course Modules</h1>
            <p class="lms-subtitle">Reference materials for {{ $course->title ?? 'this course' }}.</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('instructor.materials.index') }}" class="lms-btn lms-btn-muted">Back to Course Materials</a>
            <a href="{{ school_route('instructor.courses.modules.create', ['course' => $course->id]) }}" class="lms-btn lms-btn-primary">Create Module</a>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Available Modules</h2>
            <span class="lms-chip">{{ $modules->count() }} module(s)</span>
        </div>

        <ul class="lms-list" id="sortable-modules">
            @forelse($modules as $module)
                <li class="lms-item" data-id="{{ $module->id }}">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div class="sort-handle" style="cursor: grab; color: #9ca3af; padding: 0.5rem 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                        </div>
                        <div>
                            <p class="lms-item-title">{{ $module->title }}</p>
                            <p class="lms-item-meta">
                                @if(($module->module_type ?? 'lesson') === 'assessment')
                                    <span style="color: #059669; font-weight: 600;">{{ $module->questions->count() ?? 0 }} Question(s)</span>
                                @else
                                    <span style="color: #111827;">{{ $module->lessons->count() ?? 0 }} Lesson(s)</span>
                                @endif
                                | <span class="lms-chip" style="font-size: 0.7rem; background: {{ ($module->module_type ?? 'lesson') === 'assessment' ? '#ecfdf5' : '#eff6ff' }}; color: {{ ($module->module_type ?? 'lesson') === 'assessment' ? '#059669' : '#1d4ed8' }};">
                                    {{ ($module->module_type ?? 'lesson') === 'assessment' ? 'EXAM / QUIZ' : 'LESSON' }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="lms-item-links">
                        <a href="{{ school_route('instructor.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-link lms-link-view">View</a>
                        <a href="{{ school_route('instructor.courses.modules.edit', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-link lms-link-edit">Edit</a>
                    </div>
                </li>
            @empty
                <li class="lms-empty">No modules found.</li>
            @endforelse
        </ul>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('sortable-modules');
        if (!el) return;

        Sortable.create(el, {
            handle: '.sort-handle',
            animation: 150,
            ghostClass: 'lms-sortable-ghost',
            onEnd: function() {
                const ids = Array.from(el.querySelectorAll('.lms-item')).map(item => item.dataset.id);
                
                // Show a subtle loading state if you want, but silent is often better for UX
                fetch("{{ school_route('instructor.courses.modules.reorder', ['course' => $course->id]) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ module_ids: ids })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof showToast === 'function') showToast('Modules reordered successfully', 'success');
                    } else {
                        if (typeof showToast === 'function') showToast('Failed to reorder modules', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof showToast === 'function') showToast('An error occurred while reordering', 'error');
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
