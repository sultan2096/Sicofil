@extends('layouts.app')

@section('title', 'Modifier Abonnement - Admin SICOFIL')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-edit"></i> Modifier l'abonnement</h3>
        <a href="{{ route('admin.sicofil.abonnements.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <strong>{{ $abonnement->commune_nom }}</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.sicofil.abonnements.update', $abonnement->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Formule *</label>
                        <select name="formule" class="form-select @error('formule') is-invalid @enderror" required>
                            <option value="basique"   {{ $abonnement->formule === 'basique'   ? 'selected' : '' }}>Basique</option>
                            <option value="standard"  {{ $abonnement->formule === 'standard'  ? 'selected' : '' }}>Standard</option>
                            <option value="premium"   {{ $abonnement->formule === 'premium'   ? 'selected' : '' }}>Premium</option>
                        </select>
                        @error('formule')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut *</label>
                        <select name="statut" class="form-select @error('statut') is-invalid @enderror" required>
                            <option value="actif"     {{ $abonnement->statut === 'actif'     ? 'selected' : '' }}>Actif</option>
                            <option value="suspendu"  {{ $abonnement->statut === 'suspendu'  ? 'selected' : '' }}>Suspendu</option>
                            <option value="expire"    {{ $abonnement->statut === 'expire'    ? 'selected' : '' }}>Expiré</option>
                        </select>
                        @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de début *</label>
                        <input type="date" name="date_debut" class="form-control @error('date_debut') is-invalid @enderror"
                               value="{{ old('date_debut', \Carbon\Carbon::parse($abonnement->date_debut)->format('Y-m-d')) }}" required>
                        @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date d'expiration *</label>
                        <input type="date" name="date_expiration" class="form-control @error('date_expiration') is-invalid @enderror"
                               value="{{ old('date_expiration', \Carbon\Carbon::parse($abonnement->date_expiration)->format('Y-m-d')) }}" required>
                        @error('date_expiration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">% recettes</label>
                        <input type="number" name="pourcentage_recettes" class="form-control"
                               value="{{ old('pourcentage_recettes', $abonnement->pourcentage_recettes) }}" min="0" max="100" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fréquence de paiement *</label>
                        <select name="frequence_paiement" class="form-select" required>
                            <option value="mensuel" {{ old('frequence_paiement', $abonnement->frequence_paiement ?? 'annuel') === 'mensuel' ? 'selected' : '' }}>Mensuel</option>
                            <option value="trimestriel" {{ old('frequence_paiement', $abonnement->frequence_paiement ?? 'annuel') === 'trimestriel' ? 'selected' : '' }}>Trimestriel</option>
                            <option value="semestriel" {{ old('frequence_paiement', $abonnement->frequence_paiement ?? 'annuel') === 'semestriel' ? 'selected' : '' }}>Semestriel</option>
                            <option value="annuel" {{ old('frequence_paiement', $abonnement->frequence_paiement ?? 'annuel') === 'annuel' ? 'selected' : '' }}>Annuel</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Achat licence (FCFA)</label>
                        <input type="number" name="montant_achat_licence" class="form-control"
                               value="{{ old('montant_achat_licence', $abonnement->montant_achat_licence ?? 0) }}" min="0" step="1000">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Frais maintenance (FCFA)</label>
                        <input type="number" name="frais_maintenance" class="form-control"
                               value="{{ old('frais_maintenance', $abonnement->frais_maintenance ?? 0) }}" min="0" step="1000">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                        <a href="{{ route('admin.sicofil.abonnements.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
