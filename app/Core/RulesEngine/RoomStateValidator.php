<?php

namespace App\Core\RulesEngine;

use App\Models\Room;
use App\Models\User;

class RoomStateValidator
{
    /**
     * Vérifie que la combinaison des 3 états est cohérente.
     */
    public function validateStateCombination(Room $room): bool
    {
        // Si occupée, le nettoyage ne peut pas être en cours
        if ($room->occupation_state === 'occupied'
            && in_array($room->cleaning_state ?? 'none', ['pending', 'in_progress'], true)) {
            return false;
        }

        // Si en maintenance ou hors service, l'occupation doit être free
        if (in_array($room->technical_state ?? 'normal', ['maintenance', 'out_of_service'], true)
            && ($room->occupation_state ?? 'free') !== 'free') {
            return false;
        }

        return true;
    }

    /**
     * Valide une transition d'état (optionnel, pour règles métier fines).
     * Retourne ['valid' => bool, 'message' => string].
     */
    public function validateTransition(Room $room, string $stateType, string $newValue, User $user): array
    {
        if ($stateType === 'cleaning') {
            $allowed = ['none', 'pending', 'in_progress', 'done'];
            if (!in_array($newValue, $allowed, true)) {
                return ['valid' => false, 'message' => 'Valeur d\'état de nettoyage invalide.'];
            }
            $current = $room->cleaning_state ?? 'none';
            if ($current === 'done' && $newValue === 'pending') {
                return ['valid' => true, 'message' => '']; // Réouverture d'une tâche possible
            }
        }

        if ($stateType === 'occupation') {
            $allowed = ['free', 'occupied', 'released'];
            if (!in_array($newValue, $allowed, true)) {
                return ['valid' => false, 'message' => 'Valeur d\'état d\'occupation invalide.'];
            }
        }

        if ($stateType === 'technical') {
            $allowed = ['normal', 'issue', 'maintenance', 'out_of_service'];
            if (!in_array($newValue, $allowed, true)) {
                return ['valid' => false, 'message' => 'Valeur d\'état technique invalide.'];
            }
        }

        return ['valid' => true, 'message' => ''];
    }
}
