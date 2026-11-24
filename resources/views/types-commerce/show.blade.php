@extends('layouts.app')

@section('title', 'Détails du type de commerce')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-eye me-2"></i>
                        Détails du type de commerce
                        @if(auth()->user()->role === 'maire')
                            <small class="text-muted">(Consultation seule)</small>
                        @endif
                    </h4>
                    @if(auth()->user()->role === 'gestionnaire')
                        <div class="btn-group">
                            <a href="{{ route('types-commerce.edit', $typeCommerce) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                Modifier
                            </a>
                            @if($unitesCount == 0)
                                <a href="{{ route('types-commerce.delete', $typeCommerce->id) }}" 
                                   class="btn btn-outline-danger"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce type de commerce ?')">
                                    <i class="fas fa-trash me-1"></i>
                                    Supprimer
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="card-body">
                    @if(auth()->user()->role === 'maire')
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>
                                Information pour le Maire
                            </h6>
                            <p class="mb-0">
                                En tant que maire, vous pouvez consulter les informations des types de commerce, 
                                mais seuls les gestionnaires peuvent les créer, modifier ou supprimer.
                            </p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nom du type</label>
                                <p class="h5">
                                    {{ $typeCommerce->nom }}
                                    @if($typeCommerce->is_transport)
                                        <i class="fas fa-truck text-warning ms-2" title="Type transport"></i>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Statut</label>
                                <p>
                                    @if($typeCommerce->actif)
                                        <span class="badge bg-success fs-6">
                                            <i class="fas fa-check me-1"></i>Actif
                                        </span>
                                    @else
                                        <span class="badge bg-danger fs-6">
                                            <i class="fas fa-times me-1"></i>Inactif
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Catégorie</label>
                                <p>
                                    @if($typeCommerce->is_transport)
                                        <span class="badge bg-warning fs-6">
                                            <i class="fas fa-truck me-1"></i>Transport
                                        </span>
                                    @else
                                        <span class="badge bg-secondary fs-6">
                                            <i class="fas fa-store me-1"></i>Commerce
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Ordre d'affichage</label>
                                <p>
                                    <span class="badge bg-info fs-6">{{ $typeCommerce->ordre }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Description</label>
                        <p class="text-muted">
                            {{ $typeCommerce->description ?: 'Aucune description fournie' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Utilisation</label>
                        <p>
                            <span class="badge {{ $unitesCount > 0 ? 'bg-success' : 'bg-secondary' }} fs-6">
                                <i class="fas fa-store me-1"></i>
                                {{ $unitesCount }} unité(s) commerciale(s)
                            </span>
                        </p>
                        @if($unitesCount > 0)
                            <div class="form-text">
                                Ce type de commerce est actuellement utilisé et ne peut pas être supprimé.
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Date de création</label>
                                <p class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $typeCommerce->created_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Dernière modification</label>
                                <p class="text-muted">
                                    <i class="fas fa-edit me-1"></i>
                                    {{ $typeCommerce->updated_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($unitesCount > 0)
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>
                                Unités commerciales utilisant ce type
                            </h6>
                            <p class="mb-0">
                                Ce type de commerce est utilisé par <strong>{{ $unitesCount }} unité(s)</strong>. 
                                <a href="{{ route('unites.index', ['type_commerce' => $typeCommerce->nom]) }}" 
                                   class="alert-link">
                                    Voir la liste des unités
                                </a>
                            </p>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('types-commerce.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                        @if(auth()->user()->role === 'gestionnaire')
                            <a href="{{ route('types-commerce.edit', $typeCommerce) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                Modifier ce type
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                        @if(auth()->user()->role === 'gestionnaire')
                            <a href="{{ route('types-commerce.edit', $typeCommerce) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                Modifier ce type
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour à la liste
                        </a>
                        @if(auth()->user()->role === 'gestionnaire')
                            <a href="{{ route('types-commerce.edit', $typeCommerce) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                Modifier ce type
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 