<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\UniteCommerciale;
use App\Models\ZoneFiscale;
use App\Models\Taxe;
use App\Models\TypeCommerce;
use App\Models\NatureActivite;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class UniteCommercialeController extends Controller
{
    private function currentCommuneId(): int
    {
        return (int) auth()->user()->commune_id;
    }

    private function ensureCanAccessUnite(UniteCommerciale $unite): void
    {
        $user = auth()->user();
        if ((int) $unite->commune_id !== (int) $user->commune_id) {
            abort(403, 'Accès non autorisé à cette unité commerciale.');
        }
        if ($user->role === 'collecteur' && (int) $unite->zone_id !== (int) $user->zone_id) {
            abort(403, 'Accès non autorisé à cette unité commerciale.');
        }
    }

    /**
     * Display a listing of the unites commerciales.
     */
    public function index(Request $request)
    {
        $query = UniteCommerciale::with(['zone', 'taxe', 'collecteur']);
        
        // Restriction pour les collecteurs
        if (auth()->user()->role === 'collecteur') {
                $query->where('zone_id', auth()->user()->zone_id);
        }
        
        // Filtre de recherche générale
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('nom_proprietaire', 'like', "%{$search}%")
                  ->orWhere('prenom_proprietaire', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ninea', 'like', "%{$search}%")
                  ->orWhere('nature', 'like', "%{$search}%");
            });
        }
        
        // Filtre par type de commerce
        if ($request->filled('type_commerce')) {
            $query->where('type_commerce', $request->get('type_commerce'));
        }

        // Filtre par zone
        if ($request->filled('zone_id')) {
            $zoneId = ZoneFiscale::where('commune_id', $this->currentCommuneId())->whereKey($request->get('zone_id'))->value('id');
            if ($zoneId) {
                $query->where('zone_id', $zoneId);
            }
        }

        // Filtre par taxe
        if ($request->filled('taxe_id')) {
            $taxeId = Taxe::where('commune_id', $this->currentCommuneId())->whereKey($request->get('taxe_id'))->value('id');
            if ($taxeId) {
                $query->where('taxe_id', $taxeId);
            }
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $statut = $request->get('statut');
            if ($statut === 'actif') {
                $query->where('actif', true);
            } elseif ($statut === 'inactif') {
                $query->where('actif', false);
            }
        }

        // Tri par plus récent en premier
        $unites = $query->latest()->paginate(15);

        // Conserver les paramètres de recherche dans la pagination
        $unites->appends($request->query());

        $zones = ZoneFiscale::where('commune_id', $this->currentCommuneId())->orderBy('nom', 'asc')->get();
        $taxes = Taxe::where('commune_id', $this->currentCommuneId())->orderBy('nom', 'asc')->get();
        $typesCommerce = UniteCommerciale::where('commune_id', $this->currentCommuneId())->distinct()->orderBy('type_commerce')->pluck('type_commerce');

        return view('unites.index', compact('unites', 'zones', 'taxes', 'typesCommerce'));
    }

    /**
     * Show the form for creating a new unite commerciale.
     * Carte et position par défaut = commune de l'utilisateur.
     */
    public function create()
    {
        if (!in_array(auth()->user()->role, ['gestionnaire', 'collecteur'])) {
            abort(403, 'Accès non autorisé');
        }

        $commune = auth()->user()->commune;

        // Bloquer le recensement par collecteur si désactivé par le gestionnaire
        if (auth()->user()->role === 'collecteur' && $commune && !$commune->collecteurs_peuvent_recenser) {
            abort(403, 'Le recensement est temporairement désactivé. Contactez votre gestionnaire.');
        }

        $mapCenter = $this->defaultMapCenterForCommune($commune);

        $zones = ZoneFiscale::all();
        $taxes = Taxe::all();
        $typesCommerce = TypeCommerce::all();
        $naturesActivite = NatureActivite::all();

        $selectedZoneId = null;
        if (auth()->user()->role === 'collecteur') {
            $selectedZoneId = auth()->user()->zone_id;
        }

        return view('unites.create', compact('zones', 'taxes', 'typesCommerce', 'naturesActivite', 'selectedZoneId', 'commune', 'mapCenter'));
    }

    /**
     * Centre carte par défaut selon la commune (lat, lng).
     */
    private function defaultMapCenterForCommune(?Commune $commune): array
    {
        $defaultsByCode = [
            'SANDIARA' => [14.43540, -16.79153],
            'THIES' => [14.79, -16.93],
        ];
        $default = [14.72, -17.45];
        if ($commune && isset($defaultsByCode[strtoupper((string) ($commune->code ?? ''))])) {
            return $defaultsByCode[strtoupper($commune->code)];
        }
        return $default;
    }

    /**
     * Store a newly created unite commerciale in storage.
     */
    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['gestionnaire', 'collecteur'])) {
            abort(403, 'Accès non autorisé');
        }

        $commune = auth()->user()->commune;
        if (auth()->user()->role === 'collecteur' && $commune && !$commune->collecteurs_peuvent_recenser) {
            abort(403, 'Le recensement est temporairement désactivé. Contactez votre gestionnaire.');
        }

        $typesByNature = [
            'Boutique ou Magasin' => ["Alimentation", "Grossiste", "Mercerie", "Quincaillerie", "Shopping", "Cosmétique"],
            'Atelier' => ["Atelier mécanicien", "Atelier de couture", "Atelier de coiffure", "Atelier de forgeron", "Atelier de cordonnerie", "Atelier de menuiserie", "Boulangerie"],
            'Agriculture' => ["Chevaux", "Ânes", "Ovins", "Caprins", "Bovins", "Vaches", "Moutons", "Chèvres"],
            'Transport' => ["Bus", "Camion-citerne de carburant", "Camionnette", "Calèche", "Camion gros-porteur", "Taxi clando", "Moto Jakarta", "Car"],
            'Étal' => ["Vente sur étal"],
            'Table ou Tente commerciale' => ["Vente sur table ou sous tente"],
            'Cantine ou Kiosque' => ["Restaurant", "Gargote", "Dibiterie", "Fast Food"],
            'Entreprise' => ["Entrepôt", "Grande exploitation agricole", "Supermarché", "Station (distributeur de carburant)"],
            'Foncier' => ["Location de terrain", "Location de bâtiments"],
            'Publicité' => ["Véhicules publicitaires", "Panneaux publicitaires"],
            'Spectacles' => ["Lieux dédiés aux événements et spectacles"],
            'Papiers administratifs' => ["Extrait de naissance", "Décès", "Mariage"]
        ];
        // Validation conditionnelle : latitude/longitude requis seulement si pas de polygone
        $hasPolygon = $request->filled('geometrie_polygon') && !empty($request->input('geometrie_polygon'));
        
        $validated = $request->validate([
            'nom_proprietaire' => 'required|string|max:255',
            'prenom_proprietaire' => 'nullable|string|max:255',
            'telephone' => 'required|string|max:12|regex:/^[0-9]{1,12}$/|unique:unites_commerciales,telephone',
            'email' => 'nullable|email|max:255|unique:unites_commerciales,email',
            'ninea' => 'nullable|string|max:50|unique:unites_commerciales,ninea',
            'nin_proprietaire' => 'nullable|string|max:13|regex:/^[0-9]{0,13}$/|unique:unites_commerciales,nin_proprietaire',
            'sexe_proprietaire' => 'nullable|string|in:masculin,feminin',
            'categorie' => 'required|string|in:activite_commerciale,point_interet',
            'nature' => 'required|string|max:255',
            'type_commerce' => 'required|string|max:255',
            'adresse' => 'nullable|string',
            'zone_id' => [
                'required',
                Rule::exists('zones_fiscales', 'id')->where('commune_id', $this->currentCommuneId()),
            ],
            'latitude' => $hasPolygon ? 'nullable|numeric' : 'required|numeric',
            'longitude' => $hasPolygon ? 'nullable|numeric' : 'required|numeric',
            'altitude' => 'nullable|numeric',
            'precision' => 'nullable|numeric',
            'numero_cin' => 'nullable|string|max:50|unique:unites_commerciales,numero_cin',
            'numero_immatriculation' => 'nullable|string|max:50|unique:unites_commerciales,numero_immatriculation',
            'photo' => 'required|image|max:2048',
            'superficie' => 'nullable|numeric|min:0',
            'geometrie_polygon' => 'nullable|json'
        ], [
            'telephone.unique' => 'Ce numéro de téléphone existe déjà dans la base de données.',
            'email.unique' => 'Cet email existe déjà dans la base de données.',
            'ninea.unique' => 'Ce NINEA existe déjà dans la base de données.',
            'nin_proprietaire.unique' => 'Ce NIN (Numéro d\'Identification Nationale) existe déjà dans la base de données.',
            'numero_cin.unique' => 'Ce numéro de CIN existe déjà dans la base de données.',
            'numero_immatriculation.unique' => 'Ce numéro d\'immatriculation existe déjà dans la base de données.',
        ]);

        // Pour les collecteurs, forcer leur zone fiscale
        if (auth()->user()->role === 'collecteur') {
            $validated['zone_id'] = auth()->user()->zone_id;
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('unites', 'public');
            $validated['photo'] = $path;
        }

        $validated['collecteur_id'] = auth()->id();
        $validated['commune_id'] = $this->currentCommuneId();
        $validated['date_recensement'] = now();
        $validated['actif'] = true;

        // Décoder la géométrie polygonale si elle existe
        if (isset($validated['geometrie_polygon']) && is_string($validated['geometrie_polygon'])) {
            $validated['geometrie_polygon'] = json_decode($validated['geometrie_polygon'], true);
        }

        // Si un polygone existe mais pas de latitude/longitude, calculer le centre du polygone
        if (isset($validated['geometrie_polygon']) && !empty($validated['geometrie_polygon'])) {
            $geometry = $validated['geometrie_polygon'];
            
            if (isset($geometry['type']) && $geometry['type'] === 'Polygon' && isset($geometry['coordinates'][0])) {
                $coordinates = $geometry['coordinates'][0];
                
                // Calculer le centre (centroïde) du polygone
                $sumLat = 0;
                $sumLng = 0;
                $count = 0;
                
                foreach ($coordinates as $coord) {
                    // Les coordonnées GeoJSON sont [longitude, latitude]
                    $sumLng += $coord[0];
                    $sumLat += $coord[1];
                    $count++;
                }
                
                if ($count > 0) {
                    $centerLat = $sumLat / $count;
                    $centerLng = $sumLng / $count;
                    
                    // Utiliser le centre du polygone comme position GPS si non fourni
                    if (empty($validated['latitude']) || empty($validated['longitude'])) {
                        $validated['latitude'] = $centerLat;
                        $validated['longitude'] = $centerLng;
                    }
                }
            }
        }

        $unite = UniteCommerciale::create($validated);

        // Sauvegarder le polygone dans PostGIS si une géométrie existe
        if (isset($validated['geometrie_polygon']) && !empty($validated['geometrie_polygon'])) {
            try {
                $geometry = $validated['geometrie_polygon'];
                
                // Convertir GeoJSON en WKT pour PostGIS
                if (isset($geometry['type']) && $geometry['type'] === 'Polygon' && isset($geometry['coordinates'])) {
                    $coordinates = $geometry['coordinates'][0]; // Premier anneau (externe)
                    
                    // Construire la chaîne WKT
                    $wktCoordinates = [];
                    foreach ($coordinates as $coord) {
                        $wktCoordinates[] = $coord[0] . ' ' . $coord[1];
                    }
                    $wkt = 'POLYGON((' . implode(', ', $wktCoordinates) . '))';
                    
                    // Insérer dans PostGIS
                    DB::statement("
                        INSERT INTO public.foncier_polygons (unite_commerciale_id, geom, superficie_m2, created_at, updated_at)
                        VALUES (?, ST_GeomFromText(?, 4326), ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                    ", [
                        $unite->id,
                        $wkt,
                        $validated['superficie'] ?? null
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la sauvegarde du polygone dans PostGIS: ' . $e->getMessage());
                // Ne pas faire échouer la création de l'unité si la sauvegarde PostGIS échoue
            }
        }

        Log::createLog('create_unite', [
            'unite_id' => $unite->id,
            'unite_nom' => $unite->nom_complet_proprietaire
        ]);

        // Pour les collecteurs, rediriger vers la création de paiement
        if (auth()->user()->role === 'collecteur') {
            // Si le bouton "Continuer pour le paiement" a été cliqué
            if ($request->has('continue_to_payment')) {
                return redirect()->route('paiements.create', ['unite_id' => $unite->id])
                    ->with('success', '✅ Unité commerciale recensée avec succès !')
                    ->with('info', 'Vous pouvez maintenant enregistrer un paiement pour cette unité.')
                    ->with('unite_created', true)
                    ->with('unite_name', $unite->nom_complet_proprietaire);
            } else {
                // Si le bouton "Confirmer le recensement" a été cliqué
                return redirect()->route('unites.index')
                    ->with('success', '✅ Unité commerciale recensée avec succès !')
                    ->with('info', 'L\'unité a été ajoutée à votre liste de recensements.');
            }
        }

        return redirect()->route('unites.index')
            ->with('success', 'Unité commerciale créée avec succès.');
    }

    /**
     * Display the specified unite commerciale.
     */
    public function show(UniteCommerciale $unite)
    {
        $this->ensureCanAccessUnite($unite);

        $unite->load(['zone', 'taxe', 'collecteur', 'paiements' => function($query) {
            $query->orderBy('date_paiement', 'desc');
        }]);

        // Calculer les statistiques
        $stats = [
            'total_paiements' => $unite->paiements->count(),
            'total_montant' => $unite->paiements->where('statut', 'valide')->sum('montant'),
            'derniere_taxe' => $unite->paiements->where('statut', 'valide')->first(),
            'en_retard' => $unite->paiements->where('statut', 'en_retard')->count()
        ];

        return view('unites.show', compact('unite', 'stats'));
    }

    /**
     * Formulaire de mise à jour d'une unité pour le collecteur (avec recherche + GPS).
     */
    public function updateFormForCollecteur(Request $request)
    {
        if (auth()->user()->role !== 'collecteur') {
            abort(403, 'Accès non autorisé');
        }

        $commune = auth()->user()->commune;
        if ($commune && !$commune->collecteurs_peuvent_mettre_a_jour) {
            abort(403, 'La mise à jour est temporairement désactivée. Contactez votre gestionnaire.');
        }

        $unites = UniteCommerciale::with('zone')
            ->where('zone_id', auth()->user()->zone_id)
            ->where('actif', true)
            ->orderBy('nom_proprietaire')
            ->get();

        $zones = ZoneFiscale::where('commune_id', $this->currentCommuneId())->orderBy('nom')->get();
        $taxes = Taxe::where('commune_id', $this->currentCommuneId())->orderBy('nom')->get();

        // Données JSON pour le pré-remplissage JavaScript
        $unitesData = $unites->map(function($u) {
            return [
                'id' => $u->id,
                'nom_proprietaire' => $u->nom_proprietaire,
                'prenom_proprietaire' => $u->prenom_proprietaire,
                'type_commerce' => $u->type_commerce,
                'adresse' => $u->adresse,
                'nom_complet' => $u->nom_complet_proprietaire,
                'categorie' => $u->categorie,
                'nature' => $u->nature,
                'sous_type_commerce' => $u->sous_type_commerce,
                'sexe_proprietaire' => $u->sexe_proprietaire,
                'telephone' => $u->telephone,
                'email' => $u->email,
                'nin_proprietaire' => $u->nin_proprietaire,
                'ninea' => $u->ninea,
                'zone_id' => $u->zone_id,
                'taxe_id' => $u->taxe_id,
                'latitude' => $u->latitude,
                'longitude' => $u->longitude,
                'altitude' => $u->altitude,
                'precision' => $u->precision,
            ];
        });

        $selectedUnite = null;
        if ($request->filled('unite_id')) {
            $selectedUnite = UniteCommerciale::where('zone_id', auth()->user()->zone_id)
                ->where('id', $request->unite_id)
                ->where('actif', true)
                ->first();
        }

        return view('collecteur.unites.update', compact('unites', 'unitesData', 'selectedUnite', 'zones', 'taxes'));
    }

    /**
     * Traitement de la mise à jour par un collecteur.
     */
    public function updateByCollecteur(Request $request)
    {
        if (auth()->user()->role !== 'collecteur') {
            abort(403, 'Accès non autorisé');
        }

        $commune = auth()->user()->commune;
        if ($commune && !$commune->collecteurs_peuvent_mettre_a_jour) {
            abort(403, 'La mise à jour est temporairement désactivée. Contactez votre gestionnaire.');
        }

        $unite = UniteCommerciale::where('zone_id', auth()->user()->zone_id)
            ->where('id', $request->input('unite_id'))
            ->where('actif', true)
            ->firstOrFail();

        $this->ensureCanAccessUnite($unite);

        $validated = $request->validate([
            'nom_proprietaire' => 'required|string|max:255',
            'prenom_proprietaire' => 'required|string|max:255',
            'telephone' => 'required|string|max:12|regex:/^[0-9]{1,12}$/|unique:unites_commerciales,telephone,' . $unite->id,
            'email' => 'nullable|email|max:255|unique:unites_commerciales,email,' . $unite->id,
            'ninea' => 'nullable|string|max:50|unique:unites_commerciales,ninea,' . $unite->id,
            'nin_proprietaire' => 'nullable|string|max:13|regex:/^[0-9]{0,13}$/|unique:unites_commerciales,nin_proprietaire,' . $unite->id,
            'sexe_proprietaire' => 'nullable|string|in:masculin,feminin',
            'categorie' => 'required|string',
            'nature' => 'nullable|string|max:255',
            'adresse' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'altitude' => 'nullable|numeric',
            'precision' => 'nullable|numeric',
            'photo' => 'required|image|max:2048',
            'gps_recaptured' => 'required|in:1',
        ]);

        if ($request->hasFile('photo')) {
            if ($unite->photo) {
                Storage::disk('public')->delete($unite->photo);
            }
            $path = $request->file('photo')->store('unites', 'public');
            $validated['photo'] = $path;
        }

        $unite->update($validated);

        Log::createLog('update_unite', [
            'unite_id' => $unite->id,
            'unite_nom' => $unite->nom_complet_proprietaire
        ]);

        return redirect()->route('collecteur.unites.update-form')
            ->with('success', 'Unité commerciale mise à jour avec succès.');
    }

    /**
     * Show the form for editing the specified unite commerciale.
     */
    public function edit(UniteCommerciale $unite)
    {
        if (!in_array(auth()->user()->role, ['gestionnaire', 'collecteur'])) {
            abort(403, 'Accès non autorisé');
        }

        $commune = auth()->user()->commune;
        if (auth()->user()->role === 'collecteur' && $commune && !$commune->collecteurs_peuvent_mettre_a_jour) {
            abort(403, 'La mise à jour est temporairement désactivée. Contactez votre gestionnaire.');
        }

        $this->ensureCanAccessUnite($unite);

        $zones = ZoneFiscale::where('commune_id', $this->currentCommuneId())->orderBy('nom')->get();
        $taxes = Taxe::where('commune_id', $this->currentCommuneId())->orderBy('nom')->get();
        return view('unites.edit', compact('unite', 'zones', 'taxes'));
    }

    /**
     * Update the specified unite commerciale in storage.
     */
    public function update(Request $request, UniteCommerciale $unite)
    {
        if (!in_array(auth()->user()->role, ['gestionnaire', 'collecteur'])) {
            abort(403, 'Accès non autorisé');
        }

        $commune = auth()->user()->commune;
        if (auth()->user()->role === 'collecteur' && $commune && !$commune->collecteurs_peuvent_mettre_a_jour) {
            abort(403, 'La mise à jour est temporairement désactivée. Contactez votre gestionnaire.');
        }

        $this->ensureCanAccessUnite($unite);

        $validated = $request->validate([
            'nom_proprietaire' => 'required|string|max:255',
            'prenom_proprietaire' => 'required|string|max:255',
            'telephone' => 'required|string|max:12|regex:/^[0-9]{1,12}$/|unique:unites_commerciales,telephone,' . $unite->id,
            'email' => 'nullable|email|max:255|unique:unites_commerciales,email,' . $unite->id,
            'ninea' => 'nullable|string|max:50|unique:unites_commerciales,ninea,' . $unite->id,
            'nin_proprietaire' => 'nullable|string|max:13|regex:/^[0-9]{0,13}$/|unique:unites_commerciales,nin_proprietaire,' . $unite->id,
            'sexe_proprietaire' => 'nullable|string|in:masculin,feminin',
            'categorie' => 'required|string',
            'nature' => 'nullable|string|max:255',
            'adresse' => 'nullable|string',
            'zone_id' => [
                'required',
                Rule::exists('zones_fiscales', 'id')->where('commune_id', $this->currentCommuneId()),
            ],
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'altitude' => 'nullable|numeric',
            'precision' => 'nullable|numeric',
            'photo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($unite->photo) {
                Storage::disk('public')->delete($unite->photo);
            }
            $path = $request->file('photo')->store('unites', 'public');
            $validated['photo'] = $path;
        }

        $unite->update($validated);

        Log::createLog('update_unite', [
            'unite_id' => $unite->id,
            'unite_nom' => $unite->nom_complet_proprietaire
        ]);

        return redirect()->route('unites.index')
            ->with('success', 'Unité commerciale mise à jour avec succès.');
    }

    /**
     * Remove the specified unite commerciale from storage.
     */
    public function destroy(UniteCommerciale $unite)
    {
        if (!in_array(auth()->user()->role, ['gestionnaire'])) {
            abort(403, 'Accès non autorisé');
        }

        $this->ensureCanAccessUnite($unite);

        // Supprimer les paiements liés avant de supprimer l'unité
        $unite->paiements()->delete();

        // Supprimer la photo si elle existe
        if ($unite->photo) {
            Storage::disk('public')->delete($unite->photo);
        }

        Log::createLog('delete_unite', [
            'unite_id' => $unite->id,
            'unite_nom' => $unite->nom_complet_proprietaire
        ]);

        $unite->delete();

        return redirect()->route('unites.index')
            ->with('success', 'Unité commerciale supprimée avec succès.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(UniteCommerciale $unite)
    {
        if (auth()->user()->role === 'collecteur') {
            abort(403, 'Accès non autorisé.');
        }

        $unite->update(['actif' => !$unite->actif]);

        Log::createLog('toggle_unite_status', [
            'unite_id' => $unite->id,
            'new_status' => $unite->actif ? 'active' : 'inactive'
        ]);

        return back()->with('success', 'Statut modifié avec succès.');
    }

    public function apiIndex()
    {
        if (!in_array(auth()->user()->role, ['gestionnaire', 'maire', 'collecteur'])) {
            abort(403, 'Accès non autorisé');
        }

        $query = UniteCommerciale::with(['zone:id,nom', 'taxe:id,nom'])
            ->where('commune_id', $this->currentCommuneId());

        if (auth()->user()->role === 'collecteur') {
            $query->where('zone_id', auth()->user()->zone_id);
        }

        return response()->json($query->latest()->limit(500)->get());
    }

    public function apiStore(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Export des unités commerciales en Excel (CSV).
     */
    public function exportExcel(Request $request)
    {
        $query = UniteCommerciale::with(['zone', 'taxe', 'collecteur']);

        if (auth()->user()->role === 'collecteur') {
            $query->where('zone_id', auth()->user()->zone_id);
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('nom_proprietaire', 'like', "%{$search}%")
                  ->orWhere('prenom_proprietaire', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ninea', 'like', "%{$search}%")
                  ->orWhere('nature', 'like', "%{$search}%");
            });
        }

        if ($request->filled('zone_id')) {
            $zoneId = ZoneFiscale::where('commune_id', $this->currentCommuneId())->whereKey($request->get('zone_id'))->value('id');
            if ($zoneId) {
                $query->where('zone_id', $zoneId);
            }
        }

        if ($request->filled('taxe_id')) {
            $taxeId = Taxe::where('commune_id', $this->currentCommuneId())->whereKey($request->get('taxe_id'))->value('id');
            if ($taxeId) {
                $query->where('taxe_id', $taxeId);
            }
        }

        if ($request->filled('statut')) {
            $statut = $request->get('statut');
            if ($statut === 'actif') {
                $query->where('actif', true);
            } elseif ($statut === 'inactif') {
                $query->where('actif', false);
            }
        }

        $unites = $query->orderBy('nom_proprietaire', 'asc')->orderBy('prenom_proprietaire', 'asc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="unites_commerciales_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($unites) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'Nom complet', 'Téléphone', 'Email', 'Type commerce', 'Nature',
                'Zone', 'Taxe', 'NINEA/Matricule', 'Statut', 'Date recensement', 'Collecteur'
            ], ';');
            foreach ($unites as $u) {
                fputcsv($file, [
                    $u->nom_complet_proprietaire ?? '',
                    $u->telephone ?? '',
                    $u->email ?? '',
                    $u->type_commerce ?? '',
                    $u->nature ?? '',
                    $u->zone->nom ?? '',
                    $u->taxe ? ($u->taxe->nom . ' (' . number_format($u->taxe->montant, 0, ',', ' ') . ' FCFA)') : '',
                    $u->ninea ?? '',
                    $u->actif ? 'Actif' : 'Inactif',
                    $u->date_recensement ? $u->date_recensement->format('d/m/Y') : '',
                    $u->collecteur->name ?? '',
                ], ';');
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export des unités commerciales en PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = UniteCommerciale::with(['zone', 'taxe', 'collecteur']);

        if (auth()->user()->role === 'collecteur') {
            $query->where('zone_id', auth()->user()->zone_id);
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('nom_proprietaire', 'like', "%{$search}%")
                  ->orWhere('prenom_proprietaire', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ninea', 'like', "%{$search}%")
                  ->orWhere('nature', 'like', "%{$search}%");
            });
        }

        if ($request->filled('zone_id')) {
            $zoneId = ZoneFiscale::where('commune_id', $this->currentCommuneId())->whereKey($request->get('zone_id'))->value('id');
            if ($zoneId) {
                $query->where('zone_id', $zoneId);
            }
        }

        if ($request->filled('taxe_id')) {
            $taxeId = Taxe::where('commune_id', $this->currentCommuneId())->whereKey($request->get('taxe_id'))->value('id');
            if ($taxeId) {
                $query->where('taxe_id', $taxeId);
            }
        }

        if ($request->filled('statut')) {
            $statut = $request->get('statut');
            if ($statut === 'actif') {
                $query->where('actif', true);
            } elseif ($statut === 'inactif') {
                $query->where('actif', false);
            }
        }

        $unites = $query->orderBy('nom_proprietaire', 'asc')->orderBy('prenom_proprietaire', 'asc')->get();
        $commune = auth()->user()->commune;

        $pdf = Pdf::loadView('unites.export-pdf', compact('unites', 'commune'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('unites_commerciales_' . now()->format('Y-m-d') . '.pdf');
    }
}