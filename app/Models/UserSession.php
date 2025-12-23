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
        'device_name',
        'device_type',
        'browser',
        'platform',
        'country',
        'city',
        'region',
        'latitude',
        'longitude',
        'is_trusted_device',
        'device_fingerprint',
        'is_suspicious',
        'suspicious_reasons',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_trusted_device' => 'boolean',
        'is_suspicious' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'suspicious_reasons' => 'array',
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
    public function scopeActive($query, ?int $lifetimeMinutes = null)
    {
        $lifetimeMinutes = $lifetimeMinutes ?? config('session.lifetime', 4320);
        $expirationTime = now()->subMinutes($lifetimeMinutes);
        return $query->where('last_activity', '>', $expirationTime);
    }

    /**
     * Vérifier si la session est expirée
     */
    public function isExpired(?int $lifetimeMinutes = null): bool
    {
        if (!$this->last_activity) {
            return true;
        }
        
        $lifetimeMinutes = $lifetimeMinutes ?? config('session.lifetime', 4320);
        return $this->last_activity->lt(now()->subMinutes($lifetimeMinutes));
    }

    /**
     * Vérifier si la session est valide (existe et n'est pas expirée)
     */
    public function isValid(): bool
    {
        return $this->exists && !$this->isExpired();
    }
}
