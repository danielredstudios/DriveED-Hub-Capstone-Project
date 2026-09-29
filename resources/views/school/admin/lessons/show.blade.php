@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $lesson->title ?? 'Lesson Details')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}" data-breadcrumb-lesson="{{ $lesson->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">{{ $lesson->title ?? 'Lesson' }}</h1>
            <p class="lms-subtitle">Module: {{ $module->title ?? 'N/A' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('admin.courses.modules.lessons.index', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Back to Lessons</a>
            <a href="{{ school_route('admin.courses.modules.lessons.edit', ['course' => $course->id, 'module' => $module->id, 'lesson' => $lesson->id]) }}" class="lms-btn lms-btn-warn">Edit Lesson</a>
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
                    <p class="lms-inline-note">No lesson content provided.</p>
                @endif
            </div>

            @if(!empty($lesson->video_url))
                <div class="lms-section">
                    <h3 class="lms-section-title">Video Resource</h3>
                    <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener" class="lms-link lms-link-view">Open Video</a>
                </div>
            @endif

            <div class="lms-section">
                <h3 class="lms-section-title">Attachments</h3>
                @if(!empty($lesson->attachments) && is_array($lesson->attachments))
                    <ul class="lms-attachments">
                        @foreach($lesson->attachments as $attachment)
                            <li>{{ $attachment['name'] ?? 'Attachment' }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="lms-inline-note">No attachments uploaded.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
