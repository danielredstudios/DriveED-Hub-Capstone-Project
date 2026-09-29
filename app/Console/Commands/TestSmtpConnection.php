<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Illuminate\Support\Facades\Log;

class TestSmtpConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test-connection {email? : The recipient email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the SMTP connection and send a test email if successful';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipient = $this->argument('email') ?? config('mail.from.address');
        $this->info("Testing SMTP connection to " . config('mail.mailers.smtp.host') . ":" . config('mail.mailers.smtp.port') . "...");
        $this->info("Encryption: " . (config('mail.mailers.smtp.encryption') ?? 'None'));
        $this->info("User: " . config('mail.mailers.smtp.username'));

        try {
            Mail::raw("This is a test email from the Driving School Management System to verify SMTP connectivity.", function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject("SMTP Connection Test");
            });

            $this->info("SUCCESS: Test email sent to {$recipient}!");
            return 0;
        }
        catch (\Exception $e) {
            $this->error("FAILED: Could not establish a connection with the mail server.");
            $this->error("Error Message: " . $e->getMessage());
            $this->line("");
            $this->info("Diagnostics:");

            if (str_contains($e->getMessage(), 'Failed to establish a connection')) {
                $this->warn("- This is often a networking/firewall issue.");
                $this->warn("- On Railway, check if IPv6 is being used. You might try forced IPv4 by using an IP address for MAIL_HOST.");
                $this->warn("- Current Host: " . config('mail.mailers.smtp.host'));
            }

            if (str_contains($e->getMessage(), 'STARTTLS')) {
                $this->warn("- STARTTLS issue detected. Ensure MAIL_ENCRYPTION=tls and port 587 are correctly set.");
            }

            Log::error("SMTP Test Failed: " . $e->getMessage(), [
                'exception' => $e,
                'config' => config('mail.mailers.smtp')
            ]);

            return 1;
        }
    }
}
