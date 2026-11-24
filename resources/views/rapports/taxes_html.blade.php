@extends('layouts.app')

@section('title', 'Rapport des Taxes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i>
                        Rapport des Taxes
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
                                    <i class="fas fa-receipt"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Types de Taxes</span>
                                    <span class="info-box-number">{{ $taxes->count() ?? 0 }}</span>
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
                                    <span class="info-box-number">{{ number_format($taxes->sum('revenus_totaux') ?? 0, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-chart-pie"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Moyenne par Taxe</span>
                                    <span class="info-box-number">{{ number_format($taxes->avg('revenus_totaux') ?? 0, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-percentage"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Paiements</span>
                                    <span class="info-box-number">{{ $taxes->sum('nombre_paiements') ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des taxes -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Taxe</th>
                                    <th>Montant Unitaire</th>
                                    <th>Recettes (FCFA)</th>
                                    <th>Nombre Paiements</th>
                                    <th>Moyenne/Paiement</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($taxes as $taxe)
                                <tr>
                                    <td>
                                        <strong>{{ $taxe->nom }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ number_format($taxe->montant_unitaire ?? 0, 0, ',', ' ') }} FCFA</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-success">
                                            {{ number_format($taxe->revenus_totaux ?? 0, 0, ',', ' ') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-warning">{{ $taxe->nombre_paiements ?? 0 }}</span>
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($taxe->montant_moyen ?? 0, 0, ',', ' ') }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $performance = 0;
                                            if (($taxes->max('revenus_totaux') ?? 0) > 0) {
                                                $performance = (($taxe->revenus_totaux ?? 0) / ($taxes->max('revenus_totaux') ?? 1)) * 100;
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
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Aucune donnée de taxe disponible pour cette période
                                    </td>
                                </tr>
                                @endforelse
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
                                        Répartition des Recettes par Taxe
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartRecettesTaxes" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-bar"></i>
                                        Performance des Taxes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartPerformanceTaxes" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analyse -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line"></i>
                                        Analyse des Taxes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $meilleureTaxe = $taxes->sortByDesc('revenus_totaux')->first();
                                        $taxeMoinsPerf = $taxes->sortBy('revenus_totaux')->first();
                                    @endphp
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="alert alert-success">
                                                <h6><i class="fas fa-trophy"></i> Taxe la plus performante</h6>
                                                <strong>{{ $meilleureTaxe->nom ?? 'N/A' }}</strong><br>
                                                Recettes : {{ number_format($meilleureTaxe->revenus_totaux ?? 0, 0, ',', ' ') }} FCFA<br>
                                                Montant unitaire : {{ number_format($meilleureTaxe->montant_unitaire ?? 0, 0, ',', ' ') }} FCFA
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="alert alert-warning">
                                                <h6><i class="fas fa-exclamation-triangle"></i> Taxe à améliorer</h6>
                                                <strong>{{ $taxeMoinsPerf->nom ?? 'N/A' }}</strong><br>
                                                Recettes : {{ number_format($taxeMoinsPerf->revenus_totaux ?? 0, 0, ',', ' ') }} FCFA<br>
                                                Montant unitaire : {{ number_format($taxeMoinsPerf->montant_unitaire ?? 0, 0, ',', ' ') }} FCFA
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
    // Données pour les graphiques
    const taxes = @json($taxes->pluck('nom') ?? []);
    const recettes = @json($taxes->pluck('revenus_totaux') ?? []);
    const paiements = @json($taxes->pluck('nombre_paiements') ?? []);

    // Graphique camembert des recettes
    const ctxRecettes = document.getElementById('chartRecettesTaxes').getContext('2d');
    new Chart(ctxRecettes, {
        type: 'pie',
        data: {
            labels: taxes,
            datasets: [{
                data: recettes,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Répartition des Recettes par Taxe'
                }
            }
        }
    });

    // Graphique barres de performance
    const ctxPerformance = document.getElementById('chartPerformanceTaxes').getContext('2d');
    new Chart(ctxPerformance, {
        type: 'bar',
        data: {
            labels: taxes,
            datasets: [{
                label: 'Nombre de Paiements',
                data: paiements,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
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
                    text: 'Performance des Taxes'
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