@extends('layouts.app')

@section('title', 'Admin SICOFIL - Statistiques')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-chart-line"></i> Statistiques Globales</h3>
        <a href="{{ route('admin.sicofil.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Recettes par mois (12 derniers mois)</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recettesParMois as $row)
                            <tr>
                                <td>{{ $row['mois'] }}</td>
                                <td class="text-end">{{ number_format($row['montant'], 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Répartition par mode de paiement</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mode</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paiementsParMode as $mode)
                            <tr>
                                <td>{{ $mode->mode_paiement ?? 'indetermine' }}</td>
                                <td class="text-end">{{ $mode->total }}</td>
                                <td class="text-end">{{ number_format($mode->montant_total, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Aucune donnée</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
