@extends('layouts.app')

@section('title', 'Rapport des Zones Fiscales')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marked-alt"></i>
                        Rapport des Zones Fiscales
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">
                            Période : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Résumé général -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-map"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Zones Analysées</span>
                                    <span class="info-box-number">{{ $performanceZones->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Recettes</span>
                                    <span class="info-box-number">{{ number_format($performanceZones->sum('total_recettes'), 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-store"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Unités</span>
                                    <span class="info-box-number">{{ $performanceZones->sum('nombre_unites') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Collecteurs</span>
                                    <span class="info-box-number">{{ $performanceZones->sum('nombre_collecteurs') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des performances par zone -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Zone</th>
                                    <th>Recettes (FCFA)</th>
                                    <th>Unités</th>
                                    <th>Collecteurs</th>
                                    <th>Recette/Unité</th>
                                    <th>Recette/Collecteur</th>
                                    <th>Taux Recouvrement</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($performanceZones as $zone)
                                <tr>
                                    <td>
                                        <strong>{{ $zone->nom }}</strong>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-success">
                                            {{ number_format($zone->total_recettes ?? 0, 0, ',', ' ') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $zone->nombre_unites ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-warning">{{ $zone->nombre_collecteurs ?? 0 }}</span>
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($zone->recette_par_unite ?? 0, 0, ',', ' ') }}
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($zone->recette_par_collecteur ?? 0, 0, ',', ' ') }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $taux = $zone->taux_recouvrement ?? 0;
                                            $badgeClass = $taux >= 80 ? 'badge-success' : ($taux >= 60 ? 'badge-warning' : 'badge-danger');
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ number_format($taux, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $performance = 0;
                                            if ($performanceZones->max('total_recettes') > 0) {
                                                $performance = (($zone->total_recettes ?? 0) / $performanceZones->max('total_recettes')) * 100;
                                            }
                                            $performanceClass = $performance >= 80 ? 'bg-success' : ($performance >= 60 ? 'bg-warning' : 'bg-danger');
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $performanceClass }}" 
                                                 style="width: {{ $performance }}%">
                                                {{ number_format($performance, 0) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Graphiques -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-pie"></i>
                                        Répartition des Recettes par Zone
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartRecettesZones" width="500" height="300" style="max-width: 500px; max-height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-bar"></i>
                                        Performance des Zones
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartPerformanceZones" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analyse détaillée -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line"></i>
                                        Analyse Comparative
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $meilleureZone = $performanceZones->sortByDesc('total_recettes')->first();
                                        $zoneMoinsPerf = $performanceZones->sortBy('total_recettes')->first();
                                    @endphp
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="alert alert-success">
                                                <h6><i class="fas fa-trophy"></i> Zone la plus performante</h6>
                                                <strong>{{ $meilleureZone->nom ?? 'N/A' }}</strong><br>
                                                Recettes : {{ number_format($meilleureZone->total_recettes ?? 0, 0, ',', ' ') }} FCFA<br>
                                                Taux recouvrement : {{ number_format($meilleureZone->taux_recouvrement ?? 0, 1) }}%
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="alert alert-warning">
                                                <h6><i class="fas fa-exclamation-triangle"></i> Zone à améliorer</h6>
                                                <strong>{{ $zoneMoinsPerf->nom ?? 'N/A' }}</strong><br>
                                                Recettes : {{ number_format($zoneMoinsPerf->total_recettes ?? 0, 0, ',', ' ') }} FCFA<br>
                                                Taux recouvrement : {{ number_format($zoneMoinsPerf->taux_recouvrement ?? 0, 1) }}%
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Recommandations -->
                                    <div class="mt-3">
                                        <h6><i class="fas fa-lightbulb"></i> Recommandations</h6>
                                        <ul class="list-group list-group-flush">
                                            @if($zoneMoinsPerf && $zoneMoinsPerf->taux_recouvrement < 60)
                                                <li class="list-group-item">
                                                    <i class="fas fa-arrow-up text-warning"></i>
                                                    Renforcer la collecte dans la zone <strong>{{ $zoneMoinsPerf->nom }}</strong> 
                                                    (taux de recouvrement faible : {{ number_format($zoneMoinsPerf->taux_recouvrement, 1) }}%)
                                                </li>
                                            @endif
                                            
                                            @if($performanceZones->where('nombre_collecteurs', 0)->count() > 0)
                                                <li class="list-group-item">
                                                    <i class="fas fa-user-plus text-info"></i>
                                                    Affecter des collecteurs aux zones sans collecteur actif
                                                </li>
                                            @endif
                                            
                                            @if($performanceZones->where('nombre_unites', 0)->count() > 0)
                                                <li class="list-group-item">
                                                    <i class="fas fa-store text-primary"></i>
                                                    Développer l'activité commerciale dans les zones sans unités
                                                </li>
                                            @endif
                                        </ul>
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
    const zones = @json($performanceZones->pluck('nom'));
    const recettes = @json($performanceZones->pluck('total_recettes'));
    const tauxRecouvrement = @json($performanceZones->pluck('taux_recouvrement'));

    // Graphique en camembert pour les recettes
    const ctxRecettes = document.getElementById('chartRecettesZones').getContext('2d');
    new Chart(ctxRecettes, {
        type: 'pie',
        data: {
            labels: zones,
            datasets: [{
                data: recettes,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
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
                    text: 'Répartition des Recettes'
                }
            }
        }
    });

    // Graphique en barres pour la performance
    const ctxPerformance = document.getElementById('chartPerformanceZones').getContext('2d');
    new Chart(ctxPerformance, {
        type: 'bar',
        data: {
            labels: zones,
            datasets: [{
                label: 'Taux de Recouvrement (%)',
                data: tauxRecouvrement,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Taux de Recouvrement par Zone'
                }
            }
        }
    });
});
</script>

<style>
@media print {
    .card-tools, .btn, .progress {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
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