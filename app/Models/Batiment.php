<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batiment extends Model
{
    use HasFactory;

    protected $table = 'batiments';

    protected $fillable = [
        'nom',
        'usage',
        'type',
        'surface_m2',
        'hauteur_m',
        'etat',
        'annee_construction',
        'geometrie',
        'zone_id',
        'notes'
    ];

    protected $casts = [
        'surface_m2' => 'decimal:2',
        'hauteur_m' => 'decimal:2',
        'annee_construction' => 'integer',
        'geometrie' => 'array'
    ];

    /**
     * Relation avec la zone fiscale
     */
    public function zone()
    {
        return $this->belongsTo(ZoneFiscale::class, 'zone_id');
    }

    /**
     * Scope pour les bâtiments commerciaux
     */
    public function scopeCommercial($query)
    {
        return $query->where('usage', 'commercial');
    }

    /**
     * Scope pour les bâtiments résidentiels
     */
    public function scopeResidential($query)
    {
        return $query->where('usage', 'residential');
    }

    /**
     * Scope pour les bâtiments en bon état
     */
    public function scopeGoodCondition($query)
    {
        return $query->where('etat', 'bon');
    }

    /**
     * Scope pour une zone spécifique
     */
    public function scopeByZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Scope pour les bâtiments avec surface disponible
     */
    public function scopeWithAvailableSpace($query, $minSurface = 100)
    {
        return $query->where('surface_m2', '>=', $minSurface);
    }
}
