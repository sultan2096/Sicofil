<?php

namespace App\Http\Controllers;

use App\Models\CollectorTracking;
use App\Models\Commune;
use App\Models\User;
use App\Models\ZoneFiscale;
use App\Events\CollectorPositionUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    /**
     * Enregistrer la position d'un collecteur
     */
    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'altitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
            'activity' => 'nullable|string|in:collecting,moving,idle',
            'battery_level' => 'nullable|string'
        ]);

        // Seuls les collecteurs peuvent enregistrer leur position
        if (strtolower((string) Auth::user()->role) !== 'collecteur') {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        CollectorTracking::create([
            'collector_id' => Auth::id(),
            'commune_id' => Auth::user()->commune_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'altitude' => $request->altitude,
            'accuracy' => $request->accuracy,
            'activity' => $request->activity ?? 'moving',
            'battery_level' => $request->battery_level,
            'tracked_at' => now()
        ]);

        $communeId = Auth::user()->commune_id ?? 0;
        event(new CollectorPositionUpdated(
            Auth::id(),
            (int) $communeId,
            (float) $request->latitude,
            (float) $request->longitude,
            now()->toIso8601String()
        ));

        return response()->json(['success' => true]);
    }

    /**
     * Obtenir les positions des collecteurs pour les gestionnaires et maires
     */
    public function index(Request $request)
    {
        // Seuls les gestionnaires et maires peuvent voir les positions
        $user = Auth::user();
        $role = strtolower((string) ($user->role ?? ''));
        if (!in_array($role, ['gestionnaire', 'maire'])) {
            abort(403, 'Accès non autorisé');
        }

        $minutes = $request->get('minutes', 30);

        $collectorIds = User::where('role', 'collecteur')
            ->when(!empty($user->commune_id), function ($q) use ($user) {
                $q->where('commune_id', $user->commune_id);
            })
            ->pluck('id');

        $tracking = CollectorTracking::with('collector')
            ->whereIn('collector_id', $collectorIds)
            ->recent($minutes)
            ->orderBy('tracked_at', 'desc')
            ->get()
            ->groupBy('collector_id');

        return response()->json($tracking);
    }

    /**
     * Obtenir l'itinéraire d'un collecteur spécifique
     */
    public function show($collectorId, Request $request)
    {
        // Seuls les gestionnaires et maires peuvent voir les itinéraires
        $user = Auth::user();
        $role = strtolower((string) ($user->role ?? ''));
        if (!in_array($role, ['gestionnaire', 'maire'])) {
            abort(403, 'Accès non autorisé');
        }

        $hours = $request->get('hours', 24);

        if (!empty($user->commune_id)) {
            $collector = User::where('role', 'collecteur')
                ->where('id', $collectorId)
                ->where('commune_id', $user->commune_id)
                ->first();
            if (!$collector) {
                abort(403, 'Accès non autorisé');
            }
        }

        $route = CollectorTracking::where('collector_id', $collectorId)
            ->where('tracked_at', '>=', now()->subHours($hours))
            ->orderBy('tracked_at', 'asc')
            ->get();

        return response()->json($route);
    }

    /**
     * Vue pour le suivi en temps réel. Carte et zones = commune de l'utilisateur.
     */
    public function liveTracking()
    {
        // Seuls les gestionnaires et maires peuvent accéder
        $user = Auth::user();
        $role = strtolower((string) ($user->role ?? ''));
        if (!in_array($role, ['gestionnaire', 'maire'])) {
            abort(403, 'Accès non autorisé');
        }

        $commune = $user->commune;
        if (!$commune) {
            return redirect()
                ->route($role === 'maire' ? 'maire.dashboard' : 'gestionnaire.dashboard')
                ->with('error', 'Aucune commune associée à votre compte. Contactez l\'administrateur.');
        }

        $collectors = User::where('role', 'collecteur')
            ->where('commune_id', $commune->id)
            ->with('zone')
            ->get();

        $zones = ZoneFiscale::where('commune_id', $commune->id)
            ->get()
            ->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'nom' => $zone->nom,
                    'geometrie' => $zone->geometrie,
                ];
            })
            ->toArray();

        [$mapCenter, $mapBounds] = $this->computeMapCenterAndBoundsForTracking($zones, $commune);

        return view('tracking.live', compact('collectors', 'commune', 'zones', 'mapCenter', 'mapBounds'));
    }

    /**
     * Centre et bornes de la carte : zones de la commune ou défauts par code (SANDIARA, THIES, etc.).
     */
    private function computeMapCenterAndBoundsForTracking(array $zones, Commune $commune): array
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
            $this->extractCoordsRecursive($geom['coordinates'], $lats, $lngs);
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

        $code = strtoupper((string) ($commune->code ?? ''));
        if (isset($defaultsByCode[$code])) {
            return $defaultsByCode[$code];
        }
        return [$defaultSenegal[0], $defaultSenegal[1]];
    }

    private function extractCoordsRecursive($coords, array &$lats, array &$lngs): void
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
            $this->extractCoordsRecursive($item, $lats, $lngs);
        }
    }
}
