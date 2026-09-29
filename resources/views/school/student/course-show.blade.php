@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $course->title ?? 'Course Details')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $accentColor = $settings?->accent_color ?? '#8b5cf6';
    $primaryRgb = sscanf($primaryColor, "#%02x%02x%02x");

    $student = Auth::guard('student')->user();
    $isPracticalCourse = ($course->course_type ?? '') === 'practical';
    $existingRequest = null;
    $enrollmentStatus = null;
    $pendingEnrollmentTitle = null;
    $pendingEnrollmentMessage = null;
    $pendingEnrollmentActionUrl = null;
    $pendingEnrollmentActionLabel = 'Request Submitted';
    $hasPassedTdc = $student?->hasPassedTheoretical() ?? false;
    $hasSubmittedStudentLicense = $student?->hasSubmittedLicense() ?? false;
    $studentLicenseStatus = $student?->student_license_status ?? 'none';
    $mustPassTdcForPractical = $isPracticalCourse && !$hasPassedTdc && !$hasSubmittedStudentLicense;
    $mustUploadLicenseForPractical = $isPracticalCourse && !$hasSubmittedStudentLicense;

    if ($student) {
        $existingRequest = \App\Models\EnrollmentRequest::where('learner_id', $student->id)
            ->where('course_id', $course->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existingRequest) {
            $enrollmentStatus = $existingRequest->status;

            if ($enrollmentStatus === 'pending') {
                $paymentStatus = $existingRequest->payment_status ?? 'pending';
                $hasPaymentProof = !empty($existingRequest->payment_proof_path);

                if (!$hasPaymentProof) {
                    $pendingEnrollmentTitle = 'Payment Needed';
                    $pendingEnrollmentMessage = 'Action needed: upload your payment proof to continue with approval.';
                    $pendingEnrollmentActionLabel = 'Upload Payment Proof';
                    $pendingEnrollmentActionUrl = route('schools.student.payments.index', [
                        'school' => $school->slug,
                        'enrollment_id' => $existingRequest->id,
                    ]);
                }
                elseif (in_array($paymentStatus, ['rejected', 'revision_required'], true)) {
                    $pendingEnrollmentTitle = 'Payment Update Needed';
                    $pendingEnrollmentMessage = 'Your payment submission needs correction before your enrollment can proceed.';
                    $pendingEnrollmentActionLabel = 'Update Payment Submission';
                    $pendingEnrollmentActionUrl = route('schools.student.payments.index', [
                        'school' => $school->slug,
                        'enrollment_id' => $existingRequest->id,
                    ]);
                }
                elseif ($paymentStatus === 'paid') {
                    $pendingEnrollmentTitle = 'Pending Approval';
                    $pendingEnrollmentMessage = 'Payment is verified. Your enrollment is now waiting for final school approval.';
                    $pendingEnrollmentActionLabel = 'Awaiting Approval';
                }
                else {
                    $pendingEnrollmentTitle = 'Payment Under Review';
                    $pendingEnrollmentMessage = 'Your payment submission is being reviewed by the school.';
                    $pendingEnrollmentActionLabel = 'Payment In Review';
                }
            }
        }
    }
    
    $branches = \App\Models\Branch::where('school_id', $school->id)->where('is_active', true)->orderBy('name')->get();
    $enableBranches = $settings->enable_branches ?? false;
@endphp

