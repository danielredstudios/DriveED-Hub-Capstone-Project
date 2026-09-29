@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Log New Session')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $useGradient = $settings?->use_gradient_header ?? true;
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .session-form-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 4px solid {{ $primaryColor }};
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .page-title {
        font-size: 2rem;
        color: #111827;
        margin: 0 0 4px 0;
        font-weight: 400;
    }

    .page-subtitle {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        color: #374151;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-back:hover {
        border-color: {{ $primaryColor }};
        color: {{ $primaryColor }};
    }

    /* Error Alert */
    .error-alert {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-left: 4px solid #ef4444;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 24px;
    }

    .error-alert h4 {
        color: #991b1b;
        font-size: 0.95rem;
        margin: 0 0 8px 0;
    }

    .error-alert ul {
        margin: 0;
        padding-left: 20px;
    }

    .error-alert li {
        color: #991b1b;
        font-size: 0.875rem;
        margin-bottom: 4px;
    }

    /* Form Layout */
    .form-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        align-items: start;
    }

    /* Card Styling */
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f3f4f6;
    }

    .card-header h2 {
        font-size: 1.15rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .card-body {
        padding: 24px;
    }

    /* Form Fields */
    .form-group {
        margin-bottom: 24px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-label .required {
        color: #ef4444;
    }

    .form-hint {
        font-size: 0.8rem;
        color: #9ca3af;
        margin-top: 4px;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #374151;
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
        box-sizing: border-box;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px {{ $primaryColor }}22;
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* Enrollment Info Box */
    .enrollment-info {
        display: none;
        background: linear-gradient(135deg, {{ $primaryColor }}08 0%, {{ $primaryColor }}12 100%);
        border: 1px solid {{ $primaryColor }}30;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 24px;
    }

    .enrollment-info.visible {
        display: block;
    }

    .enrollment-info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .enrollment-info-item .info-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .enrollment-info-item .info-value {
        font-size: 1rem;
        font-weight: 700;
        color: {{ $primaryColor }};
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid #f3f4f6;
        margin-top: 24px;
    }

    .btn-cancel {
        padding: 10px 24px;
        background: #f3f4f6;
        color: #374151;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-submit {
        padding: 10px 24px;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Guidelines Card */
    .guidelines-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        position: sticky;
        top: 20px;
    }

    .guidelines-card .card-header {
        background: #f9fafb;
    }

    .guidelines-card .card-header h2 {
        font-size: 1rem;
    }

    .guidelines-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .guidelines-list li {
        padding: 10px 0;
        font-size: 0.85rem;
        color: #6b7280;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        border-bottom: 1px solid #f3f4f6;
    }

    .guidelines-list li:last-child {
        border-bottom: none;
    }

    .guidelines-list li .icon {
        color: {{ $primaryColor }};
        flex-shrink: 0;
        margin-top: 1px;
    }

    .icon-16 {
        width: 16px;
        height: 16px;
    }

    .icon-18 {
        width: 18px;
        height: 18px;
    }

    .icon-inline-start {
        vertical-align: middle;
        margin-right: 6px;
    }

    @media (max-width: 900px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .enrollment-info-grid {
            grid-template-columns: 1fr;
        }

        .guidelines-card {
            position: static;
        }
    }
</style>

<div class="session-form-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Log New Session</h1>
            <p class="page-subtitle">Record a completed training session</p>
        </div>
        <a href="{{ route('schools.instructor.sessions.index', $school) }}" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Sessions
        </a>
    </div>

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="error-alert">
            <h4>Please fix the following errors:</h4>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-grid">
        <!-- Main Form Card -->
        <div class="form-card">
            <div class="card-header">
                <h2>Session Details</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('schools.instructor.sessions.store', $school) }}" method="POST" id="sessionForm">
                    @csrf

                    <!-- Enrollment Selection -->
                    <div class="form-group">
                        <label class="form-label">Student Enrollment <span class="required">*</span></label>
                        <select name="enrollment_id" id="enrollment_id" class="form-select" required>
                            <option value="">Select a student enrollment...</option>
                            @foreach($enrollments as $enrollment)
                                <option value="{{ $enrollment->id }}" 
                                        data-course-type="{{ $enrollment->course->course_type }}"
                                        data-hours-required="{{ $enrollment->course->hours_required }}"
                                        data-hours-completed="{{ $enrollment->total_hours }}"
                                        {{ old('enrollment_id') == $enrollment->id || (isset($selectedEnrollment) && $selectedEnrollment->id == $enrollment->id) ? 'selected' : '' }}>
                                    {{ $enrollment->learner->name ?? $enrollment->student->name ?? 'N/A' }} — {{ $enrollment->course->title }}
                                    ({{ number_format($enrollment->total_hours, 1) }}/{{ number_format($enrollment->course->hours_required, 1) }} hrs)
                                </option>
                            @endforeach
                        </select>
                        <div class="form-hint">Select the student and course for this session</div>
                    </div>

                    <!-- Enrollment Info Display -->
                    <div id="enrollmentInfo" class="enrollment-info">
                        <div class="enrollment-info-grid">
                            <div class="enrollment-info-item">
                                <div class="info-label">Course Type</div>
                                <div class="info-value" id="courseType">—</div>
                            </div>
                            <div class="enrollment-info-item">
                                <div class="info-label">Hours Completed</div>
                                <div class="info-value" id="hoursCompleted">—</div>
                            </div>
                            <div class="enrollment-info-item">
                                <div class="info-label">Hours Remaining</div>
                                <div class="info-value" id="hoursRemaining">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Session Type -->
                    <div class="form-group">
                        <label class="form-label">Session Type <span class="required">*</span></label>
                        <select name="session_type" id="session_type" class="form-select" required>
                            <option value="">Select session type...</option>
                            <option value="theoretical" {{ old('session_type') == 'theoretical' ? 'selected' : '' }}>Theoretical</option>
                            <option value="practical" {{ old('session_type') == 'practical' ? 'selected' : '' }}>Practical</option>
                        </select>
                        <div class="form-hint" id="sessionTypeHint">Must match the enrolled course type</div>
                    </div>

                    <!-- Date and Time Row -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Session Date <span class="required">*</span></label>
                            <input type="date" name="session_date" id="session_date" 
                                   class="form-input" 
                                   value="{{ old('session_date', date('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Session Time <span class="required">*</span></label>
                            <input type="time" name="session_time" id="session_time" 
                                   class="form-input" 
                                   value="{{ old('session_time', date('H:i')) }}" required>
                        </div>
                    </div>

                    <!-- Hours Completed -->
                    <div class="form-group">
                        <label class="form-label">Hours Completed <span class="required">*</span></label>
                        <input type="number" name="hours_completed" id="hours_completed" 
                               class="form-input" 
                               value="{{ old('hours_completed', '1.0') }}"
                               min="0.5" max="8" step="0.5" required>
                        <div class="form-hint">Enter duration in hours (0.5 to 8 hours, increment by 0.5)</div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label class="form-label">Session Notes</label>
                        <textarea name="notes" id="notes" class="form-textarea" rows="4" 
                                  placeholder="Add notes about this session (topics covered, student performance, areas for improvement, etc.)">{{ old('notes') }}</textarea>
                        <div class="form-hint">Optional: Document session highlights, student progress, or recommendations</div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="{{ route('schools.instructor.sessions.index', $school) }}" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-submit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18 icon-inline-start">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Log Session
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Guidelines Sidebar -->
        <div class="guidelines-card">
            <div class="card-header">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18 icon-inline-start">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Session Guidelines
                </h2>
            </div>
            <div class="card-body">
                <ul class="guidelines-list">
                    <li>
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        Sessions can only be logged for active enrollments
                    </li>
                    <li>
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        Session type must match the enrolled course type
                    </li>
                    <li>
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        Session date cannot be in the future
                    </li>
                    <li>
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        Minimum duration: 0.5 hours (30 minutes)
                    </li>
                    <li>
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        Maximum duration: 8 hours per session
                    </li>
                    <li>
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        Students must complete required hours before being marked as passed
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const enrollmentSelect = document.getElementById('enrollment_id');
    const enrollmentInfo = document.getElementById('enrollmentInfo');
    const sessionTypeSelect = document.getElementById('session_type');
    const sessionTypeHint = document.getElementById('sessionTypeHint');

    function formatCourseType(courseType) {
        return courseType ? courseType.charAt(0).toUpperCase() + courseType.slice(1) : '';
    }

    function updateSessionTypeHint(courseType, selectedLabel) {
        if (!sessionTypeHint) {
            return;
        }

        if (!courseType) {
            sessionTypeHint.textContent = 'Must match the enrolled course type';
            return;
        }

        const selectedSessionType = sessionTypeSelect.value;
        const readableType = formatCourseType(courseType);
        const enrollmentLabel = selectedLabel ? selectedLabel.split(' - ')[0].trim() : 'selected enrollment';

        if (selectedSessionType && selectedSessionType !== courseType) {
            sessionTypeHint.textContent = enrollmentLabel + ' requires ' + readableType + ' sessions.';
            return;
        }

        sessionTypeHint.textContent = enrollmentLabel + ' requires ' + readableType + ' sessions.';
    }
    
    function updateEnrollmentInfo() {
        const selected = enrollmentSelect.selectedOptions[0];
        
        if (selected && selected.value) {
            const courseType = selected.getAttribute('data-course-type');
            const hoursRequired = parseFloat(selected.getAttribute('data-hours-required'));
            const hoursCompleted = parseFloat(selected.getAttribute('data-hours-completed'));
            const hoursRemaining = Math.max(0, hoursRequired - hoursCompleted);
            
            document.getElementById('courseType').textContent = formatCourseType(courseType) || '—';
            document.getElementById('hoursCompleted').textContent = hoursCompleted.toFixed(1) + ' hrs';
            document.getElementById('hoursRemaining').textContent = hoursRemaining.toFixed(1) + ' hrs';
            
            // Auto-select matching session type
            if (courseType) {
                sessionTypeSelect.value = courseType;
            }

            updateSessionTypeHint(courseType, selected.textContent);
            
            enrollmentInfo.classList.add('visible');
        } else {
            enrollmentInfo.classList.remove('visible');
            sessionTypeSelect.value = '';
            updateSessionTypeHint('', '');
        }
    }
    
    enrollmentSelect.addEventListener('change', updateEnrollmentInfo);
    sessionTypeSelect.addEventListener('change', function() {
        const selected = enrollmentSelect.selectedOptions[0];
        const courseType = selected ? selected.getAttribute('data-course-type') : '';
        const selectedLabel = selected ? selected.textContent : '';
        updateSessionTypeHint(courseType, selectedLabel);
    });
    
    // Initialize on page load
    if (enrollmentSelect.value) {
        updateEnrollmentInfo();
    } else {
        updateSessionTypeHint('', '');
    }
});
</script>
@endsection
