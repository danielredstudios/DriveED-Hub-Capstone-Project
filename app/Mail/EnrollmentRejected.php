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

class EnrollmentRejected extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $enrollment;
    public $school;
    public $remarks;
    public $rejectLicense;
    public $rejectPayment;

    /**
     * Create a new message instance.
     */
    public function __construct(EnrollmentRequest $enrollment, School $school, $remarks = null, $rejectLicense = false, $rejectPayment = false)
    {
        $this->enrollment = $enrollment;
        $this->school = $school;
        $this->remarks = $remarks;
        $this->rejectLicense = (bool) $rejectLicense;
        $this->rejectPayment = (bool) $rejectPayment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address("{$this->school->slug}@driveedhub.com", $this->school->name),
            subject: $this->school->name . ' - Enrollment Request Update',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.enrollment-rejected',
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
