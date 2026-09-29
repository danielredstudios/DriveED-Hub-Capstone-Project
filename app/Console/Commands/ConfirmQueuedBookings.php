<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\School;
use Carbon\Carbon;

class ConfirmQueuedBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:confirm-queued';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-confirm bookings that have been in queue for the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for bookings ready to be confirmed...');

        $policy = config('notification_policy.commands.bookings.confirm_queued', []);
        $notificationsEnabled = (bool) ($policy['email'] ?? false) || (bool) ($policy['in_app'] ?? false);

        if ($notificationsEnabled) {
            $this->warn('Notification channels are configured for bookings:confirm-queued, but this command intentionally applies status changes only.');
        }
        
        $schools = School::with('schoolSetting')->get();
        $totalConfirmed = 0;
        
        foreach ($schools as $school) {
            $settings = $school->schoolSetting;
            $queueDays = $settings?->booking_queue_days ?? 3;
            $queueEnabled = $settings?->enable_booking_queue ?? true;
            
            if (!$queueEnabled) {
                continue;
            }
            
            // Get pending bookings older than queue days
            $cutoffDate = Carbon::now()->subDays($queueDays);
            
            $bookings = Booking::where('school_id', $school->id)
                ->where('status', 'pending')
                ->where('created_at', '<=', $cutoffDate)
                ->get();
            
            foreach ($bookings as $booking) {
                $booking->update(['status' => 'scheduled']);
                $totalConfirmed++;
                $this->info("Confirmed booking #{$booking->id} for student {$booking->student->name}");
            }
        }
        
        $this->info("Total bookings confirmed: {$totalConfirmed}");
        
        return Command::SUCCESS;
    }
}
