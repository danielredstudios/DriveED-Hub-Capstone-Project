<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'generated_by',
        'report_type',
        'title',
        'description',
        'filters',
        'data',
        'date_from',
        'date_to',
        'file_path',
        'file_type',
    ];

    protected $casts = [
        'filters' => 'array',
        'data' => 'array',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    // Report Types
    const TYPE_ENROLLMENT = 'enrollment';
    const TYPE_DRIVING_LESSONS = 'driving_lessons';
    const TYPE_PRACTICAL_LESSONS = 'practical_lessons';
    const TYPE_THEORETICAL_LESSONS = 'theoretical_lessons';
    const TYPE_FINANCIAL = 'financial';
    const TYPE_ATTENDANCE = 'attendance';
    const TYPE_INSTRUCTOR_PERFORMANCE = 'instructor_performance';
    const TYPE_STUDENT_PROGRESS = 'student_progress';
    const TYPE_BOOKING_SUMMARY = 'booking_summary';
    const TYPE_CANCELLATION = 'cancellation';

    /**
     * Get the school that owns the report.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the admin who generated the report.
     */
    public function generatedBy()
    {
        return $this->belongsTo(Admin::class, 'generated_by');
    }

    /**
     * Scope a query to only include reports of a given type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope a query to only include reports within a date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}