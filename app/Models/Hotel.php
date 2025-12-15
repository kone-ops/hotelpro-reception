<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'name', 'logo', 'address', 'phone', 'email', 'city', 'country', 
        'primary_color', 'secondary_color', 'oracle_dsn', 
        'oracle_username', 'oracle_password', 'public_form_url', 'settings', 'form_field_config'
    ];

    protected $casts = [
        'settings' => 'array',
        'form_field_config' => 'array',
    ];

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
    
    /**
     * Obtenir la configuration d'un champ du formulaire
     */
    public function getFieldConfig(string $fieldName): array
    {
        $config = $this->form_field_config ?? [];
        
        // Configuration par défaut si non définie
        $defaults = [
            'signature' => ['visible' => true, 'required' => true],
            'identity_document' => ['visible' => true, 'required' => false],
            'identity_document_front' => ['visible' => true, 'required' => false],
            'identity_document_back' => ['visible' => true, 'required' => false],
            'document_delivery_date' => ['visible' => true, 'required' => false],
            'document_delivery_place' => ['visible' => true, 'required' => false],
            'profession' => ['visible' => true, 'required' => true],
            'address' => ['visible' => true, 'required' => false],
            'nationality' => ['visible' => true, 'required' => true],
        ];
        
        return $config[$fieldName] ?? ($defaults[$fieldName] ?? ['visible' => true, 'required' => false]);
    }
    
    /**
     * Vérifier si un champ est visible
     */
    public function isFieldVisible(string $fieldName): bool
    {
        return $this->getFieldConfig($fieldName)['visible'] ?? true;
    }
    
    /**
     * Vérifier si un champ est obligatoire
     */
    public function isFieldRequired(string $fieldName): bool
    {
        return $this->getFieldConfig($fieldName)['required'] ?? false;
    }
}
