@extends('layouts.app')

@section('title', 'Modifier l\'unité commerciale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-store"></i>
                        Modifier l'unité commerciale
                    </h3>
                    <a href="{{ route('unites.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Retour
                    </a>
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

                    <form method="POST" action="{{ route('unites.update', $unite) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Informations du propriétaire -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations du propriétaire</h5>
                            </div>
                            <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                            <label for="nom_proprietaire" class="form-label">Nom du propriétaire <span class="text-danger">*</span></label>
                                    <input type="text" 
                                                   class="form-control @error('nom_proprietaire') is-invalid @enderror" 
                                                   id="nom_proprietaire" 
                                                   name="nom_proprietaire" 
                                                   value="{{ old('nom_proprietaire', $unite->nom_proprietaire) }}" 
                                           required>
                                            @error('nom_proprietaire')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                            <label for="prenom_proprietaire" class="form-label">Prénom du propriétaire <span class="text-danger">*</span></label>
                                    <input type="text" 
                                                   class="form-control @error('prenom_proprietaire') is-invalid @enderror" 
                                                   id="prenom_proprietaire" 
                                                   name="prenom_proprietaire" 
                                                   value="{{ old('prenom_proprietaire', $unite->prenom_proprietaire) }}" 
                                           required>
                                            @error('prenom_proprietaire')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="telephone" class="form-label">Téléphone</label>
                                            <input type="tel" 
                                                   class="form-control @error('telephone') is-invalid @enderror" 
                                                   id="telephone" 
                                                   name="telephone" 
                                                   value="{{ old('telephone', $unite->telephone) }}"
                                                   maxlength="9"
                                                   pattern="[0-9]{9}"
                                                   title="Numéro de téléphone (9 chiffres exactement)"
                                                   placeholder="77 123 45 67">
                                            @error('telephone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" 
                                                   class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" 
                                                   name="email" 
                                                   value="{{ old('email', $unite->email) }}">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="sexe_proprietaire" class="form-label">Sexe</label>
                                            <select class="form-select @error('sexe_proprietaire') is-invalid @enderror" 
                                                    id="sexe_proprietaire" 
                                                    name="sexe_proprietaire">
                                                <option value="">Sélectionner</option>
                                                <option value="masculin" {{ old('sexe_proprietaire', $unite->sexe_proprietaire) === 'masculin' ? 'selected' : '' }}>Masculin</option>
                                                <option value="feminin" {{ old('sexe_proprietaire', $unite->sexe_proprietaire) === 'feminin' ? 'selected' : '' }}>Féminin</option>
                                            </select>
                                            @error('sexe_proprietaire')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ninea" class="form-label" id="ninea-label">NINEA</label>
                                            <input type="text" 
                                                   class="form-control @error('ninea') is-invalid @enderror" 
                                                   id="ninea" 
                                                   name="ninea" 
                                                   value="{{ old('ninea', $unite->ninea) }}" 
                                                   placeholder="Numéro d'identification des entreprises">
                                            <div class="form-text" id="ninea-help">
                                                Numéro d'identification des entreprises et associations
                                            </div>
                                            @error('ninea')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nin_proprietaire" class="form-label">NIN du propriétaire</label>
                                            <input type="text" 
                                                   class="form-control @error('nin_proprietaire') is-invalid @enderror" 
                                                   id="nin_proprietaire" 
                                                   name="nin_proprietaire" 
                                                   value="{{ old('nin_proprietaire', $unite->nin_proprietaire) }}"
                                                   maxlength="13"
                                                   pattern="[0-9]{0,13}"
                                                   title="Numéro d'identification nationale (13 chiffres maximum)"
                                                   placeholder="Numéro d'identification nationale">
                                            @error('nin_proprietaire')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations de l'activité -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-store me-2"></i>Informations de l'activité</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="categorie" class="form-label">Catégorie <span class="text-danger">*</span></label>
                                            <select class="form-select @error('categorie') is-invalid @enderror" 
                                                    id="categorie" 
                                                    name="categorie" 
                                                    required>
                                                <option value="">Sélectionner une catégorie</option>
                                                <option value="activite_commerciale" {{ old('categorie', $unite->categorie) === 'activite_commerciale' ? 'selected' : '' }}>Activité commerciale</option>
                                                <option value="point_interet" {{ old('categorie', $unite->categorie) === 'point_interet' ? 'selected' : '' }}>Point d'intérêt</option>
                                            </select>
                                            @error('categorie')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nature" class="form-label">Nature</label>
                                            <input type="text" 
                                                   class="form-control @error('nature') is-invalid @enderror" 
                                                   id="nature" 
                                                   name="nature" 
                                                   value="{{ old('nature', $unite->nature) }}" 
                                                   placeholder="Ex: Boutique, Restaurant, Atelier...">
                                            @error('nature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>


                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sous_type_commerce" class="form-label">Sous-type de commerce</label>
                                    <input type="text" 
                                           class="form-control @error('sous_type_commerce') is-invalid @enderror" 
                                           id="sous_type_commerce" 
                                           name="sous_type_commerce" 
                                           value="{{ old('sous_type_commerce', $unite->sous_type_commerce) }}" 
                                           placeholder="Précisez le sous-type si nécessaire">
                                    @error('sous_type_commerce')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    
                <!-- Localisation et zone -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Localisation et zone</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                                    <label for="zone_id" class="form-label">Zone fiscale <span class="text-danger">*</span></label>
                                    <select class="form-select @error('zone_id') is-invalid @enderror" 
                                            id="zone_id" 
                                                    name="zone_id" 
                                                    required>
                                                <option value="">Sélectionner une zone</option>
                                        @foreach($zones as $zone)
                                                    <option value="{{ $zone->id }}" {{ old('zone_id', $unite->zone_id) == $zone->id ? 'selected' : '' }}>
                                                {{ $zone->nom }}
                                            </option>
                                        @endforeach
                                                <option value="autre" {{ old('zone_id') === 'autre' ? 'selected' : '' }}>Autres (préciser)</option>
                                    </select>
                                    @error('zone_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    </div>
                                    
                                    <div class="mb-3" id="nouvelle-zone-field" style="display: none;">
                                        <label for="nouvelle_zone" class="form-label">Nom de la nouvelle zone <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('nouvelle_zone') is-invalid @enderror" 
                                               id="nouvelle_zone" 
                                               name="nouvelle_zone" 
                                               value="{{ old('nouvelle_zone') }}" 
                                               placeholder="Entrez le nom de la nouvelle zone">
                                        @error('nouvelle_zone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="taxe_id" class="form-label">Taxe applicable <span class="text-danger">*</span></label>
                                        
                                        <!-- Champ de recherche avec auto-complétion pour les taxes -->
                                        <div class="position-relative">
                                            <input type="text" 
                                                   class="form-control @error('taxe_id') is-invalid @enderror" 
                                                   id="taxe_search" 
                                                   placeholder="🔍 Tapez pour rechercher une taxe (ex: Taxi... pour Taxe taxi)"
                                                   autocomplete="off">
                                            
                                            <!-- Champ caché pour la valeur -->
                                            <input type="hidden" 
                                                   id="taxe_id" 
                                                   name="taxe_id" 
                                                   value="{{ old('taxe_id', $unite->taxe_id) }}" 
                                                   required>
                                            
                                            <!-- Liste déroulante des suggestions -->
                                            <div id="taxe_dropdown" class="dropdown-suggestions">
                                                <div class="suggestion-item" data-value="">
                                                    <i class="fas fa-ban text-muted me-2"></i>Aucune taxe
                                                </div>
                                                @foreach($taxes->sortBy('nom') as $taxe)
                                                    <div class="suggestion-item" data-value="{{ $taxe->id }}">
                                                        <i class="fas fa-coins text-warning me-2"></i>{{ $taxe->nom }}
                                                        <span class="badge bg-success ms-2">{{ number_format($taxe->montant, 0, ',', ' ') }} FCFA</span>
                                                        @if($taxe->periodicite && $taxe->periodicite !== 'non_periodique')
                                                            <small class="text-muted d-block">Périodicité: {{ ucfirst($taxe->periodicite) }}</small>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            
                                            <!-- Indicateur de sélection -->
                                            <div id="taxe_selected" class="selected-indicator" style="display: none;">
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                <span id="taxe_selected_text"></span>
                                                <button type="button" class="btn-clear" onclick="clearTaxe()">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        @error('taxe_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            Tapez le nom ou le montant pour filtrer les taxes
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="adresse" class="form-label">Adresse</label>
                                        <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                                  id="adresse" 
                                                  name="adresse" 
                                                  rows="2" 
                                                  placeholder="Adresse complète de l'unité commerciale">{{ old('adresse', $unite->adresse) }}</textarea>
                                        @error('adresse')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                            <label for="latitude" class="form-label">Latitude</label>
                                            <input type="number" 
                                                   step="any" 
                                                   class="form-control @error('latitude') is-invalid @enderror" 
                                                   id="latitude" 
                                                   name="latitude" 
                                                   value="{{ old('latitude', $unite->latitude) }}" 
                                                   placeholder="Ex: 14.123456">
                                            @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="longitude" class="form-label">Longitude</label>
                                            <input type="number" 
                                                   step="any" 
                                                   class="form-control @error('longitude') is-invalid @enderror" 
                                                   id="longitude" 
                                                   name="longitude" 
                                                   value="{{ old('longitude', $unite->longitude) }}" 
                                                   placeholder="Ex: -16.123456">
                                            @error('longitude')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                </div>
                            </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('unites.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du champ "Autres zones"
    const zoneSelect = document.getElementById('zone_id');
    const nouvelleZoneField = document.getElementById('nouvelle-zone-field');
    const nouvelleZoneInput = document.getElementById('nouvelle_zone');
    
    zoneSelect.addEventListener('change', function() {
        if (this.value === 'autre') {
            nouvelleZoneField.style.display = 'block';
            nouvelleZoneInput.required = true;
    } else {
            nouvelleZoneField.style.display = 'none';
            nouvelleZoneInput.required = false;
            nouvelleZoneInput.value = '';
        }
    });
    
    // Gestion intelligente du champ NINEA/Immatriculation
    const typeCommerceSelect = document.getElementById('type_commerce');
    const natureField = document.getElementById('nature');
    const taxeSelect = document.getElementById('taxe_id');
    const nineaLabel = document.getElementById('ninea-label');
    const nineaField = document.getElementById('ninea');
    const nineaHelp = document.getElementById('ninea-help');
    
    function updateNineaField() {
        const typeCommerce = typeCommerceSelect ? typeCommerceSelect.value.toLowerCase() : '';
        const nature = natureField ? natureField.value.toLowerCase() : '';
        
        // Récupérer le nom de la taxe sélectionnée
        const taxeText = taxeSelect.selectedOptions[0] ? taxeSelect.selectedOptions[0].text.toLowerCase() : '';

        // Mots-clés de transport
        const transportKeywords = [
            'taxi', 'clado', 'moto', 'jakarta', 'bus', 'transport', 
            'car', 'voiture', 'auto', 'camion', 'véhicule', 'conducteur'
        ];
        
        // Vérifier si c'est un transport
        const isTransport = transportKeywords.some(keyword => 
            typeCommerce.includes(keyword) || 
            nature.includes(keyword) || 
            taxeText.includes(keyword)
        );
        
        if (isTransport) {
            nineaLabel.textContent = 'Numéro d\'immatriculation';
            nineaField.placeholder = 'Numéro d\'immatriculation du véhicule';
            nineaHelp.textContent = 'Numéro d\'immatriculation du véhicule de transport';
        } else {
            nineaLabel.textContent = 'NINEA';
            nineaField.placeholder = 'Numéro d\'identification des entreprises';
            nineaHelp.textContent = 'Numéro d\'identification des entreprises et associations';
        }
    }
    
    // Écouter les changements
    if (typeCommerceSelect) typeCommerceSelect.addEventListener('change', updateNineaField);
    if (natureField) natureField.addEventListener('input', updateNineaField);
    if (taxeSelect) taxeSelect.addEventListener('change', updateNineaField);
    
    // Appel initial
    updateNineaField();
    
    // Restriction des champs numériques
    function setupNumericFields() {
        // Champ NIN : seulement des chiffres, max 13
        const ninField = document.getElementById('nin_proprietaire');
        if (ninField) {
            ninField.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 13);
            });
        }
        
        // Champ téléphone : seulement des chiffres, max 12
        const telField = document.getElementById('telephone');
        if (telField) {
            telField.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 12);
            });
        }
    }
    
    setupNumericFields();
});
</script>

