@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $lesson->title ?? 'Lesson')

@section('content')
@include('school.partials.lms-shared-styles')

@php
    $nextUrl = $navigation->next['url'] ?? null;
    $nextTitle = $navigation->next['title'] ?? null;
    $nextType = $navigation->next['type'] ?? null;
    $prevUrl = $navigation->prev['url'] ?? null;
    $myCourseUrl = school_route('student.my-course');
    $nextLabel = $nextType === 'assessment'
        ? 'Continue to Quiz'
        : ($nextTitle ? 'Next: ' . Str::limit($nextTitle, 28) : 'Next');
    $completeLabel = $nextUrl ? 'Mark complete & continue' : 'Mark lesson complete';
@endphp

<div class="lms-page lms-player"
     data-breadcrumb-course="{{ $course->title ?? '' }}"
     data-breadcrumb-module="{{ $module->title ?? '' }}"
     data-breadcrumb-lesson="{{ $lesson->title ?? '' }}">

    @include('school.partials.lms-outline', [
        'course' => $course,
        'outlineModules' => $outlineModules ?? collect(),
        'completedLessonIds' => $completedLessonIds ?? [],
        'activeModuleId' => $module->id,
        'activeLessonId' => $lesson->id,
    ])

    <div class="lms-player-main">
        <div class="lms-player-topbar">
            <div>
                <div class="lms-player-eyebrow">{{ $module->title }}</div>
                <h1 class="lms-title">{{ $lesson->title }}</h1>
            </div>
            @if($lessonCompleted)
                <span class="lms-chip" style="background:#dcfce7;color:#166534;">Completed</span>
            @else
                <span class="lms-chip">Lesson {{ $lesson->sort_order ?? '-' }}</span>
            @endif
        </div>

        <div class="lms-card">
            <div class="lms-card-head">
                <h2 class="lms-card-title">Lesson Content</h2>
            </div>

            <div style="padding: 24px;">
                <div class="lms-rich">
                    @if(!empty($lesson->content))
                        @php $hasHtml = $lesson->content !== strip_tags($lesson->content); @endphp
                        @if($hasHtml)
                            {!! $lesson->content !!}
                        @else
                            {!! nl2br(e($lesson->content)) !!}
                        @endif
                    @else
                        <p class="lms-inline-note">Lesson content is not yet available.</p>
                    @endif
                </div>

                @if(!empty($lesson->video_url))
                    <div class="lms-section">
                        <h3 class="lms-section-title">Video Resource</h3>
                        <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener" class="lms-link lms-link-view" style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600; text-decoration: none; padding: 8px 14px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Watch Lesson Video
                        </a>
                    </div>
                @endif

                @if(!empty($lesson->attachments) && is_array($lesson->attachments) && count($lesson->attachments) > 0)
                    <div class="lms-section">
                        <h3 class="lms-section-title">Attachments & Resources</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                            @foreach($lesson->attachments as $attachment)
                                <a href="{{ asset('storage/' . $attachment['path']) }}" target="_blank" style="display: flex; align-items: center; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; text-decoration: none; color: inherit; background: #fff; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.backgroundColor='#eff6ff'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='#fff'">
                                    <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b; margin-right: 12px; flex-shrink: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    </div>
                                    <div style="overflow: hidden; flex: 1;">
                                        <div style="font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #1e293b;">{{ $attachment['name'] }}</div>
                                        <div style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase;">{{ $attachment['type'] }} • {{ number_format($attachment['size'] / 1024, 1) }} KB</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Action bar: complete the lesson and move through the path --}}
        <div class="lms-actionbar">
            <div>
                @if($prevUrl)
                    <a href="{{ $prevUrl }}" class="lms-btn lms-btn-muted lms-nav-link" onclick="loadContent(this.href); return false;">
                        <i class="bi bi-arrow-left"></i> Previous
                    </a>
                @endif
            </div>

            <div class="lms-actionbar-right">
                @if(!$lessonCompleted)
                    {{-- data-no-ajax opts this form out of the app-wide form handler; the
                         script below owns submission so it can continue to the next item. --}}
                    <form id="lessonCompleteForm" method="POST" data-no-ajax
                          action="{{ school_route('student.courses.modules.lessons.complete', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}"
                          data-next="{{ $nextUrl ?? '' }}"
                          data-fallback="{{ $myCourseUrl }}">
                        @csrf
                        <button type="submit" class="lms-btn lms-btn-primary">
                            <i class="bi bi-check2-circle"></i> {{ $completeLabel }}
                        </button>
                    </form>
                @endif

                @if($nextUrl)
                    <a href="{{ $nextUrl }}" class="lms-btn {{ $lessonCompleted ? 'lms-btn-primary' : 'lms-btn-muted' }} lms-nav-link" onclick="loadContent(this.href); return false;">
                        {{ $nextLabel }} <i class="bi bi-arrow-right"></i>
                    </a>
                @elseif($lessonCompleted)
                    <a href="{{ $myCourseUrl }}" class="lms-btn lms-btn-primary lms-nav-link" onclick="loadContent(this.href); return false;">Back to My Course</a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // Completing a lesson should move the learner straight on to the next item.
    const form = document.getElementById('lessonCompleteForm');
    if (!form || form.dataset.completeBound === 'true') return;
    form.dataset.completeBound = 'true';

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        e.stopPropagation(); // keep the global AJAX form handler out of the way

        const btn = form.querySelector('button[type="submit"]');
        const original = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = 'Saving...'; }

        const target = form.getAttribute('data-next') || form.getAttribute('data-fallback');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: new FormData(form)
        }).then(function (response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        }).then(function () {
            if (typeof showNotification === 'function') {
                showNotification('Lesson marked as complete.', 'success');
            }
            if (typeof loadContent === 'function') {
                loadContent(target);
            } else {
                window.location.href = target;
            }
        }).catch(function (error) {
            console.error('Complete lesson error:', error);
            if (btn) { btn.disabled = false; btn.innerHTML = original; }
            if (typeof showNotification === 'function') {
                showNotification('Could not mark the lesson complete. Please try again.', 'error');
            }
        });
    });
})();
</script>
@endsection
