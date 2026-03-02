<?php

namespace App\Modules\Laundry\Models;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientLinen extends Model
{
    protected $table = 'client_linen';

    protected $fillable = [
        'hotel_id',
        'source',
        'room_id',
        'reservation_id',
        'housekeeping_task_id',
        'received_at',
        'received_by',
        'status',
        'description',
        'notes',
        'client_name',
        'picked_up_at',
        'picked_up_by',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'picked_up_at' => 'datetime',
    ];

    public const SOURCE_RECEPTION = 'reception';
    public const SOURCE_ROOM = 'room';

    public const STATUS_PENDING_PICKUP = 'pending_pickup';
    public const STATUS_AT_LAUNDRY = 'at_laundry';
    public const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    public const STATUS_PICKED_UP = 'picked_up';
    public const STATUS_SENT_TO_LAUNDRY = 'sent_to_laundry';

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING_PICKUP => 'À récupérer par la buanderie',
            self::STATUS_AT_LAUNDRY => 'À la buanderie',
            self::STATUS_READY_FOR_PICKUP => 'Prêt pour retrait client',
            self::STATUS_PICKED_UP => 'Récupéré par le client',
            self::STATUS_SENT_TO_LAUNDRY => 'Envoyé en lavage',
        ];
    }

    public static function sourceLabels(): array
    {
        return [
            self::SOURCE_RECEPTION => 'Réception',
            self::SOURCE_ROOM => 'Chambre',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function housekeepingTask(): BelongsTo
    {
        return $this->belongsTo(HousekeepingTask::class);
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function pickedUpByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picked_up_by');
    }

    public function scopeReception($query)
    {
        return $query->where('source', self::SOURCE_RECEPTION);
    }

    public function scopeRoom($query)
    {
        return $query->where('source', self::SOURCE_ROOM);
    }

    public function scopePendingPickup($query)
    {
        return $query->where('status', self::STATUS_PENDING_PICKUP);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getSourceLabelAttribute(): string
    {
        return self::sourceLabels()[$this->source] ?? $this->source;
    }
}
