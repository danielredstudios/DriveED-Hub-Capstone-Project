@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Module Lessons')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Lessons</h1>
            <p class="lms-subtitle">{{ $module->title ?? 'Module' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('admin.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Back to Module</a>
            <a href="{{ school_route('admin.courses.modules.lessons.create', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary">Create Lesson</a>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Lesson Library</h2>
            <span class="lms-chip">{{ $lessons->count() }} lesson(s)</span>
        </div>

        <ul class="lms-list">
            @forelse($lessons as $lesson)
                <li class="lms-item">
                    <div>
                        <p class="lms-item-title">{{ $lesson->title }}</p>
                        <p class="lms-item-meta">
                            Sort #{{ $lesson->sort_order ?? '-' }}
                            @if(!empty($lesson->video_url))
                                | Has video
                            @endif
                        </p>
                    </div>

                    <div class="lms-item-links">
                        <a href="{{ school_route('admin.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-view">View</a>
                        <a href="{{ school_route('admin.courses.modules.lessons.edit', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-edit">Edit</a>
                    </div>
                </li>
            @empty
                <li class="lms-empty">No lessons found.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
