@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Welcome')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .guest-dashboard {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    /* Welcome Banner */
    .welcome-banner {
        @if($settings->use_gradient_header ?? true)
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
            background: {{ $primaryColor }};
        @endif
        color: white;
        border-radius: 16px;
        padding: 40px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .welcome-banner h1 {
        margin: 0 0 12px 0;
        font-size: 2.5rem;
        color: white;
    }
    
    .welcome-banner p {
        font-size: 1.125rem;
        opacity: 0.95;
        margin: 0;
    }
    
    /* Enrollment Status */
    .enrollment-status {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .status-message {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        border-radius: 10px;
        font-size: 1.125rem;
    }
    
    .status-message.enrolled {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left: 4px solid #10b981;
    }
    
    .status-message.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }
    
    .status-message.not-enrolled {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #3730a3;
        border-left: 4px solid #6366f1;
    }
    
    .status-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }
    
    /* Section */
    .section {
        background: white;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .section-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 16px;
    }
    
    .section-description {
        font-size: 1rem;
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 24px;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 28px;
        font-size: 1rem;
        font-weight: 500;
        text-decoration: none;
        border-radius: {{ $settings->button_border_radius ?? '8px' }};
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .btn-primary {
        @if(($settings->button_style ?? 'solid') === 'gradient')
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        @else
        background: {{ $primaryColor }};
        @endif
        color: white;
    }
    
    .btn-primary:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .btn-outline {
        background: white;
        color: {{ $primaryColor }};
        border-color: {{ $primaryColor }};
    }
    
    .btn-outline:hover {
        background: {{ $primaryColor }};
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* About Grid */
    .about-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
    }
    
    .about-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    
    .about-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: linear-gradient(135deg, {{ $primaryColor }}20 0%, {{ $primaryColor }}10 100%);
        color: {{ $primaryColor }};
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .about-content h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 8px 0;
    }
    
    .about-content p {
        font-size: 0.9375rem;
        color: #6b7280;
        line-height: 1.6;
        margin: 0;
    }

    /* Student License Section */
    .license-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .license-section h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 8px 0;
    }

    .license-section p {
        font-size: 0.9375rem;
        color: #6b7280;
        margin: 0 0 16px 0;
    }

    .license-status {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        border-radius: 10px;
        margin-bottom: 16px;
    }

    .license-status.license-none {
        background: #f3f4f6;
        color: #374151;
        border-left: 4px solid #9ca3af;
    }

    .license-status.license-pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }

    .license-status.license-verified {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .license-status.license-rejected {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .license-upload-form {
        margin-top: 12px;
    }

    .license-upload-form .file-input-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .license-upload-form input[type="file"] {
        flex: 1;
        min-width: 200px;
        padding: 10px;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        background: #f9fafb;
        font-size: 0.875rem;
        cursor: pointer;
    }

    .license-upload-form input[type="file"]:hover {
        border-color: {{ $primaryColor }};
        background: white;
    }

    .btn-upload {
        padding: 10px 24px;
        background: {{ $primaryColor }};
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: opacity 0.2s;
        white-space: nowrap;
    }

    .btn-upload:hover {
        opacity: 0.9;
    }

    .license-file-info {
        font-size: 0.8rem;
        color: #9ca3af;
        margin-top: 8px;
    }

    .rejection-reason {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 12px;
        margin-top: 8px;
        font-size: 0.875rem;
        color: #991b1b;
    }

    .rejection-reason strong {
        display: block;
        margin-bottom: 4px;
    }

    /* Getting Started Checklist */
    .onboarding-section {
        background: white;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .onboarding-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 8px;
    }

    .onboarding-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .onboarding-subtitle {
        color: #6b7280;
        font-size: 0.95rem;
        margin-bottom: 28px;
        padding-left: 42px;
    }

    .onboarding-progress {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
        padding: 12px 16px;
        background: linear-gradient(135deg, {{ $primaryColor }}08 0%, {{ $primaryColor }}15 100%);
        border-radius: 10px;
    }

    .onboarding-progress-bar {
        flex: 1;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .onboarding-progress-fill {
        height: 100%;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        border-radius: 4px;
        transition: width 0.6s ease;
    }

    .progress-0 {
        width: 0%;
    }

    .progress-25 {
        width: 25%;
    }

    .progress-50 {
        width: 50%;
    }

    .progress-75 {
        width: 75%;
    }

    .progress-100 {
        width: 100%;
    }

    .onboarding-progress-text {
        font-size: 0.85rem;
        font-weight: 600;
        color: {{ $primaryColor }};
        white-space: nowrap;
    }

    .onboarding-steps {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .onboarding-step {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 0;
        border-bottom: 1px solid #f3f4f6;
        position: relative;
    }

    .onboarding-step:last-child {
        border-bottom: none;
    }

    .step-indicator {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .step-indicator.completed {
        background: #10b981;
        color: white;
    }

    .step-indicator.current {
        background: {{ $primaryColor }};
        color: white;
        box-shadow: 0 0 0 4px {{ $primaryColor }}30;
    }

    .step-indicator.upcoming {
        background: #e5e7eb;
        color: #9ca3af;
    }

    .step-content {
        flex: 1;
        min-width: 0;
    }

    .step-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .step-title.completed {
        color: #065f46;
    }

    .step-title.current {
        color: #111827;
    }

    .step-title.upcoming {
        color: #9ca3af;
    }

    .step-description {
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.5;
    }

    .step-action {
        flex-shrink: 0;
        align-self: center;
    }

    .step-action .btn-step {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-step.primary {
        background: {{ $primaryColor }};
        color: white;
    }

    .btn-step.primary:hover {
        filter: brightness(1.1);
        transform: translateY(-1px);
        color: white;
    }

    .btn-step.outline {
        background: white;
        color: {{ $primaryColor }};
        border: 2px solid {{ $primaryColor }};
    }

    .btn-step.outline:hover {
        background: {{ $primaryColor }};
        color: white;
    }

    .step-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .step-badge.done {
        background: #d1fae5;
        color: #065f46;
    }

    .step-badge.waiting {
        background: #fef3c7;
        color: #92400e;
    }

    .step-badge.rejected-badge {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Connector line between steps */
    .onboarding-step::before {
        content: '';
        position: absolute;
        left: 17px;
        top: 54px;
        bottom: -18px;
        width: 2px;
        background: #e5e7eb;
    }

    .onboarding-step:last-child::before {
        display: none;
    }

    .onboarding-step.step-completed::before {
        background: #10b981;
    }

    .upgrade-guidance {
        background: #eff6ff;
        border: 2px solid #3b82f6;
        border-radius: 10px;
        padding: 20px;
        margin-top: 16px;
        text-align: center;
    }

    .upgrade-guidance-icon {
        font-size: 1.5rem;
        margin-bottom: 8px;
    }

    .upgrade-guidance-icon svg {
        width: 32px;
        height: 32px;
    }

    .upgrade-guidance-title {
        margin: 0 0 8px 0;
        color: #1e40af;
        font-size: 1rem;
    }

    .upgrade-guidance-text {
        margin: 0 0 16px 0;
        color: #374151;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .inline-logout-form {
        display: inline;
    }

    .upgrade-guidance-btn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .icon-12 {
        width: 12px;
        height: 12px;
    }

    .icon-14 {
        width: 14px;
        height: 14px;
    }

    .icon-18 {
        width: 18px;
        height: 18px;
    }

    .icon-20 {
        width: 20px;
        height: 20px;
    }

    .icon-24 {
        width: 24px;
        height: 24px;
    }

    .icon-28 {
        width: 28px;
        height: 28px;
    }

    .icon-32 {
        width: 32px;
        height: 32px;
    }

    .icon-shrink {
        flex-shrink: 0;
    }

    .icon-inline-leading {
        vertical-align: middle;
        margin-right: 8px;
    }

    .license-status-note {
        font-size: 0.85rem;
        margin-top: 2px;
    }

    @media (max-width: 640px) {
        .onboarding-section {
            padding: 20px;
        }

        .onboarding-subtitle {
            padding-left: 0;
        }

        .onboarding-step {
            flex-wrap: wrap;
        }

        .step-action {
            width: 100%;
            padding-left: 52px;
            margin-top: 8px;
        }

        .step-action .btn-step {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="guest-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h1>Welcome, {{ $guest->name }}!</h1>
        <p>Discover driving courses and start your journey with {{ $school->name }}</p>
    </div>
    
    <!-- Getting Started Checklist -->
    @php
        $step1Done = true; // Registration is done (they're on this page)
        $step2Done = $hasSubmittedRequest ?? false;
        $step3Done = ($approvedEnrollment ?? false) ? true : false;
        
        // Determine if license is optional for this student
        $latestRequest = $pendingRequest ?? $approvedEnrollment ?? $rejectedRequest;
        $isTheoreticalOnly = $latestRequest && $latestRequest->course && $latestRequest->course->course_type === 'theoretical';
        $isNewDriver = ($latestRequest && $latestRequest->experience_level === 'new_driver') || ($guest && $guest->experience_level === 'new_driver');
        $licenseOptional = $isTheoreticalOnly || $isNewDriver;

        $completedSteps = ($step1Done ? 1 : 0) + ($step2Done ? 1 : 0) + ($step3Done ? 1 : 0);
        $progressPercent = round(($completedSteps / 3) * 100);
        
        // Determine current step
        if (!$step2Done) {
            $currentStep = 2; // Browse & enroll
        } elseif (!$step3Done) {
            $currentStep = 3; // Waiting for approval
        } else {
            $currentStep = 4; // All done
        }

        $pendingEnrollmentStage = null;
        if ($pendingRequest) {
            if (!$pendingRequest->payment_proof_path) {
                $pendingEnrollmentStage = 'payment_needed';
            }
            elseif (in_array($pendingRequest->payment_status, ['rejected', 'revision_required'], true)) {
                $pendingEnrollmentStage = 'payment_update_required';
            }
            elseif ($pendingRequest->payment_status === 'paid') {
                $pendingEnrollmentStage = 'awaiting_approval';
            }
            else {
                $pendingEnrollmentStage = 'payment_under_review';
            }
        }
    @endphp

    @if(!$step3Done)
    <div class="onboarding-section">
        <div class="onboarding-header">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $primaryColor }}" class="icon-28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <h2>Getting Started</h2>
        </div>
        <p class="onboarding-subtitle">Follow these steps to begin your driving journey with {{ $school->name }}</p>

        <div class="onboarding-progress">
            <div class="onboarding-progress-bar">
                <div class="onboarding-progress-fill progress-{{ $progressPercent }}"></div>
            </div>
            <span class="onboarding-progress-text">
                {{ $completedSteps }} of 3 complete 
            </span>
        </div>

        <div class="onboarding-steps">
            <!-- Step 1: Create Account -->
            <div class="onboarding-step step-completed">
                <div class="step-indicator completed">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="step-content">
                    <div class="step-title completed">Create Your Account</div>
                    <div class="step-description">You've successfully registered. Welcome aboard!</div>
                </div>
                <div class="step-action">
                    <span class="step-badge done">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        Done
                    </span>
                </div>
            </div>

            <!-- Step 2: Browse & Enroll -->
            <div class="onboarding-step {{ $step2Done ? 'step-completed' : '' }}">
                @if($step2Done)
                    <div class="step-indicator completed">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                @elseif($currentStep == 2)
                    <div class="step-indicator current">2</div>
                @else
                    <div class="step-indicator upcoming">2</div>
                @endif
                <div class="step-content">
                    <div class="step-title {{ $step2Done ? 'completed' : ($currentStep == 2 ? 'current' : 'upcoming') }}">Browse Courses & Enroll</div>
                    <div class="step-description">
                        @if($step2Done && $pendingRequest)
                            You submitted an enrollment request for <strong>{{ $pendingRequest->course->title }}</strong>.
                        @elseif($step2Done && $rejectedRequest && !$pendingRequest)
                            Your previous request was not approved. Try enrolling in another course.
                        @else
                            Explore our TDC & PDC courses and submit an enrollment request.
                        @endif
                    </div>
                </div>
                <div class="step-action">
                    @if($step2Done && $pendingRequest)
                        @if($pendingEnrollmentStage === 'payment_needed')
                            <a href="{{ route('schools.guest.payment.show', ['school' => $school->slug, 'enrollment_request_id' => $pendingRequest->id]) }}" 
                               class="btn-step primary">
                                Submit Payment Details
                            </a>
                        @elseif($pendingEnrollmentStage === 'payment_update_required')
                            <a href="{{ route('schools.guest.payment.show', ['school' => $school->slug, 'enrollment_request_id' => $pendingRequest->id]) }}" 
                               class="btn-step primary">
                                Update Payment Details
                            </a>
                        @elseif($pendingEnrollmentStage === 'awaiting_approval')
                            <span class="step-badge done">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Payment Verified
                            </span>
                        @else
                            <span class="step-badge waiting">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Payment Review
                            </span>
                        @endif
                    @elseif($step2Done && $rejectedRequest && !$pendingRequest)
                        <a href="{{ route('schools.guest.courses', $school) }}" class="btn-step primary">Try Again</a>
                    @elseif($currentStep == 2)
                        <a href="{{ route('schools.guest.courses', $school) }}" class="btn-step primary">
                            Browse Courses
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="step-badge done">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            Done
                        </span>
                    @endif
                </div>
            </div>



            <!-- Step 4: Get Approved -->
            <div class="onboarding-step">
                @if($step3Done)
                    <div class="step-indicator completed">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                @elseif($currentStep == 3)
                    <div class="step-indicator current">3</div>
                @else
                    <div class="step-indicator upcoming">3</div>
                @endif
                <div class="step-content">
                    <div class="step-title {{ $step3Done ? 'completed' : ($currentStep == 3 ? 'current' : 'upcoming') }}">Get Approved & Start Learning</div>
                    <div class="step-description">
                        @if ($pendingRequest && $currentStep == 3)
                            @if ($pendingEnrollmentStage === 'payment_needed' || $pendingEnrollmentStage === 'payment_update_required')
                                Please complete your payment submission to move to the final approval stage.
                            @elseif ($pendingEnrollmentStage === 'payment_under_review')
                                Your payment is being reviewed. Once verified, your enrollment moves to final approval.
                            @elseif ($pendingEnrollmentStage === 'awaiting_approval')
                                Payment verified. Your enrollment is now waiting for final school approval.
                            @else
                                An admin is reviewing your request. You'll become a full student once approved!
                            @endif
                        @else
                            Once your enrollment is approved, you'll get full access to schedules, progress tracking, and lessons.
                        @endif
                    </div>
                </div>
                <div class="step-action">
                    @if ($pendingRequest && $currentStep == 3)
                        @if ($pendingEnrollmentStage === 'payment_needed')
                             <span class="step-badge waiting" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                Action Needed: Payment
                            </span>
                        @elseif ($pendingEnrollmentStage === 'payment_update_required')
                            <span class="step-badge rejected-badge">
                                Payment Update Required
                            </span>
                        @elseif ($pendingEnrollmentStage === 'payment_under_review')
                            <span class="step-badge waiting">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Payment Under Review
                            </span>
                        @else
                            <span class="step-badge waiting">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Pending Approval
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Enrollment Approved — show success banner -->
    <div class="enrollment-status">
        <div class="status-message enrolled">
            <div class="status-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-32">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <strong>You're enrolled!</strong> You are enrolled in <strong>{{ $approvedEnrollment->course->title }}</strong>.
            </div>
        </div>
        <!-- Transition guidance -->
        <div class="upgrade-guidance">
            <div class="upgrade-guidance-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg></div>
            <h4 class="upgrade-guidance-title">Your Account Has Been Upgraded to Student!</h4>
            <p class="upgrade-guidance-text">
                Please log out and log back in to access your full student dashboard with schedules, progress tracking, and more.
            </p>
            <form method="POST" action="{{ school_route('logout') }}" class="inline-logout-form">
                @csrf
                <button type="submit" class="upgrade-guidance-btn">
                    Log Out & Re-login as Student
                </button>
            </form>
        </div>
    </div>
    @endif
    
    <!-- License Roadmap Section -->
    <div class="section" style="margin-top: 30px;">
        <h2 class="section-title">Your Licensing Journey</h2>
        <p class="section-description">
            Follow these steps to acquire your official driver's license. This guide is provided by {{ $school->name }}.
        </p>
        @include('school.student.partials.license-guide', ['student' => $guest])
    </div>
    


    <!-- Browse Courses Section -->
    <div class="section">
        <h2 class="section-title">Available Courses</h2>
        <p class="section-description">
            Explore our comprehensive driving courses designed for all skill levels. From beginner to advanced, 
            we offer programs tailored to help you become a confident and safe driver.
        </p>
        <div class="action-buttons">
            <a href="{{ route('schools.guest.courses', $school) }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Browse All Courses
            </a>
            <a href="{{ route('schools.guest.enrollmentRequests', $school) }}" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                My Requests
            </a>
        </div>
    </div>
    
    <!-- About Section -->
    <div class="section">
        <h2 class="section-title">About {{ $school->name }}</h2>
        <p class="section-description">
            {{ $school->description ?? 'We are committed to providing quality driving education to help you become a safe and confident driver.' }}
        </p>
        
        <div class="about-grid">
            <div class="about-item">
                <div class="about-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <div class="about-content">
                    <h3>Certified Instructors</h3>
                    <p>Learn from experienced and certified driving instructors dedicated to your success.</p>
                </div>
            </div>
            
            <div class="about-item">
                <div class="about-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="about-content">
                    <h3>Flexible Scheduling</h3>
                    <p>Choose lesson times that fit your schedule with our convenient scheduling system.</p>
                </div>
            </div>
            
            <div class="about-item">
                <div class="about-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div class="about-content">
                    <h3>Track Your Progress</h3>
                    <p>Monitor your learning journey with detailed progress tracking and feedback.</p>
                </div>
            </div>
            
            <div class="about-item">
                <div class="about-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="about-content">
                    <h3>Affordable Rates</h3>
                    <p>Quality driving education at competitive prices with flexible payment options.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('test_credentials') && app()->environment('local', 'development', 'testing'))
<!-- Test Credentials Popup Modal (Development Only) -->
<div id="testCredentialsModal" class="tc-modal-overlay">
    <div class="tc-modal-card">
        <!-- Header -->
        <div class="tc-header">
            <div class="tc-header-icon">🎉</div>
            <h2 class="tc-header-title">Welcome to {{ $school->name }}!</h2>
            <p class="tc-header-subtitle">Registration Successful - Save Your Credentials</p>
        </div>
        
        <!-- Warning Banner -->
        <div class="tc-warning-banner">
            <span class="tc-warning-icon">⚠️</span>
            <span>DEVELOPMENT MODE ONLY - This popup won't appear in production</span>
        </div>
        
        <!-- Content -->
        <div class="tc-content">
            <p class="tc-content-intro">
                Save these credentials for testing. You can copy them with one click:
            </p>
            
            <!-- Name -->
            <div class="tc-field-group">
                <label class="tc-field-label">Your Name</label>
                <div class="tc-field-row">
                    <input type="text" value="{{ session('test_credentials')['name'] }}" readonly class="tc-input tc-input-default">
                </div>
            </div>
            
            <!-- Email -->
            <div class="tc-field-group">
                <label class="tc-field-label">Email Address (Login Username)</label>
                <div class="tc-field-row">
                    <input type="text" value="{{ session('test_credentials')['email'] }}" readonly class="tc-input tc-input-mono">
                    <button onclick="copyText('{{ session('test_credentials')['email'] }}', this)" class="tc-copy-btn">
                        Copy
                    </button>
                </div>
            </div>
            
            <!-- Password -->
            <div class="tc-field-group tc-field-group-last">
                <label class="tc-field-label">Password</label>
                <div class="tc-field-row">
                    <input type="text" value="{{ session('test_credentials')['password'] }}" readonly class="tc-input tc-input-mono">
                    <button onclick="copyText('{{ session('test_credentials')['password'] }}', this)" class="tc-copy-btn">
                        Copy
                    </button>
                </div>
            </div>
            
            <!-- Info Box -->
            <div class="tc-info-box">
                <div class="tc-info-row">
                    <span class="tc-info-icon">💡</span>
                    <div class="tc-info-text">
                        <p class="tc-info-title"><strong>You're now logged in as a Guest!</strong></p>
                        <p class="tc-info-desc">You can now browse courses and submit enrollment requests. Once approved by an admin, you'll become a full student with access to all features.</p>
                    </div>
                </div>
            </div>
            
            <!-- Close Button -->
            <button onclick="closeModal()" class="tc-close-btn">
                Got it! Let's Get Started
            </button>
        </div>
    </div>
</div>

<style>
    .tc-modal-overlay {
        display: flex;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    }

    .tc-modal-card {
        background: white;
        border-radius: 20px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        overflow: hidden;
        animation: slideIn 0.3s ease;
    }

    .tc-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 25px;
        text-align: center;
        color: white;
    }

    .tc-header-icon {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .tc-header-title {
        margin: 0;
        font-size: 1.8rem;
    }

    .tc-header-subtitle {
        margin: 10px 0 0 0;
        opacity: 0.95;
        font-size: 0.95rem;
    }

    .tc-warning-banner {
        background: #fbbf24;
        color: #78350f;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .tc-warning-icon {
        font-size: 1.2rem;
    }

    .tc-content {
        padding: 30px;
    }

    .tc-content-intro {
        margin-bottom: 25px;
        color: #4b5563;
        text-align: center;
        font-size: 0.95rem;
    }

    .tc-field-group {
        margin-bottom: 20px;
    }

    .tc-field-group-last {
        margin-bottom: 25px;
    }

    .tc-field-label {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .tc-field-row {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f9fafb;
        padding: 14px 16px;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
    }

    .tc-input {
        flex: 1;
        background: transparent;
        border: none;
        font-size: 1rem;
        color: #1f2937;
        outline: none;
    }

    .tc-input-default {
        font-family: 'Segoe UI', sans-serif;
    }

    .tc-input-mono {
        font-family: 'Courier New', monospace;
    }

    .tc-copy-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .tc-copy-btn-copied {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .tc-info-box {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 4px solid #3b82f6;
    }

    .tc-info-row {
        display: flex;
        gap: 10px;
        align-items: start;
    }

    .tc-info-icon {
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .tc-info-text {
        font-size: 0.85rem;
        color: #1e40af;
        line-height: 1.5;
    }

    .tc-info-title {
        margin: 0 0 8px 0;
    }

    .tc-info-desc {
        margin: 0;
    }

    .tc-close-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.05rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>

<script>
    function copyText(text, button) {
        navigator.clipboard.writeText(text).then(() => {
            const originalText = button.textContent;
            button.textContent = '✓ Copied!';
            button.classList.add('tc-copy-btn-copied');
            
            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('tc-copy-btn-copied');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy:', err);
            alert('Failed to copy to clipboard');
        });
    }

    function closeModal() {
        document.getElementById('testCredentialsModal').style.display = 'none';
    }

    // Close on outside click
    document.getElementById('testCredentialsModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endif

@endsection
