<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationAuditLog extends Model
{
    protected $fillable = [
        'viewer_id',
        'viewed_user_id',
        'notification_id',
        'action',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur qui consulte
     */
    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    /**
     * Relation avec l'utilisateur dont les notifications sont consultées
     */
    public function viewedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_user_id');
    }

    /**
     * Relation avec la notification consultée
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(UserNotification::class, 'notification_id');
    }

    /**
     * Enregistrer une consultation
     */
    public static function logView(int $viewerId, int $viewedUserId, ?int $notificationId = null, string $action = 'view'): void
    {
        try {
            self::create([
                'viewer_id' => $viewerId,
                'viewed_user_id' => $viewedUserId,
                'notification_id' => $notificationId,
                'action' => $action,
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 500), // Limiter la taille
                'viewed_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Logger l'erreur mais ne pas bloquer l'application
            \Log::warning('Erreur lors de l\'enregistrement du log d\'audit de notification: ' . $e->getMessage());
        }
    }
}
