@extends('layouts.app')

@section('title', 'Rapport Comparatif')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-balance-scale"></i>
                        Rapport Comparatif
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">
                            Comparaison : {{ $periodeBase ?? 'Période Base' }} vs {{ $periodeComparaison ?? 'Période Comparaison' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Résumé comparatif -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-chart-line"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Évolution Recettes</span>
                                    <span class="info-box-number">{{ number_format($evolutionRecettes ?? 0, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Évolution Paiements</span>
                                    <span class="info-box-number">{{ number_format($evolutionPaiements ?? 0, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-percentage"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Évolution Taux</span>
                                    <span class="info-box-number">{{ number_format($evolutionTaux ?? 0, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Moyenne</span>
                                    <span class="info-box-number">{{ number_format($evolutionMoyenne ?? 0, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau comparatif -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-table"></i>
                                        Comparaison Détaillée
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Métrique</th>
                                                    <th>{{ $periodeBase ?? 'Période Base' }}</th>
                                                    <th>{{ $periodeComparaison ?? 'Période Comparaison' }}</th>
                                                    <th>Évolution</th>
                                                    <th>Tendance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Recettes totales</strong></td>
                                                    <td>{{ number_format($donneesBase['total_recettes'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                                    <td>{{ number_format($donneesComp['total_recettes'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                                    <td>
                                                        @if(($comparaisons['recettes_totales']['pourcentage'] ?? 0) > 0)
                                                            <span class="badge badge-success">+{{ number_format($comparaisons['recettes_totales']['pourcentage'] ?? 0, 1) }}%</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ number_format($comparaisons['recettes_totales']['pourcentage'] ?? 0, 1) }}%</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(($comparaisons['recettes_totales']['pourcentage'] ?? 0) > 0)
                                                            <i class="fas fa-arrow-up text-success"></i> Hausse
                                                        @elseif(($comparaisons['recettes_totales']['pourcentage'] ?? 0) < 0)
                                                            <i class="fas fa-arrow-down text-danger"></i> Baisse
                                                        @else
                                                            <i class="fas fa-minus text-muted"></i> Stable
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Nombre de paiements</strong></td>
                                                    <td>{{ $donneesBase['total_paiements'] ?? 0 }}</td>
                                                    <td>{{ $donneesComp['total_paiements'] ?? 0 }}</td>
                                                    <td>
                                                        @if(($comparaisons['nombre_paiements']['pourcentage'] ?? 0) > 0)
                                                            <span class="badge badge-success">+{{ number_format($comparaisons['nombre_paiements']['pourcentage'] ?? 0, 1) }}%</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ number_format($comparaisons['nombre_paiements']['pourcentage'] ?? 0, 1) }}%</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(($comparaisons['nombre_paiements']['pourcentage'] ?? 0) > 0)
                                                            <i class="fas fa-arrow-up text-success"></i> Hausse
                                                        @elseif(($comparaisons['nombre_paiements']['pourcentage'] ?? 0) < 0)
                                                            <i class="fas fa-arrow-down text-danger"></i> Baisse
                                                        @else
                                                            <i class="fas fa-minus text-muted"></i> Stable
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Montant moyen</strong></td>
                                                    <td>{{ number_format($donneesBase['moyenne_par_paiement'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                                    <td>{{ number_format($donneesComp['moyenne_par_paiement'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                                    <td>
                                                        @if(($comparaisons['moyenne_paiement']['pourcentage'] ?? 0) > 0)
                                                            <span class="badge badge-success">+{{ number_format($comparaisons['moyenne_paiement']['pourcentage'] ?? 0, 1) }}%</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ number_format($comparaisons['moyenne_paiement']['pourcentage'] ?? 0, 1) }}%</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(($comparaisons['moyenne_paiement']['pourcentage'] ?? 0) > 0)
                                                            <i class="fas fa-arrow-up text-success"></i> Hausse
                                                        @elseif(($comparaisons['moyenne_paiement']['pourcentage'] ?? 0) < 0)
                                                            <i class="fas fa-arrow-down text-danger"></i> Baisse
                                                        @else
                                                            <i class="fas fa-minus text-muted"></i> Stable
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphiques comparatifs -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-bar"></i>
                                        Comparaison des Recettes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartComparaison" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-pie"></i>
                                        Répartition des Évolutions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartEvolutions" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analyse et recommandations -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-lightbulb"></i>
                                        Analyse et Recommandations
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-chart-line text-primary"></i> Points Positifs</h6>
                                            <ul class="list-group list-group-flush">
                                                @if(($evolutionRecettes ?? 0) > 0)
                                                    <li class="list-group-item">
                                                        <i class="fas fa-check text-success"></i>
                                                        Augmentation des recettes de {{ number_format($evolutionRecettes ?? 0, 1) }}%
                                                    </li>
                                                @endif
                                                @if(($evolutionTaux ?? 0) > 0)
                                                    <li class="list-group-item">
                                                        <i class="fas fa-check text-success"></i>
                                                        Amélioration du taux de recouvrement de {{ number_format($evolutionTaux ?? 0, 1) }}%
                                                    </li>
                                                @endif
                                                @if(($evolutionPaiements ?? 0) > 0)
                                                    <li class="list-group-item">
                                                        <i class="fas fa-check text-success"></i>
                                                        Hausse du nombre de transactions de {{ number_format($evolutionPaiements ?? 0, 1) }}%
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-exclamation-triangle text-warning"></i> Points d'Amélioration</h6>
                                            <ul class="list-group list-group-flush">
                                                @if(($evolutionRecettes ?? 0) < 0)
                                                    <li class="list-group-item">
                                                        <i class="fas fa-arrow-down text-danger"></i>
                                                        Baisse des recettes de {{ number_format(abs($evolutionRecettes ?? 0), 1) }}%
                                                    </li>
                                                @endif
                                                @if(($evolutionTaux ?? 0) < 0)
                                                    <li class="list-group-item">
                                                        <i class="fas fa-arrow-down text-danger"></i>
                                                        Dégradation du taux de recouvrement de {{ number_format(abs($evolutionTaux ?? 0), 1) }}%
                                                    </li>
                                                @endif
                                                @if(($evolutionMoyenne ?? 0) < 0)
                                                    <li class="list-group-item">
                                                        <i class="fas fa-arrow-down text-danger"></i>
                                                        Diminution du montant moyen de {{ number_format(abs($evolutionMoyenne ?? 0), 1) }}%
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i>
                                Rapport généré le {{ now()->format('d/m/Y à H:i') }}
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('rapports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fas fa-print"></i> Imprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données pour les graphiques
    const periodes = ['{{ $periodeBase ?? "Période Base" }}', '{{ $periodeComparaison ?? "Période Comparaison" }}'];
    const recettes = [{{ $donneesBase['total_recettes'] ?? 0 }}, {{ $donneesComp['total_recettes'] ?? 0 }}];
    const paiements = [{{ $donneesBase['total_paiements'] ?? 0 }}, {{ $donneesComp['total_paiements'] ?? 0 }}];

    // Graphique de comparaison
    const ctxComparaison = document.getElementById('chartComparaison').getContext('2d');
    new Chart(ctxComparaison, {
        type: 'bar',
        data: {
            labels: periodes,
            datasets: [{
                label: 'Recettes (FCFA)',
                data: recettes,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Nombre de Paiements',
                data: paiements,
                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Comparaison des Performances'
                }
            }
        }
    });

    // Graphique des évolutions
    const ctxEvolutions = document.getElementById('chartEvolutions').getContext('2d');
    new Chart(ctxEvolutions, {
        type: 'doughnut',
        data: {
            labels: ['Recettes', 'Paiements', 'Taux', 'Moyenne'],
            datasets: [{
                data: [{{ $evolutionRecettes ?? 0 }}, {{ $evolutionPaiements ?? 0 }}, {{ $evolutionTaux ?? 0 }}, {{ $evolutionMoyenne ?? 0 }}],
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Évolutions (%)'
                }
            }
        }
    });
});
</script>

<style>
@media print {
    .card-tools, .btn {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}

.info-box {
    display: flex;
    min-height: 80px;
    background: #fff;
    width: 100%;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    margin-bottom: 1rem;
}

.info-box-icon {
    border-radius: 0.25rem 0 0 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.875rem;
    font-weight: 700;
    text-align: center;
    text-transform: uppercase;
    width: 70px;
    color: #fff;
}

.info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
}

.info-box-text {
    display: block;
    font-size: 1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
}

.info-box-number {
    display: block;
    font-weight: 700;
}
</style>
@endsection 