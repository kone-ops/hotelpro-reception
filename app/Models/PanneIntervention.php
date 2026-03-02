<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanneIntervention extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'panne_id',
        'user_id',
        'action',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function panne(): BelongsTo
    {
        return $this->belongsTo(Panne::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
