<?php

namespace App\Support;

use App\Models\School;

class DemoAccountProtection
{
    /**
     * Account patterns that must keep fixed identity fields.
     */
    private const ACCOUNT_PATTERNS = [
        'lyspeed-driving' => [
            'student' => '/^(guest|student)(?:[1-9]|1[0-5])@lyspeed\.test$/i',
            'instructor' => '/^instructor(?:[1-9]|1[0-5])@lyspeed\.test$/i',
        ],
        'drived-hub' => [
            'student' => '/^(guest|student)(?:[1-9]|1[0-5])@drivedhub\.test$/i',
            'instructor' => '/^instructor(?:[1-9]|1[0-5])@drivedhub\.test$/i',
        ],
    ];

    public static function isProtectedAccount(string $email, string $userType, School $school): bool
    {
        $slug = (string) $school->slug;
        $type = strtolower(trim($userType));

        if (!isset(self::ACCOUNT_PATTERNS[$slug][$type])) {
            return false;
        }

        $normalizedEmail = strtolower(trim($email));

        return (bool) preg_match(self::ACCOUNT_PATTERNS[$slug][$type], $normalizedEmail);
    }
}