<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Scopes\HotelScope;

class Reservation extends Model
{
    use HasFactory;
    
    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'reservations';
    
    /**
     * Le scope "booted" du modèle.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new HotelScope());
    }

    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'room_id',
        'status',
        'group_code',
        'data',
        'check_in_date',
        'check_out_date',
        'validated_at',
        'validated_by',
        'checked_in_at',
        'checked_in_by',
        'checked_out_at',
        'checked_out_by',
        'total_amount',
        'paid_amount',
        'payment_method',
        'oracle_synced_at',
        'oracle_id',
    ];

    protected $casts = [
        'data' => 'array',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'validated_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'oracle_synced_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    /**
     * Relation avec l'hôtel
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Relation avec le document d'identité (utilise reservation_id)
     */
    public function identityDocument(): HasOne
    {
        return $this->hasOne(IdentityDocument::class, 'reservation_id');
    }

    /**
     * Relation avec la signature (utilise reservation_id)
     */
    public function signature(): HasOne
    {
        return $this->hasOne(Signature::class, 'reservation_id');
    }


    /**
     * Relation avec le type de chambre
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Relation avec la chambre assignée
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Relation avec l'utilisateur qui a validé/rejeté
     */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relation avec l'utilisateur qui a effectué le check-in
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Relation avec l'utilisateur qui a effectué le check-out
     */
    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    /**
     * Scope pour les réservations individuelles
     */
    public function scopeIndividual($query)
    {
        return $query->whereNull('group_code');
    }

    /**
     * Scope pour les réservations de groupe
     */
    public function scopeGroup($query)
    {
        return $query->whereNotNull('group_code');
    }

    /**
     * Scope par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope réservations en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope réservations validées
     */
    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    /**
     * Scope réservations avec check-in effectué
     */
    public function scopeCheckedIn($query)
    {
        return $query->where('status', 'checked_in');
    }

    /**
     * Scope réservations avec check-out effectué
     */
    public function scopeCheckedOut($query)
    {
        return $query->where('status', 'checked_out');
    }

    /**
     * Vérifier si c'est une réservation de groupe
     */
    public function isGroup(): bool
    {
        return !is_null($this->group_code);
    }

    /**
     * Obtenir le nom complet du client
     */
    public function getClientFullNameAttribute(): ?string
    {
        $data = $this->data;
        if (isset($data['nom']) && isset($data['prenom'])) {
            return $data['prenom'] . ' ' . $data['nom'];
        }
        return null;
    }

    /**
     * Obtenir l'email du client
     */
    public function getClientEmailAttribute(): ?string
    {
        return $this->data['email'] ?? null;
    }

    /**
     * Obtenir le téléphone du client
     */
    public function getClientPhoneAttribute(): ?string
    {
        return $this->data['telephone'] ?? null;
    }

    /**
     * Obtenir le solde restant
     */
    public function getBalanceAttribute(): float
    {
        return ($this->total_amount ?? 0) - ($this->paid_amount ?? 0);
    }

    /**
     * Vérifier si la réservation est entièrement payée
     */
    public function isFullyPaid(): bool
    {
        return $this->balance <= 0;
    }

    /**
     * Vérifier si le client est actuellement en séjour
     */
    public function isCurrentlyStaying(): bool
    {
        return $this->status === 'checked_in' && 
               $this->check_in_date <= now() && 
               $this->check_out_date >= now();
    }
}

