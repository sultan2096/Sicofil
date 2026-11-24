@extends('layouts.app')

@section('title', 'Nouveau Recensement - SICOSA')

@push('styles')
<style>
/* Responsive Design pour le recensement */
@media (max-width: 768px) {
    .container-fluid {
        padding: 0.5rem;
    }
    
    .card {
        margin-bottom: 1rem;
        border-radius: 15px;
    }
    
    .card-header {
        padding: 1rem;
        font-size: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }
    
    /* Carte interactive responsive */
    #interactive-map {
        height: 300px !important;
        border-radius: 10px;
    }
    
    /* Boutons de géolocalisation */
    .d-grid.gap-3 {
        gap: 0.75rem !important;
    }
    
    .btn {
        border-radius: 10px;
        font-weight: 500;
    }
    
    /* Formulaire responsive */
    .form-label {
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .form-control,
    .form-select {
        font-size: 0.9rem;
        padding: 0.75rem;
        border-radius: 8px;
    }
    
    /* Grille responsive */
    .row.g-4 {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 1rem;
    }
    
    .col-md-6 {
        margin-bottom: 1rem;
    }
    
    /* Alertes responsive */
    .alert {
        font-size: 0.85rem;
        padding: 0.75rem;
        border-radius: 8px;
    }
    
    /* Position info responsive */
    #position-info .card-body {
        padding: 1rem;
    }
    
    #position-info .fw-bold {
        font-size: 0.9rem;
    }
    
    /* Manual input responsive */
    #manual-input .card-body {
        padding: 1rem;
    }
    
    #manual-input .form-control {
        font-size: 0.85rem;
    }
}

/* Optimisation pour tablettes */
@media (min-width: 769px) and (max-width: 1024px) {
    #interactive-map {
        height: 400px !important;
    }
    
    .btn-lg {
        padding: 1rem 2rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .form-control,
    .form-select {
        padding: 0.875rem;
    }
}

/* Amélioration de l'interface tactile */
@media (hover: none) and (pointer: coarse) {
    .btn {
        min-height: 44px; /* Taille minimale pour le touch */
    }
    
    .form-control,
    .form-select {
        min-height: 44px;
    }
    
    /* Espacement pour éviter les clics accidentels */
    .btn + .btn {
        margin-top: 0.5rem;
    }
}

/* Animation pour les boutons */
.btn {
    transition: all 0.3s ease;
}

.btn:active {
    transform: scale(0.95);
}

/* Amélioration de la lisibilité */
@media (max-width: 768px) {
    body {
        font-size: 0.9rem;
    }
    
    h1, h2, h3, h4, h5, h6 {
        line-height: 1.3;
    }
    
    .text-muted {
        font-size: 0.8rem;
    }
}

/* Optimisation des icônes */
@media (max-width: 768px) {
    .fas {
        margin-right: 0.5rem;
    }
    
    .btn i {
        font-size: 1.1rem;
    }
}

/* Amélioration de la navigation */
@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-outline-secondary {
        width: 100%;
        margin-top: 0.5rem;
    }
}

/* Optimisation des cartes */
@media (max-width: 768px) {
    .card.border-success,
    .card.border-info {
        border-width: 2px;
    }
    
    .card-header.bg-success,
    .card-header.bg-info {
        padding: 0.75rem 1rem;
    }
}

/* Amélioration des messages de statut */
#gps-status {
    border-radius: 8px;
    margin: 1rem 0;
}

#gps-status .alert {
    margin-bottom: 0;
}

/* Optimisation des listes */
@media (max-width: 768px) {
    ul {
        padding-left: 1.25rem;
    }
    
    li {
        margin-bottom: 0.25rem;
    }
}

/* Amélioration des badges */
@media (max-width: 768px) {
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.5rem;
    }
}

/* Optimisation des progress bars */
@media (max-width: 768px) {
    .progress {
        height: 8px;
        border-radius: 4px;
    }
}

