@extends('layouts.app')

@section('title', 'Tableau de bord - Gestionnaire')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tableau de bord Gestionnaire</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="text-muted">{{ now()->format('d/m/Y H:i') }}</span>
    </div>
</div>

<!-- Cartes de statistiques principales -->
<div class="row">
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            <span class="d-none d-sm-inline">Total unités commerciales</span>
                            <span class="d-sm-none">Unités totales</span>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($totalUnites, 0, ',', ' ') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-store fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            <span class="d-none d-sm-inline">Unités payantes</span>
                            <span class="d-sm-none">Payantes</span>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($unitesPayantes, 0, ',', ' ') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            <span class="d-none d-sm-inline">Unités non payantes</span>
                            <span class="d-sm-none">Non payantes</span>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($unitesNonPayantes, 0, ',', ' ') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            <span class="d-none d-sm-inline">Unités arriérés</span>
                            <span class="d-sm-none">Arriérés</span>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($unitesArrieres, 0, ',', ' ') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cartes de statistiques financières -->
<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Recettes du jour
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($totalPaiementsJour, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Recettes du mois
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($totalPaiementsMois, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                            Collecteurs actifs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $totalCollecteurs }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Paiements récents et nouveaux recensements -->
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-clipboard-list"></i> Nouveaux recensements
                </h6>
                <span class="badge bg-success">{{ $nouveauxRecensements->count() }} aujourd'hui</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Heure</th>
                                <th>Collecteur</th>
                                <th>Propriétaire</th>
                                <th>Type</th>
                                <th>Zone</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nouveauxRecensements as $unite)
                            <tr>
                                <td>{{ $unite->date_recensement ? $unite->date_recensement->format('H:i') : 'N/A' }}</td>
                                <td>
                                    <small>{{ $unite->collecteur ? $unite->collecteur->name : 'N/A' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $unite->nom_proprietaire }}</strong>
                                    @if($unite->prenom_proprietaire)
                                        <br><small class="text-muted">{{ $unite->prenom_proprietaire }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $unite->categorie === 'activite_commerciale' ? 'info' : 'warning' }}">
                                        {{ $unite->categorie === 'activite_commerciale' ? 'Commerce' : 'Point d\'intérêt' }}
                                    </span>
                                    <br><small>{{ $unite->nature }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $unite->zone ? $unite->zone->nom : 'N/A' }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('unites.show', $unite) }}" class="btn btn-sm btn-outline-success" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <i class="fas fa-clipboard-list"></i><br>
                                    Aucun nouveau recensement aujourd'hui
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($nouveauxRecensements->count() > 5)
                    <div class="text-center mt-2">
                        <a href="{{ route('unites.index') }}?date_recensement={{ now()->format('Y-m-d') }}" class="btn btn-sm btn-success">
                            Voir tous les recensements du jour
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Paiements récents</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Collecteur</th>
                                <th>Unité</th>
                                <th>Montant</th>
                                <th>Mode</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paiementsRecents as $paiement)
                            <tr>
                                <td>{{ $paiement->date_paiement->format('d/m') }}</td>
                                <td><small>{{ $paiement->collecteur->name }}</small></td>
                                <td><small>{{ $paiement->unite->nom_proprietaire }}</small></td>
                                <td>{{ number_format($paiement->montant, 0, ',', ' ') }}</td>
                                <td>
                                    @if($paiement->mode_paiement === 'cash')
                                        <span class="badge bg-success">Cash</span>
                                    @elseif($paiement->mode_paiement === 'wave')
                                        <span class="badge bg-info">Wave</span>
                                    @else
                                        <span class="badge bg-warning">Orange</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('paiements.show', $paiement) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Aucun paiement récent</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Paiements récents et actions rapides -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Paiements récents</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Collecteur</th>
                                <th>Unité commerciale</th>
                                <th>Montant</th>
                                <th>Mode</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paiementsRecents as $paiement)
                            <tr>
                                <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                <td>{{ $paiement->collecteur->name }}</td>
                                <td>{{ $paiement->unite->nom_proprietaire }}</td>
                                <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    @if($paiement->mode_paiement === 'cash')
                                        <span class="badge bg-success">Cash</span>
                                    @elseif($paiement->mode_paiement === 'wave')
                                        <span class="badge bg-info">Wave</span>
                                    @else
                                        <span class="badge bg-warning">Orange Money</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('paiements.show', $paiement) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Aucun paiement récent</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Actions rapides</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Créer un collecteur
                    </a>
                    <a href="{{ route('unites.create') }}" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Ajouter une unité commerciale
                    </a>
                    <a href="{{ route('zones.index') }}" class="btn btn-info">
                        <i class="fas fa-map-marked-alt"></i> Gérer les zones
                    </a>
                    <a href="{{ route('taxes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-coins"></i> Gérer les taxes
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Statistiques rapides</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">Zones fiscales : <strong>{{ $totalZones }}</strong></p>
                <p class="mb-2">Collecteurs : <strong>{{ $totalCollecteurs }}</strong></p>
                <p class="mb-0">Unités actives : <strong>{{ \App\Models\UniteCommerciale::actif()->count() }}</strong></p>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #007bff !important;
}
.border-left-success {
    border-left: 0.25rem solid #28a745 !important;
}
.border-left-info {
    border-left: 0.25rem solid #17a2b8 !important;
}
.border-left-warning {
    border-left: 0.25rem solid #ffc107 !important;
}
.border-left-danger {
    border-left: 0.25rem solid #dc3545 !important;
}
.border-left-secondary {
    border-left: 0.25rem solid #6c757d !important;
}
</style>
@endsection 