<style>
    .course-detail-container {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .course-header-banner {
        height: 300px;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .course-header-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.8;
    }

    .banner-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 40px;
        background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);
        color: white;
    }

    .course-title-large {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .course-meta-badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .meta-badge {
        padding: 6px 15px;
        border-radius: 30px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(5px);
        font-size: 0.9rem;
        font-weight: 600;
        border: 1px solid rgba(255,255,255,0.3);
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }

    .detail-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        margin-bottom: 30px;
    }

    .section-heading {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-heading i {
        color: {{ $primaryColor }};
    }

    .module-list {
        list-style: none;
        padding: 0;
    }

    .module-item {
        padding: 15px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .module-item:last-child {
        border-bottom: none;
    }

    .module-name {
        font-weight: 500;
        color: #374151;
    }

    .lesson-badge {
        background: #f3f4f6;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Sidebar / Enrollment Card */
    .enrollment-card {
        position: sticky;
        top: 20px;
    }

    .price-tag {
        font-size: 2rem;
        font-weight: 800;
        color: {{ $primaryColor }};
        margin-bottom: 15px;
    }

    .course-quick-info {
        margin-bottom: 25px;
    }

    .quick-info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.95rem;
    }

    .btn-enroll-action {
        width: 100%;
        padding: 15px;
        background: {{ $primaryColor }};
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba({{ implode(',', $primaryRgb) }}, 0.3);
    }

    .btn-enroll-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba({{ implode(',', $primaryRgb) }}, 0.4);
    }

    .status-alert {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: 600;
        text-align: center;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-approved { background: #d1fae5; color: #065f46; }

    /* Modal Styles (Borrowed from Guest View) */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 1000;
        backdrop-filter: blur(4px);
        justify-content: center;
        align-items: flex-start;
        overflow-y: auto;
        padding: 20px 12px;
        overscroll-behavior: contain;
    }

    .modal-window {
        background: white;
        border-radius: 20px;
        width: 90%;
        max-width: 600px;
        max-height: calc(100vh - 40px);
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        padding: 20px 30px;
        background: {{ $primaryColor }};
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body {
        padding: 30px;
        min-height: 0;
        overflow-y: auto;
        flex: 1 1 auto;
        -webkit-overflow-scrolling: touch;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #374151;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        border-color: {{ $primaryColor }};
        outline: none;
    }

    .btn-submit {
        background: {{ $primaryColor }};
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
    }

    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; }
        .course-title-large { font-size: 1.8rem; }
        .course-header-banner { height: 200px; }
    }
</style>

<div class="course-detail-container">


    <div class="course-header-banner">
        @if($course->banner_image)
            <img src="{{ asset($course->banner_image) }}" alt="{{ $course->title }}">
        @endif
        <div class="banner-overlay">
            <h1 class="course-title-large">{{ $course->title }}</h1>
            <div class="course-meta-badges">
                <span class="meta-badge">{{ ucfirst($course->course_type ?? 'Standard') }}</span>
                @if($course->vehicle_type)
                    <span class="meta-badge">{{ $course->vehicle_type }}</span>
                @endif
                <span class="meta-badge">{{ $course->hours_required ?? $course->duration_hours ?? 0 }} Hours Training</span>
            </div>
        </div>
    </div>

    <div class="detail-grid">
        <div class="left-section">
            <div class="detail-card">
                <h2 class="section-heading"><i class="fas fa-info-circle"></i> Course Description</h2>
                <div class="text-gray-700 leading-relaxed">
                    {!! nl2br(e($course->description ?? 'No description provided.')) !!}
                </div>
            </div>

            <div class="detail-card" style="border-left:4px solid {{ $primaryColor }};">
                <h2 class="section-heading"><i class="fas fa-shield-alt"></i> Restrictions & Eligibility</h2>
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
                    <span style="padding:6px 12px;border-radius:14px;background:#fef3c7;color:#92400e;font-weight:600;font-size:0.85rem;">{{ $course->license_type === 'non_professional' ? 'Non-Pro' : 'Pro' }} License</span>
                    <span style="padding:6px 12px;border-radius:14px;background:#fee2e2;color:#991b1b;font-weight:600;font-size:0.85rem;">DL Codes: {{ $course->license_type === 'professional' ? 'A, A1, B, B1, B2, C, D, BE, CE' : 'A, A1, B, B1, B2' }}</span>
                    @if($course->required_restriction)
                        <span style="padding:6px 12px;border-radius:14px;background:#dbeafe;color:#1e40af;font-weight:600;font-size:0.85rem;">Req. Restriction: {{ $course->required_restriction }}</span>
                    @endif
                </div>
                <p style="font-size:0.9rem;color:#6b7280;">This badge information is shown here to help you choose the right package; it is hidden on the course grid.</p>
            </div>

            <div class="detail-card">
                <h2 class="section-heading"><i class="fas fa-book"></i> Curriculum / Modules</h2>
                @if($course->modules && $course->modules->count() > 0)
                    <div class="module-list">
                        @foreach($course->modules->sortBy('sort_order') as $module)
                            <div class="module-item">
                                <span class="module-name">{{ $module->title }}</span>
                                <span class="lesson-badge">{{ $module->lessons->count() }} Lessons</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 italic">No modules available for this course yet.</p>
                @endif
            </div>
        </div>

        <div class="right-section">
            <div class="detail-card enrollment-card">
                @if($enrollmentStatus === 'approved')
                    <div class="status-alert status-approved">
                        ✓ Enrolled
                    </div>
                    <a href="{{ school_route('student.my-course') }}" class="btn-enroll-action text-center block" style="text-decoration: none;">
                        Access Course Content
                    </a>
                @elseif($enrollmentStatus === 'pending')
                    <div class="status-alert status-pending">
                        ⌛ {{ $pendingEnrollmentTitle ?? 'Enrollment Pending' }}
                    </div>
                    <p class="text-sm text-center text-gray-600 mb-4">{{ $pendingEnrollmentMessage ?? 'Your application is currently under review by our team.' }}</p>
                    @if($pendingEnrollmentActionUrl)
                        <a href="{{ $pendingEnrollmentActionUrl }}" class="btn-enroll-action text-center block" style="text-decoration: none;">
                            {{ $pendingEnrollmentActionLabel }}
                        </a>
                    @else
                        <button class="btn-enroll-action opacity-50 cursor-not-allowed" disabled>
                            {{ $pendingEnrollmentActionLabel }}
                        </button>
                    @endif
                @else
                    <div class="price-tag">
                        ₱{{ number_format($course->price ?? 0, 2) }}
                    </div>

                    <div class="course-quick-info">
                        <div class="quick-info-row">
                            <span class="text-gray-500">License Category</span>
                            <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $course->license_type ?? 'N/A')) }}</span>
                        </div>
                        <div class="quick-info-row">
                            <span class="text-gray-500">Total Hours</span>
                            <span class="font-semibold">{{ $course->hours_required ?? $course->duration_hours ?? 0 }} hrs</span>
                        </div>
                    </div>

                    @if($isPracticalCourse)
                        @if(!$hasPassedTdc)
                            <div class="status-alert status-pending" style="text-align: left;">
                                <strong>Theoretical completion required.</strong>
                                <div class="mt-2">You must pass a Theoretical Driving Course (TDC) before enrolling in this practical course.</div>
                            </div>
                        @elseif($studentLicenseStatus === 'verified')
                            <div class="status-alert status-approved">
                                License verified. You can proceed with practical enrollment.
                            </div>
                        @elseif($studentLicenseStatus === 'pending')
                            <div class="status-alert status-pending">
                                License submitted and pending review. You can proceed with enrollment.
                            </div>
                        @endif
                    @endif

                    @if($mustPassTdcForPractical)
                        <button type="button" class="btn-enroll-action opacity-50 cursor-not-allowed" disabled title="Complete TDC first">
                            Complete TDC First
                        </button>
                    @else
                        <button type="button" class="btn-enroll-action" onclick="openEnrollModal()">
                            Enroll Now
                        </button>
                    @endif
                @endif

                <div class="mt-6 border-t pt-4">
                    <p class="text-xs text-gray-400 text-center">By enrolling, you agree to our Terms and Conditions and Data Privacy Policy.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enrollment Modal -->
