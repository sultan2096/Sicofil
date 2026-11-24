@extends('layouts.app')

@section('title', 'Modifier Zone Fiscale - SICOSA')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marked-alt me-2"></i>
                        Modifier Zone Fiscale
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

                    <form action="{{ route('zones.update', $zone) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom de la zone</label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                   id="nom" name="nom" value="{{ old('nom', $zone->nom) }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $zone->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="geometrie" class="form-label">
                                <i class="fas fa-map"></i> Coordonnées géographiques (Lecture seule - Synchronisées depuis PostGIS)
                            </label>
                            @if($zone->geometrie)
                                <div class="mb-2">
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Géométrie disponible
                                    </span>
                                    <span class="badge bg-info ms-2">
                                        Type: {{ is_array($zone->geometrie) && isset($zone->geometrie['type']) ? $zone->geometrie['type'] : 'N/A' }}
                                    </span>
                                </div>
                                <textarea class="form-control @error('geometrie') is-invalid @enderror" 
                                          id="geometrie" name="geometrie" rows="12" 
                                          placeholder="Format GeoJSON" 
                                          readonly 
                                          style="background-color: #f8f9fa; cursor: not-allowed; font-family: 'Courier New', monospace; font-size: 0.85rem;">{{ old('geometrie', is_array($zone->geometrie) ? json_encode($zone->geometrie, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : ($zone->geometrie ?? 'Aucune géométrie définie')) }}</textarea>
                            @else
                                <div class="alert alert-warning mb-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Aucune géométrie définie.</strong> Synchronisez depuis PostGIS avec la commande : 
                                    <code>php artisan zones:sync-geometries</code>
                                </div>
                                <textarea class="form-control" 
                                          rows="4" 
                                          readonly 
                                          style="background-color: #f8f9fa; cursor: not-allowed;">Aucune géométrie définie. Exécutez la commande de synchronisation pour charger les géométries depuis PostGIS.</textarea>
                            @endif
                            <div class="alert alert-warning mt-3 mb-2" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                    <div class="flex-grow-1">
                                        <strong>⚠️ Important :</strong> Ce champ est en lecture seule pour protéger l'intégrité des données.
                                        <ul class="mb-0 mt-2" style="font-size: 0.9rem;">
                                            <li>Les <strong>couches shapefiles affichées sur la carte</strong> 
                                                (<a href="{{ route('map.index') }}" target="_blank" class="alert-link">
                                                    <i class="fas fa-external-link-alt"></i> /map
                                                </a>) 
                                                proviennent directement des tables <strong>PostGIS</strong> et sont affichées en temps réel
                                            </li>
                                            <li><strong>Modifier ce champ ne changera PAS</strong> les formes affichées sur la carte</li>
                                            <li>Pour synchroniser les géométries depuis PostGIS, exécutez : 
                                                <code class="bg-light px-2 py-1 rounded">php artisan zones:sync-geometries</code>
                                            </li>
                                            <li>Pour modifier les formes des zones sur la carte, modifiez directement les tables PostGIS :
                                                <ul class="mt-1">
                                                    <li><code>public.polygone_loulibentegne</code></li>
                                                    <li><code>public.polygone_sandiaracentre</code></li>
                                                    <li><code>public.polygone_ndioukhthiorokh</code></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mb-0" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>
                                    Cette géométrie est une <strong>copie de référence</strong> stockée dans la base de données Laravel. 
                                    Elle n'affecte pas l'affichage des couches sur la carte qui proviennent directement de PostGIS.
                                </small>
                            </div>
                            @error('geometrie')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('zones.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour
                            </a>
                            <button type="submit" class="btn btn-info text-white">
                                <i class="fas fa-save me-2"></i>
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 