@extends('layouts.app')

@section('title', 'Journal des Activités - SICOSA')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Journal des Activités du Système
                    </h3>
                    <div class="card-tools">
                        <span class="badge bg-light text-primary">
                            <i class="fas fa-database me-1"></i>
                            {{ $logs->total() }} entrées
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtres de recherche -->
                    <div class="card border-0 bg-light mb-4">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0">
                                <i class="fas fa-filter me-2"></i>
                                Filtres de recherche
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('logs.index') }}">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="date_debut" class="form-label fw-bold">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            Date de début
                                        </label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="date_debut" 
                                               name="date_debut" 
                                               value="{{ request('date_debut') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="date_fin" class="form-label fw-bold">
                                            <i class="fas fa-calendar-check me-1"></i>
                                            Date de fin
                                        </label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="date_fin" 
                                               name="date_fin" 
                                               value="{{ request('date_fin') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="action" class="form-label fw-bold">
                                            <i class="fas fa-tasks me-1"></i>
                                            Type d'action
                                        </label>
                                        <select class="form-select" id="action" name="action">
                                            <option value="">Toutes les actions</option>
                                            @foreach($actions as $action)
                                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="user_id" class="form-label fw-bold">
                                            <i class="fas fa-users me-1"></i>
                                            Utilisateur
                                        </label>
                                        <select class="form-select" id="user_id" name="user_id">
                                            <option value="">Tous les utilisateurs</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i>
                                            Appliquer les filtres
                                        </button>
                                        <a href="{{ route('logs.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-undo me-1"></i>
                                            Réinitialiser
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistiques du système -->
                    @if($logs->total() > 0)
                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="card bg-gradient-primary text-white h-100">
                                    <div class="card-body text-center">
                                        <div class="display-6 mb-2">
                                            <i class="fas fa-clipboard-list"></i>
                                        </div>
                                        <h4 class="mb-1">{{ number_format($logs->total()) }}</h4>
                                        <small class="opacity-75">Activités totales</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="card bg-gradient-success text-white h-100">
                                    <div class="card-body text-center">
                                        <div class="display-6 mb-2">
                                            <i class="fas fa-money-check-alt"></i>
                                        </div>
                                        <h4 class="mb-1">{{ $logs->where('action', 'payment')->count() }}</h4>
                                        <small class="opacity-75">Paiements enregistrés</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="card bg-gradient-info text-white h-100">
                                    <div class="card-body text-center">
                                        <div class="display-6 mb-2">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <h4 class="mb-1">{{ $logs->where('action', 'create_user')->count() }}</h4>
                                        <small class="opacity-75">Utilisateurs créés</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="card bg-gradient-warning text-white h-100">
                                    <div class="card-body text-center">
                                        <div class="display-6 mb-2">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <h4 class="mb-1">{{ $users->where('role', 'collecteur')->count() }}</h4>
                                        <small class="opacity-75">Collecteurs actifs</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Table des activités -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 140px;">
                                        <i class="fas fa-clock me-1"></i>
                                        Date & Heure
                                    </th>
                                    <th style="width: 200px;">
                                        <i class="fas fa-user me-1"></i>
                                        Utilisateur
                                    </th>
                                    <th class="text-center" style="width: 150px;">
                                        <i class="fas fa-bolt me-1"></i>
                                        Action
                                    </th>
                                    <th>
                                        <i class="fas fa-info-circle me-1"></i>
                                        Description détaillée
                                    </th>
                                    <th class="text-center" style="width: 120px;">
                                        <i class="fas fa-globe me-1"></i>
                                        Adresse IP
                                    </th>
                                    <th class="text-center" style="width: 80px;">
                                        <i class="fas fa-cog me-1"></i>
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr class="border-bottom">
                                        <td class="text-center">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-primary">
                                                    {{ $log->created_at->format('d/m/Y') }}
                                                </span>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $log->created_at->format('H:i:s') }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($log->user)
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        @switch($log->user->role)
                                                            @case('maire')
                                                                <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center" 
                                                                     style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-crown text-white"></i>
                                                                </div>
                                                                @break
                                                            @case('admin')
                                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" 
                                                                     style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-user-shield text-white"></i>
                                                                </div>
                                                                @break
                                                            @case('collecteur')
                                                                <div class="rounded-circle bg-success d-flex align-items-center justify-content-center" 
                                                                     style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-user-tie text-white"></i>
                                                                </div>
                                                                @break
                                                            @default
                                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                                                     style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-user text-white"></i>
                                                                </div>
                                                        @endswitch
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $log->user->name }}</div>
                                                        <small class="text-muted">
                                                            @switch($log->user->role)
                                                                @case('maire')
                                                                    <span class="badge bg-warning">
                                                                        <i class="fas fa-crown me-1"></i>
                                                                        Maire
                                                                    </span>
                                                                    @break
                                                                @case('admin')
                                                                    <span class="badge bg-primary">
                                                                        <i class="fas fa-user-shield me-1"></i>
                                                                        Admin
                                                                    </span>
                                                                    @break
                                                                @case('collecteur')
                                                                    <span class="badge bg-success">
                                                                        <i class="fas fa-user-tie me-1"></i>
                                                                        Collecteur
                                                                    </span>
                                                                    @break
                                                                @default
                                                                    <span class="badge bg-secondary">Utilisateur</span>
                                                            @endswitch
                                                        </small>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-user-slash text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span class="fw-bold text-danger">Utilisateur supprimé</span>
                                                        <br>
                                                        <small class="text-muted">Compte désactivé</small>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $actionConfig = [
                                                    'login' => ['icon' => 'sign-in-alt', 'color' => 'success', 'label' => 'Connexion'],
                                                    'logout' => ['icon' => 'sign-out-alt', 'color' => 'warning', 'label' => 'Déconnexion'],
                                                    'payment' => ['icon' => 'money-check-alt', 'color' => 'primary', 'label' => 'Paiement'],
                                                    'create_user' => ['icon' => 'user-plus', 'color' => 'info', 'label' => 'Création utilisateur'],
                                                    'update_user' => ['icon' => 'user-edit', 'color' => 'warning', 'label' => 'Modification utilisateur'],
                                                    'delete_user' => ['icon' => 'user-times', 'color' => 'danger', 'label' => 'Suppression utilisateur'],
                                                    'create_taxe' => ['icon' => 'plus-circle', 'color' => 'success', 'label' => 'Création taxe'],
                                                    'update_taxe' => ['icon' => 'edit', 'color' => 'warning', 'label' => 'Modification taxe'],
                                                    'delete_taxe' => ['icon' => 'trash', 'color' => 'danger', 'label' => 'Suppression taxe'],
                                                    'delete_unite' => ['icon' => 'store-slash', 'color' => 'danger', 'label' => 'Suppression unité'],
                                                    'cancel_payment' => ['icon' => 'ban', 'color' => 'danger', 'label' => 'Annulation paiement'],
                                                ];
                                                $config = $actionConfig[$log->action] ?? ['icon' => 'info-circle', 'color' => 'secondary', 'label' => ucfirst(str_replace('_', ' ', $log->action))];
                                            @endphp
                                            <span class="badge bg-{{ $config['color'] }} px-3 py-2">
                                                <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                                {{ $config['label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="log-description">
                                                @if($log->description)
                                                    <div class="fw-bold text-dark mb-1">{{ $log->description }}</div>
                                                @endif
                                                
                                                @if($log->details)
                                                    @php
                                                        $details = is_string($log->details) ? json_decode($log->details, true) : $log->details;
                                                    @endphp
                                                    @if(is_array($details))
                                                        <div class="small text-muted">
                                                            @switch($log->action)
                                                                @case('login')
                                                                @case('logout')
                                                                    <div class="d-flex flex-column">
                                                                        @if(isset($details['identifiant']))
                                                                            <span>
                                                                                <i class="fas fa-id-card me-1"></i>
                                                                                <strong>Identifiant:</strong> {{ $details['identifiant'] }}
                                                                            </span>
                                                                        @endif
                                                                        @if(isset($details['role']))
                                                                            <span>
                                                                                <i class="fas fa-user-tag me-1"></i>
                                                                                <strong>Rôle:</strong> {{ ucfirst($details['role']) }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    @break
                                                                @case('payment')
                                                                    <div class="d-flex flex-column">
                                                                        @if(isset($details['montant']))
                                                                            <span class="text-success fw-bold">
                                                                                <i class="fas fa-coins me-1"></i>
                                                                                {{ number_format($details['montant'], 0, ',', ' ') }} FCFA
                                                                            </span>
                                                                        @endif
                                                                        @if(isset($details['mode']))
                                                                            <span>
                                                                                <i class="fas fa-credit-card me-1"></i>
                                                                                Mode: {{ ucfirst($details['mode']) }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    @break
                                                                @case('create_user')
                                                                @case('update_user')
                                                                    <div class="d-flex flex-column">
                                                                        @if(isset($details['user_name']))
                                                                            <span>
                                                                                <i class="fas fa-user me-1"></i>
                                                                                <strong>Nom:</strong> {{ $details['user_name'] }}
                                                                            </span>
                                                                        @endif
                                                                        @if(isset($details['user_role']))
                                                                            <span>
                                                                                <i class="fas fa-user-tag me-1"></i>
                                                                                <strong>Rôle:</strong> {{ ucfirst($details['user_role']) }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    @break
                                                                @case('create_taxe')
                                                                @case('update_taxe')
                                                                    <div class="d-flex flex-column">
                                                                        @if(isset($details['taxe_nom']))
                                                                            <span>
                                                                                <i class="fas fa-tag me-1"></i>
                                                                                <strong>Taxe:</strong> {{ $details['taxe_nom'] }}
                                                                            </span>
                                                                        @endif
                                                                        @if(isset($details['montant']))
                                                                            <span class="text-primary">
                                                                                <i class="fas fa-money-bill me-1"></i>
                                                                                {{ number_format($details['montant'], 0, ',', ' ') }} FCFA
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    @break
                                                                @case('delete_user')
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-danger">
                                                                            <i class="fas fa-user-times me-1"></i>
                                                                            <strong>Supprimé:</strong> {{ $details['deleted_user_name'] ?? $details['deleted_user_identifiant'] ?? 'N/A' }}
                                                                        </span>
                                                                        @if(isset($details['deleted_user_role']))
                                                                            <span>
                                                                                <i class="fas fa-user-tag me-1"></i>
                                                                                Rôle: {{ ucfirst($details['deleted_user_role']) }}
                                                                            </span>
                                                                        @endif
                                                                        @if(isset($details['deleted_by_role']) && $details['deleted_by_role'] === 'admin')
                                                                            <span class="text-warning fw-bold">
                                                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                                                Suppression administrative
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    @break
                                                                @case('delete_unite')
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-danger">
                                                                            <i class="fas fa-store-slash me-1"></i>
                                                                            <strong>Unité supprimée:</strong> {{ $details['nom_proprietaire'] ?? 'N/A' }}
                                                                        </span>
                                                                        @if(isset($details['type_commerce']))
                                                                            <span>
                                                                                <i class="fas fa-shopping-bag me-1"></i>
                                                                                Type: {{ $details['type_commerce'] }}
                                                                            </span>
                                                                        @endif
                                                                        @if(isset($details['zone_nom']))
                                                                            <span>
                                                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                                                Zone: {{ $details['zone_nom'] }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    @break
                                                                @case('delete_taxe')
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-danger">
                                                                            <i class="fas fa-trash me-1"></i>
                                                                            <strong>Taxe supprimée:</strong> {{ $details['taxe_nom'] ?? 'N/A' }}
                                                                        </span>
                                                                        @if(isset($details['taxe_montant']))
                                                                            <span>
                                                                                <i class="fas fa-money-bill me-1"></i>
                                                                                Montant: {{ number_format($details['taxe_montant'], 0, ',', ' ') }} FCFA
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    @break
                                                                @default
                                                                    <div class="text-muted small">
                                                                        <i class="fas fa-info-circle me-1"></i>
                                                                        {{ json_encode($details) }}
                                                                    </div>
                                                            @endswitch
                                                        </div>
                                                    @else
                                                        <div class="small text-muted">{{ $log->details }}</div>
                                                    @endif
                                                @else
                                                    <div class="small text-muted">
                                                        <i class="fas fa-info me-1"></i>
                                                        {{ $config['label'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <code class="small bg-light p-2 rounded">
                                                <i class="fas fa-network-wired me-1"></i>
                                                {{ $log->ip_address ?? 'N/A' }}
                                            </code>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('logs.show', $log) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Voir les détails complets"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="mb-3">
                                                    <i class="fas fa-clipboard-list fa-4x text-muted"></i>
                                                </div>
                                                <h4 class="text-muted mb-2">Aucune activité trouvée</h4>
                                                <p class="text-muted mb-0">
                                                    Aucune activité ne correspond aux critères de recherche sélectionnés.
                                                </p>
                                                <div class="mt-3">
                                                    <a href="{{ route('logs.index') }}" class="btn btn-outline-primary">
                                                        <i class="fas fa-undo me-1"></i>
                                                        Voir toutes les activités
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination améliorée -->
                    @if($logs->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Affichage de {{ $logs->firstItem() }} à {{ $logs->lastItem() }} 
                                sur {{ $logs->total() }} résultats
                            </div>
                            <div>
                                {{ $logs->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles personnalisés pour un affichage professionnel */
.log-description {
    max-width: 350px;
    word-wrap: break-word;
    line-height: 1.4;
}

.table td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

.bg-gradient-success {
    background: linear-gradient(45deg, #28a745, #1e7e34);
}

.bg-gradient-info {
    background: linear-gradient(45deg, #17a2b8, #117a8b);
}

.bg-gradient-warning {
    background: linear-gradient(45deg, #ffc107, #d39e00);
}

.card-tools {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.border-bottom {
    border-bottom: 1px solid #dee2e6 !important;
}

code {
    font-size: 0.8rem;
}

.display-6 {
    font-size: 2rem;
}

/* Animation pour les badges */
.badge {
    transition: all 0.2s ease-in-out;
}

.badge:hover {
    transform: scale(1.05);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .log-description {
        max-width: 200px;
    }
    
    .display-6 {
        font-size: 1.5rem;
    }
}
</style>

@section('scripts')
<script>
// Initialiser les tooltips Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
@endsection