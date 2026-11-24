@extends('layouts.app')

@section('title', 'Tableau de bord - SICOSA')

@section('content')
<div class="container-fluid py-2 pb-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 mb-0 text-gray-800">
            <i class="fas fa-tachometer-alt me-2"></i>
            Tableau de bord
        </h1>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('profil.edit') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-user-cog me-1"></i>
                Mon Profil
            </a>
            <span class="text-muted small">
                <i class="fas fa-calendar me-1"></i>
                {{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="row g-2 mb-2">
        <!-- Total unités commerciales -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-6 mb-2">
            <div class="card border-0 shadow-sm h-100" style="min-height: 80px;">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small">Unités Actives</h6>
                            <h3 class="mb-0">{{ $stats['total_unites'] }}</h3>
                            <small class="text-muted">Commerces recensés</small>
                        </div>
                        <div class="rounded-circle p-2 bg-primary bg-opacity-10">
                            <i class="fas fa-store fa-lg text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total paiements -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-6 mb-2">
            <div class="card border-0 shadow-sm h-100" style="min-height: 80px;">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small">Paiements ce mois</h6>
                            <h3 class="mb-0">{{ $stats['total_paiements'] }}</h3>
                            <small class="text-muted">{{ date('F Y') }}</small>
                        </div>
                        <div class="rounded-circle p-2 bg-success bg-opacity-10">
                            <i class="fas fa-money-bill-wave fa-lg text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Montant total -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-6 mb-2">
            <div class="card border-0 shadow-sm h-100" style="min-height: 80px;">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small">Recettes du mois</h6>
                            <h3 class="mb-0">{{ number_format($stats['montant_total'], 0, ',', ' ') }} FCFA</h3>
                            <small class="text-muted">{{ date('F Y') }}</small>
                        </div>
                        <div class="rounded-circle p-2 bg-info bg-opacity-10">
                            <i class="fas fa-coins fa-lg text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total collecteurs -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-6 mb-2">
            <div class="card border-0 shadow-sm h-100" style="min-height: 80px;">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 small">Collecteurs Actifs</h6>
                            <h3 class="mb-0">{{ $stats['total_collecteurs'] }}</h3>
                            <small class="text-muted">Sur le terrain</small>
                        </div>
                        <div class="rounded-circle p-2 bg-secondary bg-opacity-10">
                            <i class="fas fa-user-tie fa-lg text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerte de retards de paiement -->
    @if($unitesEnRetard > 0)
        <div class="alert alert-warning alert-dismissible fade show mb-2 py-2" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> {{ $unitesEnRetard }} unité(s) commerciale(s) en retard de paiement.
                    <small class="d-block text-muted mt-1">
                        Des unités n'ont pas payé leurs taxes selon la périodicité définie.
                    </small>
                </div>
                <div class="ms-2">
                    <a href="{{ route('alerts.payment-overdue') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-eye me-1"></i>
                        Voir les détails
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    <!-- Graphique dynamique : Montant par zone -->
    <div class="row mb-2">
        <div class="col-xl-12">
            <div class="card border-0 shadow-sm" style="height: 200px;">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        Répartition des paiements par zone
                    </h6>
                </div>
                <div class="card-body p-2">
                    <canvas id="paiementsParZoneChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2" style="height: calc(100vh - 500px); overflow: hidden;">
        <!-- Derniers paiements -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2 text-success"></i>
                        Paiements Récents (7 derniers jours)
                    </h6>
                </div>
                <div class="card-body p-2" style="overflow-y: auto; height: calc(100% - 50px);">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Unité</th>
                                    <th>Montant</th>
                                    <th>Collecteur</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersPaiements as $paiement)
                                    <tr>
                                        <td>{{ $paiement->date_paiement ? $paiement->date_paiement->format('d/m/Y') : 'N/A' }}</td>
                                        <td>{{ $paiement->unite ? $paiement->unite->nom_complet_proprietaire : 'N/A' }}</td>
                                        <td>
                                            @if($paiement->statut === 'valide')
                                                <span class="badge bg-success">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</span>
                                            @elseif($paiement->statut === 'en_attente')
                                                <span class="badge bg-warning">{{ number_format($paiement->montant_avance, 0, ',', ' ') }} FCFA</span>
                                                <small class="text-muted d-block">Sur {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</small>
                                            @else
                                                <span class="badge bg-secondary">{{ number_format($paiement->montant_avance, 0, ',', ' ') }} FCFA</span>
                                            @endif
                                        </td>
                                        <td>{{ $paiement->collecteur ? $paiement->collecteur->name : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucun paiement récent</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières unités -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0">
                        <i class="fas fa-store me-2 text-primary"></i>
                        Nouveaux Recensements (30 derniers jours)
                    </h6>
                </div>
                <div class="card-body p-2" style="overflow-y: auto; height: calc(100% - 50px);">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Propriétaire</th>
                                    <th>Zone</th>
                                    <th>Taxe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dernieresUnites as $unite)
                                    <tr>
                                        <td>{{ $unite->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $unite->nom_complet_proprietaire }}</td>
                                        <td>{{ $unite->zone ? $unite->zone->nom : 'N/A' }}</td>
                                        <td>
                                            @if($unite->taxe)
                                                {{ number_format($unite->taxe->montant, 0, ',', ' ') }} FCFA
                                            @else
                                                <span class="text-muted">Non définie</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucune unité récente</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row g-2 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>
                        Actions Rapides
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        <!-- Suivi des collecteurs -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('tracking.live') }}" class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center p-2 text-decoration-none">
                                <i class="fas fa-map-marked-alt fa-lg mb-1"></i>
                                <h6 class="mb-1 small">Suivi Collecteurs</h6>
                                <small class="text-muted">Tracer les itinéraires</small>
                            </a>
                        </div>
                        
                        <!-- Gestion des paramètres de commerce -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('commerce-parameters.index') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-2 text-decoration-none">
                                <i class="fas fa-cogs fa-lg mb-1"></i>
                                <h6 class="mb-1 small">Paramètres Commerce</h6>
                                <small class="text-muted">Gérer types & natures</small>
                            </a>
                        </div>
                        
                        <!-- Gestion des collecteurs -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('collecteurs.index') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-2 text-decoration-none">
                                <i class="fas fa-users fa-lg mb-1"></i>
                                <h6 class="mb-1 small">Collecteurs</h6>
                                <small class="text-muted">Gérer les utilisateurs</small>
                            </a>
                        </div>
                        
                        <!-- Gestion des zones -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('zones.index') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-2 text-decoration-none">
                                <i class="fas fa-map-marked-alt fa-lg mb-1"></i>
                                <h6 class="mb-1 small">Zones Fiscales</h6>
                                <small class="text-muted">Délimiter les zones</small>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Deuxième ligne d'actions -->
                    <div class="row g-2 mt-2">
                        <!-- Gestion des taxes -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('taxes.index') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-2 text-decoration-none">
                                <i class="fas fa-receipt fa-lg mb-1"></i>
                                <h6 class="mb-1 small">Taxes</h6>
                                <small class="text-muted">Configurer les tarifs</small>
                            </a>
                        </div>
                        
                        <!-- Voir tous les paiements -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('paiements.index') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-2 text-decoration-none">
                                <i class="fas fa-money-bill-wave fa-lg mb-1"></i>
                                <h6 class="mb-1 small">Tous les Paiements</h6>
                                <small class="text-muted">Historique complet</small>
                            </a>
                        </div>
                        
                        <!-- Voir toutes les unités -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('unites.index') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-2 text-decoration-none">
                                <i class="fas fa-store fa-lg mb-1"></i>
                                <h6 class="mb-1 small">Toutes les Unités</h6>
                                <small class="text-muted">Recensements</small>
                            </a>
                        </div>
                        
                        <!-- Rapports -->
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('rapports.index') }}" class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-2 text-decoration-none">
                                <i class="fas fa-chart-line fa-lg mb-1"></i>
                                <h6 class="mb-1 small">Rapports</h6>
                                <small class="text-muted">Analyses & statistiques</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles optimisés pour éviter le défilement */
