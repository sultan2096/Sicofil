@extends('layouts.app')

@section('title', 'Tableau de bord - Maire')

@push('styles')
<style>
    .dashboard-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
        height: 120px;
    }
    
    /* Responsive pour mobile */
    @media (max-width: 768px) {
        .stat-card {
            height: auto;
            min-height: 100px;
            padding: 0.75rem;
        }
        
        .stat-number {
            font-size: 1.25rem;
        }
        
        .stat-icon {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }
        
        .dashboard-hero {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .chart-container {
            padding: 0.75rem;
        }
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 0.5rem;
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.2rem;
        line-height: 1;
    }
    
    .stat-label {
        color: #718096;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1.2;
    }
    
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 0.5rem;
        border: none;
        height: fit-content;
    }
    
    .chart-header {
        display: flex;
        align-items: center;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .chart-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }
    
    .chart-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.5rem;
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        font-size: 12px;
    }
    
    .compact-section {
        margin-bottom: 1rem;
    }
    
    .tabs-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 1rem;
    }
    
    .nav-tabs-modern {
        border: none;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 0;
        margin: 0;
    }
    
    .nav-tabs-modern .nav-link {
        border: none;
        color: rgba(255,255,255,0.8);
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
        border-radius: 0;
        font-size: 0.8rem;
    }
    
    .nav-tabs-modern .nav-link:hover {
        color: white;
        background: rgba(255,255,255,0.1);
    }
    
    .nav-tabs-modern .nav-link.active {
        color: white;
        background: rgba(255,255,255,0.2);
        border-bottom: 2px solid white;
    }
    
    .tab-content {
        padding: 1rem;
    }
    
    .welcome-text {
        font-size: 1.25rem;
        font-weight: 300;
        margin-bottom: 0.2rem;
    }
    
    .welcome-subtitle {
        font-size: 0.8rem;
        opacity: 0.9;
    }
    
    .pulse-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #48bb78;
        margin-right: 6px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(72, 187, 120, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(72, 187, 120, 0); }
    }
    
    .modern-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin: 0;
    }
    
    .modern-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .modern-table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        padding: 0.5rem;
        border: none;
    }
    
    .modern-table td {
        padding: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
        font-size: 0.8rem;
    }
    
    .modern-table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .progress-modern {
        height: 15px;
        border-radius: 8px;
        background: #e2e8f0;
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .progress-modern .progress-bar {
        background: linear-gradient(90deg, #667eea, #764ba2);
        border-radius: 8px;
        transition: width 0.6s ease;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        min-width: 30px;
    }
    
    .progress-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.7rem;
        font-weight: 600;
        color: #2d3748;
        z-index: 10;
        white-space: nowrap;
    }
    
    .sidebar-info {
        background: white;
        border-radius: 10px;
        padding: 0.5rem 0.6rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        margin-bottom: 0.5rem;
        height: fit-content;
    }
    
    .sidebar-info h6 {
        font-size: 0.75rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
    }
    
    .sidebar-info .icon {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.4rem;
        font-size: 9px;
    }
    
    .actions-rapides-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.35rem;
    }
    
    .btn-action {
        padding: 0.35rem 0.4rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 500;
        transition: all 0.2s ease;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .btn-action i {
        font-size: 0.65rem;
        margin-right: 0.25rem;
        flex-shrink: 0;
    }
    
    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    }
    
    .chart-canvas {
        max-height: 150px !important;
        width: 100% !important;
    }
    
    .row-equal-height {
        display: flex;
        flex-wrap: wrap;
    }
    
    .row-equal-height > [class*="col-"] {
        display: flex;
        flex-direction: column;
    }
    
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
        height: 100vh;
        overflow: hidden;
    }
    
    @media (max-width: 768px) {
        .dashboard-hero {
            padding: 0.75rem;
        }
        
        .stat-card {
            height: auto;
            padding: 0.75rem;
        }
        
        .chart-container {
            padding: 0.75rem;
        }
        
        .welcome-text {
            font-size: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid pb-4">
    <!-- Hero Section -->
    <div class="dashboard-hero">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-text">
        <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
    </h1>
                <p class="welcome-subtitle">
                    <span class="pulse-dot"></span>
                    Bienvenue {{ Auth::user()->name }}, voici un aperçu de votre commune
                </p>
        </div>
            <div class="col-md-4 text-end">
                <div class="d-flex align-items-center justify-content-end">
                    <i class="fas fa-clock me-2"></i>
                    <span>{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cartes de statistiques principales -->
    <div class="row compact-section">
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-2">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="stat-number" id="stat-total-unites">{{ number_format($totalUnites, 0, ',', ' ') }}</div>
                <div class="stat-label">Unités commerciales</div>
            </div>
        </div>
        
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-2">
            <div class="stat-card" style="background: linear-gradient(135deg, #d4edda, #c3e6cb); border-left: 4px solid #28a745;">
                <div class="stat-icon" style="color: #28a745;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number" style="color: #155724;" id="stat-unites-regle">{{ number_format($unitesEnRegle, 0, ',', ' ') }}</div>
                <div class="stat-label" style="color: #155724;">Unités en règle</div>
            </div>
        </div>
        
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-2">
            <div class="stat-card" style="background: linear-gradient(135deg, #fff3cd, #ffeaa7); border-left: 4px solid #ffc107;">
                <div class="stat-icon" style="color: #856404;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-number" style="color: #856404;" id="stat-unites-retard">{{ number_format($unitesEnRetard, 0, ',', ' ') }}</div>
                <div class="stat-label" style="color: #856404;">Unités en retard</div>
            </div>
        </div>
        
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-2">
            <div class="stat-card" style="background: linear-gradient(135deg, #f8d7da, #f5c6cb); border-left: 4px solid #dc3545;">
                <div class="stat-icon" style="color: #721c24;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number" style="color: #721c24;" id="stat-unites-non-payees">{{ number_format($unitesNonPayees, 0, ',', ' ') }}</div>
                <div class="stat-label" style="color: #721c24;">Unités non payées</div>
            </div>
        </div>
        
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-2">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-number" id="stat-recettes-jour">{{ number_format($totalPaiementsJour, 0, ',', ' ') }}</div>
                <div class="stat-label">Recettes jour (FCFA)</div>
            </div>
        </div>
        
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-2">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-number" id="stat-recettes-mois">{{ number_format($totalPaiementsMois, 0, ',', ' ') }}</div>
                <div class="stat-label">Recettes mois (FCFA)</div>
            </div>
        </div>
    </div>

    <!-- Section avec onglets pour les graphiques -->
    <div class="tabs-container">
        <ul class="nav nav-tabs nav-tabs-modern flex-column flex-sm-row" id="analyticsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance" type="button" role="tab">
                    <i class="fas fa-chart-bar me-2"></i>
                    <span class="d-none d-sm-inline">Performance</span>
                    <span class="d-sm-none">Perf.</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="evolution-tab" data-bs-toggle="tab" data-bs-target="#evolution" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>
                    <span class="d-none d-sm-inline">Évolution</span>
                    <span class="d-sm-none">Évol.</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="repartition-tab" data-bs-toggle="tab" data-bs-target="#repartition" type="button" role="tab">
                    <i class="fas fa-pie-chart me-2"></i>
                    <span class="d-none d-sm-inline">Répartition</span>
                    <span class="d-sm-none">Répart.</span>
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="analyticsTabContent" style="max-height: 300px; overflow-y: auto;">
            <!-- Onglet Performance -->
            <div class="tab-pane fade show active" id="performance" role="tabpanel">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <div class="chart-header">
                                <div class="chart-icon">
                                    <i class="fas fa-trophy"></i>
</div>
                                <h3 class="chart-title">Top 10 Collecteurs Performants</h3>
                        </div>
                            <canvas id="topCollecteursChart" class="chart-canvas"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <div class="chart-header">
                                <div class="chart-icon">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <h3 class="chart-title">Performance par Zone Fiscale</h3>
                            </div>
                            <canvas id="performanceZoneChart" class="chart-canvas"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Onglet Évolution -->
            <div class="tab-pane fade" id="evolution" role="tabpanel">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <div class="chart-header">
                                <div class="chart-icon">
                                    <i class="fas fa-chart-line"></i>
        </div>
                                <h3 class="chart-title">Évolution des Recettes (6 mois)</h3>
    </div>
                            <canvas id="evolutionRecettesChart" class="chart-canvas"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <div class="chart-header">
                                <div class="chart-icon">
                                    <i class="fas fa-chart-area"></i>
                                </div>
                                <h3 class="chart-title">Évolution Mensuelle</h3>
                            </div>
                            <canvas id="evolutionChart" class="chart-canvas"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Onglet Répartition -->
            <div class="tab-pane fade" id="repartition" role="tabpanel">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <div class="chart-header">
                                <div class="chart-icon">
                                    <i class="fas fa-percentage"></i>
        </div>
                                <h3 class="chart-title">Par Type de Taxe</h3>
    </div>
                            <canvas id="repartitionTaxeChart" class="chart-canvas"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <div class="chart-header">
                                <div class="chart-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <h3 class="chart-title">Unités par Zone</h3>
                            </div>
                            <canvas id="distributionUnitesChart" class="chart-canvas"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <div class="chart-header">
                                <div class="chart-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3 class="chart-title">Statut de Paiement</h3>
                            </div>
                            <canvas id="statutPaiementChart" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Tableau et informations complémentaires -->
    <div class="row compact-section" style="height: calc(100vh - 600px); overflow: hidden;">
        <div class="col-lg-8">
            <div class="chart-container h-100">
                <div class="chart-header">
                    <div class="chart-icon">
                        <i class="fas fa-table"></i>
            </div>
                    <h3 class="chart-title">Collecte par zone - {{ now()->format('F Y') }}</h3>
                </div>
                <div class="table-responsive" style="height: calc(100% - 60px); overflow-y: auto;">
                    <table class="modern-table table table-sm">
                        <thead>
                            <tr>
                                <th>Zone fiscale</th>
                                <th>Total unités</th>
                                <th>Unités payantes</th>
                                <th>Montant collecté</th>
                                <th>Taux de recouvrement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paiementsParZone as $zone)
                            <tr>
                                <td><strong>{{ $zone->nom }}</strong></td>
                                <td>{{ $zone->total_unites ?? 0 }}</td>
                                <td>{{ $zone->unites_payantes ?? 0 }}</td>
                                <td><strong>{{ number_format($zone->montant_collecte ?? 0, 0, ',', ' ') }} FCFA</strong></td>
                                <td>
                                    @php
                                        $total = $zone->total_unites ?? 0;
                                        $payantes = $zone->unites_payantes ?? 0;
                                        $taux = $total > 0 ? ($payantes / $total) * 100 : 0;
                                    @endphp
                                    <div class="progress-modern">
                                        <div class="progress-bar" style="width: {{ $taux }}%"></div>
                                        <div class="progress-text">{{ number_format($taux, 1) }}%</div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Graphique répartition par zone -->
            <div class="sidebar-info">
                <h6>
                    <div class="icon"><i class="fas fa-chart-pie"></i></div>
                    Répartition par zone
                </h6>
                <div style="height: 120px;">
                    <canvas id="repartitionZonesChart"></canvas>
                </div>
            </div>
        
            <!-- Actions rapides -->
            <div class="sidebar-info">
                <h6>
                    <div class="icon"><i class="fas fa-bolt"></i></div>
                    Actions rapides
                </h6>
                <div class="actions-rapides-grid">
                    <a href="{{ route('rapports.index') }}" class="btn btn-primary btn-action btn-sm" title="Rapports">
                        <i class="fas fa-chart-bar"></i><span>Rapports</span>
                    </a>
                    <a href="{{ route('unites.index') }}" class="btn btn-outline-primary btn-action btn-sm" title="Unités commerciales">
                        <i class="fas fa-store"></i><span>Unités</span>
                    </a>
                    <a href="{{ route('paiements.index') }}" class="btn btn-outline-primary btn-action btn-sm" title="Paiements">
                        <i class="fas fa-money-check-alt"></i><span>Paiements</span>
                    </a>
                    <a href="{{ route('suivi.collecteurs') }}" class="btn btn-outline-primary btn-action btn-sm" title="Suivi collecteurs">
                        <i class="fas fa-map-marker-alt"></i><span>Suivi</span>
                    </a>
                    <a href="{{ route('map.index') }}" class="btn btn-outline-primary btn-action btn-sm" title="Carte">
                        <i class="fas fa-map"></i><span>Carte</span>
                    </a>
                    <a href="{{ route('alerts.payment-overdue') }}" class="btn btn-outline-warning btn-action btn-sm" title="Alertes retard">
                        <i class="fas fa-exclamation-triangle"></i><span>Alertes</span>
                    </a>
                    <a href="{{ route('maire.geomarketing.advanced') }}" class="btn btn-outline-secondary btn-action btn-sm" title="Géomarketing">
                        <i class="fas fa-chart-area"></i><span>Géomarketing</span>
                    </a>
                    <a href="{{ route('profil.edit') }}" class="btn btn-outline-secondary btn-action btn-sm" title="Mon profil">
                        <i class="fas fa-user-cog"></i><span>Profil</span>
                    </a>
                </div>
            </div>

            <!-- Informations système -->
            <div class="sidebar-info">
                <h6>
                    <div class="icon"><i class="fas fa-info-circle"></i></div>
                    Informations système
                </h6>
                <p class="mb-1 small" style="font-size: 0.7rem;"><strong>Zones :</strong> {{ $totalZones }}</p>
                <p class="mb-1 small" style="font-size: 0.7rem;"><strong>Collecteurs :</strong> {{ $totalCollecteurs }}</p>
                <p class="mb-0 small" style="font-size: 0.7rem;"><strong>Mise à jour :</strong> {{ now()->format('H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
const LIVE_STATS_URL = "{{ route('maire.dashboard.live') }}";

function formatNumber(value) {
    return new Intl.NumberFormat('fr-FR').format(value || 0);
}

function refreshLiveStats() {
    fetch(LIVE_STATS_URL)
        .then(r => r.json())
        .then(data => {
            const totalUnites = document.getElementById('stat-total-unites');
            const unitesRegle = document.getElementById('stat-unites-regle');
            const unitesRetard = document.getElementById('stat-unites-retard');
            const unitesNonPayees = document.getElementById('stat-unites-non-payees');
            const recettesJour = document.getElementById('stat-recettes-jour');
            const recettesMois = document.getElementById('stat-recettes-mois');

            if (totalUnites) totalUnites.textContent = formatNumber(data.totalUnites);
            if (unitesRegle) unitesRegle.textContent = formatNumber(data.unitesEnRegle);
            if (unitesRetard) unitesRetard.textContent = formatNumber(data.unitesEnRetard);
            if (unitesNonPayees) unitesNonPayees.textContent = formatNumber(data.unitesNonPayees);
            if (recettesJour) recettesJour.textContent = formatNumber(data.totalPaiementsJour);
            if (recettesMois) recettesMois.textContent = formatNumber(data.totalPaiementsMois);
        })
        .catch(() => {});
}

document.addEventListener('DOMContentLoaded', function() {
    // Vérifier que Chart.js est chargé
    if (typeof Chart === 'undefined') {
        console.error('Chart.js n\'est pas chargé !');
        return;
    }
    
    console.log('Chart.js chargé avec succès, version:', Chart.version);
    // Configuration globale pour tous les graphiques
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;
    Chart.defaults.plugins.legend.display = true;
    Chart.defaults.plugins.legend.position = 'bottom';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.padding = 15;
    Chart.defaults.plugins.legend.labels.font = { size: 11 };

    // Couleurs pour les graphiques
    const colors = {
        primary: '#667eea',
        success: '#28a745',
        warning: '#ffc107',
        danger: '#dc3545',
        info: '#17a2b8',
        secondary: '#6c757d',
        purple: '#6f42c1',
        accent: '#fd7e14'
    };

    // Configuration des options communes
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 15,
                    font: { size: 10 }
                }
            }
        }
    };

    // Graphique de répartition par zone
    @if(isset($repartitionZones) && count($repartitionZones) > 0)
    const ctxRepartition = document.getElementById('repartitionZonesChart').getContext('2d');
    new Chart(ctxRepartition, {
        type: 'doughnut',
        data: {
            labels: @json($labelsZones ?? []),
            datasets: [{
                data: @json($repartitionZones ?? []),
                backgroundColor: ['#667eea', '#764ba2', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        font: { size: 9 },
                        padding: 10,
                        usePointStyle: true
                    }
                }
            }
        }
    });
    @endif

    // 1. Top 10 Collecteurs Performants
    const collecteursData = @json($topCollecteurs ?? []);
    console.log('Données collecteurs:', collecteursData);
    
    if (collecteursData && collecteursData.length > 0) {
        const canvasElement = document.getElementById('topCollecteursChart');
        if (!canvasElement) {
            console.error('Canvas topCollecteursChart non trouvé dans le DOM');
            return;
        }
        const ctxTopCollecteurs = canvasElement.getContext('2d');
        new Chart(ctxTopCollecteurs, {
            type: 'bar',
            data: {
                labels: collecteursData.map(c => c.nom),
                datasets: [{
                    label: 'Montant Collecté (FCFA)',
                    data: collecteursData.map(c => c.montant),
                    backgroundColor: colors.primary,
                    borderColor: colors.primary,
                    borderWidth: 1
                }]
            },
            options: {
                ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    } else {
        console.log('Aucune donnée de collecteurs disponible ou canvas non trouvé');
        const topCollecteursElement = document.getElementById('topCollecteursChart');
        if (topCollecteursElement && topCollecteursElement.parentElement) {
            topCollecteursElement.parentElement.innerHTML = '<div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> Aucune donnée disponible pour les performances des collecteurs</div>';
        }
    }

    // 2. Performance par Zone Fiscale
    const performanceZonesData = @json($performanceZones ?? []);
    console.log('Données zones:', performanceZonesData);
    
    if (performanceZonesData && performanceZonesData.length > 0) {
        const canvasZone = document.getElementById('performanceZoneChart');
        if (!canvasZone) {
            console.error('Canvas performanceZoneChart non trouvé dans le DOM');
            return;
        }
        const ctxPerformanceZone = canvasZone.getContext('2d');
        new Chart(ctxPerformanceZone, {
            type: 'radar',
        data: {
                labels: performanceZonesData.map(z => z.zone),
            datasets: [{
                    label: 'Taux de Recouvrement (%)',
                    data: performanceZonesData.map(z => z.taux),
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderColor: '#667eea',
                    borderWidth: 3,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                            font: { size: 10 },
                            padding: 10,
                        usePointStyle: true
                        }
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        min: 0,
                        ticks: {
                            stepSize: 20,
                            callback: function(value) {
                                return value + '%';
                            },
                            font: { size: 10 }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)',
                            lineWidth: 1
                        },
                        angleLines: {
                            color: 'rgba(0, 0, 0, 0.1)',
                            lineWidth: 1
                        },
                        pointLabels: {
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    } else {
        console.log('Aucune donnée de zones disponible ou canvas non trouvé');
        const zonesElement = document.getElementById('performanceZoneChart');
        if (zonesElement && zonesElement.parentElement) {
            zonesElement.parentElement.innerHTML = '<div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> Aucune donnée disponible pour la performance par zone</div>';
        }
    }

    // 3. Évolution des Recettes (6 mois)
    const evolutionRecettes6MoisData = @json($evolutionRecettes6Mois ?? []);
    const labelsEvolution6MoisData = @json($labelsEvolution6Mois ?? []);
    if (evolutionRecettes6MoisData.length > 0) {
        const ctxEvolutionRecettes = document.getElementById('evolutionRecettesChart').getContext('2d');
        new Chart(ctxEvolutionRecettes, {
            type: 'line',
            data: {
                labels: labelsEvolution6MoisData,
                datasets: [{
                    label: 'Recettes Mensuelles (FCFA)',
                    data: evolutionRecettes6MoisData,
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    borderColor: colors.info,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            }
                        }
                    }
                }
            }
        });
    }

    // 4. Répartition par Type de Taxe
    const repartitionTaxesData = @json($repartitionTaxes ?? []);
    if (repartitionTaxesData.length > 0) {
        const ctxRepartitionTaxe = document.getElementById('repartitionTaxeChart').getContext('2d');
        new Chart(ctxRepartitionTaxe, {
            type: 'pie',
            data: {
                labels: repartitionTaxesData.map(t => t.nom),
                datasets: [{
                    data: repartitionTaxesData.map(t => t.montant),
                    backgroundColor: [colors.warning, colors.success, colors.danger, colors.secondary, colors.info, colors.primary],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                                return context.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.parsed) + ' FCFA';
                            }
                        }
                    }
                }
            }
        });
    }

    // 5. Distribution des Unités par Zone
    const distributionUnitesData = @json($distributionUnites ?? []);
    if (distributionUnitesData.length > 0) {
        const ctxDistributionUnites = document.getElementById('distributionUnitesChart').getContext('2d');
        new Chart(ctxDistributionUnites, {
            type: 'doughnut',
            data: {
                labels: distributionUnitesData.map(d => d.zone),
                datasets: [{
                    data: distributionUnitesData.map(d => d.count),
                    backgroundColor: [colors.purple, colors.info, colors.success, colors.warning, colors.danger, colors.primary],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: commonOptions
        });
    }

    // 6. Statut de Paiement des Unités
    const statutPaiementData = @json($statutPaiement ?? []);
    if (statutPaiementData && statutPaiementData.total > 0) {
        const ctxStatutPaiement = document.getElementById('statutPaiementChart').getContext('2d');
        new Chart(ctxStatutPaiement, {
            type: 'doughnut',
            data: {
                labels: ['Unités Payantes', 'Unités Non Payantes'],
                datasets: [{
                    data: [statutPaiementData.payantes, statutPaiementData.non_payantes],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: { size: 10 },
                            padding: 10,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    // Graphique d'évolution (ancien)
    const evolutionData = @json($evolutionRecettes ?? []);
    const labelsEvolutionData = @json($labelsEvolution ?? []);
    if (evolutionData.length > 0) {
        const ctxEvolution = document.getElementById('evolutionChart').getContext('2d');
        new Chart(ctxEvolution, {
            type: 'line',
            data: {
                labels: labelsEvolutionData,
                datasets: [{
                    label: 'Recettes (FCFA)',
                    data: evolutionData,
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    borderColor: colors.info,
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                        }
                    }
                }
            }
        }
    });
    }
});

setInterval(refreshLiveStats, 30000);
</script>
@endpush 