/* Amélioration de l'accessibilité */
@media (max-width: 768px) {
    .btn:focus,
    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .btn:disabled {
        opacity: 0.6;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary mb-1">
                        <i class="fas fa-plus-circle"></i> 
                        Nouveau Recensement
                    </h2>
                    <p class="text-muted mb-0">Ajout d'une nouvelle unité commerciale</p>
                </div>
                <div>
                    <a href="{{ route('unites.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Étape 1: Géolocalisation -->
    <div id="geolocation-section" class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-success shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-crosshairs"></i> 
                        Capture de Position GPS
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Instructions importantes</h6>
                        <ul class="mb-0">
                            <li>Placez-vous devant l'unité commerciale</li>
                            <li>Assurez-vous d'avoir une bonne réception GPS</li>
                            <li>Autorisez l'accès à votre position si demandé</li>
                        </ul>
                    </div>

                    <div id="gps-status" class="alert alert-secondary" style="display: none;"></div>

                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-success btn-lg" id="capture-gps-btn">
                            <i class="fas fa-crosshairs"></i> 
                            Capturer ma position GPS
                        </button>
                        
                        <button type="button" class="btn btn-outline-info btn-sm" id="test-gps-btn">
                            <i class="fas fa-vial"></i> 
                            Tester la géolocalisation
                        </button>
                        
                        <button type="button" class="btn btn-outline-warning" id="manual-position-btn">
                            <i class="fas fa-edit"></i> 
                            Saisir position manuellement
                        </button>
                        
                        <button type="button" class="btn btn-outline-info" id="default-position-btn">
                            <i class="fas fa-map-marker-alt"></i> 
                            Utiliser position Sandiara
                        </button>
                    </div>

                    <!-- Position Info -->
                    <div id="position-info" class="mt-4" style="display: none;">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Position capturée</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label text-muted">Latitude</label>
                                        <div class="fw-bold text-primary" id="current-latitude">-</div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted">Longitude</label>
                                        <div class="fw-bold text-primary" id="current-longitude">-</div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Précision: <span id="current-accuracy">-</span>m | 
                                        Méthode: <span id="current-method">-</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Input -->
                    <div id="manual-input" class="mt-4" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Saisie manuelle des coordonnées</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Latitude</label>
                                        <input type="number" class="form-control" id="manual-lat" step="0.000001" placeholder="14.435400">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Longitude</label>
                                        <input type="number" class="form-control" id="manual-lng" step="0.000001" placeholder="-16.791530">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-success" id="apply-manual-btn">
                                        <i class="fas fa-check"></i> Appliquer
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="cancel-manual-btn">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Continue Button -->
                    <div id="continue-section" class="mt-4" style="display: none;">
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary btn-lg" id="continue-to-form-btn">
                                <i class="fas fa-arrow-right"></i> 
                                Continuer vers le formulaire
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-info shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-map"></i> 
                        Carte interactive
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="interactive-map" style="height: 500px; width: 100%;"></div>
                </div>
                <div class="card-footer text-center bg-light">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Déplacez le marqueur rouge pour ajuster votre position
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Étape 2: Formulaire de recensement -->
    <div id="form-section" class="row mt-4">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list"></i> 
                        Informations de l'unité commerciale
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('unites.store') }}" id="unite-form" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Coordonnées GPS (cachées) -->
                        <input type="hidden" name="latitude" id="form-latitude" required>
                        <input type="hidden" name="longitude" id="form-longitude" required>
                        <input type="hidden" name="altitude" id="form-altitude">
                        <input type="hidden" name="precision" id="form-precision">
                        
                        <!-- Section 1: Informations de base -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-info-circle"></i> Informations de base
                                </h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="categorie" class="form-label">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select @error('categorie') is-invalid @enderror" 
                                        id="categorie" 
                                        name="categorie" 
                                        required>
                                    <option value="">Sélectionnez une catégorie</option>
                                    <option value="activite_commerciale" {{ old('categorie') == 'activite_commerciale' ? 'selected' : '' }}>
                                        Activité commerciale
                                    </option>
                                    <option value="point_interet" {{ old('categorie') == 'point_interet' ? 'selected' : '' }}>
                                        Point d'intérêt commercial
                                    </option>
                                </select>
                                @error('categorie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nature" class="form-label">Nature de l'activité <span class="text-danger">*</span></label>
                                <select id="nature" name="nature" class="form-select @error('nature') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner la nature --</option>
                                </select>
                                @error('nature')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Choisissez d'abord la nature pour filtrer les types
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="type_commerce" class="form-label">Type de commerce <span class="text-danger">*</span></label>
                                <select id="type_commerce" name="type_commerce" class="form-select @error('type_commerce') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner le type --</option>
                                </select>
                                @error('type_commerce')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-filter"></i> Types filtrés selon la nature sélectionnée
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="sous_type_commerce" class="form-label">Précision du commerce</label>
                                <input type="text" 
                                       class="form-control @error('sous_type_commerce') is-invalid @enderror" 
                                       id="sous_type_commerce" 
                                       name="sous_type_commerce" 
                                       value="{{ old('sous_type_commerce') }}" 
                                       placeholder="Précision sur le type d'activité (ex: Boulangerie, Quincaillerie...)">
                                @error('sous_type_commerce')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Section 2: Informations du propriétaire -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-user"></i> Informations du propriétaire
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label for="nom_proprietaire" class="form-label">Nom du propriétaire <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nom_proprietaire') is-invalid @enderror" 
                                       id="nom_proprietaire" 
                                       name="nom_proprietaire" 
                                       value="{{ old('nom_proprietaire') }}" 
                                       required>
                                @error('nom_proprietaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="prenom_proprietaire" class="form-label">Prénom du propriétaire</label>
                                <input type="text" 
                                       class="form-control @error('prenom_proprietaire') is-invalid @enderror" 
                                       id="prenom_proprietaire" 
                                       name="prenom_proprietaire" 
                                       value="{{ old('prenom_proprietaire') }}">
                                @error('prenom_proprietaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" 
                                       class="form-control @error('telephone') is-invalid @enderror" 
                                       id="telephone" 
                                       name="telephone" 
                                       value="{{ old('telephone') }}" 
                                       maxlength="9"
                                       pattern="[0-9]{9}"
                                       title="Numéro de téléphone (9 chiffres exactement)"
                                       placeholder="77 123 45 67">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">9 chiffres exactement</div>
                            </div>

                            <div class="col-md-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="email@exemple.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="numero_cin" class="form-label">Numéro CIN <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('numero_cin') is-invalid @enderror" 
                                       id="numero_cin" 
                                       name="numero_cin" 
                                       value="{{ old('numero_cin') }}" 
                                       required
                                       placeholder="1234567890123456"
                                       maxlength="16"
                                       pattern="[0-9]{13,16}">
                                @error('numero_cin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-id-card text-info"></i> Carte d'Identité Nationale (13-16 chiffres)
                                </div>
                            </div>

                            <!-- Champ d'immatriculation pour les transports -->
                            <div class="col-md-4" id="immatriculation_field" style="display: none;">
                                <label for="numero_immatriculation" class="form-label">
                                    <i class="fas fa-car text-warning"></i> Numéro d'immatriculation <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('numero_immatriculation') is-invalid @enderror" 
                                       id="numero_immatriculation" 
                                       name="numero_immatriculation" 
                                       value="{{ old('numero_immatriculation') }}" 
                                       placeholder="DK-1234-AB"
                                       maxlength="15"
                                       style="text-transform: uppercase;">
                                @error('numero_immatriculation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-truck text-warning"></i> Plaque d'immatriculation du véhicule
                                </div>
                            </div>

                            <div class="col-md-4" id="ninea_field">
                                <label for="ninea" class="form-label">NINEA</label>
                                <input type="text" 
                                       class="form-control @error('ninea') is-invalid @enderror" 
                                       id="ninea" 
                                       name="ninea" 
                                       value="{{ old('ninea') }}" 
                                       placeholder="Numéro d'identification des entreprises">
                                @error('ninea')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Section 3: Localisation -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-map-marker-alt"></i> Localisation
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label for="zone_id" class="form-label">Zone fiscale <span class="text-danger">*</span></label>
                                <select class="form-select @error('zone_id') is-invalid @enderror" 
                                        id="zone_id" 
                                        name="zone_id" 
                                        required
                                        {{ auth()->user()->role === 'collecteur' ? 'readonly disabled' : '' }}>
                                    <option value="">Sélectionnez une zone</option>
                                    @foreach($zones as $zone)
                                        @php
                                            $isSelected = false;
                                            if (auth()->user()->role === 'collecteur' && isset($selectedZoneId)) {
                                                $isSelected = $zone->id == $selectedZoneId;
                                            } else {
                                                $isSelected = old('zone_id') == $zone->id;
                                            }
                                        @endphp
                                        <option value="{{ $zone->id }}" {{ $isSelected ? 'selected' : '' }}>
                                            {{ $zone->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('zone_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(auth()->user()->role === 'collecteur')
                                    <div class="form-text">
                                        <i class="fas fa-lock text-info"></i> Zone automatiquement assignée selon votre profil
                                    </div>
                                    <!-- Champ caché pour envoyer la zone quand le select est désactivé -->
                                    <input type="hidden" name="zone_id" value="{{ $selectedZoneId ?? '' }}">
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="taxe_id" class="form-label">Taxe applicable <span class="text-danger">*</span></label>
                                <select class="form-select @error('taxe_id') is-invalid @enderror" 
                                        id="taxe_id" 
                                        name="taxe_id" 
                                        required>
                                    <option value="">Sélectionnez une taxe</option>
                                    @foreach($taxes as $taxe)
                                        <option value="{{ $taxe->id }}" {{ old('taxe_id') == $taxe->id ? 'selected' : '' }}>
                                            {{ $taxe->nom }} - {{ number_format($taxe->montant, 0, ',', ' ') }} FCFA 
                                            ({{ ucfirst($taxe->periodicite) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('taxe_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-money-bill-wave text-success"></i> Taxe qui sera appliquée à cette unité
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="adresse" class="form-label">Adresse</label>
                                <input type="text" 
                                       class="form-control @error('adresse') is-invalid @enderror" 
                                       id="adresse" 
                                       name="adresse" 
                                       value="{{ old('adresse') }}" 
                                       placeholder="Adresse complète">
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> 
                                Enregistrer l'unité
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Données de correspondance Nature / Type de commerce
const commerceData = {
    activite_commerciale: {
        "Boutique ou Magasin": [
            "Alimentation", "Grossiste", "Mercerie", "Quincaillerie", 
            "Shopping", "Cosmétique", "Pharmacie", "Bijouterie"
        ],
        "Atelier": [
            "Atelier mécanicien", "Atelier de couture", "Atelier de coiffure", 
            "Atelier de forgeron", "Atelier de cordonnerie", "Atelier de menuiserie", 
            "Boulangerie", "Atelier de soudure"
        ],
        "Agriculture": [
            "Chevaux", "Ânes", "Ovins", "Caprins", "Bovins", 
            "Vaches", "Moutons", "Chèvres", "Volailles"
        ],
        "Transport": [
            "Bus", "Camion-citerne de carburant", "Camionnette", "Calèche", 
            "Camion gros porteur", "Taxi clando", "Moto Jakarta", "Car rapide"
        ],
        "Étal": ["Vente sur étal"],
        "Table ou Tente commerciale": ["Vente sur table", "Vente sous tente"],
        "Cantine ou Kiosque": [
            "Restaurant", "Gargote", "Dibiterie", "Fast Food", 
            "Café", "Bar", "Kiosque"
        ],
        "Entreprise": [
            "Entrepôt", "Grande exploitation agricole", "Supermarché", 
            "Station-service", "Usine", "Bureau"
        ]
    },
    point_interet: {
        "Foncier": ["Location de terrain", "Location de bâtiments"],
        "Publicité": ["Véhicules publicitaires", "Panneaux publicitaires", "Enseignes"],
        "Spectacles": ["Lieux d'événements", "Salles de spectacle", "Terrains de sport"],
        "Papiers administratifs": [
            "Extrait de naissance", "Certificat de décès", "Acte de mariage", 
            "Photocopie", "Dactylographie"
        ]
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const categorieSelect = document.getElementById('categorie');
    const natureSelect = document.getElementById('nature');
    const typeCommerceSelect = document.getElementById('type_commerce');

    // Fonction pour peupler les natures selon la catégorie
    function populateNatures(categorie) {
        natureSelect.innerHTML = '<option value="">-- Sélectionner la nature --</option>';
        typeCommerceSelect.innerHTML = '<option value="">-- Sélectionner le type --</option>';
        
        if (categorie && commerceData[categorie]) {
            Object.keys(commerceData[categorie]).forEach(nature => {
                const option = document.createElement('option');
                option.value = nature;
                option.textContent = nature;
                natureSelect.appendChild(option);
            });
        }
    }

    // Fonction pour peupler les types selon la nature
    function populateTypes(nature, categorie) {
        typeCommerceSelect.innerHTML = '<option value="">-- Sélectionner le type --</option>';
        
        if (nature && categorie && commerceData[categorie] && commerceData[categorie][nature]) {
            commerceData[categorie][nature].forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                typeCommerceSelect.appendChild(option);
            });
        }
    }

    // Fonction pour sélectionner la nature selon le type choisi
    function selectNatureByType(selectedType, categorie) {
        if (!selectedType || !categorie || !commerceData[categorie]) return;
        
        for (const [nature, types] of Object.entries(commerceData[categorie])) {
            if (types.includes(selectedType)) {
                natureSelect.value = nature;
                populateTypes(nature, categorie);
                break;
            }
        }
    }

    // Event listeners
    categorieSelect.addEventListener('change', function() {
        populateNatures(this.value);
    });

    natureSelect.addEventListener('change', function() {
        const categorie = categorieSelect.value;
        populateTypes(this.value, categorie);
    });

    typeCommerceSelect.addEventListener('change', function() {
        const categorie = categorieSelect.value;
        if (this.value && !natureSelect.value) {
            selectNatureByType(this.value, categorie);
        }
    });

    // Initialisation au chargement
    const initialCategorie = categorieSelect.value;
    if (initialCategorie) {
        populateNatures(initialCategorie);
        
        const initialNature = "{{ old('nature') }}";
        if (initialNature) {
            natureSelect.value = initialNature;
            populateTypes(initialNature, initialCategorie);
            
            const initialType = "{{ old('type_commerce') }}";
            if (initialType) {
                typeCommerceSelect.value = initialType;
            }
        }
    }
});

// === JavaScript pour la géolocalisation et la carte ===
let interactiveMap;
let currentMarker;
let currentPosition = null;

// Initialiser la carte
function initializeMap() {
    try {
        // Coordonnées par défaut pour Sandiara
        const defaultLat = 14.435400;
        const defaultLng = -16.791530;
        
        interactiveMap = L.map('interactive-map').setView([defaultLat, defaultLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(interactiveMap);
        
        console.log('✅ Carte interactive initialisée');
    } catch (error) {
        console.error('❌ Erreur lors de l\'initialisation de la carte:', error);
    }
}

// Gestionnaires GPS
function setupGPSHandlers() {
    // Capture GPS
    document.getElementById('capture-gps-btn').addEventListener('click', captureGPS);
    
    // Test GPS
    document.getElementById('test-gps-btn').addEventListener('click', testGeolocation);
    
    // Position manuelle
    document.getElementById('manual-position-btn').addEventListener('click', showManualInput);
    document.getElementById('apply-manual-btn').addEventListener('click', applyManualPosition);
    document.getElementById('cancel-manual-btn').addEventListener('click', hideManualInput);
    
    // Position par défaut (Sandiara)
    document.getElementById('default-position-btn').addEventListener('click', function() {
        currentPosition = {
            lat: 14.435400,
            lng: -16.791530,
            accuracy: 10,
            altitude: null,
            method: 'Position Sandiara'
        };
        
        handlePositionSuccess();
    });
    
    // Continuer vers le formulaire
    document.getElementById('continue-to-form-btn').addEventListener('click', showFormSection);
}

function captureGPS() {
    if (!navigator.geolocation) {
        showStatus('error', '❌ Géolocalisation non supportée par ce navigateur');
        return;
    }

    const btn = document.getElementById('capture-gps-btn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Capture en cours...';
    btn.disabled = true;
    
    showStatus('info', '🔍 Recherche de votre position GPS...');

    // Options améliorées pour la géolocalisation
    const options = {
        enableHighAccuracy: true,  // Utiliser le GPS si disponible
        timeout: 30000,            // 30 secondes de timeout
        maximumAge: 300000         // Accepter une position de 5 minutes maximum
    };

    // Première tentative avec haute précision
    navigator.geolocation.getCurrentPosition(
        function(position) {
            currentPosition = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                accuracy: position.coords.accuracy,
                altitude: position.coords.altitude || null,
                method: 'GPS Haute Précision'
            };
            
            handlePositionSuccess();
        },
        function(error) {
            // Si la haute précision échoue, essayer avec une précision normale
            console.log('Tentative haute précision échouée, essai avec précision normale...');
            
            const lowAccuracyOptions = {
                enableHighAccuracy: false,
                timeout: 15000,
                maximumAge: 600000  // 10 minutes
            };
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    currentPosition = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        altitude: position.coords.altitude || null,
                        method: 'GPS Précision Normale'
                    };
                    
                    handlePositionSuccess();
                },
                function(secondError) {
                    handlePositionError(secondError);
                },
                lowAccuracyOptions
            );
        },
        options
    );
}

function handlePositionSuccess() {
    displayPositionInfo();
    updateMapWithPosition();
    showStatus('success', '✅ Position GPS capturée avec succès !');
    
    const btn = document.getElementById('capture-gps-btn');
    btn.innerHTML = '<i class="fas fa-check"></i> Position capturée';
    btn.classList.remove('btn-success');
    btn.classList.add('btn-outline-success');
    btn.disabled = false;
    
    document.getElementById('continue-section').style.display = 'block';
    
    console.log('✅ Position capturée:', currentPosition);
}

function handlePositionError(error) {
    let message = '';
    let details = '';
    
    switch(error.code) {
        case error.PERMISSION_DENIED:
            message = '❌ Autorisation refusée';
            details = 'Vous devez autoriser l\'accès à votre position dans les paramètres du navigateur. Cliquez sur l\'icône de cadenas dans la barre d\'adresse.';
            break;
        case error.POSITION_UNAVAILABLE:
            message = '❌ Position indisponible';
            details = 'Impossible d\'obtenir votre position GPS. Vérifiez que vous êtes à l\'extérieur et que le GPS est activé.';
            break;
        case error.TIMEOUT:
            message = '⏱️ Délai dépassé';
            details = 'La géolocalisation prend trop de temps. Essayez de vous déplacer vers une zone plus dégagée.';
            break;
        default:
            message = '❌ Erreur inconnue';
            details = 'Erreur non identifiée. Code: ' + error.code;
            break;
    }
    
    showStatus('error', `${message}: ${details}`);
    
    const btn = document.getElementById('capture-gps-btn');
    btn.innerHTML = '<i class="fas fa-crosshairs"></i> Réessayer la capture GPS';
    btn.disabled = false;
    
    console.error('❌ Erreur géolocalisation:', error);
    
    // Afficher des conseils pour résoudre le problème
    showGeolocationTips();
}

function displayPositionInfo() {
    document.getElementById('current-latitude').textContent = currentPosition.lat.toFixed(6);
    document.getElementById('current-longitude').textContent = currentPosition.lng.toFixed(6);
    document.getElementById('current-accuracy').textContent = Math.round(currentPosition.accuracy);
    document.getElementById('current-method').textContent = currentPosition.method;
    document.getElementById('position-info').style.display = 'block';
}

function updateMapWithPosition() {
    try {
        // Supprimer l'ancien marqueur
        if (currentMarker) {
            interactiveMap.removeLayer(currentMarker);
        }
        
        // Centrer la carte sur la nouvelle position
        interactiveMap.setView([currentPosition.lat, currentPosition.lng], 17);
        
        // Créer un marqueur rouge personnalisé
        const redIcon = L.divIcon({
            className: 'custom-red-marker',
            html: '<div style="background-color: #dc3545; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 3px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });
        
        // Ajouter le marqueur
        currentMarker = L.marker([currentPosition.lat, currentPosition.lng], {
            icon: redIcon,
            draggable: true
        })
        .addTo(interactiveMap)
        .bindPopup(`
            <strong>📍 Position de l'unité</strong><br>
            <small>Méthode: ${currentPosition.method}</small><br>
            <small>Lat: ${currentPosition.lat.toFixed(6)}</small><br>
            <small>Lng: ${currentPosition.lng.toFixed(6)}</small><br>
            <em>Déplacez pour ajuster</em>
        `)
        .openPopup();
        
        // Événement de déplacement du marqueur
        currentMarker.on('dragend', function(e) {
            const newPos = e.target.getLatLng();
            currentPosition.lat = newPos.lat;
            currentPosition.lng = newPos.lng;
            currentPosition.method = 'Ajusté manuellement';
            
            displayPositionInfo();
            showStatus('success', '✅ Position ajustée sur la carte');
        });
        
    } catch (error) {
        console.error('❌ Erreur lors de la mise à jour de la carte:', error);
        showStatus('error', 'Erreur lors de l\'affichage sur la carte');
    }
}

function showManualInput() {
    document.getElementById('manual-input').style.display = 'block';
    document.getElementById('manual-lat').value = '';
    document.getElementById('manual-lng').value = '';
}

function hideManualInput() {
    document.getElementById('manual-input').style.display = 'none';
}

function applyManualPosition() {
    const lat = parseFloat(document.getElementById('manual-lat').value);
    const lng = parseFloat(document.getElementById('manual-lng').value);
    
    if (isNaN(lat) || isNaN(lng)) {
        alert('⚠️ Veuillez saisir des coordonnées valides');
        return;
    }
    
    if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
        alert('⚠️ Coordonnées hors limites\nLatitude: -90 à 90\nLongitude: -180 à 180');
        return;
    }
    
    currentPosition = {
        lat: lat,
        lng: lng,
        accuracy: 0,
        altitude: null,
        method: 'Saisie manuelle'
    };
    
    hideManualInput();
    handlePositionSuccess();
}

function showFormSection() {
    if (!currentPosition) {
        alert('⚠️ Veuillez d\'abord capturer votre position GPS.');
        return;
    }
    
    // Remplir les champs cachés du formulaire
    document.getElementById('form-latitude').value = currentPosition.lat.toFixed(6);
    document.getElementById('form-longitude').value = currentPosition.lng.toFixed(6);
    document.getElementById('form-altitude').value = currentPosition.altitude || '';
    document.getElementById('form-precision').value = currentPosition.accuracy || '';
    
    // Changer de section
    document.getElementById('geolocation-section').style.display = 'none';
    document.getElementById('form-section').style.display = 'block';
    
    // Faire défiler vers le formulaire
    document.getElementById('form-section').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
    
    console.log('✅ Passage au formulaire avec position:', currentPosition);
}

function showStatus(type, message) {
    const statusElement = document.getElementById('gps-status');
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    statusElement.className = `alert ${alertClass}`;
    statusElement.innerHTML = `<strong>${message}</strong>`;
    statusElement.style.display = 'block';
    
    // Masquer automatiquement après 5 secondes pour les messages de succès
    if (type === 'success') {
        setTimeout(() => {
            statusElement.style.display = 'none';
        }, 5000);
    }
}

function showGeolocationTips() {
    const tips = `
        <div class="alert alert-warning mt-3">
            <h6><i class="fas fa-lightbulb"></i> Conseils pour résoudre le problème :</h6>
            <ul class="mb-0">
                <li>Assurez-vous d'être à l'extérieur ou près d'une fenêtre</li>
                <li>Vérifiez que le GPS est activé sur votre appareil</li>
                <li>Autorisez l'accès à la position dans votre navigateur</li>
                <li>Essayez de vous déplacer vers une zone plus dégagée</li>
                <li>Utilisez "Position manuelle" si le GPS ne fonctionne pas</li>
            </ul>
        </div>
    `;
    
    const statusDiv = document.getElementById('gps-status');
    statusDiv.innerHTML += tips;
}

function testGeolocation() {
    if (!navigator.geolocation) {
        showStatus('error', '❌ Géolocalisation non supportée par ce navigateur');
        return;
    }
    
    const btn = document.getElementById('test-gps-btn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Test en cours...';
    btn.disabled = true;
    
    showStatus('info', '🧪 Test de géolocalisation en cours...');
    
    const options = {
        enableHighAccuracy: false,
        timeout: 10000,
        maximumAge: 300000
    };
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const accuracy = Math.round(position.coords.accuracy);
            const lat = position.coords.latitude.toFixed(6);
            const lng = position.coords.longitude.toFixed(6);
            
            showStatus('success', `✅ Test réussi ! Position: ${lat}, ${lng} (Précision: ${accuracy}m)`);
            
            btn.innerHTML = '<i class="fas fa-check"></i> Test réussi';
            btn.classList.remove('btn-outline-info');
            btn.classList.add('btn-outline-success');
            
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-vial"></i> Tester la géolocalisation';
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-outline-info');
                btn.disabled = false;
            }, 3000);
        },
        function(error) {
            showStatus('error', `❌ Test échoué: ${error.message}`);
            btn.innerHTML = '<i class="fas fa-vial"></i> Tester la géolocalisation';
            btn.disabled = false;
        },
        options
    );
}

// Validation pour le champ CIN
function setupCINValidation() {
    const cinField = document.getElementById('numero_cin');
    if (cinField) {
        cinField.addEventListener('input', function(e) {
            // Supprimer tous les caractères non numériques
            this.value = this.value.replace(/\D/g, '');
            // Limiter à 16 chiffres
            if (this.value.length > 16) {
                this.value = this.value.slice(0, 16);
            }
        });
        
        cinField.addEventListener('keypress', function(e) {
            // Empêcher la saisie de caractères non numériques
            if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }
}

// Gestion du champ d'immatriculation pour les transports
function setupTransportImmatriculationField() {
    const natureSelect = document.getElementById('nature');
    const typeCommerceSelect = document.getElementById('type_commerce');
    const immatriculationField = document.getElementById('immatriculation_field');
    const nineaField = document.getElementById('ninea_field');
    const immatriculationInput = document.getElementById('numero_immatriculation');
    
    function checkIfTransport() {
        const nature = natureSelect ? natureSelect.value : '';
        const typeCommerce = typeCommerceSelect ? typeCommerceSelect.value : '';
        
        // Vérifier si c'est un transport
        const isTransport = nature === 'Transport' || 
                          (typeCommerce && [
                              'Bus', 'Camion-citerne de carburant', 'Camionnette', 'Calèche', 
                              'Camion gros porteur', 'Taxi clando', 'Moto Jakarta', 'Car rapide'
                          ].includes(typeCommerce));
        
        if (isTransport) {
            // Afficher le champ d'immatriculation
            immatriculationField.style.display = 'block';
            immatriculationInput.required = true;
            
            // Masquer ou redimensionner le champ NINEA
            nineaField.classList.remove('col-md-4');
            nineaField.classList.add('col-md-4');
            
            console.log('🚛 Mode transport activé - champ immatriculation affiché');
        } else {
            // Masquer le champ d'immatriculation
            immatriculationField.style.display = 'none';
            immatriculationInput.required = false;
            immatriculationInput.value = '';
            
            // Restaurer le champ NINEA
            nineaField.classList.remove('col-md-4');
            nineaField.classList.add('col-md-4');
            
            console.log('🏪 Mode commerce classique - champ immatriculation masqué');
        }
    }
    
    // Ajouter les gestionnaires d'événements
    if (natureSelect) {
        natureSelect.addEventListener('change', checkIfTransport);
    }
    if (typeCommerceSelect) {
        typeCommerceSelect.addEventListener('change', checkIfTransport);
    }
    
    // Validation du format d'immatriculation
    if (immatriculationInput) {
        immatriculationInput.addEventListener('input', function(e) {
            // Convertir en majuscules et formater
            this.value = this.value.toUpperCase();
            
            // Supprimer les caractères non autorisés (garder lettres, chiffres et tirets)
            this.value = this.value.replace(/[^A-Z0-9\-]/g, '');
            
            // Limiter la longueur
            if (this.value.length > 15) {
                this.value = this.value.slice(0, 15);
            }
        });
    }
    
    // Vérification initiale
    checkIfTransport();
}

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser la carte
    setTimeout(initializeMap, 100);
    
    // Configurer les gestionnaires GPS
    setupGPSHandlers();
    
    // Configurer la validation CIN
    setupCINValidation();
    
    // Configurer le champ d'immatriculation pour les transports
    setupTransportImmatriculationField();
    
    console.log('✅ Géolocalisation, carte et champs spécialisés initialisés');
});
</script>

<!-- Liens CSS et JS pour Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@endsection 