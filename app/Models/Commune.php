<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'acronyme',
        'region',
        'departement',
        'actif',
        'collecteurs_peuvent_recenser',
        'collecteurs_peuvent_mettre_a_jour',
        'description',
        'logo',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'collecteurs_peuvent_recenser' => 'boolean',
        'collecteurs_peuvent_mettre_a_jour' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'commune_id');
    }

    public function zonesFiscales()
    {
        return $this->hasMany(ZoneFiscale::class, 'commune_id');
    }

    public function taxes()
    {
        return $this->hasMany(Taxe::class, 'commune_id');
    }

    public function unitesCommerciales()
    {
        return $this->hasMany(UniteCommerciale::class, 'commune_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'commune_id');
    }

    public function abonnement()
    {
        return $this->hasOne(Abonnement::class, 'commune_id');
    }

    /**
     * Maire(s) de la commune
     */
    public function maires()
    {
        return $this->hasMany(User::class, 'commune_id')->where('role', 'maire');
    }

    /**
     * Gestionnaire(s) de la commune
     */
    public function gestionnaires()
    {
        return $this->hasMany(User::class, 'commune_id')->where('role', 'gestionnaire');
    }

    /**
     * Collecteurs de la commune
     */
    public function collecteurs()
    {
        return $this->hasMany(User::class, 'commune_id')->where('role', 'collecteur');
    }

    /**
     * Acronyme affiché (ex. SICOSA, SICOTH). Si vide en base, génère SICO + 2 premières lettres du nom.
     */
    public function getDisplayAcronymeAttribute(): string
    {
        if (!empty($this->acronyme)) {
            return (string) $this->acronyme;
        }
        $letters = strtoupper(preg_replace('/[^a-zA-Z]/', '', $this->nom ?? ''));
        $suffix = strlen($letters) >= 2 ? substr($letters, 0, 2) : ($letters ?: 'XX');
        return 'SICO' . $suffix;
    }

    /**
     * URL publique du logo. Servi par la route communes.logo (pas besoin du lien storage).
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->logo)) {
            return null;
        }
        if (str_starts_with($this->logo, 'http')) {
            return $this->logo;
        }
        try {
            return route('communes.logo', ['commune' => $this]);
        } catch (\Throwable $e) {
            $path = str_replace('\\', '/', ltrim($this->logo, '/'));
            return asset('storage/' . $path);
        }
    }
}
