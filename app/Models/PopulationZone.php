<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopulationZone extends Model
{
    use HasFactory;

    protected $table = 'population_zones';

    protected $fillable = [
        'zone_id',
        'population',
        'annee',
        'source',
        'notes'
    ];

    protected $casts = [
        'annee' => 'integer',
        'population' => 'integer'
    ];

    /**
     * Relation avec la zone fiscale
     */
    public function zone()
    {
        return $this->belongsTo(ZoneFiscale::class, 'zone_id');
    }

    /**
     * Scope pour les données les plus récentes
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('annee', 'desc');
    }

    /**
     * Scope pour une année spécifique
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('annee', $year);
    }

    /**
     * Scope pour une zone spécifique
     */
    public function scopeByZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }
}
