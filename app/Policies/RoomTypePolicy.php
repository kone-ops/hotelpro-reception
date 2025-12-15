<?php

namespace App\Policies;

use App\Models\RoomType;
use App\Models\User;

class RoomTypePolicy
{
    /**
     * Déterminer si l'utilisateur peut voir le type de chambre
     */
    public function view(User $user, RoomType $roomType): bool
    {
        return $user->hotel_id === $roomType->hotel_id;
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour le type de chambre
     */
    public function update(User $user, RoomType $roomType): bool
    {
        return $user->hotel_id === $roomType->hotel_id 
            && $user->hasAnyRole(['hotel-admin', 'receptionist']);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer le type de chambre
     */
    public function delete(User $user, RoomType $roomType): bool
    {
        return $user->hotel_id === $roomType->hotel_id 
            && $user->hasRole('hotel-admin');
    }
}

