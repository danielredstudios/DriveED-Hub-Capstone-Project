<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Student;
use App\Models\EnrollmentRequest;

class EnrollmentValidator
{
    /**
     * Check if a student can enroll in a specific course
     * 
     * @param Student $student
     * @param Course $course
        * @param bool $isUploadingLicense True when the current enrollment request includes a license/credential upload
     * @return array ['allowed' => bool, 'message' => string]
     */
        public static function canEnrollInCourse(Student $student, Course $course, bool $isUploadingLicense = false): array
    {
        // Theoretical courses: Anyone can enroll
        if ($course->isTheoretical()) {
            return [
                'allowed' => true,
                'message' => 'You can enroll in this theoretical course.'
            ];
        }

        // Combo courses: allow enrollment without prerequisites.
        // Practical-phase checks are enforced at scheduling/booking stage.
        if (($course->course_type ?? null) === 'combo') {
            return [
                'allowed' => true,
                'message' => 'You can proceed with combo enrollment. Practical sessions unlock once required steps are completed.'
            ];
        }

        // Practical courses: submitted/pending license can enroll,
        // but practical scheduling is still guarded later in booking flow.
        if ($course->isPractical()) {
            if ($student->hasSubmittedLicense() || $isUploadingLicense) {
                return [
                    'allowed' => true,
                    'message' => 'You can proceed with enrollment.'
                ];
            }

            if ($student->isLicenseRejected()) {
                return [
                    'allowed' => false,
                    'message' => "Your submitted student driver's license was rejected. Please re-upload a valid license during enrollment."
                ];
            }

            if (!$student->hasPassedTheoretical()) {
                return [
                    'allowed' => false,
                    'message' => "To enroll in practical courses, upload your student driver's license or complete a theoretical course first."
                ];
            }

            return [
                'allowed' => false,
                'message' => "You must upload your student driver's license to enroll in practical courses."
            ];
        }

        return [
            'allowed' => true,
            'message' => 'You can enroll in this course.'
        ];
    }

    /**
     * Validate enrollment request based on experience level and course type
     * 
     * @param array $data Request data containing course_id, experience_level, credentials_file_path
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateEnrollmentRequest(array $data): array
    {
        $course = Course::find($data['course_id']);

        if (!$course) {
            return [
                'valid' => false,
                'message' => 'Course not found.'
            ];
        }

        // Combo and practical eligibility prerequisites are enforced in booking-stage logic.
        // Enrollment request itself remains open to allow phased progression.

        return [
            'valid' => true,
            'message' => 'Enrollment request is valid.'
        ];
    }

    /**
     * Check if student has active enrollments
     * 
     * @param Student $student
     * @return bool
     */
    public static function hasActiveEnrollments(Student $student): bool
    {
        return $student->activeEnrollments()->exists();
    }

    /**
     * Get student's current active enrollment
     * 
     * @param Student $student
     * @return \App\Models\Enrollment|null
     */
    public static function getCurrentEnrollment(Student $student)
    {
        return $student->activeEnrollments()->first();
    }

    /**
     * Check if student can be marked as passed theoretical
     * 
     * @param \App\Models\Enrollment $enrollment
     * @return array ['allowed' => bool, 'message' => string]
     */
    public static function canMarkTheoreticalPassed($enrollment): array
    {
        // Must be a theoretical course
        if (!$enrollment->course->isTheoretical()) {
            return [
                'allowed' => false,
                'message' => 'Only theoretical course enrollments can be marked as passed.'
            ];
        }

        // Check if already marked as passed
        if ($enrollment->theoretical_passed) {
            return [
                'allowed' => false,
                'message' => 'This student has already been marked as passed for theoretical.'
            ];
        }

        // Check if student has completed required hours
        $totalHours = (float) ($enrollment->used_tdc_hours ?? $enrollment->hours_completed ?? 0);
        $requiredHours = (float) ($enrollment->course->hours_required ?? 15);

        if ($totalHours < $requiredHours) {
            return [
                'allowed' => false,
                'message' => "Student must complete {$requiredHours} hours. Currently completed: {$totalHours} hours."
            ];
        }

        // Check for 3 unique session dates (LTO requirement)
        $uniqueDatesCount = $enrollment->sessionCompletions()
            ->where('status', 'completed')
            ->where('session_type', 'theoretical')
            ->distinct('session_date')
            ->count('session_date');

        if ($uniqueDatesCount < 3) {
            return [
                'allowed' => false,
                'message' => "LTO Requirement: Student must attend sessions on at least 3 unique dates. Currently attended: {$uniqueDatesCount} dates."
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Student can be marked as passed.'
        ];
    }

    /**
     * Validate credential file for experienced drivers
     * 
     * @param \Illuminate\Http\UploadedFile|null $file
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateCredentialFile($file): array
    {
        if (!$file) {
            return [
                'valid' => false,
                'message' => 'Please upload a credential file.'
            ];
        }

        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return [
                'valid' => false,
                'message' => 'File size must not exceed 5MB.'
            ];
        }

        // Check file type
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedTypes)) {
            return [
                'valid' => false,
                'message' => 'File must be PDF, JPG, or PNG format.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Credential file is valid.'
        ];
    }

    /**
     * Check if enrollment can be completed
     * 
     * @param \App\Models\Enrollment $enrollment
     * @return array ['allowed' => bool, 'message' => string]
     */
    public static function canCompleteEnrollment($enrollment): array
    {
        $totalHours = $enrollment->total_hours;
        $requiredHours = $enrollment->course->hours_required;

        if ($totalHours < $requiredHours) {
            return [
                'allowed' => false,
                'message' => "Student must complete {$requiredHours} hours. Currently completed: {$totalHours} hours."
            ];
        }

        // For theoretical courses, must be marked as passed
        if ($enrollment->course->isTheoretical() && !$enrollment->theoretical_passed) {
            return [
                'allowed' => false,
                'message' => 'Student must be marked as passed theoretical before completing enrollment.'
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Enrollment can be marked as complete.'
        ];
    }

    /**
     * Get detailed TDC progress for LTO compliance
     * 
     * @param EnrollmentRequest $enrollment
     * @return array
     */
    public static function getTdcProgress($enrollment): array
    {
        $completions = $enrollment->sessionCompletions->where('status', 'completed')->where('session_type', 'theoretical');
        $uniqueDates = $completions->pluck('session_date')->unique();
        $hours = (float) $completions->sum('hours_completed');
        $count = $uniqueDates->count();
        
        return [
            'hours' => $hours,
            'unique_dates_count' => $count,
            'unique_dates' => $uniqueDates,
            'is_compliant' => $hours >= 15 && $count >= 3
        ];
    }
}
