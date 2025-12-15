<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Models\ActivityLog;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Cache;

/**
 * Observer pour les réservations
 * Gère les événements et le cache automatiquement
 */
class ReservationObserver
{
    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        // Construire le nom du client
        $clientName = $reservation->data['nom'] ?? '';
        if (isset($reservation->data['prenom'])) {
            $clientName = $reservation->data['prenom'] . ' ' . $clientName;
        }
        
        // Logger l'activité
        ActivityLog::log(
            'Nouvelle réservation créée',
            $reservation,
            [
                'type' => $reservation->data['type_reservation'] ?? 'Individuel',
                'client' => $clientName,
            ],
            'reservation',
            'created'
        );

        // Invalider le cache
        $this->clearHotelCache($reservation);
        
        // Créer une notification pour les utilisateurs de l'hôtel
        try {
            app(NotificationService::class)->notifyNewReservation($reservation);
        } catch (\Exception $e) {
            \Log::error('Erreur création notification nouvelle réservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        // Logger l'activité
        ActivityLog::log(
            'Réservation modifiée',
            $reservation,
            [
                'status' => $reservation->status,
                'changes' => $reservation->getChanges(),
            ],
            'reservation',
            'updated'
        );

        // Invalider le cache
        $this->clearHotelCache($reservation);
        
        // Détecter les changements de statut pour les notifications
        $changes = $reservation->getChanges();
        if (isset($changes['status'])) {
            $newStatus = $changes['status'];
            $oldStatus = $reservation->getOriginal('status');
            
            try {
                $notificationService = app(NotificationService::class);
                
                if ($newStatus === 'checked_in' && $oldStatus !== 'checked_in') {
                    $notificationService->notifyCheckIn($reservation);
                } elseif ($newStatus === 'checked_out' && $oldStatus !== 'checked_out') {
                    $notificationService->notifyCheckOut($reservation);
                } elseif ($newStatus === 'validated' && $oldStatus !== 'validated') {
                    $notificationService->notifyReservationValidated($reservation);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur notification changement statut', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Reservation "deleted" event.
     */
    public function deleted(Reservation $reservation): void
    {
        // Construire le nom du client
        $clientName = $reservation->data['nom'] ?? '';
        if (isset($reservation->data['prenom'])) {
            $clientName = $reservation->data['prenom'] . ' ' . $clientName;
        }
        
        // Logger l'activité
        ActivityLog::log(
            'Réservation supprimée',
            $reservation,
            [
                'client' => $clientName,
            ],
            'reservation',
            'deleted'
        );

        // Invalider le cache
        $this->clearHotelCache($reservation);
    }

    /**
     * Invalider le cache de l'hôtel
     */
    private function clearHotelCache(Reservation $reservation): void
    {
        if ($reservation->hotel) {
            $cacheService = app(\App\Services\CacheService::class);
            $cacheService->clearHotelCache($reservation->hotel);
        }
    }
}

