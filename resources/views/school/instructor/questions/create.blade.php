@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Add Question')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" 
    @if(isset($course)) data-breadcrumb-course="{{ $course->title }}" @endif
    @if(isset($module)) data-breadcrumb-module="{{ $module->title }}" @endif
    @if(isset($module) && $module->module_type === 'assessment') data-breadcrumb-module-type="assessment" @endif
    @if(isset($lesson)) data-breadcrumb-lesson="{{ $lesson->title }}" @endif
>
    <div class="lms-header">
        <div>
            <h1 class="lms-title">Add New Question</h1>
            <p class="lms-subtitle">
                @if(isset($module))
                    Adding to assessment: <strong>{{ $module->title }}</strong>
                @else
                    Create a question for your question bank.
                @endif
            </p>
        </div>
        <div class="lms-actions">
            @if($isAjax)
                <a href="{{ request('return_url', school_route('instructor.questions.index', ['is_selecting' => 1, 'module_id' => $selectedModuleId ?? ''])) }}" class="lms-btn lms-btn-muted">Back to Bank</a>
            @else
                <a href="{{ request('return_url', school_route('instructor.questions.index')) }}" class="lms-btn lms-btn-muted">Cancel</a>
            @endif
        </div>
    </div>

    <div class="lms-card" style="padding: 2rem;">
        <form action="{{ school_route('instructor.questions.store', request()->query()) }}" method="POST" id="questionForm">
            @csrf
            
            <div style="margin-bottom: 2rem;">
                <p style="font-weight: 600; color: #444; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 1rem;">Categorization (Optional)</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label class="lms-label">Primary Course</label>
                        <select name="course_id" id="courseSelect" class="lms-input">
                            <option value="">General (No specific course)</option>
                            @foreach($courses as $courseItem)
                                <option value="{{ $courseItem->id }}" {{ ($selectedCourseId ?? '') == $courseItem->id ? 'selected' : '' }}>{{ $courseItem->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="lms-label">Topic / Lesson</label>
                        <select name="lesson_id" id="lessonSelect" class="lms-input">
                            <option value="">General Topic</option>
                            @foreach($lessons as $lessonItem)
                                <option value="{{ $lessonItem->id }}" {{ ($selectedLessonId ?? '') == $lessonItem->id ? 'selected' : '' }}>{{ $lessonItem->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label class="lms-label">Question Type</label>
                <select name="question_type" id="typeSelect" class="lms-input" required>
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="true_false">True or False</option>
                    <option value="enumeration">Enumeration</option>
                    <option value="identification">Identification</option>
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label class="lms-label">Question Text</label>
                <textarea name="question_text" class="lms-input" rows="4" required placeholder="Type your question here..."></textarea>
            </div>

            <!-- Dynamic Options Section -->
            <div id="optionsContainer" style="margin-bottom: 1.5rem;">
                <!-- Multiple Choice Options -->
                <div id="mcOptions" class="type-specific">
                    <label class="lms-label">Multiple Choice Options</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="lms-input-group">
                            <span class="lms-input-addon">A</span>
                            <input type="text" name="options[A]" class="lms-input" placeholder="Choice A">
                        </div>
                        <div class="lms-input-group">
                            <span class="lms-input-addon">B</span>
                            <input type="text" name="options[B]" class="lms-input" placeholder="Choice B">
                        </div>
                        <div class="lms-input-group">
                            <span class="lms-input-addon">C</span>
                            <input type="text" name="options[C]" class="lms-input" placeholder="Choice C">
                        </div>
                        <div class="lms-input-group">
                            <span class="lms-input-addon">D</span>
                            <input type="text" name="options[D]" class="lms-input" placeholder="Choice D">
                        </div>
                    </div>
                </div>

                <!-- True/False Options -->
                <div id="tfOptions" class="type-specific" style="display:none;">
                    <p style="font-size: 0.9rem; color: #666; background: #f8f9fa; padding: 1rem; border-radius: 6px; border: 1px dashed var(--border-color);">
                        <strong>Note:</strong> Options will automatically be set as "True" and "False".
                    </p>
                </div>

                <!-- Enumeration Options -->
                <div id="enumOptions" class="type-specific" style="display:none;">
                    <label class="lms-label">Enumeration — how to encode answers</label>
                    <p style="font-size: 0.9rem; color: #666; background: #f8f9fa; padding: 1rem; border-radius: 6px; border: 1px dashed var(--border-color);">
                        List <strong>all correct items</strong> in the "Correct Answer" field below, <strong>separated by commas</strong>
                        (e.g. <code>Red, Yellow, Green</code>). Students get one input box per item and earn partial credit per correct item (order doesn't matter).
                    </p>
                </div>

                <!-- Identification Options -->
                <div id="identOptions" class="type-specific" style="display:none;">
                    <label class="lms-label">Identification — how to encode answers</label>
                    <p style="font-size: 0.9rem; color: #666; background: #f8f9fa; padding: 1rem; border-radius: 6px; border: 1px dashed var(--border-color);">
                        Enter the <strong>exact term</strong> in the "Correct Answer" field below. Grading ignores case and extra spaces.
                        For accepted alternatives, separate them with <code>|</code> (e.g. <code>Stop | Halt</code>).
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label class="lms-label">Correct Answer</label>
                <div id="answerInputContainer">
                    <!-- Default for Multiple Choice -->
                    <select name="correct_answer" class="lms-input" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <label class="lms-label">Default Points</label>
                <input type="number" name="default_points" class="lms-input" value="1" min="1" required style="width: 120px;">
            </div>

            @if(isset($selectedModuleId))
                <input type="hidden" name="module_id" value="{{ $selectedModuleId }}">
            @endif

            <div class="lms-form-actions" style="border-top: 1px solid var(--border-color); padding-top: 2rem;">
                <button type="submit" class="lms-btn lms-btn-primary" style="width: 240px;">
                    {{ isset($selectedModuleId) ? 'Save and Add to Quiz' : 'Save to Bank' }}
                </button>
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
.lms-input.lms-input-error {
    border-color: #dc3545 !important;
    background-color: #fff8f8 !important;
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
    const questionForm = document.getElementById('questionForm');
    const courseSelect = document.getElementById('courseSelect');
    const lessonSelect = document.getElementById('lessonSelect');

    // Handle course change to fetch lessons via AJAX
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
            })
            .catch(err => {
                console.error('Error fetching lessons:', err);
                lessonSelect.innerHTML = '<option value="">Error loading lessons</option>';
            });
    });

    // Handle type change to swap option fields and answer inputs
    typeSelect.addEventListener('change', function() {
        const type = this.value;
        
        // Hide all type-specific blocks
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
                <input type="text" name="correct_answer" class="lms-input" required placeholder="Enter correct items separated by comma (e.g. Red, Yellow, Green)">
            `;
        } else if (type === 'identification') {
            if (identOptions) identOptions.style.display = 'block';
            answerInputContainer.innerHTML = `
                <input type="text" name="correct_answer" class="lms-input" required placeholder="Exact term (alternatives with |, e.g. Stop | Halt)">
                <p style="font-size:0.8rem;color:#666;margin-top:6px;">Grading ignores case and extra spaces.</p>
            `;
        }
    });

    // Client-side guard matching server rules (identification / enumeration)
    if (questionForm) {
        questionForm.addEventListener('submit', function(e) {
            const type = typeSelect.value;
            const answerEl = answerInputContainer.querySelector('[name="correct_answer"]');
            const answer = (answerEl?.value || '').trim();
            answerEl?.classList.remove('lms-input-error');
            if (type === 'enumeration') {
                const items = answer.split(',').map(s => s.trim()).filter(Boolean);
                if (items.length < 2) {
                    e.preventDefault();
                    answerEl?.classList.add('lms-input-error');
                    alert('Enumeration needs at least 2 answers separated by commas (e.g. Red, Yellow, Green).');
                }
            } else if (type === 'identification') {
                if (!answer) {
                    e.preventDefault();
                    answerEl?.classList.add('lms-input-error');
                    alert('Identification needs the exact term (use | for alternatives, e.g. Stop | Halt).');
                }
            } else if (type === 'multiple_choice') {
                const filled = ['A','B','C','D'].filter(k => {
                    const el = questionForm.querySelector(`[name="options[${k}]"]`);
                    return el && el.value.trim() !== '';
                });
                if (filled.length < 2) {
                    e.preventDefault();
                    alert('Multiple choice needs at least 2 filled choices (A–D).');
                } else if (answerEl && !filled.includes(answerEl.value)) {
                    e.preventDefault();
                    alert('Correct answer must be one of the filled choices (A–D).');
                }
            }
        });
    }
});
</script>
@endsection
