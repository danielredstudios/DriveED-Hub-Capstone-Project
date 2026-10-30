<?php

namespace App\Console\Commands;

use App\Models\SessionCompletion;
use App\Services\SessionLoggingService;
use Illuminate\Console\Command;

class LinkSessionBookings extends Command
{
    protected $signature = 'sessions:link-bookings {--school= : Limit to a school ID}';
    protected $description = 'Link existing manual session logs to the admin verification queue';

    public function handle(SessionLoggingService $logging): int
    {
        $count = 0;
        $failed = 0;
        SessionCompletion::whereNull('booking_id')->whereNull('archived_at')
            ->whereHas('enrollment')->whereHas('instructor')
            ->when($this->option('school'), fn ($query, $school) => $query->where('school_id', $school))
            ->chunkById(100, function ($sessions) use ($logging, &$count, &$failed) {
                foreach ($sessions as $session) {
                    try {
                        $logging->linkManual($session, preserveBookingFeedback: true);
                        $count++;
                    } catch (\Throwable $error) {
                        $failed++;
                        $this->warn("Session {$session->id}: {$error->getMessage()}");
                    }
                }
            });
        $this->info("Linked {$count} session logs; {$failed} require review.");
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
