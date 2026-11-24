@extends('layouts.app')

@section('title', 'Rapport Collecteurs - SICOSA')

@section('content')
<div class="container-fluid">

    <!-- En-tête du rapport -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <img src="{{ asset('images/logos/snd.jpeg') }}" alt="Logo SICOSA" 
                                 class="img-fluid rounded-circle border border-white" 
                                 style="max-height: 60px;">
                        </div>
                        <div class="col-10 text-start">
                            <h1 class="h2 mb-1">RAPPORT COLLECTEURS</h1>
                            <p class="mb-0">SYSTÈME D'INFORMATION COMMERCIALE DE SANDIARA</p>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <div class="row">
                        <div class="col-6">
                            <strong>Période :</strong> {{ $stats['periode'] }}
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted">
                                Généré le {{ now()->format('d/m/Y à H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x mb-2"></i>
                    <h3>{{ $stats['total_collecteurs'] }}</h3>
                    <p>Collecteurs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body text-center">
                    <i class="fas fa-money-check-alt fa-3x mb-2"></i>
                    <h3>{{ $stats['total_paiements'] }}</h3>
                    <p>Paiements</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body text-center">
                    <i class="fas fa-coins fa-3x mb-2"></i>
                    <h3>{{ number_format($stats['total_montant'], 0, ',', ' ') }}</h3>
                    <p>FCFA collectés</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-3x mb-2"></i>
                    <h3>
                        {{ $stats['total_paiements'] > 0 ? number_format($stats['total_montant'] / $stats['total_paiements'], 0, ',', ' ') : '0' }}
                    </h3>
                    <p>Moyenne</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance des Collecteurs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Performance des Collecteurs
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Collecteur</th>
                                    <th class="text-center">Paiements</th>
                                    <th class="text-end">Montant Total</th>
                                    <th class="text-end">Moyenne</th>
                                    <th class="text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $maxMontant = collect($stats_collecteurs)->max('montant_total');
                                @endphp
                                @foreach($stats_collecteurs as $stat)
                                    @php
                                        $performance = $maxMontant > 0 ? ($stat['montant_total'] / $maxMontant) * 100 : 0;
                                        if($performance >= 80) {
                                            $progressClass = 'bg-success';
                                        } elseif($performance >= 60) {
                                            $progressClass = 'bg-warning';
                                        } elseif($performance >= 40) {
                                            $progressClass = 'bg-info';
                                        } else {
                                            $progressClass = 'bg-danger';
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px; font-weight: bold;">
                                                    {{ strtoupper(substr($stat['collecteur']->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $stat['collecteur']->name }}</strong>
                                                    <br><small class="text-muted">{{ $stat['collecteur']->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info fs-6">{{ $stat['nombre_paiements'] }}</span>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">{{ number_format($stat['montant_total'], 0, ',', ' ') }} FCFA</strong>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-primary">{{ number_format($stat['moyenne_par_paiement'], 0, ',', ' ') }} FCFA</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $progressClass }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $performance }}%;">
                                                    {{ number_format($performance, 1) }}%
                                                </div>
                                            </div>
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

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="{{ route('rapports.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>
                Retour aux rapports
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-1"></i>
                Imprimer ce rapport
            </button>
        </div>
    </div>

    <!-- Footer -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center bg-light">
                    <p class="mb-1"><strong>SICOSA</strong> - Système d'Information Commerciale de Sandiara</p>
                    <small class="text-muted">Rapport généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: bold;
}

@media print {
    .btn {
        display: none;
    }
}
</style>
@endpush
