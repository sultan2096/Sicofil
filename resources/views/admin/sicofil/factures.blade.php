@extends('layouts.app')

@section('title', 'Admin SICOFIL - Facturation SaaS')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><i class="fas fa-file-invoice"></i> Facturation SaaS</h3>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form method="POST" action="{{ route('admin.sicofil.factures.generate') }}" class="d-flex gap-2">
                @csrf
                <select name="periode" class="form-select form-select-sm">
                    <option value="mensuel">Mensuel</option>
                    <option value="trimestriel">Trimestriel</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-bolt"></i> Générer les factures
                </button>
            </form>
            @if($factures->total() > 0)
            <a href="{{ route('admin.sicofil.factures.export.pdf') }}" class="btn btn-sm btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Exporter PDF
            </a>
            <a href="{{ route('admin.sicofil.factures.export.excel') }}" class="btn btn-sm btn-success">
                <i class="fas fa-file-excel"></i> Exporter Excel
            </a>
            @endif
        </div>
    </div>
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">En attente</div>
                    <div class="h4">{{ $statsFactures['en_attente'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Payées</div>
                    <div class="h4">{{ $statsFactures['payees'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">En retard</div>
                    <div class="h4">{{ $statsFactures['en_retard'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="text-muted">Montant attendu</div>
                    <div class="h5">{{ number_format($statsFactures['montant_total_attendu'], 0, ',', ' ') }} FCFA</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Commune</th>
                        <th>Numéro</th>
                        <th>Période</th>
                        <th>Émission</th>
                        <th>Échéance</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $facture)
                    <tr>
                        <td>{{ $facture->commune_nom }}</td>
                        <td><code>{{ $facture->numero_facture }}</code></td>
                        <td>{{ ucfirst($facture->periode) }}</td>
                        <td>{{ \Carbon\Carbon::parse($facture->date_emission)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($facture->date_echeance)->format('d/m/Y') }}</td>
                        <td><strong>{{ number_format($facture->montant_total, 0, ',', ' ') }} FCFA</strong></td>
                        <td>
                            @if($facture->statut === 'payee')
                                <span class="badge bg-success">Payée</span>
                            @elseif($facture->statut === 'en_retard')
                                <span class="badge bg-danger">En retard</span>
                            @elseif($facture->statut === 'en_attente')
                                <span class="badge bg-warning text-dark">En attente</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($facture->statut) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.sicofil.factures.pdf', $facture->id) }}" class="btn btn-sm btn-outline-danger" title="Télécharger la facture PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <p class="mb-2"><i class="fas fa-file-invoice fa-2x"></i></p>
                            <p class="mb-1">Aucune facture</p>
                            <small class="d-block mb-3">Générez des factures à partir des abonnements actifs, ou créez d’abord un abonnement pour une commune.</small>
                            <a href="{{ url('/admin-sicofil/abonnements') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-receipt me-1"></i>Voir les abonnements</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $factures->links() }}
        </div>
    </div>
</div>
@endsection
