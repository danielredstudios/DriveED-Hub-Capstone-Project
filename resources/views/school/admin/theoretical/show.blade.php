@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Training Hub')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';

    // Package-Based Limits
    $tdcLimit = $enrollment->tdc_hours_limit;
    $pdcLimit = $enrollment->pdc_hours_limit;
    
    $tdcUsed = $enrollment->used_tdc_hours;
    $pdcUsed = $enrollment->used_pdc_hours;

    $tdcProgress = $tdcLimit > 0 ? min(100, round(($tdcUsed / $tdcLimit) * 100)) : 0;
    $pdcProgress = $pdcLimit > 0 ? min(100, round(($pdcUsed / $pdcLimit) * 100)) : 0;

    $tdcClass = $tdcProgress >= 100 ? 'high' : ($tdcProgress >= 50 ? 'mid' : 'low');
    $pdcClass = $pdcProgress >= 100 ? 'high' : ($pdcProgress >= 50 ? 'mid' : 'low');

    // Chronological Session History (TDC + PDC)
    $allSessions = $enrollment->sessionCompletions->sortByDesc('session_date');

    // LTO TDC Compliance Logic
    $tdcLtoProgress = \App\Support\EnrollmentValidator::getTdcProgress($enrollment);
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

    /* Disabled State - Gray out */
    .btn:disabled, .btn-primary:disabled, .btn-success:disabled {
        background: #e5e7eb !important;
        border-color: #d1d5db !important;
        color: #9ca3af !important;
        cursor: not-allowed !important;
        transform: none !important;
        box-shadow: none !important;
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
            <h1 class="page-title">
                Student Training Hub & Life Log
                @if($liveSession)
                    <span class="badge-custom badge-success" style="vertical-align: middle; margin-left: 10px; animation: pulse 2s infinite;">
                        🔴 LIVE
                    </span>
                @endif
            </h1>
            <p class="page-subtitle">{{ $enrollment->student->name }} — Tracking full training lifecycle</p>
        </div>
        <div style="display: flex; gap: 10px;">
            @if($liveSession)
                <div class="alert-box success" style="margin-bottom: 0; padding: 8px 12px; font-size: 0.8rem;">
                    <svg class="icon-size-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Currently in Session with {{ $liveSession->instructor->name }}</span>
                </div>
            @endif
            <a href="{{ route('schools.admin.theoretical.index', ['school' => $school->slug]) }}" class="back-link">
                <svg class="icon-size-16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>

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
                    <span class="info-label">Enrollment Status:</span>
                    <span class="info-value">
                        <span class="badge-custom badge-info">{{ ucfirst($enrollment->status) }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">TDC Status:</span>
                    <span class="info-value">
                        @if($enrollment->student->has_passed_theoretical)
                            <span class="badge-custom badge-success">GRADUATED</span>
                        @else
                            <span class="badge-custom badge-secondary">IN PROGRESS</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Enrolled:</span>
                    <span class="info-value">{{ $enrollment->enrolled_at?->format('M d, Y') ?? $enrollment->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <!-- Course & Package Info -->
            <div class="info-card">
                <h5 class="card-title">
                    <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $primaryColor }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Package Details
                </h5>
                <div class="info-row">
                    <span class="info-label">Selected Course:</span>
                    <span class="info-value bold">{{ $enrollment->course->title ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Package:</span>
                    <span class="info-value bold">{{ $enrollment->package->name ?? 'Standard' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">TDC Allowance:</span>
                    <span class="info-value">{{ $tdcLimit }} hours</span>
                </div>
                <div class="info-row">
                    <span class="info-label">PDC Allowance:</span>
                    <span class="info-value">{{ $pdcLimit }} hours</span>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Graduation Actions -->
            <div class="info-card" style="border-top: 4px solid {{ $enrollment->status === 'completed' ? '#10b981' : $secondaryColor }};">
                <h5 class="card-title">
                    <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $enrollment->status === 'completed' ? '#10b981' : $secondaryColor }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Student Graduation Status
                </h5>
                
                @if($enrollment->status === 'completed')
                    <div style="text-align: center; padding: 20px 0;">
                        <div style="background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 10px; border: 1px solid #10b981;">
                            <h4 style="margin: 0; font-size: 1.2rem; font-weight: 700;">🎓 GRADUATED</h4>
                            <p style="margin: 5px 0 0 0; font-size: 0.9rem;">Course completed on {{ $enrollment->completed_at?->format('M d, Y') }}</p>
                        </div>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <!-- Step 1: TDC Graduation -->
                        <div style="padding: 15px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 1rem; color: #374151;">Step 1: Classroom Training (TDC)</div>
                                    <div style="font-size: 0.85rem; color: #6b7280;">Requirement: {{ $tdcLimit }} Hours</div>
                                </div>
                                @if($enrollment->student->has_passed_theoretical)
                                    <span class="badge-custom badge-success" style="padding: 6px 12px; font-weight: 700;">COMPLETED ✓</span>
                                @else
                                    <span class="badge-custom {{ $tdcProgress >= 100 ? 'badge-info' : 'badge-secondary' }}" style="padding: 6px 12px;">{{ $tdcProgress }}% Done</span>
                                @endif
                            </div>

                             @if(!$enrollment->student->has_passed_theoretical)
                                <div style="margin-bottom: 12px; padding: 15px; background: {{ $tdcLtoProgress['is_compliant'] ? '#f0fdf4' : '#fffbeb' }}; border: 1px solid {{ $tdcLtoProgress['is_compliant'] ? '#a7f3d0' : '#fde68a' }}; border-radius: 8px; font-size: 0.85rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: {{ $tdcLtoProgress['is_compliant'] ? '#065f46' : '#92400e' }};">
                                            <i class="bi bi-shield-check"></i>
                                            LTO Compliance Check
                                        </div>
                                        <span class="badge-custom {{ $tdcLtoProgress['is_compliant'] ? 'badge-success' : 'badge-secondary' }}" style="padding: 4px 8px; font-size: 0.7rem;">
                                            {{ $tdcLtoProgress['is_compliant'] ? 'COMPLIANT' : 'NON-COMPLIANT' }}
                                        </span>
                                    </div>
                                    
                                    <div style="display: flex; flex-direction: column; gap: 6px; color: #4b5563;">
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>Cumulative Hours (min 15):</span>
                                            <span style="font-weight: 600; color: {{ $tdcLtoProgress['hours'] >= 15 ? '#059669' : '#dc2626' }};">
                                                {{ round($tdcLtoProgress['hours'], 1) }} / 15.0
                                            </span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>Unique Sessions (min 3 days):</span>
                                            <span style="font-weight: 600; color: {{ $tdcLtoProgress['unique_dates_count'] >= 3 ? '#059669' : '#dc2626' }};">
                                                {{ $tdcLtoProgress['unique_dates_count'] }} / 3
                                            </span>
                                        </div>
                                        @if($tdcLtoProgress['unique_dates_count'] > 0)
                                            <div style="font-size: 0.75rem; color: #6b7280; padding-top: 5px; border-top: 1px dashed #d1d5db; margin-top: 2px;">
                                                Dates: {{ $tdcLtoProgress['unique_dates']->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->join(', ') }}
                                            </div>
                                        @endif
                                    </div>

                                    @if(!$tdcLtoProgress['is_compliant'])
                                        <div style="margin-top: 10px; font-size: 0.8rem; color: #b45309; font-style: italic;">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            {{ $validation['message'] ?? 'Student must meet all LTO requirements before graduation.' }}
                                        </div>
                                    @endif

                                    @if($pendingProgression && $pendingProgression->to_phase === 'practical')
                                        <div style="margin-top: 12px; padding: 10px; background: #eff6ff; border-radius: 8px; border: 1px dashed #3b82f6; display: flex; align-items: center; gap: 8px;">
                                            <div style="background: #3b82f6; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                                                <i class="bi bi-person-check-fill"></i>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #1e40af; font-weight: 600;">
                                                Formal progression request is pending review.
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <form action="{{ school_route('admin.theoretical.markAsPassed') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <textarea name="notes" class="form-control" rows="2" placeholder="Theoretical completion notes..." style="font-size: 0.85rem;" {{ !$tdcLtoProgress['is_compliant'] ? 'disabled' : '' }}></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100" style="font-weight: 600;" {{ !$tdcLtoProgress['is_compliant'] ? 'disabled' : '' }} onclick="return confirm('{{ $pendingProgression && $pendingProgression->to_phase === 'practical' ? 'Approve progression request and mark as passed?' : 'Mark student as passed TDC? This will officially unlock PDC for them.' }}')">
                                        {{ $pendingProgression && $pendingProgression->to_phase === 'practical' ? 'Approve Request & Pass TDC' : 'Mark TDC as Passed' }}
                                    </button>
                                </form>
                            @else
                                <div style="font-size: 0.85rem; color: #059669; background: #ecfdf5; padding: 8px; border-radius: 6px;">
                                    <strong>Passed on:</strong> {{ $enrollment->student->theoretical_passed_at?->format('M d, Y') ?? 'N/A' }}
                                </div>
                            @endif
                        </div>

                        <!-- Step 2: PDC Graduation -->
                        <div style="padding: 15px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb; opacity: {{ $enrollment->student->has_passed_theoretical ? '1' : '0.5' }};">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 1rem; color: #374151;">Step 2: Practical Training (PDC)</div>
                                    <div style="font-size: 0.85rem; color: #6b7280;">Requirement: {{ $pdcLimit }} Hours</div>
                                </div>
                                <span class="badge-custom {{ $pdcProgress >= 100 ? 'badge-success' : 'badge-secondary' }}" style="padding: 6px 12px;">{{ $pdcProgress }}% Done</span>
                            </div>

                            @if($enrollment->student->has_passed_theoretical)
                                <div style="margin-bottom: 12px; padding: 12px; background: {{ $pdcProgress >= 100 ? '#f0fdf4' : '#fffbeb' }}; border: 1px solid {{ $pdcProgress >= 100 ? '#a7f3d0' : '#fde68a' }}; border-radius: 8px; font-size: 0.85rem; color: {{ $pdcProgress >= 100 ? '#065f46' : '#92400e' }};">
                                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; margin-bottom: 4px;">
                                        <i class="bi bi-info-circle-fill"></i>
                                        Instructions
                                    </div>
                                    @if($pdcProgress >= 100)
                                        The student has finished their driving hours. You can now issue their graduation certificate.
                                    @else
                                        Student must complete all <strong>{{ $pdcLimit }} hours</strong> of driving. Currently at <strong>{{ $pdcUsed }} hours</strong>.
                                    @endif
                                </div>

                                <form action="{{ school_route('admin.theoretical.complete', $enrollment->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100" style="font-weight: 600; background: #10b981; border: none;" {{ $pdcProgress < 100 ? 'disabled' : '' }} onclick="return confirm('{{ $pendingProgression && $pendingProgression->to_phase === 'completed' ? 'Approve graduation request and complete course?' : 'Graduate student? This will mark the entire course as COMPLETED.' }}')">
                                        {{ $pendingProgression && $pendingProgression->to_phase === 'completed' ? 'Approve Request & Graduate' : 'Graduate & Issue Certificate' }}
                                    </button>
                                </form>
                            @else
                                <div style="text-align: center; font-size: 0.85rem; color: #9ca3af; padding: 10px; border: 1px dashed #d1d5db; border-radius: 6px;">
                                    🔒 Locked until TDC is passed
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Theoretical Progress (TDC) -->
            <div class="info-card">
                <h5 class="card-title">
                    <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $primaryColor }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Classroom Progress (TDC)
                </h5>
                <div class="progress-stats">
                    <div class="progress-stat-item">
                        <div class="progress-stat-value">{{ $tdcUsed }}</div>
                        <div class="progress-stat-label">Hours Used</div>
                    </div>
                    <div class="progress-stat-item">
                        <div class="progress-stat-value">{{ $tdcLimit }}</div>
                        <div class="progress-stat-label">Limit</div>
                    </div>
                    <div class="progress-stat-item">
                        <div class="progress-stat-value {{ $tdcClass }}">{{ $tdcProgress }}%</div>
                        <div class="progress-stat-label">Progress</div>
                    </div>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill {{ $tdcClass }}" data-progress="{{ max($tdcProgress, 5) }}">
                        {{ $tdcProgress }}%
                    </div>
                </div>
            </div>

            <!-- Practical Progress (PDC) -->
            @if($pdcLimit > 0)
                <div class="info-card">
                    <h5 class="card-title">
                        <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $secondaryColor }}">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Driving Progress (PDC)
                    </h5>
                    <div class="progress-stats">
                        <div class="progress-stat-item">
                            <div class="progress-stat-value">{{ $pdcUsed }}</div>
                            <div class="progress-stat-label">Hours Used</div>
                        </div>
                        <div class="progress-stat-item">
                            <div class="progress-stat-value">{{ $pdcLimit }}</div>
                            <div class="progress-stat-label">Limit</div>
                        </div>
                        <div class="progress-stat-item">
                            <div class="progress-stat-value {{ $pdcClass }}">{{ $pdcProgress }}%</div>
                            <div class="progress-stat-label">Progress</div>
                        </div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill {{ $pdcClass }}" style="background: linear-gradient(90deg, {{ $secondaryColor }} 0%, {{ $primaryColor }} 100%);" data-progress="{{ max($pdcProgress, 5) }}">
                            {{ $pdcProgress }}%
                        </div>
                    </div>
                </div>
            @endif

            <!-- Session History -->
            <div class="info-card">
                <h5 class="card-title">
                    <svg class="icon-size-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $primaryColor }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Chronological Training Log
                </h5>
                @if($allSessions->count() > 0)
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Instructor</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allSessions as $session)
                                    <tr>
                                        <td>
                                            @if($session->session_type === 'theoretical')
                                                <span class="badge-custom badge-primary">TDC</span>
                                            @else
                                                <span class="badge-custom" style="background: {{ $secondaryColor }}22; color: {{ $secondaryColor }};">PDC</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="font-weight: 500;">{{ $session->session_date->format('M d, Y') }}</div>
                                            <div style="font-size: 0.75rem; color: #6b7280;">{{ $session->session_time }}</div>
                                        </td>
                                        <td>{{ $session->instructor->name }}</td>
                                        <td><span class="badge-custom badge-info">{{ $session->hours_completed }}h</span></td>
                                        <td>
                                            <span class="badge-custom badge-success">Verified</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="empty-text">No sessions recorded yet</p>
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
