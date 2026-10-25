<?php

namespace App\Mail;

use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LicenseExpirationReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Student|Instructor $user;
    public ?School $school;
    public ?int $daysRemaining;
    public bool $isExpired;
    public string $formattedExpiry;
    public ?string $licenseNumber;
    public string $userRole;

    /**
     * Create a new message instance.
     */
    public function __construct(Student|Instructor $user, ?School $school = null, ?int $daysRemaining = null)
    {
        $this->user = $user;
        $this->school = $school ?? $user->school;
        $this->daysRemaining = $daysRemaining ?? $user->getLicenseDaysRemaining();
        $this->isExpired = $this->daysRemaining !== null && $this->daysRemaining < 0;
        $this->formattedExpiry = $user->license_expiration_date ? $user->license_expiration_date->format('F d, Y') : 'N/A';
        $this->licenseNumber = $user->license_number;
        $this->userRole = $user instanceof Student ? 'Student / Driver' : 'Instructor';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $schoolName = $this->school->name ?? 'DriveED Hub';
        $subject = $this->isExpired
            ? "[URGENT] Driver's License Expired Notice - {$schoolName}"
            : "[Action Required] Driver's License Expiring Soon - {$schoolName}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.license-expiration-reminder',
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
