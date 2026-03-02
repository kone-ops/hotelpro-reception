<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'room_number',
        'floor',
        'status',
        'occupation_state',
        'cleaning_state',
        'technical_state',
        'notes',
        'out_of_service_reason',
        'out_of_service_from',
        'out_of_service_until',
        'out_of_service_by',
    ];

    protected $casts = [
        'status' => 'string',
        'occupation_state' => 'string',
        'cleaning_state' => 'string',
        'technical_state' => 'string',
        'out_of_service_from' => 'date',
        'out_of_service_until' => 'date',
    ];

    /**
     * Relation avec l'hôtel
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Relation avec le type de chambre
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour les chambres disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope pour les chambres occupées
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * État global dérivé des 3 états (priorité : technique > occupation > nettoyage).
     */
    public function getGlobalStatus(): string
    {
        $technical = $this->technical_state ?? 'normal';
        $occupation = $this->occupation_state ?? 'free';
        $cleaning = $this->cleaning_state ?? 'none';

        if ($technical !== 'normal') {
            return $technical; // issue, maintenance, out_of_service
        }
        if ($occupation === 'occupied') {
            return 'occupied';
        }
        if (in_array($cleaning, ['pending', 'in_progress'], true)) {
            return 'cleaning';
        }

        return 'available';
    }

    /**
     * Met à jour la colonne status pour refléter l'état global (compatibilité existant).
     */
    public function syncStatusFromStates(): void
    {
        $this->status = $this->getGlobalStatus();
        $this->saveQuietly();
    }

    /**
     * Vérifier si la chambre est disponible pour une nouvelle réservation.
     */
    public function isAvailableForReservation(): bool
    {
        return $this->getGlobalStatus() === 'available';
    }

    /**
     * Vérifier si la chambre est disponible (alias pour compatibilité).
     */
    public function isAvailable(): bool
    {
        return $this->getGlobalStatus() === 'available';
    }

    /**
     * Changer le statut de la chambre
     */
    public function updateStatus(string $status): bool
    {
        if (!in_array($status, ['available', 'occupied', 'maintenance', 'reserved', 'cleaning'], true)) {
            return false;
        }
        
        $this->status = $status;
        return $this->save();
    }

    /**
     * Vérifier si la chambre est disponible pour une période donnée
     */
    public function isAvailableForPeriod($checkIn, $checkOut): bool
    {
        if (!$this->isAvailableForReservation()) {
            return false;
        }

        // Vérifier s'il n'y a pas de réservation qui chevauche cette période
        $hasConflict = Reservation::where('room_id', $this->id)
            ->whereIn('status', ['pending', 'validated'])
            ->where(function($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                      ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                      ->orWhere(function($q) use ($checkIn, $checkOut) {
                          $q->where('check_in_date', '<=', $checkIn)
                            ->where('check_out_date', '>=', $checkOut);
                      });
            })
            ->exists();

        return !$hasConflict;
    }

    /**
     * Obtenir les chambres disponibles pour un type et une période
     */
    public static function getAvailableRooms($hotelId, $roomTypeId, $checkIn, $checkOut)
    {
        return static::where('hotel_id', $hotelId)
            ->where('room_type_id', $roomTypeId)
            ->get()
            ->filter(function ($room) use ($checkIn, $checkOut) {
                return $room->isAvailableForReservation() && $room->isAvailableForPeriod($checkIn, $checkOut);
            });
    }

    /**
     * Relation : historique des changements d'état.
     */
    public function stateHistory()
    {
        return $this->hasMany(RoomStateHistory::class);
    }

    /**
     * Relation : tâches de nettoyage.
     */
    public function housekeepingTasks()
    {
        return $this->hasMany(HousekeepingTask::class);
    }

    /**
     * Personne ayant mis la chambre hors service.
     */
    public function outOfServiceByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'out_of_service_by');
    }

    /**
     * Pannes signalées sur cette chambre (non résolues ou toutes).
     */
    public function pannes()
    {
        return $this->hasMany(Panne::class);
    }
}

