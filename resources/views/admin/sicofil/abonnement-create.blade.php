@extends('layouts.app')

@section('title', 'Créer un abonnement - Admin SICOFIL')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-plus-circle"></i> Créer un abonnement</h3>
        <a href="{{ route('admin.sicofil.abonnements.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    @if($communesSansAbonnement->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Toutes les communes actives ont déjà un abonnement.
            <a href="{{ route('admin.sicofil.abonnements.index') }}">Voir la liste des abonnements</a>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.sicofil.abonnements.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="commune_id" class="form-label">Commune *</label>
                            <select name="commune_id" id="commune_id" class="form-select @error('commune_id') is-invalid @enderror" required>
                                <option value="">Choisir une commune</option>
                                @foreach($communesSansAbonnement as $c)
                                    <option value="{{ $c->id }}" {{ old('commune_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->nom }} ({{ $c->code ?? $c->region }})
                                    </option>
                                @endforeach
                            </select>
                            @error('commune_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="formule" class="form-label">Formule *</label>
                            <select name="formule" id="formule" class="form-select" required>
                                <option value="basique" {{ old('formule') === 'basique' ? 'selected' : '' }}>Basique</option>
                                <option value="standard" {{ old('formule', 'standard') === 'standard' ? 'selected' : '' }}>Standard</option>
                                <option value="premium" {{ old('formule') === 'premium' ? 'selected' : '' }}>Premium</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date_debut" class="form-label">Date de début *</label>
                            <input type="date" name="date_debut" id="date_debut" class="form-control" value="{{ old('date_debut', now()->format('Y-m-d')) }}" required>
                            @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date_expiration" class="form-label">Date d'expiration *</label>
                            <input type="date" name="date_expiration" id="date_expiration" class="form-control" value="{{ old('date_expiration', now()->addYear()->format('Y-m-d')) }}" required>
                            @error('date_expiration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label for="montant_fixe_annuel" class="form-label">Montant fixe annuel (FCFA)</label>
                            <input type="number" name="montant_fixe_annuel" id="montant_fixe_annuel" class="form-control" value="{{ old('montant_fixe_annuel', 0) }}" min="0" step="1000">
                        </div>
                        <div class="col-md-3">
                            <label for="pourcentage_recettes" class="form-label">% recettes</label>
                            <input type="number" name="pourcentage_recettes" id="pourcentage_recettes" class="form-control" value="{{ old('pourcentage_recettes', 0) }}" min="0" max="100" step="0.01">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer l'abonnement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
