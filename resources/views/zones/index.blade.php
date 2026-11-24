@extends('layouts.app')

@section('title', 'Zones Fiscales - SICOSA')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-map-marked-alt me-2"></i>
                @if(auth()->user()->role === 'maire')
                    Consultation des Zones Fiscales
                @else
            Zones Fiscales
                @endif
        </h1>
            <div class="text-muted small ms-1">
                @if(auth()->user()->role === 'maire')
                    Consultation des zones fiscales de la commune (lecture seule)
                @else
                    Liste des zones fiscales de la commune
                @endif
            </div>
        </div>
        @if(auth()->user()->role === 'gestionnaire')
        <a href="{{ route('zones.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvelle zone
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-2">
                <form class="d-flex w-100 w-md-auto" method="GET" action="">
                    <input type="text" name="search" class="form-control me-2" placeholder="Rechercher une zone..." value="{{ request('search') }}">
                    <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Villages et Amonts</th>
                            <th>Description</th>
                            <th>Unités</th>
                            @if(auth()->user()->role === 'gestionnaire')
                            <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zones as $zone)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $zone->id }}</span></td>
                                <td class="fw-semibold"><i class="fas fa-map-marker-alt me-1 text-primary"></i> {{ $zone->nom }}</td>
                                <td>
                                    @php
                                        $villages = $zone->villages;
                                    @endphp
                                    @if(count($villages) > 0)
                                        <div class="villages-list" style="max-width: 300px;">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-map-marker-alt"></i> 
                                                <strong>{{ count($villages) }} village(s)</strong>
                                            </small>
                                            <div class="villages-tags">
                                                @foreach($villages as $village)
                                                    <span class="badge bg-secondary bg-opacity-75 me-1 mb-1" style="font-size: 0.75rem;">
                                                        {{ $village }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">Aucun village défini</span>
                                    @endif
                                </td>
                                <td><span class="text-muted small">{{ Str::limit($zone->description, 50) }}</span></td>
                                <td><span class="badge bg-info bg-opacity-25 text-info">{{ $zone->unitesCommerciales->count() }}</span></td>
                                @if(auth()->user()->role === 'gestionnaire')
                                <td>
                                        <div class="btn-group gap-1">
                                            <a href="{{ route('zones.edit', $zone) }}" class="btn btn-sm btn-outline-info" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('zones.destroy', $zone) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette zone ?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="@if(auth()->user()->role === 'gestionnaire') 6 @else 5 @endif" class="text-center text-muted">Aucune zone fiscale trouvée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($zones->hasPages())
                <div class="mt-4">
                    {{ $zones->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.btn-group {
    gap: 0.5rem;
}
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
.villages-list {
    line-height: 1.6;
}
.villages-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}
.table td {
    vertical-align: top;
    padding-top: 1rem;
    padding-bottom: 1rem;
}
</style>
@endsection 