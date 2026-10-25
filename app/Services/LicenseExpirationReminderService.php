<?php

namespace App\Services;

use App\Mail\LicenseExpirationReminderMail;
use App\Models\Instructor;
use App\Models\Notification;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
     * Check a single student or instructor and dispatch an in-app & email notification if expiring or expired.
     */
    public function checkUser(Student|Instructor $user, bool $force = false, bool $sendEmail = true): ?Notification
    {
        if (!$user->license_expiration_date) {
            return null;
        }

        $days = $user->getLicenseDaysRemaining();
        if ($days === null || ($days > self::WARNING_THRESHOLD_DAYS && !$force)) {
            return null;
        }

        // Check if an unread or recent reminder notification was already sent within cooldown days, unless forced
        if (!$force) {
            $recentNotification = Notification::forSchool($user->school_id)
                ->forNotifiable($user)
                ->where('type', 'license_expiration_reminder')
                ->where('created_at', '>=', now()->subDays(self::NOTIFICATION_COOLDOWN_DAYS))
                ->first();

            if ($recentNotification) {
                return null;
            }
        }

        $school = $user->school;
        $licenseNum = $user->license_number ? "({$user->license_number}) " : '';
        $expiryDateStr = $user->license_expiration_date->format('M d, Y');

        if ($days < 0) {
            $absDays = abs($days);
            $dayWord = $absDays === 1 ? 'day' : 'days';
            $title = "Driver's License Expired";
            $message = "Your driver's license {$licenseNum}expired {$absDays} {$dayWord} ago on {$expiryDateStr}. Note: Driver's license renewals must be processed directly through the LTO (LTMS), not through the driving school.";
            $icon = 'warning';
        } else {
            $dayWord = $days === 1 ? 'day' : 'days';
            $title = "Driver's License Expiring Soon";
            $message = "Your driver's license {$licenseNum}will expire in {$days} {$dayWord} on {$expiryDateStr}. Reminder: Driver's license renewal must be processed directly through the LTO via portal.lto.gov.ph, not through the driving school.";
            $icon = 'license';
        }

        $actionUrl = null;
        if ($user instanceof Student && $school) {
            $actionUrl = school_route('student.profile', [], $school);
        } elseif ($user instanceof Instructor && $school) {
            $actionUrl = school_route('instructor.profile', [], $school);
        }

        $notification = Notification::send(
            $user,
            'license_expiration_reminder',
            $title,
            $message,
            $icon,
            $actionUrl
        );

        // Dispatch email notification if enabled and recipient has an email address
        if ($sendEmail && !empty($user->email)) {
            try {
                Mail::to($user->email)->send(
                    new LicenseExpirationReminderMail($user, $school, $days)
                );
            } catch (\Throwable $e) {
                Log::warning("Failed to send license expiration reminder email to {$user->email}: " . $e->getMessage());
            }
        }

        return $notification;
    }

    /**
     * Check all students and instructors across all schools (or a specific school).
     */
    public function checkAllUsers(?int $schoolId = null, bool $sendEmail = true): array
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

            $notification = $this->checkUser($student, false, $sendEmail);
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

            $notification = $this->checkUser($instructor, false, $sendEmail);
            if ($notification) {
                $stats['notifications_sent']++;
            }
        }

        return $stats;
    }

    /**
     * Get real-time summary counts of expiring and expired licenses.
     */
    public function getExpiringUsersCount(?int $schoolId = null, int $thresholdDays = self::WARNING_THRESHOLD_DAYS): array
    {
        $studentQuery = Student::whereNotNull('license_expiration_date');
        $instructorQuery = Instructor::whereNotNull('license_expiration_date');

        if ($schoolId) {
            $studentQuery->where('school_id', $schoolId);
            $instructorQuery->where('school_id', $schoolId);
        }

        $now = now()->startOfDay();
        $thresholdDate = now()->addDays($thresholdDays)->endOfDay();

        $studentsExpiring = (clone $studentQuery)
            ->whereDate('license_expiration_date', '>=', $now)
            ->whereDate('license_expiration_date', '<=', $thresholdDate)
            ->count();

        $studentsExpired = (clone $studentQuery)
            ->whereDate('license_expiration_date', '<', $now)
            ->count();

        $instructorsExpiring = (clone $instructorQuery)
            ->whereDate('license_expiration_date', '>=', $now)
            ->whereDate('license_expiration_date', '<=', $thresholdDate)
            ->count();

        $instructorsExpired = (clone $instructorQuery)
            ->whereDate('license_expiration_date', '<', $now)
            ->count();

        return [
            'students_expiring' => $studentsExpiring,
            'students_expired' => $studentsExpired,
            'instructors_expiring' => $instructorsExpiring,
            'instructors_expired' => $instructorsExpired,
            'total_expiring' => $studentsExpiring + $instructorsExpiring,
            'total_expired' => $studentsExpired + $instructorsExpired,
            'total_alerts' => $studentsExpiring + $instructorsExpiring + $studentsExpired + $instructorsExpired,
        ];
    }
}
