<?php

namespace App\Http\Controllers;

use App\Mail\LifecycleStatusUpdate;
use Illuminate\Http\Request;
use App\Models\EnrollmentRequest;
use App\Models\PhaseProgression;
use App\Models\School;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Mail\EnrollmentApproved;
use App\Mail\EnrollmentRejected;
use App\Models\Branch;
use App\Models\Notification;
use App\Models\SystemLog;

class EnrollmentRequestController extends Controller
{
    /**
     * Display all enrollment requests (Admin)
     */
    public function index(Request $request, School $school)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        $baseQuery = EnrollmentRequest::where('school_id', $school->id)
            ->with(['learner', 'course', 'package', 'approvedBy', 'branchRelation']);

        $admin->scopeToBranch($baseQuery);

        // Calculate stats BEFORE filtering
        $allRequestsCount = (clone $baseQuery)->count();
        $pendingRequestsCount = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedRequestsCount = (clone $baseQuery)->where('status', 'approved')->count();
        $completedRequestsCount = (clone $baseQuery)->where('status', 'completed')->count();
        $cancelledRequestsCount = (clone $baseQuery)->where('status', 'cancelled')->count();
        $rejectedRequestsCount = (clone $baseQuery)->where('status', 'rejected')->count();

        // Server-side filtering
        if ($request->filled('status') && $request->status !== 'all') {
            $baseQuery->where('status', $request->status);
        }

        if ($request->filled('branch')) {
            $baseQuery->whereHas('branchRelation', function ($q) use ($request) {
                $q->where('name', $request->branch);
            });
        }

        $allowedSorts = [
            'priority',
            'newest',
            'oldest',
            'withdrawal_first',
            'learner_az',
            'learner_za',
            'status_az',
            'status_za',
        ];

        $currentSort = $request->input('sort', 'priority');
        if (!in_array($currentSort, $allowedSorts, true)) {
            $currentSort = 'priority';
        }

        $learnerNameSubquery = Student::select('name')
            ->whereColumn('students.id', 'enrollment_requests.learner_id')
            ->limit(1);

        switch ($currentSort) {
            case 'priority':
                // Default triage order:
                // 1) Pending approvals (no withdrawal request)
                // 2) Withdrawal requests
                // 3) Remaining statuses
                $baseQuery->orderByRaw("CASE
                    WHEN status = 'pending' AND (cancellation_requested = 0 OR cancellation_requested IS NULL) THEN 0
                    WHEN cancellation_requested = 1 THEN 1
                    WHEN status = 'approved' THEN 2
                    WHEN status = 'revision_required' THEN 3
                    WHEN status = 'completed' THEN 4
                    WHEN status = 'rejected' THEN 5
                    WHEN status = 'cancelled' THEN 6
                    ELSE 7
                END")
                    ->orderBy('created_at', 'desc');
                break;

            case 'oldest':
                $baseQuery->orderBy('created_at', 'asc');
                break;

            case 'withdrawal_first':
                $baseQuery->orderBy('cancellation_requested', 'desc')
                    ->orderBy('created_at', 'desc');
                break;

            case 'learner_az':
                $baseQuery->orderBy($learnerNameSubquery, 'asc')
                    ->orderBy('created_at', 'desc');
                break;

            case 'learner_za':
                $baseQuery->orderBy($learnerNameSubquery, 'desc')
                    ->orderBy('created_at', 'desc');
                break;

            case 'status_az':
                $baseQuery->orderBy('status', 'asc')
                    ->orderBy('created_at', 'desc');
                break;

            case 'status_za':
                $baseQuery->orderBy('status', 'desc')
                    ->orderBy('created_at', 'desc');
                break;

            case 'newest':
            default:
                $baseQuery->orderBy('created_at', 'desc');
                break;
        }

        $allRequests = $baseQuery->paginate(20)->withQueryString();

        $branches = Branch::where('school_id', $school->id)->where('is_active', true)->orderBy('name')->get();

        $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';

        return view('school.admin.enrollment-requests.index', array_merge(compact(
            'school',
            'allRequests',
            'allRequestsCount',
            'pendingRequestsCount',
            'approvedRequestsCount',
            'completedRequestsCount',
            'cancelledRequestsCount',
            'rejectedRequestsCount',
            'admin',
            'branches',
            'currentSort',
        ), ['isAjax' => $request->ajax()]));
    }

