@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Edit Question')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page"
    @if(isset($course)) data-breadcrumb-course="{{ $course->title }}" @endif
    @if(isset($lesson)) data-breadcrumb-lesson="{{ $lesson->title }}" @endif
>
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Edit Question</h1>
            <p class="lms-subtitle">Update your question in the bank.</p>
        </div>
        <div class="lms-actions">
            <a href="{{ request('return_url', school_route('instructor.questions.index')) }}" class="lms-btn lms-btn-muted">Cancel</a>
        </div>
    </div>

    <div class="lms-card" style="padding: 2rem;">
        <form action="{{ school_route('instructor.questions.update', ['question' => $question->id]) }}" method="POST" id="questionForm">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom: 2rem;">
                <p style="font-weight: 600; color: #444; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 1rem;">Categorization (Optional)</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label class="lms-label">Primary Course</label>
                        <select name="course_id" id="courseSelect" class="lms-input">
                            <option value="">General (No specific course)</option>
                            @foreach($courses as $courseItem)
                                <option value="{{ $courseItem->id }}" {{ $question->course_id == $courseItem->id ? 'selected' : '' }}>{{ $courseItem->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="lms-label">Topic / Lesson</label>
                        <select name="lesson_id" id="lessonSelect" class="lms-input">
                            <option value="">General Topic</option>
                            @foreach($lessons as $lessonItem)
                                <option value="{{ $lessonItem->id }}" {{ $question->lesson_id == $lessonItem->id ? 'selected' : '' }}>{{ $lessonItem->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label class="lms-label">Question Type</label>
                <select name="question_type" id="typeSelect" class="lms-input" required>
                    <option value="multiple_choice" {{ $question->question_type == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                    <option value="true_false" {{ $question->question_type == 'true_false' ? 'selected' : '' }}>True or False</option>
                    <option value="enumeration" {{ $question->question_type == 'enumeration' ? 'selected' : '' }}>Enumeration</option>
                    <option value="identification" {{ $question->question_type == 'identification' ? 'selected' : '' }}>Identification</option>
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label class="lms-label">Question Text</label>
                <textarea name="question_text" class="lms-input" rows="4" required placeholder="Type your question here...">{{ $question->question_text }}</textarea>
            </div>

            <!-- Dynamic Options Section -->
            <div id="optionsContainer" style="margin-bottom: 1.5rem;">
                <!-- Multiple Choice Options -->
                <div id="mcOptions" class="type-specific" style="{{ $question->question_type == 'multiple_choice' ? '' : 'display:none;' }}">
                    <label class="lms-label">Multiple Choice Options</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="lms-input-group">
                            <span class="lms-input-addon">A</span>
                            <input type="text" name="options[A]" class="lms-input" value="{{ $question->options['A'] ?? '' }}" placeholder="Choice A">
                        </div>
                        <div class="lms-input-group">
                            <span class="lms-input-addon">B</span>
                            <input type="text" name="options[B]" class="lms-input" value="{{ $question->options['B'] ?? '' }}" placeholder="Choice B">
                        </div>
                        <div class="lms-input-group">
                            <span class="lms-input-addon">C</span>
                            <input type="text" name="options[C]" class="lms-input" value="{{ $question->options['C'] ?? '' }}" placeholder="Choice C">
                        </div>
                        <div class="lms-input-group">
                            <span class="lms-input-addon">D</span>
                            <input type="text" name="options[D]" class="lms-input" value="{{ $question->options['D'] ?? '' }}" placeholder="Choice D">
                        </div>
                    </div>
                </div>

                <!-- True/False Options -->
                <div id="tfOptions" class="type-specific" style="{{ $question->question_type == 'true_false' ? '' : 'display:none;' }}">
                    <p style="font-size: 0.9rem; color: #666; background: #f8f9fa; padding: 1rem; border-radius: 6px; border: 1px dashed var(--border-color);">
                        <strong>Note:</strong> Options are automatically "True" and "False".
                    </p>
                </div>

                <!-- Enumeration Options -->
                <div id="enumOptions" class="type-specific" style="{{ $question->question_type == 'enumeration' ? '' : 'display:none;' }}">
                    <label class="lms-label">Enumeration — how to encode answers</label>
                    <p style="font-size: 0.9rem; color: #666; background: #f8f9fa; padding: 1rem; border-radius: 6px; border: 1px dashed var(--border-color);">
                        List <strong>all correct items</strong> separated by commas (e.g. <code>Red, Yellow, Green</code>). Partial credit per item; order doesn't matter.
                    </p>
                </div>

                <!-- Identification Options -->
                <div id="identOptions" class="type-specific" style="{{ $question->question_type == 'identification' ? '' : 'display:none;' }}">
                    <label class="lms-label">Identification — how to encode answers</label>
                    <p style="font-size: 0.9rem; color: #666; background: #f8f9fa; padding: 1rem; border-radius: 6px; border: 1px dashed var(--border-color);">
                        Enter the <strong>exact term</strong>. Grading ignores case and extra spaces. Alternatives may use <code>|</code> (e.g. <code>Stop | Halt</code>).
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label class="lms-label">Correct Answer</label>
                <div id="answerInputContainer">
                    @if($question->question_type == 'multiple_choice')
                        <select name="correct_answer" class="lms-input" required>
                            <option value="A" {{ $question->correct_answer == 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ $question->correct_answer == 'B' ? 'selected' : '' }}>B</option>
                            <option value="C" {{ $question->correct_answer == 'C' ? 'selected' : '' }}>C</option>
                            <option value="D" {{ $question->correct_answer == 'D' ? 'selected' : '' }}>D</option>
                        </select>
                    @elseif($question->question_type == 'true_false')
                        <select name="correct_answer" class="lms-input" required>
                            <option value="True" {{ $question->correct_answer == 'True' ? 'selected' : '' }}>True</option>
                            <option value="False" {{ $question->correct_answer == 'False' ? 'selected' : '' }}>False</option>
                        </select>
                    @else
                        <input type="text" name="correct_answer" class="lms-input" value="{{ $question->correct_answer }}" required placeholder="Enter the correct answer">
                    @endif
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <label class="lms-label">Default Points</label>
                <input type="number" name="default_points" class="lms-input" value="{{ $question->default_points }}" min="1" required style="width: 120px;">
            </div>

            <div class="lms-form-actions" style="border-top: 1px solid var(--border-color); padding-top: 2rem;">
                <button type="submit" class="lms-btn lms-btn-primary" style="width: 200px;">Update Question</button>
                <a href="{{ request('return_url', school_route('instructor.questions.index')) }}" class="lms-btn lms-btn-muted">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.lms-input-group {
    display: flex;
    align-items: center;
}
.lms-input-addon {
    background: #f0f0f0;
    border: 1px solid var(--border-color);
    border-right: none;
    padding: 0.75rem 1rem;
    border-radius: 6px 0 0 6px;
    font-weight: bold;
    color: #555;
    min-width: 45px;
    text-align: center;
}
.lms-input-group .lms-input {
    border-radius: 0 6px 6px 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('typeSelect');
    const mcOptions = document.getElementById('mcOptions');
    const tfOptions = document.getElementById('tfOptions');
    const enumOptions = document.getElementById('enumOptions');
    const identOptions = document.getElementById('identOptions');
    const answerInputContainer = document.getElementById('answerInputContainer');
    const courseSelect = document.getElementById('courseSelect');
    const lessonSelect = document.getElementById('lessonSelect');

    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        lessonSelect.innerHTML = '<option value="">Loading lessons...</option>';
        if (!courseId) {
            lessonSelect.innerHTML = '<option value="">No specific lesson</option>';
            return;
        }
        const url = "{{ school_route('instructor.questions.ajax.lessons', ['course' => ':courseId']) }}".replace(':courseId', courseId);
        fetch(url)
            .then(response => response.json())
            .then(data => {
                lessonSelect.innerHTML = '<option value="">No specific lesson</option>';
                data.forEach(lesson => {
                    const option = document.createElement('option');
                    option.value = lesson.id;
                    option.textContent = lesson.title;
                    lessonSelect.appendChild(option);
                });
            });
    });

    typeSelect.addEventListener('change', function() {
        const type = this.value;
        mcOptions.style.display = 'none';
        tfOptions.style.display = 'none';
        enumOptions.style.display = 'none';
        if (identOptions) identOptions.style.display = 'none';
        
        if (type === 'multiple_choice') {
            mcOptions.style.display = 'block';
            answerInputContainer.innerHTML = `
                <select name="correct_answer" class="lms-input" required>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            `;
        } else if (type === 'true_false') {
            tfOptions.style.display = 'block';
            answerInputContainer.innerHTML = `
                <select name="correct_answer" class="lms-input" required>
                    <option value="True">True</option>
                    <option value="False">False</option>
                </select>
            `;
        } else if (type === 'enumeration') {
            enumOptions.style.display = 'block';
            answerInputContainer.innerHTML = `
                <input type="text" name="correct_answer" class="lms-input" required placeholder="Enter correct items separated by comma">
            `;
        } else if (type === 'identification') {
            if (identOptions) identOptions.style.display = 'block';
            answerInputContainer.innerHTML = `
                <input type="text" name="correct_answer" class="lms-input" required placeholder="Exact term (alternatives with |, e.g. Stop | Halt)">
            `;
        }
    });
});
</script>
@endsection
