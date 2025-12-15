<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'last_activity',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les sessions actives
     */
    public function scopeActive($query, int $lifetimeMinutes = 4320)
    {
        $expirationTime = now()->subMinutes($lifetimeMinutes);
        return $query->where('last_activity', '>', $expirationTime);
    }

    /**
     * Vérifier si la session est expirée
     */
    public function isExpired(int $lifetimeMinutes = 4320): bool
    {
        if (!$this->last_activity) {
            return true;
        }
        
        return $this->last_activity->lt(now()->subMinutes($lifetimeMinutes));
    }
}
