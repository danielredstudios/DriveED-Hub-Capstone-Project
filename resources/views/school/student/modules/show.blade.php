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
            <a href="{{ school_route('student.courses.modules.index', ['course' => $course->id]) }}" class="lms-btn lms-btn-muted">Back to Modules</a>
        </div>
    </div>

    @if($module->module_type === 'assessment')
        <div class="lms-card">
            <div style="padding: 3rem 2rem; text-align: center;">
                <div style="width: 64px; height: 64px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <h3 style="font-size: 1.3rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">Module Assessment</h3>
                <p style="font-size: 0.9rem; color: #6b7280; margin-bottom: 2rem; max-width: 450px; margin-left: auto; margin-right: auto;">
                    This module contains an assessment to test your knowledge. Please ensure you have reviewed all lessons before starting.
                </p>

                <div style="display: flex; justify-content: center; gap: 2.5rem; margin-bottom: 2rem;">
                    <div style="text-align: center;">
                        <div style="font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.1em; margin-bottom: 4px;">Questions</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #111827;">{{ $module->questions->count() }}</div>
                    </div>
                    <div style="width: 1px; background: #e2e8f0;"></div>
                    <div style="text-align: center;">
                        <div style="font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.1em; margin-bottom: 4px;">Total Points</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">{{ $module->questions->sum('pivot.points') }}</div>
                    </div>
                </div>

                <a href="{{ school_route('student.courses.modules.assessment.take', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary" style="padding: 12px 32px; font-size: 1rem; border-radius: 12px;">
                    Start Assessment Now
                </a>
            </div>
        </div>
    @else
        <div class="lms-card">
            <div class="lms-card-head">
                <h2 class="lms-card-title">Lessons</h2>
                <span class="lms-chip">{{ ($module->lessons ?? collect())->count() }} {{ Str::plural('lesson', ($module->lessons ?? collect())->count()) }}</span>
            </div>

            <ul class="lms-list">
                @forelse(($module->lessons ?? collect())->sortBy('sort_order') as $index => $lesson)
                    <li class="lms-item">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; flex-shrink: 0;">
                                {{ $index + 1 }}
                            </div>
                            <p class="lms-item-title">{{ $lesson->title }}</p>
                        </div>
                        <div class="lms-item-links">
                            <a href="{{ school_route('student.courses.modules.lessons.show', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-link lms-link-open">Open</a>
                        </div>
                    </li>
                @empty
                    <li class="lms-empty">No lessons are available for this module yet.</li>
                @endforelse
            </ul>
        </div>
    @endif
</div>
@endsection
