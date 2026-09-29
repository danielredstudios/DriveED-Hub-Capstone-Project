<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Admin;
use App\Models\PaymentStatusLog;
use Illuminate\Support\Facades\DB;

class PaymentVerificationService
{
    // approve(), reject(), and refund() methods removed.
    // Payment module is now read-only. All acceptance/rejection
    // is handled exclusively by the Enrollment module.
    // Role promotion occurs only via EnrollmentRequestController::processApproval().

    /**
     * Internal status logging helper.
     */
    protected function logStatus(Payment $payment, $actor, $actionType, $from, $to, $code = null, $note = null)
    {
        PaymentStatusLog::create([
            'payment_id' => $payment->id,
            'school_id' => $payment->school_id,
            'actor_id' => $actor->id,
            'action_type' => $actionType,
            'from_status' => $from,
            'to_status' => $to,
            'reason_code' => $code,
            'reason_note' => $note,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
