@extends('layouts.app')

@section('title', 'Admin SICOFIL - Dashboard National')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1"><i class="fas fa-crown"></i> Administration Centrale SICOFIL</h2>
                            <p class="mb-0">Plateforme Nationale Multi-Communes - Tableau de bord global</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <h4 class="mb-0">{{ $stats['total_communes'] }} Communes</h4>
                            <small>{{ $stats['total_communes_actives'] }} actives</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Communes Actives</p>
                            <h3 class="mb-0">{{ $stats['total_communes_actives'] }}</h3>
                        </div>
                        <div class="text-primary" style="font-size: 2rem;">
                            <i class="fas fa-city"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Utilisateurs</p>
                            <h3 class="mb-0">{{ number_format($stats['total_utilisateurs']) }}</h3>
                            <small class="text-muted">{{ $stats['total_collecteurs'] }} collecteurs</small>
                        </div>
                        <div class="text-success" style="font-size: 2rem;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Paiements (Mois)</p>
                            <h3 class="mb-0">{{ number_format($stats['total_paiements_mois']) }}</h3>
                        </div>
                        <div class="text-warning" style="font-size: 2rem;">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <p class="mb-1">Recettes (Mois)</p>
                    <h3 class="mb-0">{{ number_format($stats['recettes_totales_mois'], 0, ',', ' ') }} FCFA</h3>
                    <small>{{ number_format($stats['recettes_totales_annee'], 0, ',', ' ') }} FCFA (Année)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Communes & Abonnements expirant -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-trophy"></i> Top 10 Communes par Recettes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Commune</th>
                                    <th class="text-end">Recettes (Année)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCommunes as $index => $commune)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $commune->nom }}</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($commune->recettes ?? 0, 0, ',', ' ') }} FCFA</strong>
                                    </td>
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

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Abonnements Expirant (30j)</h5>
                </div>
                <div class="card-body">
                    @if($abonnementsExpirant->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Commune</th>
                                    <th>Formule</th>
                                    <th>Expiration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($abonnementsExpirant as $abonnement)
                                <tr>
                                    <td>{{ $abonnement->nom }}</td>
                                    <td><span class="badge bg-info">{{ ucfirst($abonnement->formule) }}</span></td>
                                    <td>
                                        <span class="text-danger">
                                            {{ \Carbon\Carbon::parse($abonnement->date_expiration)->format('d/m/Y') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-center text-muted py-3">Aucun abonnement n'expire prochainement</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Webhooks Stats & Notifications -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-webhook"></i> Activité Webhooks (24h)</h5>
                </div>
                <div class="card-body">
                    @if($webhooksStats->count() > 0)
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Statut</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($webhooksStats as $stat)
                            <tr>
                                <td>
                                    @if($stat->provider === 'wave')
                                    <span class="badge bg-info">Wave</span>
                                    @elseif($stat->provider === 'orange_money')
                                    <span class="badge bg-warning">Orange Money</span>
                                    @else
                                    <span class="badge bg-secondary">{{ ucfirst($stat->provider) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($stat->statut === 'success')
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Succès</span>
                                    @else
                                    <span class="text-danger"><i class="fas fa-times-circle"></i> {{ ucfirst($stat->statut) }}</span>
                                    @endif
                                </td>
                                <td class="text-end"><strong>{{ $stat->total }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-center text-muted py-3 mb-0">Aucune activité webhook (24h)</p>
                    <small class="text-muted">Les statistiques s'afficheront lorsque des webhooks Mobile Money (Wave, Orange Money, etc.) auront été reçus.</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bell"></i> Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>{{ $notificationsEnAttente }}</strong> notification(s) en attente d'envoi
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.sicofil.notifications') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> Voir toutes les notifications
                        </a>
                        <a href="{{ route('admin.sicofil.webhooks.logs') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-history"></i> Logs webhooks complets
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liens rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-link"></i> Accès Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('communes.index') }}" class="btn btn-outline-primary btn-lg w-100 mb-2">
                                <i class="fas fa-city"></i><br>Gestion Communes
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.sicofil.abonnements.index') }}" class="btn btn-outline-success btn-lg w-100 mb-2">
                                <i class="fas fa-receipt"></i><br>Abonnements SaaS
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.sicofil.factures.index') }}" class="btn btn-outline-warning btn-lg w-100 mb-2">
                                <i class="fas fa-file-invoice"></i><br>Facturation
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ url('/horizon') }}" target="_blank" class="btn btn-outline-info btn-lg w-100 mb-2" title="File d'attente des jobs">
                                <i class="fas fa-tasks"></i><br>Horizon
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.card-header {
    border-radius: 10px 10px 0 0 !important;
}
</style>
@endsection
