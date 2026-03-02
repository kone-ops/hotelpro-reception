<?php

namespace App\Services;

use App\Mail\CustomNotificationMail;
use App\Mail\ReservationCreated;
use App\Mail\ReservationRejected;
use App\Mail\ReservationValidated;
use App\Models\Hotel;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envoi des notifications client (email, SMS, WhatsApp) selon la config par hôtel.
 * Les templates et canaux sont configurables par le super-admin.
 * SMS/WhatsApp : emplacements prêts pour insertion API (clés configurées dans l'interface).
 */
class ClientNotificationService
{
    /**
     * Placeholders disponibles pour les templates (email, SMS, WhatsApp).
     */
    public static function placeholderList(): array
    {
        return [
            'NOM_HOTEL' => 'Nom de l\'hôtel',
            'LOGO_HOTEL' => 'URL du logo de l\'hôtel',
            'ADRESSE_HOTEL' => 'Adresse de l\'hôtel',
            'EMAIL_HOTEL' => 'Email de l\'hôtel',
            'TELEPHONE_HOTEL' => 'Téléphone de l\'hôtel',
            'VILLE_HOTEL' => 'Ville',
            'PAYS_HOTEL' => 'Pays',
            'NOM_CLIENT' => 'Nom du client',
            'PRENOM_CLIENT' => 'Prénom du client',
            'NOM_COMPLET_CLIENT' => 'Nom complet du client',
            'EMAIL_CLIENT' => 'Email du client',
            'TELEPHONE_CLIENT' => 'Téléphone du client',
            'NUMERO_RESERVATION' => 'Numéro de réservation (formaté)',
            'TYPE_CHAMBRE' => 'Type de chambre',
            'NUMERO_CHAMBRE' => 'Numéro de chambre',
            'DATE_ARRIVEE' => 'Date d\'arrivée',
            'DATE_DEPART' => 'Date de départ',
            'NOMBRE_ADULTES' => 'Nombre d\'adultes',
            'NOMBRE_ENFANTS' => 'Nombre d\'enfants',
            'RAISON_REJET' => 'Raison du rejet (pour email rejet)',
        ];
    }

    /**
     * Construire les placeholders à partir d'une réservation.
     */
    public function buildPlaceholders(Reservation $reservation, string $reason = ''): array
    {
        $reservation->loadMissing(['hotel', 'room', 'roomType']);
        $hotel = $reservation->hotel;
        $data = $reservation->data ?? [];

        $nom = $data['nom'] ?? '';
        $prenom = $data['prenom'] ?? '';
        $nomComplet = trim($prenom . ' ' . $nom) ?: 'Client';

        $placeholders = [
            'NOM_HOTEL' => $hotel->name ?? '',
            'LOGO_HOTEL' => $hotel->logo_url ?? '',
            'ADRESSE_HOTEL' => $hotel->address ?? '',
            'EMAIL_HOTEL' => $hotel->email ?? '',
            'TELEPHONE_HOTEL' => $hotel->phone ?? '',
            'VILLE_HOTEL' => $hotel->city ?? '',
            'PAYS_HOTEL' => $hotel->country ?? '',
            'NOM_CLIENT' => $nom,
            'PRENOM_CLIENT' => $prenom,
            'NOM_COMPLET_CLIENT' => $nomComplet,
            'EMAIL_CLIENT' => $data['email'] ?? '',
            'TELEPHONE_CLIENT' => $data['telephone'] ?? '',
            'NUMERO_RESERVATION' => str_pad((string) $reservation->id, 7, '0', STR_PAD_LEFT),
            'TYPE_CHAMBRE' => $reservation->roomType?->name ?? '',
            'NUMERO_CHAMBRE' => $reservation->room?->room_number ?? '',
            'DATE_ARRIVEE' => $reservation->check_in_date ? $reservation->check_in_date->format('d/m/Y') : '',
            'DATE_DEPART' => $reservation->check_out_date ? $reservation->check_out_date->format('d/m/Y') : '',
            'NOMBRE_ADULTES' => (string) ($data['nombre_adultes'] ?? $data['adultes'] ?? ''),
            'NOMBRE_ENFANTS' => (string) ($data['nombre_enfants'] ?? $data['enfants'] ?? ''),
            'RAISON_REJET' => $reason,
        ];

        // Champs personnalisés du formulaire (clés en majuscules avec préfixe)
        foreach ($data as $key => $value) {
            if (!isset($placeholders[$key])) {
                $placeholders['CHAMP_' . strtoupper($key)] = is_scalar($value) ? (string) $value : json_encode($value);
            }
        }

        return $placeholders;
    }

    /**
     * Remplacer les placeholders dans un texte.
     */
    public function replacePlaceholders(string $template, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }
        return $template;
    }

    /**
     * Envoyer les notifications pour une réservation créée (enregistrée).
     */
    public function sendReservationCreated(Reservation $reservation): void
    {
        $this->sendForEvent($reservation, 'created');
    }

    /**
     * Envoyer les notifications pour une réservation validée.
     */
    public function sendReservationValidated(Reservation $reservation): void
    {
        $this->sendForEvent($reservation, 'validated');
    }

    /**
     * Envoyer les notifications pour une réservation rejetée.
     */
    public function sendReservationRejected(Reservation $reservation, string $reason = ''): void
    {
        $this->sendForEvent($reservation, 'rejected', $reason);
    }

    /**
     * Envoyer au client un message de bienvenue (lors du check-in).
     */
    public function sendCheckInWelcome(Reservation $reservation): void
    {
        $this->sendForEvent($reservation, 'check_in');
    }

    /**
     * Envoyer au client un message d'au revoir (lors du check-out).
     */
    public function sendCheckOutGoodbye(Reservation $reservation): void
    {
        $this->sendForEvent($reservation, 'check_out');
    }

    /**
     * Envoi multi-canal selon la config de l'hôtel.
     */
    protected function sendForEvent(Reservation $reservation, string $event, string $reason = ''): void
    {
        $reservation->loadMissing(['hotel', 'room', 'roomType', 'identityDocument']);
        $hotel = $reservation->hotel;
        $config = $hotel->getNotificationConfig();
        $placeholders = $this->buildPlaceholders($reservation, $reason);

        $emailTo = $reservation->client_email;
        $phone = $reservation->client_phone;

        // --- Email
        if (!empty($config['email']['enabled']) && $emailTo) {
            $this->sendEmail($reservation, $event, $config['email'], $placeholders, $emailTo, $reason);
        }

        // --- SMS (emplacement API : config sms.api_key, sms.sender, sms.templates[*])
        if (!empty($config['sms']['enabled']) && $phone) {
            $this->sendSms($reservation, $event, $config['sms'], $placeholders, $phone);
        }

        // --- WhatsApp (emplacement API : config whatsapp.*)
        if (!empty($config['whatsapp']['enabled']) && $phone) {
            $this->sendWhatsApp($reservation, $event, $config['whatsapp'], $placeholders, $phone);
        }
    }

    protected function sendEmail(Reservation $reservation, string $event, array $emailConfig, array $placeholders, string $to, string $reason): void
    {
        $templates = $emailConfig['templates'][$event] ?? [];
        $useSystemDefault = !empty($templates['use_system_default']);
        $customSubject = trim($templates['subject'] ?? '');
        $customBody = trim($templates['body_html'] ?? '');

        try {
            if ($useSystemDefault || ($customSubject === '' && $customBody === '')) {
                // Comportement actuel : Mailable dédiés ou messages par défaut
                if ($event === 'created') {
                    Mail::to($to)->send(new ReservationCreated($reservation));
                } elseif ($event === 'validated') {
                    Mail::to($to)->send(new ReservationValidated($reservation));
                } elseif ($event === 'rejected') {
                    Mail::to($to)->send(new ReservationRejected($reservation, $reason));
                } elseif ($event === 'check_in') {
                    $subject = 'Bienvenue – ' . ($reservation->hotel->name ?? 'Notre établissement');
                    $body = '<p>Bonjour {{ NOM_COMPLET_CLIENT }},</p><p>Nous sommes ravis de vous accueillir à {{ NOM_HOTEL }}. Nous vous souhaitons un agréable séjour.</p><p>À très bientôt,<br>L\'équipe {{ NOM_HOTEL }}</p>';
                    $body = $this->replacePlaceholders($body, $placeholders);
                    $fromName = $emailConfig['from_name'] ?? $reservation->hotel->name;
                    Mail::to($to)->send(new CustomNotificationMail($subject, $body, $reservation->hotel, $fromName));
                } elseif ($event === 'check_out') {
                    $subject = 'Merci pour votre séjour – ' . ($reservation->hotel->name ?? 'Notre établissement');
                    $body = '<p>Bonjour {{ NOM_COMPLET_CLIENT }},</p><p>Merci d\'avoir séjourné à {{ NOM_HOTEL }}. Nous espérons vous revoir très bientôt.</p><p>À bientôt,<br>L\'équipe {{ NOM_HOTEL }}</p>';
                    $body = $this->replacePlaceholders($body, $placeholders);
                    $fromName = $emailConfig['from_name'] ?? $reservation->hotel->name;
                    Mail::to($to)->send(new CustomNotificationMail($subject, $body, $reservation->hotel, $fromName));
                }
            } else {
                $subject = $customSubject !== '' ? $this->replacePlaceholders($customSubject, $placeholders) : 'Notification - ' . $reservation->hotel->name;
                $bodyHtml = $this->replacePlaceholders($customBody, $placeholders);
                $fromName = $emailConfig['from_name'] ?? $reservation->hotel->name;
                Mail::to($to)->send(new CustomNotificationMail($subject, $bodyHtml, $reservation->hotel, $fromName));
            }
        } catch (\Throwable $e) {
            Log::error('ClientNotificationService: erreur envoi email', [
                'reservation_id' => $reservation->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * SMS : emplacement pour API (Twilio, etc.). Tant que non branchée, log uniquement.
     */
    protected function sendSms(Reservation $reservation, string $event, array $smsConfig, array $placeholders, string $to): void
    {
        $template = $smsConfig['templates'][$event] ?? '';
        if ($template === '') {
            return;
        }
        $body = $this->replacePlaceholders($template, $placeholders);
        $apiKey = $smsConfig['api_key'] ?? '';
        $sender = $smsConfig['sender'] ?? '';

        if ($apiKey !== '') {
            // TODO: brancher l'API SMS ici (ex: Twilio, Orange, etc.)
            // Exemple: SmsApi::send($apiKey, $sender, $to, $body);
            Log::info('ClientNotificationService: SMS (API à brancher)', [
                'reservation_id' => $reservation->id,
                'event' => $event,
                'to' => $to,
                'body_length' => strlen($body),
            ]);
        } else {
            Log::info('ClientNotificationService: SMS désactivé (aucune clé API)', [
                'reservation_id' => $reservation->id,
                'event' => $event,
            ]);
        }
    }

    /**
     * WhatsApp : emplacement pour API (WhatsApp Business, etc.). Tant que non branchée, log uniquement.
     */
    protected function sendWhatsApp(Reservation $reservation, string $event, array $waConfig, array $placeholders, string $to): void
    {
        $template = $waConfig['templates'][$event] ?? '';
        if ($template === '') {
            return;
        }
        $body = $this->replacePlaceholders($template, $placeholders);
        $apiKey = $waConfig['api_key'] ?? '';
        $phoneNumberId = $waConfig['phone_number_id'] ?? '';
        $senderName = $waConfig['sender_name'] ?? $reservation->hotel->name;

        if ($apiKey !== '' && $phoneNumberId !== '') {
            // TODO: brancher l'API WhatsApp Business ici
            // Exemple: WhatsappApi::send($apiKey, $phoneNumberId, $to, $body, $senderName, $reservation->hotel->logo_url);
            Log::info('ClientNotificationService: WhatsApp (API à brancher)', [
                'reservation_id' => $reservation->id,
                'event' => $event,
                'to' => $to,
                'body_length' => strlen($body),
            ]);
        } else {
            Log::info('ClientNotificationService: WhatsApp désactivé (aucune clé API)', [
                'reservation_id' => $reservation->id,
                'event' => $event,
            ]);
        }
    }
}