<!-- CSS pour les champs de recherche avec auto-complétion -->
<style>
/* Styles pour les suggestions */
.dropdown-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 6px 6px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.suggestion-item {
    padding: 12px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f1f1f1;
    transition: background-color 0.2s;
}

.suggestion-item:hover {
    background-color: #f8f9fa;
}

.suggestion-item.selected {
    background-color: #e3f2fd;
    border-left: 3px solid #2196f3;
}

.suggestion-item:last-child {
    border-bottom: none;
}

/* Indicateur de sélection */
.selected-indicator {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #f0f9ff;
    border: 2px solid #10b981;
    border-radius: 6px;
    padding: 8px 35px 8px 12px;
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #065f46;
}

.btn-clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6b7280;
    padding: 4px 6px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s;
}

.btn-clear:hover {
    background-color: #fee2e2;
    color: #dc2626;
}

/* Animation pour les champs actifs */
.form-control:focus + .dropdown-suggestions {
    display: block;
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Badge amélioration */
.suggestion-item .badge {
    font-size: 10px;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
    .dropdown-suggestions {
        max-height: 200px;
    }
    
    .suggestion-item {
        padding: 10px 12px;
        font-size: 14px;
    }
}
</style>

<!-- JavaScript pour les champs de recherche -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration des champs de recherche
    setupSearchField('type_commerce');
    setupSearchField('taxe');
    
    function setupSearchField(fieldName) {
        const searchInput = document.getElementById(fieldName + '_search');
        const hiddenInput = document.getElementById(fieldName + '_id') || document.getElementById(fieldName);
        const dropdown = document.getElementById(fieldName + '_dropdown');
        const selectedIndicator = document.getElementById(fieldName + '_selected');
        const selectedText = document.getElementById(fieldName + '_selected_text');
        
        if (!searchInput || !hiddenInput || !dropdown) return;
        
        let suggestions = Array.from(dropdown.querySelectorAll('.suggestion-item'));
        let selectedIndex = -1;
        
        // Restaurer la valeur sélectionnée au chargement
        const currentValue = hiddenInput.value;
        if (currentValue) {
            const selectedItem = suggestions.find(item => item.dataset.value === currentValue);
            if (selectedItem) {
                selectItem(selectedItem);
            }
        }
        
        // Événements de saisie
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            selectedIndex = -1;
            
            if (query.length === 0) {
                hideDropdown();
                return;
            }
            
            // Filtrer les suggestions
            let hasResults = false;
            suggestions.forEach(item => {
                const text = item.textContent.toLowerCase();
                const dataValue = item.dataset.value;
                
                if (text.includes(query) || (dataValue && dataValue.toString().includes(query))) {
                    item.style.display = 'block';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            if (hasResults) {
                showDropdown();
            } else {
                hideDropdown();
            }
        });
        
        // Événements de focus/blur
        searchInput.addEventListener('focus', function() {
            if (this.value.length > 0) {
                showDropdown();
            }
        });
        
        searchInput.addEventListener('blur', function() {
            // Délai pour permettre le clic sur les suggestions
            setTimeout(() => {
                hideDropdown();
            }, 200);
        });
        
        // Navigation au clavier
        searchInput.addEventListener('keydown', function(e) {
            const visibleItems = suggestions.filter(item => item.style.display !== 'none');
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, visibleItems.length - 1);
                    updateSelection(visibleItems);
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, 0);
                    updateSelection(visibleItems);
                    break;
                    
                case 'Enter':
                    e.preventDefault();
                    if (selectedIndex >= 0 && visibleItems[selectedIndex]) {
                        selectItem(visibleItems[selectedIndex]);
                    }
                    break;
                    
                case 'Escape':
                    hideDropdown();
                    searchInput.blur();
                    break;
            }
        });
        
        // Clic sur les suggestions
        suggestions.forEach(item => {
            item.addEventListener('click', function() {
                selectItem(this);
            });
        });
        
        function showDropdown() {
            dropdown.style.display = 'block';
        }
        
        function hideDropdown() {
            dropdown.style.display = 'none';
            selectedIndex = -1;
            suggestions.forEach(item => item.classList.remove('selected'));
        }
        
        function updateSelection(visibleItems) {
            suggestions.forEach(item => item.classList.remove('selected'));
            if (selectedIndex >= 0 && visibleItems[selectedIndex]) {
                visibleItems[selectedIndex].classList.add('selected');
            }
        }
        
        function selectItem(item) {
            const value = item.dataset.value;
            const text = item.textContent.trim();
            
            hiddenInput.value = value;
            searchInput.value = '';
            
            if (selectedIndicator && selectedText) {
                selectedText.textContent = text;
                selectedIndicator.style.display = 'flex';
                searchInput.style.display = 'none';
            }
            
            hideDropdown();
            
            // Déclencher l'événement change pour les autres scripts
            const changeEvent = new Event('change', { bubbles: true });
            hiddenInput.dispatchEvent(changeEvent);
            
            console.log(`✅ ${fieldName} sélectionné:`, text, '(valeur:', value + ')');
        }
        
        // Fonction de nettoyage globale
        window['clear' + fieldName.charAt(0).toUpperCase() + fieldName.slice(1)] = function() {
            hiddenInput.value = '';
            searchInput.value = '';
            searchInput.style.display = 'block';
            if (selectedIndicator) {
                selectedIndicator.style.display = 'none';
            }
            hideDropdown();
            searchInput.focus();
        };
    }
    
    console.log('✅ Système de recherche avec auto-complétion initialisé');
});
</script>

@endsection 