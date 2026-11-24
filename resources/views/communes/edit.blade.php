@extends('layouts.app')

@section('title', 'Modifier ' . $commune->nom . ' - SICOFIL')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('communes.show', $commune) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h1 class="h4 mb-0"><i class="fas fa-edit me-2"></i>Modifier {{ $commune->nom }}</h1>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('communes.update', $commune) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $commune->nom) }}" required>
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $commune->code) }}">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Acronyme du système</label>
                        <input type="text" name="acronyme" class="form-control @error('acronyme') is-invalid @enderror" value="{{ old('acronyme', $commune->acronyme) }}" placeholder="Ex: SICOSA, SICOTH">
                        <small class="text-muted">Nom affiché dans l’interface (Maire/Gestionnaire/Collecteur) pour cette commune.</small>
                        @error('acronyme')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Région</label>
                        <input type="text" name="region" class="form-control" value="{{ old('region', $commune->region) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Département</label>
                        <input type="text" name="departement" class="form-control" value="{{ old('departement', $commune->departement) }}">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="actif" value="0">
                            <input type="checkbox" name="actif" value="1" class="form-check-input" id="actif" {{ old('actif', $commune->actif) ? 'checked' : '' }}>
                            <label class="form-check-label" for="actif">Commune active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $commune->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Logo de la commune</label>
                        @if($commune->logo_url)
                            <div class="mb-2">
                                <img src="{{ $commune->logo_url }}" alt="Logo {{ $commune->nom }}" class="rounded border" style="max-height: 80px;" onerror="this.style.display='none';">
                                <span class="text-muted small ms-2">Logo actuel</span>
                            </div>
                        @endif
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="text-muted">JPG, PNG, GIF ou WebP — max 2 Mo. Laisser vide pour conserver le logo actuel. Si le logo ne s'affiche pas après enregistrement, exécutez <code>php artisan storage:link</code> pour créer le lien vers le stockage.</small>
                        @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
                        <a href="{{ route('communes.show', $commune) }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
