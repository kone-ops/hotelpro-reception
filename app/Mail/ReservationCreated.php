<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ReservationCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Reservation $reservation
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre réservation dans notre établissement - HOTELPRO',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-created',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        // Charger les relations nécessaires
        $this->reservation->load(['hotel', 'room', 'roomType']);
        
        // Générer le PDF
        $pdf = Pdf::loadView('emails.reservation-confirmation-pdf', [
            'reservation' => $this->reservation
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);
        
        // Nom de fichier lisible : reservation-{nom-client}-{yyyy-mm-dd}.pdf
        $clientName = $this->reservation->data['nom'] ?? $this->reservation->full_name ?? $this->reservation->nom ?? 'client';
        $slug = Str::slug($clientName);
        $date = now()->format('Y-m-d');
        $filename = "reservation-{$slug}-{$date}.pdf";
        
        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}

