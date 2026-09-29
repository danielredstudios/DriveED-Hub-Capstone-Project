@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Create Lesson')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" style="max-width: 900px;" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Create Lesson</h1>
            <p class="lms-subtitle">Module: {{ $module->title ?? 'N/A' }}</p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('instructor.courses.modules.lessons.index', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Cancel</a>
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

        <form method="POST" action="{{ school_route('instructor.courses.modules.lessons.store', ['course' => $course->id, 'module' => $module->id]) }}" enctype="multipart/form-data" class="lms-form">
            @csrf

            <div class="lms-field">
                <label for="title" class="lms-label">Lesson Title</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}" required class="lms-input" placeholder="e.g., LTO Rules and Regulations">
            </div>

            <div class="lms-field">
                <label for="content" class="lms-label">Lesson Content</label>
                <div class="lms-editor">
                    <div class="lms-editor-toolbar" id="lessonContentToolbar">
                        <button type="button" class="lms-editor-btn" data-editor-command="bold"><strong>B</strong></button>
                        <button type="button" class="lms-editor-btn" data-editor-command="italic"><em>I</em></button>
                        <button type="button" class="lms-editor-btn" data-editor-command="underline"><u>U</u></button>
                        <span class="lms-editor-separator" aria-hidden="true"></span>
                        <button type="button" class="lms-editor-btn" data-editor-command="formatBlock" data-editor-value="h2">H2</button>
                        <button type="button" class="lms-editor-btn" data-editor-command="formatBlock" data-editor-value="h3">H3</button>
                        <span class="lms-editor-separator" aria-hidden="true"></span>
                        <button type="button" class="lms-editor-btn" data-editor-command="insertUnorderedList">Bullet</button>
                        <button type="button" class="lms-editor-btn" data-editor-command="insertOrderedList">Numbered</button>
                        <span class="lms-editor-separator" aria-hidden="true"></span>
                        <button type="button" class="lms-editor-btn" data-editor-command="createLink">Link</button>
                        <button type="button" class="lms-editor-btn" data-editor-command="removeFormat">Clear</button>
                    </div>
                    <div id="lessonContentEditor" class="lms-editor-surface" contenteditable="true" data-placeholder="Write lesson notes, objectives, and reading content here."></div>
                </div>
                <textarea id="content" name="content" rows="10" class="lms-textarea" style="display:none;">{{ old('content') }}</textarea>
                <p class="lms-help">Rich text is supported. Unsafe HTML/scripts are removed automatically.</p>
            </div>

            <div class="lms-field">
                <label for="video_url" class="lms-label">Video URL (Optional)</label>
                <input id="video_url" name="video_url" type="url" value="{{ old('video_url') }}" class="lms-input" placeholder="https://...">
            </div>

            <div class="lms-field">
                <label for="attachments" class="lms-label">Attachments (Optional)</label>
                <input id="attachments" name="attachments[]" type="file" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png" class="lms-input">
                <p class="lms-help">Allowed file types: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, JPEG, PNG.</p>
                <p class="lms-help">Maximum size: 10MB per file.</p>
            </div>

            <div class="lms-actions" style="margin-top: 4px;">
                <button type="submit" class="lms-btn lms-btn-primary">Save Lesson</button>
                <a href="{{ school_route('instructor.courses.modules.lessons.index', ['course' => $course->id, 'module' => $module->id]) }}" class="lms-btn lms-btn-muted">Back</a>
            </div>
        </form>
        </div>
</div>

<script>
    (function initLessonContentEditor() {
        const editor = document.getElementById('lessonContentEditor');
        const toolbar = document.getElementById('lessonContentToolbar');
        const textarea = document.getElementById('content');

        if (!editor || !toolbar || !textarea || editor.dataset.editorBound === '1') {
            return;
        }

        editor.dataset.editorBound = '1';
        editor.innerHTML = textarea.value || '';

        const syncEditorToTextarea = () => {
            textarea.value = editor.innerHTML;
        };

        toolbar.addEventListener('click', function (event) {
            const button = event.target.closest('[data-editor-command]');
            if (!button) {
                return;
            }

            event.preventDefault();

            const command = button.getAttribute('data-editor-command');
            const value = button.getAttribute('data-editor-value') || null;

            editor.focus();

            if (command === 'createLink') {
                const url = window.prompt('Enter a link URL (https://...)');
                if (!url) {
                    return;
                }
                document.execCommand('createLink', false, url.trim());
            } else {
                document.execCommand(command, false, value);
            }

            syncEditorToTextarea();
        });

        editor.addEventListener('input', syncEditorToTextarea);
        editor.addEventListener('blur', syncEditorToTextarea);

        const form = editor.closest('form');
        if (form) {
            form.addEventListener('submit', syncEditorToTextarea);
        }
    })();
</script>

@endsection
