@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $module->title ?? 'Module Details')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">{{ $module->title ?? 'Module Details' }}</h1>
            <p class="lms-subtitle">Module type: {{ ucfirst($module->module_type ?? 'lesson') }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('admin.courses.modules.index', ['course' => $course->id]) }}" class="lms-btn lms-btn-muted">Back</a>
            <a href="{{ school_route('admin.courses.modules.edit', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-warn">Edit Module</a>
            <a href="{{ school_route('admin.courses.modules.lessons.create', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary">Create Lesson</a>
        </div>
    </div>

    <div class="lms-card" style="margin-bottom: 16px;">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Module Overview</h2>
            <span class="lms-chip">{{ ($module->lessons ?? collect())->count() }} lesson(s)</span>
        </div>
        <div style="padding: 18px;">
            <p class="lms-inline-note">{{ $module->description ?? 'No module description provided.' }}</p>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Lessons</h2>
        </div>

        <ul class="lms-list">
            @forelse(($module->lessons ?? collect()) as $lesson)
                <li class="lms-item">
                    <div>
                        <p class="lms-item-title">{{ $lesson->title }}</p>
                        <p class="lms-item-meta">Sort order: {{ $lesson->sort_order ?? '-' }}</p>
                    </div>
                    <div class="lms-item-links">
                        <a href="{{ school_route('admin.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-view">View</a>
                        <a href="{{ school_route('admin.courses.modules.lessons.edit', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-edit">Edit</a>
                    </div>
                </li>
            @empty
                <li class="lms-empty">No lessons in this module yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
