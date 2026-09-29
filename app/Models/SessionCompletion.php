<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionCompletion extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'enrollment_id',
        'instructor_id',
        'session_type',
        'hours_completed',
        'session_date',
        'session_time',
        'start_time',
        'end_time',
        'status',
        'notes',
        'logged_by',
    ];

    protected $casts = [
        'hours_completed' => 'decimal:2',
        'session_date' => 'date',
        'session_time' => 'string',
    ];

    /**
     * Get the school
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the enrollment request that owns this session.
     * Note: enrollment_id column references enrollment_requests table
     * (system transitioned from enrollments to enrollment_requests).
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class , 'enrollment_id');
    }

    /**
     * Alias for enrollment() — used by ExportController and other code
     * that references the enrollmentRequest relationship name.
     */
    public function enrollmentRequest(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class , 'enrollment_id');
    }

    /**
     * Get the instructor who conducted this session
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Get the user who logged this session
     */
    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(Instructor::class , 'logged_by');
    }

    /**
     * Get the student (learner) through enrollment request
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            Student::class ,
            EnrollmentRequest::class ,
            'id', // Foreign key on enrollment_requests table
            'id', // Foreign key on students table
            'enrollment_id', // Local key on session_completions table
            'learner_id' // Local key on enrollment_requests table
        );
    }

    /**
     * Get the course through enrollment request
     */
    public function course(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            Course::class ,
            EnrollmentRequest::class ,
            'id', // Foreign key on enrollment_requests table
            'id', // Foreign key on courses table
            'enrollment_id', // Local key on session_completions table
            'course_id' // Local key on enrollment_requests table
        );
    }

    /**
     * Scope for theoretical sessions
     */
    public function scopeTheoretical($query)
    {
        return $query->where('session_type', 'theoretical');
    }

    /**
     * Scope for practical sessions
     */
    public function scopePractical($query)
    {
        return $query->where('session_type', 'practical');
    }

    /**
     * Scope for sessions by date range
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('session_date', [$start, $end]);
    }

    /**
     * Scope for recent sessions
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('session_date', '>=', now()->subDays($days));
    }
}
