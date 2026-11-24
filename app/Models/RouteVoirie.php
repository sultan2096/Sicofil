<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteVoirie extends Model
{
    use HasFactory;

    protected $table = 'routes_voirie';

    protected $fillable = [
        'nom',
        'importance',
        'type',
        'longueur_km',
        'largeur_m',
        'etat',
        'geometrie',
        'zone_id',
        'notes'
    ];

    protected $casts = [
        'longueur_km' => 'decimal:2',
        'largeur_m' => 'decimal:2',
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
     * Scope pour les routes principales
     */
    public function scopePrimary($query)
    {
        return $query->where('importance', 'primary');
    }

    /**
     * Scope pour les routes secondaires
     */
    public function scopeSecondary($query)
    {
        return $query->where('importance', 'secondary');
    }

    /**
     * Scope pour les routes en bon état
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
}
