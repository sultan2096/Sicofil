@extends('layouts.app')

@section('title', 'Détails de l\'utilisateur')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i>
                        Détails de l'utilisateur
                    </h3>
                    <div>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                            Modifier
                        </a>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID</label>
                                <p class="form-control-plaintext">{{ $user->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Identifiant</label>
                                <p class="form-control-plaintext">{{ $user->identifiant }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nom complet</label>
                                <p class="form-control-plaintext">{{ $user->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Rôle</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-{{ 
                                        $user->role === 'maire' ? 'primary' : 
                                        ($user->role === 'admin' ? 'success' : 'info') 
                                    }} fs-6">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Zone fiscale</label>
                                <p class="form-control-plaintext">
                                    @if($user->zone)
                                        <span class="badge bg-secondary fs-6">{{ $user->zone->nom }}</span>
                                    @else
                                        <span class="text-muted">Aucune zone assignée</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Créé le</label>
                                <p class="form-control-plaintext">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dernière modification</label>
                                <p class="form-control-plaintext">{{ $user->updated_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($user->role === 'collecteur' && $user->zone)
                        <hr>
                        <h5><i class="fas fa-chart-bar"></i> Statistiques du collecteur</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Unités dans la zone</h6>
                                        <h4 class="text-primary">{{ $user->zone->unitesCommerciales->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Paiements ce mois</h6>
                                        <h4 class="text-success">
                                            {{ $user->paiements()->whereMonth('date_paiement', now()->month)->whereYear('date_paiement', now()->year)->count() }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Montant collecté ce mois</h6>
                                        <h4 class="text-info">
                                            {{ number_format($user->paiements()->whereMonth('date_paiement', now()->month)->whereYear('date_paiement', now()->year)->sum('montant'), 0, ',', ' ') }} FCFA
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 