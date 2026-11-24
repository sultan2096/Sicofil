@extends('layouts.app')

@section('title', 'Gestion des Paiements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-money-check-alt"></i>
                        @if(auth()->user()->role === 'maire')
                            Consultation des Paiements
                        @else
                        Gestion des Paiements
                        @endif
                    </h3>
                    <div class="btn-group">
                    @if(auth()->user()->role === 'collecteur')
                        <a href="{{ route('paiements.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i>
                            Enregistrer un paiement
                        </a>
                    @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('show_ticket_option') && session('paiement_id'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <i class="fas fa-receipt me-2"></i>
                                    <strong>Paiement enregistré avec succès !</strong>
                                    <br>Voulez-vous générer le reçu de paiement pour le remettre au client ?
                                </div>
                                <div class="ms-3">
                                    <a href="{{ route('paiements.ticket', session('paiement_id')) }}" 
                                       class="btn btn-primary btn-sm me-2" target="_blank">
                                        <i class="fas fa-receipt me-1"></i>
                                        Générer le Reçu
                                    </a>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filtres -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" action="{{ route('paiements.index') }}">
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-3 mb-2">
                                        <label for="date_debut" class="form-label">Date début</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="date_debut" 
                                               name="date_debut" 
                                               value="{{ request('date_debut') }}">
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 mb-2">
                                        <label for="date_fin" class="form-label">Date fin</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="date_fin" 
                                               name="date_fin" 
                                               value="{{ request('date_fin') }}">
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3 mb-2">
                                        <label for="statut" class="form-label">Statut</label>
                                        <select class="form-select" id="statut" name="statut">
                                            <option value="">Tous les statuts</option>
                                            <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>
                                                En attente
                                            </option>
                                            <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>
                                                Validé
                                            </option>
                                            <option value="annule" {{ request('statut') == 'annule' ? 'selected' : '' }}>
                                                Annulé
                                            </option>
                                            <option value="correction_demandee" {{ request('statut') == 'correction_demandee' ? 'selected' : '' }}>
                                                Correction demandée
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="mode_paiement" class="form-label">Mode de paiement</label>
                                        <select class="form-select" id="mode_paiement" name="mode_paiement">
                                            <option value="">Tous les modes</option>
                                            <option value="cash" {{ request('mode_paiement') == 'cash' ? 'selected' : '' }}>
                                                Cash
                                            </option>
                                            <option value="wave" {{ request('mode_paiement') == 'wave' ? 'selected' : '' }}>
                                                Wave
                                            </option>
                                            <option value="orange_money" {{ request('mode_paiement') == 'orange_money' ? 'selected' : '' }}>
                                                Orange Money
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                
                                @if(auth()->user()->role !== 'collecteur')
                                    @php
                                        // Charger les données une seule fois pour éviter les requêtes N+1
                                        $collecteurs = App\Models\User::where('role', 'collecteur')->select('id', 'name')->orderBy('name')->get();
                                        $zones = App\Models\ZoneFiscale::select('id', 'nom')->orderBy('nom')->get();
                                        $unites = App\Models\UniteCommerciale::actif()->select('id', 'nom_proprietaire', 'prenom_proprietaire', 'type_commerce')->orderBy('nom_proprietaire')->get();
                                    @endphp
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="collecteur_id" class="form-label">Collecteur</label>
                                            <select class="form-select" id="collecteur_id" name="collecteur_id">
                                                <option value="">Tous les collecteurs</option>
                                                @foreach($collecteurs as $collecteur)
                                                    <option value="{{ $collecteur->id }}" {{ request('collecteur_id') == $collecteur->id ? 'selected' : '' }}>
                                                        {{ $collecteur->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="zone_id" class="form-label">Zone fiscale</label>
                                            <select class="form-select" id="zone_id" name="zone_id">
                                                <option value="">Toutes les zones</option>
                                                @foreach($zones as $zone)
                                                    <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                                        {{ $zone->nom }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="unite_id" class="form-label">Unité Commerciale</label>
                                            <select class="form-select" id="unite_id" name="unite_id">
                                                <option value="">Toutes les unités</option>
                                                @foreach($unites as $unite)
                                                    <option value="{{ $unite->id }}" {{ request('unite_id') == $unite->id ? 'selected' : '' }}>
                                                        {{ $unite->nom_complet_proprietaire }} - {{ $unite->type_commerce }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="row mt-3">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-outline-primary me-2">
                                            <i class="fas fa-search"></i>
                                            Filtrer
                                        </button>
                                        <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-times"></i>
                                            Réinitialiser
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistiques rapides -->
                    @if($paiements->total() > 0)
                        @php
                            // Calculer les statistiques réelles sur tous les paiements (pas seulement la page courante)
                            $totalPaiements = $paiements->total();
                            
                            // Pour avoir les vraies statistiques, on doit récupérer tous les paiements avec les mêmes filtres
                            // Optimiser en ne sélectionnant que les colonnes nécessaires
                            $query = \App\Models\Paiement::select('id', 'montant', 'statut', 'collecteur_id', 'unite_id');
                            
                            // Appliquer les mêmes filtres que dans le contrôleur
                            if (auth()->user()->role === 'collecteur') {
                                if (auth()->user()->zone_id) {
                                    $query->whereHas('unite', function($q) {
                                        $q->where('zone_id', auth()->user()->zone_id);
                                    })->where('collecteur_id', auth()->id());
                                }
                            }
                            if (request()->filled('date_debut')) {
                                $query->where('date_paiement', '>=', request('date_debut'));
                            }
                            if (request()->filled('date_fin')) {
                                $query->where('date_paiement', '<=', request('date_fin'));
                            }
                            if (request()->filled('statut')) {
                                $query->where('statut', request('statut'));
                            }
                            if (request()->filled('mode_paiement')) {
                                $query->where('mode_paiement', request('mode_paiement'));
                            }
                            if (request()->filled('collecteur_id') && auth()->user()->role !== 'collecteur') {
                                $query->where('collecteur_id', request('collecteur_id'));
                            }
                            if (request()->filled('zone_id') && auth()->user()->role !== 'collecteur') {
                                $query->whereHas('unite', function($q) {
                                    $q->where('zone_id', request('zone_id'));
                                });
                            }
                            
                            // Nouveau filtre par unité commerciale
                            if (request()->filled('unite_id')) {
                                $query->where('unite_id', request('unite_id'));
                            }
                            
                            $allFilteredPaiements = $query->get();
                            $montantTotal = $allFilteredPaiements->sum('montant');
                            $montantValide = $allFilteredPaiements->where('statut', 'valide')->sum('montant');
                            $nombreEnAttente = $allFilteredPaiements->where('statut', 'en_attente')->count();
                            $montantEnAttente = $allFilteredPaiements->where('statut', 'en_attente')->sum('montant');
                            $montantMoyen = $allFilteredPaiements->count() > 0 ? $allFilteredPaiements->avg('montant') : 0;
                        @endphp
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5>{{ number_format($montantValide, 0, ',', ' ') }} FCFA</h5>
                                        <small>Montant payé</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h5>{{ $nombreEnAttente }}</h5>
                                        <small>Paiements en attente</small>
                                        <div class="mt-1">
                                            <small style="font-size: 0.85em;">{{ number_format($montantEnAttente, 0, ',', ' ') }} FCFA</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h5>{{ $totalPaiements }}</h5>
                                        <small>Total des paiements</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Liste des paiements -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Unité commerciale</th>
                                    <th>Montant</th>
                                    <th>Mode</th>
                                    <th>Collecteur</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paiements as $paiement)
                                    <tr>
                                        <td>
                                            <strong>{{ $paiement->date_paiement->format('d/m/Y') }}</strong><br>
                                            <small class="text-muted">{{ $paiement->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            @if($paiement->unite)
                                                <strong>{{ $paiement->unite->nature }}</strong><br>
                                                <small class="text-muted">{{ $paiement->unite->nom_complet_proprietaire }}</small>
                                            @else
                                                <span class="text-muted">Unité supprimée</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <span class="h6 text-primary">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</span>
                                                    
                                                    @if($paiement->montant_avance > 0 || $paiement->montant_restant > 0)
                                                        <br>
                                                        @if($paiement->montant_restant > 0)
                                                            <!-- Paiement partiel -->
                                                            <small class="text-warning">
                                                                <i class="fas fa-coins"></i> 
                                                                Avance: {{ number_format($paiement->montant_avance, 0, ',', ' ') }} FCFA
                                                            </small><br>
                                                            <small class="text-danger">
                                                                <i class="fas fa-exclamation-triangle"></i> 
                                                                Reste: {{ number_format($paiement->montant_restant, 0, ',', ' ') }} FCFA
                                                            </small>
                                                        @else
                                                            <!-- Paiement complet avec avance -->
                                                            <small class="text-success">
                                                                <i class="fas fa-check-circle"></i> 
                                                                Paiement complet
                                                            </small>
                                                        @endif
                                                    @endif
                                                </div>
                                                
                                                @if($paiement->montant_restant > 0)
                                                    <span class="badge bg-warning ms-2">
                                                        <i class="fas fa-hourglass-half"></i> Partiel
                                                    </span>
                                                @elseif($paiement->montant_avance > 0)
                                                    <span class="badge bg-success ms-2">
                                                        <i class="fas fa-check-circle"></i> Complet
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @switch($paiement->mode_paiement)
                                                @case('cash')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-money-bill"></i> Cash
                                                    </span>
                                                    @break
                                                @case('wave')
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-mobile-alt"></i> Wave
                                                    </span>
                                                    @break
                                                @case('orange_money')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-mobile-alt"></i> Orange Money
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($paiement->collecteur)
                                                {{ $paiement->collecteur->name }}<br>
                                                @if($paiement->collecteur->zone)
                                                    <small class="text-muted">{{ $paiement->collecteur->zone->nom }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Collecteur non assigné</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($paiement->statut)
                                                @case('en_attente')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> En attente
                                                    </span>
                                                    @break
                                                @case('valide')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Validé
                                                    </span>
                                                    @break
                                                @case('annule')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times"></i> Annulé
                                                    </span>
                                                    @break
                                                @case('correction_demandee')
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-question-circle"></i> Correction demandée
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($paiement->statut) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($paiement->unite)
                                                    <a href="{{ route('unites.show', $paiement->unite_id) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="Voir l'unité">
                                                        <i class="fas fa-store"></i>
                                                    </a>
                                                @endif
                                                @if($paiement->statut === 'valide')
                                                    <a href="{{ route('paiements.ticket', $paiement->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Générer le reçu" target="_blank">
                                                        <i class="fas fa-receipt"></i>
                                                    </a>
                                                @elseif($paiement->statut === 'en_attente')
                                                    <a href="{{ route('paiements.ticket', $paiement->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Générer le reçu" target="_blank">
                                                        <i class="fas fa-receipt"></i>
                                                    </a>
                                                    @if(in_array(auth()->user()->role, ['collecteur', 'gestionnaire']))
                                                        @if(auth()->user()->role === 'collecteur')
                                                            <a href="{{ route('collecteur.paiements.add-payment', $paiement->id) }}" 
                                                               class="btn btn-sm btn-warning" title="Finaliser le paiement">
                                                                <i class="fas fa-check-circle"></i>
                                                            </a>
                                                        @else
                                                            <a href="{{ route('paiements.add-payment', $paiement->id) }}" 
                                                               class="btn btn-sm btn-warning" title="Finaliser le paiement">
                                                                <i class="fas fa-check-circle"></i>
                                                            </a>
                                                        @endif
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-money-check-alt fa-3x text-muted mb-3"></i>
                                            <h4 class="text-muted">Aucun paiement</h4>
                                            <p class="text-muted">
                                                @if(auth()->user()->role === 'collecteur')
                                                    Commencez par enregistrer votre premier paiement
                                                @else
                                                    Aucun paiement n'a été enregistré pour le moment
                                                @endif
                                            </p>
                                            @if(auth()->user()->role === 'collecteur')
                                                <a href="{{ route('paiements.create') }}" class="btn btn-success">
                                                    <i class="fas fa-plus"></i>
                                                    Enregistrer un paiement
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($paiements->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $paiements->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏢 SICOSA - Script de rafraîchissement automatique chargé (DIRECT)');
    
    // Vérifier si on vient d'une finalisation de paiement
    const urlParams = new URLSearchParams(window.location.search);
    const paymentFinalized = urlParams.get('payment_finalized');
    const paymentId = urlParams.get('payment_id');
    
    console.log('Paramètres URL détectés:', { paymentFinalized, paymentId });
    
    if (paymentFinalized === 'true' && paymentId) {
        console.log('✅ Finalisation de paiement détectée - ID:', paymentId);
        
        // Afficher un message de succès
        showSuccessMessage('Paiement finalisé avec succès ! La page va se rafraîchir automatiquement.');
        
        // Rafraîchir la page après 1 seconde pour voir les changements plus rapidement
        setTimeout(function() {
            console.log('🔄 Rafraîchissement automatique en cours...');
            
            // Supprimer les paramètres URL pour éviter le rechargement en boucle
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.delete('payment_finalized');
            currentUrl.searchParams.delete('payment_id');
            
            console.log('Nouvelle URL:', currentUrl.toString());
            window.location.href = currentUrl.toString();
        }, 1000); // Réduit à 1 seconde
    }
    
    // Fonction pour afficher un message de succès
    function showSuccessMessage(message) {
        console.log('📢 Affichage du message:', message);
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insérer l'alerte en haut de la page
        const cardBody = document.querySelector('.card-body');
        if (cardBody) {
            cardBody.insertBefore(alertDiv, cardBody.firstChild);
            console.log('✅ Message de succès affiché');
        } else {
            console.error('❌ Impossible de trouver .card-body pour afficher le message');
        }
    }
    
    // Rafraîchissement automatique plus fréquent pour les paiements en attente
    const hasPendingPayments = document.querySelectorAll('tr').length > 1; // Plus que l'en-tête
    if (hasPendingPayments) {
        console.log('🔄 Auto-refresh activé pour les paiements en attente');
        
        // Rafraîchissement toutes les 5 secondes pour les collecteurs (plus agressif)
        const refreshInterval = window.location.pathname.includes('/collecteur/') ? 5000 : 10000;
        console.log('🔄 Intervalle de rafraîchissement:', refreshInterval + 'ms');
        
        setInterval(function() {
            // Vérifier s'il y a des paiements en attente
            const pendingPayments = document.querySelectorAll('.badge.bg-warning');
            if (pendingPayments.length > 0) {
                console.log('🔄 Rafraîchissement silencieux détecté - paiements en attente:', pendingPayments.length);
                // Rafraîchir silencieusement la page
                window.location.reload();
            }
        }, refreshInterval);
        
        // Rafraîchissement immédiat si on détecte des changements (plus rapide pour collecteurs)
        const initialRefreshDelay = window.location.pathname.includes('/collecteur/') ? 2000 : 5000;
        setTimeout(function() {
            console.log('🔄 Rafraîchissement initial après', initialRefreshDelay + 'ms');
            window.location.reload();
        }, initialRefreshDelay);
        
        // Rafraîchissement supplémentaire pour les collecteurs après 10 secondes
        if (window.location.pathname.includes('/collecteur/')) {
            setTimeout(function() {
                console.log('🔄 Rafraîchissement supplémentaire collecteur après 10 secondes');
                window.location.reload();
            }, 10000);
        }
    }
    
    // Test de débogage - afficher les informations dans la console
    console.log('🔍 Informations de débogage:');
    console.log('- URL actuelle:', window.location.href);
    console.log('- Paramètres détectés:', Object.fromEntries(urlParams));
    console.log('- Nombre de lignes de tableau:', document.querySelectorAll('tr').length);
    console.log('- Paiements en attente:', document.querySelectorAll('.badge.bg-warning').length);
    
    // Ajouter un bouton de rafraîchissement manuel
    addRefreshButton();
});

// Fonction pour ajouter un bouton de rafraîchissement manuel
function addRefreshButton() {
    const cardHeader = document.querySelector('.card-header');
    if (cardHeader) {
        const refreshBtn = document.createElement('button');
        refreshBtn.className = 'btn btn-outline-primary btn-sm ms-2';
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i>Actualiser';
        refreshBtn.onclick = function() {
            console.log('🔄 Rafraîchissement manuel demandé');
            window.location.reload();
        };
        
        const btnGroup = cardHeader.querySelector('.btn-group');
        if (btnGroup) {
            btnGroup.appendChild(refreshBtn);
        }
    }
}
</script>