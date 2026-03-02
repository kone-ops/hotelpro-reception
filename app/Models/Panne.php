<?php

namespace App\Models;

use App\Modules\Maintenance\Models\MaintenanceArea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Panne extends Model
{
    public const STATUS_SIGNALEE = 'signalée';
    public const STATUS_EN_COURS = 'en_cours';
    public const STATUS_RESOLUE = 'résolue';

    public const STATUSES = [
        self::STATUS_SIGNALEE => 'Signalée',
        self::STATUS_EN_COURS => 'En cours de maintenance',
        self::STATUS_RESOLUE => 'Résolue',
    ];

    public const LOCATION_ROOM = 'room';
    public const LOCATION_AREA = 'area';

    protected $fillable = [
        'hotel_id',
        'panne_type_id',
        'panne_category_id',
        'location_type',
        'room_id',
        'maintenance_area_id',
        'description',
        'reported_by',
        'reported_at',
        'status',
        'resolved_by',
        'resolved_at',
        'custom_fields',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
        'custom_fields' => 'array',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function panneType(): BelongsTo
    {
        return $this->belongsTo(PanneType::class, 'panne_type_id');
    }

    public function panneCategory(): BelongsTo
    {
        return $this->belongsTo(PanneCategory::class, 'panne_category_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function maintenanceArea(): BelongsTo
    {
        return $this->belongsTo(MaintenanceArea::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(PanneIntervention::class)->orderBy('created_at');
    }

    public function getLocationLabelAttribute(): string
    {
        if ($this->location_type === self::LOCATION_ROOM && $this->room) {
            return 'Chambre ' . $this->room->room_number;
        }
        if ($this->location_type === self::LOCATION_AREA && $this->maintenanceArea) {
            return $this->maintenanceArea->name;
        }
        return '-';
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
