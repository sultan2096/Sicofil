<?php

namespace App\Models;

use App\Models\Concerns\AppliesCommuneScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxe extends Model
{
    use HasFactory, AppliesCommuneScope;

    protected $fillable = [
        'commune_id',
        'nom',
        'periodicite',
        'montant',
        'nature',
        'activite_produit',
        'unite',
        'description',
        'base_calcul',
        'taux',
        'date_debut',
        'date_fin',
        'is_fonciere',
        'montant_par_m2',
        'categories_applicables',
        'natures_applicables',
        'types_commerce_applicables',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'taux' => 'decimal:2',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'is_fonciere' => 'boolean',
        'montant_par_m2' => 'decimal:2',
        'categories_applicables' => 'array',
        'natures_applicables' => 'array',
        'types_commerce_applicables' => 'array',
    ];

    /**
     * Accesseur pour convertir la périodicité pour l'affichage
     */
    public function getPeriodiciteDisplayAttribute()
    {
        return match($this->periodicite) {
            'mois' => 'mensuelle',
            'an' => 'annuelle',
            'jour' => 'quotidienne',
            'non_periodique' => 'ponctuelle',
            default => $this->periodicite
        };
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    /**
     * Get the unités commerciales using this tax
     */
    public function unitesCommerciales()
    {
        return $this->hasMany(UniteCommerciale::class, 'taxe_id');
    }

    /**
     * Alias for unitesCommerciales() - used by controller
     */
    public function unites()
    {
        return $this->unitesCommerciales();
    }

    /**
     * Get the paiements for this tax through unites commerciales
     */
    public function paiements()
    {
        return $this->hasManyThrough(Paiement::class, UniteCommerciale::class, 'taxe_id', 'unite_id');
    }

    /**
     * Scope pour filtrer par nature
     */
    public function scopeByNature($query, $nature)
    {
        return $query->where('nature', $nature);
    }

    /**
     * Scope pour filtrer par périodicité
     */
    public function scopeByPeriodicite($query, $periodicite)
    {
        return $query->where('periodicite', $periodicite);
    }
} 