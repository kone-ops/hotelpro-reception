<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public string $reason = ''
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Votre réservation a été rejetée - ' . $this->reservation->hotel->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
