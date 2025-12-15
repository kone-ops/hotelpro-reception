<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\HotelScope;

class FormField extends Model
{
    /**
     * Le scope "booted" du modèle.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new HotelScope());
    }
    
    protected $fillable = [
        'hotel_id', 'key', 'label', 'type', 'required', 'options', 'position', 'section', 'active'
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'active' => 'boolean',
        'position' => 'float',
        'section' => 'integer',
    ];
    
    /**
     * Relation avec l'hôtel
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
