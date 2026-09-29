<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected static function booted()
    {
        static::saving(function ($payment) {
            // Normalization: Strip non-alphanumeric and uppercase
            if ($payment->reference) {
                $payment->normalized_reference = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $payment->reference));
            }
            if ($payment->or_number) {
                $payment->normalized_or_number = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $payment->or_number));
            }

            // Model-level XOR Validation (Secondary check to Triggers)
            $hasIdentity = ($payment->guest_identity_token ? 1 : 0) + ($payment->payer_user_id ? 1 : 0) === 1;
            $hasLinkage = ($payment->booking_id ? 1 : 0) + ($payment->enrollment_request_id ? 1 : 0) === 1;

            if (!$hasIdentity) {
                throw new \Exception('Payment must have exactly one identity (guest or user).');
            }
            if (!$hasLinkage) {
                throw new \Exception('Payment must be linked to exactly one booking or enrollment request.');
            }
        });
    }

    protected $fillable = [
        'school_id',
        'branch_id',
        'booking_id',
        'enrollment_request_id',
        'guest_identity_token',
        'payer_user_id',
        'amount',
        'paid_on',
        'method',
        'reference',
        'normalized_reference',
        'or_number',
        'normalized_or_number',
        'proof_of_payment_path',
        'status',
        'rejection_reason_code',
        'rejection_reason_note',
        'received_by_admin_id',
        'received_at',
        'refunded_by_admin_id',
        'refunded_at',
        'refunded_amount',
        'snap_qr_source',
        'snap_config_id',
        'snap_expected_amount',
        'snap_qr_path',
        'snap_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_on' => 'datetime',
        'received_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refunded_amount' => 'decimal:2',
        'snap_at' => 'datetime',
        'snap_expected_amount' => 'decimal:2',
    ];

    /**
     * Get the school that owns the payment.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the booking for the payment.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the branch that owns the payment.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the enrollment request for the payment.
     */
    public function enrollmentRequest(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class);
    }

    /**
     * Get the student/user who paid.
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'payer_user_id');
    }

    /**
     * Get the status logs for the payment.
     */
    public function statusLogs()
    {
        return $this->hasMany(PaymentStatusLog::class);
    }

    /**
     * Scope a query to only include GCash payments.
     */
    public function scopeGcash($query)
    {
        return $query->where('method', 'gcash');
    }

    /**
     * Scope a query to only include On-site payments.
     */
    public function scopeOnsite($query)
    {
        return $query->where('method', 'on_site');
    }

    /**
     * Scope a query to only include payments with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include payments with a specific method.
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }
}