    /**
     * Display detailed enrollment information (Admin)
     */
    public function show(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        // Security: verify belongs to this school
        abort_if($enrollmentRequest->school_id !== $school->id, 404);

        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Branch secretary access check
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id)) {
            abort(403, 'You do not have access to this enrollment request.');
        }

        $enrollmentRequest->load(['learner', 'course', 'approvedBy', 'sessionCompletions.instructor', 'payments', 'bookings']);

        // Calculate session hours summary
        $sessionSummary = [
            'total_sessions' => $enrollmentRequest->sessionCompletions->count(),
            'total_hours' => $enrollmentRequest->sessionCompletions->where('status', 'completed')->sum('hours_completed'),
            'theoretical_sessions' => $enrollmentRequest->sessionCompletions->where('session_type', 'theoretical')->count(),
            'practical_sessions' => $enrollmentRequest->sessionCompletions->where('session_type', 'practical')->count(),
            'hours_required' => $enrollmentRequest->course->hours_required ?? 0,
        ];

        $sessionSummary['progress_percentage'] = $sessionSummary['hours_required'] > 0
            ? min(100, round(($sessionSummary['total_hours'] / $sessionSummary['hours_required']) * 100, 1))
            : 0;

        // Get phase progressions for this enrollment
        $phaseProgressions = PhaseProgression::where('enrollment_id', $enrollmentRequest->id)
            ->with('reviewedBy')
            ->latest('requested_at')
            ->get();

        return view('school.admin.enrollment-requests.show', array_merge(compact(
            'school',
            'enrollmentRequest',
            'sessionSummary',
            'phaseProgressions'
        ), ['isAjax' => $request->ajax()]));
    }

    /**
     * Approve an enrollment request - changes guest to student and creates enrollment
     */
    public function approve(School $school, EnrollmentRequest $enrollmentRequest)
    {
        // Security Check 1: Verify the request belongs to this school
        if ($enrollmentRequest->school_id !== $school->id) {
            abort(404);
        }

        // Security Check 2: Verify admin is authenticated and belongs to this school
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Security Check: Branch secretary permission and access
        if (!$admin->canApproveEnrollments()) {
            abort(403, 'You do not have permission to approve enrollments.');
        }
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id)) {
            abort(403, 'You do not have access to this enrollment request.');
        }

        // Security Check 3: Prevent finalized or withdrawing approvals
        if (in_array($enrollmentRequest->status, ['approved', 'completed'])) {
            return redirect()
                ->back()
                ->with('error', 'This enrollment request has already been finalized.');
        }

        if (in_array($enrollmentRequest->status, ['cancelled', 'rejected'])) {
            return redirect()
                ->back()
                ->with('error', 'Cannot approve a cancelled or rejected enrollment request.');
        }

        if ($enrollmentRequest->cancellation_requested) {
            return redirect()
                ->back()
                ->with('error', 'Cannot approve an enrollment request that has a pending withdrawal request. Please resolve the withdrawal first.');
        }

        // Security Check 4: Verify the student still exists and is a guest
        if (!$enrollmentRequest->student) {
            return redirect()
                ->back()
                ->with('error', 'Student not found.');
        }

        if ($enrollmentRequest->student->role !== 'guest') {
            return redirect()
                ->back()
                ->with('error', 'This user is already a student.');
        }

        // Security Check 5: Verify student is not locked to another active course
        if ($enrollmentRequest->student->is_course_locked) {
            return redirect()
                ->back()
                ->with('error', 'This student is already enrolled in an active course. They must complete or cancel their current enrollment first.');
        }

        // Idempotent school-scoped single-active guard.
        $hasOtherActiveEnrollment = EnrollmentRequest::where('school_id', $school->id)
            ->where('learner_id', $enrollmentRequest->learner_id)
            ->where('status', 'approved')
            ->where('id', '!=', $enrollmentRequest->id)
            ->exists();

        if ($hasOtherActiveEnrollment) {
            return redirect()
                ->back()
                ->with('error', 'This student already has an active enrollment in this school.');
        }

        if ($enrollmentRequest->payment_status !== 'paid') {
            return redirect()
                ->back()
                ->with('error', 'Cannot approve enrollment before payment verification. Verify payment first.');
        }

        DB::beginTransaction();
        try {
            $this->processApproval($enrollmentRequest, $admin, $school);

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Enrollment approved and student account activated.'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Enrollment approved and student account activated.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve enrollment: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred while approving the enrollment.'
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'An unexpected error occurred while approving the enrollment. Please try again.');
        }
    }

    /**
     * Reject an enrollment request - keeps user as guest
     */
    public function reject(Request $req, School $school, EnrollmentRequest $enrollmentRequest)
    {
        $validated = $req->validate([
            'remarks' => ['required', 'string', 'max:1000'],
            'reject_license' => ['nullable'],
            'reject_payment' => ['nullable'],
        ]);

        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== (int) $school->id)
            abort(403);

        if (!$admin->canApproveEnrollments()) {
            abort(403, 'You do not have permission to reject enrollments.');
        }
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id)) {
            abort(403, 'You do not have access to this enrollment request.');
        }

        if ($enrollmentRequest->status === 'rejected') {
            if ($req->ajax())
                return response()->json(['success' => false, 'message' => 'This enrollment request has already been rejected.'], 422);
            return redirect()->back()->with('error', 'This enrollment request has already been rejected.');
        }

        if ($enrollmentRequest->status === 'approved') {
            if ($req->ajax())
                return response()->json(['success' => false, 'message' => 'Cannot reject an already approved enrollment request.'], 422);
            return redirect()->back()->with('error', 'Cannot reject an already approved enrollment request.');
        }

        $rejectLicense = filter_var($req->reject_license, FILTER_VALIDATE_BOOLEAN);
        $rejectPayment = filter_var($req->reject_payment, FILTER_VALIDATE_BOOLEAN);

        DB::beginTransaction();
        try {
            if ($rejectLicense || $rejectPayment) {
                // Partial Rejection Flow
                if ($rejectLicense) {
                    $enrollmentRequest->learner->update([
                        'student_license_status' => 'rejected',
                        'student_license_rejection_reason' => $validated['remarks']
                    ]);
                }
                if ($rejectPayment) {
                    $enrollmentRequest->update([
                        'payment_status' => 'rejected',
                        'payment_proof_path' => null,
                        'payment_reference' => null,
                        'remarks' => $validated['remarks']
                    ]);

                    // Mark old Payment records as rejected and clear unique reference
                    // so the guest can re-submit without hitting the unique constraint
                    $enrollmentRequest->payments()
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'rejected',
                            'reference' => null,
                            'normalized_reference' => null,
                        ]);
                }
                $messagePrefix = "Partial rejection processed.";
            } else {
                // Full Rejection Flow
                $enrollmentRequest->update([
                    'status' => 'rejected',
                    'payment_status' => 'cancelled',
                    'remarks' => $validated['remarks'],
                    'rejected_at' => now(),
                ]);

                // Also update learner license status to rejected in a full rejection
                if ($enrollmentRequest->learner) {
                    $enrollmentRequest->learner->update([
                        'student_license_status' => 'rejected',
                        'student_license_rejection_reason' => $validated['remarks']
                    ]);
                }
                $messagePrefix = "Enrollment request rejected.";
            }

            // Ensure model state is fresh for email/notification logic
            $enrollmentRequest->load('learner');

            // Send consolidated email (queued)
            try {
                Mail::to($enrollmentRequest->learner->email)
                    ->queue(new EnrollmentRejected($enrollmentRequest, $school, $validated['remarks'], $rejectLicense, $rejectPayment));
            } catch (\Exception $e) {
                Log::warning('Failed to queue enrollment rejection email: ' . $e->getMessage());
            }

            // In-app notification
            try {
                $this->sendInAppTransitionNotification(
                    'rejected',
                    $enrollmentRequest->student,
                    'enrollment_rejected',
                    'Enrollment Request Update',
                    "Your enrollment request for {$enrollmentRequest->course->title} was not approved. Check your email for details.",
                    'warning',
                    "/{$school->slug}/guest/enrollment-requests"
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send rejection in-app notification: ' . $e->getMessage());
            }

            DB::commit();

            if ($req->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $messagePrefix . ' User notified via email.'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', $messagePrefix . ' User notified via email.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject enrollment: ' . $e->getMessage());

            if ($req->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred while rejecting the enrollment.'
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'An unexpected error occurred while rejecting the enrollment.');
        }
    }

    /**
     * Update payment status (sub-status only, no role promotion).
     * Kept for "free/waived" overrides.
     */
    public function updatePaymentStatus(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        $validated = $request->validate([
            'payment_status' => ['required', 'in:pending,on_hold,paid,partial,rejected,revision_required'],
            'payment_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Security Check etc... (skipped for brevity in replace, but keeping logic)
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== (int) $school->id)
            abort(403);
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id))
            abort(403);

        DB::beginTransaction();
        try {
            $enrollmentRequest->update([
                'payment_status' => $validated['payment_status'],
                'payment_confirmed_by' => $validated['payment_status'] === 'paid' ? $admin->id : $enrollmentRequest->payment_confirmed_by,
                'payment_confirmed_at' => $validated['payment_status'] === 'paid' ? now() : $enrollmentRequest->payment_confirmed_at,
                'payment_confirmation_notes' => $validated['payment_notes'] ?? null,
            ]);

            // Role promotion is handled exclusively by the enrollment approval path.
            // No promotion here.

            DB::commit();
            return redirect()->back()->with('success', 'Payment status updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Update failed.');
        }
    }

    /**
     * Complete an enrollment (mark as finished)
     */
    public function complete(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        // Security checks
        if ($enrollmentRequest->school_id !== $school->id) {
            abort(404);
        }

        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($enrollmentRequest->status !== 'approved') {
            return redirect()
                ->back()
                ->with('error', 'Only active enrollments can be marked as completed.');
        }

        DB::beginTransaction();
        try {
            $enrollmentRequest->complete($admin->id);

            // Unlock student from course so they can enroll in a new one
            if ($enrollmentRequest->student) {
                $enrollmentRequest->student->update(['is_course_locked' => false]);

                // Notify student about enrollment completion
                try {
                    $notificationMessage = "Congratulations! You have successfully completed {$enrollmentRequest->course->title}.";

                    $this->sendInAppTransitionNotification(
                        'enrollment_completed',
                        $enrollmentRequest->student,
                        'enrollment_completed',
                        'Course Completed!',
                        $notificationMessage,
                        'success',
                        "/{$school->slug}/student/my-progress"
                    );

                    $this->sendLifecycleTransitionEmail(
                        'enrollment_completed',
                        $enrollmentRequest->student,
                        $school,
                        'Course Completed!',
                        $notificationMessage,
                        'schools.student.my-progress',
                        'View Progress'
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send enrollment completion notification: ' . $e->getMessage());
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Enrollment marked as completed successfully.'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Enrollment marked as completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete enrollment: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred while completing the enrollment.'
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'An unexpected error occurred while completing the enrollment.');
        }
    }

    /**
     * Cancel an enrollment
     */
    public function cancel(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        // Security checks
        if ($enrollmentRequest->school_id !== $school->id) {
            abort(404);
        }

        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($enrollmentRequest->status, ['pending', 'approved'])) {
            return redirect()
                ->back()
                ->with('error', 'Only pending or active enrollments can be cancelled.');
        }

        DB::beginTransaction();
        try {
            $wasApproved = $enrollmentRequest->status === 'approved';
            $finalRemarks = $validated['remarks'] ?? null;

            if ($enrollmentRequest->status === 'pending' && $enrollmentRequest->cancellation_requested) {
                $guestReason = trim((string) $enrollmentRequest->cancellation_reason);
                $finalRemarks = $finalRemarks ?: 'Cancelled upon guest withdrawal request.';

                if ($guestReason !== '') {
                    $finalRemarks .= ' Guest reason: ' . $guestReason;
                }
            }

            $enrollmentRequest->cancel($finalRemarks);

            // Also update learner license status to 'none' if it was pending
            if ($enrollmentRequest->learner && $enrollmentRequest->learner->student_license_status === 'pending') {
                $enrollmentRequest->learner->update(['student_license_status' => 'none']);
            }

            // Also ensure payment status is cancelled
            $enrollmentRequest->update(['payment_status' => 'cancelled']);

            if ($enrollmentRequest->cancellation_requested) {
                $enrollmentRequest->update([
                    'cancellation_requested' => false,
                ]);
            }

            // Unlock student from course if they had an active enrollment
            if ($wasApproved && $enrollmentRequest->student) {
                $enrollmentRequest->student->update(['is_course_locked' => false]);
            }

            // Notify student about cancellation
            if ($enrollmentRequest->student) {
                try {
                    $isGuest = $enrollmentRequest->student->isGuest();
                    $reason = $finalRemarks ? " Reason: {$finalRemarks}" : '';
                    $notificationMessage = "Your enrollment for {$enrollmentRequest->course->title} has been cancelled.{$reason}";

                    $inAppActionUrl = $isGuest
                        ? "/{$school->slug}/guest/enrollment-requests"
                        : "/{$school->slug}/student";

                    $emailActionRoute = $isGuest
                        ? 'schools.guest.enrollmentRequests'
                        : 'schools.student.dashboard';

                    $emailActionLabel = $isGuest
                        ? 'View Requests'
                        : 'View Dashboard';

                    $this->sendInAppTransitionNotification(
                        'enrollment_cancelled',
                        $enrollmentRequest->student,
                        'enrollment_cancelled',
                        'Enrollment Cancelled',
                        $notificationMessage,
                        'warning',
                        $inAppActionUrl
                    );

                    $this->sendLifecycleTransitionEmail(
                        'enrollment_cancelled',
                        $enrollmentRequest->student,
                        $school,
                        'Enrollment Cancelled',
                        $notificationMessage,
                        $emailActionRoute,
                        $emailActionLabel
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send cancellation notification: ' . $e->getMessage());
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Enrollment cancelled successfully.'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Enrollment cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel enrollment: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred while cancelling the enrollment.'
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'An unexpected error occurred while cancelling the enrollment.');
        }
    }

    /**
     * Mark theoretical portion as passed
     */
    public function markTheoreticalPassed(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Security checks
        if ($enrollmentRequest->school_id !== $school->id) {
            abort(404);
        }

        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($enrollmentRequest->status !== 'approved') {
            return redirect()
                ->back()
                ->with('error', 'Only active enrollments can have theoretical status updated.');
        }

        DB::beginTransaction();
        try {
            // Validate LTO requirements (15h/3d rule)
            $validation = \App\Support\EnrollmentValidator::canMarkTheoreticalPassed($enrollmentRequest);
            if (!$validation['allowed']) {
                return redirect()
                    ->back()
                    ->with('error', $validation['message']);
            }

            $enrollmentRequest->markTheoreticalPassed($admin->id, $validated['notes'] ?? null);

            // Notify student about theoretical completion
            if ($enrollmentRequest->student) {
                try {
                    $notificationMessage = "You have passed the theoretical portion for {$enrollmentRequest->course->title}. You may now proceed to practical training.";

                    $this->sendInAppTransitionNotification(
                        'theoretical_passed',
                        $enrollmentRequest->student,
                        'theoretical_passed',
                        'Theoretical Exam Passed!',
                        $notificationMessage,
                        'success',
                        "/{$school->slug}/student/my-course"
                    );

                    $this->sendLifecycleTransitionEmail(
                        'theoretical_passed',
                        $enrollmentRequest->student,
                        $school,
                        'Theoretical Exam Passed!',
                        $notificationMessage,
                        'schools.student.my-course',
                        'View Course'
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send theoretical passed notification: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Theoretical portion marked as passed.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark theoretical passed: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'An unexpected error occurred while updating theoretical status.');
        }
    }

    /**
     * Bulk approve enrollment requests
     */
    public function bulkApprove(Request $request, School $school)
    {
        $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollment_requests,id',
        ]);

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        $approved = 0;

        DB::beginTransaction();
        try {
            foreach ($request->enrollment_ids as $id) {
                /** @var \App\Models\EnrollmentRequest|null $enrollment */
                $enrollment = EnrollmentRequest::find($id);

                if (!$enrollment instanceof EnrollmentRequest) {
                    continue;
                }

                // Skip if not belonging to this school
                if ((int) $enrollment->school_id !== (int) $school->id) {
                    continue;
                }

                // Skip finalized states or those with pending withdrawals
                if (in_array((string) $enrollment->status, ['approved', 'rejected', 'cancelled', 'completed'], true)) {
                    continue;
                }

                if ($enrollment->cancellation_requested) {
                    continue;
                }

                $student = $enrollment->student;
                if (!$student) {
                    continue;
                }

                // Skip if student is already active
                if ($student->role !== 'guest') {
                    continue;
                }

                // Skip if student is locked to another course
                if ($student->is_course_locked) {
                    continue;
                }

                // Strict two-step gate: only verified/paid enrollments can be approved.
                if ((string) $enrollment->payment_status !== 'paid') {
                    continue;
                }

                $this->processApproval($enrollment, $admin, $school);

                $approved++;
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', "Successfully approved {$approved} enrollment request(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk approval failed: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'An unexpected error occurred during bulk approval. Please try again.');
        }
    }

    /**
     * Bulk reject enrollment requests
     */
    public function bulkReject(Request $request, School $school)
    {
        $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollment_requests,id',
            'remarks' => 'required|string|max:1000',
        ]);

        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        $rejected = 0;

        DB::beginTransaction();
        try {
            foreach ($request->enrollment_ids as $id) {
                $enrollment = EnrollmentRequest::find($id);

                if (!$enrollment instanceof EnrollmentRequest) {
                    continue;
                }

                // Skip if not belonging to this school
                if ((int) $enrollment->school_id !== (int) $school->id) {
                    continue;
                }

                // Skip if already rejected or approved
                if (in_array((string) $enrollment->status, ['rejected', 'approved'])) {
                    continue;
                }

                $enrollment->update([
                    'status' => 'rejected',
                    'payment_status' => 'cancelled',
                    'remarks' => $request->remarks,
                    'rejected_at' => now(),
                ]);

                // Send rejection email
                try {
                    if ($this->transitionUsesChannel('rejected', 'email')) {
                        Mail::to($enrollment->learner->email)
                            ->queue(new EnrollmentRejected($enrollment, $school));
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to send bulk rejection email: ' . $e->getMessage());
                }

                // Create in-app notification
                $this->sendInAppTransitionNotification(
                    'rejected',
                    $enrollment->student,
                    'enrollment_rejected',
                    'Enrollment Request Update',
                    "Your enrollment request for {$enrollment->course->title} was not approved.",
                    'warning',
                    "/{$school->slug}/guest/enrollment-requests"
                );

                $rejected++;
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', "Successfully rejected {$rejected} enrollment request(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk rejection failed: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'An unexpected error occurred during bulk rejection. Please try again.');
        }
    }

    /**
     * Verify a student's driver license
     */
    public function verifyLicense(Request $request, School $school, Student $student)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        // Ensure the student belongs to this school
        if ($student->school_id !== $school->id) {
            abort(403, 'Student does not belong to this school.');
        }

        if ($student->student_license_status !== 'pending') {
            return redirect()->back()->with('warning', 'This license is not pending verification.');
        }

        $student->update([
            'student_license_status' => 'verified',
            'student_license_verified_at' => now(),
            'student_license_verified_by' => $admin->id,
            'student_license_rejection_reason' => null,
        ]);

        // Role promotion is handled exclusively by the enrollment approval path.
        // verifyLicense only updates the license sub-status.

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'License verified successfully!']);
        }

        // Notify the student
        $notificationMessage = "Your student driver's license has been verified. You can now enroll in Practical Driving Courses (PDC).";

        $this->sendInAppTransitionNotification(
            'license_verified',
            $student,
            'license_verified',
            'License Verified!',
            $notificationMessage,
            'success',
            "/{$school->slug}/guest/courses"
        );

        $this->sendLifecycleTransitionEmail(
            'license_verified',
            $student,
            $school,
            'License Verified!',
            $notificationMessage,
            'schools.guest.courses',
            'Browse Courses'
        );

        return redirect()->back()->with('success', "Student driver's license for {$student->name} has been verified.");
    }

    /**
     * Reject a student's driver license
     */
    public function rejectLicense(Request $request, School $school, Student $student)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($student->school_id !== $school->id) {
            abort(403, 'Student does not belong to this school.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($student->student_license_status !== 'pending') {
            return redirect()->back()->with('warning', 'This license is not pending verification.');
        }

        $student->update([
            'student_license_status' => 'rejected',
            'student_license_rejection_reason' => $request->rejection_reason,
            'student_license_verified_at' => null,
            'student_license_verified_by' => null,
        ]);

        // Notify the student
        $notificationMessage = "Your student driver's license was not approved: {$request->rejection_reason}";

        $this->sendInAppTransitionNotification(
            'license_rejected',
            $student,
            'license_rejected',
            'License Not Approved',
            $notificationMessage,
            'warning',
            "/{$school->slug}/guest/dashboard"
        );

        $this->sendLifecycleTransitionEmail(
            'license_rejected',
            $student,
            $school,
            'License Not Approved',
            $notificationMessage,
            'schools.guest.dashboard',
            'View Dashboard'
        );

        return redirect()->back()->with('success', "Student driver's license for {$student->name} has been rejected.");
    }

    /**
     * Shared single-source approval domain path.
     * Called by both approve() and bulkApprove().
     * Atomically: sets enrollment approved + promotes guest to student + locks course.
     */
    private function processApproval(EnrollmentRequest $enrollmentRequest, $admin, School $school): void
    {
        $hasOtherActiveEnrollment = EnrollmentRequest::where('school_id', $school->id)
            ->where('learner_id', $enrollmentRequest->learner_id)
            ->where('status', 'approved')
            ->where('id', '!=', $enrollmentRequest->id)
            ->lockForUpdate()
            ->exists();

        if ($hasOtherActiveEnrollment) {
            throw new \RuntimeException('Single-active rule violation: student already has another approved enrollment.');
        }

        $enrollmentRequest->update([
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'enrolled_at' => now(),
        ]);

        $student = $enrollmentRequest->student;
        if ($student) {
            // Atomic role promotion: guest -> student
            if ($student->role === 'guest') {
                $student->promoteToStudent();
            }
            $studentUpdate = ['is_course_locked' => true];
            if (!empty($enrollmentRequest->requested_dl_code)) {
                $studentUpdate['dl_code'] = $enrollmentRequest->requested_dl_code;
            }
            $student->update($studentUpdate);

            // Promote staged license if present
            if ($enrollmentRequest->credentials_file_path) {
                $stagedPath = $enrollmentRequest->credentials_file_path;
                $newFileName = basename($stagedPath);
                $permanentPath = 'student-licenses/' . $newFileName;

                try {
                    if (Storage::disk('local')->exists($stagedPath)) {
                        Storage::disk('local')->move($stagedPath, $permanentPath);

                        $student->update([
                            'student_license_path' => $permanentPath,
                            'student_license_status' => 'verified',
                            'student_license_verified_at' => now(),
                            'student_license_verified_by' => $admin->id,
                        ]);

                        // Update request to reflect new permanent path (optional but good for audit)
                        $enrollmentRequest->update(['credentials_file_path' => $permanentPath]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to promote staged license: ' . $e->getMessage());
                }
            }
        }

        // Send approval email
        try {
            if ($this->transitionUsesChannel('approved', 'email')) {
                Mail::to($enrollmentRequest->learner->email)
                    ->queue(new EnrollmentApproved($enrollmentRequest, $school));
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send enrollment approval email: ' . $e->getMessage());
        }

        // In-app notification
        $this->sendInAppTransitionNotification(
            'approved',
            $enrollmentRequest->student,
            'enrollment_approved',
            'Enrollment Approved!',
            "Your enrollment for {$enrollmentRequest->course->title} has been approved. Welcome aboard!",
            'success',
            "/{$school->slug}/student"
        );
    }

    private function transitionUsesChannel(string $transition, string $channel): bool
    {
        $channels = config("notification_policy.enrollment_transitions.{$transition}.channels", []);

        return in_array($channel, $channels, true);
    }

    private function sendInAppTransitionNotification(
        string $transition,
        Student $student,
        string $type,
        string $title,
        string $message,
        string $icon,
        string $actionUrl,
    ): void {
        if (!$this->transitionUsesChannel($transition, 'in_app')) {
            return;
        }

        Notification::send(
            $student,
            $type,
            $title,
            $message,
            $icon,
            $actionUrl
        );
    }

    private function sendLifecycleTransitionEmail(
        string $transition,
        Student $student,
        School $school,
        string $title,
        string $message,
        ?string $actionRoute = null,
        ?string $actionLabel = null,
    ): void {
        if (!config('notification_policy.enable_lifecycle_transition_emails', false)) {
            return;
        }

        $disabledTransitions = config('notification_policy.disabled_lifecycle_email_transitions', []);
        if (in_array($transition, $disabledTransitions, true)) {
            return;
        }

        if (!$this->transitionUsesChannel($transition, 'email')) {
            return;
        }

        $actionUrl = $actionRoute ? route($actionRoute, ['school' => $school]) : null;

        Mail::to($student->email)->queue(
            new LifecycleStatusUpdate(
                $school,
                $student->name,
                $title,
                $message,
                $actionUrl,
                $actionLabel,
            )
        );
    }

    /**
     * View a student's uploaded driver license file
     */
    public function viewLicense(Request $request, School $school, Student $student)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($student->school_id !== $school->id) {
            abort(403, 'Student does not belong to this school.');
        }

        // 1. Direct Disk Check (Public then Local)
        if (!empty($student->student_license_path)) {
            $path = $student->student_license_path;

            foreach (['public', 'local'] as $disk) {
                if (Storage::disk($disk)->exists($path)) {
                    $fullPath = Storage::disk($disk)->path($path);
                    return response()->file($fullPath, [
                        'Content-Disposition' => 'inline; filename="' . ($student->student_license_filename ?? basename($path)) . '"',
                    ]);
                }
            }
        }

        // 2. Legacy Base64 data (Old Method)
        if (!empty($student->student_license_data)) {
            $decodedData = base64_decode($student->student_license_data, true);

            if ($decodedData !== false) {
                $licenseFilename = $student->student_license_filename ?: "student-license-{$student->id}";
                $safeFilename = str_replace(["\r", "\n", '"'], '', basename($licenseFilename));

                return response($decodedData, 200, [
                    'Content-Type' => $student->student_license_mime_type ?: 'application/octet-stream',
                    'Content-Disposition' => 'inline; filename="' . $safeFilename . '"',
                ]);
            }
        }

        // 3. Last Resort: Try Candidate Paths (Messy/Partial paths)
        $storedPath = trim((string) $student->student_license_path);
        if ($storedPath !== '') {
            $normalizedPath = ltrim(parse_url($storedPath, PHP_URL_PATH) ?: $storedPath, '/');
            $fileName = basename($normalizedPath);

            $candidates = array_values(array_filter(array_unique([
                $storedPath,
                $normalizedPath,
                Str::after($normalizedPath, 'storage/'),
                Str::after($normalizedPath, 'public/'),
                $fileName ? 'student-licenses/' . $fileName : null,
            ])));

            foreach (['public', 'local'] as $disk) {
                foreach ($candidates as $candidatePath) {
                    if (Storage::disk($disk)->exists($candidatePath)) {
                        $fullPath = Storage::disk($disk)->path($candidatePath);
                        return response()->file($fullPath);
                    }
                }
            }
        }

        Log::warning('Student license file not found during admin view', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'stored_path' => $student->student_license_path,
        ]);

        abort(404, 'License file not found.');
    }

    /**
     * Get JSON data for the enrollment verification modal
     */
    public function apiShow(School $school, EnrollmentRequest $enrollmentRequest)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== (int) $school->id)
            abort(403);
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id))
            abort(403);

        $enrollmentRequest->load(['learner', 'course', 'package', 'payments']);

        // Fallback: if legacy enrollment receipt fields were cleared (e.g. after a
        // partial rejection) but a Payment record exists, surface the latest
        // non-rejected payment so the admin always sees the receipt.
        $latestPayment = $enrollmentRequest->payments
            ->whereNotNull('proof_of_payment_path')
            ->where('proof_of_payment_path', '!=', '')
            ->where('status', '!=', 'rejected')
            ->sortByDesc('created_at')
            ->first();
        if ($latestPayment) {
            if (empty($enrollmentRequest->payment_proof_path)) {
                $enrollmentRequest->payment_proof_path = $latestPayment->proof_of_payment_path;
            }
            if (empty($enrollmentRequest->payment_reference)) {
                $enrollmentRequest->payment_reference = $latestPayment->reference ?? $latestPayment->or_number;
            }
            if (empty($enrollmentRequest->payment_method)) {
                $enrollmentRequest->payment_method = $latestPayment->method;
            }
        }

        $displayPrice = (float) ($enrollmentRequest->price ?? 0);
        if ($displayPrice <= 0) {
            $displayPrice = (float) ($enrollmentRequest->package->price ?? ($enrollmentRequest->course->price ?? 0));
        }

        $hasStoredLicense = !empty($enrollmentRequest->learner->student_license_path)
            || !empty($enrollmentRequest->learner->student_license_data);
        $hasStagedLicense = !empty($enrollmentRequest->credentials_file_path);

        $isLicenseSubmittedForReview = in_array(
            $enrollmentRequest->learner->student_license_status,
            ['pending', 'verified', 'rejected'],
            true
        );

        $licenseUrl = null;
        if ($hasStagedLicense) {
            $licenseUrl = route('schools.admin.enrollments.view-credential', ['school' => $school->slug, 'enrollmentRequest' => $enrollmentRequest->id]);
        } elseif ($hasStoredLicense && $isLicenseSubmittedForReview) {
            $licenseUrl = route('schools.admin.enrollments.viewLicense', ['school' => $school->slug, 'student' => $enrollmentRequest->learner->id]);
        }

        return response()->json([
            'id' => $enrollmentRequest->id,
            'student_name' => $enrollmentRequest->learner->name,
            'course_title' => $enrollmentRequest->course->title,
            'total_price' => number_format($displayPrice, 2),
            // Statuses
            'status' => $enrollmentRequest->status,
            'payment_status' => $enrollmentRequest->payment_status,
            'license_status' => $enrollmentRequest->learner->student_license_status,
            // Data
            'payment_method' => $enrollmentRequest->payment_method,
            'reference_number' => $enrollmentRequest->payment_reference,
            'requested_dl_code' => $enrollmentRequest->requested_dl_code,
            'remarks' => $enrollmentRequest->remarks,
            'cancellation_requested' => (bool) $enrollmentRequest->cancellation_requested,
            'cancellation_reason' => $enrollmentRequest->cancellation_reason,
            // Paths/Routes
            'license_url' => $licenseUrl,
            'receipt_url' => $enrollmentRequest->payment_proof_path
                ? route('schools.admin.enrollments.view-payment-proof', ['school' => $school->slug, 'enrollmentRequest' => $enrollmentRequest->id])
                : null,
            // Verification Endpoints
            'verify_payment_url' => route('schools.admin.enrollments.api.verify-payment', ['school' => $school->slug, 'enrollmentRequest' => $enrollmentRequest->id]),
            'verify_license_url' => route('schools.admin.enrollments.api.verify-license', ['school' => $school->slug, 'enrollmentRequest' => $enrollmentRequest->id]),
            'reject_url' => route('schools.admin.enrollments.reject', ['school' => $school->slug, 'enrollmentRequest' => $enrollmentRequest->id]),
        ]);
    }

    /**
     * Unified approval: Verifies payment AND approves enrollment
     * (Role promotion, course locking, payment completion, and state transition)
     */
    public function unifiedApprove(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== (int) $school->id)
            abort(403);
        if (!$admin->canApproveEnrollments())
            abort(403);
        if ($enrollmentRequest->school_id !== (int) $school->id)
            abort(404);
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id))
            abort(403);

        $hasOtherActiveEnrollment = EnrollmentRequest::where('school_id', $school->id)
            ->where('learner_id', $enrollmentRequest->learner_id)
            ->where('status', 'approved')
            ->where('id', '!=', $enrollmentRequest->id)
            ->exists();

        if ($hasOtherActiveEnrollment) {
            $message = 'Cannot approve: learner already has another active enrollment in this school.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->back()->with('error', $message);
        }

        if ($enrollmentRequest->payment_status !== 'paid') {
            $message = 'Cannot approve enrollment before payment verification. Verify payment first.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->back()->with('error', $message);
        }

        DB::beginTransaction();
        try {
            // Keep payment record lifecycle aligned when approval occurs post-verification.
            $enrollmentRequest->payments()->where('status', 'pending')->update([
                'status' => 'approved',
                'received_by_admin_id' => $admin->id,
                'received_at' => now(),
                'updated_at' => now(),
            ]);

            // Perform Enrollment Approval (Role Promotion, Course Locking)
            if ($enrollmentRequest->status !== 'approved') {
                $this->processApproval($enrollmentRequest, $admin, $school);
            }

            // Auto-verify license (if it's still pending).
            $student = $enrollmentRequest->student;
            if ($student && $student->student_license_status === 'pending') {
                $student->update([
                    'student_license_status' => 'verified',
                    'student_license_verified_at' => now(),
                    'student_license_verified_by' => $admin->id,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Enrollment fully approved! Student has been activated.'
                ]);
            }

            return redirect()->back()->with('success', 'Enrollment fully approved! Student has been activated.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('UnifiedApprove failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unified approval failed: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify payment via AJAX (legacy route, kept for backward compatibility)
     */
    public function verifyPayment(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== (int) $school->id)
            abort(403);
        if (!$admin->canApproveEnrollments())
            abort(403);
        if ($enrollmentRequest->school_id !== (int) $school->id)
            abort(404);
        if ($admin->isBranchSecretary() && !$admin->canAccessBranch($enrollmentRequest->branch_id))
            abort(403);

        $hasLegacyProof = !empty($enrollmentRequest->payment_proof_path)
            && $enrollmentRequest->payment_status !== 'rejected';

        $hasValidPaymentProof = $enrollmentRequest->payments()
            ->whereNotNull('proof_of_payment_path')
            ->where('proof_of_payment_path', '!=', '')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'rejected');
            })
            ->exists();

        if (!$hasLegacyProof && !$hasValidPaymentProof) {
            return response()->json([
                'success' => false,
                'message' => 'Verification rejected: no valid non-rejected payment proof found.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $enrollmentRequest->update([
                'payment_status' => 'paid',
                'payment_confirmed_by' => $admin->id,
                'payment_confirmed_at' => now(),
            ]);

            // Sync related Payment records (F-001 Lifecycle Parity)
            $enrollmentRequest->payments()->where('status', 'pending')->update([
                'status' => 'approved',
                'received_by_admin_id' => $admin->id,
                'received_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment verified successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('verifyPayment failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Verification failed.'], 500);
        }
    }

    /**
     * Verify license via AJAX (API route accepts EnrollmentRequest, not Student).
     * Resolves student from the enrollment request and delegates to license verification.
     */
    public function apiVerifyLicense(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== (int) $school->id)
            abort(403);
        if (!$admin->canApproveEnrollments())
            abort(403);

        $student = $enrollmentRequest->student;
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found for this enrollment.'], 404);
        }

        if ($student->school_id !== $school->id) {
            return response()->json(['success' => false, 'message' => 'Student does not belong to this school.'], 403);
        }

        if ($student->student_license_status !== 'pending' && $student->student_license_status !== 'none') {
            return response()->json(['success' => false, 'message' => 'License is not in a verifiable state.'], 422);
        }

        $student->update([
            'student_license_status' => 'verified',
            'student_license_verified_at' => now(),
            'student_license_verified_by' => $admin->id,
            'student_license_rejection_reason' => null,
        ]);

        // Role promotion is handled exclusively by the enrollment approval path.
        // apiVerifyLicense only updates the license sub-status.

        return response()->json(['success' => true, 'message' => 'License verified successfully!']);
    }

    /**
     * View a student's uploaded payment proof
     */
    public function viewPaymentProof(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== (int) $school->id)
            abort(403);

        $path = $enrollmentRequest->payment_proof_path;
        if (empty($path)) {
            // Fallback to latest non-rejected Payment record (ledger is source of truth).
            $fallback = $enrollmentRequest->payments()
                ->whereNotNull('proof_of_payment_path')
                ->where('proof_of_payment_path', '!=', '')
                ->where('status', '!=', 'rejected')
                ->latest()
                ->first();
            if ($fallback) {
                $path = $fallback->proof_of_payment_path;
            }
        }
        if (empty($path))
            abort(404, 'No payment proof path found for this enrollment.');

        // Try both public and local disks for robustness
        foreach (['public', 'local'] as $diskName) {
            if (Storage::disk($diskName)->exists($path)) {
                $fullPath = Storage::disk($diskName)->path($path);

                // Set appropriate filename for display/download
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $filename = "payment-receipt-{$enrollmentRequest->id}.{$extension}";

                return response()->file($fullPath, [
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                ]);
            }
        }

        Log::warning('Payment proof file not found during admin view', [
            'enrollment_id' => $enrollmentRequest->id,
            'path' => $path
        ]);

        abort(404, 'Payment proof file not found on any configured disk.');
    }

    /**
     * View a staged student license (credential) file
     */
    public function viewCredential(Request $request, School $school, EnrollmentRequest $enrollmentRequest)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== (int) $school->id)
            abort(403);

        $path = $enrollmentRequest->credentials_file_path;
        if (empty($path))
            abort(404, 'No credential file found for this enrollment.');

        foreach (['public', 'local'] as $diskName) {
            if (Storage::disk($diskName)->exists($path)) {
                $fullPath = Storage::disk($diskName)->path($path);
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $filename = "staged-license-{$enrollmentRequest->id}.{$extension}";

                return response()->file($fullPath, [
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                ]);
            }
        }

        abort(404, 'Credential file not found on any configured disk.');
    }
}
