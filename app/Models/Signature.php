<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signature extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'image_base64',
    ];

    /**
     * Relation avec la réservation
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Obtenir l'image de la signature
     */
    public function getImageAttribute(): ?string
    {
        return $this->image_base64;
    }

    /**
     * Vérifier si la signature existe
     */
    public function hasSignature(): bool
    {
        return !is_null($this->image_base64) && !empty($this->image_base64);
    }
}
