<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\EnrollmentRequest;
use App\Models\GCashSetting;
use App\Models\Payment;
use App\Models\School;
use App\Services\PaymentSubmissionService;
use App\Services\ReceiptStorageService;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of payments.
     */
    public function index(Request $request, School $school, ReceiptStorageService $storageService)
    {
        // Require authentication
        if (!Auth::guard('admin')->check() && !Auth::guard('student')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $query = Payment::where('school_id', '=', $school->id)
            ->with(['payer', 'booking.course', 'enrollmentRequest.course']);

        // Filter by role - students can only see their own payments
        if (Auth::guard('student')->check()) {
            $studentId = Auth::guard('student')->id();
            $query->where('payer_user_id', $studentId);
        }
        elseif (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            if ($admin instanceof Admin && $admin->isBranchSecretary() && $admin->branch_id) {
                // Use the new branch_id field for reliable scoping across all payment types
                $query->where('branch_id', '=', $admin->branch_id);
            }
        }

        if (request('enrollment_id')) {
            $query->where('enrollment_request_id', '=', request('enrollment_id'));
        }

        if (request('status')) {
            $query->where('status', '=', request('status'));
        }

        if (request('method')) {
            $query->where('method', '=', request('method'));
        }

        $payments = $query->latest('paid_on')->paginate(10);

        // Append temporary URLs for GCash receipts
        $payments->getCollection()->transform(function ($payment) use ($storageService) {
            if ($payment->method === 'gcash' && $payment->proof_of_payment_path) {
                $payment->proof_url = $storageService->getUrl($payment->proof_of_payment_path);
            }
            return $payment;
        });

        // Pre-compute statistics for display
        $stats = [
            'total_revenue' => (clone $query)->where('status', 'approved')->sum('amount'),
            'approved_count' => (clone $query)->where('status', 'approved')->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
        ];

        $pendingEnrollments = collect();
        $pendingBookings = collect();
        $gcashPaymentImageUrl = null;

        if (Auth::guard('student')->check()) {
            $studentId = Auth::guard('student')->id();
            $student = Auth::guard('student')->user();

            $activeGcashSetting = GCashSetting::getActiveSetting($school->id, $student?->branch_id);
            if ($activeGcashSetting && $activeGcashSetting->is_active && !empty($activeGcashSetting->qr_path)) {
                $gcashPaymentImageUrl = route('schools.guest.storage.gcash-qr', [
                    'school' => $school,
                    'gcashSetting' => $activeGcashSetting,
                ]);
            }

            $pendingEnrollments = EnrollmentRequest::where('learner_id', $studentId)
                ->whereIn('payment_status', ['pending', 'partial', 'on_hold', 'rejected', 'revision_required'])
                ->with('course')
                ->withCount([
                    'payments as active_payment_records_count' => function ($query) {
                        $query->whereIn('status', ['pending', 'approved']);
                    }
                ])
                ->get();

            $pendingBookings = Booking::where('student_id', $studentId)
                ->whereIn('payment_status', ['pending', 'partial', 'on_hold', 'rejected', 'revision_required'])
                ->with('course')
                ->withCount([
                    'payment as active_payment_record_count' => function ($query) {
                        $query->whereIn('status', ['pending', 'approved']);
                    }
                ])
                ->get();
        }

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'payments' => $payments
            ]);
        }

        // Only admin and student have payment views
        $guard = Auth::guard('admin')->check() ? 'admin' : 'student';
        $view = "school.{$guard}.payments";
        return view($view, array_merge(
            compact('school', 'payments', 'stats', 'pendingEnrollments', 'pendingBookings', 'gcashPaymentImageUrl'),
            ['isAjax' => $request->ajax()]
        ));
    }

    /**
     * Store a newly created payment (GCash or On-site).
     */
    public function store(Request $request, School $school, PaymentSubmissionService $submissionService, ReceiptStorageService $storageService)
    {
        $isAdmin = Auth::guard('admin')->check();
        $student = Auth::guard('student')->user();
        $studentId = $student?->id;

        $validated = $request->validate([
            'method' => 'required|in:gcash,on_site',
            'amount' => 'required|numeric|min:1',
            'booking_id' => [
                'nullable',
                Rule::exists('bookings', 'id')->where('school_id', $school->id)
            ],
            'enrollment_request_id' => [
                'nullable',
                Rule::exists('enrollment_requests', 'id')->where('school_id', $school->id)
            ],
            
            // GCash fields (13-digit numeric reference)
            'reference' => 'required_if:method,gcash|nullable|string|regex:/^[0-9]{13}$/',
            'proof_of_payment' => 'required_if:method,gcash|nullable|image|max:5120',

            // On-site fields (1-15 digit OR number)
            'or_number' => 'required_if:method,on_site|nullable|string|regex:/^[0-9]{1,15}$/',
        ], [
            'reference.required_if' => 'GCash reference number is required.',
            'reference.regex' => 'GCash reference number must be exactly 13 digits (numbers only).',
            'or_number.required_if' => 'OR number is required for on-site payments.',
            'or_number.regex' => 'OR number must be 1 to 15 digits (numbers only).',
            'proof_of_payment.required_if' => 'Please upload your receipt screenshot.',
            'amount.min' => 'Amount must be at least ₱1.',
        ]);

        // Forensic XOR Linkage check
        if (empty($validated['booking_id']) && empty($validated['enrollment_request_id'])) {
            return $this->paymentErrorResponse(
                $request,
                'Payment must be linked to a booking or enrollment.',
                422,
                'error'
            );
        }

        $data = $validated;
        $data['school_id'] = $school->id;
        $linkedPayerId = $studentId;
        $enrollment = null;
        $booking = null;
        $paymentConcierge = [
            'allow_submission' => true,
            'revision_mode' => false,
            'level' => 'info',
            'message' => '',
        ];

        // Determine branch and check ownership
        // Layer 2: Fail-Closed Retrieval
        if (!empty($validated['booking_id'])) {
            $booking = $school->bookings()->findOrFail($validated['booking_id']);
            $linkedPayerId = (int) $booking->student_id;
            if (!$isAdmin && $linkedPayerId !== (int) $studentId) {
                abort(403);
            }
            $data['branch_id'] = $booking->branch_id;
            $paymentConcierge = $this->buildBookingPaymentConcierge($booking);
        } else {
            $enrollment = $school->enrollmentRequests()->findOrFail($validated['enrollment_request_id']);
            $linkedPayerId = (int) $enrollment->learner_id;
            if (!$isAdmin && $linkedPayerId !== (int) $studentId) {
                abort(403);
            }
            $data['branch_id'] = $enrollment->branch_id;
            $paymentConcierge = $this->buildEnrollmentPaymentConcierge($enrollment);
        }

        if (!$paymentConcierge['allow_submission']) {
            return $this->blockedPaymentResponse($request, $paymentConcierge);
        }

        $data['payer_user_id'] = $linkedPayerId;
        $storedProofPath = null;

        try {
            if ($validated['method'] === 'gcash') {
                // Store receipt securely
                $storedProofPath = $storageService->store($request->file('proof_of_payment'), $school->id);
                $data['proof_of_payment_path'] = $storedProofPath;

                // For revision flow, retire old references so a corrected re-upload can reuse the same reference.
                if (!empty($paymentConcierge['revision_mode']) && $enrollment instanceof EnrollmentRequest) {
                    $enrollment->payments()
                        ->whereIn('status', ['pending', 'on_hold', 'rejected'])
                        ->update([
                            'status' => 'rejected',
                            'reference' => null,
                            'normalized_reference' => null,
                            'or_number' => null,
                            'normalized_or_number' => null,
                            'updated_at' => now(),
                        ]);
                }

                $payment = $submissionService->submitGcash($data);
            } else {
                // On-site usually recorded by Admin
                if (!$isAdmin)
                    abort(403, 'Students cannot record on-site payments.');
                $data['received_by_admin_id'] = Auth::guard('admin')->id();
                $data['received_at'] = now();
                $payment = $submissionService->submitOnsite($data);
            }

            // Sync receipt to parent enrollment/booking so admin Enrollment page
            // (which reads enrollment_requests.payment_* fields) always sees it.
            // This fixes "receipt shows in Payments but not in Enrollment" inconsistency.
            try {
                if ($enrollment instanceof EnrollmentRequest) {
                    $refValue = $validated['method'] === 'gcash'
                        ? ($validated['reference'] ?? null)
                        : ($validated['or_number'] ?? null);
                    $enrollment->update([
                        'payment_method' => $validated['method'],
                        'payment_reference' => $refValue,
                        'payment_proof_path' => $storedProofPath ?? $payment->proof_of_payment_path,
                        'payment_status' => in_array((string) $enrollment->payment_status, ['rejected', 'revision_required'], true)
                            ? 'pending'
                            : ($enrollment->payment_status ?? 'pending'),
                    ]);
                } elseif ($booking) {
                    $booking->update([
                        'payment_status' => in_array((string) ($booking->payment_status ?? ''), ['rejected', 'revision_required'], true)
                            ? 'pending'
                            : ($booking->payment_status ?? 'pending'),
                    ]);
                }
            } catch (\Exception $syncEx) {
                Log::warning('Payment receipt sync to enrollment failed: ' . $syncEx->getMessage());
            }
        } catch (QueryException $e) {
            if ($storedProofPath) {
                Storage::disk('local')->delete($storedProofPath);
            }

            $friendlyMessage = $this->friendlyPaymentErrorMessage($e, (string) $validated['method']);
            if ($friendlyMessage) {
                return $this->paymentErrorResponse($request, $friendlyMessage, 422, 'warning');
            }

            Log::error('Payment submission query failed', [
                'school_id' => $school->id,
                'student_id' => $studentId,
                'enrollment_request_id' => $validated['enrollment_request_id'] ?? null,
                'booking_id' => $validated['booking_id'] ?? null,
                'method' => $validated['method'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->paymentErrorResponse(
                $request,
                'Payment submission failed. Please verify your reference number and try again.',
                422,
                'error'
            );
        } catch (\Exception $e) {
            if ($storedProofPath) {
                Storage::disk('local')->delete($storedProofPath);
            }

            Log::error('Payment submission failed', [
                'school_id' => $school->id,
                'student_id' => $studentId,
                'enrollment_request_id' => $validated['enrollment_request_id'] ?? null,
                'booking_id' => $validated['booking_id'] ?? null,
                'method' => $validated['method'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->paymentErrorResponse(
                $request,
                'Payment submission failed. Please try again or contact support.',
                422,
                'error'
            );
        }

        $successMessage = !empty($paymentConcierge['revision_mode'])
            ? 'Updated payment details submitted for verification.'
            : 'Payment submitted for verification.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $successMessage, 'payment' => $payment], 201);
        }

        // F-008: Role-Aware Explicit Redirects
        if (Auth::guard('admin')->check()) {
            return redirect()->route('schools.admin.payments.index', $school->slug)->with('success', $successMessage);
        } elseif (Auth::guard('student')->check()) {
            // Check if user is a guest role student
            if (Auth::guard('student')->user()->isGuest()) {
                return redirect()->route('schools.guest.payments.index', $school->slug)->with('success', $successMessage);
            }
            return redirect()->route('schools.student.payments.index', $school->slug)->with('success', $successMessage);
        }

        return redirect()->back()->with('success', $successMessage);
    }

    private function buildEnrollmentPaymentConcierge(EnrollmentRequest $enrollment): array
    {
        $enrollmentStatus = (string) $enrollment->status;
        $paymentStatus = (string) ($enrollment->payment_status ?? 'pending');

        $hasLegacySubmissionData = !empty($enrollment->payment_reference)
            || !empty($enrollment->payment_proof_path);

        $activePaymentStatuses = $enrollment->payments()
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('status');

        $hasPendingPaymentRecord = $activePaymentStatuses->contains('pending');
        $hasApprovedPaymentRecord = $activePaymentStatuses->contains('approved');
        $hasActivePaymentRecord = $hasPendingPaymentRecord || $hasApprovedPaymentRecord;

        if (in_array($enrollmentStatus, ['cancelled', 'rejected'], true)
            && !in_array($paymentStatus, ['rejected', 'revision_required'], true)) {
            return [
                'allow_submission' => false,
                'revision_mode' => false,
                'level' => 'error',
                'message' => 'This enrollment request is no longer active, so payment submission is disabled.',
            ];
        }

        if ($paymentStatus === 'paid' || $hasApprovedPaymentRecord) {
            return [
                'allow_submission' => false,
                'revision_mode' => false,
                'level' => 'success',
                'message' => 'Payment is already verified for this enrollment. No need to submit again.',
            ];
        }

        if (in_array($paymentStatus, ['rejected', 'revision_required'], true)) {
            if ($hasActivePaymentRecord) {
                return [
                    'allow_submission' => false,
                    'revision_mode' => false,
                    'level' => 'warning',
                    'message' => 'Your updated payment is already submitted and waiting for admin review.',
                ];
            }

            return [
                'allow_submission' => true,
                'revision_mode' => true,
                'level' => 'info',
                'message' => 'Your previous payment needs revision. Submit an updated receipt to continue.',
            ];
        }

        if (in_array($paymentStatus, ['pending', 'on_hold', 'partial'], true)
            && ($hasLegacySubmissionData || $hasActivePaymentRecord)) {
            return [
                'allow_submission' => false,
                'revision_mode' => false,
                'level' => 'warning',
                'message' => 'Payment details for this enrollment are already submitted and currently under review.',
            ];
        }

        return [
            'allow_submission' => true,
            'revision_mode' => false,
            'level' => 'info',
            'message' => '',
        ];
    }

    private function buildBookingPaymentConcierge(Booking $booking): array
    {
        $paymentStatus = (string) ($booking->payment_status ?? 'pending');

        $activePaymentStatuses = Payment::where('school_id', $booking->school_id)
            ->where('booking_id', $booking->id)
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('status');

        $hasPendingPaymentRecord = $activePaymentStatuses->contains('pending');
        $hasApprovedPaymentRecord = $activePaymentStatuses->contains('approved');

        if ($paymentStatus === 'paid' || $hasApprovedPaymentRecord) {
            return [
                'allow_submission' => false,
                'revision_mode' => false,
                'level' => 'success',
                'message' => 'Payment is already verified for this booking. No need to submit again.',
            ];
        }

        if (in_array($paymentStatus, ['rejected', 'revision_required'], true)) {
            if ($hasPendingPaymentRecord) {
                return [
                    'allow_submission' => false,
                    'revision_mode' => false,
                    'level' => 'warning',
                    'message' => 'Your updated booking payment is already submitted and waiting for admin review.',
                ];
            }

            return [
                'allow_submission' => true,
                'revision_mode' => true,
                'level' => 'info',
                'message' => 'Your booking payment needs revision. Submit an updated receipt to continue.',
            ];
        }

        if (in_array($paymentStatus, ['pending', 'on_hold', 'partial'], true) && $hasPendingPaymentRecord) {
            return [
                'allow_submission' => false,
                'revision_mode' => false,
                'level' => 'warning',
                'message' => 'Payment details for this booking are already submitted and currently under review.',
            ];
        }

        return [
            'allow_submission' => true,
            'revision_mode' => false,
            'level' => 'info',
            'message' => '',
        ];
    }

    private function blockedPaymentResponse(Request $request, array $state)
    {
        $message = $state['message'] ?? 'Payment submission is not allowed right now.';
        $level = $state['level'] ?? 'warning';

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'state' => $level,
            ], 422);
        }

        return redirect()->back()->with($this->resolveFlashKey($level), $message);
    }

    private function paymentErrorResponse(Request $request, string $message, int $status = 422, string $flashType = 'error')
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()->withInput()->with($flashType, $message);
    }

    private function resolveFlashKey(string $level): string
    {
        return match ($level) {
            'error' => 'error',
            'success' => 'success',
            'info' => 'info',
            default => 'warning',
        };
    }

    private function friendlyPaymentErrorMessage(QueryException $e, string $method): ?string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'payments_gcash_global_unique')
            || ($method === 'gcash' && str_contains($message, 'normalized_reference'))) {
            return 'That GCash reference number is already used in this school. Please double-check it or use a different reference.';
        }

        if (str_contains($message, 'payments_onsite_branch_unique')
            || ($method === 'on_site' && str_contains($message, 'normalized_or_number'))) {
            return 'That official receipt number is already recorded for this branch. Please verify the OR number and try again.';
        }

        if (str_contains($message, 'Duplicate entry') || str_contains($message, 'Integrity constraint violation')) {
            return 'Payment submission was blocked because this reference already exists. Please verify the number and submit again.';
        }

        return null;
    }

    /**
     * Display the specified payment.
     */
    public function show(School $school, Payment $payment)
    {
        abort_if($payment->school_id !== $school->id, 404);

        // Security check
        if (Auth::guard('student')->check()) {
            $studentId = Auth::guard('student')->id();
            $payment->load(['booking', 'enrollmentRequest']);
            
            $isOwner = false;
            if ($payment->booking && (int)$payment->booking->student_id === (int)$studentId) {
                $isOwner = true;
            } elseif ($payment->enrollmentRequest && (int)$payment->enrollmentRequest->learner_id === (int)$studentId) {
                $isOwner = true;
            }

            if (!$isOwner) {
                abort(403, 'You do not have access to this payment record.');
            }
        }
        elseif (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            if ($admin instanceof Admin && $admin->isBranchSecretary() && $admin->branch_id) {
                // Securely check branch access using payment-level branch_id
                if ($payment->branch_id && (int)$payment->branch_id !== (int)$admin->branch_id) {
                    abort(403, 'You do not have access to payments from this branch.');
                }
            }
        }
        else {
            abort(403);
        }

        $payment->load(['booking.student', 'booking.course', 'booking.instructor']);

        // Always return JSON - payment details shown in modals/lists
        return response()->json([
            'success' => true,
            'payment' => $payment
        ]);
    }

    // update() method removed — Payment module is read-only.
    // All acceptance/rejection is handled exclusively by the Enrollment module.

    /**
     * Remove the specified payment.
     */
    public function destroy(Request $request, School $school, $id)
    {
        // Layer 2: Fail-Closed Retrieval
        $payment = $school->payments()->findOrFail($id);

        // Security: Branch secretary restriction
        $admin = Auth::guard('admin')->user();
        if (!$admin instanceof Admin) {
            abort(403, 'Unauthorized admin action.');
        }
        if ($admin->isBranchSecretary() && $admin->branch_id) {
            // Securely check branch access using payment-level branch_id
            if ($payment->branch_id && (int)$payment->branch_id !== (int)$admin->branch_id) {
                abort(403, 'You do not have access to payments from this branch.');
            }
        }

        // Warning: Deleting a completed payment affects financial reports
        if ($payment->status === 'approved' && !$admin->isSchoolAdmin()) {
            abort(403, 'Only school administrators can delete completed payments.');
        }

        $payment->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment record deleted successfully.'
            ]);
        }

        return redirect()->route('schools.admin.payments.index', $school)
            ->with('success', 'Payment record deleted successfully.');
    }

    /**
     * Get payment statistics.
     */
    public function statistics(School $school)
    {
        $admin = Auth::guard('admin')->user();
        // Only admins can view general statistics
        if (!$admin instanceof Admin) {
            abort(403);
        }

        $stats = [
            'total_revenue' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', '=', 'approved')
                ->sum('amount') - $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', '=', 'refunded')
                ->sum('refunded_amount'),
            'pending_payments' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', '=', 'pending')
                ->count(),
            'total_payments' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->count(),
            'by_method' => $admin->scopeToBranch(Payment::where('school_id', $school->id))
                ->where('status', 'approved')
                ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('method')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }
}
