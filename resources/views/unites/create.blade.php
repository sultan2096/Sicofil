@extends('layouts.app')

@section('title', 'Nouveau Recensement - SICOSA')

@section('content')
<style>
/* Mise en page carte / formulaire - Nouveau Recensement */
#geolocation-section .row {
    align-items: start;
}
#geolocation-section .col-lg-6:last-child .card {
    max-height: 100%;
}
#geolocation-section .map-card-wrapper .card-body {
    padding: 0 !important;
    overflow: hidden;
    min-height: 0;
}
/* Carte réduite et bien placée dans sa zone */
#interactive-map {
    height: 300px !important;
    max-height: 300px !important;
    width: 100% !important;
    min-height: 240px;
    border-radius: 0 0 0.25rem 0.25rem;
}
#geolocation-section .map-card-wrapper {
    max-width: 100%;
    overflow: hidden;
}
#geolocation-section .map-card-wrapper .card {
    max-width: 100%;
    overflow: hidden;
}
@media (max-width: 991px) {
    #interactive-map {
        height: 280px !important;
        max-height: 280px !important;
    }
}
/* Sur cette page : masquer le bouton Déconnexion du menu pour éviter les clics accidentels */
body:has(#geolocation-section) nav form[action*="logout"] {
    display: none !important;
}
</style>
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-primary mb-1">
                <i class="fas fa-plus-circle"></i> 
                Nouveau Recensement
            </h2>
            <p class="text-muted mb-0">Ajout d'une nouvelle unité commerciale</p>
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
                            Utiliser position {{ isset($commune) ? $commune->nom : 'Sandiara' }}
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
                                        <input type="number" class="form-control" id="manual-lat" step="0.000001" placeholder="{{ isset($mapCenter) ? $mapCenter[0] : '14.435400' }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Longitude</label>
                                        <input type="number" class="form-control" id="manual-lng" step="0.000001" placeholder="{{ isset($mapCenter) ? $mapCenter[1] : '-16.791530' }}">
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

        <div class="col-lg-6 mb-4 map-card-wrapper">
            <div class="card border-info shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-map"></i> 
                        Carte interactive
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="interactive-map"></div>
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
    <div id="form-section" class="row mt-4" style="display: block;">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list"></i> 
                        Informations de l'unité commerciale
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('unites.store') }}" id="unite-form" enctype="multipart/form-data" onsubmit="return validateFormBeforeSubmit(event)">
                        @csrf
                        
                        <!-- Coordonnées GPS (cachées) -->
                        <!-- Note: required sera retiré automatiquement si un polygone est dessiné -->
                        <input type="hidden" name="latitude" id="form-latitude" required>
                        <input type="hidden" name="longitude" id="form-longitude" required>
                        <input type="hidden" name="altitude" id="form-altitude">
                        <input type="hidden" name="precision" id="form-precision">
                        
                        <!-- Informations du collecteur (si applicable) -->
                        @if(auth()->user()->role === 'collecteur')
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Collecteur responsable</label>
                                <div class="fw-bold text-primary">{{ auth()->user()->name }}</div>
                                <input type="hidden" name="zone_id" value="{{ auth()->user()->zone_id }}">
                                <input type="hidden" name="collecteur_id" value="{{ auth()->user()->id }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Zone d'affectation</label>
                                <div class="fw-bold text-primary">
                                    {{ auth()->user()->zone ? auth()->user()->zone->nom : 'Toutes zones' }}
                                </div>
                            </div>
                        </div>
                        <hr>
                        @else
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="zone_id" class="form-label">Zone fiscale <span class="text-danger">*</span></label>
                                <select class="form-select @error('zone_id') is-invalid @enderror" 
                                        id="zone_id" 
                                        name="zone_id" 
                                        required>
                                    <option value="">Sélectionnez une zone</option>
                                    @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>
                                            {{ $zone->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('zone_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <hr>
                        @endif

                        <!-- Section 1: Informations de base -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-info-circle"></i> Informations de base
                                </h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="categorie" class="form-label">Catégorie d'Unité Commerciale <span class="text-danger">*</span></label>
                                <select class="form-select @error('categorie') is-invalid @enderror" 
                                        id="categorie" 
                                        name="categorie" 
                                        required>
                                    <option value="">Sélectionnez une catégorie</option>
                                    <option value="activite_commerciale" {{ old('categorie') == 'activite_commerciale' ? 'selected' : '' }}>
                                        Activités Commerciales
                                    </option>
                                    <option value="point_interet" {{ old('categorie') == 'point_interet' ? 'selected' : '' }}>
                                        Points d'Intérêt Commerciaux
                                    </option>
                                </select>
                                @error('categorie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sélecteurs synchronisés Nature / Type de commerce -->
                            <div class="col-md-6">
                                <label for="nature" class="form-label">Nature de l'Activité Commerciale ou du Point d'Intérêt</label>
                                <select id="nature" name="nature" class="form-select" required>
                                    <option value="">Sélectionner la nature</option>
                                    <!-- options générées dynamiquement -->
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="type_commerce" class="form-label">Type de Commerce ou du Point d'Intérêt</label>
                                <select id="type_commerce" name="type_commerce" class="form-select" required style="display: block !important; visibility: visible !important;">
                                    <option value="">Sélectionner le type</option>
                                    <!-- options générées dynamiquement -->
                                </select>
                                <div class="form-text" id="type-commerce-info">
                                    <!-- Info sera affichée ici -->
                                </div>
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
                                <label for="sexe_proprietaire" class="form-label">Sexe</label>
                                <select class="form-select @error('sexe_proprietaire') is-invalid @enderror" 
                                        id="sexe_proprietaire" 
                                        name="sexe_proprietaire">
                                    <option value="">Non spécifié</option>
                                    <option value="masculin" {{ old('sexe_proprietaire') == 'masculin' ? 'selected' : '' }}>Masculin</option>
                                    <option value="feminin" {{ old('sexe_proprietaire') == 'feminin' ? 'selected' : '' }}>Féminin</option>
                                </select>
                                @error('sexe_proprietaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="nin_proprietaire" class="form-label">NIN du propriétaire</label>
                                <input type="text" 
                                       class="form-control @error('nin_proprietaire') is-invalid @enderror" 
                                       id="nin_proprietaire" 
                                       name="nin_proprietaire" 
                                       value="{{ old('nin_proprietaire') }}" 
                                       maxlength="13"
                                       pattern="[0-9]{0,13}"
                                       title="Numéro d'identification nationale (13 chiffres maximum)"
                                       placeholder="Numéro d'identification nationale">
                                @error('nin_proprietaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">13 chiffres maximum</div>
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

                            <div class="col-md-6">
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

                            <div class="col-md-6">
                                <label for="ninea" class="form-label" id="ninea-label">NINEA</label>
                                <input type="text" 
                                       class="form-control @error('ninea') is-invalid @enderror" 
                                       id="ninea" 
                                       name="ninea" 
                                       value="{{ old('ninea') }}" 
                                       placeholder="Numéro d'identification des entreprises">
                                @error('ninea')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text" id="ninea-help">Pour les entreprises</div>
                            </div>
                        </div>

                        <!-- Section : Photo de l'unité commerciale -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-camera"></i> Photo de l'unité commerciale
                                </h6>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Photo obligatoire pour valider le recensement
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="photo" class="form-label">Photo du commerce <span class="text-danger">*</span></label>
                                <input type="file" 
                                       class="form-control @error('photo') is-invalid @enderror" 
                                       id="photo" 
                                       name="photo" 
                                       accept="image/*"
                                       capture="camera"
                                       required>
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Photo de la façade ou de l'intérieur du commerce</div>
                            </div>
                        </div>

                        <!-- Section 3: Localisation -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-map-marker-alt"></i> Localisation
                                </h6>
                            </div>

                            <div class="col-12">
                                <label for="adresse" class="form-label">Adresse complète</label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                          id="adresse" 
                                          name="adresse" 
                                          rows="3" 
                                          placeholder="Adresse détaillée de l'unité commerciale...">{{ old('adresse') }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Section 4: Dessin de polygone pour terrains fonciers -->
                        <div id="foncier-polygon-section" class="row g-4 mb-4" style="display: none;">
                            <div class="col-12">
                                <h6 class="text-warning border-bottom pb-2">
                                    <i class="fas fa-draw-polygon"></i> Dessin de la parcelle (Polygone)
                                </h6>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Instructions :</strong> Pour les terrains et bâtiments, vous pouvez tracer la forme de la parcelle directement sur la carte en cliquant sur les 4 coins (ou plus). 
                                    La superficie sera calculée automatiquement.
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">
                                            <i class="fas fa-map"></i> Carte de dessin
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="polygon-drawing-map" style="height: 400px; width: 100%;"></div>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button type="button" class="btn btn-sm btn-success" id="start-drawing-btn">
                                                <i class="fas fa-pencil-alt"></i> Commencer le dessin
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" id="clear-polygon-btn" style="display: none;">
                                                <i class="fas fa-trash"></i> Effacer
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" id="finish-drawing-btn" style="display: none;">
                                                <i class="fas fa-check"></i> Terminer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-ruler-combined"></i> Informations de la parcelle
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="superficie" class="form-label">Superficie (m²)</label>
                                            <input type="number" 
                                                   class="form-control @error('superficie') is-invalid @enderror" 
                                                   id="superficie" 
                                                   name="superficie" 
                                                   value="{{ old('superficie') }}" 
                                                   readonly
                                                   step="0.01">
                                            @error('superficie')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Calculée automatiquement à partir du polygone tracé</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Nombre de points</label>
                                            <div class="form-control" id="polygon-points-count" readonly>0</div>
                                            <div class="form-text">Nombre de sommets du polygone</div>
                                        </div>

                                        <div class="alert alert-success" id="polygon-success-alert" style="display: none;">
                                            <i class="fas fa-check-circle"></i> 
                                            <strong>Polygone créé avec succès !</strong>
                                            <div id="polygon-info" class="mt-2"></div>
                                        </div>

                                        <!-- Champ caché pour stocker la géométrie du polygone -->
                                        <input type="hidden" id="geometrie_polygon" name="geometrie_polygon" value="{{ old('geometrie_polygon') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section supprimée : Type et Nature du Commerce -->
                        <!-- Cette section a été supprimée car elle faisait doublon avec le champ "Type de Commerce ou du Point d'Intérêt" -->

                        <!-- Boutons d'action -->


                        <!-- Bouton de soumission du formulaire -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg" id="submit-btn">
                                    <i class="fas fa-save"></i> 
                                    Enregistrer l'unité commerciale
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS et JS pour Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin="" />

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<!-- Leaflet.draw pour le dessin de polygones -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

<script>
// Centre de carte = commune de l'utilisateur (Thiès, Sandiara, etc.)
window.UNITE_MAP_CENTER = @json(isset($mapCenter) ? $mapCenter : [14.43540, -16.79153]);
window.UNITE_COMMUNE_NOM = @json(isset($commune) ? $commune->nom : 'Sandiara');

// Variables globales
let interactiveMap = null;
let currentMarker = null;
let currentPosition = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🏢 SICOSA - Module de recensement chargé');
    
    // S'assurer que le formulaire est visible par défaut
    document.getElementById('form-section').style.display = 'block';
    
    // Initialisation
    initializeMap();
    setupEventListeners();
    setupFormLogic();
    
    function initializeMap() {
        try {
            const defaultLat = window.UNITE_MAP_CENTER[0];
            const defaultLng = window.UNITE_MAP_CENTER[1];

            interactiveMap = L.map('interactive-map').setView([defaultLat, defaultLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
                minZoom: 2
            }).addTo(interactiveMap);

            const popup = L.popup()
                .setLatLng([defaultLat, defaultLng])
                .setContent('<strong>🗺️ Carte initialisée</strong><br>Cliquez sur "Capturer ma position GPS" pour commencer')
                .openOn(interactiveMap);
            
            console.log('✅ Carte interactive initialisée');
            
            // Forcer le redimensionnement de la carte après un court délai
            setTimeout(() => {
                interactiveMap.invalidateSize();
            }, 250);
            
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation de la carte:', error);
            showStatus('error', 'Erreur lors du chargement de la carte');
        }
    }
    
    function setupEventListeners() {
        // GPS
        document.getElementById('capture-gps-btn').addEventListener('click', captureGPS);
        document.getElementById('test-gps-btn').addEventListener('click', testGeolocation);
        document.getElementById('manual-position-btn').addEventListener('click', showManualInput);
        document.getElementById('apply-manual-btn').addEventListener('click', applyManualPosition);
        document.getElementById('cancel-manual-btn').addEventListener('click', hideManualInput);
        document.getElementById('default-position-btn').addEventListener('click', useDefaultPosition);
        
        // Navigation entre sections
        document.getElementById('continue-to-form-btn').addEventListener('click', showFormSection);
        document.getElementById('back-to-location-btn').addEventListener('click', showLocationSection);
        
        console.log('✅ Event listeners configurés');
    }
    
    function setupFormLogic() {
        // Afficher/masquer la section photos selon la catégorie
        const categorieSelect = document.getElementById('categorie');
        const photosSection = document.getElementById('photos-section');
        
        categorieSelect.addEventListener('change', function() {
            if (this.value === 'point_interet') {
                photosSection.style.display = 'block';
            } else {
                photosSection.style.display = 'none';
            }
        });
        
        // Restriction des champs numériques
        setupNumericFields();
        
        // Gestion intelligente du champ NINEA/Immatriculation
        setupSmartNineaField();

        // Synchronisation Nature / Type de commerce
        // Cette logique est maintenant gérée dans DOMContentLoaded
        console.log('✅ Logique du formulaire configurée');
    }
    
    function setupNumericFields() {
        // Champ NIN : seulement des chiffres, max 13
        const ninField = document.getElementById('nin_proprietaire');
        if (ninField) {
            ninField.addEventListener('input', function(e) {
                // Supprimer tous les caractères non numériques
                this.value = this.value.replace(/\D/g, '');
                // Limiter à 13 chiffres
                if (this.value.length > 13) {
                    this.value = this.value.slice(0, 13);
                }
            });
            
            ninField.addEventListener('keypress', function(e) {
                // Empêcher la saisie de caractères non numériques
                if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                    e.preventDefault();
                }
            });
        }
        
        // Champ téléphone : seulement des chiffres, max 12
        const phoneField = document.getElementById('telephone');
        if (phoneField) {
            phoneField.addEventListener('input', function(e) {
                // Supprimer tous les caractères non numériques
                this.value = this.value.replace(/\D/g, '');
                // Limiter à 12 chiffres
                if (this.value.length > 12) {
                    this.value = this.value.slice(0, 12);
                }
            });
            
            phoneField.addEventListener('keypress', function(e) {
                // Empêcher la saisie de caractères non numériques
                if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                    e.preventDefault();
                }
            });
        }
        
        console.log('✅ Restrictions numériques configurées');
    }
    
    function setupSmartNineaField() {
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
            let taxeName = '';
            if (taxeSelect && taxeSelect.selectedIndex > 0) {
                taxeName = taxeSelect.options[taxeSelect.selectedIndex].text.toLowerCase();
            }
            
            // Mots-clés de transport étendus
            const transportKeywords = [
                'transport', 'taxi', 'clado', 'moto', 'jakarta', 'bus', 'car', 
                'véhicule', 'auto', 'voiture', 'camion', 'truck', 'tricycle',
                'scooter', 'motocycle', 'automobile', 'minibus', 'microbus'
            ];
            
            // Vérifier si c'est du transport (type, nature OU taxe)
            const isTransport = transportKeywords.some(keyword => 
                typeCommerce.includes(keyword) || 
                nature.includes(keyword) || 
                taxeName.includes(keyword)
            );
            
            if (isTransport) {
                nineaLabel.innerHTML = '<i class="fas fa-car me-1 text-info"></i>Numéro de matricule';
                nineaField.placeholder = 'Ex: DK-1234-AB ou SN-5678-CD';
                nineaHelp.innerHTML = '<i class="fas fa-info-circle me-1 text-info"></i>Numéro d\'immatriculation du véhicule';
                nineaField.style.borderColor = '#17a2b8';
                nineaField.style.backgroundColor = '#f0f9ff';
                
                // Message d'aide contextuel
                if (taxeName.includes('taxi')) {
                    nineaHelp.innerHTML = '<i class="fas fa-taxi me-1 text-warning"></i>Immatriculation du taxi';
                } else if (taxeName.includes('moto') || taxeName.includes('jakarta')) {
                    nineaHelp.innerHTML = '<i class="fas fa-motorcycle me-1 text-warning"></i>Immatriculation de la moto';
                } else if (taxeName.includes('bus')) {
                    nineaHelp.innerHTML = '<i class="fas fa-bus me-1 text-warning"></i>Immatriculation du bus';
                }
            } else {
                nineaLabel.innerHTML = '<i class="fas fa-building me-1 text-primary"></i>NINEA';
                nineaField.placeholder = 'Numéro d\'identification des entreprises';
                nineaHelp.innerHTML = '<i class="fas fa-info-circle me-1 text-primary"></i>Pour les entreprises';
                nineaField.style.borderColor = '';
                nineaField.style.backgroundColor = '';
            }
        }
        
        // Écouter les changements
        if (typeCommerceSelect) {
            typeCommerceSelect.addEventListener('change', updateNineaField);
        }
        if (natureField) {
            natureField.addEventListener('input', updateNineaField);
        }
        if (taxeSelect) {
            taxeSelect.addEventListener('change', updateNineaField);
        }
        
        // Initialiser
        updateNineaField();
        
        console.log('✅ Champ NINEA intelligent configuré avec détection des taxes de transport');
    }
    
    function captureGPS() {
        if (!navigator.geolocation) {
            showStatus('error', '❌ Géolocalisation non supportée par ce navigateur');
            document.getElementById('manual-position-btn').style.display = 'block';
            return;
        }

        const btn = document.getElementById('capture-gps-btn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Capture en cours...';
        btn.disabled = true;
        
        document.getElementById('manual-position-btn').style.display = 'block';
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
                
                // Mettre à jour le popup
                currentMarker.setPopupContent(`
                    <strong>📍 Position ajustée</strong><br>
                    <small>Méthode: ${currentPosition.method}</small><br>
                    <small>Lat: ${currentPosition.lat.toFixed(6)}</small><br>
                    <small>Lng: ${currentPosition.lng.toFixed(6)}</small>
                `);
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
        
        // Faire défiler vers les boutons après un délai
        setTimeout(() => {
            const actionButtons = document.querySelector('.card.border-primary');
            if (actionButtons) {
                actionButtons.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }, 1000);
        
        console.log('✅ Passage au formulaire avec position:', currentPosition);
    }
    
    function showLocationSection() {
        document.getElementById('geolocation-section').style.display = 'block';
        document.getElementById('form-section').style.display = 'none';
        
        // Faire défiler vers la géolocalisation
        document.getElementById('geolocation-section').scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
        
        // Redimensionner la carte
        setTimeout(() => {
            if (interactiveMap) {
                interactiveMap.invalidateSize();
            }
        }, 300);
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

    function useDefaultPosition() {
        const defaultLat = window.UNITE_MAP_CENTER[0];
        const defaultLng = window.UNITE_MAP_CENTER[1];
        currentPosition = {
            lat: defaultLat,
            lng: defaultLng,
            accuracy: 0,
            altitude: null,
            method: 'Position ' + window.UNITE_COMMUNE_NOM
        };
        handlePositionSuccess();
        showStatus('info', '✅ Position par défaut (' + window.UNITE_COMMUNE_NOM + ') utilisée.');
    }
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

/* Correction pour le champ Type de Commerce */
#type_commerce {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 1 !important;
}

#type_commerce:disabled {
    background-color: #f8f9fa;
    opacity: 0.6;
}

/* Forcer l'affichage même quand le champ est activé */
#type_commerce:not(:disabled) {
    background-color: white !important;
    opacity: 1 !important;
    display: block !important;
    visibility: visible !important;
}

/* Animation pour les changements de contenu */
#type_commerce option {
    transition: all 0.2s ease;
}

/* Indicateur visuel pour les champs actifs */
.form-select.filter-selected {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

/* Correction pour le bouton d'enregistrement */
#submit-btn {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 9999 !important;
    background: linear-gradient(135deg, #28a745, #20c997) !important;
    color: white !important;
    border: 3px solid #155724 !important;
    font-weight: 700 !important;
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.6) !important;
    font-size: 18px !important;
    padding: 15px 30px !important;
}

#submit-btn:hover {
    background: linear-gradient(135deg, #218838, #1ea085) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.8) !important;
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

@push('scripts')
<script>
// --- Synchronisation Nature / Type de commerce ---
const data = {
  "Boutique ou Magasin": ["Alimentation générale", "Grossiste", "Mercerie", "Quincaillerie", "Prêt-à-porter", "Cosmétique"],
  "Atelier": ["Mécanique", "Couture", "Forge", "Cordonnerie", "Menuiserie", "Boulangerie"],
  "Forail": ["Chevaux", "Ânes", "Vaches", "Moutons", "Chèvres"],
  "Parcelle agricole": ["Petites exploitations agricoles (celui des particuliers ou l'agriculture familiale)"],
  "Stationnement": ["Bus", "Camion-citerne de carburant", "Camionnette", "Calèche", "Camion gros porteur", "Taxi clando", "Moto Jakarta", "Car"],
  "Étal": ["Vente sur étal (sans distinction spécifique)"],
  "Table ou Tente commerciale": ["Vente sur table ou sous tente (sans distinction spécifique)"],
  "Cantine ou Kiosque": ["Restaurant", "Gargote", "Dibiterie", "Fast-Food"],
  "Entreprise ou Service": ["Entrepôt", "Grande exploitation agricole (celui des entreprises)", "Assurance", "Banque", "Multiservice", "Supermarché", "Station-service"],
  "Foncier": ["Location de terrain", "Location de bâtiments"],
  "Publicité": ["Véhicules publicitaires", "Panneaux publicitaires"],
  "Spectacles": ["Lieux dédiés aux événements et spectacles"]
};

const natureCategorieMap = {
  "activite_commerciale": [
    "Boutique ou Magasin", "Atelier", "Forail", "Parcelle agricole", "Stationnement", "Étal", "Table ou Tente commerciale", "Cantine ou Kiosque", "Entreprise ou Service"
  ],
  "point_interet": [
    "Foncier", "Publicité", "Spectacles"
  ]
};
let typeToNature = {};
Object.entries(data).forEach(([nature, types]) => {
  types.forEach(type => {
    typeToNature[type] = nature;
  });
});
function populateNature(selectedCat) {
  console.log('🔄 populateNature appelée avec:', selectedCat);
  const natureSelect = document.getElementById('nature');
  if (!natureSelect) {
    console.error('❌ Champ nature non trouvé dans populateNature');
    return;
  }
  
  natureSelect.innerHTML = '<option value="">Sélectionner la nature</option>';
  let natures = Object.keys(data);
  
  if (selectedCat && natureCategorieMap[selectedCat]) {
    natures = natureCategorieMap[selectedCat];
    console.log('📋 Natures filtrées pour', selectedCat, ':', natures);
  } else {
    console.log('📋 Toutes les natures:', natures);
  }
  
  natures.forEach(nature => {
    const opt = document.createElement('option');
    opt.value = nature;
    opt.textContent = nature;
    natureSelect.appendChild(opt);
  });
  
  console.log('✅ populateNature terminée,', natures.length, 'options ajoutées');
}
function populateTypeByNature(natureValue) {
  console.log('🔄 populateTypeByNature appelée avec:', natureValue);
  const typeSelect = document.getElementById('type_commerce');
  if (!typeSelect) {
    console.error('❌ Champ type_commerce non trouvé dans populateTypeByNature');
    return;
  }
  
  // Réinitialise la valeur sélectionnée
  typeSelect.value = '';
  typeSelect.innerHTML = '';
  
  // Forcer l'affichage du champ
  typeSelect.style.display = 'block';
  typeSelect.style.visibility = 'visible';
  typeSelect.style.opacity = '1';
  
  if (natureValue && data[natureValue] && data[natureValue].length > 0) {
    console.log('📋 Types trouvés pour', natureValue, ':', data[natureValue]);
    
    const defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.textContent = 'Sélectionner le type de commerce';
    typeSelect.appendChild(defaultOpt);
    
    data[natureValue].forEach(type => {
      const opt = document.createElement('option');
      opt.value = type;
      opt.textContent = type;
      typeSelect.appendChild(opt);
      console.log('✅ Option ajoutée:', type);
    });
    
    typeSelect.required = true;
    typeSelect.disabled = false;
    
    // Forcer le re-rendu du champ
    typeSelect.style.display = 'none';
    typeSelect.offsetHeight; // Trigger reflow
    typeSelect.style.display = 'block';
    
    console.log('✅ populateTypeByNature terminée,', data[natureValue].length, 'options ajoutées');
  } else {
    console.log('⚠️ Aucun type trouvé pour:', natureValue);
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = 'Sélectionner d\'abord une nature';
    typeSelect.appendChild(opt);
    typeSelect.required = false;
    typeSelect.disabled = true;
  }
  
  // Toujours s'assurer que le champ est visible et fonctionnel
  typeSelect.style.display = 'block';
  typeSelect.style.visibility = 'visible';
  typeSelect.style.opacity = '1';
  
        // Vérifier si la section polygone doit être affichée après le peuplement
        setTimeout(function() {
            if (typeof window.togglePolygonSection === 'function') {
                window.togglePolygonSection();
            } else if (typeof togglePolygonSection === 'function') {
                togglePolygonSection();
            }
        }, 200);
}
function selectNatureByType(typeValue) {
  console.log('🔄 selectNatureByType appelée avec:', typeValue);
  const natureSelect = document.getElementById('nature');
  if (!natureSelect) {
    console.error('❌ Champ nature non trouvé dans selectNatureByType');
    return;
  }
  
  if (typeValue && typeToNature[typeValue]) {
    console.log('🔄 Sélection de la nature:', typeToNature[typeValue]);
    natureSelect.value = typeToNature[typeValue];
    populateTypeByNature(typeToNature[typeValue]);
  }
}
// Initialisation avec DOMContentLoaded pour s'assurer que le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM chargé, initialisation du formulaire...');
    
    const categorieSelect = document.getElementById('categorie');
    const natureSelect = document.getElementById('nature');
    const typeSelect = document.getElementById('type_commerce');
    
    if (!categorieSelect) {
        console.error('❌ Champ catégorie non trouvé');
        return;
    } else {
        console.log('✅ Champ catégorie trouvé');
    }
    
    if (!natureSelect) {
        console.error('❌ Champ nature non trouvé');
        return;
    } else {
        console.log('✅ Champ nature trouvé');
    }
    
    if (!typeSelect) {
        console.error('❌ Champ type commerce non trouvé');
        return;
    } else {
        console.log('✅ Champ type commerce trouvé');
    }
    
    if (typeof populateNature !== 'function') {
        console.error('❌ Fonction populateNature non trouvée');
        return;
    } else {
        console.log('✅ Fonction populateNature trouvée');
    }
    
    // Ajouter les événements
    categorieSelect.addEventListener('change', function() {
        console.log('🔄 Catégorie changée:', this.value);
        populateNature(this.value);
        // Réinitialiser la nature et le type de commerce
        natureSelect.value = '';
        populateTypeByNature('');
        
        // Vérifier la section polygone après le changement de catégorie
        setTimeout(function() {
            if (typeof window.togglePolygonSection === 'function') {
                window.togglePolygonSection();
            } else if (typeof togglePolygonSection === 'function') {
                togglePolygonSection();
            }
        }, 400);
    });
    
    natureSelect.addEventListener('change', function() {
        console.log('🔄 Nature changée:', this.value);
        populateTypeByNature(this.value);
        
        // Ajouter une classe CSS pour indiquer l'état
        if (this.value) {
            this.classList.add('filter-selected');
            
            // Activer le champ type_commerce quand une nature est sélectionnée
            typeSelect.disabled = false;
            typeSelect.style.display = 'block';
            typeSelect.style.visibility = 'visible';
            typeSelect.style.opacity = '1';
        } else {
            this.classList.remove('filter-selected');
            
            // Désactiver le champ type_commerce quand aucune nature n'est sélectionnée
            typeSelect.disabled = true;
        }
        
        // Afficher un message d'information
        const infoDiv = document.getElementById('type-commerce-info');
        if (infoDiv) {
            if (this.value) {
                infoDiv.innerHTML = `<i class="fas fa-info-circle text-info"></i> Types disponibles pour: ${this.value}`;
            } else {
                infoDiv.innerHTML = '';
            }
        }
        
        // Vérifier la section polygone après le changement de nature
        setTimeout(function() {
            if (typeof window.togglePolygonSection === 'function') {
                window.togglePolygonSection();
            } else if (typeof togglePolygonSection === 'function') {
                togglePolygonSection();
            }
        }, 400);
    });
    
    typeSelect.addEventListener('change', function() {
        console.log('🔄 Type commerce changé:', this.value);
        
        // S'assurer que le champ reste activé quand une valeur est sélectionnée
        if (this.value && this.value !== '') {
            this.disabled = false;
            this.classList.add('filter-selected');
            
            // Afficher un message d'information
            const infoDiv = document.getElementById('type-commerce-info');
            if (infoDiv) {
                infoDiv.innerHTML = `<i class="fas fa-check-circle text-success"></i> Type sélectionné: ${this.value}`;
            }
        } else {
            this.classList.remove('filter-selected');
            
            // Afficher un message d'information
            const infoDiv = document.getElementById('type-commerce-info');
            if (infoDiv) {
                infoDiv.innerHTML = '';
            }
        }
        
        // Vérifier la section polygone après le changement de type
        setTimeout(function() {
            if (typeof window.togglePolygonSection === 'function') {
                window.togglePolygonSection();
            } else if (typeof togglePolygonSection === 'function') {
                togglePolygonSection();
            }
        }, 200);
        
        // Ne pas appeler selectNatureByType pour éviter les conflits
        // selectNatureByType(this.value);
    });
    
    // Initialisation avec la valeur par défaut
    const selectedCat = categorieSelect.value;
    console.log('🔄 Initialisation avec catégorie:', selectedCat);
    populateNature(selectedCat);
    
    // S'assurer que le champ type_commerce est visible et fonctionnel
    typeSelect.style.display = 'block';
    typeSelect.style.visibility = 'visible';
    typeSelect.style.opacity = '1';
    
    if (natureSelect.value) {
        console.log('🔄 Initialisation avec nature:', natureSelect.value);
        populateTypeByNature(natureSelect.value);
    } else {
        // Initialiser avec un message par défaut
        populateTypeByNature('');
    }
    
    // Vérifier la section polygone après l'initialisation complète
    setTimeout(function() {
        // La fonction togglePolygonSection sera définie plus tard dans le code
        // On utilise window pour s'assurer qu'elle soit accessible
        if (typeof window.togglePolygonSection === 'function') {
            window.togglePolygonSection();
        } else if (typeof togglePolygonSection === 'function') {
            togglePolygonSection();
        } else {
            console.log('⏳ Fonction togglePolygonSection pas encore disponible, sera appelée plus tard');
        }
    }, 1500);
    
    console.log('✅ Initialisation terminée');
    
    // Fonction de débogage pour diagnostiquer les problèmes
    window.debugCommerceFields = function() {
        console.log('🔍 === DIAGNOSTIC DES CHAMPS COMMERCE ===');
        console.log('Catégorie:', categorieSelect.value, categorieSelect);
        console.log('Nature:', natureSelect.value, natureSelect);
        console.log('Type Commerce:', typeSelect.value, typeSelect);
        console.log('Type Commerce options:', typeSelect.options.length);
        console.log('Type Commerce disabled:', typeSelect.disabled);
        console.log('Type Commerce style:', {
            display: typeSelect.style.display,
            visibility: typeSelect.style.visibility,
            opacity: typeSelect.style.opacity
        });
        
        // Tester la fonction populateTypeByNature
        if (natureSelect.value) {
            console.log('🧪 Test populateTypeByNature avec:', natureSelect.value);
            populateTypeByNature(natureSelect.value);
        }
    };
    
    // Exposer la fonction globalement pour le débogage
    window.debugCommerceFields();
    
    // Fonction de test pour vérifier le fonctionnement
    window.testCommerceFields = function() {
        console.log('🧪 === TEST DES CHAMPS COMMERCE ===');
        
        // Test 1: Sélectionner une catégorie
        console.log('Test 1: Sélection de catégorie "activite_commerciale"');
        categorieSelect.value = 'activite_commerciale';
        categorieSelect.dispatchEvent(new Event('change'));
        
        setTimeout(() => {
            console.log('Nature options après catégorie:', natureSelect.options.length);
            
            // Test 2: Sélectionner une nature
            if (natureSelect.options.length > 1) {
                console.log('Test 2: Sélection de nature "Atelier"');
                natureSelect.value = 'Atelier';
                natureSelect.dispatchEvent(new Event('change'));
                
                setTimeout(() => {
                    console.log('Type options après nature:', typeSelect.options.length);
                    console.log('Type disabled:', typeSelect.disabled);
                    
                    // Test 3: Sélectionner un type
                    if (typeSelect.options.length > 1) {
                        console.log('Test 3: Sélection de type "Mécanique"');
                        typeSelect.value = 'Mécanique';
                        typeSelect.dispatchEvent(new Event('change'));
                        
                        setTimeout(() => {
                            console.log('✅ Test terminé - Vérifiez les valeurs:');
                            console.log('Catégorie:', categorieSelect.value);
                            console.log('Nature:', natureSelect.value);
                            console.log('Type:', typeSelect.value);
                            console.log('Type disabled:', typeSelect.disabled);
                            console.log('Type visible:', typeSelect.style.display);
                        }, 500);
                    }
                }, 500);
            }
        }, 500);
    };
    
    console.log('🔧 Fonctions de débogage disponibles:');
    console.log('- debugCommerceFields() : Diagnostic des champs');
    console.log('- testCommerceFields() : Test automatique du fonctionnement');
    
        // === Gestion du dessin de polygone pour les terrains fonciers ===
        let polygonDrawingMap = null;
        let drawnPolygon = null;
        let drawControl = null;
        let isDrawing = false;

        // Initialiser la carte de dessin de polygone
        function initPolygonDrawingMap() {
            if (polygonDrawingMap) {
                return; // Déjà initialisée
            }

            const defaultLat = currentPosition ? currentPosition.lat : window.UNITE_MAP_CENTER[0];
            const defaultLng = currentPosition ? currentPosition.lng : window.UNITE_MAP_CENTER[1];

            polygonDrawingMap = L.map('polygon-drawing-map').setView([defaultLat, defaultLng], 18);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
                minZoom: 2
            }).addTo(polygonDrawingMap);

            // Ajouter un marqueur à la position actuelle si disponible
            if (currentPosition) {
                L.marker([currentPosition.lat, currentPosition.lng], {
                    icon: L.divIcon({
                        className: 'custom-red-marker',
                        html: '<div style="background-color: #dc3545; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 3px 6px rgba(0,0,0,0.3);"></div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    })
                }).addTo(polygonDrawingMap).bindPopup('Position de l\'unité');
            }

            // Configuration de Leaflet.draw
            const drawOptions = {
                position: 'topright',
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        drawError: {
                            color: '#e1e100',
                            message: '<strong>Erreur :</strong> Le polygone ne peut pas se croiser !'
                        },
                        shapeOptions: {
                            color: '#97009c'
                        }
                    },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false
                },
                edit: {
                    featureGroup: new L.FeatureGroup(),
                    remove: true
                }
            };

            drawControl = new L.Control.Draw(drawOptions);
            polygonDrawingMap.addControl(drawControl);

            // Événements de dessin
            polygonDrawingMap.on(L.Draw.Event.CREATED, function(e) {
                const layer = e.layer;
                const type = e.layerType;

                if (type === 'polygon') {
                    // Supprimer l'ancien polygone s'il existe
                    if (drawnPolygon) {
                        polygonDrawingMap.removeLayer(drawnPolygon);
                    }

                    drawnPolygon = layer;
                    polygonDrawingMap.addLayer(drawnPolygon);

                    // Calculer la superficie en m² (formule de Shoelace adaptée pour les coordonnées géographiques)
                    const latlngs = drawnPolygon.getLatLngs()[0];
                    let areaM2 = 0;
                    
                    if (latlngs.length > 2) {
                        // Calculer la latitude moyenne pour la conversion longitude -> mètres
                        const centerLat = latlngs.reduce((sum, ll) => sum + ll.lat, 0) / latlngs.length;
                        const toRad = (deg) => deg * (Math.PI / 180);
                        
                        // Facteurs de conversion (mètres par degré)
                        // 1 degré de latitude ≈ 111320 m (constant)
                        // 1 degré de longitude ≈ 111320 * cos(latitude) m (varie selon la latitude)
                        const latMeter = 111320;
                        const lngMeter = 111320 * Math.cos(toRad(centerLat));
                        
                        // Formule de Shoelace (Gauss area formula) en coordonnées projetées
                        let sum = 0;
                        for (let i = 0; i < latlngs.length; i++) {
                            const j = (i + 1) % latlngs.length;
                            // Convertir les coordonnées géographiques en mètres
                            const xi = latlngs[i].lng * lngMeter;
                            const yi = latlngs[i].lat * latMeter;
                            const xj = latlngs[j].lng * lngMeter;
                            const yj = latlngs[j].lat * latMeter;
                            // Formule de Shoelace
                            sum += xi * yj - xj * yi;
                        }
                        areaM2 = Math.abs(sum) / 2;
                    }

                    // Mettre à jour les champs
                    document.getElementById('superficie').value = areaM2.toFixed(2);
                    document.getElementById('polygon-points-count').textContent = drawnPolygon.getLatLngs()[0].length;

                    // Créer la géométrie GeoJSON
                    const geoJson = drawnPolygon.toGeoJSON();
                    document.getElementById('geometrie_polygon').value = JSON.stringify(geoJson);

                    // Calculer le centre du polygone et remplir automatiquement latitude/longitude
                    const bounds = drawnPolygon.getBounds();
                    const center = bounds.getCenter();
                    const centerLat = center.lat;
                    const centerLng = center.lng;
                    
                    // Remplir les champs latitude/longitude avec le centre du polygone
                    const latInput = document.getElementById('form-latitude');
                    const lngInput = document.getElementById('form-longitude');
                    
                    if (latInput && lngInput) {
                        latInput.value = centerLat.toFixed(6);
                        lngInput.value = centerLng.toFixed(6);
                        
                        // Retirer l'attribut required car on a maintenant un polygone
                        latInput.removeAttribute('required');
                        lngInput.removeAttribute('required');
                        
                        // Mettre à jour l'affichage de la position si visible
                        const currentLatDisplay = document.getElementById('current-latitude');
                        const currentLngDisplay = document.getElementById('current-longitude');
                        if (currentLatDisplay) currentLatDisplay.textContent = centerLat.toFixed(6);
                        if (currentLngDisplay) currentLngDisplay.textContent = centerLng.toFixed(6);
                        
                        // Mettre à jour currentPosition pour la carte principale
                        if (typeof currentPosition !== 'undefined') {
                            currentPosition = { lat: centerLat, lng: centerLng };
                        }
                        
                        console.log('✅ Position GPS automatiquement définie depuis le centre du polygone:', centerLat, centerLng);
                    }

                    // Afficher les informations
                    document.getElementById('polygon-success-alert').style.display = 'block';
                    document.getElementById('polygon-info').innerHTML = `
                        <div>Superficie: <strong>${areaM2.toFixed(2)} m²</strong></div>
                        <div>Points: <strong>${drawnPolygon.getLatLngs()[0].length}</strong></div>
                        <div class="mt-2"><small class="text-success"><i class="fas fa-check-circle"></i> Position GPS automatiquement définie depuis le centre du polygone</small></div>
                    `;

                    // Afficher les boutons
                    document.getElementById('clear-polygon-btn').style.display = 'inline-block';
                    document.getElementById('finish-drawing-btn').style.display = 'inline-block';
                    document.getElementById('start-drawing-btn').style.display = 'none';

                    // Désactiver le dessin
                    drawControl._toolbars.draw._modes.polygon.handler.disable();
                    isDrawing = false;
                }
            });

            polygonDrawingMap.on(L.Draw.Event.DRAWSTART, function() {
                isDrawing = true;
            });

            polygonDrawingMap.on(L.Draw.Event.DRAWSTOP, function() {
                isDrawing = false;
            });

            // Redimensionner la carte après un court délai
            setTimeout(() => {
                polygonDrawingMap.invalidateSize();
            }, 250);
        }

        // Gérer l'affichage de la section polygone
        // Définir la fonction globalement pour qu'elle soit accessible partout
        window.togglePolygonSection = function() {
            const natureSelect = document.getElementById('nature');
            const typeSelect = document.getElementById('type_commerce');
            const polygonSection = document.getElementById('foncier-polygon-section');
            const categorieSelect = document.getElementById('categorie');

            console.log('🔍 togglePolygonSection appelée');
            console.log('  - natureSelect:', natureSelect ? 'trouvé' : 'non trouvé');
            console.log('  - typeSelect:', typeSelect ? 'trouvé' : 'non trouvé');
            console.log('  - polygonSection:', polygonSection ? 'trouvé' : 'non trouvé');

            if (!natureSelect || !typeSelect || !polygonSection) {
                console.error('❌ Éléments manquants pour togglePolygonSection');
                return;
            }

            const nature = natureSelect.value;
            const type = typeSelect.value;
            const categorie = categorieSelect ? categorieSelect.value : '';

            console.log('  - Catégorie:', categorie);
            console.log('  - Nature:', nature);
            console.log('  - Type:', type);

            // Afficher la section pour "point_interet" > "Foncier" > "Location de terrain" OU "Location de bâtiments"
            const isFoncierType = type === 'Location de terrain' || type === 'Location de bâtiments';
            if (categorie === 'point_interet' && nature === 'Foncier' && isFoncierType) {
                console.log('✅ Affichage de la section polygone pour:', type);
                polygonSection.style.display = 'block';
                
                // Initialiser la carte si elle n'existe pas encore
                if (!polygonDrawingMap) {
                    console.log('🗺️ Initialisation de la carte de dessin...');
                    setTimeout(() => {
                        initPolygonDrawingMap();
                    }, 100);
                } else {
                    console.log('🗺️ Réajustement de la taille de la carte...');
                    setTimeout(() => {
                        polygonDrawingMap.invalidateSize();
                    }, 100);
                }
            } else {
                console.log('❌ Masquage de la section polygone');
                polygonSection.style.display = 'none';
                
                // Réinitialiser les champs seulement si la section existe
                const superficieInput = document.getElementById('superficie');
                const geometrieInput = document.getElementById('geometrie_polygon');
                const pointsCount = document.getElementById('polygon-points-count');
                const successAlert = document.getElementById('polygon-success-alert');
                
                if (superficieInput) superficieInput.value = '';
                if (geometrieInput) geometrieInput.value = '';
                if (pointsCount) pointsCount.textContent = '0';
                if (successAlert) successAlert.style.display = 'none';
                
                // Supprimer le polygone de la carte
                if (drawnPolygon && polygonDrawingMap) {
                    polygonDrawingMap.removeLayer(drawnPolygon);
                    drawnPolygon = null;
                }
            }
        };
        
        // Alias local pour compatibilité
        const togglePolygonSection = window.togglePolygonSection;

        // Fonction de validation avant soumission du formulaire
        window.validateFormBeforeSubmit = function(event) {
            const latInput = document.getElementById('form-latitude');
            const lngInput = document.getElementById('form-longitude');
            const geometrieInput = document.getElementById('geometrie_polygon');
            const nomProprietaireInput = document.getElementById('nom_proprietaire');
            
            let errors = [];
            
            // Vérifier nom_proprietaire
            if (!nomProprietaireInput || !nomProprietaireInput.value || nomProprietaireInput.value.trim() === '') {
                errors.push('nom_proprietaire');
            }
            
            // Vérifier latitude/longitude OU polygone
            const hasPolygon = geometrieInput && geometrieInput.value && geometrieInput.value.trim() !== '';
            const hasLatLng = latInput && latInput.value && lngInput && lngInput.value;
            
            if (!hasPolygon && !hasLatLng) {
                errors.push('latitude');
                errors.push('longitude');
            }
            
            if (errors.length > 0) {
                event.preventDefault();
                const errorMessage = 'Veuillez remplir tous les champs obligatoires: ' + errors.join(', ');
                alert(errorMessage);
                
                // Mettre en évidence les champs manquants
                if (errors.includes('nom_proprietaire') && nomProprietaireInput) {
                    nomProprietaireInput.focus();
                    nomProprietaireInput.style.borderColor = '#dc3545';
                }
                
                if (errors.includes('latitude') && !hasPolygon) {
                    // Afficher un message pour la capture GPS ou le dessin de polygone
                    const gpsSection = document.getElementById('geolocation-section');
                    const polygonSection = document.getElementById('foncier-polygon-section');
                    if (polygonSection && polygonSection.style.display !== 'none') {
                        polygonSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        alert('⚠️ Vous devez dessiner un polygone sur la carte de dessin.\n\nLe polygone définira automatiquement la position GPS au centre de la parcelle.');
                    } else if (gpsSection) {
                        gpsSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        alert('⚠️ Vous devez soit :\n1. Capturer votre position GPS, OU\n2. Dessiner un polygone sur la carte de dessin\n\nLe polygone définira automatiquement la position GPS au centre de la parcelle.');
                    }
                }
                
                return false;
            }
            
            return true;
        };

        // Boutons de contrôle
        document.addEventListener('DOMContentLoaded', function() {
            // Écouter les changements de nature et type
            const natureSelect = document.getElementById('nature');
            const typeSelect = document.getElementById('type_commerce');

            if (natureSelect) {
                natureSelect.addEventListener('change', function() {
                    setTimeout(togglePolygonSection, 100);
                });
            }

            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    setTimeout(togglePolygonSection, 100);
                });
            }

            // Bouton pour commencer le dessin
            const startDrawingBtn = document.getElementById('start-drawing-btn');
            if (startDrawingBtn) {
                startDrawingBtn.addEventListener('click', function() {
                    if (!polygonDrawingMap) {
                        initPolygonDrawingMap();
                        setTimeout(() => {
                            startDrawing();
                        }, 100);
                    } else {
                        startDrawing();
                    }
                });
            }

            // Bouton pour effacer le polygone
            const clearPolygonBtn = document.getElementById('clear-polygon-btn');
            if (clearPolygonBtn) {
                clearPolygonBtn.addEventListener('click', function() {
                    if (drawnPolygon && polygonDrawingMap) {
                        polygonDrawingMap.removeLayer(drawnPolygon);
                        drawnPolygon = null;
                    }

                    document.getElementById('superficie').value = '';
                    document.getElementById('geometrie_polygon').value = '';
                    document.getElementById('polygon-points-count').textContent = '0';
                    document.getElementById('polygon-success-alert').style.display = 'none';
                    document.getElementById('clear-polygon-btn').style.display = 'none';
                    document.getElementById('finish-drawing-btn').style.display = 'none';
                    document.getElementById('start-drawing-btn').style.display = 'inline-block';
                    
                    // Remettre l'attribut required sur latitude/longitude si le polygone est effacé
                    const latInput = document.getElementById('form-latitude');
                    const lngInput = document.getElementById('form-longitude');
                    if (latInput && !latInput.value) {
                        latInput.setAttribute('required', 'required');
                    }
                    if (lngInput && !lngInput.value) {
                        lngInput.setAttribute('required', 'required');
                    }
                });
            }

            // Bouton pour terminer le dessin
            const finishDrawingBtn = document.getElementById('finish-drawing-btn');
            if (finishDrawingBtn) {
                finishDrawingBtn.addEventListener('click', function() {
                    if (drawControl && isDrawing) {
                        drawControl._toolbars.draw._modes.polygon.handler.disable();
                        isDrawing = false;
                    }
                });
            }

            // Fonction pour commencer le dessin
            function startDrawing() {
                if (!polygonDrawingMap || !drawControl) {
                    console.error('Carte ou contrôle de dessin non initialisé');
                    return;
                }

                // Supprimer l'ancien polygone s'il existe
                if (drawnPolygon) {
                    polygonDrawingMap.removeLayer(drawnPolygon);
                    drawnPolygon = null;
                }

                // Activer le mode dessin
                drawControl._toolbars.draw._modes.polygon.handler.enable();
                isDrawing = true;

                document.getElementById('start-drawing-btn').style.display = 'none';
            }

            // Initialiser l'état au chargement
            setTimeout(togglePolygonSection, 500);
        });

        // Fonction de débogage pour le formulaire
    window.debugForm = function() {
        console.log('🔍 === DIAGNOSTIC DU FORMULAIRE ===');
        const form = document.getElementById('unite-form');
        const submitBtn = document.getElementById('submit-btn');
        
        console.log('Formulaire trouvé:', !!form);
        console.log('Bouton submit trouvé:', !!submitBtn);
        
        if (form) {
            console.log('Action du formulaire:', form.action);
            console.log('Méthode du formulaire:', form.method);
            console.log('Champs requis:', form.querySelectorAll('[required]').length);
        }
        
        if (submitBtn) {
            console.log('Bouton disabled:', submitBtn.disabled);
            console.log('Bouton visible:', submitBtn.style.display);
        }
    };
    
    // Ajouter un événement de débogage au bouton submit
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            console.log('🔄 Tentative de soumission du formulaire...');
            
            // Vérifier les champs requis
            const requiredFields = document.querySelectorAll('[required]');
            let missingFields = [];
            
            requiredFields.forEach(field => {
                if (!field.value || field.value.trim() === '') {
                    missingFields.push(field.name || field.id);
                }
            });
            
            if (missingFields.length > 0) {
                console.log('❌ Champs manquants:', missingFields);
                alert('Veuillez remplir tous les champs obligatoires: ' + missingFields.join(', '));
                e.preventDefault();
                return false;
            }
            
            console.log('✅ Tous les champs requis sont remplis');
            console.log('📤 Soumission du formulaire...');
        });
    }
    
    console.log('- debugForm() : Diagnostic du formulaire');
});
        
        // Fonction pour continuer vers le paiement
        window.continueToPayment = function() {
            // Vérifier que le formulaire est valide
            const form = document.querySelector('form');
            if (!form.checkValidity()) {
                alert('⚠️ Veuillez remplir tous les champs obligatoires avant de continuer vers le paiement.');
                form.reportValidity();
                return;
            }
            
            // Soumettre le formulaire avec un paramètre spécial pour le paiement
            const paymentInput = document.createElement('input');
            paymentInput.type = 'hidden';
            paymentInput.name = 'continue_to_payment';
            paymentInput.value = '1';
            form.appendChild(paymentInput);
            
            // Changer le texte du bouton pendant la soumission
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
            submitBtn.disabled = true;
            
            // Soumettre le formulaire
            form.submit();
        };
        
        // Fonction pour faire défiler vers les boutons d'action
        window.scrollToActions = function() {
            const actionSection = document.getElementById('actions-section');
            if (actionSection) {
                // Ajouter un effet de surbrillance
                actionSection.style.boxShadow = '0 0 30px rgba(220, 53, 69, 0.8)';
                actionSection.style.transform = 'scale(1.02)';
                actionSection.style.transition = 'all 0.3s ease';
                
                // Faire défiler vers la section
                actionSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'center'
                });
                
                // Retirer l'effet après 2 secondes
                setTimeout(() => {
                    actionSection.style.boxShadow = '0 8px 25px rgba(220, 53, 69, 0.3)';
                    actionSection.style.transform = 'scale(1)';
                }, 2000);
            } else {
                // Si la section n'est pas trouvée, faire défiler vers le bas
                window.scrollTo({
                    top: document.body.scrollHeight,
                    behavior: 'smooth'
                });
                
                // Afficher un message d'alerte
                setTimeout(() => {
                    alert('⚠️ Section des actions non trouvée. Veuillez faire défiler manuellement vers le bas de la page.');
                }, 1000);
            }
        };
        
        // Fonction pour forcer l'affichage de la section des actions
        window.forceShowActions = function() {
            const actionSection = document.getElementById('actions-section');
            if (actionSection) {
                // Forcer l'affichage
                actionSection.style.display = 'block';
                actionSection.style.visibility = 'visible';
                actionSection.style.opacity = '1';
                actionSection.style.position = 'relative';
                actionSection.style.zIndex = '1000';
                
                // Ajouter une bordure clignotante
                let blinkCount = 0;
                const blinkInterval = setInterval(() => {
                    actionSection.style.borderColor = blinkCount % 2 === 0 ? '#dc3545' : '#ff6b6b';
                    blinkCount++;
                    if (blinkCount > 6) {
                        clearInterval(blinkInterval);
                        actionSection.style.borderColor = '#dc3545';
                    }
                }, 300);
                
                // Faire défiler vers la section
                scrollToActions();
            }
        };
        
        // --- Sélection de la taxe applicable ---
        // Suppose que tu utilises un composant JS pour la recherche de taxe (autocomplete ou select2)
        // Ajoute ce code pour mettre à jour le champ caché taxe_id lors de la sélection
        // Exemple pour un select classique :
        // document.getElementById('select-taxe').addEventListener('change', function() {
        //   document.getElementById('taxe_id').value = this.value;
        // });
        // Si tu utilises un composant custom, adapte l'event pour mettre à jour #taxe_id
        
        // CSS pour forcer la visibilité du bouton de soumission
        const style = document.createElement('style');
        style.textContent = `
            button[type="submit"] {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                position: relative !important;
                z-index: 9999 !important;
                background: linear-gradient(135deg, #28a745, #20c997) !important;
                color: white !important;
                border: 3px solid #155724 !important;
                font-weight: 700 !important;
                box-shadow: 0 6px 20px rgba(40, 167, 69, 0.6) !important;
                font-size: 18px !important;
                padding: 15px 30px !important;
            }
            .row.mt-4 {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
        `;
        document.head.appendChild(style);
        
        // Forcer l'affichage du bouton au chargement - Version robuste
        function forceButtonVisibility() {
            const submitButton = document.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.style.display = 'block';
                submitButton.style.visibility = 'visible';
                submitButton.style.opacity = '1';
                submitButton.style.position = 'relative';
                submitButton.style.zIndex = '9999';
                console.log('✅ Bouton de soumission forcé à être visible');
                return true;
            } else {
                console.log('❌ Bouton de soumission non trouvé, nouvelle tentative...');
                return false;
            }
        }
        
        // Essayer plusieurs fois avec des délais différents
        setTimeout(forceButtonVisibility, 500);
        setTimeout(forceButtonVisibility, 1000);
        setTimeout(forceButtonVisibility, 2000);
        setTimeout(forceButtonVisibility, 3000);
        
        // Observer les changements du DOM
        const observer = new MutationObserver(() => {
            forceButtonVisibility();
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Arrêter l'observation après 10 secondes
        setTimeout(() => {
            observer.disconnect();
        }, 10000);
</script>
@endpush

@endsection
