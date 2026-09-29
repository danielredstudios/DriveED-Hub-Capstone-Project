<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\Admin;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny($user): bool
    {
        return true; // Filtered by scope in controllers
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view($user, Payment $payment): bool
    {
        // 1. School Admin: Full access
        if ($user instanceof Admin && $user->role === 'school_admin' && (int)$user->school_id === (int)$payment->school_id) {
            return true;
        }

        // 2. Branch Secretary: Own branch only
        if ($user instanceof Admin && $user->role === 'branch_secretary' && (int)$user->branch_id === (int)$payment->branch_id) {
            return true;
        }

        // 3. Student/Guest: Own records only
        if ($user instanceof \App\Models\Student && (int)$user->id === (int)$payment->payer_user_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create($user): bool
    {
        return true; // Controlled by submission flows
    }

    /**
     * Determine whether the user can update (edit notes/status).
     */
    public function update($user, Payment $payment): bool
    {
        // Terminal records: Only administrative notes are editable (core financial data is immutable)
        // This check is often handled in the Request validation but policy can enforce access.

        if ($user instanceof Admin) {
            // School Admin: can update if at same school
            if ($user->role === 'school_admin' && (int)$user->school_id === (int)$payment->school_id) {
                return true;
            }
            // Branch Secretary: can update pending/own-branch only
            if ($user->role === 'branch_secretary' && (int)$user->branch_id === (int)$payment->branch_id) {
                return $payment->status === 'pending';
            }
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the payment.
     */
    public function cancel($user, Payment $payment): bool
    {
        if ($payment->status !== 'pending') return false;

        // Student/Guest: can cancel own pending
        if ($user instanceof \App\Models\Student && (int)$user->id === (int)$payment->payer_user_id) {
            return true;
        }

        // Branch Secretary: can cancel own branch pending
        if ($user instanceof Admin && $user->role === 'branch_secretary' && (int)$user->branch_id === (int)$payment->branch_id) {
            return true;
        }

        // School Admin: always can cancel pending
        if ($user instanceof Admin && $user->role === 'school_admin' && (int)$user->school_id === (int)$payment->school_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve/reject the payment.
     */
    public function verify($user, Payment $payment): bool
    {
        if ($payment->status !== 'pending') return false;

        // Both branch secretary and school admin can verify
        if ($user instanceof Admin) {
            if ($user->role === 'school_admin' && (int)$user->school_id === (int)$payment->school_id) {
                return true;
            }
            if ($user->role === 'branch_secretary' && (int)$user->branch_id === (int)$payment->branch_id) {
                return true;
            }
        }

        return false;
    }
}
