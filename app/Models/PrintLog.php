<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PrintLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'printer_id',
        'user_id',
        'hotel_id',
        'type_document',
        'reference',
        'contenu',
        'statut',
        'erreur',
        'metadata',
        'debut_impression',
        'fin_impression',
        'tentatives'
    ];

    protected $casts = [
        'metadata' => 'array',
        'debut_impression' => 'datetime',
        'fin_impression' => 'datetime',
        'tentatives' => 'integer'
    ];

    // Relations
    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    // Scopes
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeByPrinter($query, $printerId)
    {
        return $query->where('printer_id', $printerId);
    }

    public function scopeByHotel($query, $hotelId)
    {
        return $query->where('hotel_id', $hotelId);
    }

    public function scopeByTypeDocument($query, $type)
    {
        return $query->where('type_document', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('statut', 'succes');
    }

    public function scopeFailed($query)
    {
        return $query->where('statut', 'echec');
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('statut', ['en_attente', 'en_cours']);
    }

    // Méthodes de statut
    public function isEnAttente()
    {
        return $this->statut === 'en_attente';
    }

    public function isEnCours()
    {
        return $this->statut === 'en_cours';
    }

    public function isSucces()
    {
        return $this->statut === 'succes';
    }

    public function isEchec()
    {
        return $this->statut === 'echec';
    }

    public function isAnnule()
    {
        return $this->statut === 'annule';
    }

    // Méthodes utilitaires
    public function getDureeImpressionAttribute()
    {
        if ($this->debut_impression && $this->fin_impression) {
            return $this->debut_impression->diffInSeconds($this->fin_impression);
        }
        return null;
    }

    public function getDureeImpressionFormateeAttribute()
    {
        $duree = $this->getDureeImpressionAttribute();
        if ($duree !== null) {
            if ($duree < 60) {
                return $duree . ' secondes';
            } elseif ($duree < 3600) {
                return floor($duree / 60) . ' minutes ' . ($duree % 60) . ' secondes';
            } else {
                $heures = floor($duree / 3600);
                $minutes = floor(($duree % 3600) / 60);
                return $heures . ' heures ' . $minutes . ' minutes';
            }
        }
        return 'Non disponible';
    }

    public function getStatutLabelAttribute()
    {
        $labels = [
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'succes' => 'Succès',
            'echec' => 'Échec',
            'annule' => 'Annulé'
        ];

        return $labels[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute()
    {
        $colors = [
            'en_attente' => 'warning',
            'en_cours' => 'info',
            'succes' => 'success',
            'echec' => 'danger',
            'annule' => 'secondary'
        ];

        return $colors[$this->statut] ?? 'secondary';
    }

    public function getTypeDocumentLabelAttribute()
    {
        $labels = [
            'recu_client' => 'Reçu client',
            'ticket_caisse' => 'Ticket caisse',
            'ticket_non_paye' => 'Ticket non payé',
            'test' => 'Test d\'impression',
            'fiche_reception' => 'Fiche de réception',
            'badge_client' => 'Badge client',
            'facture' => 'Facture',
            'rapport' => 'Rapport'
        ];

        return $labels[$this->type_document] ?? $this->type_document;
    }

    // Méthodes de gestion
    public function marquerEnCours()
    {
        $this->update([
            'statut' => 'en_cours',
            'debut_impression' => now(),
            'tentatives' => $this->tentatives + 1
        ]);
    }

    public function marquerSucces()
    {
        $this->update([
            'statut' => 'succes',
            'fin_impression' => now(),
            'erreur' => null
        ]);
    }

    public function marquerEchec($erreur)
    {
        $this->update([
            'statut' => 'echec',
            'fin_impression' => now(),
            'erreur' => $erreur
        ]);
    }

    public function marquerAnnule()
    {
        $this->update([
            'statut' => 'annule',
            'fin_impression' => now()
        ]);
    }

    public function relancer()
    {
        if ($this->isEchec() || $this->isAnnule()) {
            $this->update([
                'statut' => 'en_attente',
                'erreur' => null,
                'debut_impression' => null,
                'fin_impression' => null
            ]);
            return true;
        }
        return false;
    }

    public function ajouterMetadata($key, $value)
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->update(['metadata' => $metadata]);
    }

    public function getMetadata($key, $default = null)
    {
        return data_get($this->metadata, $key, $default);
    }

    // Méthodes statiques
    public static function getStatistiques($dateDebut = null, $dateFin = null, $hotelId = null)
    {
        $query = static::query();

        if ($dateDebut) {
            $query->where('created_at', '>=', $dateDebut);
        }

        if ($dateFin) {
            $query->where('created_at', '<=', $dateFin);
        }

        if ($hotelId) {
            $query->where('hotel_id', $hotelId);
        }

        return [
            'total' => $query->count(),
            'succes' => $query->where('statut', 'succes')->count(),
            'echec' => $query->where('statut', 'echec')->count(),
            'en_attente' => $query->where('statut', 'en_attente')->count(),
            'en_cours' => $query->where('statut', 'en_cours')->count(),
            'annule' => $query->where('statut', 'annule')->count(),
            'taux_succes' => $query->count() > 0 ? round(($query->where('statut', 'succes')->count() / $query->count()) * 100, 2) : 0,
            'temps_moyen' => $query->whereNotNull('debut_impression')
                                  ->whereNotNull('fin_impression')
                                  ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, debut_impression, fin_impression)) as temps_moyen')
                                  ->value('temps_moyen')
        ];
    }

    public static function getTypesDocuments()
    {
        return [
            'recu_client' => 'Reçu client',
            'ticket_caisse' => 'Ticket caisse',
            'ticket_non_paye' => 'Ticket non payé',
            'test' => 'Test d\'impression',
            'fiche_reception' => 'Fiche de réception',
            'badge_client' => 'Badge client',
            'facture' => 'Facture',
            'rapport' => 'Rapport'
        ];
    }
}
