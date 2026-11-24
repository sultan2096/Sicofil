<?php

namespace App\Http\Controllers;

use App\Models\ZoneFiscale;
use App\Models\UniteCommerciale;
use App\Models\Paiement;
use App\Models\Commune;
use App\Services\GeoValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    /**
     * Display the map. Pour maire/gestionnaire/collecteur : uniquement la commune de l'utilisateur.
     */
    public function index()
    {
        $user = auth()->user();
        $role = strtolower((string) ($user->role ?? ''));
        $isSicofil = ($role === 'admin_sicofil');
        $commune = $isSicofil ? (session('commune_scope_id') ? Commune::find(session('commune_scope_id')) : null) : $user->commune;

        // Admin SICOFIL sans scope : afficher Sandiara par défaut pour voir les données
        if ($isSicofil && !$commune) {
            $commune = Commune::where('code', 'SANDIARA')->orWhere('nom', 'Sandiara')->first()
                ?? Commune::where('actif', true)->orderBy('nom')->first();
        }

        // Maire/gestionnaire/collecteur sans commune : ne pas afficher la carte
        if (!$isSicofil && !$commune) {
            return redirect()->route($role === 'maire' ? 'maire.dashboard' : ($role === 'gestionnaire' ? 'gestionnaire.dashboard' : 'collecteur.dashboard'))
                ->with('error', 'Aucune commune associée à votre compte. Contactez l\'administrateur.');
        }

        // Zones et unités : filtre explicite par commune (sans scope global pour éviter toute exclusion)
        $zonesQuery = $commune
            ? ZoneFiscale::withoutGlobalScopes()->where('commune_id', $commune->id)
            : ZoneFiscale::withoutGlobalScopes();
        $zones = $zonesQuery->get()->map(function ($zone) {
            return [
                'id' => $zone->id,
                'nom' => $zone->nom,
                'description' => $zone->description,
                'geometrie' => $zone->geometrie,
                'area_km2' => $this->calculateArea($zone->geometrie),
                'unites_count' => $zone->unitesCommerciales()->count(),
                'collecteurs_count' => $zone->collecteurs()->count(),
            ];
        })->toArray();

        $unitesQuery = $commune
            ? UniteCommerciale::withoutGlobalScopes()->where('commune_id', $commune->id)->with(['zone', 'taxe'])
            : UniteCommerciale::withoutGlobalScopes()->with(['zone', 'taxe']);
        $unites = $unitesQuery->actif()->get()->map(function ($unite) {
            return [
                'id' => $unite->id,
                'nom_proprietaire' => $unite->nom_proprietaire,
                'prenom_proprietaire' => $unite->prenom_proprietaire,
                'type_commerce' => $unite->type_commerce,
                'nature' => $unite->nature,
                'categorie' => $unite->categorie,
                'coordinates' => $unite->coordinates,
                'zone_nom' => $unite->zone ? $unite->zone->nom : 'Aucune zone',
                'taxe_nom' => $unite->taxe ? $unite->taxe->nom : 'Non définie',
                'adresse' => $unite->adresse,
                'telephone' => $unite->telephone,
            ];
        });

        [$mapCenter, $mapBounds] = $this->computeMapCenterAndBoundsFromZones($zones, $commune);

        return response()
            ->view('map.index', compact('zones', 'unites', 'commune', 'mapCenter', 'mapBounds'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Calcule le centre et les bornes de la carte à partir des zones, ou défauts par commune / Sénégal.
     */
    private function computeMapCenterAndBoundsFromZones(array $zones, ?Commune $commune): array
    {
        $defaultsByCode = [
            'SANDIARA' => [[14.41, -16.77], [[14.42699, -16.80247], [14.44382, -16.78059]]],
            'THIES' => [[14.79, -16.93], [[14.70, -17.00], [14.85, -16.85]]],
        ];
        $defaultSenegal = [[14.72, -17.45], null];

        $lats = [];
        $lngs = [];
        foreach ($zones as $z) {
            $geom = $z['geometrie'] ?? null;
            if (!$geom || !isset($geom['coordinates'])) {
                continue;
            }
            $this->extractCoordinatesRecursive($geom['coordinates'], $lats, $lngs);
        }
        if (!empty($lats) && !empty($lngs)) {
            $minLat = min($lats);
            $maxLat = max($lats);
            $minLng = min($lngs);
            $maxLng = max($lngs);
            $center = [($minLat + $maxLat) / 2, ($minLng + $maxLng) / 2];
            $bounds = [[$minLat, $minLng], [$maxLat, $maxLng]];
            return [$center, $bounds];
        }

        if ($commune && isset($defaultsByCode[$commune->code])) {
            return $defaultsByCode[$commune->code];
        }
        return [$defaultSenegal[0], $defaultSenegal[1]];
    }

    private function extractCoordinatesRecursive($coords, &$lats, &$lngs): void
    {
        if (!is_array($coords)) {
            return;
        }
        if (isset($coords[0]) && is_numeric($coords[0]) && isset($coords[1]) && is_numeric($coords[1])) {
            $lngs[] = (float) $coords[0];
            $lats[] = (float) $coords[1];
            return;
        }
        foreach ($coords as $item) {
            $this->extractCoordinatesRecursive($item, $lats, $lngs);
        }
    }

    /**
     * Afficher la carte thématique interactive. Données filtrées par commune (maire/gestionnaire/collecteur).
     */
    public function showThematicMap()
    {
        $user = auth()->user();
        $isSicofil = strtolower((string) $user->role) === 'admin_sicofil';
        $commune = $isSicofil ? (session('commune_scope_id') ? Commune::find(session('commune_scope_id')) : null) : $user->commune;

        $donnees_carte = collect();
        $zones = ZoneFiscale::select('id', 'nom')->get();
        $types_commerce = UniteCommerciale::distinct()->pluck('type_commerce')->filter()->values();
        $stats = [
            'total_unites' => UniteCommerciale::actif()->count(),
            'total_paiements' => Paiement::where('statut', 'valide')->count(),
            'total_recettes' => Paiement::where('statut', 'valide')->sum('montant'),
            'total_zones' => ZoneFiscale::count(),
        ];

        return view('rapports.carte-thematique', compact('donnees_carte', 'zones', 'types_commerce', 'stats', 'commune'));
    }

    /**
     * Diagnostic : quelles tables SHP existent en base ?
     */
    public function boundariesStatus()
    {
        $candidates = $this->getBoundaryTableCandidates();
        $status = [];
        // Couche "zones" = zones_fiscales (plateforme), pas de table PostGIS
        $status['zones'] = [
            'exists' => ZoneFiscale::withoutGlobalScopes()->whereNotNull('geometrie')->exists(),
            'table' => 'zones_fiscales (Laravel)',
        ];
        foreach ($candidates as $layer => $tables) {
            $found = $this->resolveBoundaryTable($layer);
            $status[$layer] = [
                'exists' => $found !== null,
                'table' => $found,
            ];
        }
        $allTables = [];
        try {
            $rows = DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public' ORDER BY tablename");
            $allTables = array_column($rows, 'tablename');
        } catch (\Throwable $e) {
            $allTables = ['error' => $e->getMessage()];
        }
        $conn = config('database.default');
        return response()->json([
            'db' => config("database.connections.{$conn}.database"),
            'host' => config("database.connections.{$conn}.host"),
            'driver' => $conn,
            'layers' => $status,
            'all_tables' => $allTables,
        ]);
    }

    /**
     * Noms de tables PostGIS possibles par couche (avec underscores ou avec espaces selon import).
     * La première table existante en base sera utilisée.
     */
    private function getBoundaryTableCandidates(): array
    {
        return [
            'commune' => ['commune_sandiara', 'sandiaracommune'],
            'sandiaracentre' => ['polygone_sandiaracentre', 'polygone sandiaracentre', 'polygone sandiaracentre '],
            'ndioukhthiorokh' => ['polygone_ndioukhthiorokh', 'polygone ndioukhthiorokhe'],
            'loulibentegne' => ['polygone_loulibentegne', 'polygone loulybentegne'],
        ];
    }

    /**
     * Retourne le nom SQL qualifié (public."nom table") de la première table existante pour une couche.
     */
    private function resolveBoundaryTable(string $layer): ?string
    {
        $candidates = $this->getBoundaryTableCandidates();
        if (!isset($candidates[$layer])) {
            return null;
        }
        foreach ($candidates[$layer] as $tableName) {
            $exists = DB::selectOne(
                "SELECT 1 FROM pg_catalog.pg_tables WHERE schemaname = 'public' AND tablename = ?",
                [$tableName]
            );
            if ($exists) {
                return 'public.' . (strpos($tableName, ' ') !== false ? '"' . str_replace('"', '""', $tableName) . '"' : $tableName);
            }
        }
        return null;
    }

    /**
     * Fallback: retourne les géométries des zones fiscales en GeoJSON quand aucun SHP n'est importé.
     * Filtre par zone pour éviter de dessiner 3 fois les mêmes polygones.
     */
    private function getZonesFiscalesAsGeoJSON(string $layer): array
    {
        $query = ZoneFiscale::withoutGlobalScopes()->whereNotNull('geometrie');

        // Filtrer par zone pour que chaque couche n'affiche que sa zone
        if ($layer === 'commune') {
            // Pour commune: retourner toutes les zones (union approximative) ou vide
            $query->whereIn('nom', ['Loulibentegne', 'Sandiara Centre', 'Ndioukhthiorokh']);
        } elseif ($layer === 'sandiaracentre') {
            $query->where(function ($q) {
                $q->where('nom', 'ilike', '%sandiara%centre%')
                    ->orWhere('nom', 'ilike', 'sandiara centre');
            });
        } elseif ($layer === 'loulibentegne') {
            $query->where(function ($q) {
                $q->where('nom', 'ilike', '%loulibentegne%')
                    ->orWhere('nom', 'ilike', '%louly%benteign%');
            });
        } elseif ($layer === 'ndioukhthiorokh') {
            $query->where(function ($q) {
                $q->where('nom', 'ilike', '%ndioukh%')
                    ->orWhere('nom', 'ilike', '%ndiouk%thiorokh%');
            });
        }

        $zones = $query->get(['id', 'nom', 'geometrie', 'commune_id']);
        $features = [];
        foreach ($zones as $z) {
            $geom = $z->geometrie;
            if (!is_array($geom) || empty($geom['type']) || empty($geom['coordinates'])) {
                continue;
            }
            $features[] = [
                'type' => 'Feature',
                'id' => $z->id,
                'properties' => [
                    'id' => $z->id,
                    'nom' => $z->nom,
                    'commune_id' => $z->commune_id,
                ],
                'geometry' => $geom,
            ];
        }

        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    /**
     * Toutes les zones fiscales avec géométrie (toutes communes) — pour affichage direct sur la carte.
     * Utilisé quand on importe un SHP pour une autre commune : les zones vont dans zones_fiscales et s'affichent ici.
     */
    private function getZonesFiscalesAsGeoJSONAll(?string $communeId = null): array
    {
        $query = ZoneFiscale::withoutGlobalScopes()->whereNotNull('geometrie');
        if ($communeId !== null && $communeId !== '') {
            $query->where('commune_id', (int) $communeId);
        }
        $zones = $query->get(['id', 'nom', 'geometrie', 'commune_id']);

        $features = [];
        foreach ($zones as $z) {
            $geom = $z->geometrie;
            if (!is_array($geom) || empty($geom['type']) || empty($geom['coordinates'])) {
                continue;
            }
            $features[] = [
                'type' => 'Feature',
                'id' => $z->id,
                'properties' => [
                    'id' => $z->id,
                    'nom' => $z->nom,
                    'commune_id' => $z->commune_id,
                ],
                'geometry' => $geom,
            ];
        }

        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    /**
     * Retourne un FeatureCollection GeoJSON pour les couches shapefile importées
     * GET /api/map/boundaries?layer=zones|commune|sandiaracentre|ndioukhthiorokh|loulibentegne&simplify=0.0001
     * layer=zones : toutes les zones fiscales (table zones_fiscales), y compris SHP importés pour toute commune
     * Optional: bbox=minx,miny,maxx,maxy (WGS84) pour filtrer par emprise
     */
    public function getBoundariesGeoJSON(Request $request)
    {
        $layer = $request->query('layer');
        $simplify = (float) ($request->query('simplify', 0.0));
        $bbox = $request->query('bbox'); // "minx,miny,maxx,maxy"
        $user = auth()->user();
        $isSicofil = $user && strtolower((string) $user->role) === 'admin_sicofil';
        $communeId = $request->query('commune_id');
        // Pour maire/gestionnaire/collecteur : forcer la commune de l'utilisateur (sécurité)
        if (!$isSicofil && $user && $user->commune_id) {
            $communeId = (string) $user->commune_id;
        }
        if ($isSicofil && session('commune_scope_id')) {
            $communeId = $communeId ?: (string) session('commune_scope_id');
        }

        // Couche "zones" = zones fiscales de la commune (ou toutes si admin SICOFIL sans scope)
        if ($layer === 'zones') {
            $cacheKey = 'map.boundaries.zones.' . ($communeId ?: 'all');
            $fc = Cache::remember($cacheKey, 300, function () use ($communeId) {
                return $this->getZonesFiscalesAsGeoJSONAll($communeId ?: null);
            });
            return response()->json([
                'success' => true,
                'layer' => $layer,
                'simplify' => $simplify,
                'data' => $fc,
            ], 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Cache-Control' => 'private, max-age=120',
            ]);
        }

        $layerToTable = [
            'commune' => 'public.commune_sandiara',
            'sandiaracentre' => 'public.polygone_sandiaracentre',
            'ndioukhthiorokh' => 'public.polygone_ndioukhthiorokh',
            'loulibentegne' => 'public.polygone_loulibentegne',
        ];

        if (!isset($layerToTable[$layer])) {
            return response()->json([
                'success' => false,
                'error' => 'Layer invalide. Utiliser: zones|commune|sandiaracentre|ndioukhthiorokh|loulibentegne'
            ], 400);
        }

        $table = $this->resolveBoundaryTable($layer);
        if ($table === null) {
            // Aucune table SHP : retourner vide (afficher uniquement les vrais SHP importés)
            return response()->json([
                'success' => true,
                'layer' => $layer,
                'simplify' => $simplify,
                'bbox' => $bbox,
                'data' => ['type' => 'FeatureCollection', 'features' => []],
            ], 200, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        // Toujours convertir en WGS84 (4326) pour la carte - corrige SRID 0
        $geomBase = "CASE WHEN ST_SRID(geom) = 0 THEN ST_SetSRID(geom, 4326) ELSE ST_Transform(geom, 4326) END";
        $geomExpr = $geomBase;
        if ($simplify > 0) {
            $geomExpr = "ST_SimplifyPreserveTopology({$geomBase}, {$simplify})";
        }

        $where = '';
        if ($bbox) {
            $parts = explode(',', $bbox);
            if (count($parts) === 4) {
                [$minx, $miny, $maxx, $maxy] = array_map('floatval', $parts);
                $where = sprintf(
                    "WHERE %s && ST_MakeEnvelope(%f, %f, %f, %f, 4326)",
                    $geomExpr, $minx, $miny, $maxx, $maxy
                );
            }
        }

        $sql = "
            SELECT json_build_object(
                'type',     'FeatureCollection',
                'features', COALESCE(json_agg(
                    json_build_object(
                        'type',       'Feature',
                        'id',         COALESCE(gid, rn),
                        'geometry',   ST_AsGeoJSON({$geomExpr})::json,
                        'properties', jsonb_strip_nulls((row_to_json(t)::jsonb) - 'geom')
                    )
                ), '[]'::json)
            ) AS geojson
            FROM (
                SELECT *, row_number() over() as rn
                FROM {$table}
                {$where}
            ) AS t
            WHERE {$geomExpr} IS NOT NULL
        ";

        try {
            $result = DB::select($sql);
            $fc = $result && isset($result[0]->geojson) ? $result[0]->geojson : ['type' => 'FeatureCollection', 'features' => []];
            // Certains drivers renvoient une chaîne JSON; décoder pour retourner un objet/array utilisable par le front
            if (is_string($fc)) {
                $decoded = json_decode($fc, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $fc = $decoded;
                } else {
                    // fallback vide si le JSON est invalide
                    \Log::warning("Erreur décodage JSON pour layer {$layer}: " . json_last_error_msg());
                    $fc = ['type' => 'FeatureCollection', 'features' => []];
                }
            }

            // S'assurer que $fc est un array valide
            if (!is_array($fc)) {
                \Log::warning("Géométrie invalide pour layer {$layer}, type: " . gettype($fc));
                $fc = ['type' => 'FeatureCollection', 'features' => []];
            }

            // Vérifier que la structure est correcte
            if (!isset($fc['type']) || !isset($fc['features'])) {
                \Log::warning("Structure GeoJSON invalide pour layer {$layer}");
                $fc = ['type' => 'FeatureCollection', 'features' => []];
            }

            return response()->json([
                'success' => true,
                'layer' => $layer,
                'simplify' => $simplify,
                'bbox' => $bbox,
                'data' => $fc
            ], 200, ['Content-Type' => 'application/json; charset=utf-8']);
        } catch (\Throwable $e) {
            \Log::error("Erreur lors de la génération GeoJSON pour layer {$layer}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la génération GeoJSON: '.$e->getMessage(),
                'layer' => $layer
            ], 500, ['Content-Type' => 'application/json; charset=utf-8']);
        }
    }

    /**
     * API pour la carte thématique géospatiale
     * Fournit les données avec statut de paiement pour les rapports
     */
    public function getThematicMapData(Request $request)
    {
        $request->validate([
            'statut' => 'nullable|in:paye,impaye,tous',
            'zone_id' => 'nullable|exists:zones_fiscales,id',
            'periode_debut' => 'nullable|date',
            'periode_fin' => 'nullable|date',
            'type_commerce' => 'nullable|string',
            'categorie' => 'nullable|string'
        ]);

        $query = UniteCommerciale::with(['zone', 'taxe', 'paiements' => function($q) {
            $q->where('statut', 'valide');
        }]);

        // Filtres
        if ($request->zone_id) {
            $query->where('zone_id', $request->zone_id);
        }

        if ($request->type_commerce) {
            $query->where('type_commerce', $request->type_commerce);
        }

        if ($request->categorie) {
            $query->where('categorie', $request->categorie);
        }

        $unites = $query->actif()->get();

        // Traitement des données avec statut de paiement
        $mapData = $unites->map(function($unite) use ($request) {
            // Déterminer le statut de paiement
            $statutPaiement = $this->determinerStatutPaiement($unite, $request->periode_debut, $request->periode_fin);
            
            // Calculer le montant dû
            $montantDu = $this->calculerMontantDu($unite, $request->periode_debut, $request->periode_fin);
            
            // Filtrer par statut si demandé
            if ($request->statut && $request->statut !== 'tous') {
                if ($request->statut === 'paye' && $statutPaiement !== 'paye') {
                    return null;
                }
                if ($request->statut === 'impaye' && $statutPaiement !== 'impaye') {
                    return null;
                }
            }

            return [
                'id' => $unite->id,
                'nom_proprietaire' => $unite->nom_proprietaire,
                'prenom_proprietaire' => $unite->prenom_proprietaire,
                'nom_commerce' => $unite->nom_commerce,
                'type_commerce' => $unite->type_commerce,
                'categorie' => $unite->categorie,
                'adresse' => $unite->adresse,
                'telephone' => $unite->telephone,
                'coordinates' => [
                    'lat' => $unite->latitude,
                    'lng' => $unite->longitude
                ],
                'zone' => $unite->zone ? [
                    'id' => $unite->zone->id,
                    'nom' => $unite->zone->nom
                ] : null,
                'taxe' => $unite->taxe ? [
                    'id' => $unite->taxe->id,
                    'nom' => $unite->taxe->nom,
                    'montant' => $unite->taxe->montant
                ] : null,
                'statut_paiement' => $statutPaiement,
                'montant_du' => $montantDu,
                'dernier_paiement' => $unite->paiements->sortByDesc('date_paiement')->first(),
                'nombre_paiements' => $unite->paiements->count()
            ];
        })->filter()->values();

        // Statistiques agrégées
        $statistiques = [
            'total_unites' => $mapData->count(),
            'payees' => $mapData->where('statut_paiement', 'paye')->count(),
            'impayees' => $mapData->where('statut_paiement', 'impaye')->count(),
            'en_retard' => $mapData->where('statut_paiement', 'en_retard')->count(),
            'total_montant_du' => $mapData->sum('montant_du'),
            'par_zone' => $mapData->groupBy('zone.nom')->map(function($group) {
                return [
                    'total' => $group->count(),
                    'payees' => $group->where('statut_paiement', 'paye')->count(),
                    'impayees' => $group->where('statut_paiement', 'impaye')->count(),
                    'montant_total_du' => $group->sum('montant_du')
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $mapData,
            'statistiques' => $statistiques,
            'filtres_appliques' => $request->only(['statut', 'zone_id', 'periode_debut', 'periode_fin', 'type_commerce', 'categorie'])
        ]);
    }

    /**
     * Détermine le statut de paiement d'une unité commerciale
     */
    private function determinerStatutPaiement($unite, $periodeDebut = null, $periodeFin = null)
    {
        $paiementsValides = $unite->paiements->where('statut', 'valide');
        
        if ($periodeDebut && $periodeFin) {
            $paiementsValides = $paiementsValides->filter(function($paiement) use ($periodeDebut, $periodeFin) {
                return $paiement->date_paiement >= $periodeDebut && $paiement->date_paiement <= $periodeFin;
            });
        }

        if ($paiementsValides->isEmpty()) {
            return 'impaye';
        }

        // Vérifier si le paiement est complet
        $montantTaxe = $unite->taxe ? $unite->taxe->montant : 0;
        $montantPaye = $paiementsValides->sum('montant');

        if ($montantPaye >= $montantTaxe) {
            return 'paye';
        } elseif ($montantPaye > 0) {
            return 'partiel';
        } else {
            return 'impaye';
        }
    }

    /**
     * Calcule le montant dû pour une unité commerciale
     */
    private function calculerMontantDu($unite, $periodeDebut = null, $periodeFin = null)
    {
        $montantTaxe = $unite->taxe ? $unite->taxe->montant : 0;
        
        $paiementsValides = $unite->paiements->where('statut', 'valide');
        
        if ($periodeDebut && $periodeFin) {
            $paiementsValides = $paiementsValides->filter(function($paiement) use ($periodeDebut, $periodeFin) {
                return $paiement->date_paiement >= $periodeDebut && $paiement->date_paiement <= $periodeFin;
            });
        }

        $montantPaye = $paiementsValides->sum('montant');
        
        return max(0, $montantTaxe - $montantPaye);
    }

    /**
     * Get map data as JSON (for AJAX updates)
     */
    public function getData(Request $request)
    {
        $query = UniteCommerciale::with(['zone', 'taxe']);

        // Filtres
        if ($request->zone_id) {
            $query->where('zone_id', $request->zone_id);
        }

        if ($request->type_commerce) {
            $query->where('type_commerce', $request->type_commerce);
        }

        if ($request->categorie) {
            $query->where('categorie', $request->categorie);
        }

        $unites = $query->actif()->get()->map(function($unite) {
            return [
                'id' => $unite->id,
                'nom_proprietaire' => $unite->nom_proprietaire,
                'prenom_proprietaire' => $unite->prenom_proprietaire,
                'type_commerce' => $unite->type_commerce,
                'nature' => $unite->nature,
                'categorie' => $unite->categorie,
                'coordinates' => $unite->coordinates,
                'zone_nom' => $unite->zone ? $unite->zone->nom : 'Aucune zone',
                'taxe_nom' => $unite->taxe ? $unite->taxe->nom : 'Non définie',
                'adresse' => $unite->adresse,
                'telephone' => $unite->telephone,
            ];
        });

        return response()->json($unites);
    }

    /**
     * Search unites by text
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $unites = UniteCommerciale::where(function($q) use ($validated) {
            $search = $validated['query'];
            $q->where('nom_proprietaire', 'like', "%{$search}%")
              ->orWhere('prenom_proprietaire', 'like', "%{$search}%")
              ->orWhere('type_commerce', 'like', "%{$search}%")
              ->orWhere('adresse', 'like', "%{$search}%");
        })
        ->actif()
        ->limit(10)
        ->get()
        ->map(function($unite) {
            return [
                'id' => $unite->id,
                'label' => $unite->nom_complet_proprietaire . ' - ' . $unite->type_commerce,
                'coordinates' => $unite->coordinates,
            ];
        });

        return response()->json($unites);
    }

    /**
     * Afficher les détails d'une unité commerciale
     */
    public function show($id)
    {
        $unite = UniteCommerciale::with(['zone', 'taxe', 'paiements' => function($q) {
            $q->where('statut', 'valide');
        }])->findOrFail($id);

        return response()->json([
            'id' => $unite->id,
            'nom_proprietaire' => $unite->nom_proprietaire,
            'prenom_proprietaire' => $unite->prenom_proprietaire,
            'nom_commerce' => $unite->nom_commerce,
            'type_commerce' => $unite->type_commerce,
            'categorie' => $unite->categorie,
            'adresse' => $unite->adresse,
            'telephone' => $unite->telephone,
            'coordinates' => [
                'lat' => $unite->latitude,
                'lng' => $unite->longitude
            ],
            'zone' => $unite->zone ? [
                'id' => $unite->zone->id,
                'nom' => $unite->zone->nom
            ] : null,
            'taxe' => $unite->taxe ? [
                'id' => $unite->taxe->id,
                'nom' => $unite->taxe->nom,
                'montant' => $unite->taxe->montant
            ] : null,
            'statut_paiement' => $this->determinerStatutPaiement($unite),
            'montant_du' => $this->calculerMontantDu($unite),
            'dernier_paiement' => $unite->paiements->sortByDesc('date_paiement')->first(),
            'nombre_paiements' => $unite->paiements->count()
        ]);
    }

    /**
     * Calculer l'aire approximative d'un polygone
     */
    private function calculateArea($geometry)
    {
        if (!isset($geometry['coordinates'][0])) {
            return 0;
        }
        
        $coordinates = $geometry['coordinates'][0];
        $area = 0;
        $n = count($coordinates);
        
        for ($i = 0; $i < $n - 1; $i++) {
            $area += ($coordinates[$i][0] * $coordinates[$i + 1][1]) - ($coordinates[$i + 1][0] * $coordinates[$i][1]);
        }
        
        return abs($area) / 2 * 111000 * 111000 / 1000000; // Conversion approximative en km²
    }

    /**
     * Affiche le formulaire d'import de zones (GeoJSON). Maire/gestionnaire/collecteur : uniquement leur commune.
     */
    public function showImportZonesForm()
    {
        $user = auth()->user();
        $isSicofil = strtolower((string) $user->role) === 'admin_sicofil';
        if ($isSicofil && session('commune_scope_id')) {
            $communes = Commune::where('id', session('commune_scope_id'))->get();
            $preselectCommuneId = session('commune_scope_id');
        } elseif ($isSicofil) {
            $communes = Commune::where('actif', true)->orderBy('nom')->get();
            $preselectCommuneId = old('commune_id');
        } else {
            if (!$user->commune_id) {
                return redirect()->route('map.index')->with('error', 'Aucune commune associée à votre compte.');
            }
            $communes = collect([$user->commune]);
            $preselectCommuneId = $user->commune_id;
        }
        return view('map.import-zones', compact('communes', 'preselectCommuneId', 'isSicofil'));
    }

    /**
     * Traite l'import de zones depuis des fichiers GeoJSON et/ou Shapefile.
     * GeoJSON : .json, .geojson. Shapefile : .zip ou ensemble .shp + .shx + .dbf (même nom).
     */
    public function storeImportZones(Request $request, GeoValidator $validator)
    {
        $user = auth()->user();
        $isSicofil = strtolower((string) $user->role) === 'admin_sicofil';
        $rules = [
            'commune_id' => 'required|exists:communes,id',
            'fichier' => 'required',
            'fichier.*' => 'file|max:51200',
            'remplacer' => 'nullable|boolean',
        ];
        $request->validate($rules);

        $communeId = (int) $request->commune_id;
        if (!$isSicofil && $user->commune_id && (int) $user->commune_id !== $communeId) {
            return redirect()->route('map.import-zones')->with('error', 'Vous ne pouvez importer des zones que pour votre commune.');
        }
        $replace = (bool) $request->remplacer;

        $files = $request->file('fichier');
        if (!is_array($files)) {
            $files = $files ? [$files] : [];
        }
        $files = array_filter($files);
        if (empty($files)) {
            return redirect()->route('map.import-zones')->with('error', 'Aucun fichier sélectionné.');
        }

        // Séparer : GeoJSON, ZIP, et composants shapefile (.shp, .shx, .dbf, .prj, etc.)
        $geojsonExtensions = ['json', 'geojson'];
        $shpExtensions = ['shp', 'shx', 'dbf', 'prj', 'cpg', 'sbn', 'sbx'];
        $geojsonFiles = [];
        $zipFiles = [];
        $shpByBasename = []; // [ 'polygone loulybentegne' => [ UploadedFile .shp, .shx, .dbf, ... ], ... ]

        foreach ($files as $file) {
            $name = $file->getClientOriginalName();
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $base = pathinfo($name, PATHINFO_FILENAME);

            if (in_array($ext, $geojsonExtensions, true)) {
                $geojsonFiles[] = $file;
            } elseif ($ext === 'zip') {
                $zipFiles[] = $file;
            } elseif (in_array($ext, $shpExtensions, true)) {
                if (!isset($shpByBasename[$base])) {
                    $shpByBasename[$base] = [];
                }
                $shpByBasename[$base][] = $file;
            }
        }

        $hasGeoJson = count($geojsonFiles) > 0;
        $hasZip = count($zipFiles) > 0;
        $hasShpGroups = !empty(array_filter($shpByBasename, function ($group) {
            foreach ($group as $f) {
                if (strtolower(pathinfo($f->getClientOriginalName(), PATHINFO_EXTENSION)) === 'shp') {
                    return true;
                }
            }
            return false;
        }));

        if (!$hasGeoJson && !$hasZip && !$hasShpGroups) {
            return redirect()->route('map.import-zones')->with('error', 'Aucun fichier valide. Utilisez des fichiers GeoJSON (.json, .geojson), un .zip contenant un shapefile, ou un jeu .shp + .shx + .dbf (même nom).');
        }

        if ($replace) {
            ZoneFiscale::withoutGlobalScopes()->where('commune_id', $communeId)->delete();
        }

        $totalCreated = 0;
        $errors = [];

        // 1) Import GeoJSON
        foreach ($geojsonFiles as $file) {
            $content = @file_get_contents($file->getRealPath());
            if ($content === false) {
                $errors[] = $file->getClientOriginalName() . ' : illisible.';
                continue;
            }
            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['type']) || $data['type'] !== 'FeatureCollection') {
                $errors[] = $file->getClientOriginalName() . ' : GeoJSON FeatureCollection attendu.';
                continue;
            }
            $features = $data['features'] ?? [];
            foreach ($features as $i => $feature) {
                if (!isset($feature['geometry']) || !in_array($feature['geometry']['type'] ?? '', ['Polygon', 'MultiPolygon'], true)) {
                    continue;
                }
                $check = $validator->validate($feature['geometry']);
                if (!$check['ok']) {
                    $errors[] = $file->getClientOriginalName() . " (#" . ($i + 1) . ") : " . implode(' ', $check['errors']);
                    continue;
                }
                $props = $feature['properties'] ?? [];
                $nom = $props['name'] ?? $props['NOM'] ?? $props['nom'] ?? ('Zone ' . ($i + 1));
                ZoneFiscale::withoutGlobalScopes()->create([
                    'commune_id' => $communeId,
                    'nom' => is_string($nom) ? $nom : 'Zone ' . ($i + 1),
                    'geometrie' => $check['geometry'],
                ]);
                $totalCreated++;
            }
        }

        // 2) Import ZIP (shapefile dans le zip)
        foreach ($zipFiles as $file) {
            $result = $this->importZonesFromZip($file, $communeId, false);
            $totalCreated += $result['created'];
            if ($result['error']) {
                $errors[] = $result['error'];
            }
        }

        // 3) Import shapefile par groupe (même basename)
        foreach ($shpByBasename as $base => $group) {
            $hasShp = false;
            foreach ($group as $f) {
                if (strtolower(pathinfo($f->getClientOriginalName(), PATHINFO_EXTENSION)) === 'shp') {
                    $hasShp = true;
                    break;
                }
            }
            if (!$hasShp) {
                continue;
            }
            $result = $this->importZonesFromShpGroup($group, $communeId, false);
            $totalCreated += $result['created'];
            if ($result['error']) {
                $errors[] = $result['error'];
            }
        }

        if ($totalCreated > 0) {
            Cache::forget('map.boundaries.zones.' . $communeId);
        }

        $isAdminSicofil = strtolower(auth()->user()->role ?? '') === 'admin_sicofil';
        $redirectTo = $isAdminSicofil ? route('communes.map') : route('map.index');

        if ($totalCreated > 0) {
            $message = $totalCreated . ' zone(s) importée(s). Elles s\'affichent sur la carte.';
            if (!empty($errors)) {
                $message .= ' ' . implode(' ', array_slice($errors, 0, 3));
            }
            return redirect($redirectTo)->with('success', $message);
        }

        if (!empty($errors)) {
            return redirect()->route('map.import-zones')->with('error', implode(' ', $errors));
        }
        return redirect($redirectTo)->with('info', 'Aucune géométrie Polygon/MultiPolygon trouvée.');
    }

    /**
     * Import zones depuis un fichier .zip contenant un shapefile (.shp + .shx + .dbf...).
     */
    private function importZonesFromZip($file, int $communeId, bool $truncate): array
    {
        $tmpDir = storage_path('app/temp_shp_' . uniqid());
        if (!@mkdir($tmpDir, 0755, true)) {
            return ['created' => 0, 'error' => $file->getClientOriginalName() . ' : impossible de créer le dossier temporaire.'];
        }
        try {
            $zip = new \ZipArchive();
            if ($zip->open($file->getRealPath()) !== true) {
                return ['created' => 0, 'error' => $file->getClientOriginalName() . ' : ZIP invalide.'];
            }
            $zip->extractTo($tmpDir);
            $zip->close();

            $shpPath = null;
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpDir, \RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($it as $f) {
                if ($f->isFile() && strtolower(substr($f->getFilename(), -4)) === '.shp') {
                    $shpPath = $f->getPathname();
                    break;
                }
            }
            if (!$shpPath) {
                return ['created' => 0, 'error' => $file->getClientOriginalName() . ' : aucun .shp dans le ZIP.'];
            }

            $exitCode = Artisan::call('sicofil:import:shapefile', [
                'path' => $shpPath,
                '--commune' => $communeId,
                '--truncate' => $truncate,
            ]);
            $out = trim(Artisan::output());
            $created = (int) preg_replace('/\D/', '', $out) ?: 0;
            if ($exitCode !== 0) {
                return ['created' => 0, 'error' => $file->getClientOriginalName() . ' : ' . ($out ?: 'erreur import shapefile.')];
            }
            return ['created' => $created, 'error' => null];
        } finally {
            if (is_dir($tmpDir)) {
                foreach (glob($tmpDir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
                    @unlink($f);
                }
                @rmdir($tmpDir);
            }
        }
    }

    /**
     * Import zones depuis un groupe de fichiers shapefile (même basename : .shp, .shx, .dbf, ...).
     */
    private function importZonesFromShpGroup(array $uploadedFiles, int $communeId, bool $truncate): array
    {
        $tmpDir = storage_path('app/temp_shp_' . uniqid());
        if (!@mkdir($tmpDir, 0755, true)) {
            return ['created' => 0, 'error' => 'Impossible de créer le dossier temporaire.'];
        }
        try {
            $shpPath = null;
            foreach ($uploadedFiles as $f) {
                $name = $f->getClientOriginalName();
                $dest = $tmpDir . DIRECTORY_SEPARATOR . $name;
                if (@copy($f->getRealPath(), $dest) && strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'shp') {
                    $shpPath = $dest;
                }
            }
            if (!$shpPath || !is_file($shpPath)) {
                return ['created' => 0, 'error' => 'Fichier .shp manquant dans le groupe.'];
            }

            $exitCode = Artisan::call('sicofil:import:shapefile', [
                'path' => $tmpDir,
                '--commune' => $communeId,
                '--truncate' => $truncate,
            ]);
            $out = trim(Artisan::output());
            $created = (int) preg_replace('/\D/', '', $out) ?: 0;
            if ($exitCode !== 0) {
                return ['created' => 0, 'error' => 'Shapefile : ' . ($out ?: 'erreur import.')];
            }
            return ['created' => $created, 'error' => null];
        } finally {
            if (is_dir($tmpDir)) {
                foreach (glob($tmpDir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
                    @unlink($f);
                }
                @rmdir($tmpDir);
            }
        }
    }
} 