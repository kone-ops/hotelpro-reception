<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Hotel extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'logo', 'address', 'phone', 'email', 'city', 'country', 
        'primary_color', 'secondary_color', 'oracle_dsn', 
        'oracle_username', 'oracle_password', 'public_form_url', 'settings', 'form_field_config', 'notification_settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'form_field_config' => 'array',
        'notification_settings' => 'array',
    ];
    
    /**
     * Les accesseurs à ajouter automatiquement au tableau/JSON
     */
    protected $appends = ['logo_url'];
    
    /**
     * Obtenir la clé de route (par défaut: id)
     * Assure que Laravel utilise l'ID pour le route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Alias pour compatibilité avec l'ancien code
     */
    public function preReservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function formFields()
    {
        return $this->hasMany(FormField::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function panneCategories()
    {
        return $this->hasMany(PanneCategory::class, 'hotel_id');
    }

    public function panneTypes()
    {
        return $this->hasMany(PanneType::class, 'hotel_id');
    }

    public function pannes()
    {
        return $this->hasMany(Panne::class, 'hotel_id');
    }

    /**
     * Obtenir l'URL complète du logo
     * 
     * @return string|null L'URL du logo ou null si le logo n'existe pas
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }
        
        // Nettoyer le chemin (compatibilité avec anciens chemins storage/)
        $cleanPath = $this->logo;
        if (strpos($cleanPath, 'storage/') === 0) {
            $cleanPath = str_replace('storage/', 'images/', $cleanPath);
        }
        
        // Vérifier si le fichier existe
        $fullPath = public_path($cleanPath);
        if (File::exists($fullPath)) {
            return asset($cleanPath);
        }
        
        return null;
    }
    
    /**
     * Vérifier si le logo existe
     * 
     * @return bool
     */
    public function hasLogo(): bool
    {
        if (!$this->logo) {
            return false;
        }
        
        // Nettoyer le chemin (compatibilité avec anciens chemins storage/)
        $cleanPath = $this->logo;
        if (strpos($cleanPath, 'storage/') === 0) {
            $cleanPath = str_replace('storage/', 'images/', $cleanPath);
        }
        
        return File::exists(public_path($cleanPath));
    }

    /**
     * Configuration des notifications client (email, SMS, WhatsApp) avec valeurs par défaut.
     * Si null ou vide : email activé avec templates système par défaut.
     */
    public function getNotificationConfig(): array
    {
        $stored = $this->notification_settings ?? [];
        if (!is_array($stored)) {
            $stored = [];
        }

        $emailEvents = ['created', 'validated', 'rejected', 'check_in', 'check_out'];
        $defaults = [
            'email' => [
                'enabled' => true,
                'from_name' => null,
                'templates' => [
                    'created' => ['subject' => '', 'body_html' => '', 'use_system_default' => true],
                    'validated' => ['subject' => '', 'body_html' => '', 'use_system_default' => true],
                    'rejected' => ['subject' => '', 'body_html' => '', 'use_system_default' => true],
                    'check_in' => ['subject' => '', 'body_html' => '', 'use_system_default' => true],
                    'check_out' => ['subject' => '', 'body_html' => '', 'use_system_default' => true],
                ],
            ],
            'sms' => [
                'enabled' => false,
                'api_key' => '',
                'sender' => '',
                'templates' => [
                    'created' => '',
                    'validated' => '',
                    'rejected' => '',
                    'check_in' => '',
                    'check_out' => '',
                ],
            ],
            'whatsapp' => [
                'enabled' => false,
                'api_key' => '',
                'phone_number_id' => '',
                'sender_number' => '',
                'sender_name' => '',
                'templates' => [
                    'created' => '',
                    'validated' => '',
                    'rejected' => '',
                    'check_in' => '',
                    'check_out' => '',
                ],
            ],
        ];

        foreach (['email', 'sms', 'whatsapp'] as $channel) {
            $defaults[$channel] = array_merge(
                $defaults[$channel],
                $stored[$channel] ?? []
            );
            if ($channel === 'email' && isset($stored[$channel]['templates'])) {
                foreach ($emailEvents as $event) {
                    $defaults[$channel]['templates'][$event] = array_merge(
                        $defaults[$channel]['templates'][$event] ?? [],
                        $stored[$channel]['templates'][$event] ?? []
                    );
                }
            }
            if (in_array($channel, ['sms', 'whatsapp']) && isset($stored[$channel]['templates'])) {
                foreach (['created', 'validated', 'rejected', 'check_in', 'check_out'] as $event) {
                    $defaults[$channel]['templates'][$event] = $stored[$channel]['templates'][$event] ?? $defaults[$channel]['templates'][$event];
                }
            }
        }

        return $defaults;
    }
}
