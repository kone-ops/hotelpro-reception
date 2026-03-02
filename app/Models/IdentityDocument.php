<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;

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
        if (!$this->front_path) {
            return null;
        }
        
        // Nettoyer le chemin (compatibilité avec anciens chemins storage/)
        $cleanPath = $this->front_path;
        if (strpos($cleanPath, 'storage/') === 0) {
            $cleanPath = str_replace('storage/', 'images/', $cleanPath);
        }
        
        $fullPath = public_path($cleanPath);
        if (File::exists($fullPath)) {
            return asset($cleanPath);
        }
        
        return null;
    }

    /**
     * Obtenir l'URL complète du verso
     */
    public function getBackUrlAttribute(): ?string
    {
        if (!$this->back_path) {
            return null;
        }
        
        // Nettoyer le chemin (compatibilité avec anciens chemins storage/)
        $cleanPath = $this->back_path;
        if (strpos($cleanPath, 'storage/') === 0) {
            $cleanPath = str_replace('storage/', 'images/', $cleanPath);
        }
        
        $fullPath = public_path($cleanPath);
        if (File::exists($fullPath)) {
            return asset($cleanPath);
        }
        
        return null;
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
