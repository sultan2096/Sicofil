<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NatureActivite extends Model
{
    use HasFactory;

    protected $table = 'nature_activites';

    protected $fillable = [
        'nom',
        'description',
        'categorie',
        'actif',
        'ordre'
    ];

    protected $casts = [
        'actif' => 'boolean',
        'ordre' => 'integer'
    ];

    /**
     * Scope pour les natures actives
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
     * Scope pour filtrer par catégorie
     */
    public function scopeByCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    /**
     * Relation avec les types de commerce
     */
    public function typesCommerce()
    {
        return $this->hasMany(TypeCommerce::class, 'nature_activite_id');
    }

    /**
     * Relation avec les unités commerciales
     */
    public function unitesCommerciales()
    {
        return $this->hasMany(UniteCommerciale::class, 'nature', 'nom');
    }
}
