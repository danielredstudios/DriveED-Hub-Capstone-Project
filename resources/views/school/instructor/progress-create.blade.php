@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Add Progress')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 28px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        max-width: 600px;
    }

    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #374151; font-size: 0.85rem; }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.88rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus { border-color: {{ $primaryColor }}; }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #f3f4f6;
    }

    .btn-form-primary {
        background: {{ $primaryColor }};
        color: white;
        padding: 10px 22px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .btn-form-primary:hover { background: {{ $secondaryColor }}; }

    .btn-form-secondary {
        background: #f3f4f6;
        color: #374151;
        padding: 10px 22px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .btn-form-secondary:hover { background: #e5e7eb; }
</style>

<div class="admin-container">
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Add Student Progress</h1>
            <p class="page-subtitle">Record progress for a student</p>
        </div>
    </div>

    <div class="form-card">
        <form action="{{ $schoolRoute('instructor.progress.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="student_id">Student</label>
                <select name="student_id" id="student_id" required>
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ ($studentId ?? '') == $student->id ? 'selected' : '' }}>
                            {{ $student->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="course_id">Course</label>
                <select name="course_id" id="course_id" required>
                    <option value="">Select Course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ ($courseId ?? '') == $course->id ? 'selected' : '' }}>
                            {{ $course->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="completion_percent">Completion Percentage</label>
                <input type="number" name="completion_percent" id="completion_percent" 
                       min="0" max="100" value="0" required>
            </div>

            <div class="form-group">
                <label for="notes">Notes (Optional)</label>
                <textarea name="notes" id="notes" rows="4" placeholder="Add any notes about the student's progress..."></textarea>
            </div>

            <div class="form-actions">
                <a href="{{ $schoolRoute('instructor.progress.index') }}" class="btn-form-secondary" onclick="loadContent(this.href); return false;">Cancel</a>
                <button type="submit" class="btn-form-primary">Save Progress</button>
            </div>
        </form>
    </div>
</div>
@endsection
