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
 * Service de gestion des pré-réservations
 * Centralise toute la logique métier
 */
class ReservationService
{
    /**
     * Créer une nouvelle pré-réservation avec documents
     */
    public function create(Hotel $hotel, array $data, array $documents = [], ?string $signature = null): Reservation
    {
        return DB::transaction(function () use ($hotel, $data, $documents, $signature) {
            // Créer la pré-réservation
            $Reservation = $hotel->Reservations()->create([
                'status' => 'pending',
                'data' => $data,
                'group_code' => $data['type_reservation'] === 'groupe' ? $data['code_groupe'] : null,
            ]);

            // Sauvegarder les documents d'identité
            if (!empty($documents)) {
                $this->saveIdentityDocuments($Reservation, $documents);
            }

            // Sauvegarder la signature
            if ($signature) {
                $this->saveSignature($Reservation, $signature);
            }

            Log::info('Pré-réservation créée', [
                'id' => $Reservation->id,
                'hotel_id' => $hotel->id,
                'type' => $data['type_reservation'] ?? 'Individuel',
            ]);

            return $Reservation;
        });
    }

    /**
     * Mettre à jour une pré-réservation
     */
    public function update(Reservation $Reservation, array $data): Reservation
    {
        DB::transaction(function () use ($Reservation, $data) {
            $Reservation->update([
                'data' => $data,
                'group_code' => $data['type_reservation'] === 'groupe' ? $data['code_groupe'] : null,
            ]);

            Log::info('Pré-réservation modifiée', [
                'id' => $Reservation->id,
                'modified_by' => auth()->id(),
            ]);
        });

        return $Reservation->fresh();
    }

    /**
     * Valider une pré-réservation
     */
    public function validate(Reservation $Reservation, int $validatedBy): bool
    {
        return DB::transaction(function () use ($Reservation, $validatedBy) {
            $Reservation->update([
                'status' => 'validated',
                'validated_at' => now(),
                'validated_by' => $validatedBy,
            ]);

            // Envoyer email de confirmation
            $this->sendValidationEmail($Reservation);

            Log::info('Pré-réservation validée', [
                'id' => $Reservation->id,
                'validated_by' => $validatedBy,
            ]);

            return true;
        });
    }

    /**
     * Rejeter une pré-réservation
     */
    public function reject(Reservation $Reservation, int $rejectedBy, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($Reservation, $rejectedBy, $reason) {
            $Reservation->update([
                'status' => 'rejected',
                'validated_by' => $rejectedBy,
            ]);

            // Envoyer email de rejet
            $this->sendRejectionEmail($Reservation, $reason);

            Log::info('Pré-réservation rejetée', [
                'id' => $Reservation->id,
                'rejected_by' => $rejectedBy,
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Remettre en attente
     */
    public function setPending(Reservation $Reservation): bool
    {
        $Reservation->update([
            'status' => 'pending',
            'validated_by' => null,
            'validated_at' => null,
        ]);

        Log::info('Pré-réservation remise en attente', [
            'id' => $Reservation->id,
        ]);

        return true;
    }

    /**
     * Obtenir les statistiques d'un hôtel
     */
    public function getHotelStats(Hotel $hotel): array
    {
        return [
            'total' => $hotel->Reservations()->count(),
            'pending' => $hotel->Reservations()->where('status', 'pending')->count(),
            'validated' => $hotel->Reservations()->where('status', 'validated')->count(),
            'rejected' => $hotel->Reservations()->where('status', 'rejected')->count(),
            'individual' => $hotel->Reservations()->individual()->count(),
            'group' => $hotel->Reservations()->group()->count(),
            'today' => $hotel->Reservations()->whereDate('created_at', today())->count(),
            'this_week' => $hotel->Reservations()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => $hotel->Reservations()->whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Recherche avancée
     */
    public function search(Hotel $hotel, array $filters)
    {
        $query = $hotel->Reservations();

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
    private function saveIdentityDocuments(Reservation $Reservation, array $documents): void
    {
        if (isset($documents['front'])) {
            $frontPath = $documents['front']->store('identity_documents', 'public');
        }

        if (isset($documents['back'])) {
            $backPath = $documents['back']->store('identity_documents', 'public');
        }

        $Reservation->identityDocument()->create([
            'type' => $documents['type'] ?? 'unknown',
            'front_path' => $frontPath ?? null,
            'back_path' => $backPath ?? null,
        ]);
    }

    /**
     * Sauvegarder la signature
     */
    private function saveSignature(Reservation $Reservation, string $signatureBase64): void
    {
        $Reservation->signature()->create([
            'image_base64' => $signatureBase64,
        ]);
    }

    /**
     * Envoyer email de validation
     */
    private function sendValidationEmail(Reservation $Reservation): void
    {
        try {
            $email = $Reservation->data['email'] ?? null;
            
            if ($email) {
                Mail::to($email)->send(new ReservationValidated($Reservation));
                
                Log::info('Email de validation envoyé', [
                    'reservation_id' => $Reservation->id,
                    'email' => $email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur envoi email validation', [
                'reservation_id' => $Reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envoyer email de rejet
     */
    private function sendRejectionEmail(Reservation $Reservation, ?string $reason): void
    {
        try {
            $email = $Reservation->data['email'] ?? null;
            
            if ($email) {
                Mail::to($email)->send(new ReservationRejected($Reservation, $reason));
                
                Log::info('Email de rejet envoyé', [
                    'reservation_id' => $Reservation->id,
                    'email' => $email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur envoi email rejet', [
                'reservation_id' => $Reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}





