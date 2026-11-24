@extends('layouts.app')

@section('title', 'Nouvelle Zone Fiscale - SICOSA')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marked-alt me-2"></i>
                        Nouvelle Zone Fiscale
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('zones.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom de la zone</label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                   id="nom" name="nom" value="{{ old('nom') }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="geometrie" class="form-label">Coordonnées géographiques (optionnel)</label>
                            <textarea class="form-control @error('geometrie') is-invalid @enderror" 
                                      id="geometrie" name="geometrie" rows="3" 
                                      placeholder="Format GeoJSON">{{ old('geometrie') }}</textarea>
                            <small class="text-muted">Format GeoJSON pour définir les limites de la zone</small>
                            @error('geometrie')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    Informations
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-3">
                                            <i class="fas fa-users fa-2x text-info mb-2"></i>
                                            <h6>Collecteurs</h6>
                                            <p class="text-muted small">Vous pourrez assigner des collecteurs à cette zone après sa création</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3">
                                            <i class="fas fa-store fa-2x text-success mb-2"></i>
                                            <h6>Unités commerciales</h6>
                                            <p class="text-muted small">Les commerces seront assignés à cette zone lors de leur enregistrement</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3">
                                            <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                                            <h6>Suivi des recettes</h6>
                                            <p class="text-muted small">Les statistiques de collecte seront disponibles par zone</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('zones.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 