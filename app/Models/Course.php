<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Course extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'title',
        'description',
        'banner_image',
        'features',
        'price',
        'duration_hours',
        'max_students',
        'type',
        'vehicle_type',
        'required_restriction',
        'status',
        'is_featured',
        'sort_order',
        'course_type',
        'license_type',
        'hours_required',
        'lto_base_hours',
        'combo_course_ids',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_hours' => 'decimal:1',
            'hours_required' => 'decimal:2',
            'lto_base_hours' => 'integer',
            'features' => 'array',
            'is_featured' => 'boolean',
            'combo_course_ids' => 'array',
        ];
    }

    public function comboCourses()
    {
        if (empty($this->combo_course_ids) || !is_array($this->combo_course_ids)) return collect();
        return self::whereIn('id', $this->combo_course_ids)->where('school_id', $this->school_id)->get();
    }

    /**
     * Get the school that owns the course.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the bookings for the course.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the progress records for the course.
     */
    public function progresses(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    /**
     * Get the packages for the course.
     */
    public function packages(): HasMany
    {
        return $this->hasMany(CoursePackage::class)->orderBy('sort_order');
    }

    /**
     * Get the modules for the course.
     */
    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_order');
    }

    /**
     * Get the enrollments for the course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(EnrollmentRequest::class);
    }

    /**
     * Scope a query to only include active courses.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the total number of enrolled students for this course.
     */
    public function enrolledStudentsCount()
    {
        return $this->bookings()->whereIn('status', ['confirmed', 'in_progress', 'completed'])->count();
    }

    /**
     * Check if the course has reached maximum enrollment.
     */
    public function isFull()
    {
        if ($this->max_students === null) {
            return false; // No limit set
        }
        return $this->enrolledStudentsCount() >= $this->max_students;
    }

    /**
     * Get available slots remaining.
     */
    public function availableSlots()
    {
        if ($this->max_students === null) {
            return null; // Unlimited
        }
        return max(0, $this->max_students - $this->enrolledStudentsCount());
    }

    /**
     * Check if course is theoretical
     */
    public function isTheoretical(): bool
    {
        return $this->course_type === 'theoretical';
    }

    /**
     * Check if course is practical
     */
    public function isPractical(): bool
    {
        return $this->course_type === 'practical';
    }

    /**
     * Check if course is combo (theoretical + practical).
     */
    public function isCombo(): bool
    {
        return $this->course_type === 'combo';
    }

    /**
     * Get license type display name
     */
    public function getLicenseTypeDisplayAttribute(): string
    {
        return match ($this->license_type) {
                'non_professional' => 'Non-Professional',
                'professional' => 'Professional',
                default => $this->license_type,
            };
    }

    /**
     * Get course type display name
     */
    public function getCourseTypeDisplayAttribute(): string
    {
        return match ($this->course_type) {
                'theoretical' => 'Theoretical',
                'practical' => 'Practical',
            'combo' => 'Combo',
                default => $this->course_type,
            };
    }

    /**
     * Scope for theoretical courses
     */
    public function scopeTheoretical($query)
    {
        return $query->where('course_type', 'theoretical');
    }

    /**
     * Scope for practical courses
     */
    public function scopePractical($query)
    {
        return $query->where('course_type', 'practical');
    }

    /**
     * Scope for courses by license type
     */
    public function scopeLicenseType($query, $type)
    {
        return $query->where('license_type', $type);
    }
}
