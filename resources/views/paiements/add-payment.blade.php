@extends('layouts.app')

@section('title', 'Finaliser le Paiement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i>
                        Finaliser le Paiement
                    </h3>
                    <div class="card-tools">
                        @if(auth()->user()->role === 'collecteur')
                            <a href="{{ route('collecteur.paiements.show', $paiement->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Retour au paiement
                            </a>
                        @else
                            <a href="{{ route('paiements.show', $paiement->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Retour au paiement
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Informations du paiement actuel -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle"></i>
                                        Informations du Paiement Actuel
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h6 class="text-muted">Montant Total</h6>
                                                <h4 class="text-primary">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h6 class="text-muted">Déjà Payé</h6>
                                                <h4 class="text-success">{{ number_format($paiement->montant_avance, 0, ',', ' ') }} FCFA</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h6 class="text-muted">Montant Restant</h6>
                                                <h4 class="text-warning">{{ number_format($paiement->montant_restant, 0, ',', ' ') }} FCFA</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h6 class="text-muted">Taxe</h6>
                                                <h5 class="text-dark">
                                                    {{ optional($paiement->unite->taxe)->nom ?? '—' }}
                                                </h5>
                                                @if($paiement->unite && $paiement->unite->taxe)
                                                    <small class="text-muted">Montant taxe: {{ number_format($paiement->unite->taxe->montant, 0, ',', ' ') }} FCFA</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <div class="progress" style="height: 20px;">
                                            @php
                                                $pourcentage = ($paiement->montant_avance / $paiement->montant) * 100;
                                            @endphp
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $pourcentage }}%" 
                                                 aria-valuenow="{{ $pourcentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ number_format($pourcentage, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Statut Actuel
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-3x text-warning mb-2"></i>
                                    <h5 class="text-warning">Paiement Partiel</h5>
                                    <p class="text-muted">En attente de finalisation</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de paiement supplémentaire -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-plus"></i>
                                        Finaliser un Paiement (Paiement supplémentaire)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ auth()->user()->role === 'collecteur' ? route('collecteur.paiements.store-additional', $paiement->id) : route('paiements.store-additional', $paiement->id) }}" id="add-payment-form">
                                        @csrf
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="montant_supplementaire" class="form-label">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                        Montant à Ajouter <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('montant_supplementaire') is-invalid @enderror" 
                                                               id="montant_supplementaire" 
                                                               name="montant_supplementaire" 
                                                               value="{{ old('montant_supplementaire') }}"
                                                               min="0.01" 
                                                               max="{{ $paiement->montant_restant }}"
                                                               step="0.01"
                                                               required>
                                                        <span class="input-group-text">FCFA</span>
                                                    </div>
                                                    <div class="form-text">
                                                        Montant maximum : {{ number_format($paiement->montant_restant, 0, ',', ' ') }} FCFA
                                                    </div>
                                                    @error('montant_supplementaire')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="date_paiement" class="form-label">
                                                        <i class="fas fa-calendar-day"></i>
                                                        Date de Paiement <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" class="form-control @error('date_paiement') is-invalid @enderror" id="date_paiement" name="date_paiement" value="{{ old('date_paiement', now()->toDateString()) }}" required>
                                                    @error('date_paiement')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="mode_paiement" class="form-label">
                                                        <i class="fas fa-credit-card"></i>
                                                        Mode de Paiement <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-select @error('mode_paiement') is-invalid @enderror" 
                                                            id="mode_paiement" 
                                                            name="mode_paiement" 
                                                            required>
                                                        <option value="">Sélectionner un mode</option>
                                                        <option value="cash" {{ old('mode_paiement') == 'cash' ? 'selected' : '' }}>Espèces</option>
                                                        <option value="wave" {{ old('mode_paiement') == 'wave' ? 'selected' : '' }}>Wave</option>
                                                        <option value="orange_money" {{ old('mode_paiement') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                                                    </select>
                                                    @error('mode_paiement')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-map-marked-alt"></i> Coordonnées du paiement initial</label>
                                                    <div class="text-muted">
                                                        <div>Latitude: <strong>{{ $paiement->payment_latitude ?? '—' }}</strong></div>
                                                        <div>Longitude: <strong>{{ $paiement->payment_longitude ?? '—' }}</strong></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="reference_transaction" class="form-label">
                                                        <i class="fas fa-hashtag"></i>
                                                        Référence de Transaction
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control @error('reference_transaction') is-invalid @enderror" 
                                                           id="reference_transaction" 
                                                           name="reference_transaction" 
                                                           value="{{ old('reference_transaction') }}"
                                                           placeholder="Numéro de chèque, virement, etc.">
                                                    @error('reference_transaction')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="commentaire" class="form-label">
                                                        <i class="fas fa-comment"></i>
                                                        Commentaire
                                                    </label>
                                                    <textarea class="form-control @error('commentaire') is-invalid @enderror" 
                                                              id="commentaire" 
                                                              name="commentaire" 
                                                              rows="2"
                                                              placeholder="Commentaire sur ce paiement supplémentaire">{{ old('commentaire') }}</textarea>
                                                    @error('commentaire')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Géolocalisation pour les collecteurs -->
                                        @if(auth()->user()->role === 'collecteur')
                                        <div class="row mb-4">
                                            <div class="col-12">
                                                <div class="card shadow border-primary">
                                                    <div class="card-header bg-primary text-white">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                            Géolocalisation obligatoire pour le paiement supplémentaire
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <div id="additional-payment-location-map" style="height: 250px; border-radius: 8px; border: 2px solid #dee2e6;"></div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="d-grid gap-2">
                                                                    <button type="button" id="capture-additional-location-btn" class="btn btn-primary btn-lg">
                                                                        <i class="fas fa-crosshairs"></i>
                                                                        Capturer ma position GPS
                                                                    </button>
                                                                    <button type="button" id="manual-additional-location-btn" class="btn btn-outline-secondary" style="display: none;">
                                                                        <i class="fas fa-map-pin"></i>
                                                                        Position manuelle
                                                                    </button>
                                                                </div>
                                                                
                                                                <div class="mt-3" id="additional-location-info">
                                                                    <div class="alert alert-info">
                                                                        <i class="fas fa-info-circle"></i>
                                                                        <strong>Instructions :</strong><br>
                                                                        Cliquez sur "Capturer ma position GPS" pour enregistrer votre position actuelle.
                                                                    </div>
                                                                </div>
                                                                
                                                                <div id="additional-location-status" class="mt-3" style="display: none;">
                                                                    <!-- Le statut de géolocalisation sera affiché ici -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Champs cachés pour la géolocalisation -->
                                        @if(auth()->user()->role === 'collecteur')
                                        <input type="hidden" id="payment_latitude" name="payment_latitude" value="">
                                        <input type="hidden" id="payment_longitude" name="payment_longitude" value="">
                                        <input type="hidden" id="payment_accuracy" name="payment_accuracy" value="">
                                        @endif

                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            @if(auth()->user()->role === 'collecteur')
                                                <a href="{{ route('collecteur.paiements.show', $paiement->id) }}" class="btn btn-secondary me-md-2">
                                                    <i class="fas fa-times"></i>
                                                    Annuler
                                                </a>
                                            @else
                                                <a href="{{ route('paiements.show', $paiement->id) }}" class="btn btn-secondary me-md-2">
                                                    <i class="fas fa-times"></i>
                                                    Annuler
                                                </a>
                                            @endif
                                            <button type="submit" class="btn btn-success btn-lg" id="submit-additional-payment-btn">
                                                <i class="fas fa-plus"></i>
                                                Ajouter le Paiement
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations de l'unité commerciale -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-store"></i>
                                        Unité Commerciale
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Propriétaire :</strong>
                                        <p class="text-muted">{{ $paiement->unite->nom_proprietaire }}</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Nature :</strong>
                                        <p class="text-muted">{{ $paiement->unite->nature }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Taxe :</strong>
                                        <p class="text-muted">
                                            {{ optional($paiement->unite->taxe)->nom ?? '—' }}
                                            @if($paiement->unite && $paiement->unite->taxe)
                                                <br><small>Montant: {{ number_format($paiement->unite->taxe->montant, 0, ',', ' ') }} FCFA</small>
                                            @endif
                                        </p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Zone :</strong>
                                        <p>
                                            @if($paiement->unite->zone)
                                                <span class="badge bg-info">{{ $paiement->unite->zone->nom }}</span>
                                            @else
                                                <span class="badge bg-secondary">Non assignée</span>
                                            @endif
                                        </p>
                                    </div>
                                    
                                    <div class="d-grid">
                                        @if(auth()->user()->role === 'collecteur')
                                            <a href="{{ route('collecteur.unites.show', $paiement->unite->id) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                                Voir l'unité
                                            </a>
                                        @else
                                            <a href="{{ route('unites.show', $paiement->unite->id) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                                Voir l'unité
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Variables globales pour la géolocalisation
let additionalPaymentMap = null;
let additionalCurrentLocation = null;
let additionalLocationMarker = null;
let additionalIsLocationCaptured = false;

document.addEventListener('DOMContentLoaded', function() {
    console.log('💰 SICOSA - Module de paiement supplémentaire chargé');
    
    // Initialiser la carte si c'est un collecteur
    @if(auth()->user()->role === 'collecteur')
    initializeAdditionalPaymentMap();
    setupAdditionalLocationCapture();
    @endif
    
    // Validation du formulaire
    setupFormValidation();
});

// Fonctions de géolocalisation pour les collecteurs
@if(auth()->user()->role === 'collecteur')
function initializeAdditionalPaymentMap() {
    // Position par défaut (Sandiara)
    additionalPaymentMap = L.map('additional-payment-location-map').setView([14.43540, -16.79153], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(additionalPaymentMap);

    console.log('🗺️ Carte de paiement supplémentaire initialisée');
}

function setupAdditionalLocationCapture() {
    const captureBtn = document.getElementById('capture-additional-location-btn');
    const manualBtn = document.getElementById('manual-additional-location-btn');
    
    captureBtn.addEventListener('click', captureAdditionalCurrentLocation);
    manualBtn.addEventListener('click', enableAdditionalManualLocation);
    
    // Permettre le clic sur la carte pour position manuelle
    additionalPaymentMap.on('click', function(e) {
        if (manualBtn.style.display !== 'none') {
            setAdditionalManualLocation(e.latlng);
        }
    });
}

function captureAdditionalCurrentLocation() {
    const captureBtn = document.getElementById('capture-additional-location-btn');
    const manualBtn = document.getElementById('manual-additional-location-btn');
    
    captureBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Capture en cours...';
    captureBtn.disabled = true;
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            handleAdditionalLocationSuccess,
            handleAdditionalLocationError,
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    } else {
        handleAdditionalLocationError({ code: 0, message: 'Géolocalisation non supportée' });
    }
}

function handleAdditionalLocationSuccess(position) {
    const captureBtn = document.getElementById('capture-additional-location-btn');
    const manualBtn = document.getElementById('manual-additional-location-btn');
    
    additionalCurrentLocation = {
        lat: position.coords.latitude,
        lng: position.coords.longitude,
        accuracy: position.coords.accuracy
    };
    
    // Mettre à jour les champs cachés
    document.getElementById('payment_latitude').value = additionalCurrentLocation.lat;
    document.getElementById('payment_longitude').value = additionalCurrentLocation.lng;
    document.getElementById('payment_accuracy').value = additionalCurrentLocation.accuracy;
    
    // Ajouter le marqueur sur la carte
    if (additionalLocationMarker) {
        additionalPaymentMap.removeLayer(additionalLocationMarker);
    }
    
    additionalLocationMarker = L.marker([additionalCurrentLocation.lat, additionalCurrentLocation.lng], {
        icon: L.divIcon({
            className: 'location-marker',
            html: '<div style="background-color: #28a745; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        })
    }).addTo(additionalPaymentMap);
    
    // Centrer la carte sur la position
    additionalPaymentMap.setView([additionalCurrentLocation.lat, additionalCurrentLocation.lng], 16);
    
    // Mettre à jour l'interface
    captureBtn.innerHTML = '<i class="fas fa-check"></i> Position capturée';
    captureBtn.className = 'btn btn-success btn-lg';
    additionalIsLocationCaptured = true;
    
    showAdditionalLocationStatus('success', `Position GPS capturée avec une précision de ${Math.round(additionalCurrentLocation.accuracy)}m`);
    
    // Envoyer la position de tracking
    sendAdditionalTrackingPosition();
}

function handleAdditionalLocationError(error) {
    const captureBtn = document.getElementById('capture-additional-location-btn');
    const manualBtn = document.getElementById('manual-additional-location-btn');
    
    captureBtn.innerHTML = '<i class="fas fa-crosshairs"></i> Capturer ma position GPS';
    captureBtn.disabled = false;
    
    let errorMessage = 'Erreur de géolocalisation';
    switch(error.code) {
        case 1:
            errorMessage = 'Permission de géolocalisation refusée';
            break;
        case 2:
            errorMessage = 'Position indisponible';
            break;
        case 3:
            errorMessage = 'Délai d\'attente dépassé';
            break;
    }
    
    showAdditionalLocationStatus('error', errorMessage);
    enableAdditionalManualLocation();
}

function enableAdditionalManualLocation() {
    const manualBtn = document.getElementById('manual-additional-location-btn');
    manualBtn.style.display = 'block';
    manualBtn.innerHTML = '<i class="fas fa-map-pin"></i> Cliquez sur la carte';
    
    showAdditionalLocationStatus('info', 'Cliquez sur la carte pour définir votre position manuellement');
}

function setAdditionalManualLocation(latlng) {
    additionalCurrentLocation = {
        lat: latlng.lat,
        lng: latlng.lng,
        accuracy: 100 // Précision estimée pour position manuelle
    };
    
    // Mettre à jour les champs cachés
    document.getElementById('payment_latitude').value = additionalCurrentLocation.lat;
    document.getElementById('payment_longitude').value = additionalCurrentLocation.lng;
    document.getElementById('payment_accuracy').value = additionalCurrentLocation.accuracy;
    
    // Ajouter le marqueur sur la carte
    if (additionalLocationMarker) {
        additionalPaymentMap.removeLayer(additionalLocationMarker);
    }
    
    additionalLocationMarker = L.marker([additionalCurrentLocation.lat, additionalCurrentLocation.lng], {
        icon: L.divIcon({
            className: 'location-marker',
            html: '<div style="background-color: #ffc107; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        })
    }).addTo(additionalPaymentMap);
    
    additionalIsLocationCaptured = true;
    
    showAdditionalLocationStatus('success', 'Position manuelle définie');
    
    // Envoyer la position de tracking
    sendAdditionalTrackingPosition();
}

function sendAdditionalTrackingPosition() {
    if (!additionalCurrentLocation) return;
    
    const trackingData = {
        latitude: additionalCurrentLocation.lat,
        longitude: additionalCurrentLocation.lng,
        accuracy: additionalCurrentLocation.accuracy,
        activity: 'collecting',
        battery_level: getBatteryLevel() + '%'
    };
    
    fetch(BASE_URL + '/tracking/position', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(trackingData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('📍 Position de tracking envoyée avec succès');
        }
    })
    .catch(error => {
        console.error('❌ Erreur lors de l\'envoi de la position:', error);
    });
}

function getBatteryLevel() {
    if ('getBattery' in navigator) {
        navigator.getBattery().then(battery => {
            return Math.round(battery.level * 100);
        });
    }
    return Math.round(Math.random() * 100); // Valeur simulée
}

function showAdditionalLocationStatus(type, message) {
    const statusDiv = document.getElementById('additional-location-status');
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    statusDiv.innerHTML = `<div class="alert ${alertClass}"><i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i> ${message}</div>`;
    statusDiv.style.display = 'block';
}
@endif

function setupFormValidation() {
    const form = document.getElementById('add-payment-form');
    const submitBtn = document.getElementById('submit-additional-payment-btn');
    
    form.addEventListener('submit', function(e) {
        @if(auth()->user()->role === 'collecteur')
        if (!additionalIsLocationCaptured) {
            e.preventDefault();
            alert('Veuillez capturer votre position GPS avant de soumettre le formulaire.');
            return false;
        }
        @endif
        
        // Validation du montant
        const montantInput = document.getElementById('montant_supplementaire');
        const montantMax = {{ $paiement->montant_restant }};
        
        if (parseFloat(montantInput.value) > montantMax) {
            e.preventDefault();
            alert(`Le montant ne peut pas dépasser ${montantMax.toLocaleString()} FCFA`);
            return false;
        }
        
        // Confirmation avant soumission
        const montant = parseFloat(montantInput.value);
        const montantRestant = {{ $paiement->montant_restant }};
        const nouveauRestant = montantRestant - montant;
        
        let message = `Confirmer l'ajout de ${montant.toLocaleString()} FCFA ?`;
        if (nouveauRestant <= 0) {
            message += '\n\nCe paiement finalisera complètement le montant restant.';
        } else {
            message += `\n\nMontant restant après ce paiement : ${nouveauRestant.toLocaleString()} FCFA`;
        }
        
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
        
        // Désactiver le bouton pour éviter les double-soumissions
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
    });
}
</script>
@endpush
@endsection
