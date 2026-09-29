<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TimeSlot extends Model
{
    use HasSchoolScope;
    use HasFactory;

    private const MAX_INVALID_TIME_LOG_CACHE = 500;

    protected static array $invalidTimeLogCache = [];

    protected $fillable = [
        'school_id',
        'branch_id',
        'course_id',
        'session_type',
        'date',
        'start_time',
        'end_time',
        'status',
        'max_instructors',
        'max_students',
        'notes',
        'batch_group',
        'batch_day_number',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'max_students' => 'integer',
        'max_instructors' => 'integer',
        'batch_day_number' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructors()
    {
        return $this->belongsToMany(Instructor::class , 'schedule_instructors')
            ->withTimestamps()
            ->withPivot(['school_id', 'assignment_type']);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class , 'time_slot_id');
    }

    public function hasInstructor(int $instructorId): bool
    {
        return $this->instructors()->wherePivot('instructor_id', $instructorId)->exists();
    }

    /**
     * Check if the slot has enough instructors assigned.
     * This is for ADMIN-side instructor management.
     */
    public function isInstructorFull(): bool
    {
        return $this->instructors->count() >= ($this->max_instructors ?? 1);
    }

    /**
     * Check if instructors can self-select this slot.
     */
    public function isOpenForInstructorSelection(): bool
    {
        return $this->status === 'open' &&
            $this->instructors->count() < ($this->max_instructors ?? 0);
    }

    /**
     * Get available spots FOR INSTRUCTORS to join.
     */
    public function getAvailableInstructorSpots(): int
    {
        return max(0, ($this->max_instructors ?? 0) - $this->instructors->count());
    }

    /**
     * Get the session type, falling back to the course type if not explicitly set.
     * Course-First Logic: 
     * - If the course is 'practical' or 'theoretical', we trust the course type.
     * - Only for 'combo' courses do we check the session_type column.
     */
    public function getResolvedSessionTypeAttribute(): string
    {
        $courseType = $this->course?->course_type;

        // If it's a dedicated course, the session must match that type.
        if (in_array($courseType, ['theoretical', 'practical'], true)) {
            return $courseType;
        }

        // For combo courses, look at the explicit session type, defaulting to theoretical.
        if (in_array($this->session_type, ['theoretical', 'practical'], true)) {
            return $this->session_type;
        }
        
        return 'theoretical';
    }

    /**
     * Logic for Student Booking Visibility & Capacity.
     * 1. 0 Instructors = 0 Visibility/Capacity.
     * 2. TDC (Theoretical) = max_students (fixed classroom size).
     * 3. PDC (Practical) = instructors_count (1-on-1 driving).
     */
    public function getAvailableStudentSpots(): int
    {
        $instructorCount = $this->instructors->count();
        if ($instructorCount === 0) {
            return 0;
        }

        $sessionType = $this->resolved_session_type;

        $bookingsCount = $this->bookings
            ->whereIn('status', ['pending', 'scheduled', 'confirmed', 'done', 'completed'])
            ->count();

        if ($sessionType === 'theoretical') {
            // Classroom capacity is fixed to max_students once at least 1 instructor joins.
            return max(0, ($this->max_students ?? 30) - $bookingsCount);
        }

        // Practical capacity is 1-on-1 with the number of instructors present.
        return max(0, $instructorCount - $bookingsCount);
    }

    /**
     * Scope for Student Booking View.
     * Ensures students only see slots that have at least one instructor.
     */
    public function scopeVisibleToStudents($query)
    {
        return $query->whereHas('instructors')
                     ->where('status', 'open');
    }

    // Keep legacy method names if they are used elsewhere to prevent breaking.
    public function getAvailableSpots(): int
    {
        return $this->getAvailableInstructorSpots();
    }

    public function isFull(): bool
    {
        return $this->isInstructorFull();
    }

    public function isOpenForSelection(): bool
    {
        return $this->isOpenForInstructorSelection();
    }

    // Get count of admin-assigned instructors
    public function getAdminAssignedCount(): int
    {
        return $this->instructors
            ->where('pivot.assignment_type', 'admin_assigned')
            ->count();
    }

    // Get count of self-selected instructors
    public function getSelfSelectedCount(): int
    {
        return $this->instructors
            ->where('pivot.assignment_type', 'self_selected')
            ->count();
    }

    // Get instructors by assignment type
    public function getAdminAssignedInstructors()
    {
        return $this->instructors()
            ->wherePivot('assignment_type', 'admin_assigned')
            ->get();
    }

    public function getSelfSelectedInstructors()
    {
        return $this->instructors()
            ->wherePivot('assignment_type', 'self_selected')
            ->get();
    }

    // Check if a specific instructor can select this slot
    public function canInstructorSelect(int $instructorId): bool
    {
        return $this->isOpenForSelection() &&
            !$this->hasInstructor($instructorId);
    }

    public function getFormattedStartTimeAttribute(): ?string
    {
        return $this->formatTimeValue($this->start_time, 'start_time');
    }

    public function getFormattedEndTimeAttribute(): ?string
    {
        return $this->formatTimeValue($this->end_time, 'end_time');
    }

    private function formatTimeValue(?string $value, string $attribute): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable $e) {
            // Use a bounded process-local cache to avoid unbounded memory growth
            // under long-lived workers (Octane/Swoole/RoadRunner).
            $cacheKey = ($this->id ?? 'new') . ':' . $attribute . ':' . substr(sha1($value), 0, 16);

            if (!isset(self::$invalidTimeLogCache[$cacheKey])) {
                if (count(self::$invalidTimeLogCache) < self::MAX_INVALID_TIME_LOG_CACHE) {
                    Log::warning('Invalid time format encountered in TimeSlot accessor.', [
                        'timeslot_id' => $this->id,
                        'attribute' => $attribute,
                        'value' => $value,
                        'error' => $e->getMessage(),
                    ]);

                    self::$invalidTimeLogCache[$cacheKey] = true;
                }
            }

            return null;
        }
    }
}