<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentRequest extends Model
{
    public function scopeForInstructor($query, Instructor $instructor)
    {
        return $query->where('school_id', $instructor->school_id)->where(function ($connected) use ($instructor) {
            $connected->whereHas('learner.bookings', fn ($booking) => $booking->where('instructor_id', $instructor->id))
                ->orWhereHas('learner.enrollmentRequests.sessionCompletions', fn ($session) => $session->where('instructor_id', $instructor->id));
        });
    }

    use HasSchoolScope;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'learner_id',
        'course_id',
        'status',
        'payment_status',
        'payment_confirmed_by',
        'payment_confirmed_at',
        'payment_confirmation_notes',
        'remarks',
        'location',
        'requested_license_type',
        'requested_dl_code',
        'experience_level',
        'credentials_file_path',
        'verification_notes',
        'approved_by',
        'approved_at',
        'enrolled_at',
        'completed_at',
        'cancelled_at',
        'rejected_at',
        'theoretical_passed',
        'theoretical_passed_at',
        'theoretical_passed_by',
        'theoretical_pass_notes',
        'cancellation_requested',
        'cancellation_reason',
        // New enrollment fields
        'package_id',
        'price',
        'payment_method',
        'payment_reference',
        'payment_proof_path',
    ];

    protected $appends = [
        'uid',
    ];

    public function getUidAttribute(): string
    {
        $year = $this->created_at ? $this->created_at->format('Y') : date('Y');
        return sprintf('ENR-%s-%05d', $year, $this->id);
    }

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'rejected_at' => 'datetime',
            'theoretical_passed' => 'boolean',
            'theoretical_passed_at' => 'datetime',
            'payment_confirmed_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branchRelation()
    {
        return $this->belongsTo(\App\Models\Branch::class , 'branch_id');
    }

    public function learner()
    {
        return $this->belongsTo(Student::class , 'learner_id');
    }

    // Alias for backward compatibility
    public function student()
    {
        return $this->learner();
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function package()
    {
        return $this->belongsTo(CoursePackage::class , 'package_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class , 'approved_by');
    }

    public function theoreticalPassedBy()
    {
        return $this->belongsTo(Admin::class , 'theoretical_passed_by');
    }

    public function paymentConfirmedBy()
    {
        return $this->belongsTo(Admin::class , 'payment_confirmed_by');
    }

    // New relationships (replacing old enrollments table)
    public function progress()
    {
        return $this->hasMany(Progress::class , 'enrollment_request_id');
    }

    /**
     * Transition requests (formerly "phase progressions") raised against this
     * enrollment by the student's license upload.
     */
    public function phaseProgressions()
    {
        return $this->hasMany(PhaseProgression::class , 'enrollment_id');
    }

    /**
     * The most recent transition request, whichever state it is in.
     */
    public function latestPhaseProgression()
    {
        return $this->hasOne(PhaseProgression::class , 'enrollment_id')->latestOfMany('requested_at');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class , 'enrollment_request_id');
    }

    public function sessionCompletions()
    {
        return $this->hasMany(SessionCompletion::class , 'enrollment_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class , 'enrollment_request_id');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isActive()
    {
        return $this->status === 'approved';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function approve($adminId)
    {
        // Update enrollment request status and set enrolled_at timestamp
        $this->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'enrolled_at' => now(),
        ]);

        // Promote staged license to student's profile if present
        if ($this->credentials_file_path) {
            $this->learner->update([
                'student_license_path' => $this->credentials_file_path,
                'student_license_status' => 'verified',
                'student_license_verified_at' => now(),
                'student_license_verified_by' => $adminId,
            ]);
        }

        // Update the learner's role from guest to student
        $this->learner->role = 'student';
        $this->learner->save();
    }

    public function reject($remarks = null)
    {
        $this->update([
            'status' => 'rejected',
            'remarks' => $remarks,
            'rejected_at' => now(),
        ]);
    }

    public function complete($adminId = null, $notes = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark enrollment as completed with full lifecycle handling
     * (Unlock student from course and mark theoretical passed if applicable)
     */
    public function markAsCompleted(): void
    {
        $adminId = \Illuminate\Support\Facades\Auth::guard('admin')->id();
        $this->complete($adminId);

        // Unlock the student
        if ($this->learner) {
            $this->learner->unlockFromCourse();

            // If this was a theoretical course, mark student as passed theoretical
            if ($this->course && $this->course->course_type === 'theoretical') {
                $this->learner->markTheoreticalPassed();
            }
        }
    }

    public function cancel($remarks = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'remarks' => $remarks,
        ]);
    }

    public function markTheoreticalPassed($adminId, $notes = null)
    {
        $this->update([
            'theoretical_passed' => true,
            'theoretical_passed_at' => now(),
            'theoretical_passed_by' => $adminId,
            'theoretical_pass_notes' => $notes ?? $this->theoretical_pass_notes,
        ]);

        // Confirm the student's pending license-triggered transition request.
        $this->resolvePendingPhaseRequest('practical', $adminId, $notes);

        if ($this->learner && !$this->learner->hasPassedTheoretical()) {
            $this->learner->markTheoreticalPassed();
        }

        if ($this->status === 'approved' && $this->course?->course_type === 'theoretical') {
            $this->complete($adminId, $notes);
            $student = $this->learner;
            if ($student && (
                (string) $student->active_enrollment_id === (string) $this->id
                || ($student->active_enrollment_id === null && $student->is_course_locked
                    && !$student->enrollmentRequests()->whereKeyNot($this->id)->whereIn('status', ['pending', 'approved'])->exists())
            )) {
                $student->unlockFromCourse();
            }
        }
    }

    /**
     * Is this enrollment a Bundle/Combo (TDC + PDC in one) enrollment?
     */
    public function isCombo(): bool
    {
        return ($this->course->course_type ?? null) === 'combo';
    }

    /**
     * The student's transition state for a Bundle/Combo enrollment.
     *
     * pending     — license uploaded, awaiting the school admin
     * progressing — confirmed, the practical phase is unlocked
     * rejected    — sent back for a corrected license
     * null        — nothing submitted yet
     */
    public function getTransitionStateAttribute(): ?string
    {
        if (!$this->isCombo()) {
            return null;
        }

        $latest = $this->relationLoaded('latestPhaseProgression')
            ? $this->getRelation('latestPhaseProgression')
            : $this->latestPhaseProgression()->first();

        if (!$latest) {
            return $this->theoretical_passed ? 'progressing' : null;
        }

        return $latest->progress_state;
    }

    /**
     * Student-facing label for the transition state.
     */
    public function getTransitionStateLabelAttribute(): ?string
    {
        return match ($this->transition_state) {
            'progressing' => 'Progressing',
            'pending' => 'Pending',
            'rejected' => 'Needs Attention',
            default => null,
        };
    }

    /**
     * Does this enrollment need the student to upload a license so the
     * practical phase can be unlocked?
     */
    public function awaitsLicenseForPractical(): bool
    {
        if (!$this->isCombo()) {
            return false;
        }

        if ($this->status !== 'approved') {
            return false;
        }

        // Already confirmed for practical — nothing to submit.
        if ($this->theoretical_passed) {
            return false;
        }

        $learner = $this->learner;

        if (!$learner) {
            return false;
        }

        // A pending or approved request is already in flight.
        $openRequest = $this->phaseProgressions()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($openRequest) {
            return false;
        }

        return true;
    }

    /**
     * Confirm any pending transition request targeting a specific phase.
     * Used when an admin action independently unlocks that phase.
     */
    public function resolvePendingPhaseRequest(string $toPhase, int $adminId, string $notes = null): void
    {
        $request = PhaseProgression::where('enrollment_id', $this->id)
            ->where('to_phase', $toPhase)
            ->where('status', 'pending')
            ->first();

        if ($request) {
            $request->update([
                'status' => 'approved',
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'admin_notes' => $notes ?? "Confirmed alongside the training phase update."
            ]);
        }
    }

    /**
     * Check if this request requires student permit verification
     * (Only practical courses require permit)
     */
    public function requiresPermitVerification(): bool
    {
        return $this->course && $this->course->course_type === 'practical';
    }

    /**
     * Check if student permit has been uploaded
     */
    public function hasStudentPermit(): bool
    {
        return !empty($this->credentials_file_path);
    }

    /**
     * Get the student permit file URL
     */
    public function getStudentPermitUrl(): ?string
    {
        if (!$this->hasStudentPermit()) {
            return null;
        }
        return route('schools.guest.storage.credential', [
            'school' => $this->school->slug,
            'enrollment' => $this->id
        ]);
    }

    /**
     * Check if learner has completed any theoretical course at this school
     */
    public function learnerHasCompletedTheoreticalHere(): bool
    {
        return $this->learner && $this->learner->has_passed_theoretical;
    }

    /**
     * Get learner's completed courses at this school (for internal history)
     */
    public function getLearnerCourseHistory()
    {
        if (!$this->learner) {
            return collect();
        }

        return self::where('learner_id', $this->learner_id)
            ->where('school_id', $this->school_id)
            ->where('status', 'completed')
            ->with('course')
            ->orderBy('completed_at', 'desc')
            ->get();
    }

    /**
     * Get the Theoretical (TDC) hour limit for this enrollment.
     */
    public function getTdcHoursLimitAttribute(): float
    {
        if (!$this->course) {
            return 0.0;
        }

        // 1. Check if the specific Package has an explicit TDC limit
        if ($this->package && $this->package->tdc_hours > 0) {
            return (float) $this->package->tdc_hours;
        }

        // 2. Fallback for dedicated TDC courses: Use the legacy training_hours
        if ($this->course->course_type === 'theoretical') {
            return (float) ($this->package->training_hours ?? $this->course->hours_required ?? 15.0);
        }

        // 3. Absolute Fallback: Use Course required hours
        return (float) ($this->course->hours_required ?? 15.0);
    }

    /**
     * Get the Practical (PDC) hour limit for this enrollment.
     */
    public function getPdcHoursLimitAttribute(): float
    {
        if (!$this->course) {
            return 0.0;
        }

        // 1. Check if the specific Package has an explicit PDC limit
        if ($this->package && $this->package->pdc_hours > 0) {
            return (float) $this->package->pdc_hours;
        }

        // 2. Fallback for Practical/Combo: Use legacy training_hours
        if (in_array($this->course->course_type, ['practical', 'combo'])) {
            return (float) ($this->package->training_hours ?? 10.0);
        }

        return 0.0;
    }

    /**
     * Get the total used Theoretical (TDC) hours.
     */
    public function getUsedTdcHoursAttribute()
    {
        return $this->sessionCompletions()
            ->where('status', 'completed')
            ->where('session_type', 'theoretical')
            ->sum('hours_completed');
    }

    /**
     * Get the total used Practical (PDC) hours.
     */
    public function getUsedPdcHoursAttribute()
    {
        return $this->sessionCompletions()
            ->where('status', 'completed')
            ->where('session_type', 'practical')
            ->sum('hours_completed');
    }

    /**
     * Get the remaining TDC hours.
     */
    public function getRemainingTdcHoursAttribute(): float
    {
        return max(0.0, $this->tdc_hours_limit - $this->used_tdc_hours);
    }

    /**
     * Get the remaining PDC hours.
     */
    public function getRemainingPdcHoursAttribute(): float
    {
        return max(0.0, $this->pdc_hours_limit - $this->used_pdc_hours);
    }
}
