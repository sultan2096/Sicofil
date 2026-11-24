@extends('layouts.app')

@section('title', 'Rapport par Type de Commerce')

@section('content')
<div class="container-fluid">
    <!-- En-tête du rapport -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="{{ asset('images/logos/snd.jpeg') }}" alt="Logo SICOSA" 
                                 class="img-fluid rounded-circle border border-white" 
                                 style="max-height: 80px;">
                        </div>
                        <div class="col-md-10">
                            <h1 class="mb-2">
                                <i class="fas fa-tags me-2"></i>
                                Rapport par Type de Commerce
                            </h1>
                            <p class="mb-0">Système d'Information Commerciale de Sandiara - République du Sénégal</p>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-light text-center">
                    <h3 class="text-primary">{{ $stats['periode'] }}</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Zone :</strong> {{ $stats['zone'] }}</p>
                            <p class="mb-1"><strong>Type filtré :</strong> {{ $stats['type_filtre'] }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Catégorie :</strong> {{ $stats['categorie_filtree'] }}</p>
                            <p class="mb-1"><strong>Nombre de types :</strong> {{ $stats['nombre_types'] }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Rapport généré le {{ now()->format('d/m/Y à H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-gradient-primary text-white h-100">
                <div class="card-body text-center">
                    <div class="display-6 mb-2">
                        <i class="fas fa-store"></i>
                    </div>
                    <h4 class="mb-1">{{ number_format($stats['total_unites']) }}</h4>
                    <small>Unités commerciales</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-gradient-success text-white h-100">
                <div class="card-body text-center">
                    <div class="display-6 mb-2">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <h4 class="mb-1">{{ number_format($stats['total_paiements']) }}</h4>
                    <small>Paiements validés</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-gradient-warning text-white h-100">
                <div class="card-body text-center">
                    <div class="display-6 mb-2">
                        <i class="fas fa-coins"></i>
                    </div>
                    <h4 class="mb-1">{{ number_format($stats['total_recettes'], 0, ',', ' ') }}</h4>
                    <small>FCFA de recettes</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-gradient-info text-white h-100">
                <div class="card-body text-center">
                    <div class="display-6 mb-2">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['nombre_types'] }}</h4>
                    <small>Types de commerce</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau détaillé -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-table me-2"></i>
                        Détail par Type de Commerce
                    </h4>
                </div>
                <div class="card-body">
                    @if($donnees->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Type de Commerce</th>
                                        <th class="text-center">Catégorie</th>
                                        <th class="text-center">Nb. Unités</th>
                                        <th class="text-center">Nb. Paiements</th>
                                        <th class="text-end">Recettes Totales</th>
                                        <th class="text-end">Montant Moyen</th>
                                        <th class="text-center">% du Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($donnees as $index => $type)
                                        @php
                                            $pourcentage = $stats['total_recettes'] > 0 ? 
                                                round(($type->total_recettes / $stats['total_recettes']) * 100, 2) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong class="text-primary">
                                                    @if(str_contains(strtolower($type->type_commerce), 'transport'))
                                                        🚗 {{ $type->type_commerce ?: 'Non spécifié' }}
                                                    @else
                                                        🏪 {{ $type->type_commerce ?: 'Non spécifié' }}
                                                    @endif
                                                </strong>
                                                @if($index < 3)
                                                    <span class="badge bg-warning ms-2">Top {{ $index + 1 }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($type->categorie === 'activite_commerciale')
                                                    <span class="badge bg-success">Commercial</span>
                                                @elseif($type->categorie === 'point_interet')
                                                    <span class="badge bg-info">Intérêt</span>
                                                @else
                                                    <span class="badge bg-secondary">Non défini</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ number_format($type->nombre_unites) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success">{{ number_format($type->nombre_paiements) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">{{ number_format($type->total_recettes, 0, ',', ' ') }} FCFA</strong>
                                            </td>
                                            <td class="text-end">
                                                {{ number_format($type->montant_moyen, 0, ',', ' ') }} FCFA
                                            </td>
                                            <td class="text-center">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-info" role="progressbar" 
                                                         style="width: {{ $pourcentage }}%" 
                                                         aria-valuenow="{{ $pourcentage }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ $pourcentage }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th class="text-center">{{ number_format($stats['total_unites']) }}</th>
                                        <th class="text-center">{{ number_format($stats['total_paiements']) }}</th>
                                        <th class="text-end">{{ number_format($stats['total_recettes'], 0, ',', ' ') }} FCFA</th>
                                        <th class="text-end">
                                            @if($stats['total_paiements'] > 0)
                                                {{ number_format($stats['total_recettes'] / $stats['total_paiements'], 0, ',', ' ') }} FCFA
                                            @else
                                                0 FCFA
                                            @endif
                                        </th>
                                        <th class="text-center">100%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Analyse par catégorie -->
                        @if($donnees->where('categorie', '!=', null)->count() > 0)
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-chart-pie me-2"></i>
                                                Répartition par Catégorie
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $commercial = $donnees->where('categorie', 'activite_commerciale');
                                                $interet = $donnees->where('categorie', 'point_interet');
                                                $totalCommercial = $commercial->sum('total_recettes');
                                                $totalInteret = $interet->sum('total_recettes');
                                            @endphp
                                            <div class="mb-2">
                                                <strong>Activités commerciales :</strong> 
                                                {{ number_format($totalCommercial, 0, ',', ' ') }} FCFA
                                                <small class="text-muted">({{ $commercial->sum('nombre_unites') }} unités)</small>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Points d'intérêt :</strong> 
                                                {{ number_format($totalInteret, 0, ',', ' ') }} FCFA
                                                <small class="text-muted">({{ $interet->sum('nombre_unites') }} unités)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-trophy me-2"></i>
                                                Top Performers
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @foreach($donnees->take(3) as $index => $top)
                                                <div class="mb-2">
                                                    <span class="badge bg-warning me-2">{{ $index + 1 }}</span>
                                                    <strong>{{ $top->type_commerce }}</strong><br>
                                                    <small class="text-muted">
                                                        {{ number_format($top->total_recettes, 0, ',', ' ') }} FCFA 
                                                        ({{ $top->nombre_paiements }} paiements)
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                            </div>
                            <h5 class="text-muted">Aucune donnée trouvée</h5>
                            <p class="text-muted">
                                Aucun paiement n'a été trouvé pour les critères sélectionnés sur la période {{ $stats['periode'] }}.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="{{ route('rapports.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>
                Retour aux rapports
            </a>
            <button onclick="window.print()" class="btn btn-primary me-2">
                <i class="fas fa-print me-1"></i>
                Imprimer
            </button>
            <a href="{{ route('rapports.type-commerce', array_merge(request()->all(), ['format' => 'pdf'])) }}" 
               class="btn btn-danger me-2" target="_blank">
                <i class="fas fa-file-pdf me-1"></i>
                Télécharger PDF
            </a>
            <a href="{{ route('rapports.type-commerce', array_merge(request()->all(), ['format' => 'excel'])) }}" 
               class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i>
                Télécharger Excel
            </a>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-tools, .breadcrumb, .navbar, .footer {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 11px;
    }
    
    .container-fluid {
        padding: 0 !important;
    }
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
}
.bg-gradient-success {
    background: linear-gradient(45deg, #28a745, #1e7e34);
}
.bg-gradient-warning {
    background: linear-gradient(45deg, #ffc107, #d39e00);
}
.bg-gradient-info {
    background: linear-gradient(45deg, #17a2b8, #117a8b);
}

.display-6 {
    font-size: 2rem;
}
</style>
@endsection 