<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Notification;
use App\Mail\SessionReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendSessionReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reminders:sessions {--hours=24 : Hours before session to send reminder}';

    /**
     * The console command description.
     */
    protected $description = 'Send email and in-app reminders for upcoming driving sessions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $policy = config('notification_policy.commands.reminders.sessions', []);

        $sendEmail = (bool) ($policy['email'] ?? true);
        $sendInAppStudent = (bool) ($policy['in_app_student'] ?? true);
        $sendInAppInstructor = (bool) ($policy['in_app_instructor'] ?? true);

        $now = now();
        $reminderWindow = $now->copy()->addHours($hours);

        $this->info("Sending reminders for sessions between {$now->format('Y-m-d H:i')} and {$reminderWindow->format('Y-m-d H:i')}...");

        // Find confirmed bookings within the reminder window that haven't been reminded yet
        $bookings = Booking::with(['student', 'instructor', 'course', 'timeSlot', 'school'])
            ->where('status', 'confirmed')
            ->where('scheduled_at', '>', $now)
            ->where('scheduled_at', '<=', $reminderWindow)
            ->get();

        $sentCount = 0;
        $failedCount = 0;

        foreach ($bookings as $booking) {
            if (!$booking->student || !$booking->school) {
                continue;
            }

            // Check if we already sent a reminder notification for this booking
            $alreadySent = Notification::where('type', 'session_reminder')
                ->where('notifiable_type', get_class($booking->student))
                ->where('notifiable_id', $booking->student->id)
                ->where('message', 'LIKE', '%Booking #' . $booking->id . '%')
                ->where('created_at', '>', $now->copy()->subHours($hours))
                ->exists();

            if ($alreadySent) {
                $this->line("  Skipping booking #{$booking->id} (already reminded)");
                continue;
            }

            try {
                // Channel behavior is controlled by notification_policy.commands.reminders.sessions.
                if ($sendEmail) {
                    Mail::to($booking->student->email)
                        ->send(new SessionReminder($booking, $booking->school));
                }

                // Create in-app notification for student
                $sessionTime = $booking->scheduled_at->format('M d, g:i A');
                if ($sendInAppStudent) {
                    Notification::send(
                        $booking->student,
                        'session_reminder',
                        'Upcoming Session',
                        "You have a driving session on {$sessionTime}. Booking #{$booking->id}.",
                        'session',
                        "/{$booking->school->slug}/student/schedule"
                    );
                }

                // Also notify the instructor if assigned
                if ($booking->instructor && $sendInAppInstructor) {
                    Notification::send(
                        $booking->instructor,
                        'session_reminder',
                        'Upcoming Session',
                        "You have a session with {$booking->student->name} on {$sessionTime}. Booking #{$booking->id}.",
                        'session',
                        "/{$booking->school->slug}/instructor/my-schedule"
                    );
                }

                $sentCount++;
                $this->line("  ✓ Sent reminder for booking #{$booking->id} ({$booking->student->name})");
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("  ✗ Failed for booking #{$booking->id}: {$e->getMessage()}");
                Log::warning("Session reminder failed for booking #{$booking->id}: " . $e->getMessage());
            }
        }

        $this->info("Done! Sent: {$sentCount}, Failed: {$failedCount}, Total found: {$bookings->count()}");

        return self::SUCCESS;
    }
}
