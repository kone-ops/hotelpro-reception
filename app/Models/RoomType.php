<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'price',
        'description',
        'capacity',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'capacity' => 'integer',
        'is_available' => 'boolean',
    ];

    /**
     * Relation avec l'hôtel
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Relation avec les chambres
     */
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Obtenir le nombre de chambres de ce type
     */
    public function getRoomsCountAttribute(): int
    {
        return $this->rooms()->count();
    }

    /**
     * Obtenir le nombre de chambres disponibles
     */
    public function getAvailableRoomsCountAttribute(): int
    {
        return $this->rooms()->where('status', 'available')->count();
    }
}
