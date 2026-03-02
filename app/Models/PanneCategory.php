<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PanneCategory extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'slug',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function panneTypes(): HasMany
    {
        return $this->hasMany(PanneType::class, 'panne_category_id');
    }

    public function pannes(): HasMany
    {
        return $this->hasMany(Panne::class, 'panne_category_id');
    }
}
