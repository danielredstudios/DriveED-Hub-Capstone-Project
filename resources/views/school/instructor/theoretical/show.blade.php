@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Review Theoretical Completion')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';

    // ...existing code...
    
    $totalHours = $enrollment->sessionCompletions->sum('hours_completed');
    $requiredHours = $enrollment->course->theoretical_hours ?? 15;
    $progress = $requiredHours > 0 ? min(100, round(($totalHours / $requiredHours) * 100)) : 0;
    $progressClass = $progress >= 100 ? 'high' : ($progress >= 50 ? 'mid' : 'low');
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .theoretical-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1400px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid {{ $primaryColor }};
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }

    .back-link:hover {
        color: {{ $primaryColor }};
        border-color: {{ $primaryColor }};
        background: rgba(102, 126, 234, 0.05);
    }

    /* Two-column layout */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 20px;
    }

    /* Info card */
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .card-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 18px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-title svg {
        flex-shrink: 0;
    }

    .info-row {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid #f9fafb;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 500;
        color: #6b7280;
        min-width: 130px;
        font-size: 0.9rem;
    }

    .info-value {
        color: #1f2937;
        font-size: 0.9rem;
    }

    .info-value.bold {
        font-weight: 600;
    }

    /* Badges */
    .badge-custom {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-secondary {
        background: #e5e7eb;
        color: #4b5563;
    }

    .badge-primary {
        background: linear-gradient(135deg, {{ $primaryColor }}22 0%, {{ $primaryColor }}33 100%);
        color: {{ $primaryColor }};
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    /* Progress stats grid */
    .progress-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }

    .progress-stat-item {
        text-align: center;
        padding: 12px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .progress-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }

    .progress-stat-value.high { color: #10b981; }
    .progress-stat-value.mid { color: #f59e0b; }
    .progress-stat-value.low { color: #ef4444; }

    .progress-stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 4px;
    }

    /* Progress bar */
    .progress-bar-container {
        height: 28px;
        background: #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        position: relative;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8rem;
        font-weight: 700;
        min-width: 40px;
        transition: width 0.5s ease;
    }

    .progress-bar-fill.high {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    .progress-bar-fill.mid {
        background: linear-gradient(90deg, #f59e0b 0%, #eab308 100%);
    }

    .progress-bar-fill.low {
        background: linear-gradient(90deg, #ef4444 0%, #f97316 100%);
    }

    .icon-size-16 {
        width: 16px;
        height: 16px;
    }

    .icon-size-18 {
        width: 18px;
        height: 18px;
    }

    /* Session table */
    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
    }

    th {
        padding: 12px 14px;
        text-align: left;
        font-weight: 600;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }

    td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.88rem;
    }

    tbody tr:hover {
        background-color: #f8fafc;
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    /* Alert boxes */
    .alert-box {
        padding: 14px 18px;
        border-radius: 10px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 16px;
        font-size: 0.9rem;
    }

    .alert-box.success {
        background: #f0fdf4;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .alert-box.warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }

    .alert-box svg {
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* Form controls */
    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #1f2937;
        resize: vertical;
        font-family: inherit;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }

    .form-textarea:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px {{ $primaryColor }}22;
    }

    .form-textarea::placeholder {
        color: #9ca3af;
    }

    .btn-submit {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-submit.success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-submit.success:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
    }

    /* Empty state */
    .empty-text {
        text-align: center;
        padding: 24px;
        color: #9ca3af;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .theoretical-container { padding: 15px; }
        .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        .content-grid { grid-template-columns: 1fr; }
        .progress-stats { grid-template-columns: repeat(3, 1fr); }
        table { min-width: 500px; }
    }
</style>

<div class="theoretical-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Review Theoretical Completion</h1>
            <p class="page-subtitle">{{ $enrollment->student->name }} — {{ $enrollment->course->title ?? 'N/A' }}</p>
        </div>
        <a href="{{ school_route('instructor.theoretical.index') }}" class="back-link">
            <svg class="icon-size-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Theoretical
        </a>
    </div>



    <div class="content-grid">
        <!-- Left Column -->
        <div>
            <!-- Student Info -->
            <div class="info-card">
                <h5 class="card-title">
                    <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $primaryColor }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Student Information
                </h5>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value bold">{{ $enrollment->student->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $enrollment->student->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Student Type:</span>
                    <span class="info-value">
                        <span class="badge-custom badge-info">{{ ucfirst(str_replace('_', ' ', $enrollment->student->student_type)) }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">License Type:</span>
                    <span class="info-value">
                        <span class="badge-custom badge-secondary">{{ ucfirst(str_replace('_', ' ', $enrollment->student->license_type)) }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Enrolled:</span>
                    <span class="info-value">{{ $enrollment->enrolled_at->format('M d, Y') }}</span>
                </div>
            </div>

            <!-- Course Info -->
            <div class="info-card">
                <h5 class="card-title">
                    <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $primaryColor }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Course Details
                </h5>
                <div class="info-row">
                    <span class="info-label">Course:</span>
                    <span class="info-value bold">{{ $enrollment->course->title ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">
                        <span class="badge-custom badge-primary">{{ ucfirst($enrollment->course->course_type) }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">License:</span>
                    <span class="info-value">
                        <span class="badge-custom badge-secondary">{{ ucfirst(str_replace('_', ' ', $enrollment->course->license_type)) }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Required Hours:</span>
                    <span class="info-value bold">{{ $requiredHours }} hours</span>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Progress -->
            <div class="info-card">
                <h5 class="card-title">
                    <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $primaryColor }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Training Progress
                </h5>
                <div class="progress-stats">
                    <div class="progress-stat-item">
                        <div class="progress-stat-value">{{ $totalHours }}</div>
                        <div class="progress-stat-label">Hours Completed</div>
                    </div>
                    <div class="progress-stat-item">
                        <div class="progress-stat-value">{{ $requiredHours }}</div>
                        <div class="progress-stat-label">Required Hours</div>
                    </div>
                    <div class="progress-stat-item">
                        <div class="progress-stat-value {{ $progressClass }}">{{ $progress }}%</div>
                        <div class="progress-stat-label">Progress</div>
                    </div>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill {{ $progressClass }}" data-progress="{{ max($progress, 5) }}">
                        {{ $progress }}%
                    </div>
                </div>
            </div>

            <!-- Session History -->
            <div class="info-card">
                <h5 class="card-title">
                    <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $primaryColor }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Session History
                </h5>
                @if($enrollment->sessionCompletions->count() > 0)
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Instructor</th>
                                    <th>Hours</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollment->sessionCompletions->sortByDesc('session_date') as $session)
                                    <tr>
                                        <td>{{ $session->session_date->format('M d, Y') }}</td>
                                        <td>{{ $session->session_time }}</td>
                                        <td>{{ $session->instructor->name }}</td>
                                        <td><span class="badge-custom badge-success">{{ $session->hours_completed }}h</span></td>
                                        <td><span class="badge-custom badge-info">{{ ucfirst($session->session_type) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="empty-text">No sessions recorded yet</p>
                @endif
            </div>

            <!-- Mark as Passed -->
            <div class="info-card">
                <h5 class="card-title">
                    @if($validation['allowed'])
                        <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#10b981">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#f59e0b">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.832c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    @endif
                    Mark as Passed
                </h5>
                @if($validation['allowed'])
                    <div class="alert-box success">
                        <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $validation['message'] }}</span>
                    </div>
                    <form action="{{ school_route('instructor.theoretical.markAsPassed') }}"
                          method="POST">
                        @csrf
                        <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">

                        <div class="form-group">
                            <label>Notes (Optional)</label>
                            <textarea name="notes" class="form-textarea" rows="3"
                                      placeholder="Add any additional notes..."></textarea>
                        </div>

                        <button type="button" class="btn-submit success" onclick="showConfirm({title:'Confirm Action',message:'Are you sure you want to mark this student as passed theoretical training?',type:'success',onConfirm:()=>this.closest('form').submit()})">
                            <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Mark as Passed Theoretical
                        </button>
                    </form>
                @else
                    <div class="alert-box warning">
                        <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.832c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <span>{{ $validation['message'] }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.progress-bar-fill[data-progress]').forEach(function (bar) {
            const value = parseFloat(bar.getAttribute('data-progress'));
            const width = Number.isFinite(value) ? Math.max(0, Math.min(100, value)) : 0;
            bar.style.width = width + '%';
        });
    });
</script>

@endsection
