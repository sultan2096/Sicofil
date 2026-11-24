<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ZoneFiscale;
use App\Models\Log;
use App\Models\UniteCommerciale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function currentCommuneId(): int
    {
        return (int) auth()->user()->commune_id;
    }

    private function ensureCanAccessUser(User $user): void
    {
        if ((int) $user->commune_id !== $this->currentCommuneId()) {
            abort(403, 'Accès non autorisé à cet utilisateur.');
        }
    }

    /**
     * Display a listing of users.
     */
    public function index()
    {
        if (!in_array(auth()->user()->role, ['gestionnaire', 'maire'])) {
            abort(403, 'Accès non autorisé');
        }

        // Le maire peut voir tous les utilisateurs (y compris lui-meme), le gestionnaire voit les collecteurs et les gestionnaires
        if (auth()->user()->role === 'maire') {
            $users = User::whereIn('role', ['gestionnaire', 'collecteur', 'maire'])
                ->with('zone')
                ->latest()
                ->paginate(10);
        } else {
            // Gestionnaire voit les collecteurs, les gestionnaires (y compris lui-meme) et le maire
            $users = User::whereIn('role', ['collecteur', 'gestionnaire', 'maire'])
                ->with('zone')
                ->latest()
                ->paginate(10);
        }

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        // Seuls les gestionnaires peuvent créer des utilisateurs
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Seuls les gestionnaires peuvent créer des utilisateurs');
        }

        $zones = ZoneFiscale::where('commune_id', $this->currentCommuneId())->orderBy('nom')->get();
        return view('users.create', compact('zones'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // Seuls les gestionnaires peuvent créer des utilisateurs
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Seuls les gestionnaires peuvent créer des utilisateurs');
        }

        // Validation de base
        $rules = [
            'name' => 'required|string|max:255',
            'identifiant' => 'required|string|unique:users|max:255',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:maire,collecteur',
        ];

        // Validation conditionnelle pour la zone (seulement pour les collecteurs)
        if ($request->role !== 'maire') {
            if ($request->zone_id === 'other') {
                $rules['custom_zone_name'] = 'required|string|max:255';
            } else {
                $rules['zone_id'] = [
                    'required',
                    Rule::exists('zones_fiscales', 'id')->where('commune_id', $this->currentCommuneId()),
                ];
            }
        }

        $validated = $request->validate($rules);

        $commune_id = $this->currentCommuneId();

        // Gérer la création d'une nouvelle zone si nécessaire
        $zone_id = $validated['zone_id'] ?? null;
        
        if ($request->zone_id === 'other') {
            $custom_zone_name = $validated['custom_zone_name'];
            
            $existing_zone = ZoneFiscale::where('nom', $custom_zone_name)->where('commune_id', $commune_id)->first();
            
            if ($existing_zone) {
                $zone_id = $existing_zone->id;
                $zone_message = "Zone existante '$custom_zone_name' utilisée";
            } else {
                $new_zone = ZoneFiscale::create([
                    'nom' => $custom_zone_name,
                    'description' => 'Zone créée automatiquement lors de l\'ajout du collecteur ' . $validated['name'],
                    'commune_id' => $commune_id,
                ]);
                $zone_id = $new_zone->id;
                $zone_message = "Nouvelle zone '$custom_zone_name' créée";
                
                // Log de la création de zone
                Log::createLog('create_zone', [
                    'zone_id' => $new_zone->id,
                    'zone_name' => $custom_zone_name,
                    'created_by' => auth()->user()->id,
                    'context' => 'Création automatique lors de l\'ajout d\'un collecteur'
                ]);
            }
        }

        $base = Str::slug($validated['identifiant']) . '-' . $commune_id;
        $email = $base . '@collecteur.local';
        $n = 0;
        while (User::where('email', $email)->exists()) {
            $email = $base . '-' . (++$n) . '@collecteur.local';
        }

        $role = $validated['role'] ?? 'collecteur';

        $user = User::create([
            'name' => $validated['name'],
            'identifiant' => $validated['identifiant'],
            'email' => $email,
            'password' => $validated['password'],
            'role' => $role,
            'zone_id' => $zone_id,
            'commune_id' => $commune_id,
        ]);

        $logAction = $role === 'maire' ? 'create_maire' : 'create_collecteur';
        Log::createLog($logAction, [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'zone_id' => $user->zone_id
        ]);

        $success_message = ($role === 'maire' ? 'Maire' : 'Collecteur') . ' créé avec succès';
        if (isset($zone_message)) {
            $success_message .= '. ' . $zone_message;
        }

        return redirect()->route('users.index')
            ->with('success', $success_message);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Gestionnaire et maire peuvent voir les détails des utilisateurs
        if (!in_array(auth()->user()->role, ['gestionnaire', 'maire'])) {
            abort(403, 'Accès non autorisé');
        }

        $this->ensureCanAccessUser($user);

        // Charger les relations nécessaires
        $user->load('zone');
        
        // Récupérer les statistiques de l'utilisateur
        $stats = [];
        
        if ($user->role === 'collecteur') {
            // Statistiques pour un collecteur
            $stats = [
                'total_paiements' => $user->paiements()->count(),
                'total_montant' => $user->paiements()->where('statut', 'valide')->sum('montant'),
                'paiements_ce_mois' => $user->paiements()->whereMonth('created_at', now()->month)->count(),
                'montant_ce_mois' => $user->paiements()->whereMonth('created_at', now()->month)->where('statut', 'valide')->sum('montant'),
                'derniere_activite' => $user->paiements()->latest()->first()?->created_at,
                'unites_recensees' => $user->zone ? $user->zone->unitesCommerciales()->count() : 0,
            ];
            
            // Récupérer les derniers paiements
            $derniers_paiements = $user->paiements()->with('unite')->latest()->limit(5)->get();
        } else {
            // Statistiques générales pour gestionnaire
            $stats = [
                'derniere_connexion' => Log::where('user_id', $user->id)
                    ->where('action', 'login')
                    ->latest()
                    ->first()?->created_at,
                'total_actions' => Log::where('user_id', $user->id)->count(),
                'actions_ce_mois' => Log::where('user_id', $user->id)
                    ->whereMonth('created_at', now()->month)
                    ->count(),
            ];
            
            $derniers_paiements = collect();
        }
        
        // Récupérer les dernières activités (logs)
        $dernieres_activites = Log::where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();
        
        return view('users.show', compact('user', 'stats', 'derniers_paiements', 'dernieres_activites'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Seuls les gestionnaires et le maire peuvent modifier des utilisateurs
        if (!in_array(auth()->user()->role, ['gestionnaire', 'maire'])) {
            abort(403, 'Seuls les gestionnaires et le maire peuvent modifier des utilisateurs');
        }

        $this->ensureCanAccessUser($user);

        if (!in_array($user->role, ['collecteur', 'gestionnaire', 'maire'])) {
            abort(403, 'Vous ne pouvez modifier que les collecteurs, gestionnaires et maire');
        }

        $zones = ZoneFiscale::where('commune_id', $this->currentCommuneId())->orderBy('nom')->get();
        return view('users.edit', compact('user', 'zones'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Seuls les gestionnaires et le maire peuvent modifier des utilisateurs
        if (!in_array(auth()->user()->role, ['gestionnaire', 'maire'])) {
            abort(403, 'Seuls les gestionnaires et le maire peuvent modifier des utilisateurs');
        }

        $this->ensureCanAccessUser($user);

        if (!in_array($user->role, ['collecteur', 'gestionnaire', 'maire'])) {
            abort(403, 'Vous ne pouvez modifier que les collecteurs, gestionnaires et maire');
        }

        // Validation de base
        $rules = [
            'name' => 'required|string|max:255',
            'identifiant' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
        ];

        // Validation conditionnelle pour la zone (seulement pour les collecteurs)
        $zone_id = $user->zone_id;
        if ($user->role === 'collecteur') {
            if ($request->zone_id === 'other') {
                $rules['custom_zone_name'] = 'required|string|max:255';
            } else {
                $rules['zone_id'] = [
                    'required',
                    Rule::exists('zones_fiscales', 'id')->where('commune_id', $this->currentCommuneId()),
                ];
            }
        }

        $validated = $request->validate($rules);

        // Gérer la création d'une nouvelle zone si nécessaire (seulement pour les collecteurs)
        if ($user->role === 'collecteur') {
            $zone_id = $validated['zone_id'] ?? null;
        }
        
        if ($user->role === 'collecteur' && $request->zone_id === 'other') {
            $custom_zone_name = $validated['custom_zone_name'];
            
            // Vérifier si une zone avec ce nom existe déjà
            $existing_zone = ZoneFiscale::where('nom', $custom_zone_name)->where('commune_id', $this->currentCommuneId())->first();
            
            if ($existing_zone) {
                // Utiliser la zone existante
                $zone_id = $existing_zone->id;
                $zone_message = "Zone existante '$custom_zone_name' utilisée";
            } else {
                // Créer une nouvelle zone
                $new_zone = ZoneFiscale::create([
                    'nom' => $custom_zone_name,
                    'description' => 'Zone créée automatiquement lors de la modification du collecteur ' . $validated['name'],
                    'commune_id' => $this->currentCommuneId(),
                ]);
                $zone_id = $new_zone->id;
                $zone_message = "Nouvelle zone '$custom_zone_name' créée";
                
                // Log de la création de zone
                Log::createLog('create_zone', [
                    'zone_id' => $new_zone->id,
                    'zone_name' => $custom_zone_name,
                    'created_by' => auth()->user()->id,
                    'context' => 'Création automatique lors de la modification d\'un collecteur'
                ]);
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'identifiant' => $validated['identifiant'],
        ];

        if ($user->role === 'collecteur') {
            $updateData['zone_id'] = $zone_id;
        }

        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $user->update($updateData);

        Log::createLog('update_collecteur', [
            'collecteur_id' => $user->id,
            'collecteur_name' => $user->name,
            'zone_id' => $user->zone_id
        ]);

        $success_message = 'Collecteur mis à jour avec succès';
        if (isset($zone_message)) {
            $success_message .= '. ' . $zone_message;
        }

        return redirect()->route('users.index')
            ->with('success', $success_message);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Seuls les gestionnaires peuvent supprimer des utilisateurs
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Seuls les gestionnaires peuvent supprimer des utilisateurs');
        }

        $this->ensureCanAccessUser($user);

        if ($user->role !== 'collecteur') {
            abort(403, 'Vous ne pouvez supprimer que les collecteurs');
        }

        // Détacher le collecteur des unités commerciales avant suppression
        \App\Models\UniteCommerciale::where('collecteur_id', $user->id)->update(['collecteur_id' => null]);

        Log::createLog('delete_collecteur', [
            'collecteur_id' => $user->id,
            'collecteur_name' => $user->name,
            'zone_id' => $user->zone_id
        ]);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Collecteur supprimé avec succès');
    }

    /**
     * Affiche le formulaire de modification du profil du gestionnaire
     */
    public function editProfile()
    {
        $user = auth()->user();
        return view('users.profil', compact('user'));
    }

    /**
     * Met à jour le profil du gestionnaire
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'identifiant' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        
        $updateData = [
            'name' => $validated['name'],
            'identifiant' => $validated['identifiant']
        ];
        
        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }
        
        $user->update($updateData);
        
        return back()->with('success', 'Profil mis à jour avec succès');
    }
} 