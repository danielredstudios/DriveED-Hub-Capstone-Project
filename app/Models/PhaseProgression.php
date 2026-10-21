<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student-initiated request to move from the theoretical phase into the
 * practical phase of a Bundle/Combo enrollment.
 *
 * The request is opened by the student's driver's-license upload on their
 * dashboard and confirmed by the school admin from the Enrollment Request.
 */
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
        'trigger',
        'requested_at',
        'submitted_by',
        'license_path',
        'student_notes',
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
     * Get the student who submitted the request
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(Student::class , 'submitted_by');
    }

    /**
     * Alias used by the enrollment request screens.
     */
    public function student(): BelongsTo
    {
        return $this->submittedBy();
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

    /**
     * Was this opened by the student's license upload?
     */
    public function isLicenseTriggered(): bool
    {
        return ($this->trigger ?? 'license') === 'license';
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

    /**
     * Open (or refresh) the licence-triggered transition request for an
     * enrollment. Safe to call repeatedly: a pending request is refreshed
     * rather than duplicated.
     */
    public static function openFromLicense(
        EnrollmentRequest $enrollment,
        Student $student,
        ?string $licensePath = null,
        ?string $studentNotes = null,
        string $fromPhase = 'theoretical',
        string $toPhase = 'practical',
    ): self {
        $existing = static::where('enrollment_id', $enrollment->id)
            ->where('to_phase', $toPhase)
            ->where('status', 'pending')
            ->first();

        $attributes = [
            'enrollment_id' => $enrollment->id,
            'school_id' => $enrollment->school_id,
            'branch_id' => $enrollment->branch_id,
            'from_phase' => $fromPhase,
            'to_phase' => $toPhase,
            'trigger' => 'license',
            'submitted_by' => $student->id,
            'license_path' => $licensePath,
            'student_notes' => $studentNotes,
        ];

        if ($existing) {
            $existing->update($attributes + ['requested_at' => now()]);

            return $existing;
        }

        // A previously rejected request may be re-opened after a fresh upload.
        $rejected = static::where('enrollment_id', $enrollment->id)
            ->where('to_phase', $toPhase)
            ->where('status', 'rejected')
            ->latest('requested_at')
            ->first();

        if ($rejected) {
            $rejected->update($attributes + [
                'status' => 'pending',
                'requested_at' => now(),
                'reviewed_at' => null,
                'reviewed_by' => null,
                'admin_notes' => null,
            ]);

            return $rejected;
        }

        return static::create($attributes + [
            'requested_at' => now(),
            'status' => 'pending',
        ]);
    }

    /**
     * The student-facing state of this transition.
     *
     * Pending  — submitted, awaiting the school admin's confirmation
     * Progressing — confirmed, the practical phase is unlocked
     * Rejected — sent back for a corrected license
     */
    public function getProgressStateAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'progressing',
            'rejected' => 'rejected',
            default => 'pending',
        };
    }

    /**
     * Human-readable label for the student-facing state.
     */
    public function getProgressStateLabelAttribute(): string
    {
        return match ($this->getProgressStateAttribute()) {
            'progressing' => 'Progressing',
            'rejected' => 'Needs Attention',
            default => 'Pending',
        };
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
