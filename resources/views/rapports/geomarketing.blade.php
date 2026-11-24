@extends('layouts.app')

@section('title', 'Rapport Géomarketing - SICOSA')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('images/logos/snd.jpeg') }}" alt="Logo SICOSA" 
                                 class="me-3" style="height: 40px; width: 40px; border-radius: 50%;">
                            <div>
                                <h4 class="mb-0">
                                    <i class="fas fa-chart-line"></i> 
                                    Rapport Géomarketing
                                </h4>
                                <small class="opacity-75">Analyse des opportunités d'investissement</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('rapports.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <a href="{{ route('rapports.geomarketing', array_merge(request()->all(), ['format' => 'pdf'])) }}" 
                               class="btn btn-danger btn-sm" target="_blank">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <a href="{{ route('rapports.geomarketing', array_merge(request()->all(), ['format' => 'excel'])) }}" 
                               class="btn btn-success btn-sm" target="_blank">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtres appliqués -->
                    @if(isset($filtres_appliques))
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="fas fa-filter"></i> Filtres appliqués</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <small><strong>Période :</strong><br>{{ $filtres_appliques['date_debut'] }} au {{ $filtres_appliques['date_fin'] }}</small>
                            </div>
                            <div class="col-md-3">
                                <small><strong>Zone :</strong><br>{{ $filtres_appliques['zone_selectionnee'] }}</small>
                            </div>
                            <div class="col-md-3">
                                <small><strong>Nature d'activité :</strong><br>{{ $filtres_appliques['nature_selectionnee'] }}</small>
                            </div>
                            <div class="col-md-3">
                                <small><strong>Type de commerce :</strong><br>{{ $filtres_appliques['type_selectionne'] }}</small>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <small><strong>Seuil de score :</strong><br>{{ $filtres_appliques['seuil_score'] > 0 ? $filtres_appliques['seuil_score'].'+' : 'Tous' }}</small>
                            </div>
                            <div class="col-md-3">
                                <small><strong>Type de rapport :</strong><br>{{ $filtres_appliques['type_rapport'] }}</small>
                            </div>
                            <div class="col-md-6">
                                <small><strong>Couches d'analyse :</strong><br>{{ implode(', ', $filtres_appliques['couches_analyse']) }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Statistiques principales -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white text-center">
                                <div class="card-body">
                                    <h3 class="mb-1">{{ $stats['total_zones'] }}</h3>
                                    <small>Zones analysées</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white text-center">
                                <div class="card-body">
                                    <h3 class="mb-1">{{ number_format($stats['score_moyen'], 1) }}</h3>
                                    <small>Score moyen</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white text-center">
                                <div class="card-body">
                                    <h3 class="mb-1">{{ $stats['total_unites'] }}</h3>
                                    <small>Unités commerciales</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white text-center">
                                <div class="card-body">
                                    <h3 class="mb-1">{{ count($propositions_investissement) }}</h3>
                                    <small>Propositions</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Zones d'opportunités -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-map-marked-alt text-primary"></i> Zones d'opportunités</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Zone</th>
                                                    <th>Score</th>
                                                    <th>Unités</th>
                                                    <th>Recommandation</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($zones_analyse as $zone)
                                                <tr>
                                                    <td><strong>{{ $zone['nom'] }}</strong></td>
                                                    <td>
                                                        @if($zone['score'] >= 75)
                                                            <span class="badge bg-success">{{ $zone['score'] }}/100</span>
                                                        @elseif($zone['score'] >= 60)
                                                            <span class="badge bg-warning">{{ $zone['score'] }}/100</span>
                                                        @elseif($zone['score'] >= 45)
                                                            <span class="badge bg-danger">{{ $zone['score'] }}/100</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $zone['score'] }}/100</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $zone['unites_count'] }} unités</td>
                                                    <td><small>{{ $zone['recommandation'] }}</small></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Propositions d'investissement -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-lightbulb text-warning"></i> Propositions d'investissement</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($propositions_investissement as $proposition)
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-primary">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0">{{ $proposition['type_investissement'] }} - {{ $proposition['zone'] }}</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <small class="text-muted">Score:</small><br>
                                                            <strong class="text-success">{{ $proposition['score'] }}/100</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">ROI:</small><br>
                                                            <strong class="text-success">{{ $proposition['roi_estime'] }}%</strong>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-2">
                                                        <div class="col-6">
                                                            <small class="text-muted">Investissement:</small><br>
                                                            <strong>{{ number_format($proposition['cout_investissement']) }} FCFA</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">Délai:</small><br>
                                                            <strong>{{ $proposition['delai'] }}</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recommandations -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="fas fa-clipboard-check text-primary"></i> Recommandations</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-success">Points forts:</h6>
                                            <ul>
                                                <li>Zone Centre-Ville: excellent potentiel (85/100)</li>
                                                <li>Diversité des opportunités identifiées</li>
                                                <li>ROI attractif sur les investissements</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-warning">Actions prioritaires:</h6>
                                            <ul>
                                                <li>Concentrer les investissements sur la Zone Centre-Ville</li>
                                                <li>Développer les services de proximité</li>
                                                <li>Améliorer l'accessibilité des zones périphériques</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

@media print {
    .btn {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
}
</style>
@endsection
