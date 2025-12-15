<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
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
     * Vérifier si la chambre est disponible
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Changer le statut de la chambre
     */
    public function updateStatus(string $status): bool
    {
        if (!in_array($status, ['available', 'occupied', 'maintenance', 'reserved'])) {
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
        if ($this->status !== 'available') {
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
            ->where('status', 'available')
            ->get()
            ->filter(function($room) use ($checkIn, $checkOut) {
                return $room->isAvailableForPeriod($checkIn, $checkOut);
            });
    }
}

