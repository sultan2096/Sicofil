<?php

namespace App\Http\Controllers;

use App\Models\Taxe;
use App\Models\Log;
use App\Models\UniteCommerciale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxeController extends Controller
{
    /**
     * Display a listing of the taxes.
     */
    public function index()
    {
        if (!in_array(auth()->user()->role, ['gestionnaire', 'maire'])) {
            abort(403, 'Accès non autorisé');
        }

        // Forcer le rafraîchissement des données
        // DB::connection()->getPdo()->exec('SET SESSION query_cache_type = OFF');

        $taxes = Taxe::withCount('unitesCommerciales')
            ->latest()
            ->get(); // Utiliser get() au lieu de paginate() temporairement

        return response()
            ->view('taxes.index', compact('taxes'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Show the form for creating a new taxe.
     */
    public function create()
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }

        return view('taxes.create');
    }

    /**
     * Store a newly created taxe in storage.
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:taxes',
            'description' => 'nullable|string',
            'montant' => 'nullable|numeric|min:0',
            'montant_par_m2' => 'nullable|numeric|min:0',
            'is_fonciere' => 'boolean',
            'periodicite' => 'required|in:jour,mois,an,non_periodique',
            'types_commerce_applicables' => 'nullable|array',
            'types_commerce_applicables.*' => 'string',
        ]);

        // Validation conditionnelle : si c'est une taxe foncière, montant_par_m2 est requis, sinon montant est requis
        if ($request->has('is_fonciere') && $request->is_fonciere) {
            $validated['is_fonciere'] = true;
            if (empty($validated['montant_par_m2'])) {
                return back()->withErrors(['montant_par_m2' => 'Le montant par m² est requis pour une taxe foncière.'])->withInput();
            }
            // Pour les taxes foncières, le montant est 0 car il est calculé selon la superficie
            $validated['montant'] = 0;
        } else {
            $validated['is_fonciere'] = false;
            if (empty($validated['montant'])) {
                return back()->withErrors(['montant' => 'Le montant est requis.'])->withInput();
            }
            // S'assurer que montant n'est pas null
            $validated['montant'] = (float) $validated['montant'];
        }

        // Ajouter les champs requis par la base de données
        // La colonne 'nature' est NOT NULL dans la table taxes (enum: produits_domaniaux, taxes_municipales, produits_exploitation)
        if (!isset($validated['nature'])) {
            // Pour une taxe foncière, utiliser 'taxes_municipales' par défaut
            $validated['nature'] = 'taxes_municipales';
        }
        
        // La colonne 'activite_produit' est NOT NULL dans la table taxes
        if (!isset($validated['activite_produit'])) {
            $validated['activite_produit'] = $validated['is_fonciere'] ? 'Taxe foncière' : ($validated['nom'] ?? 'Taxe');
        }
        
        // S'assurer que montant n'est jamais null (requis par la base de données)
        if (!isset($validated['montant']) || $validated['montant'] === null) {
            $validated['montant'] = 0;
        }
        
        $taxe = Taxe::create($validated);

        Log::createLog('create_taxe', [
            'taxe_id' => $taxe->id,
            'taxe_nom' => $taxe->nom,
            'montant' => $taxe->montant
        ]);

        return redirect()->route('taxes.index')
            ->with('success', 'Taxe créée avec succès.');
    }

    /**
     * Display the specified taxe.
     */
    public function show(Taxe $taxe)
    {
        // Admin et maire peuvent voir les détails des taxes
        if (!in_array(auth()->user()->role, ['admin', 'maire'])) {
            abort(403, 'Accès non autorisé.');
        }

        $taxe->load(['unitesCommerciales' => function($query) {
            $query->actif()->orderBy('nom_proprietaire')->orderBy('prenom_proprietaire');
        }]);

        return view('taxes.show', compact('taxe'));
    }

    /**
     * Show the form for editing the specified taxe.
     */
    public function edit($id)
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }

        $taxe = Taxe::findOrFail($id);

        return view('taxes.edit', compact('taxe'));
    }

    /**
     * Update the specified taxe in storage.
     */
    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }

        $taxe = Taxe::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:taxes,nom,' . $taxe->id,
            'description' => 'nullable|string',
            'montant' => 'nullable|numeric|min:0',
            'montant_par_m2' => 'nullable|numeric|min:0',
            'is_fonciere' => 'boolean',
            'periodicite' => 'required|in:jour,mois,an,non_periodique',
            'types_commerce_applicables' => 'nullable|array',
            'types_commerce_applicables.*' => 'string',
        ]);

        // Validation conditionnelle : si c'est une taxe foncière, montant_par_m2 est requis, sinon montant est requis
        if ($request->has('is_fonciere') && $request->is_fonciere) {
            $validated['is_fonciere'] = true;
            if (empty($validated['montant_par_m2'])) {
                return back()->withErrors(['montant_par_m2' => 'Le montant par m² est requis pour une taxe foncière.'])->withInput();
            }
            $validated['montant'] = 0; // Pour les taxes foncières, le montant est 0 car il est calculé
        } else {
            $validated['is_fonciere'] = false;
            if (empty($validated['montant'])) {
                return back()->withErrors(['montant' => 'Le montant est requis.'])->withInput();
            }
        }

        // S'assurer que les champs requis par la base de données sont présents
        // La colonne 'nature' est NOT NULL dans la table taxes
        if (!isset($validated['nature'])) {
            // Conserver la valeur existante ou utiliser une valeur par défaut
            $validated['nature'] = $taxe->nature ?? 'taxes_municipales';
        }
        
        // La colonne 'activite_produit' est NOT NULL dans la table taxes
        if (!isset($validated['activite_produit'])) {
            // Conserver la valeur existante ou utiliser une valeur par défaut
            $validated['activite_produit'] = $taxe->activite_produit ?? ($validated['is_fonciere'] ? 'Taxe foncière' : ($validated['nom'] ?? 'Taxe'));
        }

        $taxe->update($validated);

        Log::createLog('update_taxe', [
            'taxe_id' => $taxe->id,
            'taxe_nom' => $taxe->nom,
            'montant' => $taxe->montant
        ]);

        return redirect()->route('taxes.index')
            ->with('success', 'Taxe mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Taxe $taxe)
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }

        // Sauvegarder les infos
        $nom = $taxe->nom;
        $id = $taxe->id;
        
        try {
            // Mettre à jour les unités commerciales
            UniteCommerciale::where('taxe_id', $id)->update(['taxe_id' => null]);
            
            // Supprimer la taxe
            $taxe->delete();
            
            // Log
        Log::createLog('delete_taxe', [
                'taxe_id' => $id,
                'taxe_nom' => $nom
        ]);

            return redirect()->route('taxes.index')
                ->with('success', "Taxe '$nom' (ID: $id) supprimée avec succès.");

        } catch (\Exception $e) {
        return redirect()->route('taxes.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Get taxes by nature (for AJAX)
     */
    public function getByNature(Request $request)
    {
        $validated = $request->validate([
            'nature' => 'required|string',
            'periodicite' => 'nullable|string',
        ]);

        $query = Taxe::where('activite_produit', 'like', '%' . $validated['nature'] . '%');

        if ($request->filled('periodicite')) {
            $query->where('periodicite', $validated['periodicite']);
        }

        $taxes = $query->orderBy('nom')->get();

        return response()->json($taxes);
    }
} 