@extends('layouts.app')

@section('title', 'Nouvelle Taxe - SICOSA')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Nouvelle Taxe
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('taxes.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom de la taxe</label>
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
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="is_fonciere" name="is_fonciere" value="1" {{ old('is_fonciere') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_fonciere">
                                    <strong>Taxe foncière</strong> (montant par m²)
                                </label>
                                <div class="form-text">Cochez cette case si la taxe est calculée en fonction de la superficie (m²)</div>
                            </div>
                        </div>

                        <div class="mb-3" id="montant-normal-group">
                            <label for="montant" class="form-label">Montant (FCFA)</label>
                            <input type="number" class="form-control @error('montant') is-invalid @enderror" 
                                   id="montant" name="montant" value="{{ old('montant') }}" min="0" step="0.01">
                            @error('montant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Montant fixe de la taxe</div>
                        </div>

                        <div class="mb-3" id="montant-fonciere-group" style="display: none;">
                            <label for="montant_par_m2" class="form-label">Montant par m² (FCFA/m²)</label>
                            <input type="number" class="form-control @error('montant_par_m2') is-invalid @enderror" 
                                   id="montant_par_m2" name="montant_par_m2" value="{{ old('montant_par_m2') }}" min="0" step="0.01">
                            @error('montant_par_m2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Montant de la taxe par m² de superficie</div>
                        </div>

                        <div class="mb-3">
                            <label for="periodicite" class="form-label">Périodicité</label>
                            <select class="form-select @error('periodicite') is-invalid @enderror" 
                                    id="periodicite" name="periodicite" required>
                                <option value="">Sélectionner une périodicité</option>
                                <option value="jour" {{ old('periodicite') == 'jour' ? 'selected' : '' }}>Journalier</option>
                                <option value="mois" {{ old('periodicite') == 'mois' ? 'selected' : '' }}>Mensuel</option>
                                <option value="an" {{ old('periodicite') == 'an' ? 'selected' : '' }}>Annuel</option>
                                <option value="non_periodique" {{ old('periodicite') == 'non_periodique' ? 'selected' : '' }}>Non périodique</option>
                            </select>
                            @error('periodicite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Filtres d'applicabilité -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-filter me-2"></i>
                                    Filtres d'applicabilité
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Sélectionnez les types de commerce auxquels cette taxe s'applique. Laissez vide pour appliquer à tous les types.
                                </div>

                                <!-- Natures et Types de commerce -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Types de commerce par nature (sélection multiple)</label>
                                    
                                    @php
                                        $natureTypes = [
                                            "Boutique ou Magasin" => ["Alimentation générale", "Grossiste", "Mercerie", "Quincaillerie", "Prêt-à-porter", "Cosmétique"],
                                            "Atelier" => ["Mécanique", "Couture", "Forge", "Cordonnerie", "Menuiserie", "Boulangerie"],
                                            "Forail" => ["Chevaux", "Ânes", "Vaches", "Moutons", "Chèvres"],
                                            "Parcelle agricole" => ["Petites exploitations agricoles (celui des particuliers ou l'agriculture familiale)"],
                                            "Stationnement" => ["Bus", "Camion-citerne de carburant", "Camionnette", "Calèche", "Camion gros porteur", "Taxi clando", "Moto Jakarta", "Car"],
                                            "Étal" => ["Vente sur étal (sans distinction spécifique)"],
                                            "Table ou Tente commerciale" => ["Vente sur table ou sous tente (sans distinction spécifique)"],
                                            "Cantine ou Kiosque" => ["Restaurant", "Gargote", "Dibiterie", "Fast-Food"],
                                            "Entreprise ou Service" => ["Entrepôt", "Grande exploitation agricole (celui des entreprises)", "Assurance", "Banque", "Multiservice", "Supermarché", "Station-service"],
                                            "Foncier" => ["Location de terrain", "Location de bâtiments"],
                                            "Publicité" => ["Véhicules publicitaires", "Panneaux publicitaires"],
                                            "Spectacles" => ["Lieux dédiés aux événements et spectacles"]
                                        ];
                                    @endphp

                                    @foreach($natureTypes as $nature => $types)
                                        <div class="mb-3">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input nature-checkbox" type="checkbox" 
                                                       id="nature_{{ str_replace([' ', '(', ')', "'", '/'], '_', $nature) }}" 
                                                       data-nature="{{ $nature }}"
                                                       onchange="toggleNatureTypes('{{ $nature }}', this.checked)">
                                                <label class="form-check-label text-primary fw-bold" for="nature_{{ str_replace([' ', '(', ')', "'", '/'], '_', $nature) }}">
                                                    {{ $nature }}
                                                </label>
                                            </div>
                                            <div class="row ms-3" id="types_{{ str_replace([' ', '(', ')', "'", '/'], '_', $nature) }}">
                                                @foreach($types as $type)
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input type-checkbox" type="checkbox" 
                                                                   id="type_{{ str_replace([' ', '(', ')', "'", '/'], '_', $type) }}" 
                                                                   name="types_commerce_applicables[]" 
                                                                   value="{{ $type }}"
                                                                   data-nature="{{ $nature }}"
                                                                   {{ in_array($type, old('types_commerce_applicables', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="type_{{ str_replace([' ', '(', ')', "'", '/'], '_', $type) }}">
                                                                {{ $type }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <script>
                        function toggleNatureTypes(nature, checked) {
                            const natureId = nature.replace(/[\s\(\)'\/]/g, '_');
                            const typeCheckboxes = document.querySelectorAll(`.type-checkbox[data-nature="${nature}"]`);
                            typeCheckboxes.forEach(checkbox => {
                                checkbox.checked = checked;
                            });
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            const isFonciereCheckbox = document.getElementById('is_fonciere');
                            const montantNormalGroup = document.getElementById('montant-normal-group');
                            const montantFonciereGroup = document.getElementById('montant-fonciere-group');
                            const montantInput = document.getElementById('montant');
                            const montantParM2Input = document.getElementById('montant_par_m2');

                            function toggleMontantFields() {
                                if (isFonciereCheckbox.checked) {
                                    montantNormalGroup.style.display = 'none';
                                    montantFonciereGroup.style.display = 'block';
                                    montantInput.removeAttribute('required');
                                    montantParM2Input.setAttribute('required', 'required');
                                } else {
                                    montantNormalGroup.style.display = 'block';
                                    montantFonciereGroup.style.display = 'none';
                                    montantInput.setAttribute('required', 'required');
                                    montantParM2Input.removeAttribute('required');
                                }
                            }

                            isFonciereCheckbox.addEventListener('change', toggleMontantFields);
                            toggleMontantFields(); // Initialiser l'état
                        });
                        </script>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('taxes.index') }}" class="btn btn-secondary">
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