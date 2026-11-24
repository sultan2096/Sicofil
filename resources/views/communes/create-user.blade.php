@extends('layouts.app')

@section('title', 'Créer un utilisateur — ' . $commune->nom . ' - SICOFIL')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('communes.show', $commune) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        <h1 class="h4 mb-0"><i class="fas fa-user-plus me-2"></i>Créer un utilisateur pour {{ $commune->nom }} ({{ $commune->acronyme ?? 'commune' }})</h1>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="text-muted mb-4">Cet utilisateur pourra se connecter via <strong>Accéder par commune → {{ $commune->nom }}</strong> et verra uniquement l’interface de sa commune ({{ $commune->acronyme ?? 'SICOSA' }}).</p>

            <form action="{{ route('communes.users.store', $commune) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Identifiant de connexion <span class="text-danger">*</span></label>
                        <input type="text" name="identifiant" class="form-control @error('identifiant') is-invalid @enderror" value="{{ old('identifiant') }}" required>
                        @error('identifiant')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email (optionnel)</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rôle <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="maire" {{ old('role') === 'maire' ? 'selected' : '' }}>Maire</option>
                            <option value="gestionnaire" {{ old('role') === 'gestionnaire' ? 'selected' : '' }}>Gestionnaire</option>
                            <option value="collecteur" {{ old('role') === 'collecteur' ? 'selected' : '' }}>Collecteur</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6" id="zone-field" style="{{ old('role') === 'collecteur' ? '' : 'display:none;' }}">
                        <label class="form-label">Zone (pour collecteur)</label>
                        <select name="zone_id" class="form-select @error('zone_id') is-invalid @enderror">
                            <option value="">— Choisir une zone —</option>
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}" {{ old('zone_id') == $z->id ? 'selected' : '' }}>{{ $z->nom }}</option>
                            @endforeach
                        </select>
                        @error('zone_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($zones->isEmpty())
                            <small class="text-warning">Aucune zone pour cette commune. Créez des zones ou importez un GeoJSON (Carte → Importer des zones) pour cette commune.</small>
                        @endif
                    </div>
                    <div class="col-md-6"></div>
                    <div class="col-md-6">
                        <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success"><i class="fas fa-user-plus me-1"></i>Créer l’utilisateur</button>
                        <a href="{{ route('communes.show', $commune) }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('role').addEventListener('change', function() {
    var zoneField = document.getElementById('zone-field');
    zoneField.style.display = this.value === 'collecteur' ? 'block' : 'none';
});
</script>
@endsection
