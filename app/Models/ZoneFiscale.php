<?php

namespace App\Models;

use App\Models\Concerns\AppliesCommuneScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneFiscale extends Model
{
    use HasFactory, AppliesCommuneScope;

    protected $table = 'zones_fiscales';

    protected $fillable = [
        'nom',
        'geometrie',
        'description',
        'commune_id',
    ];

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    protected $casts = [
        'geometrie' => 'array',
    ];

    /**
     * Get the users (collecteurs) assigned to this zone
     */
    public function users()
    {
        return $this->hasMany(User::class, 'zone_id');
    }

    /**
     * Get the unités commerciales in this zone
     */
    public function unitesCommerciales()
    {
        return $this->hasMany(UniteCommerciale::class, 'zone_id');
    }

    /**
     * Get the collecteurs assigned to this zone (multi-collecteurs supporté)
     */
    public function collecteurs()
    {
        return $this->hasMany(User::class, 'zone_id')->where('role', 'collecteur');
    }

    /**
     * Get only active collecteurs assigned to this zone.
     */
    public function collecteursActifs()
    {
        return $this->collecteurs()->where(function ($q) {
            $q->whereNull('actif')->orWhere('actif', true);
        });
    }

    /**
     * Affecter un collecteur à cette zone (la zone doit appartenir à la même commune que le collecteur).
     */
    public function assignCollecteur(User $collecteur): bool
    {
        if ($collecteur->role !== 'collecteur') {
            return false;
        }
        if ((int) $collecteur->commune_id !== (int) $this->commune_id) {
            return false;
        }
        $collecteur->zone_id = $this->id;
        return $collecteur->save();
    }

    /**
     * Get the paiements for this zone via unités commerciales
     */
    public function paiements()
    {
        return $this->hasManyThrough(Paiement::class, UniteCommerciale::class, 'zone_id', 'unite_id');
    }

    /**
     * Get the villages and amonts for this zone
     * 
     * @return array
     */
    public function getVillagesAttribute()
    {
        $villagesMap = [
            'Loulibentegne' => [
                'Kouthie Wolof', 'Kibik', 'Ngomène', 'Louly Mbafay', 'Louly Benteigné',
                'Louly Sindiane', 'Mbourokh', 'Nianiar', 'Louly Ngogom'
            ],
            'Sandiara Centre' => [
                'Faylar 1', 'Faylar 2', 'Sandiara Sérère', 'Sandiara Wolof',
                'Garage Diakher', 'Soukhem', 'Godaguène', 'Ndiouk Fissel'
            ],
            'Ndioukhthiorokh' => [
                'Ndioukh Thiorokh', 'Samane', 'Louly Ndia', 'Keur Diba', 'Soussane', 'Ndollor'
            ],
            // Variantes possibles (avec espace et minuscule)
            'Ndioukh thiorokh' => [
                'Ndioukh Thiorokh', 'Samane', 'Louly Ndia', 'Keur Diba', 'Soussane', 'Ndollor'
            ],
            'Ndioukh Thiorokh' => [
                'Ndioukh Thiorokh', 'Samane', 'Louly Ndia', 'Keur Diba', 'Soussane', 'Ndollor'
            ],
            'ndioukhthiorokh' => [
                'Ndioukh Thiorokh', 'Samane', 'Louly Ndia', 'Keur Diba', 'Soussane', 'Ndollor'
            ],
            'ndioukh thiorokh' => [
                'Ndioukh Thiorokh', 'Samane', 'Louly Ndia', 'Keur Diba', 'Soussane', 'Ndollor'
            ],
        ];

        // Normaliser le nom de la zone pour la recherche
        $normalizedNom = trim($this->nom);
        
        // Essayer avec le nom exact (case-sensitive)
        if (isset($villagesMap[$normalizedNom])) {
            return $villagesMap[$normalizedNom];
        }

        // Essayer avec le nom en minuscules
        $lowerNom = strtolower($normalizedNom);
        if (isset($villagesMap[$lowerNom])) {
            return $villagesMap[$lowerNom];
        }

        // Essayer avec le nom sans espaces
        $noSpaceNom = str_replace(' ', '', $normalizedNom);
        if (isset($villagesMap[$noSpaceNom])) {
            return $villagesMap[$noSpaceNom];
        }

        // Essayer avec différentes variantes (recherche partielle)
        foreach ($villagesMap as $key => $villages) {
            $keyLower = strtolower(str_replace(' ', '', $key));
            $nomLower = strtolower(str_replace(' ', '', $normalizedNom));
            
            // Si les noms normalisés correspondent (sans espaces, en minuscules)
            if ($keyLower === $nomLower) {
                return $villages;
            }
            
            // Recherche partielle (contient)
            if (stripos($normalizedNom, $key) !== false || stripos($key, $normalizedNom) !== false) {
                return $villages;
            }
        }

        return [];
    }
} 