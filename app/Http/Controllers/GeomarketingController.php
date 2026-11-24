<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UniteCommerciale;
use App\Models\ZoneFiscale;
use App\Models\TypeCommerce;
use App\Models\NatureActivite;
use App\Models\PopulationZone;
use App\Models\RouteVoirie;
use App\Models\Batiment;
use Illuminate\Support\Facades\DB;

class GeomarketingController extends Controller
{
    /**
     * Affiche le tableau de bord de géomarketing.
     */
    public function index()
    {
        $naturesActivites = NatureActivite::where('actif', true)->orderBy('nom')->get();
        $typesCommerce = TypeCommerce::where('actif', true)->orderBy('nom')->get();

        return view('maire.geomarketing', [
            'naturesActivites' => $naturesActivites,
            'typesCommerce' => $typesCommerce
        ]);
    }

    /**
     * Fournit les données pour la carte de géomarketing.
     */
    public function data(Request $request)
    {
        $natureActiviteId = $request->input('nature_activite_id');
        $typeCommerceId = $request->input('type_commerce_id');

        // Récupérer zones avec leur géométrie et filtrer à la commune de Sandiara
        $bbox = self::getSandiaraBbox();
        $zones = ZoneFiscale::whereNotNull('geometrie')->get(['id', 'nom', 'geometrie'])
            ->filter(function ($z) use ($bbox) {
                $geom = json_decode($z->geometrie, true);
                $zb = self::bboxFromGeometry($geom);
                return self::bboxIntersects($zb, $bbox);
            })->values();

        // Compter les unités commerciales par zone en fonction des filtres
        $query = UniteCommerciale::query()
            ->select('zone_id', DB::raw('count(*) as total'))
            ->where('actif', true)
            ->groupBy('zone_id');

        if ($typeCommerceId) {
            // Si on filtre par type, on cherche le nom du type
            $type = TypeCommerce::find($typeCommerceId);
            if ($type) {
                $query->where('type_commerce', $type->nom);
            }
        } elseif ($natureActiviteId) {
            // Sinon, si on filtre par nature, on prend tous les types de cette nature
            $types = TypeCommerce::where('nature_activite_id', $natureActiviteId)->pluck('nom');
            $query->whereIn('type_commerce', $types);
        }

        $comptesParZone = $query->pluck('total', 'zone_id');

        // Formatter les données pour le frontend
        $data = $zones->map(function ($zone) use ($comptesParZone) {
            return [
                'id' => $zone->id,
                'nom' => $zone->nom,
                'geometrie' => json_decode($zone->geometrie), // Assurez-vous que la géométrie est décodée
                'nombre_unites' => $comptesParZone->get($zone->id, 0)
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Données population par zone (GeoJSON-like). Placeholder en attendant la source réelle.
     */
    public function population(Request $request)
    {
        // Récupérer population la plus récente par zone (par annee si dispo, sinon dernier enregistrement)
        $rows = PopulationZone::query()
            ->select('zone_id', 'population', 'annee', 'updated_at', 'created_at')
            ->orderByRaw('COALESCE(annee, 0) DESC')
            ->get()
            ->groupBy('zone_id')
            ->map(function ($group) {
                // Prendre la plus grande année, sinon le plus récent
                $byYear = $group->filter(fn($r) => !is_null($r->annee));
                if ($byYear->count()) {
                    $maxYear = $byYear->max('annee');
                    return $byYear->firstWhere('annee', $maxYear);
                }
                return $group->sortByDesc('updated_at')->sortByDesc('created_at')->first();
            });

        // Charger les zones avec géométrie et filtrer sur Sandiara
        $bbox = self::getSandiaraBbox();
        $zones = ZoneFiscale::whereNotNull('geometrie')->get(['id', 'nom', 'geometrie'])
            ->filter(function ($z) use ($bbox) {
                $geom = json_decode($z->geometrie, true);
                $zb = self::bboxFromGeometry($geom);
                return self::bboxIntersects($zb, $bbox);
            })->values();

        $features = [];
        foreach ($zones as $z) {
            $pop = $rows->get($z->id);
            $features[] = [
                'type' => 'Feature',
                'geometry' => json_decode($z->geometrie),
                'properties' => [
                    'zone_id' => $z->id,
                    'nom' => $z->nom,
                    'population' => $pop ? (int) $pop->population : null,
                    'annee' => $pop ? $pop->annee : null,
                ],
            ];
        }

        return response()->json(['features' => $features]);
    }

    /**
     * Heatmap populationnelle approximative par centroïdes de zones.
     * Retourne des points [lat, lng, intensité] normalisés 0-1, limités à Sandiara.
     */
    public function heatmap(Request $request)
    {
        // Population la plus récente par zone
        $popPerZone = PopulationZone::query()
            ->select('zone_id', 'population', 'annee', 'updated_at', 'created_at')
            ->orderByRaw('COALESCE(annee, 0) DESC')
            ->get()
            ->groupBy('zone_id')
            ->map(function ($group) {
                $byYear = $group->filter(fn($r) => !is_null($r->annee));
                if ($byYear->count()) {
                    $maxYear = $byYear->max('annee');
                    return $byYear->firstWhere('annee', $maxYear);
                }
                return $group->sortByDesc('updated_at')->sortByDesc('created_at')->first();
            });

        $bbox = self::getSandiaraBbox();
        $zones = ZoneFiscale::whereNotNull('geometrie')->get(['id', 'nom', 'geometrie'])
            ->filter(function ($z) use ($bbox) {
                $geom = json_decode($z->geometrie, true);
                $zb = self::bboxFromGeometry($geom);
                return self::bboxIntersects($zb, $bbox);
            })->values();

        // Trouver le max pour normalisation
        $maxPop = 0;
        foreach ($zones as $z) {
            $p = $popPerZone->get($z->id);
            $val = $p ? max(0, (int)$p->population) : 0;
            if ($val > $maxPop) $maxPop = $val;
        }
        if ($maxPop <= 0) $maxPop = 1; // éviter division par zéro

        $points = [];
        foreach ($zones as $z) {
            $geom = json_decode($z->geometrie, true);
            $zb = self::bboxFromGeometry($geom);
            [$lat, $lng] = self::centroidFromBbox($zb);
            $p = $popPerZone->get($z->id);
            $val = $p ? max(0, (int)$p->population) : 0;
            $intensity = $val / $maxPop; // 0..1
            $points[] = [$lat, $lng, round($intensity, 4)];
        }

        return response()->json(['points' => $points]);
    }

    /**
     * Données voirie/routes (LineString GeoJSON). Placeholder.
     */
    public function roads(Request $request)
    {
        $bbox = self::getSandiaraBbox();
        $features = RouteVoirie::query()
            ->select(['id', 'nom', 'importance', 'geometrie'])
            ->get()
            ->map(function ($r) {
                return [
                    'type' => 'Feature',
                    'geometry' => is_array($r->geometrie) ? $r->geometrie : json_decode($r->geometrie, true),
                    'properties' => [
                        'id' => $r->id,
                        'nom' => $r->nom,
                        'importance' => $r->importance,
                    ],
                ];
            })
            ->filter(function ($f) use ($bbox) {
                $b = self::bboxFromGeometry($f['geometry']);
                return self::bboxIntersects($b, $bbox);
            })
            ->values();

        return response()->json(['features' => $features]);
    }

    /**
     * Données bâti (Polygon GeoJSON). Placeholder.
     */
    public function bati(Request $request)
    {
        $bbox = self::getSandiaraBbox();
        $features = Batiment::query()
            ->select(['id', 'nom', 'usage', 'surface_m2', 'geometrie'])
            ->get()
            ->map(function ($b) {
                return [
                    'type' => 'Feature',
                    'geometry' => is_array($b->geometrie) ? $b->geometrie : json_decode($b->geometrie, true),
                    'properties' => [
                        'id' => $b->id,
                        'nom' => $b->nom,
                        'usage' => $b->usage,
                        'surface_m2' => $b->surface_m2,
                    ],
                ];
            })
            ->filter(function ($f) use ($bbox) {
                $b = self::bboxFromGeometry($f['geometry']);
                return self::bboxIntersects($b, $bbox);
            })
            ->values();

        return response()->json(['features' => $features]);
    }

    /**
     * Recommandations d'investissement par zone. Prototype basé sur densité commerces.
     */
    public function recommendations(Request $request)
    {
        // Score basé sur rareté d'unités, densité par habitant (units / 1000 hab.)
        // et proximité d'axes principaux (routes "primary") via une approximation légère.
        $natureActiviteId = $request->input('nature_activite_id');
        $typeCommerceId = $request->input('type_commerce_id');

        $zones = ZoneFiscale::whereNotNull('geometrie')->get(['id', 'nom']);

        $query = UniteCommerciale::query()
            ->select('zone_id', DB::raw('count(*) as total'))
            ->where('actif', true)
            ->groupBy('zone_id');

        if ($typeCommerceId) {
            $type = TypeCommerce::find($typeCommerceId);
            if ($type) {
                $query->where('type_commerce', $type->nom);
            }
        } elseif ($natureActiviteId) {
            $types = TypeCommerce::where('nature_activite_id', $natureActiviteId)->pluck('nom');
            $query->whereIn('type_commerce', $types);
        }

        $comptesParZone = $query->pluck('total', 'zone_id');

        // Population la plus récente par zone
        $popMap = PopulationZone::query()
            ->select('zone_id', 'population', 'annee', 'updated_at', 'created_at')
            ->orderByRaw('COALESCE(annee, 0) DESC')
            ->get()
            ->groupBy('zone_id')
            ->map(function ($group) {
                $byYear = $group->filter(fn($r) => !is_null($r->annee));
                if ($byYear->count()) {
                    $maxYear = $byYear->max('annee');
                    return $byYear->firstWhere('annee', $maxYear);
                }
                return $group->sortByDesc('updated_at')->sortByDesc('created_at')->first();
            });

        // Préparer métriques pour normalisation
        $totals = $comptesParZone->values()->all();
        $minUnits = count($totals) ? min($totals) : 0;
        $maxUnits = count($totals) ? max($totals) : 1;
        $rangeUnits = max(1, $maxUnits - $minUnits);

        // Calculer units per 1k habitants, puis normaliser l'inverse (moins d'unités par 1k => score plus haut)
        $uPer1kList = [];
        foreach ($zones as $z) {
            $count = (int) $comptesParZone->get($z->id, 0);
            $pop = $popMap->get($z->id);
            $p = $pop ? max(0, (int) $pop->population) : 0;
            $uPer1k = $p > 0 ? ($count / max(1, $p) * 1000) : null; // null si pas de population
            if (!is_null($uPer1k)) $uPer1kList[] = $uPer1k;
        }
        $minUPer1k = count($uPer1kList) ? min($uPer1kList) : 0.0;
        $maxUPer1k = count($uPer1kList) ? max($uPer1kList) : 1.0;
        $rangeUPer1k = max(0.0001, $maxUPer1k - $minUPer1k);

        // ===== Proximité axes principaux (approx. bbox) =====
        // Récupérer bboxes des routes primary
        $primaryRoads = RouteVoirie::where('importance', 'primary')->get(['geometrie']);
        $roadBBoxes = [];
        foreach ($primaryRoads as $r) {
            $geom = is_array($r->geometrie) ? $r->geometrie : json_decode($r->geometrie, true);
            if (!$geom) continue;
            $roadBBoxes[] = self::bboxFromGeometry($geom);
        }

        // Pré-compute bbox par zone et compter intersections de bbox (proxy proximité)
        $zoneBBoxes = [];
        $proxCounts = [];
        foreach ($zones as $z) {
            $zoneGeom = json_decode(ZoneFiscale::find($z->id)->geometrie, true);
            $zb = self::bboxFromGeometry($zoneGeom);
            $zoneBBoxes[$z->id] = $zb;
            $cnt = 0;
            foreach ($roadBBoxes as $rb) {
                if (self::bboxIntersects($zb, $rb)) $cnt++;
            }
            $proxCounts[$z->id] = $cnt;
        }
        $allCnt = array_values($proxCounts);
        $minProx = count($allCnt) ? min($allCnt) : 0;
        $maxProx = count($allCnt) ? max($allCnt) : 1;
        $rangeProx = max(1, $maxProx - $minProx);

        $items = $zones->map(function ($z) use ($comptesParZone, $popMap, $minUnits, $rangeUnits, $minUPer1k, $rangeUPer1k, $proxCounts, $minProx, $rangeProx) {
            $count = (int) $comptesParZone->get($z->id, 0);
            $pop = $popMap->get($z->id);
            $p = $pop ? max(0, (int) $pop->population) : 0;
            $uPer1k = $p > 0 ? ($count / max(1, $p) * 1000) : null;

            // 1) composante rareté (moins d'unités => score +)
            $scarcity = 1 - (($count - $minUnits) / $rangeUnits);
            // 2) composante déficit par habitant (moins d'unités/1k => score +)
            $deficit = is_null($uPer1k) ? 0.5 : 1 - (($uPer1k - $minUPer1k) / $rangeUPer1k);
            // 3) composante proximité axes (plus d'intersections bbox => score +)
            $pc = $proxCounts[$z->id] ?? 0;
            $proximity = ($pc - $minProx) / $rangeProx;

            // Score global (pondérations): 0.35 rareté, 0.45 déficit/pop, 0.20 proximité axes
            $score = round((0.35 * $scarcity + 0.45 * $deficit + 0.20 * $proximity) * 100, 1);

            $reasonParts = [];
            if ($p > 0) {
                $reasonParts[] = 'Population: ' . number_format($p, 0, ',', ' ');
                $reasonParts[] = 'Unités/1000 hab.: ' . number_format(($uPer1k ?? 0), 2, ',', ' ');
            } else {
                $reasonParts[] = 'Population indisponible';
            }
            if ($count <= ($minUnits + $rangeUnits * 0.2)) {
                $reasonParts[] = 'Faible densité commerciale';
            }
            if ($proximity >= 0.6) {
                $reasonParts[] = 'Bonne accessibilité axes principaux';
            }

            return [
                'zone_id' => $z->id,
                'zone' => $z->nom,
                'unites' => $count,
                'population' => $p ?: null,
                'units_per_1000' => is_null($uPer1k) ? null : round($uPer1k, 4),
                'score' => $score,
                'proximity_axes_score' => round($proximity * 100, 1),
                'reason' => implode(' · ', $reasonParts),
            ];
        })->sortByDesc('score')->values();

        return response()->json(['data' => $items]);
    }

    /**
     * BBox approximative de la commune de Sandiara [minLng, minLat, maxLng, maxLat].
     */
    private static function getSandiaraBbox(): array
    {
        // Doit correspondre aux bornes utilisées côté front (Leaflet setMaxBounds)
        return [-16.805, 14.425, -16.775, 14.455];
    }

    /**
     * Carte de densité combinée (population x bâti x accessibilité axes) sur grille ~300-500m.
     * Retourne { features: [ { type, geometry, properties: { score, pop, bati, access } } ] }
     */
    public function combinedDensity(Request $request)
    {
        $bbox = self::getSandiaraBbox();

        // 1) Préparer population par zone (valeur brute)
        $popPerZone = PopulationZone::query()
            ->select('zone_id', 'population', 'annee', 'updated_at', 'created_at')
            ->orderByRaw('COALESCE(annee, 0) DESC')
            ->get()
            ->groupBy('zone_id')
            ->map(function ($group) {
                $byYear = $group->filter(fn($r) => !is_null($r->annee));
                if ($byYear->count()) {
                    $maxYear = $byYear->max('annee');
                    return $byYear->firstWhere('annee', $maxYear);
                }
                return $group->sortByDesc('updated_at')->sortByDesc('created_at')->first();
            });

        $zones = ZoneFiscale::whereNotNull('geometrie')->get(['id', 'geometrie']);
        $zoneBoxes = [];
        foreach ($zones as $z) {
            $geom = json_decode($z->geometrie, true);
            $zb = self::bboxFromGeometry($geom);
            if (self::bboxIntersects($zb, $bbox)) {
                $zoneBoxes[$z->id] = $zb;
            }
        }

        // 2) Bâti: bboxes de bâtiments (compte par cellule)
        $batiments = Batiment::select(['id', 'geometrie'])->get();
        $batBoxes = [];
        foreach ($batiments as $b) {
            $geom = is_array($b->geometrie) ? $b->geometrie : json_decode($b->geometrie, true);
            if (!$geom) continue;
            $bb = self::bboxFromGeometry($geom);
            if (self::bboxIntersects($bb, $bbox)) $batBoxes[] = $bb;
        }

        // 3) Routes primary: bboxes
        $primaryRoads = RouteVoirie::where('importance', 'primary')->get(['geometrie']);
        $roadBoxes = [];
        foreach ($primaryRoads as $r) {
            $geom = is_array($r->geometrie) ? $r->geometrie : json_decode($r->geometrie, true);
            if (!$geom) continue;
            $rb = self::bboxFromGeometry($geom);
            if (self::bboxIntersects($rb, $bbox)) $roadBoxes[] = $rb;
        }

        // 4) Générer une grille ~0.003° (≈300m) sur la bbox
        $cells = $this->generateGrid($bbox, 0.003, 0.003);

        // 5) Calculer métriques par cellule
        $scores = [];
        $popVals = [];
        $batiVals = [];
        $accessVals = [];

        foreach ($cells as $cell) {
            $cb = $cell['bbox']; // [minLng, minLat, maxLng, maxLat]

            // Population: somme des populations des zones dont la bbox intersecte la cellule (proxy simple)
            $pop = 0;
            foreach ($zoneBoxes as $zoneId => $zb) {
                if (self::bboxIntersects($cb, $zb)) {
                    $p = $popPerZone->get($zoneId);
                    $pop += $p ? max(0, (int)$p->population) : 0;
                }
            }

            // Bâti: nombre de bâtiments intersectant la cellule
            $batiCount = 0;
            foreach ($batBoxes as $bb) {
                if (self::bboxIntersects($cb, $bb)) $batiCount++;
            }

            // Accessibilité: nombre de segments primary intersectant la cellule
            $accessCount = 0;
            foreach ($roadBoxes as $rb) {
                if (self::bboxIntersects($cb, $rb)) $accessCount++;
            }

            $popVals[] = $pop;
            $batiVals[] = $batiCount;
            $accessVals[] = $accessCount;

            $scores[] = [
                'cell' => $cell,
                'pop' => $pop,
                'bati' => $batiCount,
                'access' => $accessCount,
            ];
        }

        $maxPop = max(1, max($popVals));
        $maxBati = max(1, max($batiVals));
        $maxAccess = max(1, max($accessVals));

        // Pondérations
        $wPop = 0.45; $wBati = 0.35; $wAccess = 0.20;

        $features = [];
        foreach ($scores as $s) {
            $pN = $s['pop'] / $maxPop;      // 0..1
            $bN = $s['bati'] / $maxBati;    // 0..1
            $aN = $s['access'] / $maxAccess;// 0..1
            $score = round(($wPop * $pN + $wBati * $bN + $wAccess * $aN) * 100, 1);

            $poly = $this->bboxToPolygon($s['cell']['bbox']);
            $features[] = [
                'type' => 'Feature',
                'geometry' => $poly,
                'properties' => [
                    'score' => $score,
                    'pop' => round($pN * 100, 1),
                    'bati' => round($bN * 100, 1),
                    'access' => round($aN * 100, 1),
                ]
            ];
        }

        return response()->json(['features' => $features]);
    }

    /**
     * Calcule la bounding box [minLng, minLat, maxLng, maxLat] d'une géométrie GeoJSON.
     * Accepte Geometry, Feature ou FeatureCollection (approx.).
     */
    private static function bboxFromGeometry($geom): array
    {
        if (!$geom) return [0,0,0,0];
        if (isset($geom['type']) && strtolower($geom['type']) === 'feature') {
            return self::bboxFromGeometry($geom['geometry'] ?? null);
        }
        if (isset($geom['type']) && strtolower($geom['type']) === 'featurecollection') {
            $bbox = null;
            foreach (($geom['features'] ?? []) as $f) {
                $b = self::bboxFromGeometry($f['geometry'] ?? null);
                $bbox = is_null($bbox) ? $b : [
                    min($bbox[0], $b[0]), min($bbox[1], $b[1]),
                    max($bbox[2], $b[2]), max($bbox[3], $b[3])
                ];
            }
            return $bbox ?: [0,0,0,0];
        }

        $type = strtolower($geom['type'] ?? '');
        $coords = $geom['coordinates'] ?? [];

        $minLng = INF; $minLat = INF; $maxLng = -INF; $maxLat = -INF;

        $walk = function($c) use (&$minLng, &$minLat, &$maxLng, &$maxLat, &$walk) {
            if (!is_array($c)) return;
            if (isset($c[0]) && is_numeric($c[0]) && isset($c[1]) && is_numeric($c[1])) {
                $lng = (float)$c[0];
                $lat = (float)$c[1];
                $minLng = min($minLng, $lng);
                $minLat = min($minLat, $lat);
                $maxLng = max($maxLng, $lng);
                $maxLat = max($maxLat, $lat);
                return;
            }
            foreach ($c as $cc) { $walk($cc); }
        };

        switch ($type) {
            case 'point':
            case 'multipoint':
            case 'linestring':
            case 'multilinestring':
            case 'polygon':
            case 'multipolygon':
                $walk($coords);
                break;
            default:
                return [0,0,0,0];
        }

        if ($minLng === INF) return [0,0,0,0];
        return [$minLng, $minLat, $maxLng, $maxLat];
    }

    /**
     * Teste l'intersection de deux bbox [minLng, minLat, maxLng, maxLat].
     */
    private static function bboxIntersects(array $a, array $b): bool
    {
        return !(
            $a[2] < $b[0] || // a.maxLng < b.minLng
            $a[0] > $b[2] || // a.minLng > b.maxLng
            $a[3] < $b[1] || // a.maxLat < b.minLat
            $a[1] > $b[3]    // a.minLat > b.maxLat
        );
    }

    /**
     * Centroïde simple à partir d'une bbox [minLng, minLat, maxLng, maxLat] -> [lat, lng]
     */
    private static function centroidFromBbox(array $bbox): array
    {
        $lng = ($bbox[0] + $bbox[2]) / 2.0;
        $lat = ($bbox[1] + $bbox[3]) / 2.0;
        return [$lat, $lng];
    }

    /**
     * Génère une grille de bboxes sur une bbox parent avec pas (deg) en lat/lng.
     * Retourne [{ bbox: [minLng,minLat,maxLng,maxLat] }]
     */
    private function generateGrid(array $bbox, float $stepLat, float $stepLng): array
    {
        [$minLng, $minLat, $maxLng, $maxLat] = $bbox;
        $cells = [];
        for ($lat = $minLat; $lat < $maxLat; $lat += $stepLat) {
            $lat2 = min($maxLat, $lat + $stepLat);
            for ($lng = $minLng; $lng < $maxLng; $lng += $stepLng) {
                $lng2 = min($maxLng, $lng + $stepLng);
                $cells[] = ['bbox' => [$lng, $lat, $lng2, $lat2]];
            }
        }
        return $cells;
    }

    /**
     * Convertit une bbox en polygon GeoJSON.
     */
    private function bboxToPolygon(array $bbox): array
    {
        [$minLng, $minLat, $maxLng, $maxLat] = $bbox;
        return [
            'type' => 'Polygon',
            'coordinates' => [[
                [$minLng, $minLat],
                [$maxLng, $minLat],
                [$maxLng, $maxLat],
                [$minLng, $maxLat],
                [$minLng, $minLat]
            ]]
        ];
    }
}
