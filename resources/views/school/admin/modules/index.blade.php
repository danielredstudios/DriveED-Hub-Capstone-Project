@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Manage Modules')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Course Modules</h1>
            <p class="lms-subtitle">Manage learning materials for {{ $course->title ?? 'this course' }}.</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('admin.materials.index') }}" class="lms-btn lms-btn-muted">Back to Course Materials</a>
            <a href="{{ school_route('admin.courses.modules.create', ['course' => $course->id]) }}" class="lms-btn lms-btn-primary">Create Module</a>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Modules List</h2>
            <span class="lms-chip">{{ $modules->count() }} total</span>
        </div>

        <ul class="lms-list">
            @forelse($modules as $module)
                <li class="lms-item">
                    <div>
                        <p class="lms-item-title">{{ $module->title }}</p>
                        <p class="lms-item-meta">
                            Type: {{ ucfirst($module->module_type ?? 'lesson') }}
                            | {{ $module->lessons->count() }} lesson(s)
                        </p>
                    </div>

                    <div class="lms-item-links">
                        <a href="{{ school_route('admin.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-link lms-link-view">View</a>
                        <a href="{{ school_route('admin.courses.modules.edit', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-link lms-link-edit">Edit</a>
                    </div>
                </li>
            @empty
                <li class="lms-empty">No modules found for this course yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
