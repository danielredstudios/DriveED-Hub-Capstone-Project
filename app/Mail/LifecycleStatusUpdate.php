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

class LifecycleStatusUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public School $school,
        public string $recipientName,
        public string $title,
        public string $message,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public ?string $emailSubject = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address("{$this->school->slug}@driveedhub.com", $this->school->name),
            subject: $this->emailSubject ?: "{$this->school->name} - {$this->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lifecycle-status-update',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
