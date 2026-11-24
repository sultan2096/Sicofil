<?php

namespace App\Models;

use App\Models\Concerns\AppliesCommuneScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniteCommerciale extends Model
{
    use HasFactory, AppliesCommuneScope;

    protected $table = 'unites_commerciales';

    protected $fillable = [
        // Champs de base (table principale)
        'nom_proprietaire',
        'telephone',
        'email',
        'numero_cin',
        'numero_immatriculation',
        'ninea',
        'categorie',
        'nature',
        'type_commerce',
        'latitude',
        'longitude',
        'adresse',
        'commune_id',
        'zone_id',
        'taxe_id',
        'actif',
        
        // Champs ajoutés par migrations
        'nom_complet',
        'nom_commercial',
        'nature_point_interet',
        'type_point_interet',
        'enseigne_petit_panneau',
        'enseigne_et_petit_panneau',
        'taille_enseigne',
        'taille_grands_panneaux',
        'photo_panneau',
        'numero_identification_proprietaire',
        'telephone_proprietaire',
        
        // Champs de recensement
        'prenom_proprietaire',
        'sexe_proprietaire',
        'nin_proprietaire',
        'altitude',
        'precision',
        'sous_type_commerce',
        
        // Champs PostGIS (gérés automatiquement par les événements du modèle)
        // 'location',
        // 'geometrie',

        // Champs fonciers (polygones et superficie)
        'superficie',
        'geometrie_polygon',
        'enseigne_petite',
        'enseigne_grande',
        'taille_enseigne_petite',
        'taille_enseigne_grande',
        'collecteur_id',
        'photo',
        'date_recensement'
    ];

    protected $casts = [
        'date_recensement' => 'datetime',
        'coordinates' => 'array',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'altitude' => 'decimal:2',
        'precision' => 'decimal:2',
        'enseigne_petit_panneau' => 'boolean',
        'enseigne_et_petit_panneau' => 'boolean',
        'superficie' => 'decimal:2',
        'geometrie_polygon' => 'array'
    ];

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    /**
     * Relation avec la zone fiscale
     */
    public function zone()
    {
        return $this->belongsTo(ZoneFiscale::class, 'zone_id');
    }

    /**
     * Relation avec la taxe
     */
    public function taxe()
    {
        return $this->belongsTo(Taxe::class, 'taxe_id');
    }

    /**
     * Relation avec les paiements
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'unite_id');
    }

    /**
     * Relation avec le collecteur qui a recensé
     */
    public function collecteur()
    {
        return $this->belongsTo(User::class, 'collecteur_id');
    }

    /**
     * Scope pour les unités actives
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope par catégorie
     */
    public function scopeByCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    /**
     * Scope par zone
     */
    public function scopeByZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Accesseurs virtuels pour latitude/longitude depuis location PostGIS
     */
    public function getLatitudeAttribute($value)
    {
        if ($this->location) {
            $result = \Illuminate\Support\Facades\DB::selectOne(
                "SELECT ST_Y(?) as lat", [$this->location]
            );
            return $result ? (float) $result->lat : $value;
        }
        return $value;
    }

    public function getLongitudeAttribute($value)
    {
        if ($this->location) {
            $result = \Illuminate\Support\Facades\DB::selectOne(
                "SELECT ST_X(?) as lng", [$this->location]
            );
            return $result ? (float) $result->lng : $value;
        }
        return $value;
    }

    /**
     * Get coordinates as array for Leaflet
     */
    public function getCoordinatesAttribute($value)
    {
        // Privilégier latitude/longitude car ce sont les champs à jour
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }

        if ($value) {
            return json_decode($value, true);
        }

        return null;
    }

    /**
     * Synchroniser location PostGIS depuis latitude/longitude à la sauvegarde
     */
    protected static function booted()
    {
        static::saved(function ($model) {
            // Synchroniser location (POINT) depuis latitude/longitude
            if ($model->wasChanged('latitude') || $model->wasChanged('longitude')) {
                if ($model->latitude && $model->longitude) {
                    \Illuminate\Support\Facades\DB::statement(
                        'UPDATE unites_commerciales SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?',
                        [(float) $model->longitude, (float) $model->latitude, $model->id]
                    );
                }
            }

            // Synchroniser geometrie (POLYGON) depuis geometrie_polygon (JSON)
            if ($model->wasChanged('geometrie_polygon')) {
                if ($model->geometrie_polygon) {
                    $geojson = is_array($model->geometrie_polygon)
                        ? json_encode($model->geometrie_polygon)
                        : $model->geometrie_polygon;

                    // Extraire la geometry d'un objet Feature si nécessaire
                    $geojsonData = json_decode($geojson, true);
                    if (isset($geojsonData['geometry'])) {
                        $geojsonData = $geojsonData['geometry'];
                        $geojson = json_encode($geojsonData);
                    }

                    \Illuminate\Support\Facades\DB::statement(
                        'UPDATE unites_commerciales SET geometrie = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326) WHERE id = ?',
                        [$geojson, $model->id]
                    );
                } else {
                    \Illuminate\Support\Facades\DB::statement(
                        'UPDATE unites_commerciales SET geometrie = NULL WHERE id = ?',
                        [$model->id]
                    );
                }
            }
        });
    }

    /**
     * Get the full name of the owner (nom + prénom)
     */
    public function getNomCompletProprietaireAttribute()
    {
        return trim($this->nom_proprietaire . ' ' . $this->prenom_proprietaire);
    }

    public function getStatutBadgeAttribute()
    {
        $badges = [
            'actif' => 'success',
            'inactif' => 'danger',
            'en_attente' => 'warning',
            'suspendu' => 'secondary'
        ];

        return $badges[$this->statut] ?? 'secondary';
    }

    public function getTypeIconAttribute()
    {
        $icons = [
            'Boutique ou Magasin' => 'fas fa-store',
            'Atelier' => 'fas fa-tools',
            'Foirail' => 'fas fa-horse',
            'Stationnement' => 'fas fa-parking',
            'Etal' => 'fas fa-shopping-basket',
            'Table ou tente commerciale' => 'fas fa-umbrella',
            'Cantine ou kiosque' => 'fas fa-utensils',
            'Entreprise ou Service' => 'fas fa-building',
            'Parcelle Agricole' => 'fas fa-seedling'
        ];

        return $icons[$this->nature] ?? 'fas fa-store';
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeRecensesAujourdhui($query)
    {
        return $query->whereDate('date_recensement', today());
    }

    public function scopeParZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    public function scopeParCollecteur($query, $collecteurId)
    {
        return $query->where('collecteur_id', $collecteurId);
    }
} 