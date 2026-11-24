@extends('layouts.app')

@section('title', 'Rapport Nature et Type de Commerce')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group"></i>
                        Rapport combiné Nature et Type de Commerce
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">
                            Période : {{ $stats['periode'] }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- KPIs -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-store"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Unités</span>
                                    <span class="info-box-number">{{ number_format($stats['total_unites'] ?? 0, 0, ',', ' ') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Recettes</span>
                                    <span class="info-box-number">{{ number_format($stats['total_recettes'] ?? 0, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-list"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paiements</span>
                                    <span class="info-box-number">{{ number_format($stats['total_paiements'] ?? 0, 0, ',', ' ') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-th"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Combinaisons</span>
                                    <span class="info-box-number">{{ $stats['nombre_combinaisons'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau détaillé -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nature</th>
                                    <th>Type de commerce</th>
                                    <th>Catégorie</th>
                                    <th class="text-center">Unités</th>
                                    <th class="text-center">Paiements</th>
                                    <th class="text-right">Recettes (FCFA)</th>
                                    <th class="text-right">Moyenne (FCFA)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($donnees as $ligne)
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary">{{ $ligne->nature ?? 'Non spécifiée' }}</span>
                                    </td>
                                    <td><strong>{{ $ligne->type_commerce ?? 'Non spécifié' }}</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $ligne->categorie)) ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $ligne->nombre_unites ?? 0 }}</td>
                                    <td class="text-center">{{ $ligne->nombre_paiements ?? 0 }}</td>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($ligne->total_recettes ?? 0, 0, ',', ' ') }}
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($ligne->montant_moyen ?? 0, 0, ',', ' ') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Aucune donnée trouvée pour cette période.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="thead-light">
                                <tr>
                                    <th colspan="3" class="text-right">TOTALS</th>
                                    <th class="text-center">{{ number_format($stats['total_unites'] ?? 0, 0, ',', ' ') }}</th>
                                    <th class="text-center">{{ number_format($stats['total_paiements'] ?? 0, 0, ',', ' ') }}</th>
                                    <th class="text-right">{{ number_format($stats['total_recettes'] ?? 0, 0, ',', ' ') }}</th>
                                    <th></th>
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
    .card-tools, .btn { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .table { font-size: 11px; }
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
