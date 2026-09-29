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
            <a href="{{ school_route('instructor.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Back to Module</a>
            <a href="{{ school_route('instructor.courses.modules.lessons.create', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-primary">Create Lesson</a>
            <a href="{{ school_route('instructor.courses.modules.lessons.edit', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-btn lms-btn-warn">Edit Lesson</a>
            <form method="POST" action="{{ school_route('instructor.courses.modules.lessons.destroy', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" onsubmit="return confirm('Delete this lesson? This action cannot be undone.');" style="display: inline-flex;">
                @csrf
                @method('DELETE')
                <button type="submit" class="lms-btn" style="background: #ef4444; color: #ffffff;">Delete Lesson</button>
            </form>
        </div>
    </div>

    <div class="lms-card">
        <div class="lms-card-head">
            <h2 class="lms-card-title">Lesson Content</h2>
            <span class="lms-chip">Sort #{{ $lesson->sort_order ?? '-' }}</span>
        </div>

        <div style="padding: 18px;">
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
                <div class="lms-section" style="margin-bottom: 2rem;">
                    <h3 class="lms-section-title" style="margin-bottom: 0.75rem;">Video Resource</h3>
                    <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener" class="lms-link lms-link-view" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; font-weight: 600;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Open Video
                    </a>
                </div>
            @endif

            <div class="lms-section">
                <h3 class="lms-section-title" style="margin-bottom: 1rem;">Attachments & Resources</h3>
                @if(!empty($lesson->attachments) && is_array($lesson->attachments))
                    <div style="display: grid; grid-template-cols: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                        @foreach($lesson->attachments as $attachment)
                            <a href="{{ asset('storage/' . $attachment['path']) }}" target="_blank" style="display: flex; align-items: center; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: inherit; background: #fff; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.backgroundColor='#eff6ff'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='#fff'">
                                <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-center; color: #64748b; margin-right: 12px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px; margin: auto;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                </div>
                                <div style="overflow: hidden; flex: 1;">
                                    <div style="font-size: 0.8rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $attachment['name'] }}</div>
                                    <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">{{ $attachment['type'] }} • {{ number_format($attachment['size'] / 1024, 1) }} KB</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="lms-inline-note">No attachments uploaded for this lesson.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
