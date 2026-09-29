<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\EnrollmentRequest;
use App\Models\School;
use Illuminate\Mail\Mailables\Address;

class EnrollmentRequestReceived extends Mailable
{
    use SerializesModels;

    public $enrollment;
    public $school;

    /**
     * Create a new message instance.
     */
    public function __construct(EnrollmentRequest $enrollment, School $school)
    {
        $this->enrollment = $enrollment;
        $this->school = $school;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address("{$this->school->slug}@driveedhub.com", $this->school->name),
            subject: $this->school->name . ' - Enrollment Request Received',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.enrollment-request-received',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
