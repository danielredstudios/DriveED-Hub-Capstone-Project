<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the booking queue confirmation to run daily
Schedule::command('bookings:confirm-queued')->daily();

// Send session reminders for sessions happening in the next 24 hours (runs daily at 7 AM)
Schedule::command('reminders:sessions --hours=24')->dailyAt('07:00');

Artisan::command('mail:test {to}', function (string $to) {
    try {
        Mail::raw('DriveED SMTP test email. If you received this, mail setup works.', function ($message) use ($to) {
            $message->to($to)
                ->subject('DriveED Mail Test');
        });

        $this->info("Test email sent to {$to}.");
    } catch (\Throwable $e) {
        $this->error('Mail test failed: ' . $e->getMessage());
    }
})->purpose('Send a simple SMTP test email to verify mail configuration');
