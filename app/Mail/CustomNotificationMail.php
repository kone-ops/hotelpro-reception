<?php

namespace App\Mail;

use App\Models\Hotel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email de notification client avec sujet et corps HTML personnalisables (templates super-admin).
 */
class CustomNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $bodyHtml,
        public Hotel $hotel,
        public ?string $fromName = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            from: [config('mail.from.address') => $this->fromName ?? config('mail.from.name')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
