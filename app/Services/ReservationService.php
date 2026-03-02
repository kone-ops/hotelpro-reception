<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Hotel;
use App\Models\IdentityDocument;
use App\Models\Signature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationValidated;
use App\Mail\ReservationRejected;

/**
 * Service de gestion des réservations
 * Centralise toute la logique métier
 */
class ReservationService
{
    /**
     * Créer une nouvelle réservation avec documents
     */
    public function create(Hotel $hotel, array $data, array $documents = [], ?string $signature = null): Reservation
    {
        return DB::transaction(function () use ($hotel, $data, $documents, $signature) {
            // Créer la réservation
            $reservation = $hotel->reservations()->create([
                'status' => 'pending',
                'data' => $data,
                'group_code' => $data['type_reservation'] === 'groupe' ? $data['code_groupe'] : null,
            ]);

            // Sauvegarder les documents d'identité
            if (!empty($documents)) {
                $this->saveIdentityDocuments($reservation, $documents);
            }

            // Sauvegarder la signature
            if ($signature) {
                $this->saveSignature($reservation, $signature);
            }

            Log::info('Réservation créée', [
                'id' => $reservation->id,
                'hotel_id' => $hotel->id,
                'type' => $data['type_reservation'] ?? 'Individuel',
            ]);

            return $reservation;
        });
    }

    /**
     * Mettre à jour une réservation
     */
    public function update(Reservation $reservation, array $data): Reservation
    {
        DB::transaction(function () use ($reservation, $data) {
            $reservation->update([
                'data' => $data,
                'group_code' => $data['type_reservation'] === 'groupe' ? $data['code_groupe'] : null,
            ]);

            Log::info('Réservation modifiée', [
                'id' => $reservation->id,
                'modified_by' => auth()->id(),
            ]);
        });

        return $reservation->fresh();
    }

    /**
     * Valider une réservation
     */
    public function validate(Reservation $reservation, int $validatedBy): bool
    {
        return DB::transaction(function () use ($reservation, $validatedBy) {
            $reservation->update([
                'status' => 'validated',
                'validated_at' => now(),
                'validated_by' => $validatedBy,
            ]);

            // Envoyer email de confirmation
            $this->sendValidationEmail($reservation);

            Log::info('Réservation validée', [
                'id' => $reservation->id,
                'validated_by' => $validatedBy,
            ]);

            return true;
        });
    }

    /**
     * Rejeter une réservation
     */
    public function reject(Reservation $reservation, int $rejectedBy, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($reservation, $rejectedBy, $reason) {
            $reservation->update([
                'status' => 'rejected',
                'validated_by' => $rejectedBy,
            ]);

            // Envoyer email de rejet
            $this->sendRejectionEmail($reservation, $reason);

            Log::info('Réservation rejetée', [
                'id' => $reservation->id,
                'rejected_by' => $rejectedBy,
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Remettre en attente
     */
    public function setPending(Reservation $reservation): bool
    {
        $reservation->update([
            'status' => 'pending',
            'validated_by' => null,
            'validated_at' => null,
        ]);

        Log::info('Réservation remise en attente', [
            'id' => $reservation->id,
        ]);

        return true;
    }

    /**
     * Obtenir les statistiques d'un hôtel
     */
    public function getHotelStats(Hotel $hotel): array
    {
        return [
            'total' => $hotel->reservations()->count(),
            'pending' => $hotel->reservations()->where('status', 'pending')->count(),
            'validated' => $hotel->reservations()->where('status', 'validated')->count(),
            'rejected' => $hotel->reservations()->where('status', 'rejected')->count(),
            'individual' => $hotel->reservations()->individual()->count(),
            'group' => $hotel->reservations()->group()->count(),
            'today' => $hotel->reservations()->whereDate('created_at', today())->count(),
            'this_week' => $hotel->reservations()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => $hotel->reservations()->whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Recherche avancée
     */
    public function search(Hotel $hotel, array $filters)
    {
        $query = $hotel->reservations();

        // Filtre par statut
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filtre par type
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'groupe') {
                $query->group();
            } elseif ($filters['type'] === 'individuel') {
                $query->individual();
            }
        }

        // Filtre par date
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Recherche par nom/email/téléphone
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(data, '$.nom') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(data, '$.prenom') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(data, '$.email') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(data, '$.telephone') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(data, '$.nom_groupe') LIKE ?", ["%{$search}%"]);
            });
        }

        return $query->latest()->paginate(20);
    }

    /**
     * Sauvegarder les documents d'identité
     */
    private function saveIdentityDocuments(Reservation $reservation, array $documents): void
    {
        if (isset($documents['front'])) {
            $frontPath = $documents['front']->store('identity_documents', 'public');
        }

        if (isset($documents['back'])) {
            $backPath = $documents['back']->store('identity_documents', 'public');
        }

        $reservation->identityDocument()->create([
            'type' => $documents['type'] ?? 'unknown',
            'front_path' => $frontPath ?? null,
            'back_path' => $backPath ?? null,
        ]);
    }

    /**
     * Sauvegarder la signature
     */
    private function saveSignature(Reservation $reservation, string $signatureBase64): void
    {
        $reservation->signature()->create([
            'image_base64' => $signatureBase64,
        ]);
    }

    /**
     * Envoyer email de validation
     */
    private function sendValidationEmail(Reservation $reservation): void
    {
        try {
            $email = $reservation->data['email'] ?? null;

            if ($email) {
                Mail::to($email)->send(new ReservationValidated($reservation));

                Log::info('Email de validation envoyé', [
                    'reservation_id' => $reservation->id,
                    'email' => $email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur envoi email validation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envoyer email de rejet
     */
    private function sendRejectionEmail(Reservation $reservation, ?string $reason): void
    {
        try {
            $email = $reservation->data['email'] ?? null;

            if ($email) {
                Mail::to($email)->send(new ReservationRejected($reservation, $reason));

                Log::info('Email de rejet envoyé', [
                    'reservation_id' => $reservation->id,
                    'email' => $email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur envoi email rejet', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