.container-fluid {
    height: 100vh;
    overflow: hidden;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.table th {
    font-weight: 600;
    color: #4a5568;
    font-size: 0.8rem;
}

.table td {
    vertical-align: middle;
    font-size: 0.8rem;
}

.rounded-circle {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Responsive pour mobile */
@media (max-width: 768px) {
    .container-fluid {
        padding: 0.5rem;
    }
    
    .card-body {
        padding: 0.5rem !important;
    }
    
    .table th,
    .table td {
        font-size: 0.7rem;
        padding: 0.25rem;
    }
    
    .btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    
    .h4 {
        font-size: 1.1rem;
    }
    
    .h6 {
        font-size: 0.9rem;
    }
    
    .h3 {
        font-size: 1.5rem;
    }
}

/* Optimisation pour les petits écrans */
@media (max-height: 600px) {
    .container-fluid {
        padding: 0.25rem;
    }
    
    .mb-2 {
        margin-bottom: 0.25rem !important;
    }
    
    .py-2 {
        padding-top: 0.25rem !important;
        padding-bottom: 0.25rem !important;
    }
    
    .card-body {
        padding: 0.25rem !important;
    }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('paiementsParZoneChart').getContext('2d');
    const labels = [
        @foreach($statsParZone as $zone)
            @if(($zone->paiements_sum_montant ?? 0) > 0)
                '{{ $zone->nom }}',
            @endif
        @endforeach
    ];
    const data = [
        @foreach($statsParZone as $zone)
            @if(($zone->paiements_sum_montant ?? 0) > 0)
                {{ $zone->paiements_sum_montant ?? 0 }},
            @endif
        @endforeach
    ];
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Montant total (FCFA)',
                data: data,
                backgroundColor: [
                    '#1e3a8a', '#f59e0b', '#3b82f6', '#10b981', '#f97316', '#8b5cf6', '#14b8a6', '#ef4444', '#6366f1', '#22d3ee'
                ],
                borderRadius: 8,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' FCFA';
                        }
                    }
                }
            }
        }
    });

</script>
@endpush
