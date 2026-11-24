<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeCommerce extends Model
{
    use HasFactory;

    protected $table = 'types_commerce';

    protected $fillable = [
        'nom',
        'description',
        'nature_activite_id',
        'is_transport',
        'actif',
        'ordre'
    ];

    protected $casts = [
        'is_transport' => 'boolean',
        'actif' => 'boolean',
        'ordre' => 'integer'
    ];

    /**
     * Scope pour les types actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour ordonner par ordre puis par nom
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('nom');
    }

    /**
     * Relation avec la nature d'activité
     */
    public function natureActivite()
    {
        return $this->belongsTo(NatureActivite::class, 'nature_activite_id');
    }

    /**
     * Relation avec les unités commerciales
     */
    public function unites()
    {
        return $this->hasMany(UniteCommerciale::class, 'type_commerce', 'nom');
    }

    /**
     * Vérifier si le type est lié au transport
     */
    public function isTransport()
    {
        return $this->is_transport;
    }
}
