<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasSchoolScope;
    use HasFactory;

    // Booking Statuses
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_DONE = 'done';           // Marked by Instructor
    const STATUS_COMPLETED = 'completed'; // Verified by Admin
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no-show';

    protected $fillable = [
        'school_id',
        'branch_id',
        'student_id',
        'instructor_id',
        'course_id',
        'package_id',
        'enrollment_request_id',
        'time_slot_id',
        'scheduled_at',
        'booking_date',
        'status',
        'cancelled_by',
        'cancellation_reason',
        'cancelled_at',
        'attendance_status',
        'payment_status',
        'total_amount',
        'notes',
        'instructor_feedback',
        'attendance_marked_at',
        'session_grade',
        'student_feedback',
        'skills_practiced',
        'session_status',
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'scheduled_at' => 'datetime',
        'attendance_marked_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'skills_practiced' => 'array',
    ];

    /**
     * Get the school that owns the booking.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the branch for the booking.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the student that owns the booking.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the instructor for the booking.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Get the course for the booking.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the package for the booking.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(CoursePackage::class , 'package_id');
    }

    /**
     * Get the time slot for the booking.
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class , 'time_slot_id');
    }

    /**
     * Get the enrollment request for the booking.
     */
    public function enrollmentRequest(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class , 'enrollment_request_id');
    }

    /**
     * Get the payment for the booking.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scope a query to only include bookings for a specific student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to only include bookings for a specific instructor.
     */
    public function scopeForInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    /**
     * Scope a query to only include bookings with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include upcoming bookings.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at', 'asc');
    }

    /**
     * Scope a query to only include past bookings.
     */
    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at', 'desc');
    }

    /**
     * Check if the booking is ready for admin validation.
     */
    public function isReadyForValidation(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    /**
     * Check if the booking is fully completed and logged.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get the duration of the booking in hours.
     * Calculated from the associated TimeSlot.
     */
    public function getDurationHoursAttribute(): float
    {
        if (!$this->timeSlot) {
            return 0.0;
        }

        try {
            $start = \Illuminate\Support\Carbon::parse($this->timeSlot->start_time);
            $end = \Illuminate\Support\Carbon::parse($this->timeSlot->end_time);
            
            // If end time is before start time, assume it crossed midnight
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            return round($start->diffInMinutes($end) / 60, 1);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Get the session type (theoretical or practical) for this booking.
     */
    public function getSessionTypeAttribute(): string
    {
        return $this->timeSlot ? $this->timeSlot->resolved_session_type : 'theoretical';
    }
}
