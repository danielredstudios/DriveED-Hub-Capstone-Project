<?php

namespace App\Mail;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Mailables\Address;

class PasswordResetRequested extends Mailable
{
    use SerializesModels;

    public function __construct(
        public School $school,
        public string $recipientName,
        public string $resetUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address("{$this->school->slug}@driveedhub.com", $this->school->name),
            subject: "{$this->school->name} - Password Reset Request",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-requested',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
