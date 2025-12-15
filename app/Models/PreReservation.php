<?php

namespace App\Models;

/**
 * Alias de compatibilité pour Reservation
 * 
 * Cette classe permet de garder la compatibilité avec l'ancien code
 * qui utilisait PreReservation au lieu de Reservation.
 * 
 * Elle pointe vers la même table : reservations
 */
class PreReservation extends Reservation
{
    // Hérite de tout de Reservation
    // Pointe vers la même table 'reservations'
}

