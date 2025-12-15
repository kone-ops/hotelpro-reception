<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Créer une notification pour un utilisateur
     */
    public function create(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $icon = 'info',
        ?string $color = null,
        $notifiable = null,
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $actionText = null
    ): UserNotification {
        return UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color ?? $this->getDefaultColor($icon),
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable ? $notifiable->id : null,
            'data' => $data,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
        ]);
    }

    /**
     * Notifier tous les utilisateurs d'un hôtel
     */
    public function notifyHotelUsers(
        int $hotelId,
        string $type,
        string $title,
        string $message,
        ?string $icon = 'info',
        ?string $color = null,
        $notifiable = null,
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $actionText = null
    ): void {
        $users = User::where('hotel_id', $hotelId)->get();
        
        foreach ($users as $user) {
            $this->create(
                $user,
                $type,
                $title,
                $message,
                $icon,
                $color,
                $notifiable,
                $data,
                $actionUrl,
                $actionText
            );
        }
    }

    /**
     * Notifier les utilisateurs selon leur rôle
     */
    public function notifyByRole(
        string $role,
        string $type,
        string $title,
        string $message,
        ?string $icon = 'info',
        ?string $color = null,
        $notifiable = null,
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $actionText = null
    ): void {
        $users = User::role($role)->get();
        
        foreach ($users as $user) {
            $this->create(
                $user,
                $type,
                $title,
                $message,
                $icon,
                $color,
                $notifiable,
                $data,
                $actionUrl,
                $actionText
            );
        }
    }

    /**
     * Notification pour nouvelle réservation
     */
    public function notifyNewReservation(Reservation $reservation): void
    {
        $hotel = $reservation->hotel;
        $clientName = $reservation->client_full_name ?? 'Client';
        
        $this->notifyHotelUsers(
            $hotel->id,
            'new_reservation',
            'Nouvelle Réservation',
            "Nouvelle réservation de {$clientName}",
            'success',
            null,
            $reservation,
            [
                'reservation_id' => $reservation->id,
                'client_name' => $clientName,
                'check_in' => $reservation->check_in_date?->format('d/m/Y'),
                'check_out' => $reservation->check_out_date?->format('d/m/Y'),
            ],
            route('hotel.reservations.show', $reservation),
            'Voir la réservation'
        );
    }

    /**
     * Notification pour check-in
     */
    public function notifyCheckIn(Reservation $reservation): void
    {
        $hotel = $reservation->hotel;
        $clientName = $reservation->client_full_name ?? 'Client';
        
        $this->notifyHotelUsers(
            $hotel->id,
            'check_in',
            'Arrivée Client',
            "{$clientName} vient d'arriver",
            'info',
            null,
            $reservation,
            [
                'reservation_id' => $reservation->id,
                'client_name' => $clientName,
                'room' => $reservation->room?->number,
            ],
            route('hotel.reservations.show', $reservation),
            'Voir la réservation'
        );
    }

    /**
     * Notification pour check-out
     */
    public function notifyCheckOut(Reservation $reservation): void
    {
        $hotel = $reservation->hotel;
        $clientName = $reservation->client_full_name ?? 'Client';
        
        $this->notifyHotelUsers(
            $hotel->id,
            'check_out',
            'Départ Client',
            "{$clientName} vient de partir",
            'warning',
            null,
            $reservation,
            [
                'reservation_id' => $reservation->id,
                'client_name' => $clientName,
                'room' => $reservation->room?->number,
            ],
            route('hotel.reservations.show', $reservation),
            'Voir la réservation'
        );
    }

    /**
     * Notification pour réservation validée
     */
    public function notifyReservationValidated(Reservation $reservation): void
    {
        $hotel = $reservation->hotel;
        $clientName = $reservation->client_full_name ?? 'Client';
        
        // Si pas de chambre assignée, envoyer une notification d'alerte
        if (!$reservation->room_id) {
            $this->notifyHotelUsers(
                $hotel->id,
                'reservation_validated_no_room',
                '⚠️ Réservation Validée - Chambre Requise',
                "La réservation de {$clientName} a été validée mais aucune chambre n'est assignée. Veuillez assigner une chambre pour permettre le check-in.",
                'warning',
                null,
                $reservation,
                [
                    'reservation_id' => $reservation->id,
                    'client_name' => $clientName,
                    'requires_room' => true,
                ],
                route('reception.reservations.edit', $reservation),
                'Assigner une chambre'
            );
        } else {
            $this->notifyHotelUsers(
                $hotel->id,
                'reservation_validated',
                'Réservation Validée',
                "La réservation de {$clientName} a été validée",
                'success',
                null,
                $reservation,
                [
                    'reservation_id' => $reservation->id,
                    'client_name' => $clientName,
                ],
                route('reception.reservations.show', $reservation),
                'Voir la réservation'
            );
        }
    }
    
    /**
     * Notification pour tentative de check-in sans chambre
     */
    public function notifyCheckInNoRoom(Reservation $reservation): void
    {
        $hotel = $reservation->hotel;
        $clientName = $reservation->client_full_name ?? 'Client';
        
        $this->notifyHotelUsers(
            $hotel->id,
            'check_in_no_room',
            '⚠️ Check-in Impossible',
            "Impossible d'effectuer le check-in de {$clientName} : aucune chambre n'est assignée à cette réservation.",
            'danger',
            null,
            $reservation,
            [
                'reservation_id' => $reservation->id,
                'client_name' => $clientName,
                'requires_room' => true,
            ],
            route('reception.reservations.edit', $reservation),
            'Assigner une chambre'
        );
    }

    /**
     * Obtenir la couleur par défaut selon l'icône
     */
    private function getDefaultColor(string $icon): string
    {
        return match($icon) {
            'success' => '#198754',
            'error', 'danger' => '#dc3545',
            'warning' => '#ffc107',
            'info' => '#0dcaf0',
            default => '#6c757d',
        };
    }

    /**
     * Marquer toutes les notifications comme lues pour un utilisateur
     */
    public function markAllAsRead(User $user): int
    {
        return UserNotification::where('user_id', $user->id)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Supprimer les anciennes notifications (plus de 30 jours)
     */
    public function cleanOldNotifications(int $days = 30): int
    {
        return UserNotification::where('created_at', '<', now()->subDays($days))
            ->where('read', true)
            ->delete();
    }
}

