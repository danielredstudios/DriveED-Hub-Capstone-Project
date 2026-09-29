<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorRemovalRequest extends Model
{
    protected $fillable = [
        'school_id',
        'time_slot_id',
        'instructor_id',
        'schedule_instructor_id',
        'status',
        'reason',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Get the school this request belongs to
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the time slot this request is for
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    /**
     * Get the instructor who made the request
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Get the admin who processed the request
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    /**
     * Get the schedule instructor pivot record
     */
    public function scheduleInstructor(): BelongsTo
    {
        return $this->belongsTo(ScheduleInstructor::class);
    }

    /**
     * Check if the request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Scope to get only pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
