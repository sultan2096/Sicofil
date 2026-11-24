<?php

namespace App\Models;

use App\Models\Concerns\AppliesCommuneScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory, AppliesCommuneScope;

    protected $fillable = [
        'commune_id',
        'collecteur_id',
        'contribuable_id',
        'unite_id',
        'taxe_id',
        'montant',
        'montant_avance',
        'montant_restant',
        'date_paiement',
        'mode_paiement',
        'statut',
        'commentaire',
        'reference_transaction',
        'reference',
        'transaction_id',
        'payment_latitude',
        'payment_longitude',
        'payment_accuracy',
        'payment_method',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_avance' => 'decimal:2',
        'montant_restant' => 'decimal:2',
        'date_paiement' => 'date',
    ];

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    /**
     * Get the collecteur who made this payment
     */
    public function collecteur()
    {
        return $this->belongsTo(User::class, 'collecteur_id');
    }

    /**
     * Get the contribuable who made this payment (for online payments)
     */
    public function contribuable()
    {
        return $this->belongsTo(Contribuable::class, 'contribuable_id');
    }

    /**
     * Get the unité commerciale
     */
    public function unite()
    {
        return $this->belongsTo(UniteCommerciale::class, 'unite_id');
    }

    /**
     * Scope pour les paiements validés
     */
    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }

    /**
     * Scope par mode de paiement
     */
    public function scopeByModePaiement($query, $mode)
    {
        return $query->where('mode_paiement', $mode);
    }

    /**
     * Scope par période
     */
    public function scopePeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_paiement', [$dateDebut, $dateFin]);
    }

    /**
     * Scope par collecteur
     */
    public function scopeByCollecteur($query, $collecteurId)
    {
        return $query->where('collecteur_id', $collecteurId);
    }

    /**
     * Scope par zone (via l'unité commerciale)
     */
    public function scopeByZone($query, $zoneId)
    {
        return $query->whereHas('unite', function($q) use ($zoneId) {
            $q->where('zone_id', $zoneId);
        });
    }
} 