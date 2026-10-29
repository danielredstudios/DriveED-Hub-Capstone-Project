<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\SystemLog;

class ArchiveInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:archive-inactive {--days=90 : Inactivity threshold in days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically archive user accounts that have been inactive for specified days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $studentCount = Student::whereNull('archived_at')
            ->where(function ($q) use ($cutoff) {
                $q->where('status', 'inactive')
                  ->orWhere(function ($sub) use ($cutoff) {
                      $sub->whereNotNull('last_login_at')->where('last_login_at', '<', $cutoff);
                  })
                  ->orWhere(function ($sub) use ($cutoff) {
                      $sub->whereNull('last_login_at')->where('created_at', '<', $cutoff);
                  });
            })
            ->update([
                'archived_at' => now(),
                'status' => 'archived',
            ]);

        $instructorCount = Instructor::whereNull('archived_at')
            ->where(function ($q) use ($cutoff) {
                $q->where('status', 'inactive')
                  ->orWhere(function ($sub) use ($cutoff) {
                      $sub->whereNotNull('last_login_at')->where('last_login_at', '<', $cutoff);
                  })
                  ->orWhere(function ($sub) use ($cutoff) {
                      $sub->whereNull('last_login_at')->where('created_at', '<', $cutoff);
                  });
            })
            ->update([
                'archived_at' => now(),
                'status' => 'archived',
            ]);

        $total = $studentCount + $instructorCount;

        SystemLog::logInfo(
            "CLI: Auto-archived {$total} inactive users (Threshold: {$days} days)",
            'system',
            ['students' => $studentCount, 'instructors' => $instructorCount, 'days' => $days],
            null,
            'cli_archive_inactive_users'
        );

        $this->info("Successfully archived {$studentCount} inactive student(s) and {$instructorCount} inactive instructor(s) (Threshold: {$days} days).");

        return 0;
    }
}
