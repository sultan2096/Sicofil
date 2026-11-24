<?php

namespace App\Http\Controllers;

use App\Models\ZoneFiscale;
use App\Models\Log;
use App\Services\GeoValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ZoneFiscaleController extends Controller
{
    private function currentCommuneId(): int
    {
        return (int) auth()->user()->commune_id;
    }

    private function ensureCanAccessZone(ZoneFiscale $zone): void
    {
        if ((int) $zone->commune_id !== $this->currentCommuneId()) {
            abort(403, 'Accès non autorisé à cette zone fiscale.');
        }
    }

    private function validateAndNormalizeGeometry($raw, GeoValidator $validator): ?array
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        $result = $validator->validate($raw);
        if (!$result['ok']) {
            abort(422, 'Géométrie invalide: ' . implode(' ', $result['errors']));
        }
        return $result['geometry'];
    }

    private function invalidateMapCache(int $communeId): void
    {
        Cache::forget('map.boundaries.zones.' . $communeId);
    }

    /**
     * Display a listing of the zones fiscales.
     */
    public function index()
    {
        // Gestionnaire et maire peuvent voir les zones fiscales
        if (!in_array(auth()->user()->role, ['gestionnaire', 'maire'])) {
            abort(403, 'Accès non autorisé.');
        }

        $zones = ZoneFiscale::withCount(['unitesCommerciales', 'collecteurs'])->latest()->paginate(10);
        
        return view('zones.index', compact('zones'));
    }

    /**
     * Show the form for creating a new zone fiscale.
     */
    public function create()
    {
        // Seul le gestionnaire peut créer des zones fiscales
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé. Seul le gestionnaire peut créer des zones fiscales.');
        }

        return view('zones.create');
    }

    /**
     * Store a newly created zone fiscale in storage.
     */
    public function store(Request $request, GeoValidator $validator)
    {
        // Seul le gestionnaire peut créer des zones fiscales
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé. Seul le gestionnaire peut créer des zones fiscales.');
        }

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('zones_fiscales', 'nom')->where('commune_id', $this->currentCommuneId()),
            ],
            'description' => 'nullable|string',
            'geometrie' => 'nullable|json',
        ]);

        $validated['commune_id'] = $this->currentCommuneId();
        $validated['geometrie'] = $this->validateAndNormalizeGeometry($validated['geometrie'] ?? null, $validator);

        $zone = ZoneFiscale::create($validated);
        $this->invalidateMapCache($zone->commune_id);

        Log::createLog('create_zone', [
            'zone_id' => $zone->id,
            'zone_nom' => $zone->nom
        ]);

        return redirect()->route('zones.index')
            ->with('success', 'Zone fiscale créée avec succès.');
    }

    /**
     * Display the specified zone fiscale.
     */
    public function show(ZoneFiscale $zone)
    {
        // Admin et maire peuvent voir les détails des zones fiscales
        if (!in_array(auth()->user()->role, ['admin', 'maire'])) {
            abort(403, 'Accès non autorisé.');
        }

        $this->ensureCanAccessZone($zone);

        $zone->load(['unitesCommerciales' => function($query) {
            $query->actif()->orderBy('nom_proprietaire')->orderBy('prenom_proprietaire');
        }, 'collecteurs']);

        // Statistiques de la zone
        $stats = [
            'total_unites' => $zone->unitesCommerciales->count(),
            'unites_actives' => $zone->unitesCommerciales->where('actif', true)->count(),
            'collecteurs' => $zone->collecteurs->count(),
            'recette_mois' => $zone->unitesCommerciales->sum(function($unite) {
                return $unite->paiements()
                    ->whereMonth('date_paiement', now()->month)
                    ->whereYear('date_paiement', now()->year)
                    ->valide()
                    ->sum('montant');
            })
        ];

        return view('zones.show', compact('zone', 'stats'));
    }

    /**
     * Show the form for editing the specified zone fiscale.
     */
    public function edit(ZoneFiscale $zone)
    {
        // Seul le gestionnaire peut modifier les zones fiscales
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé. Seul le gestionnaire peut modifier les zones fiscales.');
        }

        return view('zones.edit', compact('zone'));
    }

    /**
     * Update the specified zone fiscale in storage.
     */
    public function update(Request $request, ZoneFiscale $zone)
    {
        // Seul le gestionnaire peut modifier les zones fiscales
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé. Seul le gestionnaire peut modifier les zones fiscales.');
        }

        $this->ensureCanAccessZone($zone);

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('zones_fiscales', 'nom')->where('commune_id', $this->currentCommuneId())->ignore($zone->id),
            ],
            'description' => 'nullable|string',
            'geometrie' => 'nullable|json',
        ]);

        // IMPORTANT: Ne pas mettre à jour la géométrie depuis le formulaire d'édition
        // Les géométries doivent être synchronisées depuis PostGIS via la commande zones:sync-geometries
        // pour éviter toute désynchronisation avec les couches shapefiles affichées sur la carte
        // Les couches shapefiles sur /map proviennent directement de PostGIS et ne sont pas affectées
        // par les modifications dans zones_fiscales
        if (isset($validated['geometrie'])) {
            unset($validated['geometrie']);
        }

        $zone->update($validated);

        Log::createLog('update_zone', [
            'zone_id' => $zone->id,
            'zone_nom' => $zone->nom
        ]);

        return redirect()->route('zones.index')
            ->with('success', 'Zone fiscale mise à jour avec succès.');
    }

    /**
     * Remove the specified zone fiscale from storage.
     */
    public function destroy(ZoneFiscale $zone)
    {
        // Seul le gestionnaire peut supprimer les zones fiscales
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé. Seul le gestionnaire peut supprimer les zones fiscales.');
        }

        $this->ensureCanAccessZone($zone);

        // Détacher la zone des unités commerciales et des collecteurs avant suppression
        $zone->unitesCommerciales()->update(['zone_id' => null]);
        $zone->collecteurs()->update(['zone_id' => null]);

        Log::createLog('delete_zone', [
            'zone_id' => $zone->id,
            'zone_nom' => $zone->nom
        ]);

        $communeId = (int) $zone->commune_id;
        $zone->delete();
        $this->invalidateMapCache($communeId);

        return redirect()->route('zones.index')
            ->with('success', 'Zone fiscale supprimée avec succès.');
    }

    /**
     * Update zone boundaries (for map drawing)
     */
    public function updateBoundaries(Request $request, ZoneFiscale $zone, GeoValidator $validator)
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé.');
        }

        $this->ensureCanAccessZone($zone);

        $validated = $request->validate([
            'geometrie' => 'required|json',
        ]);

        $geometry = $this->validateAndNormalizeGeometry($validated['geometrie'], $validator);
        $zone->update(['geometrie' => $geometry]);
        $this->invalidateMapCache($zone->commune_id);

        Log::createLog('update_zone_boundaries', [
            'zone_id' => $zone->id,
            'zone_nom' => $zone->nom
        ]);

        return response()->json(['success' => true]);
    }

    public function apiIndex()
    {
        if (!in_array(auth()->user()->role, ['gestionnaire', 'maire', 'collecteur'])) {
            abort(403, 'Accès non autorisé.');
        }

        $query = ZoneFiscale::where('commune_id', $this->currentCommuneId())
            ->select('id', 'nom', 'description', 'commune_id');

        if (auth()->user()->role === 'collecteur') {
            $query->whereKey(auth()->user()->zone_id);
        }

        return response()->json($query->orderBy('nom')->get());
    }
}