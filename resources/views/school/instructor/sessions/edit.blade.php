@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('page-title', 'Edit Session - Instructor')

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
    .session-edit-container {
        padding: 20px;
        max-width: 800px;
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

    /* Card Styling */
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .card-body {
        padding: 24px;
    }

    /* Student Info Box */
    .student-info-box {
        background: linear-gradient(135deg, {{ $primaryColor }}08 0%, {{ $primaryColor }}12 100%);
        border: 1px solid {{ $primaryColor }}30;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .student-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        @if($useGradient)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .student-details strong {
        display: block;
        color: #111827;
        font-size: 1rem;
        margin-bottom: 2px;
    }

    .student-details .course-name {
        font-size: 0.85rem;
        color: #6b7280;
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

    .field-error {
        font-size: 0.8rem;
        color: #ef4444;
        margin-top: 4px;
    }

    .form-input,
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
    .form-textarea:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px {{ $primaryColor }}22;
    }

    .form-input.has-error,
    .form-textarea.has-error {
        border-color: #ef4444;
    }

    .form-input[readonly] {
        background: #f9fafb;
        color: #6b7280;
        cursor: not-allowed;
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

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
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
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .icon-18 {
        width: 18px;
        height: 18px;
    }

    .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column-reverse;
            gap: 12px;
        }

        .form-actions .btn-cancel,
        .form-actions .btn-submit {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }
</style>

<div class="session-edit-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Session</h1>
            <p class="page-subtitle">Update session details</p>
        </div>
        <a href="{{ route('schools.instructor.sessions.index', $school) }}" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Sessions
        </a>
    </div>

    <div class="form-card">
        <div class="card-body">
            <form action="{{ route('schools.instructor.sessions.update', ['school' => $school->slug, 'sessionCompletion' => $sessionCompletion->id]) }}" 
                  method="POST">
                @csrf
                @method('PATCH')

                <!-- Student Info (Read-only) -->
                <div class="student-info-box">
                    <div class="student-avatar">
                        {{ strtoupper(substr($sessionCompletion->enrollment->student->name, 0, 1)) }}
                    </div>
                    <div class="student-details">
                        <strong>{{ $sessionCompletion->enrollment->student->name }}</strong>
                        <span class="course-name">{{ $sessionCompletion->enrollment->course->title ?? $sessionCompletion->enrollment->course->course_name ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Session Type (Read-only) -->
                <div class="form-group">
                    <label class="form-label">Session Type</label>
                    <input type="text" class="form-input" 
                           value="{{ ucfirst($sessionCompletion->session_type) }}" 
                           readonly>
                </div>

                <!-- Date and Time Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="session_date" class="form-label">Session Date <span class="required">*</span></label>
                        <input type="date" 
                               class="form-input {{ $errors->has('session_date') ? 'has-error' : '' }}" 
                               id="session_date" 
                               name="session_date" 
                               value="{{ old('session_date', $sessionCompletion->session_date->format('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}"
                               required>
                        @error('session_date')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="session_time" class="form-label">Session Time <span class="required">*</span></label>
                        <input type="time" 
                               class="form-input {{ $errors->has('session_time') ? 'has-error' : '' }}" 
                               id="session_time" 
                               name="session_time" 
                               value="{{ old('session_time', $sessionCompletion->session_time) }}"
                               required>
                        @error('session_time')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Hours Completed -->
                <div class="form-group">
                    <label for="hours_completed" class="form-label">Hours Completed <span class="required">*</span></label>
                    <input type="number" 
                           class="form-input {{ $errors->has('hours_completed') ? 'has-error' : '' }}" 
                           id="hours_completed" 
                           name="hours_completed" 
                           value="{{ old('hours_completed', $sessionCompletion->hours_completed) }}"
                           step="0.5"
                           min="0.5"
                           max="8"
                           required>
                    @error('hours_completed')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Enter hours in 0.5 increments (e.g., 1.5, 2, 2.5)</div>
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-textarea {{ $errors->has('notes') ? 'has-error' : '' }}" 
                              id="notes" 
                              name="notes" 
                              rows="4" 
                              placeholder="Add any notes about this session...">{{ old('notes', $sessionCompletion->notes) }}</textarea>
                    @error('notes')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('schools.instructor.sessions.index', $school) }}" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
