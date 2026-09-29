<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\GCashSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentSubmissionService
{
    /**
     * Submit a GCash payment with forensic snapshotting and normalization.
     */
    public function submitGcash(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Snapshot the QR setup used
            $setting = GCashSetting::getActiveSetting($data['school_id'], $data['branch_id']);
            
            if (!$setting) {
                throw new \Exception('No active GCash account found for this branch or school.');
            }

            // 2. Normalize and Prepare
            $payment = new Payment([
                'school_id' => $data['school_id'],
                'branch_id' => $data['branch_id'],
                'booking_id' => $data['booking_id'] ?? null,
                'enrollment_request_id' => $data['enrollment_request_id'] ?? null,
                'payer_user_id' => $data['payer_user_id'] ?? null,
                'guest_identity_token' => $data['guest_identity_token'] ?? null,
                'method' => 'gcash',
                'amount' => $data['amount'],
                'reference' => $data['reference'],
                'paid_on' => now(),
                'status' => 'pending',
                'proof_of_payment_path' => $data['proof_of_payment_path'],
                
                // Forensics: Snapshot
                'snap_qr_source' => $setting->branch_id ? 'branch' : 'school',
                'snap_config_id' => $setting->id,
                'snap_expected_amount' => $data['amount'],
                'snap_qr_path' => $setting->qr_path,
                'snap_at' => now(),
            ]);

            $payment->save();

            // 3. Log initial status
            $payment->statusLogs()->create([
                'school_id' => $payment->school_id,
                'actor_id' => $payment->payer_user_id,
                'action_type' => 'submit',
                'to_status' => 'pending',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $payment;
        });
    }

    /**
     * Submit an On-site payment.
     */
    public function submitOnsite(array $data)
    {
        return DB::transaction(function () use ($data) {
            $payment = new Payment([
                'school_id' => $data['school_id'],
                'branch_id' => $data['branch_id'],
                'booking_id' => $data['booking_id'] ?? null,
                'enrollment_request_id' => $data['enrollment_request_id'] ?? null,
                'payer_user_id' => $data['payer_user_id'] ?? null,
                'guest_identity_token' => $data['guest_identity_token'] ?? null,
                'method' => 'on_site',
                'amount' => $data['amount'],
                'or_number' => $data['or_number'],
                'paid_on' => $data['paid_on'] ?? now(),
                'status' => 'pending',
                
                // Audit: Required for On-site
                'received_by_admin_id' => $data['received_by_admin_id'] ?? null,
                'received_at' => $data['received_at'] ?? now(),
            ]);

            $payment->save();

            $payment->statusLogs()->create([
                'school_id' => $payment->school_id,
                'actor_id' => auth()->id(), // Admin usually submits on-site
                'action_type' => 'submit',
                'to_status' => 'pending',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $payment;
        });
    }
}
