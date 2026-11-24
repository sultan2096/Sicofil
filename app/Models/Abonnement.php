<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    use HasFactory;

    protected $table = 'abonnements';

    protected $fillable = [
        'commune_id',
        'formule',
        'statut',
        'date_debut',
        'date_expiration',
        'montant_fixe_annuel',
        'pourcentage_recettes',
        'frequence_paiement',
        'montant_achat_licence',
        'frais_maintenance',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_expiration' => 'date',
    ];

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }
}
