<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentActionRequest extends Model
{
    use HasSchoolScope;
    use HasFactory;

    const ACTION_ADD = 'add';
    const ACTION_REMOVE = 'remove';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';

    protected $fillable = [
        'school_id',
        'branch_id',
        'requested_by',
        'student_id',
        'action',
        'reason',
        'student_name',
        'student_email',
        'student_contact',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // ──────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(Admin::class, 'requested_by');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    // ──────────────────────────────────────
    // Status helpers
    // ──────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isDenied(): bool
    {
        return $this->status === self::STATUS_DENIED;
    }

    public function isAddRequest(): bool
    {
        return $this->action === self::ACTION_ADD;
    }

    public function isRemoveRequest(): bool
    {
        return $this->action === self::ACTION_REMOVE;
    }

    /**
     * Approve this request.
     */
    public function approve(Admin $reviewer, ?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    /**
     * Deny this request.
     */
    public function deny(Admin $reviewer, ?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_DENIED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    // ──────────────────────────────────────
    // Query scopes
    // ──────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
