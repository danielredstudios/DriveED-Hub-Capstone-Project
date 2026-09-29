@php
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $borderRadius = $settings?->border_radius ?? 12;
@endphp

<style>
    .license-journey-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border: 2px solid {{ $primaryColor }}22;
        border-radius: {{ $borderRadius }}px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .journey-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .journey-title svg {
        width: 24px;
        height: 24px;
        fill: {{ $primaryColor }};
    }

    .journey-steps {
        display: flex;
        flex-direction: column;
        gap: 16px;
        position: relative;
    }

    .journey-steps::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 20px;
        bottom: 20px;
        width: 2px;
        background: {{ $primaryColor }}33;
    }

    .journey-step {
        display: flex;
        gap: 20px;
        position: relative;
        z-index: 1;
    }

    .step-number {
        width: 32px;
        height: 32px;
        background: white;
        border: 2px solid {{ $primaryColor }};
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: {{ $primaryColor }};
        font-size: 0.875rem;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .journey-step.active .step-number {
        background: {{ $primaryColor }};
        color: white;
    }

    .step-content {
        flex: 1;
    }

    .step-title {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.95rem;
        margin-bottom: 4px;
    }

    .step-desc {
        font-size: 0.85rem;
        color: #6b7280;
        line-height: 1.5;
    }

    .step-badge {
        display: inline-block;
        margin-top: 6px;
        padding: 2px 8px;
        background: {{ $primaryColor }}15;
        color: {{ $primaryColor }};
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
    }
</style>

<div class="license-journey-card">
    <h3 class="journey-title">
        <svg viewBox="0 0 24 24"><path d="M12,2L4.5,20.29L5.21,21L12,18L18.79,21L19.5,20.29L12,2Z"/></svg>
        Your Roadmap to a Driver's License
    </h3>
    
    <div class="journey-steps">
        @php
            $steps = $settings->license_instructions ?? [];
            if (!is_array($steps)) $steps = [];
            
            // 1. Fetch all system statuses once – include active enrollment context for practical-only flow
            $isTdcDone = $student->has_passed_theoretical ?? false;
            $hasVerifiedPermit = ($student->student_license_status ?? '') === 'verified';
            $isPdcDone = $student ? $student->enrollmentRequests()
                ->whereHas('course', fn($q) => $q->where('course_type', 'practical')->orWhere('type', 'Practical'))
                ->where('status', 'completed')
                ->exists() : false;
            $hasLicenseCode = !empty($student->dl_code);
            $activeEnrollmentsList = isset($activeEnrollments) ? $activeEnrollments : null;
            $primaryEnrollmentObj = isset($primaryEnrollment) ? $primaryEnrollment : null;

            // Robust practical enrollment detection
            $hasPracticalEnrollment = false;
            if ($primaryEnrollmentObj && in_array(strtolower($primaryEnrollmentObj->course?->course_type ?? $primaryEnrollmentObj->course?->type ?? ''), ['practical', 'pdc'])) {
                $hasPracticalEnrollment = true;
            } elseif ($activeEnrollmentsList && $activeEnrollmentsList->first(fn($e) => in_array(strtolower($e->course?->course_type ?? $e->course?->type ?? ''), ['practical', 'pdc']))) {
                $hasPracticalEnrollment = true;
            } elseif ($student) {
                $hasPracticalEnrollment = $student->enrollmentRequests()
                    ->whereHas('course', fn($q) => $q->where('course_type', 'practical')->orWhere('type', 'Practical'))
                    ->whereNotIn('status', ['rejected', 'cancelled'])
                    ->exists();
            }

            // Advance practical learners directly to Step 3 (PDC) by treating Steps 1 & 2 as satisfied
            $isPracticalOnly = $hasPracticalEnrollment || $hasVerifiedPermit || ($student && method_exists($student, 'hasStoredLicense') && $student->hasStoredLicense());

            // 2. Process steps with sequential dependency
            // A step can only be "Completed" if its milestone is met AND all previous milestones were met.
            $processedSteps = [];
            $previousMilestonesMet = true;
            $foundCurrentStep = false;

            foreach($steps as $index => $step) {
                $milestone = $step['milestone'] ?? 'none';
                $milestoneMet = false;

                // Check if the specific milestone for THIS step is met
                switch($milestone) {
                    case 'tdc':     $milestoneMet = $isTdcDone; break;
                    case 'permit':  $milestoneMet = $hasVerifiedPermit; break;
                    case 'pdc':     $milestoneMet = $isPdcDone; break;
                    case 'license': $milestoneMet = $hasLicenseCode; break;
                    case 'none':    $milestoneMet = true; break; // Manual steps don't block
                }

            // Sequential Logic: A step is only 'Done' if its milestone is met AND previous were met
                // Practical-only enrollment skips TDC/Permit pre-steps if already submitted/enrolled in PDC
                $effectiveMet = $milestoneMet;
                if ($isPracticalOnly && ($milestone === 'tdc' || $milestone === 'permit')) {
                    $effectiveMet = true;
                }
                $isStepCompleted = $effectiveMet && $previousMilestonesMet;
                
                // If this milestone isn't met, the sequence is "broken" for future steps (respect practical skip)
                if (!$effectiveMet && $milestone !== 'none') {
                    $previousMilestonesMet = false;
                }

                $isActive = false;
                if (!$isStepCompleted && !$foundCurrentStep) {
                    $isActive = true;
                    $foundCurrentStep = true;
                }

                $processedSteps[] = [
                    'title' => $step['title'] ?? ('Step ' . ($index + 1)),
                    'description' => $step['description'] ?? '',
                    'isCompleted' => $isStepCompleted,
                    'isActive' => $isActive,
                    'index' => $index + 1
                ];
            }
        @endphp

        @forelse($processedSteps as $step)
            <div class="journey-step {{ $step['isActive'] ? 'active' : '' }} {{ $step['isCompleted'] ? 'completed' : '' }}">
                <div class="step-number">
                    @if($step['isCompleted'])
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                    @else
                        {{ $step['index'] }}
                    @endif
                </div>
                <div class="step-content">
                    <div class="step-title" style="{{ $step['isCompleted'] ? 'color: #059669; text-decoration: line-through; opacity: 0.7;' : '' }}">
                        {{ $step['title'] }}
                    </div>
                    <div class="step-desc" style="{{ $step['isCompleted'] ? 'opacity: 0.6;' : '' }}">
                        {{ $step['description'] }}
                    </div>
                    
                    @if($step['isCompleted'])
                        <span class="step-badge" style="background:#d1fae5; color:#065f46; border: 1px solid #10b98144;">
                            <i class="bi bi-check-circle-fill" style="margin-right: 4px;"></i> Completed
                        </span>
                    @elseif($step['isActive'])
                        <span class="step-badge" style="background: {{ $primaryColor }}22; color: {{ $primaryColor }}; border: 1px solid {{ $primaryColor }}44;">
                            <i class="bi bi-play-circle-fill" style="margin-right: 4px;"></i> Your Next Step
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-muted">No roadmap steps configured by the school.</p>
        @endforelse
    </div>
</div>
