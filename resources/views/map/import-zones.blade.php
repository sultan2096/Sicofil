@extends('layouts.app')

@section('title', 'Importer des zones (GeoJSON) - Carte')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-sicosa-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Importer des zones sur la carte</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        Importez des zones pour la commune choisie au format <strong>GeoJSON</strong> ou <strong>Shapefile</strong>. GeoJSON : un ou plusieurs fichiers .json / .geojson. Shapefile : sélectionnez ensemble le .shp et les fichiers associés (.shx, .dbf, .prj) d’un même polygone, ou un fichier .zip contenant un jeu complet. Les zones s’afficheront sur la carte.
                    </p>

                    <form action="{{ route('map.import-zones.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="commune_id" class="form-label">Commune <span class="text-danger">*</span></label>
                            @if(isset($isSicofil) && $isSicofil && $communes->count() > 1)
                            <select name="commune_id" id="commune_id" class="form-select" required>
                                <option value="">— Choisir une commune —</option>
                                @foreach($communes as $c)
                                    <option value="{{ $c->id }}" {{ ($preselectCommuneId ?? old('commune_id')) == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                                @endforeach
                            </select>
                            @else
                            <input type="hidden" name="commune_id" value="{{ $preselectCommuneId ?? $communes->first()?->id }}">
                            <p class="form-control-plaintext mb-0"><strong>{{ $communes->first()?->nom ?? '—' }}</strong> (votre commune)</p>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="fichier" class="form-label">Fichier(s) GeoJSON ou Shapefile <span class="text-danger">*</span></label>
                            <input type="file" name="fichier[]" id="fichier" class="form-control" accept=".json,.geojson,.shp,.shx,.dbf,.prj,.zip" multiple required>
                            <small class="form-text text-muted">GeoJSON : .json, .geojson. Shapefile : sélectionnez en une fois le .shp + .shx + .dbf (et .prj si besoin) du même nom, ou un .zip contenant un jeu complet. Plusieurs jeux ou fichiers possibles. Max 50 Mo par fichier.</small>
                        </div>
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="remplacer" id="remplacer" value="1" class="form-check-input" {{ old('remplacer') ? 'checked' : '' }}>
                                <label for="remplacer" class="form-check-label">Remplacer les zones existantes de cette commune</label>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Importer</button>
                            @if(strtolower(auth()->user()->role ?? '') === 'admin_sicofil')
                            <a href="{{ route('communes.map') }}" class="btn btn-outline-secondary">Retour à la carte multi-communes</a>
                            @else
                            <a href="{{ route('map.index') }}" class="btn btn-outline-secondary">Voir la carte</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
