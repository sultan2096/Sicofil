@extends('layouts.app')

@section('title', 'Créer un type de commerce')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Créer un nouveau type de commerce
                    </h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('types-commerce.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">
                                        Nom du type de commerce <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('nom') is-invalid @enderror" 
                                           id="nom" 
                                           name="nom" 
                                           value="{{ old('nom') }}" 
                                           required 
                                           maxlength="100"
                                           placeholder="Ex: Alimentation générale">
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nature" class="form-label">Nature <span class="text-danger">*</span></label>
                                    <select class="form-select @error('nature') is-invalid @enderror" id="nature" name="nature" required>
                                        <option value="">Sélectionner la nature</option>
                                        <option value="Boutique ou Magasin">Boutique ou Magasin</option>
                                        <option value="Atelier">Atelier</option>
                                        <option value="Agriculture">Agriculture</option>
                                        <option value="Transport">Transport</option>
                                        <option value="Étal">Étal</option>
                                        <option value="Table ou Tente commerciale">Table ou Tente commerciale</option>
                                        <option value="Cantine ou Kiosque">Cantine ou Kiosque</option>
                                        <option value="Entreprise">Entreprise</option>
                                        <option value="Foncier">Foncier</option>
                                        <option value="Publicité">Publicité</option>
                                        <option value="Spectacles">Spectacles</option>
                                        <option value="Papiers administratifs">Papiers administratifs</option>
                                    </select>
                                    @error('nature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ordre" class="form-label">
                                        Ordre d'affichage
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('ordre') is-invalid @enderror" 
                                           id="ordre" 
                                           name="ordre" 
                                           value="{{ old('ordre', 0) }}" 
                                           min="0"
                                           placeholder="0">
                                    <div class="form-text">
                                        Plus le nombre est petit, plus le type apparaîtra en premier
                                    </div>
                                    @error('ordre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      maxlength="255"
                                      placeholder="Description optionnelle du type de commerce">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_transport" 
                                               name="is_transport" 
                                               value="1"
                                               {{ old('is_transport') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_transport">
                                            <i class="fas fa-truck me-1"></i>
                                            Type de transport
                                        </label>
                                        <div class="form-text">
                                            Cochez si ce type concerne le transport (taxi, moto, etc.)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="actif" 
                                               name="actif" 
                                               value="1"
                                               {{ old('actif', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="actif">
                                            <i class="fas fa-toggle-on me-1"></i>
                                            Type actif
                                        </label>
                                        <div class="form-text">
                                            Les types inactifs n'apparaissent pas dans les formulaires
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('types-commerce.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Créer le type de commerce
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Aperçu en temps réel
    document.getElementById('is_transport').addEventListener('change', function() {
        const label = document.querySelector('label[for="is_transport"] i');
        if (this.checked) {
            label.className = 'fas fa-truck me-1 text-warning';
        } else {
            label.className = 'fas fa-store me-1';
        }
    });

    document.getElementById('actif').addEventListener('change', function() {
        const label = document.querySelector('label[for="actif"] i');
        if (this.checked) {
            label.className = 'fas fa-toggle-on me-1 text-success';
        } else {
            label.className = 'fas fa-toggle-off me-1 text-danger';
        }
    });
</script>
@endpush 