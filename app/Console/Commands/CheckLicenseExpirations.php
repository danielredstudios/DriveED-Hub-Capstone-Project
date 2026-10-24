<?php

namespace App\Console\Commands;

use App\Services\LicenseExpirationReminderService;
use Illuminate\Console\Command;

class CheckLicenseExpirations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'license:check-expirations {--school= : Optional school ID to check}';

    /**
     * The console command description.
     */
    protected $description = "Scan drivers licenses for approaching expiration dates and send notifications";

    /**
     * Execute the console command.
     */
    public function handle(LicenseExpirationReminderService $service): int
    {
        $schoolId = $this->option('school') ? (int) $this->option('school') : null;

        $this->info("Scanning driver's licenses for approaching expiration dates...");

        $stats = $service->checkAllUsers($schoolId);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Students with Expiry Date Checked', $stats['students_checked']],
                ['Instructors with Expiry Date Checked', $stats['instructors_checked']],
                ['Licenses Expiring Soon (<= 60 days)', $stats['expiring_soon']],
                ['Licenses Already Expired', $stats['already_expired']],
                ['New Notification Reminders Dispatched', $stats['notifications_sent']],
            ]
        );

        $this->info("License expiration check completed successfully.");

        return Command::SUCCESS;
    }
}