<div id="enrollModal" class="modal-backdrop">
    <div class="modal-window">
        <div class="modal-header">
            <h3 class="text-xl font-bold">Enrollment Application</h3>
            <button onclick="closeEnrollModal()" class="text-2xl font-bold opacity-70 hover:opacity-100">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('schools.student.enroll', ['school' => $school->slug, 'course' => $course->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                @if($course->packages && $course->packages->count() > 0)
                <div class="form-group">
                    <label class="form-label">Choose Package</label>
                    <select name="package_id" class="form-select" required>
                        <option value="">Select a package...</option>
                        @foreach($course->packages as $package)
                            <option value="{{ $package->id }}">
                                {{ $package->name }} - ₱{{ number_format($package->price, 2) }} 
                                ({{ $package->transmission_type ? strtoupper($package->transmission_type) : '' }})
                                @if($package->training_hours) - {{ $package->training_hours }} hrs @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($enableBranches && $branches->count() > 0)
                <div class="form-group">
                    <label class="form-label">Preferred Branch</label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">Select branch...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">Experience Level</label>
                    <select name="experience_level" class="form-select" required id="exp_level">
                        <option value="new_driver">New Driver</option>
                        <option value="experienced">Experienced Driver</option>
                    </select>
                </div>

                @if($mustUploadLicenseForPractical)
                <div class="form-group">
                    <label class="form-label">Upload Student Driver's License</label>
                    <input type="file" name="student_license" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="text-gray-500">Required for practical enrollment. Accepted: PDF, JPG, PNG.</small>
                </div>
                @endif

                <div class="form-group" id="license_upload" style="display: none;">
                    <label class="form-label">Supporting Document (Optional)</label>
                    <input type="file" name="credential_file" class="form-control" accept="image/*,.pdf">
                    <small class="text-gray-500">Optional file for experienced drivers.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Additional Remarks (Optional)</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>

                <button type="submit" class="btn-submit">Submit Enrollment Request</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openEnrollModal() {
        document.getElementById('enrollModal').style.display = 'flex';
    }
    function closeEnrollModal() {
        document.getElementById('enrollModal').style.display = 'none';
    }

    document.getElementById('exp_level').addEventListener('change', function() {
        document.getElementById('license_upload').style.display = this.value === 'experienced' ? 'block' : 'none';
    });

    window.onclick = function(event) {
        if (event.target == document.getElementById('enrollModal')) {
            closeEnrollModal();
        }
    }
</script>

@endsection
