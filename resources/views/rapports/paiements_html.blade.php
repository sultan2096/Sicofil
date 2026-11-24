@extends('layouts.app')

@section('title', 'Rapport des Paiements par Unité Commerciale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h3 class="card-title">
                        <i class="fas fa-money-check-alt"></i>
                        Rapport des Paiements par Unité Commerciale
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">
                            Période : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- KPIs -->
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
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paiements Validés</span>
                                    <span class="info-box-number">{{ $stats['paiements_valides'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">En Attente</span>
                                    <span class="info-box-number">{{ $stats['paiements_en_attente'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-list"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Paiements</span>
                                    <span class="info-box-number">{{ $stats['total_paiements'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau détaillé -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Unité commerciale</th>
                                    <th>Nature / Type</th>
                                    <th>Zone</th>
                                    <th>Collecteur</th>
                                    <th>Taxe</th>
                                    <th>Montant</th>
                                    <th>Avance</th>
                                    <th>Reste</th>
                                    <th>Statut</th>
                                    <th>Mode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paiements as $index => $paiement)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $paiement->date_paiement ? \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        <strong>{{ $paiement->unite->nom_commercial ?? $paiement->unite->nom_complet_proprietaire ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $paiement->unite->type_commerce ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $paiement->unite->nature ?? 'N/A' }}</span><br>
                                        <small>{{ $paiement->unite->type_commerce ?? '' }}</small>
                                    </td>
                                    <td>{{ $paiement->unite->zone->nom ?? 'N/A' }}</td>
                                    <td>{{ $paiement->collecteur->name ?? 'N/A' }}</td>
                                    <td>{{ $paiement->unite->taxe->nom ?? 'N/A' }}</td>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="text-right text-success">
                                        {{ $paiement->montant_avance > 0 ? number_format($paiement->montant_avance, 0, ',', ' ') . ' FCFA' : '-' }}
                                    </td>
                                    <td class="text-right text-danger">
                                        {{ $paiement->montant_restant > 0 ? number_format($paiement->montant_restant, 0, ',', ' ') . ' FCFA' : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statutBadge = match($paiement->statut) {
                                                'valide' => 'badge-success',
                                                'en_attente' => 'badge-warning',
                                                'annule' => 'badge-danger',
                                                default => 'badge-secondary'
                                            };
                                            $statutLabel = match($paiement->statut) {
                                                'valide' => 'Validé',
                                                'en_attente' => 'En attente',
                                                'annule' => 'Annulé',
                                                default => ucfirst($paiement->statut)
                                            };
                                        @endphp
                                        <span class="badge {{ $statutBadge }}">{{ $statutLabel }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light">{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Aucun paiement trouvé pour cette période.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="thead-light">
                                <tr>
                                    <th colspan="7" class="text-right">TOTALS</th>
                                    <th class="text-right">{{ number_format($stats['total_recettes'] ?? 0, 0, ',', ' ') }} FCFA</th>
                                    <th class="text-right text-success">{{ number_format($stats['montant_avance_total'] ?? 0, 0, ',', ' ') }} FCFA</th>
                                    <th class="text-right text-danger">{{ number_format($stats['montant_restant_total'] ?? 0, 0, ',', ' ') }} FCFA</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
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
    .card-tools, .btn, .dataTables_length, .dataTables_filter, .dataTables_paginate, .dataTables_info {
        display: none !important;
    }
    .card { border: none !important; box-shadow: none !important; }
    .table { font-size: 11px; }
    .badge { border: 1px solid #ddd; }
}

.info-box {
    display: flex; min-height: 80px; background: #fff; width: 100%;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem; margin-bottom: 1rem;
}
.info-box-icon {
    border-radius: 0.25rem 0 0 0.25rem; display: flex; align-items: center; justify-content: center;
    font-size: 1.875rem; font-weight: 700; text-align: center; text-transform: uppercase;
    width: 70px; color: #fff;
}
.info-box-content { display: flex; flex-direction: column; justify-content: center; line-height: 1.8; flex: 1; padding: 0 10px; }
.info-box-text { display: block; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-transform: uppercase; }
.info-box-number { display: block; font-weight: 700; }
</style>
@endsection
