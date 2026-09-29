<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasSchoolScope;
    use HasFactory, Notifiable;

    protected $fillable = [
        'school_id',
        'branch_id',
        'name',
        'email',
        'password',
        'contact',
        'address',
        'location',
        'enrollment_date',
        'profile_picture',
        'student_license_path',
        'student_license_data',
        'student_license_mime_type',
        'student_license_filename',
        'student_license_status',
        'student_license_verified_at',
        'student_license_verified_by',
        'student_license_rejection_reason',
        'experience_level',
        'has_passed_theoretical',
        'theoretical_passed_at',
        'verification_code_expires_at',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'last_logout_at',
        'status',
        'is_active',
        'must_reset_password',
        'dl_code',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
        'student_license_data',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'has_passed_theoretical' => 'boolean',
            'theoretical_passed_at' => 'datetime',
            'student_license_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'verification_code_expires_at' => 'datetime',
            'is_course_locked' => 'boolean',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'last_logout_at' => 'datetime',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branchRelation()
    {
        return $this->belongsTo(Branch::class , 'branch_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the active enrollment for this student
     */
    public function activeEnrollment()
    {
        return $this->belongsTo(EnrollmentRequest::class , 'active_enrollment_id');
    }

    /**
     * Get all enrollment records (from enrollment_requests table)
     */
    public function enrollmentRecords()
    {
        return $this->hasMany(EnrollmentRequest::class , 'learner_id');
    }

    /**
     * Check if student is currently enrolled in a course
     */
    public function isEnrolledInCourse(): bool
    {
        return $this->is_course_locked && $this->active_enrollment_id !== null;
    }

    /**
     * Check if student can enroll in a new course
     */
    public function canEnrollInNewCourse(): bool
    {
        return !$this->is_course_locked;
    }

    /**
     * Lock student to a course
     */
    public function lockToCourse(EnrollmentRequest $enrollment): void
    {
        $this->update([
            'active_enrollment_id' => $enrollment->id,
            'is_course_locked' => true,
        ]);
    }

    /**
     * Unlock student from course (when course is completed)
     */
    public function unlockFromCourse(): void
    {
        $this->update([
            'active_enrollment_id' => null,
            'is_course_locked' => false,
        ]);
    }

    public function progresses()
    {
        return $this->hasMany(Progress::class);
    }

    public function enrollmentRequests()
    {
        return $this->hasMany(EnrollmentRequest::class , 'learner_id');
    }

    /**
     * Get courses the student is enrolled in through their bookings
     */
    public function enrolledCourses()
    {
        return $this->hasManyThrough(
            Course::class ,
            Booking::class ,
            'student_id', // Foreign key on bookings table
            'id', // Foreign key on courses table
            'id', // Local key on students table
            'course_id' // Local key on bookings table
        )->distinct();
    }

    // Helper methods for role
    public function isGuest()
    {
        return $this->role === 'guest';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function promoteToStudent()
    {
        $this->role = 'student';
        $this->save();
    }

    /**
     * Get all enrollments for this student (all enrollment requests)
     */
    public function enrollments()
    {
        return $this->hasMany(EnrollmentRequest::class, 'learner_id');
    }

    public function lessonCompletions()
    {
        return $this->hasMany(StudentLessonCompletion::class);
    }


    /**
     * Get active enrollments
     */
    public function activeEnrollments()
    {
        return $this->hasMany(EnrollmentRequest::class , 'learner_id')
            ->where('status', 'approved');
    }

    /**
     * Check if student has passed theoretical
     */
    public function hasPassedTheoretical(): bool
    {
        return $this->has_passed_theoretical === true;
    }

    /**
     * Check if student is new driver
     */
    public function isNewDriver(): bool
    {
        return $this->experience_level === 'new_driver';
    }

    /**
     * Check if student is experienced
     */
    public function isExperienced(): bool
    {
        return $this->experience_level === 'experienced';
    }

    /**
     * Check if student can enroll in practical courses
     */
    public function canEnrollPractical(): bool
    {
        return $this->hasVerifiedLicense();
    }

    /**
     * Check if student has a verified student driver's license
     */
    public function hasVerifiedLicense(): bool
    {
        return $this->student_license_status === 'verified';
    }

    /**
     * Check if student's license is pending verification
     */
    public function isLicensePending(): bool
    {
        return $this->student_license_status === 'pending';
    }

    /**
     * Check if student's license was rejected
     */
    public function isLicenseRejected(): bool
    {
        return $this->student_license_status === 'rejected';
    }

    /**
     * Check if student has not uploaded a license yet
     */
    public function hasNoLicense(): bool
    {
        return ($this->student_license_status === 'none' || $this->student_license_status === null)
            && !$this->hasStoredLicense();
    }

    /**
     * Check if any license file is stored for the student.
     */
    public function hasStoredLicense(): bool
    {
        return !empty($this->student_license_path) || !empty($this->student_license_data);
    }

    /**
     * Check if a license was uploaded early and saved as draft.
     */
    public function hasDraftLicense(): bool
    {
        return $this->hasStoredLicense()
            && !$this->isLicensePending()
            && !$this->hasVerifiedLicense()
            && !$this->isLicenseRejected();
    }

    /**
     * Check if student has submitted a license and it is awaiting verification.
     */
    public function hasSubmittedLicense(): bool
    {
        return $this->isLicensePending() || $this->hasVerifiedLicense() || $this->hasDraftLicense();
    }

    /**
     * Get the admin who verified the license
     */
    public function licenseVerifiedBy()
    {
        return $this->belongsTo(Admin::class , 'student_license_verified_by');
    }

    /**
     * Mark student as passed theoretical
     */
    public function markTheoreticalPassed()
    {
        $this->update([
            'has_passed_theoretical' => true,
            'theoretical_passed_at' => now(),
        ]);
    }

    /**
     * Scope for students who passed theoretical
     */
    public function scopePassedTheoretical($query)
    {
        return $query->where('has_passed_theoretical', true);
    }

    /**
     * Scope for new drivers
     */
    public function scopeNewDrivers($query)
    {
        return $query->where('experience_level', 'new_driver');
    }

    /**
     * Scope for experienced drivers
     */
    public function scopeExperienced($query)
    {
        return $query->where('experience_level', 'experienced');
    }
    /**
     * Generate a 6-digit OTP code
     */
    public function generateVerificationCode()
    {
        $this->verification_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->verification_code_expires_at = now()->addMinutes(15);
        $this->save();

        return $this->verification_code;
    }

    /**
     * Check if verification code is valid with hardening.
     */
    public function isVerificationCodeValid($code)
    {
        // 1. Expiry check (15 mins)
        if (!$this->verification_code_expires_at || $this->verification_code_expires_at->isPast()) {
            return false;
        }

        // 2. Brute-force protection: Limit to 5 attempts
        if ($this->verification_attempts >= 5) {
            return false;
        }

        // 3. Increment attempts
        $this->increment('verification_attempts');
        $this->last_verification_attempt_at = now();
        $this->save();

        // 4. Comparison
        return $this->verification_code === $code;
    }

    /**
     * Mark email as verified and clear OTP (Single-use).
     */
    public function markEmailAsVerified()
    {
        $this->email_verified_at = now();
        $this->verification_code = null;
        $this->verification_code_expires_at = null;
        $this->verification_attempts = 0;
        $this->save();
    }

    /**
     * Check if email is verified
     */
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }}