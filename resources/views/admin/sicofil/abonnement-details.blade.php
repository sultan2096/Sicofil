@extends('layouts.app')

@section('title', 'Admin SICOFIL - Abonnement')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-receipt"></i> Détails Abonnement</h3>
        <a href="{{ route('admin.sicofil.abonnements.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Abonnements
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">{{ $abonnement->commune_nom }}</h5>
            <div class="row">
                <div class="col-md-4"><strong>Formule:</strong> {{ ucfirst($abonnement->formule) }}</div>
                <div class="col-md-4"><strong>Statut:</strong> {{ ucfirst($abonnement->statut) }}</div>
                <div class="col-md-4"><strong>Expiration:</strong> {{ \Carbon\Carbon::parse($abonnement->date_expiration)->format('d/m/Y') }}</div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4"><strong>Montant fixe annuel:</strong> {{ number_format($abonnement->montant_fixe_annuel, 0, ',', ' ') }} FCFA</div>
                <div class="col-md-4"><strong>% recettes:</strong> {{ number_format($abonnement->pourcentage_recettes, 2, ',', ' ') }} %</div>
                <div class="col-md-4"><strong>Date début:</strong> {{ \Carbon\Carbon::parse($abonnement->date_debut)->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Factures liées</h5>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Période</th>
                        <th>Émission</th>
                        <th>Échéance</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $facture)
                    <tr>
                        <td>{{ $facture->numero_facture }}</td>
                        <td>{{ ucfirst($facture->periode) }}</td>
                        <td>{{ \Carbon\Carbon::parse($facture->date_emission)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($facture->date_echeance)->format('d/m/Y') }}</td>
                        <td>{{ number_format($facture->montant_total, 0, ',', ' ') }} FCFA</td>
                        <td>{{ ucfirst($facture->statut) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Aucune facture</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
