<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class ReservationStatusService
{
    /**
     * Workflow strict des statuts de réservation - TOUTES LES ACTIONS SONT IRRÉVERSIBLES
     */
    private const STATUS_WORKFLOW = [
        'pending' => ['validated', 'rejected'],
        'validated' => ['checked_in'], // Irréversible - aucune possibilité de retour en attente
        'rejected' => [], // Irréversible - aucune possibilité de réexaminer
        'checked_in' => ['checked_out'], // Irréversible après check-in
        'checked_out' => [], // Terminal - aucune transition possible
    ];

    /**
     * Statuts terminaux (aucune modification possible)
     * Tous les statuts sauf 'pending' sont maintenant terminaux
     */
    private const TERMINAL_STATUSES = ['validated', 'rejected', 'checked_in', 'checked_out'];

    /**
     * Statuts verrouillés (modifications limitées)
     * Tous les statuts sauf 'pending' sont maintenant verrouillés
     */
    private const LOCKED_STATUSES = ['validated', 'rejected', 'checked_in', 'checked_out'];

    /**
     * Vérifier si une transition de statut est autorisée
     */
    public function canTransition(string $currentStatus, string $newStatus): bool
    {
        if (!isset(self::STATUS_WORKFLOW[$currentStatus])) {
            return false;
        }

        return in_array($newStatus, self::STATUS_WORKFLOW[$currentStatus]);
    }

    /**
     * Vérifier si une réservation est dans un statut terminal
     */
    public function isTerminalStatus(string $status): bool
    {
        return in_array($status, self::TERMINAL_STATUSES);
    }

    /**
     * Vérifier si une réservation est verrouillée (modifications limitées)
     */
    public function isLockedStatus(string $status): bool
    {
        return in_array($status, self::LOCKED_STATUSES);
    }

    /**
     * Vérifier si une réservation peut être modifiée
     * Seules les réservations en attente peuvent être modifiées
     */
    public function canBeModified(Reservation $reservation): bool
    {
        // Seules les réservations en attente peuvent être modifiées
        // Toutes les autres actions (validation, rejet, check-in, check-out) sont irréversibles
        return $reservation->status === 'pending';
    }

    /**
     * Vérifier si les données critiques peuvent être modifiées
     */
    public function canModifyCriticalData(Reservation $reservation): bool
    {
        // Après check-in, les données critiques sont verrouillées
        return !$this->isLockedStatus($reservation->status);
    }

    /**
     * Obtenir les transitions possibles pour un statut donné
     */
    public function getPossibleTransitions(string $status): array
    {
        return self::STATUS_WORKFLOW[$status] ?? [];
    }

    /**
     * Valider une transition de statut
     */
    public function validateTransition(Reservation $reservation, string $newStatus): array
    {
        $currentStatus = $reservation->status;
        
        // Vérifier si la transition est autorisée
        if (!$this->canTransition($currentStatus, $newStatus)) {
            return [
                'allowed' => false,
                'message' => "La transition de '{$currentStatus}' vers '{$newStatus}' n'est pas autorisée."
            ];
        }

        // Vérifications spécifiques selon le statut cible
        if ($newStatus === 'checked_in') {
            // Vérifier qu'une chambre est assignée
            if (!$reservation->room_id) {
                return [
                    'allowed' => false,
                    'message' => 'Une chambre doit être assignée avant de pouvoir effectuer le check-in.'
                ];
            }

            // Vérifier que la date de check-in est valide
            if ($reservation->check_in_date) {
                $checkInDate = \Carbon\Carbon::parse($reservation->check_in_date);
                $today = now()->startOfDay();
                
                if ($checkInDate->lt($today)) {
                    return [
                        'allowed' => false,
                        'message' => 'Impossible de faire le check-in : la date d\'arrivée est antérieure à aujourd\'hui.'
                    ];
                }
            }
        }

        if ($newStatus === 'checked_out') {
            // Vérifier que le client est en check-in
            if ($reservation->status !== 'checked_in') {
                return [
                    'allowed' => false,
                    'message' => 'Le client doit être en check-in pour effectuer le check-out.'
                ];
            }
        }

        return [
            'allowed' => true,
            'message' => 'Transition autorisée.'
        ];
    }

    /**
     * Obtenir le message d'erreur pour une transition non autorisée
     */
    public function getTransitionErrorMessage(string $currentStatus, string $newStatus): string
    {
        $possibleTransitions = $this->getPossibleTransitions($currentStatus);
        
        if (empty($possibleTransitions)) {
            return "La réservation est dans un statut terminal ({$currentStatus}) et ne peut plus être modifiée.";
        }

        $transitionsList = implode(', ', $possibleTransitions);
        return "Transition non autorisée. Depuis '{$currentStatus}', seules les transitions suivantes sont possibles : {$transitionsList}";
    }
}

