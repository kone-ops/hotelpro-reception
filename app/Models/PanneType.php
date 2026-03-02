<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PanneType extends Model
{
    protected $fillable = [
        'hotel_id',
        'panne_category_id',
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

    public function panneCategory(): BelongsTo
    {
        return $this->belongsTo(PanneCategory::class, 'panne_category_id');
    }

    public function pannes(): HasMany
    {
        return $this->hasMany(Panne::class, 'panne_type_id');
    }
}
