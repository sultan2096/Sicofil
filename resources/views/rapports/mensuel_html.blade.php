@extends('layouts.app')

@section('title', 'Rapport Mensuel')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt"></i>
                        Rapport Mensuel
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">
                            Mois : {{ $dateDebut->format('F Y') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Résumé mensuel -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Recettes du Mois</span>
                                    <span class="info-box-number">{{ number_format($donneesActuelles['total_recettes'] ?? 0, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-chart-line"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Croissance</span>
                                    <span class="info-box-number">{{ number_format($croissance ?? 0, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-percentage"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Taux Recouvrement</span>
                                    <span class="info-box-number">{{ number_format($donneesActuelles['taux_reussite'] ?? 0, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paiements</span>
                                    <span class="info-box-number">{{ $donneesActuelles['total_paiements'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Évolution quotidienne -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line"></i>
                                        Évolution Quotidienne des Recettes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartEvolution" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des performances -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-table"></i>
                                        Détails des Performances
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Métrique</th>
                                                    <th>Valeur</th>
                                                    <th>Évolution</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Recettes totales</td>
                                                    <td>{{ number_format($donneesActuelles['total_recettes'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                                    <td>
                                                        @if(($croissance ?? 0) > 0)
                                                            <span class="badge badge-success">+{{ number_format($croissance ?? 0, 1) }}%</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ number_format($croissance ?? 0, 1) }}%</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Nombre de paiements</td>
                                                    <td>{{ $donneesActuelles['total_paiements'] ?? 0 }}</td>
                                                    <td>
                                                        @if(($evolutionPaiements ?? 0) > 0)
                                                            <span class="badge badge-success">+{{ number_format($evolutionPaiements ?? 0, 1) }}%</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ number_format($evolutionPaiements ?? 0, 1) }}%</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Montant moyen</td>
                                                    <td>{{ number_format($donneesActuelles['moyenne_par_paiement'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                                    <td>
                                                        @if(($evolutionMoyenne ?? 0) > 0)
                                                            <span class="badge badge-success">+{{ number_format($evolutionMoyenne ?? 0, 1) }}%</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ number_format($evolutionMoyenne ?? 0, 1) }}%</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Taux de recouvrement</td>
                                                    <td>{{ number_format($donneesActuelles['taux_reussite'] ?? 0, 1) }}%</td>
                                                    <td>
                                                        @if(($evolutionTaux ?? 0) > 0)
                                                            <span class="badge badge-success">+{{ number_format($evolutionTaux ?? 0, 1) }}%</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ number_format($evolutionTaux ?? 0, 1) }}%</span>
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

                    <!-- Prévisions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-crystal-ball"></i>
                                        Prévisions pour le Mois Prochain
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="alert alert-info">
                                                <h6><i class="fas fa-chart-line"></i> Prévision Recettes</h6>
                                                <strong>{{ number_format($previsionsMoisSuivant['recettes_prevues'] ?? 0, 0, ',', ' ') }} FCFA</strong><br>
                                                <small>Basé sur la tendance actuelle</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="alert alert-warning">
                                                <h6><i class="fas fa-users"></i> Prévision Paiements</h6>
                                                <strong>{{ $previsionsMoisSuivant['nombre_paiements_prevus'] ?? 0 }}</strong><br>
                                                <small>Nombre estimé de transactions</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="alert alert-success">
                                                <h6><i class="fas fa-target"></i> Objectif</h6>
                                                <strong>{{ number_format($objectifs['recettes'] ?? 0, 0, ',', ' ') }} FCFA</strong><br>
                                                <small>Objectif mensuel fixé</small>
                                            </div>
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
    // Données pour le graphique d'évolution
    const evolutionData = @json($evolutionRecettes ?? []);
    const labels = evolutionData.map(item => item.periode);
    const recettes = evolutionData.map(item => item.total);

    // Graphique d'évolution
    const ctxEvolution = document.getElementById('chartEvolution').getContext('2d');
    new Chart(ctxEvolution, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Recettes (FCFA)',
                data: recettes,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
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
                    text: 'Évolution des Recettes en {{ $dateDebut->format("F Y") }}'
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