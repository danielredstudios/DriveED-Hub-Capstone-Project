<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ScheduleInstructor extends Pivot
{
    protected $table = 'schedule_instructors';

    protected $fillable = [
        'school_id',
        'time_slot_id',
        'instructor_id',
        'assignment_type',
    ];

    // Relationship to time slot
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    // Relationship to instructor
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}