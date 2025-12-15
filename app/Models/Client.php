<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'hotel_id',
        'email',
        'telephone',
        'numero_piece_identite',
        'type_piece_identite',
        'nom',
        'prenom',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'adresse',
        'profession',
        'piece_identite_recto_path',
        'piece_identite_verso_path',
        'piece_identite_delivery_date',
        'piece_identite_delivery_place',
        'piece_identite_ocr_data',
        'reservations_count',
        'last_reservation_at',
        'first_seen_at',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'piece_identite_delivery_date' => 'date',
        'piece_identite_ocr_data' => 'array',
        'last_reservation_at' => 'datetime',
        'first_seen_at' => 'datetime',
        'reservations_count' => 'integer',
    ];

    /**
     * Le scope "booted" du modèle.
     * Note: Le HotelScope est désactivé pour permettre les recherches publiques
     * Le filtrage par hotel_id se fait explicitement dans les requêtes
     */
    // protected static function booted(): void
    // {
    //     static::addGlobalScope(new HotelScope());
    // }

    /**
     * Relation avec l'hôtel
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Relation avec les réservations
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'client_id');
    }

    /**
     * Obtenir le nom complet du client
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }

    /**
     * Scope pour rechercher par email
     */
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope pour rechercher par téléphone
     */
    public function scopeByTelephone($query, $telephone)
    {
        return $query->where('telephone', $telephone);
    }

    /**
     * Scope pour rechercher par numéro de pièce d'identité
     */
    public function scopeByIdentityNumber($query, $numero)
    {
        return $query->where('numero_piece_identite', $numero);
    }

    /**
     * Incrémenter le compteur de réservations
     */
    public function incrementReservationsCount(): void
    {
        $this->increment('reservations_count');
        $this->update(['last_reservation_at' => now()]);
    }

    /**
     * Obtenir l'URL complète du recto de la pièce d'identité
     */
    public function getPieceIdentiteRectoUrlAttribute(): ?string
    {
        return $this->piece_identite_recto_path ? asset('storage/' . $this->piece_identite_recto_path) : null;
    }

    /**
     * Obtenir l'URL complète du verso de la pièce d'identité
     */
    public function getPieceIdentiteVersoUrlAttribute(): ?string
    {
        return $this->piece_identite_verso_path ? asset('storage/' . $this->piece_identite_verso_path) : null;
    }

    /**
     * Vérifier si le client a une pièce d'identité
     */
    public function hasIdentityDocument(): bool
    {
        return !is_null($this->piece_identite_recto_path);
    }
}
