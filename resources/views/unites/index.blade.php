@extends('layouts.app')

@section('title', 'Gestion des Unités Commerciales')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-store me-2"></i>
                @if(auth()->user()->role === 'maire')
                    Consultation des Unités Commerciales
                @else
                        Gestion des Unités Commerciales
                @endif
            </h1>
            <div class="text-muted small ms-1">
                @if(auth()->user()->role === 'maire')
                    Consultation des unités commerciales recensées (lecture seule)
                @else
                    Liste des unités commerciales recensées
                @endif
                    </div>
                </div>
        @if(auth()->user()->role === 'gestionnaire')
            <a href="{{ route('unites.export-excel', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-2"></i>Exporter Excel
            </a>
            <a href="{{ route('unites.export-pdf', request()->query()) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf me-2"></i>Exporter PDF
            </a>
            <a href="{{ route('unites.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvelle unité
            </a>
        @endif
    </div>
    <div class="card shadow-sm border-0">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <!-- Filtres -->
            <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <form method="GET" action="{{ route('unites.index') }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nom, téléphone...">
                            </div>

                            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2">
                                <label for="zone_id" class="form-label">Zone</label>
                                <select class="form-select" id="zone_id" name="zone_id">
                                    <option value="">Toutes les zones</option>
                                    @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                            {{ $zone->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2">
                                <label for="taxe_id" class="form-label">Taxation</label>
                                <select class="form-select" id="taxe_id" name="taxe_id">
                                    <option value="">Toutes les taxes</option>
                                    @foreach($taxes->sortBy('nom') as $taxe)
                                        <option value="{{ $taxe->id }}" {{ request('taxe_id') == $taxe->id ? 'selected' : '' }}>
                                            {{ $taxe->nom }} ({{ number_format($taxe->montant, 0, ',', ' ') }} FCFA)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2">
                                <label for="type_commerce" class="form-label">Type</label>
                                <select class="form-select" id="type_commerce" name="type_commerce">
                                    <option value="">Tous les types</option>
                                    @foreach($typesCommerce as $type)
                                        <option value="{{ $type }}" {{ request('type_commerce') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2 d-flex align-items-end gap-1">
                                <button type="submit" class="btn btn-outline-primary flex-fill">
                                    <i class="fas fa-search"></i>
                                    <span class="d-none d-sm-inline">Rechercher</span>
                                </button>
                                <a href="{{ route('unites.index') }}" class="btn btn-outline-secondary flex-fill">
                                    <i class="fas fa-times"></i>
                                    <span class="d-none d-sm-inline">Réinitialiser</span>
                                </a>
                            </div>
                        </div>
                            </form>
                        </div>
                    </div>
                    <!-- Table des unités -->
                    <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                                <tr>
                                    <th>Propriétaire</th>
                            <th>Type de commerce</th>
                                    <th>Contact</th>
                                    <th>Zone</th>
                            <th>Taxation</th>
                                    <th>Statut</th>
                            @if(auth()->user()->role === 'gestionnaire')
                                    <th>Actions</th>
                            @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unites as $unite)
                                    <tr>
                                        <td>
                                    <strong><i class="fas fa-user me-1 text-primary"></i> {{ $unite->nom_complet_proprietaire }}</strong>
                                            @if($unite->ninea)
                                        @php
                                            $isTransport = false;
                                            
                                            // Vérifier type de commerce
                                            $typeCommerce = strtolower($unite->type_commerce ?? '');
                                            
                                            // Vérifier nature
                                            $nature = strtolower($unite->nature ?? '');
                                            
                                            // Vérifier nom de la taxe
                                            $taxeName = $unite->taxe ? strtolower($unite->taxe->nom) : '';
                                            
                                            // Mots-clés de transport
                                            $transportKeywords = ['transport', 'taxi', 'clado', 'moto', 'jakarta', 'bus', 'car', 'véhicule', 'auto', 'voiture', 'camion', 'truck', 'tricycle', 'scooter', 'motocycle', 'automobile', 'minibus', 'microbus'];
                                            
                                            foreach ($transportKeywords as $keyword) {
                                                if (strpos($typeCommerce, $keyword) !== false || 
                                                    strpos($nature, $keyword) !== false || 
                                                    strpos($taxeName, $keyword) !== false) {
                                                    $isTransport = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        
                                        <br><small class="text-muted">
                                            @if($isTransport)
                                                <i class="fas fa-car me-1 text-info"></i>Matricule: {{ $unite->ninea }}
                                            @else
                                                <i class="fas fa-building me-1 text-primary"></i>NINEA: {{ $unite->ninea }}
                                            @endif
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-25 text-secondary">
                                        <i class="fas fa-tag me-1"></i>{{ $unite->type_commerce }}
                                    </span>
                                    @if($unite->nature)
                                        <br><small class="text-muted">{{ $unite->nature }}</small>
                                            @endif
                                        </td>
                                        <td>
                                    @if($unite->telephone)
                                        <span class="badge bg-info bg-opacity-25 text-info">
                                            <i class="fas fa-phone me-1"></i>{{ $unite->telephone }}
                                        </span>
                                    @endif
                                            @if($unite->email)
                                        <br><span class="text-muted"><i class="fas fa-envelope me-1"></i>{{ $unite->email }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($unite->zone)
                                        <span class="badge bg-success bg-opacity-25 text-success"><i class="fas fa-map-marker-alt me-1"></i> {{ $unite->zone->nom }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Non assignée</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($unite->taxe)
                                        <span class="badge bg-primary bg-opacity-25 text-primary">{{ $unite->taxe->nom }}</span><br>
                                        <small class="text-success fw-bold">{{ number_format($unite->taxe->montant, 0, ',', ' ') }} FCFA</small>
                                            @else
                                                <span class="text-muted">Non définie</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $unite->actif ? 'success' : 'danger' }}">
                                        <i class="fas fa-{{ $unite->actif ? 'check-circle' : 'times-circle' }} me-1"></i>
                                                {{ $unite->actif ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                @if(auth()->user()->role === 'gestionnaire')
                                        <td>
                                        <div class="btn-group gap-1">
                                            <a href="{{ route('unites.edit', $unite) }}" class="btn btn-sm btn-outline-info" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <a href="{{ route('unites.delete', $unite->id) }}" 
                                               class="btn btn-sm btn-outline-danger" 
                                               title="Supprimer" 
                                               onclick="return confirm('⚠️ ATTENTION: Suppression définitive!\n\nCette action supprimera DÉFINITIVEMENT:\n✓ L\'unité de la base de données\n✓ L\'unité de la carte géographique\n✓ Tous les paiements associés\n✓ Les photos liées\n\nÊtes-vous sûr de vouloir continuer ?')">
                                                        <i class="fas fa-trash"></i>
                                            </a>
                                            </div>
                                        </td>
                                @endif
                                    </tr>
                                @empty
                                    <tr>
                                <td colspan="@if(auth()->user()->role === 'gestionnaire') 7 @else 6 @endif" class="text-center py-4">
                                            <i class="fas fa-store fa-3x mb-3 text-muted"></i>
                                            <p class="text-muted mb-0">Aucune unité commerciale trouvée</p>
                                    @if(request()->hasAny(['search', 'type_commerce', 'zone_id', 'taxe_id', 'statut']))
                                        <p class="text-muted"><small>Essayez de modifier vos critères de recherche</small></p>
                                    @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #1e3a8a;
    border-bottom: 2px solid #e5e7eb;
}
.table td {
    vertical-align: middle;
}
.table-hover tbody tr:hover {
    background-color: #f1f5f9;
    transition: background 0.2s;
}
.card {
    border-radius: 1rem;
}
.btn-group {
    gap: 0.5rem;
}
</style>
@endsection 