@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Create Module')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" style="max-width: 850px;" data-breadcrumb-course="{{ $course->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Create Module</h1>
            <p class="lms-subtitle">Add a new module for {{ $course->title ?? 'this course' }}.</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('instructor.courses.modules.index', ['course' => $course->id]) }}" class="lms-btn lms-btn-muted">Cancel</a>
        </div>
    </div>

    <div class="lms-form-wrap">
        @if($errors->any())
            <div class="lms-errors">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ school_route('instructor.courses.modules.store', ['course' => $course->id]) }}" class="lms-form">
            @csrf

            <div class="lms-field">
                <label for="title" class="lms-label">Module Title</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}" required class="lms-input" placeholder="e.g., Road Signs and Markings">
            </div>

            <div class="lms-field">
                <label for="description" class="lms-label">Description</label>
                <textarea id="description" name="description" rows="4" class="lms-textarea" placeholder="Brief summary of what students will learn.">{{ old('description') }}</textarea>
            </div>

            <div class="lms-field">
                <label for="module_type" class="lms-label">Module Type</label>
                <select id="module_type" name="module_type" class="lms-select" required>
                    <option value="lesson" @selected(old('module_type') === 'lesson')>Lesson</option>
                    <option value="reading" @selected(old('module_type') === 'reading')>Reading</option>
                    <option value="video" @selected(old('module_type') === 'video')>Video</option>
                    <option value="assessment" @selected(old('module_type') === 'assessment')>Assessment</option>
                </select>
                <p class="lms-help">This helps categorize learning materials in the student view.</p>
            </div>

            <div class="lms-actions" style="margin-top: 4px;">
                <button type="submit" class="lms-btn lms-btn-primary">Save Module</button>
                <a href="{{ school_route('instructor.courses.modules.index', ['course' => $course->id]) }}" class="lms-btn lms-btn-muted">Back</a>
            </div>
        </form>
        </div>
</div>
@endsection
