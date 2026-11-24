@extends('layouts.app')

@section('title', 'Alertes - Retards de Paiement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Alertes - Retards de Paiement
                    </h3>
                    <div>
                        <button class="btn btn-primary" onclick="checkForUpdates()">
                            <i class="fas fa-sync-alt"></i>
                            Actualiser
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Résumé des alertes -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $unitesEnRetard->count() }}</h5>
                                    <small>Unités en retard</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $unitesEnRetard->where('retard_jours', '>', 30)->count() }}</h5>
                                    <small>Retard > 30 jours</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $unitesEnRetard->where('dernier_paiement', null)->count() }}</h5>
                                    <small>Jamais payé</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $alertes->count() }}</h5>
                                    <small>Alertes cette semaine</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($unitesEnRetard->isEmpty())
                        <div class="alert alert-success text-center py-5">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h4>Excellent !</h4>
                            <p class="mb-0">Aucune unité commerciale n'est en retard de paiement.</p>
                        </div>
                    @else
                        <!-- Liste des unités en retard -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Unité Commerciale</th>
                                        <th>Zone</th>
                                        <th>Taxe & Périodicité</th>
                                        <th>Dernier Paiement</th>
                                        <th>Période Manquée</th>
                                        <th>Retard</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unitesEnRetard as $data)
                                        @php
                                            $unite = $data['unite'];
                                            $retardJours = $data['retard_jours'];
                                            $severite = $retardJours > 60 ? 'danger' : ($retardJours > 30 ? 'warning' : 'info');
                                        @endphp
                                        <tr class="table-{{ $severite }}">
                                            <td>
                                                <strong>{{ $unite->nom_proprietaire }}</strong><br>
                                                <small class="text-muted">{{ $unite->nature }}</small><br>
                                                @if($unite->telephone)
                                                    <small><i class="fas fa-phone"></i> {{ $unite->telephone }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $unite->zone->nom }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $unite->taxe->nom }}</strong><br>
                                                <small class="text-muted">
                                                    {{ number_format($unite->taxe->montant, 0, ',', ' ') }} FCFA
                                                </small><br>
                                                <span class="badge bg-primary">
                                                    {{ ucfirst($unite->taxe->periodicite) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($data['dernier_paiement'])
                                                    <strong>{{ $data['dernier_paiement']->format('d/m/Y') }}</strong><br>
                                                    <small class="text-muted">{{ $data['dernier_paiement']->diffForHumans() }}</small>
                                                @else
                                                    <span class="text-danger">
                                                        <i class="fas fa-times"></i> Jamais payé
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">{{ $data['periode_manquee'] }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $severite }} fs-6">
                                                    {{ $retardJours }} jour{{ $retardJours > 1 ? 's' : '' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('unites.show', $unite) }}" 
                                                       class="btn btn-sm btn-outline-info" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('paiements.create') }}?unite_id={{ $unite->id }}" 
                                                       class="btn btn-sm btn-outline-success" title="Enregistrer paiement">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                    @if($unite->telephone)
                                                        <a href="tel:{{ $unite->telephone }}" 
                                                           class="btn btn-sm btn-outline-primary" title="Appeler">
                                                            <i class="fas fa-phone"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Légende -->
                        <div class="mt-3">
                            <h6>Légende :</h6>
                            <span class="badge bg-info me-2">0-30 jours</span>
                            <span class="badge bg-warning me-2">31-60 jours</span>
                            <span class="badge bg-danger me-2">Plus de 60 jours</span>
                        </div>
                    @endif

                    <!-- Historique des alertes récentes -->
                    @if($alertes->isNotEmpty())
                        <div class="mt-4">
                            <h5><i class="fas fa-history"></i> Alertes récentes (7 derniers jours)</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Unité</th>
                                            <th>Zone</th>
                                            <th>Retard</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($alertes->take(10) as $alerte)
                                            @php
                                                $details = $alerte->details;
                                                $vue = isset($details['seen_by']);
                                            @endphp
                                            <tr class="{{ $vue ? 'table-light' : '' }}">
                                                <td>{{ $alerte->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $details['unite_name'] ?? 'N/A' }}</td>
                                                <td>{{ $details['zone_name'] ?? 'N/A' }}</td>
                                                <td>{{ $details['retard_jours'] ?? 0 }} jour(s)</td>
                                                <td>
                                                    @if($vue)
                                                        <span class="badge bg-success">Vue</span>
                                                    @else
                                                        <span class="badge bg-warning">Nouvelle</span>
                                                        <form method="POST" action="{{ route('alerts.mark-seen', $alerte->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Marquer comme vue">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkForUpdates() {
    // Recharger la page pour actualiser les données
    window.location.reload();
}

// Auto-refresh toutes les 5 minutes
setInterval(function() {
    fetch('{{ route('api.alerts.overdue-count') }}')
        .then(response => response.json())
        .then(data => {
            if (data.count > 0) {
                // Afficher une notification discrète si de nouveaux retards sont détectés
                console.log('Alertes de retard mises à jour:', data.message);
            }
        })
        .catch(error => console.log('Erreur lors de la vérification des alertes:', error));
}, 300000); // 5 minutes
</script>
@endsection 