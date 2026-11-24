@extends('layouts.app')

@section('title', 'Fonctionnalité désactivée')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Fonctionnalité désactivée
                    </h4>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-tags fa-4x text-muted"></i>
                    </div>
                    
                    <h3 class="text-warning mb-3">Types de commerce désactivés</h3>
                    
                    <p class="text-muted mb-4">
                        La gestion des types de commerce a été désactivée dans cette version de SICOSA.
                        Les unités commerciales sont maintenant gérées directement par leur nature d'activité.
                    </p>
                    
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Alternatives disponibles
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-store me-2 text-primary"></i>
                                <a href="{{ route('unites.index') }}" class="alert-link">
                                    Gérer les unités commerciales
                                </a>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-coins me-2 text-success"></i>
                                <a href="{{ route('taxes.index') }}" class="alert-link">
                                    Gérer les taxes
                                </a>
                            </li>
                            <li>
                                <i class="fas fa-map-marked-alt me-2 text-info"></i>
                                <a href="{{ route('zones.index') }}" class="alert-link">
                                    Gérer les zones fiscales
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="btn btn-primary me-2">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Retour au tableau de bord
                        </a>
                        <a href="{{ route('unites.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-store me-1"></i>
                            Voir les unités commerciales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 