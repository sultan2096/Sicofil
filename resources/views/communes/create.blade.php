@extends('layouts.app')

@section('title', 'Ajouter une commune - SICOFIL')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('communes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="h4 mb-0"><i class="fas fa-plus-circle me-2"></i>Nouvelle commune</h1>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('communes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom de la commune <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Code (optionnel)</label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Ex: MBOUR, THIES">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Acronyme du système (nom affiché pour cette commune)</label>
                        <input type="text" name="acronyme" class="form-control @error('acronyme') is-invalid @enderror" value="{{ old('acronyme') }}" placeholder="Ex: SICOSA (Sandiara), SICOTH (Thiès), SICOMB (Mbour)">
                        <small class="text-muted">Ex. SICOSA pour Sandiara, SICOTH pour Thiès. Affiché dans l’interface Maire/Gestionnaire/Collecteur.</small>
                        @error('acronyme')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Région</label>
                        <input type="text" name="region" class="form-control" value="{{ old('region') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Département</label>
                        <input type="text" name="departement" class="form-control" value="{{ old('departement') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description (optionnel)</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Logo de la commune (optionnel)</label>
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="text-muted">JPG, PNG, GIF ou WebP — max 2 Mo. Chaque commune peut avoir son propre logo.</small>
                        @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Créer la commune</button>
                        <a href="{{ route('communes.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <p class="text-muted small mt-3">Après création, vous pourrez affecter à cette commune des zones fiscales, taxes, et utilisateurs (maire, gestionnaire, collecteurs) depuis l’interface de la commune.</p>
</div>
@endsection
