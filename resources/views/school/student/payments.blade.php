@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Payments')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
    $settings = $school->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
@endphp

<style>
.payments-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 4px solid {{ $primaryColor }};
}

.page-title {
    font-size: 2rem;
    color: #111827;
    margin: 0;
    font-weight: 400;
}

.total-spent {
    @if($settings->use_gradient_header ?? true)
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
    @else
        background: {{ $primaryColor }};
    @endif
    color: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    text-align: center;
}

.total-spent h2 {
    font-size: 3rem;
    margin: 10px 0;
}

.payments-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #f9fafb;
}

th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #374151;
}

td {
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-completed { background: #d1fae5; color: #065f46; }
.badge-pending { background: #fef3c7; color: #92400e; }

/* Mobile card styles for payments */
.payment-card {
    display: none;
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    border-left: 4px solid {{ $primaryColor }};
}

.payment-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.payment-card-row:last-child {
    margin-bottom: 0;
}

.payment-card-label {
    color: #6b7280;
    font-size: 0.8rem;
    font-weight: 500;
}

.payment-card-value {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.9rem;
}

.payment-card-amount {
    font-size: 1.1rem;
    color: #10b981;
    font-weight: 700;
}

.total-spent-label {
    font-size: 1.2rem;
    opacity: 0.9;
}

.amount-emphasis {
    color: #10b981;
}

.payments-pagination {
    padding: 15px 20px;
    display: flex;
    justify-content: center;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .payments-container {
        padding: 15px;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .total-spent {
        padding: 20px;
    }
    
    .total-spent h2 {
        font-size: 2rem;
    }
    
    .total-spent p {
        font-size: 1rem !important;
    }
    
    /* Hide table, show cards on mobile */
    .payments-table table {
        display: none;
    }
    
    .payment-card {
        display: block;
    }
}

@media (max-width: 480px) {
    .payments-container {
        padding: 10px;
    }
    
    .page-title {
        font-size: 1.25rem;
    }
    
    .total-spent {
        padding: 15px;
        border-radius: 10px;
    }
    
    .total-spent h2 {
        font-size: 1.75rem;
    }
    
    .total-spent p {
        font-size: 0.9rem !important;
    }
    
    .payment-card {
        padding: 12px;
    }
    
    .badge {
        padding: 4px 10px;
        font-size: 0.7rem;
    }
}

/* Compact Pagination Styling */
nav[role="navigation"] {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

nav[role="navigation"] > div {
    display: flex;
    align-items: center;
    gap: 4px;
}

nav[role="navigation"] > div:first-child {
    display: none;
}

nav[role="navigation"] span[aria-current="page"] span,
nav[role="navigation"] a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.15s ease;
}

nav[role="navigation"] span[aria-current="page"] span {
    background: {{ $primaryColor }};
    color: white;
}

nav[role="navigation"] a {
    background: #f3f4f6;
    color: #374151;
}

nav[role="navigation"] a:hover {
    background: #e5e7eb;
    color: #1f2937;
}

nav[role="navigation"] svg {
    width: 14px !important;
    height: 14px !important;
}

nav[role="navigation"] span[aria-disabled="true"] {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    background: #f9fafb;
    color: #d1d5db;
    border-radius: 6px;
    cursor: not-allowed;
}

nav[role="navigation"] span[aria-disabled="true"] svg {
    width: 14px !important;
    height: 14px !important;
}

nav[role="navigation"] span:not([aria-current]):not([aria-disabled]) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    font-size: 0.85rem;
    color: #6b7280;
}

@media (min-width: 640px) {
    nav[role="navigation"] > div:first-child {
        display: block;
        font-size: 0.82rem;
        color: #6b7280;
        margin-right: 16px;
    }
}
</style>

<div class="payments-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <h1 class="page-title">My Payments</h1>
        @if(($pendingEnrollments ?? collect())->isNotEmpty() || ($pendingBookings ?? collect())->isNotEmpty())
            <button class="btn btn-primary" onclick="openPaymentModal()" style="padding: 10px 20px; background: {{ $primaryColor }}; color: white; border-radius: 6px; font-weight: 500; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Upload Payment
            </button>
        @endif
    </div>

    <div class="total-spent">
        <p class="total-spent-label">Total Amount Paid</p>
        <h2>&#8369;{{ number_format($totalPaid ?? $payments->where('status', 'completed')->sum('amount'), 2) }}</h2>
    </div>

    <div class="payments-table">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Course</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A' }}</td>
                    <td><strong>{{ $payment->booking?->course?->title ?? $payment->enrollmentRequest?->course?->title ?? 'N/A' }}</strong></td>
                    <td><strong class="amount-emphasis">&#8369;{{ number_format($payment->amount, 2) }}</strong></td>
                    <td>{{ ucfirst($payment->method ?? 'N/A') }}</td>
                    <td><span class="badge badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <p class="empty-state-title">No payment records found</p>
                            <p class="empty-state-text">Payment history will appear here once payments are made</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Mobile card view --}}
        @forelse($payments as $payment)
        <div class="payment-card">
            <div class="payment-card-row">
                <span class="payment-card-label">Date</span>
                <span class="payment-card-value">{{ $payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A' }}</span>
            </div>
            <div class="payment-card-row">
                <span class="payment-card-label">Course</span>
                <span class="payment-card-value">{{ $payment->booking?->course?->title ?? $payment->enrollmentRequest?->course?->title ?? 'N/A' }}</span>
            </div>
            <div class="payment-card-row">
                <span class="payment-card-label">Amount</span>
                <span class="payment-card-amount">&#8369;{{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="payment-card-row">
                <span class="payment-card-label">Method</span>
                <span class="payment-card-value">{{ ucfirst($payment->method ?? 'N/A') }}</span>
            </div>
            <div class="payment-card-row">
                <span class="payment-card-label">Status</span>
                <span class="badge badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="empty-state-title">No payment records found</p>
            <p class="empty-state-text">Payment history will appear here once payments are made</p>
        </div>
        @endforelse

        @if($payments->hasPages())
        <div class="payments-pagination">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>

@if(($pendingEnrollments ?? collect())->isNotEmpty() || ($pendingBookings ?? collect())->isNotEmpty())
<div id="paymentModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 50; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; border-radius: 12px; width: 100%; max-width: 500px; padding: 24px; position: relative;">
        <button onclick="closePaymentModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; cursor: pointer; color: #6b7280;">
            <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-top: 0; margin-bottom: 20px; color: #111827;">Upload Proof of Payment</h2>

        @if(!empty($gcashPaymentImageUrl))
            <div style="margin-bottom: 16px; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; background: #f9fafb;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 8px;">GCash Payment Image</label>
                <img src="{{ $gcashPaymentImageUrl }}" alt="GCash payment image" style="width: 100%; max-height: 260px; object-fit: contain; border-radius: 8px; background: #fff; border: 1px solid #d1d5db;">
                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 8px; margin-bottom: 0;">Use this image as reference for payment destination details.</p>
            </div>
        @else
            <div style="margin-bottom: 16px; border: 1px solid #fde68a; border-radius: 10px; padding: 12px; background: #fffbeb; color: #92400e; font-size: 0.875rem;">
                GCash payment image is not yet configured by the admin. Please contact your school admin before submitting payment.
            </div>
        @endif

        <form method="POST" action="{{ route('schools.' . ((Auth::guard('student')->user()->role ?? 'student') === 'guest' ? 'guest' : 'student') . '.payments.store', ['school' => $school->slug]) }}" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 6px;">Payment For <span style="color: red;">*</span></label>
                <select id="paymentTypeSelect" name="payment_target" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;" onchange="updatePaymentTarget()">
                    <option value="" disabled selected>Select what you are paying for...</option>
                    @foreach($pendingEnrollments ?? [] as $enrollment)
                        @php
                            $enrollmentPaymentStatus = (string) ($enrollment->payment_status ?? 'pending');
                            $hasLegacySubmissionData = !empty($enrollment->payment_reference) || !empty($enrollment->payment_proof_path);
                            $hasActivePaymentRecord = ((int) ($enrollment->active_payment_records_count ?? 0)) > 0;
                            $isRevisionStatus = in_array($enrollmentPaymentStatus, ['rejected', 'revision_required'], true);
                            $isAwaitingReview = in_array($enrollmentPaymentStatus, ['pending', 'on_hold', 'partial'], true)
                                && ($hasLegacySubmissionData || $hasActivePaymentRecord);
                            $isAlreadyResubmitted = $isRevisionStatus && $hasActivePaymentRecord;
                            $isEnrollmentClosed = in_array((string) ($enrollment->status ?? ''), ['cancelled', 'rejected'], true) && !$isRevisionStatus;
                            $isPaidStatus = $enrollmentPaymentStatus === 'paid';
                            $isEnrollmentOptionDisabled = $isPaidStatus || $isAwaitingReview || $isAlreadyResubmitted || $isEnrollmentClosed;

                            $enrollmentStateLabel = 'Ready for Upload';
                            if ($isPaidStatus) {
                                $enrollmentStateLabel = 'Paid';
                            } elseif ($isAwaitingReview || $isAlreadyResubmitted) {
                                $enrollmentStateLabel = 'Under Review';
                            } elseif ($isRevisionStatus) {
                                $enrollmentStateLabel = 'Needs Update';
                            } elseif ($isEnrollmentClosed) {
                                $enrollmentStateLabel = 'Enrollment Closed';
                            }
                        @endphp
                        <option value="enrollment_{{ $enrollment->id }}" @disabled($isEnrollmentOptionDisabled)>
                            Enrollment Request: {{ $enrollment->course->title ?? 'Course' }} ({{ $enrollmentStateLabel }})
                        </option>
                    @endforeach
                    @foreach($pendingBookings ?? [] as $booking)
                        @php
                            $bookingPaymentStatus = (string) ($booking->payment_status ?? 'pending');
                            $hasBookingActivePayment = ((int) ($booking->active_payment_record_count ?? 0)) > 0;
                            $isBookingRevisionStatus = in_array($bookingPaymentStatus, ['rejected', 'revision_required'], true);
                            $isBookingAwaitingReview = in_array($bookingPaymentStatus, ['pending', 'on_hold', 'partial'], true)
                                && $hasBookingActivePayment;
                            $isBookingAlreadyResubmitted = $isBookingRevisionStatus && $hasBookingActivePayment;
                            $isBookingPaidStatus = $bookingPaymentStatus === 'paid';
                            $isBookingOptionDisabled = $isBookingPaidStatus || $isBookingAwaitingReview || $isBookingAlreadyResubmitted;

                            $bookingStateLabel = 'Ready for Upload';
                            if ($isBookingPaidStatus) {
                                $bookingStateLabel = 'Paid';
                            } elseif ($isBookingAwaitingReview || $isBookingAlreadyResubmitted) {
                                $bookingStateLabel = 'Under Review';
                            } elseif ($isBookingRevisionStatus) {
                                $bookingStateLabel = 'Needs Update';
                            }
                        @endphp
                        <option value="booking_{{ $booking->id }}" @disabled($isBookingOptionDisabled)>
                            Booking: {{ $booking->course->title ?? 'Course' }} ({{ $bookingStateLabel }})
                        </option>
                    @endforeach
                </select>
                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 6px; margin-bottom: 0;">
                    Only items marked "Ready for Upload" or "Needs Update" can accept a new payment submission.
                </p>
            </div>

            <input type="hidden" id="enrollment_request_id" name="enrollment_request_id" value="">
            <input type="hidden" id="booking_id" name="booking_id" value="">

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 6px;">Amount (PHP) <span style="color: red;">*</span></label>
                <input type="number" name="amount" min="1" step="0.01" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
            </div>

            <input type="hidden" name="method" value="gcash">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 6px;">GCash Reference No. <span style="color: red;">*</span></label>
                <input type="text" name="reference" required maxlength="13" minlength="13" pattern="[0-9]{13}" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,13)" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;" placeholder="e.g. 1234567890123">
                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 4px;">Numbers only, exactly 13 digits. For on-site OR payments, use the dedicated payment page for your enrollment instead.</p>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 6px;">Proof of Payment (Screenshot) <span style="color: red;">*</span></label>
                <input type="file" name="proof_of_payment" accept="image/*" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem;">
                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 4px;">Accepted formats: JPG, PNG, WEBP. Max size: 5MB.</p>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closePaymentModal()" style="padding: 10px 16px; background: white; border: 1px solid #d1d5db; color: #374151; border-radius: 6px; font-weight: 500; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 16px; background: {{ $primaryColor }}; color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">Submit Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal() {
    document.getElementById('paymentModal').style.display = 'flex';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

function updatePaymentTarget() {
    const select = document.getElementById('paymentTypeSelect');
    const val = select.value;

    document.getElementById('enrollment_request_id').value = '';
    document.getElementById('booking_id').value = '';

    if (val.startsWith('enrollment_')) {
        document.getElementById('enrollment_request_id').value = val.replace('enrollment_', '');
    } else if (val.startsWith('booking_')) {
        document.getElementById('booking_id').value = val.replace('booking_', '');
    }
}
</script>
@endif
@endsection
