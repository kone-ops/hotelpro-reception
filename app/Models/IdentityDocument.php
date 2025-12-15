<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentityDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'type',
        'front_path',
        'back_path',
        'ocr_data',
        'number',
        'delivery_date',
        'delivery_place',
    ];

    protected $casts = [
        'ocr_data' => 'array',
        'delivery_date' => 'date',
    ];

    /**
     * Relation avec la réservation
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Obtenir l'URL complète du recto
     */
    public function getFrontUrlAttribute(): ?string
    {
        return $this->front_path ? asset('storage/' . $this->front_path) : null;
    }

    /**
     * Obtenir l'URL complète du verso
     */
    public function getBackUrlAttribute(): ?string
    {
        return $this->back_path ? asset('storage/' . $this->back_path) : null;
    }

    /**
     * Vérifier si le document a un verso
     */
    public function hasBackSide(): bool
    {
        return !is_null($this->back_path);
    }

    /**
     * Vérifier si l'OCR a été effectué
     */
    public function hasOcrData(): bool
    {
        return !is_null($this->ocr_data) && !empty($this->ocr_data);
    }
}
