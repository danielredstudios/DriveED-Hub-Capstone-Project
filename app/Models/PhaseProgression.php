<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhaseProgression extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $table = 'phase_progression_requests';

    protected $fillable = [
        'enrollment_id',
        'school_id',
        'branch_id',
        'from_phase',
        'to_phase',
        'requested_at',
        'reviewed_at',
        'reviewed_by',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Get the enrollment this progression request belongs to
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class , 'enrollment_id');
    }

    /**
     * Get the school
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the admin who reviewed this request
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class , 'reviewed_by');
    }

    // ──────────────────────────────────────────────
    // Status helpers
    // ──────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // ──────────────────────────────────────────────
    // Actions
    // ──────────────────────────────────────────────

    /**
     * Approve this phase progression request
     */
    public function approve(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Reject this phase progression request
     */
    public function reject(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
            'admin_notes' => $notes,
        ]);
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to pending requests only
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get a human-readable description of the transition
     */
    public function getTransitionLabel(): string
    {
        return ucfirst($this->from_phase) . ' → ' . ucfirst($this->to_phase);
    }
}
