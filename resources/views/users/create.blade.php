@extends('layouts.app')

@section('title', 'Nouveau Collecteur - SICOSA')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Nouveau Collecteur
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom complet</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="identifiant" class="form-label">Identifiant</label>
                            <input type="text" class="form-control @error('identifiant') is-invalid @enderror" 
                                   id="identifiant" name="identifiant" value="{{ old('identifiant') }}" required>
                            @error('identifiant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="mb-3">
                            <label for="zone_id" class="form-label">Zone fiscale</label>
                            <select class="form-select @error('zone_id') is-invalid @enderror" 
                                    id="zone_id" name="zone_id" required onchange="toggleCustomZone()">
                                <option value="">Sélectionner une zone</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>
                                        {{ $zone->nom }}
                                    </option>
                                @endforeach
                                <option value="other" {{ old('zone_id') == 'other' ? 'selected' : '' }}>
                                    Autres (préciser)
                                </option>
                            </select>
                            @error('zone_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="custom-zone-div" style="display: none;">
                            <label for="custom_zone_name" class="form-label">Nom de la nouvelle zone fiscale</label>
                            <input type="text" class="form-control @error('custom_zone_name') is-invalid @enderror" 
                                   id="custom_zone_name" name="custom_zone_name" value="{{ old('custom_zone_name') }}" 
                                   placeholder="Saisir le nom de la nouvelle zone">
                            @error('custom_zone_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Cette zone sera créée automatiquement lors de l'enregistrement du collecteur.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
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

<script>
function toggleCustomZone() {
    const zoneSelect = document.getElementById('zone_id');
    const customZoneDiv = document.getElementById('custom-zone-div');
    const customZoneInput = document.getElementById('custom_zone_name');
    
    if (zoneSelect.value === 'other') {
        customZoneDiv.style.display = 'block';
        customZoneInput.required = true;
    } else {
        customZoneDiv.style.display = 'none';
        customZoneInput.required = false;
        customZoneInput.value = '';
    }
}

// Vérifier au chargement de la page si "Autres" est sélectionné
document.addEventListener('DOMContentLoaded', function() {
    toggleCustomZone();
});
</script>
@endsection 