<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'course_id',
        'enrollment_request_id',
        'status',
        'hours_completed',
        'enrolled_at',
        'completed_at',
        'theoretical_passed',
        'theoretical_passed_at',
        'theoretical_passed_by',
        'theoretical_pass_notes',
    ];

    protected $casts = [
        'hours_completed' => 'decimal:2',
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'theoretical_passed' => 'boolean',
        'theoretical_passed_at' => 'datetime',
    ];

    /**
     * Get the school
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the enrollment request that created this enrollment
     */
    public function enrollmentRequest(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class);
    }

    /**
     * Get all session completions for this enrollment
     */
    public function sessionCompletions(): HasMany
    {
        return $this->hasMany(SessionCompletion::class);
    }

    /**
     * Check if enrollment is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if enrollment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if enrollment is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): float
    {
        if (!$this->course || $this->course->hours_required <= 0) {
            return 0;
        }
        
        $percentage = ($this->hours_completed / $this->course->hours_required) * 100;
        return min(100, round($percentage, 1));
    }

    /**
     * Check if hours requirement is met
     */
    public function hasMetHoursRequirement(): bool
    {
        if (!$this->course) {
            return false;
        }
        
        return $this->hours_completed >= $this->course->hours_required;
    }

    /**
     * Add hours to this enrollment (called when session is logged)
     */
    public function addHours(float $hours): void
    {
        $this->increment('hours_completed', $hours);
    }

    /**
     * Mark enrollment as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Unlock the student
        $this->student->unlockFromCourse();

        // If this was a theoretical course, mark student as passed theoretical
        if ($this->course && $this->course->course_type === 'theoretical') {
            $this->student->markTheoreticalPassed();
        }
    }

    /**
     * Mark enrollment as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
        ]);

        // Unlock the student
        $this->student->unlockFromCourse();
    }

    /**
     * Scope for active enrollments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed enrollments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
