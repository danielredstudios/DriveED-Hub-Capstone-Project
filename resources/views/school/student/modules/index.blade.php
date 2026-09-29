@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Course Modules')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">{{ $course->title ?? 'Course' }}</h1>
            <p class="lms-subtitle">Select a module to view lessons.</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('student.my-course') }}" class="lms-btn lms-btn-muted">Back to My Course</a>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Modules</h2>
            <span class="lms-chip">{{ $modules->count() }} {{ Str::plural('module', $modules->count()) }}</span>
        </div>

        <ul class="lms-list">
            @forelse($modules->sortBy('sort_order') as $index => $module)
                <li class="lms-item">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $module->module_type === 'assessment' ? '#eff6ff' : '#f0fdf4' }}; color: {{ $module->module_type === 'assessment' ? '#3b82f6' : '#16a34a' }}; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="lms-item-title">{{ $module->title }}</p>
                            <p class="lms-item-meta">
                                @if($module->module_type === 'assessment')
                                    <span style="color: #3b82f6; font-weight: 600;">Assessment Module</span>
                                @else
                                    {{ $module->lessons->count() ?? 0 }} {{ Str::plural('lesson', $module->lessons->count()) }}
                                    @if($module->questions && $module->questions->count() > 0)
                                        • <span style="color: #3b82f6;">+ Quiz</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="lms-item-links">
                        @if($module->module_type === 'assessment' || ($module->lessons->count() === 0 && $module->questions && $module->questions->count() > 0))
                            <a href="{{ school_route('student.courses.modules.assessment.take', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary" style="padding: 6px 16px; font-size: 0.82rem;">Start Quiz</a>
                        @else
                            <a href="{{ school_route('student.courses.modules.lessons.index', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-link lms-link-open">View Lessons</a>
                        @endif
                    </div>
                </li>
            @empty
                <li class="lms-empty">No modules are available for this course yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
