@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Edit Module')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" style="max-width: 850px;" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Edit Module</h1>
            <p class="lms-subtitle">Update content setup for {{ $module->title }}.</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('admin.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Cancel</a>
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

        <form method="POST" action="{{ school_route('admin.courses.modules.update', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-form">
            @csrf
            @method('PUT')

            <div class="lms-field">
                <label for="title" class="lms-label">Module Title</label>
                <input id="title" name="title" type="text" value="{{ old('title', $module->title) }}" required class="lms-input">
            </div>

            <div class="lms-field">
                <label for="description" class="lms-label">Description</label>
                <textarea id="description" name="description" rows="4" class="lms-textarea">{{ old('description', $module->description) }}</textarea>
            </div>

            <div class="lms-field">
                <label for="module_type" class="lms-label">Module Type</label>
                <select id="module_type" name="module_type" class="lms-select" required>
                    @foreach(['lesson' => 'Lesson', 'reading' => 'Reading', 'video' => 'Video', 'assessment' => 'Assessment'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('module_type', $module->module_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lms-actions" style="margin-top: 4px;">
                <button type="submit" class="lms-btn lms-btn-primary">Update Module</button>
                <a href="{{ school_route('admin.courses.modules.show', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Back</a>
            </div>
        </form>
        </div>
</div>
@endsection
