<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $table = 'progresses';

    protected $fillable = [
        'school_id',
        'student_id',
        'course_id',
        'notes',
        'completion_percent',
        'last_updated',
    ];

    protected $casts = [
        'completion_percent' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    /**
     * Get the school that owns the progress.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the student for the progress.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the course for the progress.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope a query to only include progress for a specific student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to only include completed courses.
     */
    public function scopeCompleted($query)
    {
        return $query->where('completion_percent', '>=', 100);
    }

    /**
     * Scope a query to only include in-progress courses.
     */
    public function scopeInProgress($query)
    {
        return $query->where('completion_percent', '>', 0)
                     ->where('completion_percent', '<', 100);
    }
}
