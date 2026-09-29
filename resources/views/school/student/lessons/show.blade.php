@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $lesson->title ?? 'Lesson Details')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" style="max-width: 950px;" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}" data-breadcrumb-lesson="{{ $lesson->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">{{ $lesson->title ?? 'Lesson' }}</h1>
            <p class="lms-subtitle">Module: {{ $module->title ?? 'N/A' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('student.my-course') }}" class="lms-btn lms-btn-muted">Back to My Course</a>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Lesson Content</h2>
            <span class="lms-chip">Lesson {{ $lesson->sort_order ?? '-' }}</span>
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

            <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                @if($lessonCompleted)
                    <span class="lms-chip" style="background: #dcfce7; color: #166534;">Completed</span>
                @else
                    <form method="POST" action="{{ school_route('student.courses.modules.lessons.complete', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}">
                        @csrf
                        <button type="submit" class="lms-btn lms-btn-primary">Mark Lesson Complete</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Learning Path Navigation --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
        @if(isset($navigation->prev))
            <a href="{{ $navigation->prev['url'] }}" class="lms-btn lms-btn-muted" style="display: flex; align-items: center; gap: 0.75rem; max-width: 45%;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                <div style="text-align: left; overflow: hidden;">
                    <div style="font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Previous</div>
                    <div style="font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $navigation->prev['title'] }}</div>
                </div>
            </a>
        @else
            <div></div>
        @endif

        @if(isset($navigation->next))
            <a href="{{ $navigation->next['url'] }}" class="lms-btn lms-btn-primary" style="display: flex; align-items: center; gap: 0.75rem; max-width: 45%;">
                <div style="text-align: right; overflow: hidden;">
                    <div style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.7;">Next</div>
                    <div style="font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $navigation->next['title'] }}</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        @endif
    </div>
</div>
@endsection
