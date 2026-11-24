<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\UniteCommerciale;
use App\Models\Paiement;
use App\Models\User;
use App\Models\ZoneFiscale;
use App\Models\Taxe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CommuneController extends Controller
{
    /**
     * Tableau de bord multi-communes : vue globale de toutes les communes et données.
     */
    public function index(Request $request)
    {
        $communes = Commune::where('actif', true)
            ->withCount([
                'unitesCommerciales' => fn ($q) => $q->withoutGlobalScopes(),
                'users',
                'zonesFiscales',
                'taxes',
            ])
            ->withSum(['paiements as total_recettes' => function ($q) {
                $q->withoutGlobalScopes()->where('statut', 'valide');
            }], 'montant')
            ->orderBy('nom')
            ->get();

        // Agrégats globaux (sans scope pour vue admin nationale)
        $stats = [
            'total_communes' => Commune::where('actif', true)->count(),
            'total_unites' => UniteCommerciale::withoutGlobalScopes()->count(),
            'total_paiements' => Paiement::withoutGlobalScopes()->where('statut', 'valide')->count(),
            'total_recettes' => Paiement::withoutGlobalScopes()->where('statut', 'valide')->sum('montant'),
            'total_collecteurs' => User::where('role', 'collecteur')->count(),
            'total_gestionnaires' => User::where('role', 'gestionnaire')->count(),
            'total_maires' => User::where('role', 'maire')->count(),
        ];

        // --- Données dynamiques (charts + carte) ---
        $generationDate = now();

        // Couleurs par commune (pour chart + carte)
        $palette = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316', '#64748b'];
        $communeColors = [];
        foreach ($communes as $i => $c) {
            $communeColors[$c->id] = $palette[$i % count($palette)];
        }

        // Chart 1: Recettes mensuelles (12 derniers mois)
        $startChart = now()->subMonths(11)->startOfMonth();
        $endChart = now()->endOfMonth();
        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'pgsql'
            ? "to_char(date_paiement, 'YYYY-MM')"
            : "DATE_FORMAT(date_paiement, '%Y-%m')";

        $recettesByMonth = Paiement::withoutGlobalScopes()
            ->where('statut', 'valide')
            ->whereBetween('date_paiement', [$startChart, $endChart])
            ->selectRaw($monthExpr . ' as periode, COALESCE(SUM(montant),0) as total')
            ->groupBy('periode')
            ->orderBy('periode')
            ->get()
            ->keyBy('periode');

        $monthlyLabels = [];
        $monthlyTotals = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $key = $d->format('Y-m');
            $monthlyLabels[] = $d->format('M Y');
            $monthlyTotals[] = (float) (($recettesByMonth[$key]->total ?? 0) ?: 0);
        }

        // Chart 2: Recettes par commune
        $byCommuneLabels = $communes->pluck('nom')->values()->all();
        $byCommuneTotals = $communes->map(fn ($c) => (float) ($c->total_recettes ?? 0))->values()->all();
        $byCommuneColors = $communes->map(fn ($c) => $communeColors[$c->id] ?? '#64748b')->values()->all();

        // Chart 3: Répartition utilisateurs
        $usersLabels = ['Maires', 'Gestionnaires', 'Collecteurs'];
        $usersTotals = [
            (int) ($stats['total_maires'] ?? 0),
            (int) ($stats['total_gestionnaires'] ?? 0),
            (int) ($stats['total_collecteurs'] ?? 0),
        ];

        $charts = [
            'monthly' => [
                'labels' => $monthlyLabels,
                'totals' => $monthlyTotals,
            ],
            'by_commune' => [
                'labels' => $byCommuneLabels,
                'totals' => $byCommuneTotals,
                'colors' => $byCommuneColors,
            ],
            'users' => [
                'labels' => $usersLabels,
                'totals' => $usersTotals,
            ],
        ];

        // Carte: GeoJSON des zones (affichées par couleur de commune)
        $communeNames = $communes->pluck('nom', 'id')->all();
        $zones = ZoneFiscale::query()
            ->whereNotNull('geometrie')
            ->get(['id', 'nom', 'commune_id', 'geometrie']);

        $zonesGeoJson = [
            'type' => 'FeatureCollection',
            'features' => $zones
                ->filter(function ($z) {
                    return is_array($z->geometrie)
                        && !empty($z->geometrie['type'])
                        && !empty($z->geometrie['coordinates']);
                })
                ->map(function ($z) use ($communeNames, $communeColors) {
                    $communeId = $z->commune_id;
                    $communeNom = $communeId ? ($communeNames[$communeId] ?? '—') : 'Sans commune';
                    return [
                        'type' => 'Feature',
                        'properties' => [
                            'id' => $z->id,
                            'nom' => $z->nom,
                            'commune_id' => $communeId,
                            'commune' => $communeNom,
                            'color' => $communeId ? ($communeColors[$communeId] ?? '#64748b') : '#64748b',
                        ],
                        'geometry' => $z->geometrie,
                    ];
                })
                ->values()
                ->all(),
        ];

        // Centres des communes (approx: centre du bbox des zones)
        $flattenPoints = function ($coordinates) {
            $points = [];
            $stack = [$coordinates];
            while (!empty($stack)) {
                $current = array_pop($stack);
                if (is_array($current) && count($current) === 2 && is_numeric($current[0]) && is_numeric($current[1])) {
                    $points[] = [(float) $current[0], (float) $current[1]]; // [lng, lat]
                    continue;
                }
                if (is_array($current)) {
                    foreach ($current as $item) {
                        $stack[] = $item;
                    }
                }
            }
            return $points;
        };

        $communeBBoxes = [];
        foreach ($zones as $z) {
            $communeId = (int) ($z->commune_id ?? 0);
            if ($communeId <= 0 || !is_array($z->geometrie) || empty($z->geometrie['coordinates'])) {
                continue;
            }
            $points = $flattenPoints($z->geometrie['coordinates']);
            if (empty($points)) {
                continue;
            }
            if (!isset($communeBBoxes[$communeId])) {
                $communeBBoxes[$communeId] = [
                    'minLng' => null, 'maxLng' => null,
                    'minLat' => null, 'maxLat' => null,
                ];
            }
            foreach ($points as [$lng, $lat]) {
                $communeBBoxes[$communeId]['minLng'] = $communeBBoxes[$communeId]['minLng'] === null ? $lng : min($communeBBoxes[$communeId]['minLng'], $lng);
                $communeBBoxes[$communeId]['maxLng'] = $communeBBoxes[$communeId]['maxLng'] === null ? $lng : max($communeBBoxes[$communeId]['maxLng'], $lng);
                $communeBBoxes[$communeId]['minLat'] = $communeBBoxes[$communeId]['minLat'] === null ? $lat : min($communeBBoxes[$communeId]['minLat'], $lat);
                $communeBBoxes[$communeId]['maxLat'] = $communeBBoxes[$communeId]['maxLat'] === null ? $lat : max($communeBBoxes[$communeId]['maxLat'], $lat);
            }
        }

        $communesMap = $communes->map(function ($c) use ($communeColors, $communeBBoxes) {
            $bbox = $communeBBoxes[$c->id] ?? null;
            $center = null;
            if ($bbox && $bbox['minLat'] !== null) {
                $center = [
                    'lat' => ($bbox['minLat'] + $bbox['maxLat']) / 2,
                    'lng' => ($bbox['minLng'] + $bbox['maxLng']) / 2,
                ];
            }

            return [
                'id' => (int) $c->id,
                'nom' => (string) $c->nom,
                'code' => (string) ($c->code ?? ''),
                'region' => (string) ($c->region ?? ''),
                'departement' => (string) ($c->departement ?? ''),
                'color' => $communeColors[$c->id] ?? '#64748b',
                'center' => $center,
                'zones_count' => (int) ($c->zones_fiscales_count ?? 0),
                'unites_count' => (int) ($c->unites_commerciales_count ?? 0),
                'users_count' => (int) ($c->users_count ?? 0),
                'total_recettes' => (float) ($c->total_recettes ?? 0),
            ];
        })->values()->all();

        // Insights simples
        $topCommune = $communes->sortByDesc(fn ($c) => (float) ($c->total_recettes ?? 0))->first();
        $avgParPaiement = ($stats['total_paiements'] ?? 0) > 0
            ? ((float) $stats['total_recettes'] / (float) $stats['total_paiements'])
            : 0;
        $avgParUnite = ($stats['total_unites'] ?? 0) > 0
            ? ((float) $stats['total_recettes'] / (float) $stats['total_unites'])
            : 0;

        $insights = [
            'top_commune_nom' => $topCommune?->nom,
            'top_commune_recettes' => (float) ($topCommune?->total_recettes ?? 0),
            'avg_recette_par_paiement' => $avgParPaiement,
            'avg_recette_par_unite' => $avgParUnite,
        ];

        return view('communes.index', compact('communes', 'stats', 'generationDate', 'charts', 'zonesGeoJson', 'communesMap', 'insights'));
    }

    /**
     * Carte multi-communes : visualiser les zones (limites) par commune.
     */
    public function map(Request $request)
    {
        $communes = Commune::where('actif', true)
            ->withCount([
                'unitesCommerciales' => fn ($q) => $q->withoutGlobalScopes(),
                'users',
                'zonesFiscales',
                'taxes',
            ])
            ->withSum(['paiements as total_recettes' => function ($q) {
                $q->withoutGlobalScopes()->where('statut', 'valide');
            }], 'montant')
            ->orderBy('nom')
            ->get();

        $stats = [
            'total_communes' => Commune::where('actif', true)->count(),
            'total_unites' => UniteCommerciale::withoutGlobalScopes()->count(),
            'total_paiements' => Paiement::withoutGlobalScopes()->where('statut', 'valide')->count(),
            'total_recettes' => Paiement::withoutGlobalScopes()->where('statut', 'valide')->sum('montant'),
        ];

        $generationDate = now();

        // Couleurs par commune
        $palette = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316', '#64748b'];
        $communeColors = [];
        foreach ($communes as $i => $c) {
            $communeColors[$c->id] = $palette[$i % count($palette)];
        }

        // GeoJSON des zones fiscales (pour la couche carte par commune)
        $communeNames = $communes->pluck('nom', 'id')->all();
        $zones = ZoneFiscale::withoutGlobalScopes()
            ->whereNotNull('geometrie')
            ->get(['id', 'nom', 'commune_id', 'geometrie']);

        // Normaliser geometrie (parfois chaîne JSON ou objet)
        $zones->each(function ($z) {
            if (is_string($z->geometrie)) {
                $decoded = json_decode($z->geometrie, true);
                $z->geometrie = is_array($decoded) ? $decoded : null;
            }
        });

        $zonesGeoJson = [
            'type' => 'FeatureCollection',
            'features' => $zones
                ->filter(function ($z) {
                    $g = $z->geometrie;
                    return is_array($g)
                        && !empty($g['type'])
                        && !empty($g['coordinates']);
                })
                ->map(function ($z) use ($communeNames, $communeColors) {
                    $communeId = $z->commune_id;
                    $communeNom = $communeId ? ($communeNames[$communeId] ?? '—') : 'Sans commune';
                    return [
                        'type' => 'Feature',
                        'properties' => [
                            'id' => $z->id,
                            'nom' => $z->nom,
                            'commune_id' => $communeId,
                            'commune' => $communeNom,
                            'color' => $communeId ? ($communeColors[$communeId] ?? '#64748b') : '#64748b',
                        ],
                        'geometry' => $z->geometrie,
                    ];
                })
                ->values()
                ->all(),
        ];

        $flattenPoints = function ($coordinates) {
            $points = [];
            $stack = [$coordinates];
            while (!empty($stack)) {
                $current = array_pop($stack);
                if (is_array($current) && count($current) === 2 && is_numeric($current[0]) && is_numeric($current[1])) {
                    $points[] = [(float) $current[0], (float) $current[1]]; // [lng, lat]
                    continue;
                }
                if (is_array($current)) {
                    foreach ($current as $item) {
                        $stack[] = $item;
                    }
                }
            }
            return $points;
        };

        $communeBBoxes = [];
        foreach ($zones as $z) {
            $communeId = (int) ($z->commune_id ?? 0);
            if ($communeId <= 0 || !is_array($z->geometrie) || empty($z->geometrie['coordinates'])) {
                continue;
            }
            $points = $flattenPoints($z->geometrie['coordinates']);
            if (empty($points)) {
                continue;
            }
            if (!isset($communeBBoxes[$communeId])) {
                $communeBBoxes[$communeId] = [
                    'minLng' => null, 'maxLng' => null,
                    'minLat' => null, 'maxLat' => null,
                ];
            }
            foreach ($points as [$lng, $lat]) {
                $communeBBoxes[$communeId]['minLng'] = $communeBBoxes[$communeId]['minLng'] === null ? $lng : min($communeBBoxes[$communeId]['minLng'], $lng);
                $communeBBoxes[$communeId]['maxLng'] = $communeBBoxes[$communeId]['maxLng'] === null ? $lng : max($communeBBoxes[$communeId]['maxLng'], $lng);
                $communeBBoxes[$communeId]['minLat'] = $communeBBoxes[$communeId]['minLat'] === null ? $lat : min($communeBBoxes[$communeId]['minLat'], $lat);
                $communeBBoxes[$communeId]['maxLat'] = $communeBBoxes[$communeId]['maxLat'] === null ? $lat : max($communeBBoxes[$communeId]['maxLat'], $lat);
            }
        }

        $communesMap = $communes->map(function ($c) use ($communeColors, $communeBBoxes) {
            $bbox = $communeBBoxes[$c->id] ?? null;
            $center = null;
            if ($bbox && $bbox['minLat'] !== null) {
                $center = [
                    'lat' => ($bbox['minLat'] + $bbox['maxLat']) / 2,
                    'lng' => ($bbox['minLng'] + $bbox['maxLng']) / 2,
                ];
            }
            // Si la commune n'a pas de zones avec géométrie, pas de centre : la carte utilisera la vue par défaut (Sénégal)
            return [
                'id' => (int) $c->id,
                'nom' => (string) $c->nom,
                'color' => $communeColors[$c->id] ?? '#64748b',
                'center' => $center,
                'zones_count' => (int) ($c->zones_fiscales_count ?? 0),
                'unites_count' => (int) ($c->unites_commerciales_count ?? 0),
                'users_count' => (int) ($c->users_count ?? 0),
                'total_recettes' => (float) ($c->total_recettes ?? 0),
            ];
        })->values()->all();

        // Unités géolocalisées (points, sans scope pour admin national)
        $unites = UniteCommerciale::withoutGlobalScopes()
            ->where('actif', true)
            ->where(function ($q) {
                $q->whereNotNull('coordinates')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('latitude')->whereNotNull('longitude');
                    });
            })
            ->with([
                'zone:id,nom',
                'commune:id,nom',
            ])
            ->get([
                'id',
                'nom_proprietaire',
                'nature',
                'type_commerce',
                'categorie',
                'zone_id',
                'commune_id',
                'coordinates',
                'latitude',
                'longitude',
            ]);

        $unitesPoints = $unites
            ->map(function ($u) {
                $lat = null;
                $lng = null;
                $coords = $u->coordinates;
                if (is_array($coords)) {
                    if (isset($coords['lat'], $coords['lng'])) {
                        $lat = (float) $coords['lat'];
                        $lng = (float) $coords['lng'];
                    } elseif (isset($coords['latitude'], $coords['longitude'])) {
                        $lat = (float) $coords['latitude'];
                        $lng = (float) $coords['longitude'];
                    } elseif (isset($coords[0], $coords[1]) && is_numeric($coords[0]) && is_numeric($coords[1])) {
                        // GeoJSON [lng, lat]
                        $lng = (float) $coords[0];
                        $lat = (float) $coords[1];
                    }
                }
                if ($lat === null && $u->latitude && $u->longitude) {
                    $lat = (float) $u->latitude;
                    $lng = (float) $u->longitude;
                }
                if ($lat === null || $lng === null) {
                    return null;
                }
                return [
                    'id' => (int) $u->id,
                    'nom' => (string) ($u->nom_proprietaire ?? ''),
                    'nature' => (string) ($u->nature ?? ''),
                    'type_commerce' => (string) ($u->type_commerce ?? ''),
                    'categorie' => (string) ($u->categorie ?? ''),
                    'zone_id' => (int) ($u->zone_id ?? 0),
                    'zone' => (string) ($u->zone?->nom ?? ''),
                    'commune_id' => (int) ($u->commune_id ?? 0),
                    'commune' => (string) ($u->commune?->nom ?? ''),
                    'lat' => $lat,
                    'lng' => $lng,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $stats['unites_geolocalisees'] = count($unitesPoints);

        return view('communes.map', compact('communes', 'stats', 'generationDate', 'communesMap', 'unitesPoints', 'zonesGeoJson'));
    }

    /**
     * Liste publique des communes pour « Accéder par commune » (sans authentification).
     */
    public function listForPublic()
    {
        $communes = Commune::where('actif', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'code', 'acronyme', 'logo', 'region']);
        return view('communes.list-public', compact('communes'));
    }

    /**
     * Portail de connexion par commune : affiche le logo de la commune et des liens de connexion distincts (Maire, Gestionnaire, Collecteur).
     */
    public function portal(string $code)
    {
        $commune = Commune::where('actif', true)
            ->where(function ($q) use ($code) {
                $q->where('code', $code);
                if (is_numeric($code)) {
                    $q->orWhere('id', (int) $code);
                }
            })
            ->firstOrFail();
        $maireLogin = url('/maire/login') . '?commune=' . urlencode($commune->code ?? $commune->id);
        $gestionnaireLogin = url('/gestionnaire/login') . '?commune=' . urlencode($commune->code ?? $commune->id);
        $collecteurLogin = url('/collecteur/login') . '?commune=' . urlencode($commune->code ?? $commune->id);
        return view('communes.portal', compact('commune', 'maireLogin', 'gestionnaireLogin', 'collecteurLogin'));
    }

    /**
     * Formulaire d'ajout d'une commune.
     */
    public function create()
    {
        return view('communes.create');
    }

    /**
     * Enregistrer une nouvelle commune.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:communes,code',
            'acronyme' => 'nullable|string|max:30',
            'region' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $nom = $request->nom;
        $code = $request->code ?: strtoupper(\Str::slug($nom, ''));
        $acronyme = $request->filled('acronyme') ? $request->acronyme : self::defaultAcronymeForNom($nom);

        $commune = Commune::create([
            'nom' => $nom,
            'code' => $code,
            'acronyme' => $acronyme,
            'region' => $request->region,
            'departement' => $request->departement,
            'actif' => true,
            'description' => $request->description,
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->storePublicly('communes/' . $commune->id, 'public');
            $commune->update(['logo' => $path]);
        }

        return redirect()->route('communes.index')
            ->with('success', "Commune « {$commune->nom} » créée. Vous pouvez y affecter des zones, taxes et utilisateurs (maire, gestionnaire, collecteurs).");
    }

    /**
     * Détail d'une commune (données agrégées).
     */
    public function show(Commune $commune)
    {
        $commune->loadCount([
            'unitesCommerciales',
            'users',
            'zonesFiscales',
            'taxes',
        ]);
        $commune->loadSum(['paiements as total_recettes' => function ($q) {
            $q->where('statut', 'valide');
        }], 'montant');

        $maires = $commune->maires()->get();
        $gestionnaires = $commune->gestionnaires()->get();
        $collecteurs = $commune->collecteurs()->with('zone')->get();

        return view('communes.show', compact('commune', 'maires', 'gestionnaires', 'collecteurs'));
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(Commune $commune)
    {
        return view('communes.edit', compact('commune'));
    }

    /**
     * Servir le logo de la commune (évite de dépendre du lien symbolique storage).
     */
    public function logo(Commune $commune)
    {
        if (empty($commune->logo)) {
            abort(404);
        }
        $path = str_replace('\\', '/', ltrim($commune->logo, '/'));
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }
        $fullPath = Storage::disk('public')->path($path);
        $mime = mime_content_type($fullPath) ?: 'image/png';
        return response()->file($fullPath, ['Content-Type' => $mime]);
    }

    /**
     * Mise à jour d'une commune.
     */
    public function update(Request $request, Commune $commune)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:communes,code,' . $commune->id,
            'acronyme' => 'nullable|string|max:30',
            'region' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'actif' => 'boolean',
            'description' => 'nullable|string|max:5000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = [
            'nom' => $request->nom,
            'code' => $request->code ?: $commune->code,
            'acronyme' => $request->filled('acronyme') ? $request->acronyme : self::defaultAcronymeForNom($request->nom),
            'region' => $request->region,
            'departement' => $request->departement,
            'actif' => $request->boolean('actif'),
            'description' => $request->description,
        ];

        if ($request->hasFile('logo')) {
            if ($commune->logo && Storage::disk('public')->exists($commune->logo)) {
                Storage::disk('public')->delete($commune->logo);
            }
            $path = $request->file('logo')->storePublicly('communes/' . $commune->id, 'public');
            $data['logo'] = $path;
        }

        $commune->update($data);

        return redirect()->route('communes.index')
            ->with('success', "Commune « {$commune->nom} » mise à jour.");
    }

    /**
     * Formulaire : créer un utilisateur (Maire, Gestionnaire ou Collecteur) pour cette commune.
     */
    public function createUserForm(Commune $commune)
    {
        $zones = $commune->zonesFiscales()->orderBy('nom')->get();
        return view('communes.create-user', compact('commune', 'zones'));
    }

    /**
     * Enregistrer un nouvel utilisateur pour la commune.
     */
    public function storeUser(Request $request, Commune $commune)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'identifiant' => 'required|string|max:255|unique:users,identifiant',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:maire,gestionnaire,collecteur',
        ];
        if ($request->role === 'collecteur') {
            $rules['zone_id'] = 'nullable|exists:zones_fiscales,id';
        }
        $messages = [
            'email.unique' => 'Cet email est déjà utilisé par un autre utilisateur. Choisissez un autre email ou laissez vide.',
        ];
        $validated = $request->validate($rules, $messages);

        $user = User::withoutGlobalScopes()->create([
            'name' => $validated['name'],
            'identifiant' => $validated['identifiant'],
            'email' => $validated['email'] ?? ($validated['identifiant'] . '@' . Str::slug($commune->nom) . '.sn'),
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'commune_id' => $commune->id,
            'zone_id' => $validated['zone_id'] ?? null,
        ]);

        $roleLabel = $validated['role'] === 'maire' ? 'Maire' : ($validated['role'] === 'gestionnaire' ? 'Gestionnaire' : 'Collecteur');
        return redirect()->route('communes.show', $commune)
            ->with('success', "Utilisateur « {$user->name} » ({$roleLabel}) créé. Il peut se connecter via Accéder par commune → {$commune->nom}.");
    }

    /**
     * Désactiver une commune (soft : actif = false).
     */
    public function destroy(Commune $commune)
    {
        $nom = $commune->nom;
        $commune->update(['actif' => false]);
        return redirect()->route('communes.index')
            ->with('success', "Commune « {$nom} » a été désactivée.");
    }

    /**
     * Activer un scope de commune (admin SICOFIL)
     */
    public function setScope(Commune $commune)
    {
        session([
            'commune_scope_id' => $commune->id,
            'active_commune_id' => $commune->id,
        ]);
        return redirect()->back()->with('success', "Scope activé: {$commune->nom}");
    }

    /**
     * Supprimer le scope de commune (admin SICOFIL)
     */
    public function clearScope()
    {
        session()->forget(['commune_scope_id', 'active_commune_id']);
        return redirect()->back()->with('success', "Scope multi-communes désactivé.");
    }

    /**
     * Génère un acronyme par défaut à partir du nom de la commune (ex. Sandiara → SICOSA, Thiès → SICOTH).
     */
    private static function defaultAcronymeForNom(string $nom): string
    {
        $letters = strtoupper(preg_replace('/[^a-zA-Z]/', '', $nom));
        $suffix = strlen($letters) >= 2 ? substr($letters, 0, 2) : ($letters ?: 'XX');
        return 'SICO' . $suffix;
    }
}
