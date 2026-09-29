<?php

namespace App\Mail;

use App\Models\School;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Mailables\Address;

class OtpVerificationCode extends Mailable
{
    use SerializesModels;

    public function __construct(
        public School $school,
        public Student $student,
        public string $otpCode,
        public bool $isResend = false,
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->isResend
            ? "{$this->school->name} - New Verification Code"
            : "{$this->school->name} - Email Verification Required";

        return new Envelope(
            from: new Address("{$this->school->slug}@driveedhub.com", $this->school->name),
            subject: $subject
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-verification-code',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
