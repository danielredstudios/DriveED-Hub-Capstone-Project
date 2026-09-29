<?php

return [
    // Global guard: lifecycle transition emails are disabled by default.
    // These transitions are admin confirmation actions and should remain in-app only.
    'enable_lifecycle_transition_emails' => true,

    // Explicit channel policy for enrollment lifecycle transitions.
    'enrollment_transitions' => [
        'approved' => [
            'channels' => ['email', 'in_app'],
        ],
        'rejected' => [
            'channels' => ['email', 'in_app'],
        ],
        'payment_status_updated' => [
            'channels' => ['email', 'in_app'],
        ],
        'enrollment_completed' => [
            'channels' => ['email', 'in_app'],
        ],
        'enrollment_cancelled' => [
            'channels' => ['email', 'in_app'],
        ],
        'theoretical_passed' => [
            'channels' => ['email', 'in_app'],
        ],
        'license_verified' => [
            'channels' => ['email', 'in_app'],
        ],
        'license_rejected' => [
            'channels' => ['email', 'in_app'],
        ],
    ],

    // Keep lifecycle email code paths in place but explicitly disable these
    // transitions unless intentionally removed from this list.
    'disabled_lifecycle_email_transitions' => [
    ],

    // Explicit command-level notification intent.
    'commands' => [
        'reminders' => [
            'sessions' => [
                'email' => true,
                'in_app_student' => true,
                'in_app_instructor' => true,
            ],
        ],
        'bookings' => [
            'confirm_queued' => [
                'email' => false,
                'in_app' => false,
            ],
        ],
    ],
];
