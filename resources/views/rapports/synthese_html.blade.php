@extends('layouts.app')

@section('title', 'Rapport de Synthèse Exécutive')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Rapport de Synthèse Exécutive
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">
                            Période : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- KPIs principaux -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Recettes</span>
                                    <span class="info-box-number">{{ number_format($stats['total_recettes'] ?? 0, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-percentage"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Taux Recouvrement</span>
                                    <span class="info-box-number">{{ number_format($stats['taux_recouvrement'] ?? 0, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-store"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Unités Actives</span>
                                    <span class="info-box-number">{{ $stats['unites_actives'] ?? 0 }}/{{ $stats['total_unites'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paiements Valides</span>
                                    <span class="info-box-number">{{ $stats['paiements_valides'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Résumé exécutif -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-clipboard-list"></i>
                                        Résumé Exécutif
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Points Clés</h6>
                                        <ul>
                                            <li>Période analysée : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}</li>
                                            <li>Total des recettes collectées : {{ number_format($stats['total_recettes'] ?? 0, 0, ',', ' ') }} FCFA</li>
                                            <li>Taux de recouvrement global : {{ number_format($stats['taux_recouvrement'] ?? 0, 1) }}%</li>
                                            <li>Nombre d'unités commerciales actives : {{ $stats['unites_actives'] ?? 0 }} sur {{ $stats['total_unites'] ?? 0 }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphiques -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-pie"></i>
                                        Répartition des Recettes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(empty($performanceZones) || (method_exists($performanceZones, 'isEmpty') && $performanceZones->isEmpty()) || (method_exists($performanceZones, 'sum') && $performanceZones->sum('total_recettes') == 0))
                                        <div class="alert alert-info text-center my-3">
                                            <i class="fas fa-info-circle"></i> Aucune donnée disponible pour la répartition des recettes sur cette période.
                                        </div>
                                    @else
                                        <canvas id="chartRecettes" width="400" height="200"></canvas>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-bar"></i>
                                        Évolution des Recettes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(empty($recettesEvolution) || $recettesEvolution->isEmpty())
                                        <div class="alert alert-info text-center my-3">
                                            <i class="fas fa-info-circle"></i> Aucune donnée d'évolution disponible pour cette période.
                                        </div>
                                    @else
                                        <canvas id="chartEvolution" width="400" height="200"></canvas>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recommandations -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-lightbulb"></i>
                                        Recommandations Stratégiques
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-arrow-up text-success"></i> Actions Prioritaires</h6>
                                            <ul class="list-group list-group-flush">
                                                @if(($stats['taux_recouvrement'] ?? 0) < 80)
                                                    <li class="list-group-item">
                                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                                        Améliorer le taux de recouvrement (actuellement {{ number_format($stats['taux_recouvrement'] ?? 0, 1) }}%)
                                                    </li>
                                                @endif
                                                @if(($stats['unites_actives'] ?? 0) < ($stats['total_unites'] ?? 0) * 0.8)
                                                    <li class="list-group-item">
                                                        <i class="fas fa-store text-info"></i>
                                                        Augmenter le nombre d'unités commerciales actives
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-chart-line text-primary"></i> Objectifs</h6>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">
                                                    <i class="fas fa-target text-success"></i>
                                                    Atteindre un taux de recouvrement de 85%
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-target text-success"></i>
                                                    Augmenter les recettes de 15% le mois prochain
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="fas fa-target text-success"></i>
                                                    Réduire les paiements annulés de 50%
                                                </li>
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