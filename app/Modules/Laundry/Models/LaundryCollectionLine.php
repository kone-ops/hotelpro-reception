<?php

namespace App\Modules\Laundry\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundryCollectionLine extends Model
{
    protected $table = 'laundry_collection_lines';

    protected $fillable = [
        'laundry_collection_id',
        'laundry_item_type_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(LaundryCollection::class, 'laundry_collection_id');
    }

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(LaundryItemType::class, 'laundry_item_type_id');
    }
}
