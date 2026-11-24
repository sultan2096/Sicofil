@extends('layouts.app')

@section('title', 'Types de commerce')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-tags me-2"></i>
                        Gestion des Types de Commerce
                        @if(auth()->user()->role === 'maire')
                            <small class="text-muted">(Consultation seule)</small>
                        @endif
                    </h4>
                    @if(auth()->user()->role === 'gestionnaire')
                        <a href="{{ route('types-commerce.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Nouveau Type
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($typesCommerce->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                        <th><i class="fas fa-tag me-1"></i>Nom</th>
                                        <th><i class="fas fa-info-circle me-1"></i>Description</th>
                                        <th><i class="fas fa-truck me-1"></i>Transport</th>
                                        <th><i class="fas fa-sort-numeric-up me-1"></i>Ordre</th>
                                        <th><i class="fas fa-toggle-on me-1"></i>Statut</th>
                                        <th><i class="fas fa-store me-1"></i>Utilisation</th>
                                        <th class="text-center"><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($typesCommerce as $type)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $type->id }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $type->nom }}</strong>
                                                @if($type->is_transport)
                                                    <i class="fas fa-truck text-warning ms-1" title="Transport"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $type->description ?: 'Aucune description' }}
                                                </small>
                                            </td>
                                            <td>
                                                @if($type->is_transport)
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-truck me-1"></i>Transport
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-store me-1"></i>Commerce
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $type->ordre }}</span>
                                            </td>
                                            <td>
                                                @if($type->actif)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Actif
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times me-1"></i>Inactif
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $unitesCount = \App\Models\UniteCommerciale::where('type_commerce', $type->nom)->count();
                                                @endphp
                                                <span class="badge {{ $unitesCount > 0 ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $unitesCount }} unité(s)
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('types-commerce.show', $type) }}" 
                                                       class="btn btn-sm btn-outline-info" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if(auth()->user()->role === 'gestionnaire')
                                                        <a href="{{ route('types-commerce.edit', $type) }}" 
                                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        @if($unitesCount == 0)
                                                            <a href="{{ route('types-commerce.delete', $type->id) }}" 
                                                               class="btn btn-sm btn-outline-danger" 
                                                               title="Supprimer"
                                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce type de commerce ?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        @else
                                                            <button class="btn btn-sm btn-outline-secondary" 
                                                                    disabled 
                                                                    title="Impossible de supprimer - {{ $unitesCount }} unité(s) utilisent ce type">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $typesCommerce->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-tags fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">Aucun type de commerce</h5>
                            <p class="text-muted">Aucun type de commerce n'a été créé pour le moment.</p>
                            @if(auth()->user()->role === 'gestionnaire')
                                <a href="{{ route('types-commerce.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    Créer le premier type
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection