<?php

namespace App\Http\Controllers;

use App\Models\NatureActivite;
use App\Models\TypeCommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommerceParametersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'gestionnaire') {
                abort(403, 'Accès réservé aux gestionnaires');
            }
            return $next($request);
        });
    }

    // ====== GESTION DES NATURES D'ACTIVITÉ ======

    /**
     * Affichage de la page de gestion des paramètres de commerce
     */
    public function index()
    {
        $natures = NatureActivite::ordered()->get();
        $types = TypeCommerce::with('natureActivite')->ordered()->get();
        
        return view('commerce-parameters.index', compact('natures', 'types'));
    }

    /**
     * Créer une nouvelle nature d'activité
     */
    public function storeNature(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:100|unique:nature_activites,nom',
            'description' => 'nullable|string|max:255',
            'categorie' => 'required|in:activite_commerciale,point_interet',
            'ordre' => 'nullable|integer|min:0'
        ]);

        NatureActivite::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'categorie' => $request->categorie,
            'ordre' => $request->ordre ?? 0,
            'actif' => true
        ]);

        return redirect()->route('commerce-parameters.index')
                         ->with('success', 'Nature d\'activité créée avec succès.');
    }

    /**
     * Mettre à jour une nature d'activité
     */
    public function updateNature(Request $request, NatureActivite $nature)
    {
        $request->validate([
            'nom' => 'required|string|max:100|unique:nature_activites,nom,' . $nature->id,
            'description' => 'nullable|string|max:255',
            'categorie' => 'required|in:activite_commerciale,point_interet',
            'actif' => 'boolean',
            'ordre' => 'nullable|integer|min:0'
        ]);

        $nature->update([
            'nom' => $request->nom,
            'description' => $request->description,
            'categorie' => $request->categorie,
            'actif' => $request->has('actif'),
            'ordre' => $request->ordre ?? $nature->ordre
        ]);

        return redirect()->route('commerce-parameters.index')
                         ->with('success', 'Nature d\'activité mise à jour avec succès.');
    }

    /**
     * Supprimer une nature d'activité
     */
    public function destroyNature(NatureActivite $nature)
    {
        // Vérifier s'il y a des types de commerce liés
        if ($nature->typesCommerce()->count() > 0) {
            return redirect()->route('commerce-parameters.index')
                           ->with('error', 'Impossible de supprimer cette nature car elle est utilisée par des types de commerce.');
        }

        // Vérifier s'il y a des unités commerciales liées
        if ($nature->unitesCommerciales()->count() > 0) {
            return redirect()->route('commerce-parameters.index')
                           ->with('error', 'Impossible de supprimer cette nature car elle est utilisée par des unités commerciales.');
        }

        $nature->delete();

        return redirect()->route('commerce-parameters.index')
                         ->with('success', 'Nature d\'activité supprimée avec succès.');
    }

    // ====== GESTION DES TYPES DE COMMERCE ======

    /**
     * Créer un nouveau type de commerce
     */
    public function storeType(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:100|unique:types_commerce,nom',
            'description' => 'nullable|string|max:255',
            'nature_activite_id' => 'required|exists:nature_activites,id',
            'is_transport' => 'boolean',
            'ordre' => 'nullable|integer|min:0'
        ]);

        TypeCommerce::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'nature_activite_id' => $request->nature_activite_id,
            'is_transport' => $request->has('is_transport'),
            'ordre' => $request->ordre ?? 0,
            'actif' => true
        ]);

        return redirect()->route('commerce-parameters.index')
                         ->with('success', 'Type de commerce créé avec succès.');
    }

    /**
     * Mettre à jour un type de commerce
     */
    public function updateType(Request $request, TypeCommerce $type)
    {
        $request->validate([
            'nom' => 'required|string|max:100|unique:types_commerce,nom,' . $type->id,
            'description' => 'nullable|string|max:255',
            'nature_activite_id' => 'required|exists:nature_activites,id',
            'is_transport' => 'boolean',
            'actif' => 'boolean',
            'ordre' => 'nullable|integer|min:0'
        ]);

        $type->update([
            'nom' => $request->nom,
            'description' => $request->description,
            'nature_activite_id' => $request->nature_activite_id,
            'is_transport' => $request->has('is_transport'),
            'actif' => $request->has('actif'),
            'ordre' => $request->ordre ?? $type->ordre
        ]);

        return redirect()->route('commerce-parameters.index')
                         ->with('success', 'Type de commerce mis à jour avec succès.');
    }

    /**
     * Supprimer un type de commerce
     */
    public function destroyType(TypeCommerce $type)
    {
        // Vérifier s'il y a des unités commerciales liées
        if ($type->unites()->count() > 0) {
            return redirect()->route('commerce-parameters.index')
                           ->with('error', 'Impossible de supprimer ce type car il est utilisé par des unités commerciales.');
        }

        $type->delete();

        return redirect()->route('commerce-parameters.index')
                         ->with('success', 'Type de commerce supprimé avec succès.');
    }

    // ====== API POUR LES FILTRES DYNAMIQUES ======

    /**
     * API pour récupérer les données de filtrage
     */
    public function getFiltersData()
    {
        $data = [
            'activite_commerciale' => [],
            'point_interet' => []
        ];

        $natures = NatureActivite::with('typesCommerce')->actif()->ordered()->get();
        
        foreach ($natures as $nature) {
            $types = $nature->typesCommerce()->actif()->ordered()->pluck('nom')->toArray();
            if (!empty($types)) {
                $data[$nature->categorie][$nature->nom] = $types;
            }
        }

        return response()->json($data);
    }

    /**
     * API pour récupérer les types par nature
     */
    public function getTypesByNature($natureId)
    {
        $nature = NatureActivite::find($natureId);
        if (!$nature) {
            return response()->json([]);
        }

        $types = $nature->typesCommerce()->actif()->ordered()->get();
        return response()->json($types);
    }
}
