<?php

namespace App\Modules\Laundry\Models;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaundryCollection extends Model
{
    protected $table = 'laundry_collections';

    protected $fillable = [
        'hotel_id',
        'room_id',
        'housekeeping_task_id',
        'collected_at',
        'collected_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_WASH = 'in_wash';
    public const STATUS_DONE = 'done';

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function housekeepingTask(): BelongsTo
    {
        return $this->belongsTo(HousekeepingTask::class);
    }

    public function collectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(LaundryCollectionLine::class, 'laundry_collection_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInWash($query)
    {
        return $query->where('status', self::STATUS_IN_WASH);
    }

    public function scopeDone($query)
    {
        return $query->where('status', self::STATUS_DONE);
    }
}
