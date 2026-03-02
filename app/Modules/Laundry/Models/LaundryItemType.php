<?php

namespace App\Modules\Laundry\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaundryItemType extends Model
{
    protected $table = 'laundry_item_types';

    protected $fillable = [
        'hotel_id',
        'name',
        'code',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function collectionLines(): HasMany
    {
        return $this->hasMany(LaundryCollectionLine::class, 'laundry_item_type_id');
    }
}
