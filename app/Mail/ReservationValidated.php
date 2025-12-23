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

class ReservationValidated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre réservation dans notre établissement - HOTELPRO',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-validated',
        );
    }

    public function attachments(): array
    {
        // Charger les relations nécessaires
        $this->reservation->load(['hotel', 'room', 'roomType']);

        // Générer le PDF de confirmation
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
