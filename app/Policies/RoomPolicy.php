<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir la chambre
     */
    public function view(User $user, Room $room): bool
    {
        return $user->hotel_id === $room->hotel_id;
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour la chambre
     */
    public function update(User $user, Room $room): bool
    {
        // Vérifier que la chambre appartient à l'hôtel de l'utilisateur
        if ($user->hotel_id !== $room->hotel_id) {
            return false;
        }
        
        // Vérifier que l'utilisateur a un des rôles autorisés
        // Utiliser hasRole pour chaque rôle pour être plus explicite
        return $user->hasRole('hotel-admin') || $user->hasRole('receptionist') || $user->hasRole('super-admin');
    }

    /**
     * Déterminer si l'utilisateur peut supprimer la chambre
     */
    public function delete(User $user, Room $room): bool
    {
        return $user->hotel_id === $room->hotel_id 
            && $user->hasRole('hotel-admin');
    }
}

