@extends('layouts.app')

@section('title', 'Enregistrer un paiement')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-money-check-alt"></i>
        Enregistrer un paiement
        @if(session('unite_created'))
            <span class="badge bg-success ms-2">Suite au recensement</span>
        @endif
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('paiements.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

{{-- Section de géolocalisation pour les collecteurs --}}
@if(auth()->user()->role === 'collecteur')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fas fa-map-marker-alt"></i>
                    Géolocalisation obligatoire pour l'enregistrement du paiement
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div id="payment-location-map" style="height: 300px; border-radius: 8px; border: 2px solid #dee2e6;"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-grid gap-2">
                            <button type="button" id="capture-location-btn" class="btn btn-primary btn-lg">
                                <i class="fas fa-crosshairs"></i>
                                Capturer ma position GPS
                            </button>
                            <button type="button" id="manual-location-btn" class="btn btn-outline-secondary" style="display: none;">
                                <i class="fas fa-map-pin"></i>
                                Position manuelle
                            </button>
                        </div>
                        
                        <div class="mt-3" id="location-info">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Instructions :</strong><br>
                                Cliquez sur "Capturer ma position GPS" pour enregistrer votre position actuelle avant de procéder au paiement.
                            </div>
                        </div>
                        
                        <div id="location-status" class="mt-3" style="display: none;">
                            <!-- Le statut de géolocalisation sera affiché ici -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(session('unite_created'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
            <div>
                <h5 class="alert-heading mb-1">Recensement terminé avec succès !</h5>
                <p class="mb-1">
                    <strong>{{ session('unite_name') }}</strong> a été ajouté(e) à votre zone de collecte.
                </p>
                <small class="text-muted">
                    <i class="fas fa-arrow-right"></i> Étape suivante : Enregistrer le paiement de taxe
                </small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Section de débogage temporaire --}}
@if(app()->environment(['local', 'development']) || request()->has('debug'))
    <div class="alert alert-warning">
        <strong>🔧 Informations de débogage :</strong><br>
        - Unite ID depuis URL : {{ request('unite_id') ?? 'Non défini' }}<br>
        - Unite ID sélectionnée : {{ $selectedUniteId ?? 'Non défini' }}<br>
        - Unite trouvée : {{ isset($selectedUnite) && $selectedUnite ? $selectedUnite->nom_proprietaire : 'Non trouvée' }}<br>
        - Nombre d'unités disponibles : {{ $unites->count() }}<br>
        - Session unite_created : {{ session('unite_created') ? 'Oui' : 'Non' }}
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle"></i>
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(isset($selectedUnite) && $selectedUnite)
    <div class="alert alert-primary alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-store fa-2x me-3 text-primary"></i>
            <div>
                <h6 class="alert-heading mb-1">Unité pré-sélectionnée</h6>
                <p class="mb-0">
                    <strong>{{ $selectedUnite->nom_proprietaire }}</strong> - {{ $selectedUnite->type_commerce }}
                    @if($selectedUnite->adresse)
                        <br><small class="text-muted">{{ $selectedUnite->adresse }}</small>
                    @endif
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-body">
                <form method="POST" action="{{ route('paiements.store') }}" id="payment-form">
                    @csrf
                    
                    {{-- Champs cachés pour la géolocalisation --}}
                    @if(auth()->user()->role === 'collecteur')
                    <input type="hidden" id="payment_latitude" name="payment_latitude" value="">
                    <input type="hidden" id="payment_longitude" name="payment_longitude" value="">
                    <input type="hidden" id="payment_accuracy" name="payment_accuracy" value="">
                    @endif
                    
                    <div class="mb-3">
                        <label for="unite_id" class="form-label">Unité commerciale <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control mb-2" 
                               id="unite-search" 
                               placeholder="Rechercher par nom, prénom ou type de commerce..."
                               autocomplete="off">
                        <select class="form-select @error('unite_id') is-invalid @enderror" 
                                id="unite_id" 
                                name="unite_id" 
                                required>
                            <option value="">-- Sélectionnez une unité --</option>
                            @foreach($unites as $unite)
                                @php
                                    $isSelected = old('unite_id', $selectedUniteId ?? request('unite_id')) == $unite->id;
                                @endphp
                                <option value="{{ $unite->id }}" 
                                        data-superficie="{{ $unite->superficie ?? 0 }}"
                                        data-has-polygon="{{ !empty($unite->geometrie_polygon) ? '1' : '0' }}"
                                        data-search="{{ strtolower($unite->nom_proprietaire . ' ' . $unite->prenom_proprietaire . ' ' . $unite->type_commerce . ' ' . $unite->adresse) }}"
                                        data-categorie="{{ $unite->categorie }}"
                                        data-nature="{{ $unite->nature }}"
                                        data-type-commerce="{{ $unite->type_commerce }}"
                                        {{ $isSelected ? 'selected' : '' }}>
                                    {{ $unite->nom_complet_proprietaire }} - {{ $unite->type_commerce }}
                                    @if($unite->adresse) ({{ $unite->adresse }}) @endif
                                    @if($unite->superficie)
                                        [{{ number_format($unite->superficie, 2, ',', ' ') }} m²]
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('unite_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(isset($selectedUnite) && $selectedUnite)
                            <div class="form-text text-success">
                                <i class="fas fa-check-circle"></i> 
                                Unité automatiquement sélectionnée suite au recensement
                            </div>
                        @endif
                        @if($unites->isEmpty())
                            <div class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Aucune unité commerciale disponible dans votre zone
                            </div>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <label for="taxe_id" class="form-label">Type de taxe <span class="text-danger">*</span></label>
                        <select class="form-select @error('taxe_id') is-invalid @enderror" 
                                id="taxe_id" 
                                name="taxe_id" 
                                required>
                            <option value="">-- Sélectionnez une taxe --</option>
                            @foreach($taxes as $taxe)
                                <option value="{{ $taxe->id }}" 
                                        data-montant="{{ $taxe->montant }}"
                                        data-montant-par-m2="{{ $taxe->montant_par_m2 ?? 0 }}"
                                        data-is-fonciere="{{ $taxe->is_fonciere ? '1' : '0' }}"
                                        data-periodicite="{{ $taxe->periodicite_display }}"
                                        data-types="{{ json_encode($taxe->types_commerce_applicables ?? []) }}"
                                        {{ old('taxe_id') == $taxe->id ? 'selected' : '' }}>
                                    {{ $taxe->nom }} 
                                    @if($taxe->is_fonciere)
                                        - {{ number_format($taxe->montant_par_m2 ?? 0, 0, ',', ' ') }} FCFA/m² ({{ $taxe->periodicite_display }})
                                    @else
                                        - {{ number_format($taxe->montant, 0, ',', ' ') }} FCFA ({{ $taxe->periodicite_display }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('taxe_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" id="taxe-info">
                            <!-- Info taxe sera affichée ici -->
                        </div>
                        @if(isset($selectedUnite) && $taxes->isEmpty())
                            <div class="alert alert-warning mt-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Aucune taxe applicable à cette unité commerciale selon les filtres définis. Contactez le gestionnaire pour configurer les taxes.
                            </div>
                        @endif
                    </div>
                    
                    <!-- Affichage de la superficie pour les terrains -->
                    <div id="superficie-info" class="mb-3" style="display: none;">
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-ruler-combined fa-2x me-3"></i>
                                <div>
                                    <strong>Terrain foncier détecté</strong>
                                    <div id="superficie-details" class="mt-1">
                                        <!-- Les détails de la superficie seront affichés ici -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="montant" class="form-label">Montant (FCFA) <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('montant') is-invalid @enderror" 
                               id="montant" 
                               name="montant" 
                               value="{{ old('montant') }}" 
                               min="0" 
                               step="0.01" 
                               readonly
                               style="background-color: #f8f9fa;"
                               required>
                        @error('montant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text" id="montant-calculation-info">
                            <i class="fas fa-info-circle"></i> Le montant est automatiquement calculé selon la taxe sélectionnée
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_paiement" class="form-label">Date du paiement <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control @error('date_paiement') is-invalid @enderror" 
                               id="date_paiement" 
                               name="date_paiement" 
                               value="{{ old('date_paiement', date('Y-m-d')) }}" 
                               max="{{ date('Y-m-d') }}" 
                               required>
                        @error('date_paiement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="mode_paiement" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                        <select class="form-select @error('mode_paiement') is-invalid @enderror" 
                                id="mode_paiement" 
                                name="mode_paiement" 
                                required>
                            <option value="">-- Sélectionnez un mode --</option>
                            <option value="cash" {{ old('mode_paiement') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="wave" {{ old('mode_paiement') == 'wave' ? 'selected' : '' }}>Wave</option>
                            <option value="orange_money" {{ old('mode_paiement') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                        </select>
                        @error('mode_paiement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3" id="reference-group" style="display: none;">
                        <label for="reference_transaction" class="form-label">Référence de transaction</label>
                        <input type="text" 
                               class="form-control @error('reference_transaction') is-invalid @enderror" 
                               id="reference_transaction" 
                               name="reference_transaction" 
                               value="{{ old('reference_transaction') }}" 
                               maxlength="255">
                        @error('reference_transaction')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Pour Wave et Orange Money, notez la référence de transaction</div>
                    </div>
                    
                    <!-- Section gestion des avances -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-calculator text-info"></i> 
                                Gestion des avances et paiements partiels
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="montant_avance" class="form-label">
                                        <i class="fas fa-coins text-warning"></i> 
                                        Montant de l'avance (FCFA)
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('montant_avance') is-invalid @enderror" 
                                           id="montant_avance" 
                                           name="montant_avance" 
                                           value="{{ old('montant_avance', 0) }}" 
                                           min="0" 
                                           step="1">
                                    @error('montant_avance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle"></i> 
                                        Montant déjà payé en avance (optionnel)
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="montant_restant" class="form-label">
                                        <i class="fas fa-balance-scale text-primary"></i> 
                                        Montant restant (FCFA)
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('montant_restant') is-invalid @enderror" 
                                           id="montant_restant" 
                                           name="montant_restant" 
                                           value="{{ old('montant_restant') }}" 
                                           min="0" 
                                           step="1"
                                           readonly>
                                    @error('montant_restant')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-calculator"></i> 
                                        Calculé automatiquement : Total - Avance
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Indicateur de statut -->
                            <div class="alert" id="payment-status" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <i id="status-icon" class="fas fa-circle me-2"></i>
                                    <span id="status-text"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                        <textarea class="form-control @error('commentaire') is-invalid @enderror" 
                                  id="commentaire" 
                                  name="commentaire" 
                                  rows="3" 
                                  maxlength="500">{{ old('commentaire') }}</textarea>
                        @error('commentaire')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-success btn-lg" id="submit-payment-btn">
                            <i class="fas fa-save"></i> Enregistrer le paiement
                        </button>
                        @if(session('unite_created'))
                            <a href="{{ route('collecteur.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-skip-forward"></i> Ignorer pour le moment
                            </a>
                        @else
                        <a href="{{ route('paiements.index') }}" class="btn btn-secondary">
                            Annuler
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informations utiles</h6>
            </div>
            <div class="card-body">
                <h6>Modes de paiement :</h6>
                <ul>
                    <li><strong>Cash</strong> : Paiement en espèces</li>
                    <li><strong>Wave</strong> : Paiement mobile via Wave</li>
                    <li><strong>Orange Money</strong> : Paiement mobile via Orange Money</li>
                </ul>
                
                <hr>
                
                <h6>Rappels importants :</h6>
                <ul class="mb-0">
                    <li>Vérifiez toujours l'identité du payeur</li>
                    <li>Le montant doit correspondre à la taxe applicable</li>
                    <li>Pour les paiements mobiles, attendez la confirmation avant de valider</li>
                    <li>Remettez un reçu au contribuable après paiement</li>
                </ul>
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
let paymentMap = null;
let currentLocation = null;
let locationMarker = null;
let isLocationCaptured = false;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🏢 SICOSA - Module de paiement avec géolocalisation chargé');
    
    // Initialiser la carte si c'est un collecteur
    @if(auth()->user()->role === 'collecteur')
    initializePaymentMap();
    setupLocationCapture();
    @endif
    
    // Références aux éléments
    const modePaiement = document.getElementById('mode_paiement');
    const referenceGroup = document.getElementById('reference-group');
    const uniteSelect = document.getElementById('unite_id');
    const uniteSearch = document.getElementById('unite-search');
    const montantInput = document.getElementById('montant');
    const taxeInfo = document.getElementById('taxe-info');
    const montantAvanceInput = document.getElementById('montant_avance');
    const montantRestantInput = document.getElementById('montant_restant');
    
    // Filtrage de la liste deroulante des unites commerciales
    if (uniteSearch && uniteSelect) {
        uniteSearch.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const options = uniteSelect.querySelectorAll('option');
            let firstVisible = null;
            options.forEach(function(option, index) {
                if (index === 0) return; // ignorer le placeholder
                const searchData = (option.dataset.search || '').toLowerCase();
                const text = option.textContent.toLowerCase();
                if (!term || searchData.includes(term) || text.includes(term)) {
                    option.style.display = '';
                    if (!firstVisible) firstVisible = option;
                } else {
                    option.style.display = 'none';
                }
            });
            // Ouvrir le select si une recherche est en cours pour faciliter la selection
            if (term.length > 0 && firstVisible) {
                uniteSelect.size = Math.min(10, uniteSelect.options.length);
            } else {
                uniteSelect.size = 1;
            }
        });
        
        // Lorsqu'une unite est selectionnee, on ferme la liste
        uniteSelect.addEventListener('change', function() {
            uniteSelect.size = 1;
            uniteSearch.value = '';
            // Re-afficher toutes les options
            const options = uniteSelect.querySelectorAll('option');
            options.forEach(function(option) {
                option.style.display = '';
            });
            
            // Filtrer les taxes selon l'unité sélectionnée
            filterTaxesByUnite();
        });
        
        // Quand on clique sur le select, vider la recherche
        uniteSelect.addEventListener('click', function() {
            if (uniteSelect.size > 1) {
                uniteSelect.size = 1;
            }
        });
    }
    
    // Fonction pour filtrer les taxes selon l'unité sélectionnée
    function filterTaxesByUnite() {
        const uniteSelect = document.getElementById('unite_id');
        const taxeSelect = document.getElementById('taxe_id');
        const selectedOption = uniteSelect.options[uniteSelect.selectedIndex];
        
        if (!selectedOption || selectedOption.value === '') {
            // Si aucune unité n'est sélectionnée, afficher toutes les taxes
            const taxeOptions = taxeSelect.querySelectorAll('option');
            taxeOptions.forEach(function(option) {
                option.style.display = '';
            });
            return;
        }
        
        // Récupérer le type de commerce de l'unité sélectionnée
        const typeCommerce = selectedOption.dataset.typeCommerce || '';
        
        // Filtrer les taxes
        const taxeOptions = taxeSelect.querySelectorAll('option');
        let hasApplicableTaxe = false;
        
        taxeOptions.forEach(function(option, index) {
            if (index === 0) {
                // Garder l'option par défaut
                option.style.display = '';
                return;
            }
            
            // Récupérer les filtres de la taxe
            const taxeTypes = JSON.parse(option.dataset.types || '[]');
            
            // Si la taxe n'a pas de filtres, elle est applicable à toutes les unités
            if (taxeTypes.length === 0) {
                option.style.display = '';
                hasApplicableTaxe = true;
                return;
            }
            
            // Vérifier si la taxe est applicable à cette unité
            if (taxeTypes.includes(typeCommerce)) {
                option.style.display = '';
                hasApplicableTaxe = true;
            } else {
                option.style.display = 'none';
            }
        });
        
        // Réinitialiser la sélection si aucune taxe n'est applicable
        if (!hasApplicableTaxe) {
            taxeSelect.value = '';
            taxeInfo.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i> Aucune taxe applicable à cette unité';
        } else {
            taxeInfo.innerHTML = '';
        }
    }
    
    // Fonction pour calculer le montant restant
    function calculateMontantRestant() {
        const montantTotal = parseFloat(montantInput.value) || 0;
        const montantAvance = parseFloat(montantAvanceInput.value) || 0;
        const montantRestant = Math.max(0, montantTotal - montantAvance);
        
        montantRestantInput.value = montantRestant;
        
        // Références aux éléments de statut
        const paymentStatus = document.getElementById('payment-status');
        const statusIcon = document.getElementById('status-icon');
        const statusText = document.getElementById('status-text');
        
        // Mettre à jour l'affichage visuel et le statut
        if (montantTotal === 0) {
            // Aucun montant défini
            paymentStatus.style.display = 'none';
            montantRestantInput.style.borderColor = '';
            montantRestantInput.style.backgroundColor = '';
        } else if (montantRestant === 0) {
            // Paiement complet
            montantRestantInput.style.borderColor = '#28a745';
            montantRestantInput.style.backgroundColor = '#d4edda';
            
            paymentStatus.className = 'alert alert-success';
            paymentStatus.style.display = 'block';
            statusIcon.className = 'fas fa-check-circle me-2 text-success';
            statusText.innerHTML = `<strong>✅ PAIEMENT COMPLET</strong><br>
                                   <small>Total : ${montantTotal.toLocaleString()} FCFA - Entièrement payé</small>`;
        } else if (montantAvance > 0) {
            // Paiement partiel
            montantRestantInput.style.borderColor = '#ffc107';
            montantRestantInput.style.backgroundColor = '#fff3cd';
            
            paymentStatus.className = 'alert alert-warning';
            paymentStatus.style.display = 'block';
            statusIcon.className = 'fas fa-exclamation-triangle me-2 text-warning';
            statusText.innerHTML = `<strong>⚠️ PAIEMENT PARTIEL</strong><br>
                                   <small>Avance : ${montantAvance.toLocaleString()} FCFA / Total : ${montantTotal.toLocaleString()} FCFA<br>
                                   Reste à payer : <strong>${montantRestant.toLocaleString()} FCFA</strong></small>`;
        } else {
            // Aucune avance
            montantRestantInput.style.borderColor = '#6c757d';
            montantRestantInput.style.backgroundColor = '#f8f9fa';
            
            paymentStatus.className = 'alert alert-info';
            paymentStatus.style.display = 'block';
            statusIcon.className = 'fas fa-info-circle me-2 text-info';
            statusText.innerHTML = `<strong>💰 EN ATTENTE DE PAIEMENT</strong><br>
                                   <small>Montant total à payer : <strong>${montantTotal.toLocaleString()} FCFA</strong></small>`;
        }
        
        // Validation d'avance excessive
        if (montantAvance > montantTotal && montantTotal > 0) {
            montantAvanceInput.style.borderColor = '#dc3545';
            montantAvanceInput.style.backgroundColor = '#f8d7da';
            
            paymentStatus.className = 'alert alert-danger';
            paymentStatus.style.display = 'block';
            statusIcon.className = 'fas fa-times-circle me-2 text-danger';
            statusText.innerHTML = `<strong>❌ ERREUR</strong><br>
                                   <small>L'avance (${montantAvance.toLocaleString()} FCFA) ne peut pas dépasser le montant total (${montantTotal.toLocaleString()} FCFA)</small>`;
        } else {
            montantAvanceInput.style.borderColor = '';
            montantAvanceInput.style.backgroundColor = '';
        }
    }
    
    // Afficher/masquer le champ référence selon le mode de paiement
    modePaiement.addEventListener('change', function() {
        if (this.value === 'wave' || this.value === 'orange_money') {
            referenceGroup.style.display = 'block';
        } else {
            referenceGroup.style.display = 'none';
        }
    });
    
    // Fonction pour mettre à jour les informations de taxe
    function updateTaxeInfo() {
        const selectedOption = uniteSelect.options[uniteSelect.selectedIndex];
        if (selectedOption.value) {
            const taxeMontant = parseFloat(selectedOption.dataset.taxe) || 0;
            const taxeNom = selectedOption.dataset.taxeNom || 'Aucune taxe';
            
            if (taxeMontant > 0) {
                taxeInfo.innerHTML = `<i class="fas fa-info-circle text-info"></i> Taxe applicable : <strong>${taxeNom}</strong> - Montant : <strong>${new Intl.NumberFormat('fr-FR').format(taxeMontant)} FCFA</strong>`;
                montantInput.value = taxeMontant;
                montantInput.style.backgroundColor = '#e8f5e8';
                
                // Ajouter une animation pour attirer l'attention
                montantInput.style.transition = 'background-color 0.5s ease';
                setTimeout(() => {
                    montantInput.style.backgroundColor = '';
                }, 2000);
            } else {
                taxeInfo.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i> Aucune taxe définie pour cette unité';
                montantInput.value = '';
            }
            
            // Recalculer le montant restant après mise à jour du montant
            calculateMontantRestant();
        } else {
            taxeInfo.innerHTML = '';
            montantInput.value = '';
            calculateMontantRestant();
        }
    }
    
    // Écouter les changements de sélection d'unité
    uniteSelect.addEventListener('change', updateTaxeInfo);
    
    // Écouteurs pour recalcul automatique du montant restant
    montantInput.addEventListener('input', calculateMontantRestant);
    montantAvanceInput.addEventListener('input', calculateMontantRestant);
    
    // Déclencher immédiatement si une unité est pré-sélectionnée
    if (uniteSelect.value) {
        console.log('Unité pré-sélectionnée détectée:', uniteSelect.value);
        updateTaxeInfo();
        
        // Mettre en évidence l'unité sélectionnée
        uniteSelect.style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
            uniteSelect.style.backgroundColor = '';
        }, 3000);
    }
    
    // Déclencher l'événement de mode de paiement si déjà sélectionné
    if (modePaiement.value) {
        modePaiement.dispatchEvent(new Event('change'));
    }
    
    // Ajouter des logs pour le débogage
    console.log('Formulaire de paiement initialisé');
    console.log('Unité sélectionnée:', uniteSelect.value);
    console.log('Nombre d\'unités disponibles:', uniteSelect.options.length - 1);
});

// Fonctions de géolocalisation pour les collecteurs
@if(auth()->user()->role === 'collecteur')
function initializePaymentMap() {
    try {
        // Position par défaut (Sandiara)
        const defaultLat = 14.43540;
        const defaultLng = -16.79153;
        
        // Créer la carte
        paymentMap = L.map('payment-location-map').setView([defaultLat, defaultLng], 15);
        
        // Ajouter les tuiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
            minZoom: 2
        }).addTo(paymentMap);
        
        // Popup initial
        const popup = L.popup()
            .setLatLng([defaultLat, defaultLng])
            .setContent('<strong>🗺️ Carte de paiement</strong><br>Cliquez sur "Capturer ma position GPS" pour commencer')
            .openOn(paymentMap);
        
        console.log('✅ Carte de paiement initialisée');
        
        // Forcer le redimensionnement de la carte
        setTimeout(() => {
            paymentMap.invalidateSize();
        }, 250);
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'initialisation de la carte:', error);
        showLocationStatus('error', 'Erreur lors du chargement de la carte');
    }
}

function setupLocationCapture() {
    const captureBtn = document.getElementById('capture-location-btn');
    const manualBtn = document.getElementById('manual-location-btn');
    const submitBtn = document.getElementById('submit-payment-btn');
    const form = document.getElementById('payment-form');
    
    // Bouton de capture GPS
    captureBtn.addEventListener('click', captureCurrentLocation);
    
    // Bouton de position manuelle
    manualBtn.addEventListener('click', enableManualLocation);
    
    // Validation avant soumission
    form.addEventListener('submit', function(e) {
        if (!isLocationCaptured) {
            e.preventDefault();
            showLocationStatus('error', '❌ Vous devez d\'abord capturer votre position GPS avant d\'enregistrer le paiement');
            return false;
        }
        
        // Envoyer la position de tracking
        sendTrackingPosition();
    });
}

function captureCurrentLocation() {
    if (!navigator.geolocation) {
        showLocationStatus('error', '❌ Géolocalisation non supportée par ce navigateur');
        document.getElementById('manual-location-btn').style.display = 'block';
        return;
    }

    const btn = document.getElementById('capture-location-btn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Capture en cours...';
    btn.disabled = true;
    
    document.getElementById('manual-location-btn').style.display = 'block';
    showLocationStatus('info', '🔍 Recherche de votre position GPS...');

    // Options améliorées pour la géolocalisation
    const options = {
        enableHighAccuracy: true,
        timeout: 30000,
        maximumAge: 300000
    };

    navigator.geolocation.getCurrentPosition(
        function(position) {
            currentLocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                accuracy: position.coords.accuracy,
                altitude: position.coords.altitude || null,
                method: 'GPS'
            };
            
            handleLocationSuccess();
        },
        function(error) {
            handleLocationError(error);
        },
        options
    );
}

function handleLocationSuccess() {
    // Mettre à jour la carte
    if (paymentMap) {
        paymentMap.setView([currentLocation.lat, currentLocation.lng], 18);
        
        // Supprimer l'ancien marqueur
        if (locationMarker) {
            paymentMap.removeLayer(locationMarker);
        }
        
        // Ajouter le nouveau marqueur
        locationMarker = L.marker([currentLocation.lat, currentLocation.lng])
            .addTo(paymentMap)
            .bindPopup(`
                <div class="text-center">
                    <strong>📍 Position capturée</strong><br>
                    <small>Précision: ${Math.round(currentLocation.accuracy)}m</small><br>
                    <small>${currentLocation.lat.toFixed(6)}, ${currentLocation.lng.toFixed(6)}</small>
                </div>
            `)
            .openPopup();
    }
    
    // Mettre à jour les champs cachés
    document.getElementById('payment_latitude').value = currentLocation.lat;
    document.getElementById('payment_longitude').value = currentLocation.lng;
    document.getElementById('payment_accuracy').value = currentLocation.accuracy;
    
    // Mettre à jour l'interface
    const btn = document.getElementById('capture-location-btn');
    btn.innerHTML = '<i class="fas fa-check"></i> Position capturée';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');
    btn.disabled = true;
    
    // Masquer les instructions et afficher le statut
    document.getElementById('location-info').style.display = 'none';
    showLocationStatus('success', `✅ Position GPS capturée avec succès !<br>Précision: ${Math.round(currentLocation.accuracy)}m`);
    
    isLocationCaptured = true;
    
    console.log('✅ Position GPS capturée:', currentLocation);
}

function handleLocationError(error) {
    const btn = document.getElementById('capture-location-btn');
    btn.innerHTML = '<i class="fas fa-crosshairs"></i> Capturer ma position GPS';
    btn.disabled = false;
    
    let errorMessage = '❌ Erreur de géolocalisation: ';
    switch(error.code) {
        case error.PERMISSION_DENIED:
            errorMessage += 'Permission refusée. Veuillez autoriser la géolocalisation.';
            break;
        case error.POSITION_UNAVAILABLE:
            errorMessage += 'Position non disponible. Vérifiez votre connexion GPS.';
            break;
        case error.TIMEOUT:
            errorMessage += 'Timeout. La recherche de position a pris trop de temps.';
            break;
        default:
            errorMessage += 'Erreur inconnue.';
            break;
    }
    
    showLocationStatus('error', errorMessage);
    console.error('❌ Erreur géolocalisation:', error);
}

function enableManualLocation() {
    if (paymentMap) {
        paymentMap.on('click', function(e) {
            currentLocation = {
                lat: e.latlng.lat,
                lng: e.latlng.lng,
                accuracy: 0,
                method: 'Manuel'
            };
            
            // Supprimer l'ancien marqueur
            if (locationMarker) {
                paymentMap.removeLayer(locationMarker);
            }
            
            // Ajouter le nouveau marqueur
            locationMarker = L.marker([currentLocation.lat, currentLocation.lng])
                .addTo(paymentMap)
                .bindPopup(`
                    <div class="text-center">
                        <strong>📍 Position manuelle</strong><br>
                        <small>${currentLocation.lat.toFixed(6)}, ${currentLocation.lng.toFixed(6)}</small>
                    </div>
                `)
                .openPopup();
            
            // Mettre à jour les champs cachés
            document.getElementById('payment_latitude').value = currentLocation.lat;
            document.getElementById('payment_longitude').value = currentLocation.lng;
            document.getElementById('payment_accuracy').value = 0;
            
            // Mettre à jour l'interface
            const btn = document.getElementById('manual-location-btn');
            btn.innerHTML = '<i class="fas fa-check"></i> Position définie';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            btn.disabled = true;
            
            showLocationStatus('success', '✅ Position manuelle définie avec succès !');
            
            isLocationCaptured = true;
            
            // Désactiver le mode clic
            paymentMap.off('click');
        });
        
        showLocationStatus('info', '📍 Cliquez sur la carte pour définir votre position manuellement');
    }
}

function sendTrackingPosition() {
    if (!currentLocation) return;
    
    const data = {
        latitude: currentLocation.lat,
        longitude: currentLocation.lng,
        altitude: currentLocation.altitude,
        accuracy: currentLocation.accuracy,
        activity: 'collecting',
        battery_level: getBatteryLevel()
    };

    fetch(BASE_URL + '/tracking/position', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Position de tracking envoyée');
        }
    })
    .catch(error => {
        console.error('❌ Erreur envoi position tracking:', error);
    });
}

function getBatteryLevel() {
    if ('getBattery' in navigator) {
        navigator.getBattery().then(battery => {
            return Math.round(battery.level * 100) + '%';
        });
    }
    return null;
}

function showLocationStatus(type, message) {
    const statusDiv = document.getElementById('location-status');
    statusDiv.style.display = 'block';
    
    let alertClass = 'alert-info';
    let iconClass = 'fas fa-info-circle';
    
    switch(type) {
        case 'success':
            alertClass = 'alert-success';
            iconClass = 'fas fa-check-circle';
            break;
        case 'error':
            alertClass = 'alert-danger';
            iconClass = 'fas fa-exclamation-triangle';
            break;
        case 'info':
            alertClass = 'alert-info';
            iconClass = 'fas fa-info-circle';
            break;
    }
    
    statusDiv.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show">
            <i class="${iconClass}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

// Données des unités avec leurs superficies (passées depuis le contrôleur)
const unitesData = @json($unitesData ?? []);

// Gestion de l'affichage automatique du montant selon la taxe
document.addEventListener('DOMContentLoaded', function() {
    const taxeSelect = document.getElementById('taxe_id');
    const uniteSelect = document.getElementById('unite_id');
    const montantInput = document.getElementById('montant');
    const taxeInfo = document.getElementById('taxe-info');
    const superficieInfo = document.getElementById('superficie-info');
    const superficieDetails = document.getElementById('superficie-details');
    const montantCalculationInfo = document.getElementById('montant-calculation-info');
    
    // Fonction pour calculer le montant
    function calculateMontant() {
        const selectedUniteId = uniteSelect ? uniteSelect.value : null;
        const selectedTaxeId = taxeSelect ? taxeSelect.value : null;
        
        if (!selectedUniteId || !selectedTaxeId) {
            montantInput.value = '';
            taxeInfo.innerHTML = '';
            superficieInfo.style.display = 'none';
            montantCalculationInfo.innerHTML = '<i class="fas fa-info-circle"></i> Le montant est automatiquement calculé selon la taxe sélectionnée';
            return;
        }
        
        const selectedOption = taxeSelect.options[taxeSelect.selectedIndex];
        const isFonciere = selectedOption.getAttribute('data-is-fonciere') === '1';
        const montantParM2 = parseFloat(selectedOption.getAttribute('data-montant-par-m2')) || 0;
        const montantFixe = parseFloat(selectedOption.getAttribute('data-montant')) || 0;
        const periodicite = selectedOption.getAttribute('data-periodicite');
        const taxeNom = selectedOption.textContent.split(' - ')[0];
        
        // Récupérer les données de l'unité depuis les options ou unitesData
        const selectedUniteOption = uniteSelect ? uniteSelect.options[uniteSelect.selectedIndex] : null;
        let superficie = null;
        let hasPolygon = false;
        
        if (selectedUniteOption && selectedUniteOption.value) {
            // Utiliser les data-attributes si disponibles
            const superficieAttr = selectedUniteOption.getAttribute('data-superficie');
            const hasPolygonAttr = selectedUniteOption.getAttribute('data-has-polygon');
            
            if (superficieAttr) {
                superficie = parseFloat(superficieAttr);
            }
            if (hasPolygonAttr) {
                hasPolygon = hasPolygonAttr === '1';
            }
        }
        
        // Fallback vers unitesData si les attributs ne sont pas disponibles
        if ((superficie === null || superficie === 0) && unitesData[selectedUniteId]) {
            const uniteData = unitesData[selectedUniteId];
            if (uniteData) {
                superficie = parseFloat(uniteData.superficie) || null;
                hasPolygon = uniteData.has_polygon || false;
            }
        }
        
        let montant = 0;
        let calculationText = '';
        
        if (isFonciere) {
            // Taxe foncière : calculer selon la superficie
            if (superficie && superficie > 0) {
                montant = superficie * montantParM2;
                calculationText = `${superficie.toFixed(2)} m² × ${montantParM2.toLocaleString('fr-FR')} FCFA/m² = ${montant.toLocaleString('fr-FR')} FCFA`;
                
                // Afficher les informations de superficie
                superficieInfo.style.display = 'block';
                superficieDetails.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Superficie :</strong> ${superficie.toFixed(2)} m²
                        </div>
                        <div class="col-md-6">
                            <strong>Montant par m² :</strong> ${montantParM2.toLocaleString('fr-FR')} FCFA/m²
                        </div>
                    </div>
                    ${hasPolygon ? '<div class="mt-2"><small class="text-success"><i class="fas fa-check-circle"></i> Polygone géographique enregistré</small></div>' : ''}
                `;
            } else {
                // Pas de superficie définie
                montant = 0;
                calculationText = '⚠️ Aucune superficie définie pour ce terrain. Veuillez d\'abord recenser le terrain avec sa superficie.';
                superficieInfo.style.display = 'block';
                superficieDetails.innerHTML = `
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention :</strong> Cette unité n'a pas de superficie définie. 
                        La taxe foncière nécessite une superficie pour être calculée.
                        <br><small>Veuillez d'abord recenser le terrain avec sa superficie via le formulaire de recensement.</small>
                    </div>
                `;
            }
            
            montantCalculationInfo.innerHTML = `<i class="fas fa-calculator"></i> ${calculationText}`;
            montantInput.style.backgroundColor = '#fff3cd';
            montantInput.style.borderColor = '#ffc107';
        } else {
            // Taxe normale : montant fixe
            montant = montantFixe;
            calculationText = `Montant fixe : ${montant.toLocaleString('fr-FR')} FCFA`;
            
            // Masquer les informations de superficie
            superficieInfo.style.display = 'none';
            
            montantCalculationInfo.innerHTML = `<i class="fas fa-info-circle"></i> ${calculationText}`;
            montantInput.style.backgroundColor = '#d1ecf1';
            montantInput.style.borderColor = '#bee5eb';
        }
        
        // Mettre à jour le montant
        montantInput.value = montant.toFixed(2);
        
        // Afficher les informations de la taxe
        taxeInfo.innerHTML = `
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i>
                <strong>Taxe sélectionnée :</strong> ${taxeNom}<br>
                <strong>Montant :</strong> ${montant.toLocaleString('fr-FR')} FCFA<br>
                <strong>Périodicité :</strong> ${periodicite}
                ${isFonciere && superficie ? `<br><small><i class="fas fa-ruler-combined"></i> Basé sur la superficie du terrain</small>` : ''}
            </div>
        `;
        
        // Recalculer le montant restant si les champs existent
        if (typeof calculateMontantRestant === 'function') {
            calculateMontantRestant();
        }
    }
    
    // Écouter les changements de taxe et d'unité
    if (taxeSelect) {
        taxeSelect.addEventListener('change', calculateMontant);
    }
    
    if (uniteSelect) {
        uniteSelect.addEventListener('change', calculateMontant);
    }
    
    // Déclencher le calcul initial si une taxe et une unité sont déjà sélectionnées
    if (taxeSelect && taxeSelect.value && uniteSelect && uniteSelect.value) {
        calculateMontant();
    }
});
@endif
</script>
@endpush
@endsection 