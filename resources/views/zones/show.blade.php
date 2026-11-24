@extends('layouts.app')

@section('title', 'Détails de la zone fiscale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $zone->nom }}
                    </h3>
                    <div>
                        <a href="{{ route('zones.edit', $zone) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                            Modifier
                        </a>
                        <a href="{{ route('zones.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nom de la zone</label>
                                        <p class="form-control-plaintext">{{ $zone->nom }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Description</label>
                                        <p class="form-control-plaintext">{{ $zone->description ?: 'Aucune description' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-map-marker-alt"></i> Villages et Amonts
                                        </label>
                                        @php
                                            $villages = $zone->villages;
                                        @endphp
                                        @if(count($villages) > 0)
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <p class="mb-2 text-muted">
                                                        <strong>{{ count($villages) }} village(s) et amont(s) dans cette zone :</strong>
                                                    </p>
                                                    <div class="villages-list">
                                                        @foreach($villages as $village)
                                                            <span class="badge bg-primary me-2 mb-2" style="font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                                                                <i class="fas fa-home"></i> {{ $village }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-muted">Aucun village défini pour cette zone</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Créée le</label>
                                        <p class="form-control-plaintext">{{ $zone->created_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Dernière modification</label>
                                        <p class="form-control-plaintext">{{ $zone->updated_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($zone->geometrie)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Géométrie</label>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <pre class="mb-0">{{ json_encode($zone->geometrie, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $zone->unitesCommerciales->count() }}</h4>
                                            <p class="mb-0">Unités commerciales</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $zone->collecteurs->count() }}</h4>
                                            <p class="mb-0">Collecteurs assignés</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Collecteurs assignés -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-users"></i> Collecteurs assignés</h5>
                            @if($zone->collecteurs->count() > 0)
                                <div class="list-group">
                                    @foreach($zone->collecteurs as $collecteur)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $collecteur->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $collecteur->identifiant }}</small>
                                            </div>
                                            <span class="badge bg-info rounded-pill">Collecteur</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Aucun collecteur assigné à cette zone
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h5><i class="fas fa-store"></i> Unités commerciales</h5>
                            @if($zone->unitesCommerciales->count() > 0)
                                <div class="list-group" style="max-height: 300px; overflow-y: auto;">
                                    @foreach($zone->unitesCommerciales as $unite)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $unite->nom_proprietaire }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $unite->nature }} - {{ $unite->type_commerce }}</small>
                                            </div>
                                            <span class="badge bg-{{ $unite->actif ? 'success' : 'secondary' }} rounded-pill">
                                                {{ $unite->actif ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Aucune unité commerciale dans cette zone
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($zone->unitesCommerciales->count() > 0)
                        <hr>
                        <h5><i class="fas fa-chart-bar"></i> Statistiques de la zone</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Unités actives</h6>
                                        <h4 class="text-success">{{ $zone->unitesCommerciales->where('actif', true)->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Activités commerciales</h6>
                                        <h4 class="text-primary">{{ $zone->unitesCommerciales->where('categorie', 'activite_commerciale')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Points d'intérêt</h6>
                                        <h4 class="text-info">{{ $zone->unitesCommerciales->where('categorie', 'point_interet')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Avec taxe assignée</h6>
                                        <h4 class="text-warning">{{ $zone->unitesCommerciales->whereNotNull('taxe_id')->count() }}</h4>
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