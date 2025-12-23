<?php

namespace App\Exceptions;

/**
 * Exception levée lorsqu'une réservation est verrouillée et ne peut pas être modifiée
 */
class ReservationLockedException extends ReservationException
{
    /**
     * Créer une instance de l'exception
     */
    public static function modificationNotAllowed(string $status): self
    {
        return new self(
            "Cette réservation est dans un statut '{$status}' et ne peut plus être modifiée pour des raisons de sécurité et de traçabilité."
        );
    }
    
    /**
     * Créer une instance pour les données critiques verrouillées
     */
    public static function criticalDataLocked(): self
    {
        return new self(
            "Les données critiques de cette réservation sont verrouillées et ne peuvent plus être modifiées après le check-in."
        );
    }
}

