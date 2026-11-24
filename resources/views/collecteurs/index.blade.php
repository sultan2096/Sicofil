@extends('layouts.app')

@section('title', 'Gestion des Collecteurs - SICOSA')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-users me-2"></i>
                Gestion des Collecteurs
            </h1>
            <div class="text-muted small ms-1">Liste des collecteurs enregistrés dans le système</div>
        </div>
        <a href="{{ route('collecteurs.create') }}" class="btn btn-lg btn-primary shadow-sm">
            <i class="fas fa-plus me-2"></i>
            Nouveau Collecteur
        </a>
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
                    <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un collecteur..." value="{{ request('search') }}">
                    <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Identifiant</th>
                            <th>Zone</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $user->id }}</span></td>
                                <td class="fw-semibold"><i class="fas fa-user-circle me-1 text-primary"></i> {{ $user->name }}</td>
                                <td><span class="badge bg-info bg-opacity-25 text-info">{{ $user->identifiant }}</span></td>
                                <td>
                                    @if($user->zone)
                                        <span class="badge bg-success bg-opacity-25 text-success"><i class="fas fa-map-marker-alt me-1"></i> {{ $user->zone->nom }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Non assigné</span>
                                    @endif
                                </td>
                                <td><span class="text-muted">{{ $user->created_at->format('d/m/Y H:i') }}</span></td>
                                <td>
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('collecteurs.edit', $user) }}" class="btn btn-sm btn-outline-info" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('collecteurs.destroy', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce collecteur ?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucun collecteur trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
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
</style>
@endsection 