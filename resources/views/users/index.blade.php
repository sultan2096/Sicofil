@extends('layouts.app')

@section('title', auth()->user()->role === 'maire' ? 'Gestion des Utilisateurs - SICOSA' : 'Gestion des Collecteurs - SICOSA')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-users me-2"></i>
                        @if(auth()->user()->role === 'maire')
                            Gestion des Utilisateurs
                        @else
                            Gestion des Collecteurs
                        @endif
                    </h3>
                    @if(auth()->user()->role === 'gestionnaire')
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            <span class="d-none d-sm-inline">Ajouter un collecteur</span>
                            <span class="d-sm-none">Ajouter</span>
                        </a>
                    @endif
                </div>
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

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Identifiant</th>
                                    @if(auth()->user()->role === 'maire')
                                    <th>Rôle</th>
                                    @endif
                                    <th>Zone assignée</th>
                                    <th>Date de création</th>
                                    @if(auth()->user()->role === 'gestionnaire')
                                    <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->identifiant }}</td>
                                        @if(auth()->user()->role === 'maire')
                                        <td>
                                            @if($user->role === 'gestionnaire')
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-user-cog me-1"></i>Gestionnaire
                                                </span>
                                            @elseif($user->role === 'collecteur')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-mobile-alt me-1"></i>Collecteur
                                                </span>
                                            @endif
                                        </td>
                                        @endif
                                        <td>{{ $user->zone ? $user->zone->nom : ($user->role === 'gestionnaire' ? 'Toutes les zones' : 'Non assignée') }}</td>
                                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                        @if(auth()->user()->role === 'gestionnaire')
                                        <td>
                                            @if($user->role === 'collecteur')
                                            <div class="btn-group">
                                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('users.delete', $user->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce collecteur ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                            @else
                                                <span class="text-muted small">Actions non disponibles</span>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="@if(auth()->user()->role === 'maire') 5 @elseif(auth()->user()->role === 'gestionnaire') 5 @else 4 @endif" class="text-center py-4">
                                            <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                            <p class="text-muted mb-0">
                                                @if(auth()->user()->role === 'maire')
                                                    Aucun utilisateur trouvé
                                                @else
                                                    Aucun collecteur trouvé
                                                @endif
                                            </p>
                                        </td>
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
    </div>
</div>
@endsection 