<?php

namespace App\Services;

use App\Models\Instructor;
use App\Models\Notification;
use App\Models\Student;
use Carbon\Carbon;

class LicenseExpirationReminderService
{
    /**
     * Threshold in days to warn users before license expiration.
     */
    public const WARNING_THRESHOLD_DAYS = 60;

    /**
     * Cooldown in days between automated notifications for the same user.
     */
    public const NOTIFICATION_COOLDOWN_DAYS = 7;

    /**
     * Check a single student or instructor and dispatch a notification if expiring or expired.
     */
    public function checkUser(Student|Instructor $user): ?Notification
    {
        if (!$user->license_expiration_date) {
            return null;
        }

        $days = $user->getLicenseDaysRemaining();
        if ($days === null || $days > self::WARNING_THRESHOLD_DAYS) {
            return null;
        }

        // Check if an unread or recent reminder notification was already sent within cooldown days
        $recentNotification = Notification::forSchool($user->school_id)
            ->forNotifiable($user)
            ->where('type', 'license_expiration_reminder')
            ->where('created_at', '>=', now()->subDays(self::NOTIFICATION_COOLDOWN_DAYS))
            ->first();

        if ($recentNotification) {
            return null;
        }

        $school = $user->school;
        $licenseNum = $user->license_number ? "({$user->license_number}) " : '';
        $expiryDateStr = $user->license_expiration_date->format('M d, Y');

        if ($days < 0) {
            $absDays = abs($days);
            $dayWord = $absDays === 1 ? 'day' : 'days';
            $title = "⚠️ Driver's License Expired";
            $message = "Your driver's license {$licenseNum}expired {$absDays} {$dayWord} ago on {$expiryDateStr}. Note: Driver's license renewals must be processed directly through the LTO (LTMS), not through the driving school.";
            $icon = 'warning';
        } else {
            $dayWord = $days === 1 ? 'day' : 'days';
            $title = "🪪 Driver's License Expiring Soon";
            $message = "Your driver's license {$licenseNum}will expire in {$days} {$dayWord} on {$expiryDateStr}. Reminder: Driver's license renewal must be processed directly through the LTO via portal.lto.gov.ph, not through the driving school.";
            $icon = 'license';
        }

        $actionUrl = null;
        if ($user instanceof Student && $school) {
            $actionUrl = school_route('student.profile', [], $school);
        } elseif ($user instanceof Instructor && $school) {
            $actionUrl = school_route('instructor.settings', [], $school);
        }

        return Notification::send(
            $user,
            'license_expiration_reminder',
            $title,
            $message,
            $icon,
            $actionUrl
        );
    }

    /**
     * Check all students and instructors across all schools (or a specific school).
     */
    public function checkAllUsers(?int $schoolId = null): array
    {
        $stats = [
            'students_checked' => 0,
            'instructors_checked' => 0,
            'notifications_sent' => 0,
            'expiring_soon' => 0,
            'already_expired' => 0,
        ];

        $studentQuery = Student::whereNotNull('license_expiration_date');
        $instructorQuery = Instructor::whereNotNull('license_expiration_date');

        if ($schoolId) {
            $studentQuery->where('school_id', $schoolId);
            $instructorQuery->where('school_id', $schoolId);
        }

        $students = $studentQuery->get();
        foreach ($students as $student) {
            $stats['students_checked']++;
            if ($student->isLicenseExpired()) {
                $stats['already_expired']++;
            } elseif ($student->isLicenseExpiringSoon(self::WARNING_THRESHOLD_DAYS)) {
                $stats['expiring_soon']++;
            }

            $notification = $this->checkUser($student);
            if ($notification) {
                $stats['notifications_sent']++;
            }
        }

        $instructors = $instructorQuery->get();
        foreach ($instructors as $instructor) {
            $stats['instructors_checked']++;
            if ($instructor->isLicenseExpired()) {
                $stats['already_expired']++;
            } elseif ($instructor->isLicenseExpiringSoon(self::WARNING_THRESHOLD_DAYS)) {
                $stats['expiring_soon']++;
            }

            $notification = $this->checkUser($instructor);
            if ($notification) {
                $stats['notifications_sent']++;
            }
        }

        return $stats;
    }
}
