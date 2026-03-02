<?php

namespace App\Modules\Maintenance\Models;

use App\Models\Hotel;
use App\Models\Panne;
use App\Models\PanneCategory;
use App\Models\PanneType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceArea extends Model
{
    protected $table = 'maintenance_areas';

    public const CATEGORY_ESPACES_PUBLICS = 'espaces_publics';
    public const CATEGORY_ESPACES_TECHNIQUES = 'espaces_techniques';
    public const CATEGORY_ESPACES_EXTERIEURS = 'espaces_exterieurs';
    public const CATEGORY_LOISIRS = 'loisirs';
    public const CATEGORY_ADMINISTRATION = 'administration';

    public const CATEGORIES = [
        self::CATEGORY_ESPACES_PUBLICS => 'Espaces publics',
        self::CATEGORY_ESPACES_TECHNIQUES => 'Espaces techniques',
        self::CATEGORY_ESPACES_EXTERIEURS => 'Espaces extérieurs',
        self::CATEGORY_LOISIRS => 'Loisirs',
        self::CATEGORY_ADMINISTRATION => 'Administration',
    ];

    public const STATE_NORMAL = 'normal';
    public const STATE_ISSUE = 'issue';
    public const STATE_MAINTENANCE = 'maintenance';
    public const STATE_OUT_OF_SERVICE = 'out_of_service';

    protected $fillable = [
        'hotel_id',
        'category',
        'name',
        'description',
        'technical_state',
        'notes',
        'panne_category_id',
        'panne_type_id',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function pannes(): HasMany
    {
        return $this->hasMany(Panne::class, 'maintenance_area_id');
    }

    public function panneCategory(): BelongsTo
    {
        return $this->belongsTo(PanneCategory::class, 'panne_category_id');
    }

    public function panneType(): BelongsTo
    {
        return $this->belongsTo(PanneType::class, 'panne_type_id');
    }

    public static function getCategoryLabel(string $category): string
    {
        return self::CATEGORIES[$category] ?? $category;
    }
}
