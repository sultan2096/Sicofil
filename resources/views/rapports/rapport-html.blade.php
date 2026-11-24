@extends('layouts.app')

@section('title', 'Rapport Financier - Aperçu HTML')

@section('content')
<div class="container-fluid">
    <!-- En-tête du rapport -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h1 class="mb-0">{{ $titre }}</h1>
                    <p class="mb-0">Système d'Information Commerciale de Sandiara</p>
                    <small>Commune de Sandiara - République du Sénégal</small>
                </div>
                <div class="card-body bg-light text-center">
                    <h3 class="text-primary">{{ $periode['libelle'] }}</h3>
                    <p class="mb-1"><strong>Période analysée :</strong> {{ $periode['debut'] }} au {{ $periode['fin'] }}</p>
                    @if($zone_selectionnee)
                        <p class="mb-1"><strong>Zone fiscale :</strong> {{ $zone_selectionnee->nom }}</p>
                    @endif
                    <small class="text-muted">Rapport généré le {{ $date_generation }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Résumé des recettes par période -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-chart-bar"></i> 1. RÉSUMÉ DES RECETTES PAR PÉRIODE</h4>
                </div>
                <div class="card-body">
                    <!-- Statistiques principales -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white text-center">
                                <div class="card-body">
                                    <h3>{{ number_format($recettes_periode['total'], 0, ',', ' ') }} FCFA</h3>
                                    <p class="mb-0">Recettes totales</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white text-center">
                                <div class="card-body">
                                    <h3>{{ number_format($recettes_periode['nombre_paiements']) }}</h3>
                                    <p class="mb-0">Nombre de paiements</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white text-center">
                                <div class="card-body">
                                    <h3>{{ number_format($recettes_periode['montant_moyen'], 0, ',', ' ') }} FCFA</h3>
                                    <p class="mb-0">Montant moyen</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white text-center">
                                <div class="card-body">
                                    <h3>{{ $recettes_periode['par_taxe']->count() }}</h3>
                                    <p class="mb-0">Types de taxes</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Répartition par type de taxe -->
                    <h5 class="text-success mb-3">Répartition par type de taxe</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Type de taxe</th>
                                    <th>Taux</th>
                                    <th class="text-end">Montant collecté</th>
                                    <th class="text-end">% du total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recettes_periode['par_taxe'] as $taxe)
                                    <tr>
                                        <td><strong>{{ $taxe->nom }}</strong></td>
                                        <td><span class="badge bg-primary">{{ $taxe->taux }}%</span></td>
                                        <td class="text-end">{{ number_format($taxe->total, 0, ',', ' ') }} FCFA</td>
                                        <td class="text-end">
                                            <span class="badge bg-success">
                                                {{ $recettes_periode['total'] > 0 ? number_format(($taxe->total / $recettes_periode['total']) * 100, 1) : 0 }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Répartition par zone fiscale -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-map-marked-alt"></i> 2. RÉPARTITION PAR ZONE FISCALE</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Zone fiscale</th>
                                    <th class="text-end">Montant collecté</th>
                                    <th class="text-end">Nb paiements</th>
                                    <th class="text-end">% du total</th>
                                    <th class="text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalZones = $recettes_zones->sum('paiements_sum_montant') @endphp
                                @foreach($recettes_zones as $zone)
                                    <tr>
                                        <td><strong>{{ $zone->nom }}</strong></td>
                                        <td class="text-end">{{ number_format($zone->paiements_sum_montant ?? 0, 0, ',', ' ') }} FCFA</td>
                                        <td class="text-end">{{ $zone->paiements_count ?? 0 }}</td>
                                        <td class="text-end">
                                            {{ $totalZones > 0 ? number_format((($zone->paiements_sum_montant ?? 0) / $totalZones) * 100, 1) : 0 }}%
                                        </td>
                                        <td class="text-center">
                                            @php 
                                                $pourcentage = $totalZones > 0 ? (($zone->paiements_sum_montant ?? 0) / $totalZones) * 100 : 0;
                                            @endphp
                                            @if($pourcentage >= 30)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif($pourcentage >= 15)
                                                <span class="badge bg-warning">Bon</span>
                                            @else
                                                <span class="badge bg-danger">À améliorer</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Performance des collecteurs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-users"></i> 3. PERFORMANCE DES COLLECTEURS</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Collecteur</th>
                                    <th>Zone assignée</th>
                                    <th class="text-end">Montant collecté</th>
                                    <th class="text-end">Nb paiements</th>
                                    <th class="text-end">Moyenne par paiement</th>
                                    <th class="text-center">Classement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($performance_collecteurs as $index => $collecteur)
                                    <tr>
                                        <td><strong>{{ $collecteur->name }}</strong></td>
                                        <td>{{ $collecteur->zoneFiscale->nom ?? 'Non assignée' }}</td>
                                        <td class="text-end">{{ number_format($collecteur->paiements_collectes_sum_montant ?? 0, 0, ',', ' ') }} FCFA</td>
                                        <td class="text-end">{{ $collecteur->paiements_collectes_count ?? 0 }}</td>
                                        <td class="text-end">
                                            {{ ($collecteur->paiements_collectes_count ?? 0) > 0 ? number_format(($collecteur->paiements_collectes_sum_montant ?? 0) / $collecteur->paiements_collectes_count, 0, ',', ' ') : 0 }} FCFA
                                        </td>
                                        <td class="text-center">
                                            @if($index == 0)
                                                <span class="badge bg-success fs-6">🥇 1er</span>
                                            @elseif($index == 1)
                                                <span class="badge bg-warning fs-6">🥈 2ème</span>
                                            @elseif($index == 2)
                                                <span class="badge bg-info fs-6">🥉 3ème</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $index + 1 }}ème</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Statistiques détaillées -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Indicateurs de performance</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Taux de réussite des paiements</h6>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $statistiques['taux_reussite'] }}%"
                                 aria-valuenow="{{ $statistiques['taux_reussite'] }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ number_format($statistiques['taux_reussite'], 1) }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $statistiques['paiements_valides'] }}/{{ $statistiques['total_paiements'] }} paiements validés
                        </small>
                    </div>
                    
                    <div class="mb-4">
                        <h6>Taux de participation des unités</h6>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $statistiques['taux_participation'] }}%"
                                 aria-valuenow="{{ $statistiques['taux_participation'] }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ number_format($statistiques['taux_participation'], 1) }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $statistiques['unites_actives'] }}/{{ $statistiques['total_unites'] }} unités actives
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Répartition des paiements</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Paiements validés</strong></td>
                            <td class="text-end">
                                <span class="badge bg-success fs-6">{{ $statistiques['paiements_valides'] }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Paiements annulés</strong></td>
                            <td class="text-end">
                                <span class="badge bg-danger fs-6">{{ $statistiques['paiements_annules'] }}</span>
                            </td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total des paiements</strong></td>
                            <td class="text-end">
                                <span class="badge bg-primary fs-6">{{ $statistiques['total_paiements'] }}</span>
                            </td>
                        </tr>
                    </table>
                    
                    <hr>
                    
                    <h6 class="mt-3">Unités commerciales</h6>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Unités actives</strong></td>
                            <td class="text-end">
                                <span class="badge bg-success fs-6">{{ $statistiques['unites_actives'] }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Total des unités</strong></td>
                            <td class="text-end">
                                <span class="badge bg-info fs-6">{{ $statistiques['total_unites'] }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 5: Évolution des recettes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><i class="fas fa-chart-line"></i> 5. ÉVOLUTION DES RECETTES</h4>
                </div>
                <div class="card-body">
                    @if($evolution_recettes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Période</th>
                                        <th class="text-end">Montant collecté</th>
                                        <th class="text-end">Nombre de paiements</th>
                                        <th class="text-end">Moyenne par paiement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evolution_recettes as $periode)
                                        <tr>
                                            <td><strong>{{ $periode->periode }}</strong></td>
                                            <td class="text-end">{{ number_format($periode->total, 0, ',', ' ') }} FCFA</td>
                                            <td class="text-end">{{ $periode->nombre }}</td>
                                            <td class="text-end">
                                                {{ $periode->nombre > 0 ? number_format($periode->total / $periode->nombre, 0, ',', ' ') : 0 }} FCFA
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <h5>Aucune donnée disponible</h5>
                            <p>Aucune donnée disponible pour la période sélectionnée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Section 6: Recommandations -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-lightbulb"></i> 6. RECOMMANDATIONS</h4>
                </div>
                <div class="card-body">
                    <h5 class="text-primary">Analyse des performances :</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                @if($statistiques['taux_reussite'] < 90)
                                    <li class="mb-2">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                        <strong>Améliorer le taux de réussite :</strong> 
                                        Le taux de réussite actuel ({{ number_format($statistiques['taux_reussite'], 1) }}%) peut être amélioré en renforçant la formation des collecteurs.
                                    </li>
                                @endif
                                
                                @if($statistiques['taux_participation'] < 70)
                                    <li class="mb-2">
                                        <i class="fas fa-users text-info"></i>
                                        <strong>Augmenter la participation :</strong> 
                                        Seulement {{ number_format($statistiques['taux_participation'], 1) }}% des unités commerciales participent activement. Une campagne de sensibilisation est recommandée.
                                    </li>
                                @endif
                                
                                @if($recettes_zones->where('paiements_sum_montant', 0)->count() > 0)
                                    <li class="mb-2">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        <strong>Zones à faible performance :</strong> 
                                        Certaines zones fiscales montrent des performances faibles. Un accompagnement spécifique est nécessaire.
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-star text-success"></i>
                                    <strong>Collecteurs performants :</strong> 
                                    Capitaliser sur les bonnes pratiques des collecteurs les plus performants pour former les autres.
                                </li>
                                
                                <li class="mb-2">
                                    <i class="fas fa-chart-line text-primary"></i>
                                    <strong>Suivi régulier :</strong> 
                                    Mettre en place un système de suivi hebdomadaire pour identifier rapidement les problèmes.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <a href="{{ route('rapports.formulaire') }}" class="btn btn-secondary btn-lg me-3">
                <i class="fas fa-arrow-left"></i> Retour au formulaire
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="fas fa-print"></i> Imprimer cette page
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn, .card-header { 
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        .no-print {
            display: none !important;
        }
        
        .card {
            break-inside: avoid;
            margin-bottom: 1rem;
        }
        
        .table {
            font-size: 12px;
        }
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .progress {
        background-color: #e9ecef;
    }
    
    .badge {
        font-size: 0.75em;
    }
</style>
@endpush
@endsection 