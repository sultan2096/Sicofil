@extends('layouts.app')

@section('title', 'Carte Thématique' . (isset($commune) && $commune ? ' - ' . $commune->nom : ' - SICOSA'))

@section('content')
@php
    $thematicCommuneNom = isset($commune) && $commune ? $commune->nom : 'Sandiara';
    $thematicAcronyme = isset($commune) && $commune ? $commune->display_acronyme : 'SICOSA';
    $thematicLogoUrl = (isset($commune) && $commune && $commune->logo_url) ? $commune->logo_url : asset('images/logos/snd.jpeg');
    $thematicMapCenter = isset($mapCenter) ? $mapCenter : [14.43540, -16.79153];
    $thematicMapBounds = isset($mapBounds) ? $mapBounds : null;
@endphp

<!-- CORRECTION POUR L'AFFICHAGE COMPLET -->
<style>
    html, body { 
        height: auto !important; 
        overflow: visible !important; 
        margin: 0 !important;
        padding: 0 !important;
    }
    .main-content, .container-fluid { 
        height: auto !important; 
        overflow: visible !important; 
        padding: 10px !important;
    }
    .card { 
        margin-bottom: 10px !important; 
        border-radius: 8px !important;
    }
    .card-header { 
        padding: 10px 15px !important; 
        border-bottom: 1px solid #dee2e6 !important;
    }
    .card-body { 
        padding: 15px !important; 
    }
    .mb-3 { 
        margin-bottom: 10px !important; 
    }
    .mb-2 { 
        margin-bottom: 8px !important; 
    }
    .py-2 { 
        padding-top: 8px !important; 
        padding-bottom: 8px !important; 
    }
    .stat-item h5 { 
        font-size: 1.1rem !important; 
        margin-bottom: 2px !important; 
    }
    .stat-item small { 
        font-size: 0.75rem !important; 
    }
    #thematic-map { 
        height: 500px !important; 
        width: 100% !important; 
        border-radius: 8px !important;
        position: relative !important;
        border: 3px solid #333 !important; /* Cadrage */
    }
    #thematic-map.exporting { padding: 16px; background: #fff; }
    .btn { 
        padding: 6px 12px !important; 
        font-size: 0.9rem !important; 
    }
    .btn-sm { 
        padding: 4px 8px !important; 
        font-size: 0.8rem !important; 
    }
    .form-control, .form-select { 
        padding: 6px 10px !important; 
        font-size: 0.9rem !important; 
    }
    .form-label { 
        font-size: 0.9rem !important; 
        margin-bottom: 4px !important; 
    }
    h3, h4, h5, h6 { 
        margin-bottom: 8px !important; 
    }
    .sicosa-map-title { 
        font-size: 1.3rem !important; 
        margin-bottom: 5px !important; 
    }
    .sicosa-map-subtitle { 
        font-size: 0.9rem !important; 
        margin-bottom: 10px !important; 
    }
    .modal-dialog { 
        max-height: 90vh !important; 
        overflow-y: auto !important; 
    }
    .modal-body { 
        max-height: 70vh !important; 
        overflow-y: auto !important; 
    }
    /* Masquer l'attribution Leaflet/OSM à l'écran et à l'export */
    .leaflet-control-attribution { display: none !important; }
    /* Overlays pour export (flèche du nord, titre, QR) */
    .map-overlay {
        position: absolute;
        z-index: 5000;
        pointer-events: none;
    }
    .map-north-arrow {
        top: 50%; /* au milieu vertical entre le titre (haut) et l'échelle (bas) */
        left: 16px;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        background: white;
        border: 1px solid #ccc;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .map-north-arrow svg { width: 22px; height: 22px; }
    .map-export-title {
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255,255,255,0.95);
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 6px 10px;
        font-weight: 600;
        color: #0d6efd;
        max-width: 80%;
        text-align: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .map-qrcode {
        bottom: 10px;
        right: 10px;
        background: rgba(255,255,255,0.95);
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .map-qrcode .qrcaption { font-size: 10px; color: #6c757d; text-align: center; margin-top: 2px; }
    .map-export-header {
        top: 10px;
        left: 10px;
        right: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(255,255,255,0.95);
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 6px 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        font-size: 12px;
        color: #212529;
    }
    /* S'assurer que la légende est bien visible et au-dessus lors de l'export */
    .map-legend-control { z-index: 6000; }
    .leaflet-top.leaflet-right { z-index: 6000 !important; }

    /* Styles des marqueurs de la carte thématique */
    .custom-marker {
        background: transparent !important;
        border: none !important;
    }
    .marker-paye {
        background-color: #28a745 !important;
    }
    .marker-impaye {
        background-color: #dc3545 !important;
    }
    .marker-partiel {
        background-color: #ffc107 !important;
    }
    .marker-en_retard {
        background-color: #6c757d !important;
    }

    /* Légende intégrée */
    .legend-logo {
        height: 18px;
        width: 18px;
        border-radius: 50%;
        object-fit: cover;
    }
    .legend-header {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 6px;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 4px;
    }
    .legend-title {
        font-weight: 700;
        color: #0d6efd;
        font-size: 12px;
    }
    .legend-content {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
    }
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 1px solid #fff;
        box-shadow: 0 0 0 1px #343a40;
        flex-shrink: 0;
    }
    .legend-paye {
        background-color: #28a745;
    }
    .legend-footer {
        margin-top: 4px;
        padding-top: 4px;
        border-top: 1px solid #e9ecef;
        text-align: center;
        color: #6c757d;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <img src="{{ $thematicLogoUrl }}" alt="Logo {{ $thematicAcronyme }}" 
                                 class="me-3" style="height: 30px; width: 30px; border-radius: 50%; object-fit: contain;" onerror="this.src='{{ asset('images/logos/snd.jpeg') }}';">
                            <div>
                                <h5 class="mb-0">
                                    <i class="fas fa-map-marked-alt"></i> 
                                    Carte Thématique Géospatiale
                                </h5>
                                <small class="opacity-75">Système d'Information Commerciale de {{ $thematicCommuneNom }}</small>
                            </div>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-light btn-sm" onclick="exportMapAsImage()">
                                <i class="fas fa-download"></i> Exporter Image
                            </button>
                            <button class="btn btn-light btn-sm" onclick="exportMapAsPDF()">
                                <i class="fas fa-file-pdf"></i> Exporter PDF
                            </button>
                            <button class="btn btn-outline-light btn-sm text-dark" onclick="toggleReportMode()" id="reportModeBtn" title="Afficher/Masquer les éléments du rapport (titre, nord, QR, bordure)">
                                <i class="fas fa-layer-group"></i> Mode rapport: OFF
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Contrôles de style de carte -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="mb-0">
                                                <i class="fas fa-palette me-2"></i>
                                                Style de la carte
                                            </h6>
                                            <small class="text-muted">Choisissez le style d'affichage de la carte</small>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" onclick="changeMapStyle('standard')">
                                                <i class="fas fa-map"></i> Standard
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="changeMapStyle('satellite')">
                                                <i class="fas fa-satellite"></i> Satellite
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="changeMapStyle('terrain')">
                                                <i class="fas fa-mountain"></i> Terrain
                                            </button>
                                            <!-- Style sombre retiré -->
                                            <button type="button" class="btn btn-outline-warning" onclick="reloadMap()" title="Recharger la carte">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="testTileLoading('satellite')" title="Tester les tuiles satellite">
                                <i class="fas fa-vial"></i>
                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="row mb-2">
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-2">
                            <label class="form-label">Statut de paiement</label>
                            <select id="statut-filter" class="form-select form-select-sm">
                                <option value="tous">Tous</option>
                                <option value="paye">Payé</option>
                                <option value="impaye">Impayé</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-2">
                            <label class="form-label">Zone fiscale</label>
                            <select id="zone-filter" class="form-select form-select-sm">
                                <option value="">Toutes les zones</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-2">
                            <label class="form-label">Type de commerce</label>
                            <select id="type-commerce-filter" class="form-select form-select-sm">
                                <option value="">Tous les types</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-2">
                            <label class="form-label">Période début</label>
                            <input type="date" id="periode-debut" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-2">
                            <label class="form-label">Période fin</label>
                            <input type="date" id="periode-fin" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-2">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-primary btn-sm d-block w-100" onclick="appliquerFiltres()">
                                <i class="fas fa-filter"></i> 
                                <span class="d-none d-sm-inline">Appliquer</span>
                                <span class="d-sm-none">OK</span>
                            </button>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <div class="row text-center">
                                        <div class="col-6 col-sm-4 col-md-2 mb-2">
                                            <div class="stat-item">
                                                <h5 class="text-primary mb-0" id="total-unites">{{ number_format($stats['total_unites']) }}</h5>
                                                <small class="text-muted">Total unités</small>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-2 mb-2">
                                            <div class="stat-item">
                                                <h5 class="text-success mb-0" id="payees">0</h5>
                                                <small class="text-muted">Payées</small>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-2 mb-2">
                                            <div class="stat-item">
                                                <h5 class="text-danger mb-0" id="impayees">0</h5>
                                                <small class="text-muted">Impayées</small>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-2 mb-2">
                                            <div class="stat-item">
                                                <h5 class="text-warning mb-0" id="en-retard">0</h5>
                                                <small class="text-muted">En retard</small>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-2 mb-2">
                                            <div class="stat-item">
                                                <h5 class="text-info mb-0" id="total-montant">0 FCFA</h5>
                                                <small class="text-muted">Montant dû</small>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-2 mb-2">
                                            <div class="stat-item">
                                                <div class="text-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ $donnees_carte->count() ?? 0 }} unités affichées
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Barre contrôles carte (comme /map) -->
                    <div class="row mb-2">
                        <div class="col-md-8">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="centerThematicMap()">
                                    <i class="fas fa-crosshairs"></i> Recentrer
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-zones-fiscales" onclick="toggleThematicZones()">
                                    <i class="fas fa-layer-group"></i> Zones fiscales
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-limites-communales" onclick="toggleThematicCommune()">
                                    <i class="fas fa-border-all"></i> Limites communales
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="fitThematicZonesToView()">
                                    <i class="fas fa-expand-arrows-alt"></i> Voir les zones
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="card border-primary">
                                        <div class="card-body text-center p-2">
                                            <i class="fas fa-store text-primary"></i>
                                            <div class="fw-bold" id="card-unites-count">{{ number_format($stats['total_unites'] ?? 0) }}</div>
                                            <small>Unités commerciales</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card border-success">
                                        <div class="card-body text-center p-2">
                                            <i class="fas fa-map-marked-alt text-success"></i>
                                            <div class="fw-bold">{{ isset($zones) ? $zones->count() : 0 }}</div>
                                            <small>Zones fiscales</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Titre de la carte -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="text-center">
                                <h4 class="text-primary mb-1 sicosa-map-title">
                                    <i class="fas fa-map-marked-alt"></i> 
                                    <span class="d-none d-sm-inline">Carte Thématique des Unités Commerciales</span>
                                    <span class="d-sm-none">Carte Thématique</span>
                                </h4>
                                <p class="text-muted sicosa-map-subtitle">
                                    <span class="d-none d-sm-inline">Visualisation géospatiale du statut de paiement des taxes - Commune de {{ $thematicCommuneNom }}</span>
                                    <span class="d-sm-none">Visualisation géospatiale - {{ $thematicCommuneNom }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Carte -->
                    <div class="row">
                        <div class="col-12">
                            <div id="thematic-map" class="map-container">
                                <!-- Overlays capturés par html2canvas (désormais à l'intérieur de la carte) -->
                                
                                <div id="export-title" class="map-overlay map-export-title" style="display:none;"></div>
                                <div class="map-overlay map-north-arrow" title="Nord géographique" style="display:none;" id="north-arrow">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path fill="#dc3545" d="M12 2l4 9-4-2-4 2 4-9z"/>
                                        <text x="11.2" y="20" font-size="8" fill="#212529">N</text>
                                    </svg>
                                </div>
                                <div id="qr-container" class="map-overlay map-qrcode" style="display:none;">
                                    <div id="qr-code"></div>
                                    <div class="qrcaption">Scanner pour ouvrir</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les détails -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de l'unité commerciale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="details-content">
                <!-- Contenu dynamique -->
            </div>
        </div>
    </div>
</div>

<!-- Légende intégrée dans la carte -->
<div id="map-legend" class="map-legend-integrated" style="display: none;">
    <div class="legend-header">
        <img src="{{ $thematicLogoUrl }}" alt="Logo {{ $thematicAcronyme }}" class="legend-logo" onerror="this.src='{{ asset('images/logos/snd.jpeg') }}';">
        <span class="legend-title">Légende</span>
    </div>
    <div class="legend-content">
    <div class="legend-item">
        <div class="legend-color legend-paye"></div>
        <span>Payé (à jour)</span>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background-color: #dc3545;"></div>
        <span>Impayé (en retard)</span>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background-color: #ffc107;"></div>
        <span>Paiement partiel</span>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background-color: #6c757d;"></div>
        <span>En retard</span>
    </div>
        <hr style="margin: 8px 0;">
    <div class="legend-item">
        <div class="legend-color" style="background-color: #007bff; border-radius: 50%;"></div>
        <span>Agrégation par zone</span>
    </div>
    </div>
        <div class="legend-footer">
            <small>Commune de {{ $thematicCommuneNom }}</small>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="{{ asset('css/thematic-map.css') }}" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script type="application/json" id="sicosa-data">{"zones": @json($zones ?? []), "typesCommerce": @json($types_commerce ?? []), "initialStyle": @json($style ?? 'standard')}</script>

<script>
const bootEl = document.getElementById('sicosa-data');
const BOOT = bootEl ? JSON.parse(bootEl.textContent) : { zones: [], typesCommerce: [], initialStyle: 'standard' };
let map;
let markersLayer;
let clusterGroup;
let communeBoundaryGroup;
let zonesBoundaryGroup;
let currentData = [];
let currentTileLayer; // Pour gérer les styles de carte

// Sources alternatives pour les tuiles satellite
const satelliteSources = [
    {
        name: 'Google Satellite',
        url: 'https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',
        attribution: '© Google Satellite | Commune de {{ $thematicCommuneNom }}'
    },
    {
        name: 'Esri Satellite',
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        attribution: '© Esri | Commune de {{ $thematicCommuneNom }}'
    },
    {
        name: 'Bing Satellite',
        url: 'https://ecn.t3.tiles.virtualearth.net/tiles/a{q}.jpeg?g=1',
        attribution: '© Bing Maps | Commune de {{ $thematicCommuneNom }}'
    }
];

let currentSatelliteSource = 0;

// Fonction pour essayer la source satellite suivante
function tryNextSatelliteSource() {
    currentSatelliteSource = (currentSatelliteSource + 1) % satelliteSources.length;
    const source = satelliteSources[currentSatelliteSource];
    
    console.log(`🔄 Essai de la source satellite: ${source.name}`);
    
    if (map && currentTileLayer) {
        // Supprimer la couche actuelle
        map.removeLayer(currentTileLayer);
        
        // Ajouter la nouvelle source
        currentTileLayer = L.tileLayer(source.url, {
            attribution: source.attribution,
            maxZoom: 19,
            minZoom: 1
        }).addTo(map);
        
        // Gérer les erreurs
        currentTileLayer.on('tileerror', function(e) {
            console.warn(`⚠️ Erreur avec ${source.name}, essai de la source suivante...`);
            setTimeout(tryNextSatelliteSource, 1000);
        });
        
        currentTileLayer.on('tileload', function(e) {
            console.log(`✅ ${source.name} fonctionne correctement`);
        });
    }
}

const thematicMapCenter = @json($thematicMapCenter);
const thematicMapBounds = @json($thematicMapBounds);

// Définir les différents styles de carte
const mapStyles = {
    standard: {
        url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: '© OpenStreetMap contributors | Commune de {{ $thematicCommuneNom }}'
    },
    satellite: {
        url: 'https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',
        attribution: '© Google Satellite | Commune de {{ $thematicCommuneNom }}'
    },
    terrain: {
        url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
        attribution: '© OpenTopoMap | Commune de {{ $thematicCommuneNom }}'
    },
    dark: {
        url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
        attribution: '© CartoDB | Commune de {{ $thematicCommuneNom }}'
    }
};

document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    loadFilters();
    // Charger les données initiales sans filtres
    loadMapData({});
    
    // Ajouter les écouteurs d'événements pour les styles de carte
    addMapStyleListeners();
    
    // Appliquer le style initial si différent de standard
    if (BOOT.initialStyle && BOOT.initialStyle !== 'standard') {
        setTimeout(() => {
            changeMapStyle(BOOT.initialStyle);
            console.log('Style initial appliqué:', BOOT.initialStyle);
        }, 1000);
    }
});

function initializeMap() {
    // Initialiser la carte
    map = L.map('thematic-map', {
        center: thematicMapCenter,
        zoom: 15,
        zoomControl: true,
        attributionControl: false,
        maxZoom: 18,
        minZoom: 1
    }).setView(thematicMapCenter, 15);
    
    // Couche de base OpenStreetMap (style standard par défaut)
    currentTileLayer = L.tileLayer(mapStyles.standard.url, {
        attribution: mapStyles.standard.attribution,
        maxZoom: 18,
        minZoom: 1,
        subdomains: 'abc',
        errorTileUrl: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
    }).addTo(map);
    
    // Gérer les erreurs de chargement des tuiles
    currentTileLayer.on('tileerror', function(e) {
        console.warn('⚠️ Erreur de chargement de tuile initiale:', e);
    });
    
    // Groupe de marqueurs avec clustering
    clusterGroup = L.markerClusterGroup({
        chunkedLoading: true,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        maxClusterRadius: 50,
        iconCreateFunction: function(cluster) {
            const count = cluster.getChildCount();
            return L.divIcon({
                html: `<div class="cluster-marker" style="width: 40px; height: 40px; line-height: 36px; border-radius: 50%; background-color: #007bff; color: white; text-align: center; font-weight: bold; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">${count}</div>`,
                className: 'custom-cluster',
                iconSize: L.point(40, 40)
            });
        }
    });
    
    map.addLayer(clusterGroup);
    
    // Couches SHP : commune et zones séparées pour contrôles indépendants
    communeBoundaryGroup = L.layerGroup();
    zonesBoundaryGroup = L.layerGroup();
    loadBoundaryLayers();
    map.addLayer(zonesBoundaryGroup);
    thematicZonesVisible = true;
    document.getElementById('btn-zones-fiscales')?.classList.add('active');
    
    setTimeout(() => {
        map.invalidateSize();
        map.setView(thematicMapCenter, 15);
        if (thematicMapBounds && thematicMapBounds.length >= 2) {
            try {
                map.fitBounds([thematicMapBounds[0], thematicMapBounds[1]], { padding: [40, 40], maxZoom: 15 });
            } catch (e) {}
        }
    }, 100);
    setTimeout(() => {
        map.setView(thematicMapCenter, 15);
        if (thematicMapBounds && thematicMapBounds.length >= 2) {
            try { map.fitBounds([thematicMapBounds[0], thematicMapBounds[1]], { padding: [40, 40], maxZoom: 15 }); } catch (e) {}
        }
    }, 500);
    
    // Cartouche titre supprimé à la demande (nous utilisons désormais la flèche du nord à cet emplacement)
    
    // Légende compacte (topright)
    const legendControl = L.control({position: 'topright'});
    legendControl.onAdd = function(map) {
        const div = L.DomUtil.create('div', 'map-legend-control');
        div.innerHTML = `
            <div class="map-legend-integrated" style="background:rgba(255,255,255,0.98);padding:6px 10px;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,0.15);border:1px solid #e9ecef;max-width:160px;font-size:11px;color:#212529;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;border-bottom:1px solid #e9ecef;padding-bottom:4px;">
                    <img src="{{ $thematicLogoUrl }}" alt="Logo {{ $thematicAcronyme }}" style="height:16px;width:16px;border-radius:50%;" onerror="this.src='{{ asset('images/logos/snd.jpeg') }}';">
                    <span style="font-weight:700;color:#0d6efd;font-size:11px;">Légende</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:3px;">
                    <div style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:50%;background:#28a745;border:1px solid #fff;box-shadow:0 0 0 1px #343a40;"></span><span>Payé</span></div>
                    <div style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:50%;background:#dc3545;border:1px solid #fff;box-shadow:0 0 0 1px #343a40;"></span><span>Impayé</span></div>
                    <div style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:50%;background:#ffc107;border:1px solid #fff;box-shadow:0 0 0 1px #343a40;"></span><span>Partiel</span></div>
                    <div style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:50%;background:#6c757d;border:1px solid #fff;box-shadow:0 0 0 1px #343a40;"></span><span>En retard</span></div>
                    <hr style="margin:4px 0;border-color:#e9ecef;">
                    <div style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:50%;background:#0d6efd;border:1px solid #fff;box-shadow:0 0 0 1px #343a40;"></span><span>Agrégation</span></div>
                </div>
                <div style="text-align:center;margin-top:4px;"><small style="color:#6c757d;font-size:10px;">{{ $thematicCommuneNom }}</small></div>
            </div>
        `;
        return div;
    };
    legendControl.addTo(map);

    // Barre d'échelle (métrique)
    L.control.scale({ position: 'bottomleft', imperial: false }).addTo(map);
    
    console.log('🗺️ Carte initialisée avec succès');
}

function getBoundaryStyle(layerKey) {
    switch (layerKey) {
        case 'commune': return { color: '#ff0000', weight: 3, fillColor: '#ff0000', fillOpacity: 0.08, dashArray: '6,6' };
        case 'sandiaracentre': return { color: '#2C3E50', weight: 2, fillColor: '#FFB6C1', fillOpacity: 0.5 };
        case 'ndioukhthiorokh': return { color: '#2C3E50', weight: 2, fillColor: '#87CEEB', fillOpacity: 0.5 };
        case 'loulibentegne': return { color: '#2C3E50', weight: 2, fillColor: '#90EE90', fillOpacity: 0.5 };
        default: return { color: '#555', weight: 2, fillOpacity: 0.2 };
    }
}

function loadBoundaryLayers() {
    if (!map || !communeBoundaryGroup || !zonesBoundaryGroup) return;
    let loaded = 0;
    const total = 2;
    function tryFitToZones() {
        loaded++;
        if (loaded < total) return;
        const allLayers = [...communeBoundaryGroup.getLayers(), ...zonesBoundaryGroup.getLayers()];
        if (allLayers.length > 0) {
            try {
                const group = L.featureGroup(allLayers);
                if (group.getBounds().isValid()) {
                    map.fitBounds(group.getBounds(), { padding: [40, 40], maxZoom: 15 });
                }
            } catch (e) { console.warn(e); }
        } else if (thematicMapBounds && thematicMapBounds.length >= 2) {
            try { map.fitBounds([thematicMapBounds[0], thematicMapBounds[1]], { padding: [40, 40], maxZoom: 15 }); } catch (e) {}
        } else {
            map.setView(thematicMapCenter, 15);
        }
    }
    fetch(BASE_URL + '/api/map/boundaries?layer=commune')
        .then(r => r.json())
        .then(json => {
            if (json.success && json.data && json.data.features && json.data.features.length > 0) {
                L.geoJSON(json.data, { style: getBoundaryStyle('commune') }).addTo(communeBoundaryGroup);
            }
            tryFitToZones();
        })
        .catch(e => { console.warn('Boundary commune', e); tryFitToZones(); });
    fetch(BASE_URL + '/api/map/boundaries?layer=zones')
        .then(r => r.json())
        .then(json => {
            if (json.success && json.data && json.data.features && json.data.features.length > 0) {
                const zoneColors = ['#FFB6C1', '#90EE90', '#87CEEB', '#DDA0DD', '#F0E68C', '#D2B48C'];
                L.geoJSON(json.data, {
                    style: function(feature) {
                        if (feature && feature.properties && feature.properties.nom) {
                            let idx = 0;
                            const nom = String(feature.properties.nom);
                            for (let i = 0; i < nom.length; i++) idx += nom.charCodeAt(i);
                            const color = zoneColors[Math.abs(idx) % zoneColors.length];
                            return { color: '#2C3E50', weight: 2, fillColor: color, fillOpacity: 0.5 };
                        }
                        return { color: '#2C3E50', weight: 2, fillOpacity: 0.3 };
                    }
                }).addTo(zonesBoundaryGroup);
            }
            tryFitToZones();
        })
        .catch(e => { console.warn('Boundary zones', e); tryFitToZones(); });
}

let thematicCommuneVisible = false;
let thematicZonesVisible = true;
function centerThematicMap() {
    if (map) {
        map.setView(thematicMapCenter, 15);
        if (thematicMapBounds && thematicMapBounds.length >= 2) {
            try { map.fitBounds([thematicMapBounds[0], thematicMapBounds[1]], { padding: [40, 40], maxZoom: 15 }); } catch (e) {}
        }
    }
}
function toggleThematicCommune() {
    if (!map || !communeBoundaryGroup) return;
    thematicCommuneVisible = !thematicCommuneVisible;
    if (thematicCommuneVisible) {
        map.addLayer(communeBoundaryGroup);
        if (thematicZonesVisible) { map.removeLayer(zonesBoundaryGroup); thematicZonesVisible = false; }
        document.getElementById('btn-limites-communales')?.classList.add('active');
        document.getElementById('btn-zones-fiscales')?.classList.remove('active');
    } else {
        map.removeLayer(communeBoundaryGroup);
        document.getElementById('btn-limites-communales')?.classList.remove('active');
    }
}
function toggleThematicZones() {
    if (!map || !zonesBoundaryGroup) return;
    thematicZonesVisible = !thematicZonesVisible;
    if (thematicZonesVisible) {
        map.addLayer(zonesBoundaryGroup);
        if (thematicCommuneVisible) { map.removeLayer(communeBoundaryGroup); thematicCommuneVisible = false; }
        document.getElementById('btn-zones-fiscales')?.classList.add('active');
        document.getElementById('btn-limites-communales')?.classList.remove('active');
    } else {
        map.removeLayer(zonesBoundaryGroup);
        document.getElementById('btn-zones-fiscales')?.classList.remove('active');
    }
}
function fitThematicZonesToView() {
    if (!map || !zonesBoundaryGroup) return;
    const layers = zonesBoundaryGroup.getLayers();
    if (layers.length === 0) return;
    try {
        const group = L.featureGroup(layers);
        if (group.getBounds().isValid()) map.fitBounds(group.getBounds(), { padding: [30, 30] });
    } catch (e) { console.warn(e); }
}

function loadFilters() {
    // Utiliser les données passées par le contrôleur
    const zones = Array.isArray(BOOT.zones) ? BOOT.zones : [];
    const zoneSelect = document.getElementById('zone-filter');
    zones.forEach(zone => {
        const option = document.createElement('option');
        option.value = zone.id;
        option.textContent = zone.nom;
        zoneSelect.appendChild(option);
    });
    
    const typesCommerce = Array.isArray(BOOT.typesCommerce) ? BOOT.typesCommerce : [];
    const typeSelect = document.getElementById('type-commerce-filter');
    typesCommerce.forEach(type => {
        const option = document.createElement('option');
        option.value = type;
        option.textContent = type;
        typeSelect.appendChild(option);
    });
}

function loadMapData(filters = {}) {
    // Construire l'URL avec les filtres
    const params = new URLSearchParams(filters);
    const url = BASE_URL + `/api/map/thematic-data?${params.toString()}`;
    
    // Afficher un indicateur de chargement
    console.log('Chargement des données de la carte...');
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                currentData = data.data;
                updateMap(data.data);
                updateStatistics(data.statistiques);
                console.log(`Données chargées: ${data.data.length} unités`);
            } else {
                console.error('Erreur dans la réponse:', data);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des données:', error);
            // Afficher un message d'erreur à l'utilisateur
            alert('Erreur lors du chargement des données de la carte. Veuillez réessayer.');
        });
}

function updateMap(data) {
    // Nettoyer les marqueurs existants
    clusterGroup.clearLayers();
    
    data.forEach(unite => {
        if (unite.coordinates && unite.coordinates.lat && unite.coordinates.lng) {
            const marker = createMarker(unite);
            clusterGroup.addLayer(marker);
        }
    });
}

function createMarker(unite) {
    // Créer l'icône selon le statut - POINTS RONDS au lieu de carrés
    const icon = L.divIcon({
        className: 'custom-marker',
        html: `<div class="marker-${unite.statut_paiement}" style="width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8]
    });
    
    const marker = L.marker([unite.coordinates.lat, unite.coordinates.lng], { icon });
    
    // Popup avec informations détaillées
    const popupContent = `
        <div class="popup-content">
            <div class="popup-header">
                <h6 class="mb-0">
                    <i class="fas fa-store"></i> 
                    ${unite.nom_proprietaire}
                    ${unite.prenom_proprietaire ? ' ' + unite.prenom_proprietaire : ''}
                </h6>
            </div>
            <div class="popup-body">
                <div class="mb-2">
                    <strong>Commerce:</strong> ${unite.nom_commerce || 'Non spécifié'}
                </div>
                <div class="mb-2">
                    <strong>Type:</strong> ${unite.type_commerce}
                </div>
                <div class="mb-2">
                    <strong>Zone:</strong> ${unite.zone ? unite.zone.nom : 'Non assignée'}
                </div>
                <div class="mb-2">
                    <strong>Statut:</strong> 
                    <span class="status-badge status-${unite.statut_paiement}">
                        ${getStatutLabel(unite.statut_paiement)}
                    </span>
                </div>
                <div class="mb-2">
                    <strong>Montant dû:</strong> ${formatCurrency(unite.montant_du)}
                </div>
                <div class="mb-2">
                    <strong>Adresse:</strong> ${unite.adresse || 'Non spécifiée'}
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-primary btn-sm" onclick="showDetails(${unite.id})">
                        <i class="fas fa-eye"></i> Voir détails
                    </button>
                </div>
            </div>
        </div>
    `;
    
    marker.bindPopup(popupContent);
    
    return marker;
}

function updateStatistics(statistiques) {
    if (!statistiques) return;
    const totalUnites = statistiques.total_unites ?? 0;
    const elTotal = document.getElementById('total-unites');
    const elCard = document.getElementById('card-unites-count');
    if (elTotal) elTotal.textContent = totalUnites;
    if (elCard) elCard.textContent = typeof totalUnites === 'number' ? totalUnites.toLocaleString('fr-FR') : totalUnites;
    document.getElementById('payees').textContent = statistiques.payees;
    document.getElementById('impayees').textContent = statistiques.impayees;
    document.getElementById('en-retard').textContent = statistiques.en_retard;
    document.getElementById('total-montant').textContent = formatCurrency(statistiques.total_montant_du);
}

function appliquerFiltres() {
    const filters = {
        statut: document.getElementById('statut-filter').value,
        zone_id: document.getElementById('zone-filter').value,
        type_commerce: document.getElementById('type-commerce-filter').value,
        periode_debut: document.getElementById('periode-debut').value,
        periode_fin: document.getElementById('periode-fin').value
    };
    
    loadMapData(filters);
    // Mettre à jour le titre export selon filtres
    updateExportTitle(filters);
}

function showDetails(uniteId) {
    // Fermer le popup
    map.closePopup();
    
    // Charger les détails complets
    fetch(BASE_URL + `/api/unites/${uniteId}`)
        .then(response => response.json())
        .then(unite => {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations générales</h6>
                        <p><strong>Propriétaire:</strong> ${unite.nom_proprietaire} ${unite.prenom_proprietaire || ''}</p>
                        <p><strong>Commerce:</strong> ${unite.nom_commerce || 'Non spécifié'}</p>
                        <p><strong>Type:</strong> ${unite.type_commerce}</p>
                        <p><strong>Catégorie:</strong> ${unite.categorie}</p>
                        <p><strong>Adresse:</strong> ${unite.adresse || 'Non spécifiée'}</p>
                        <p><strong>Téléphone:</strong> ${unite.telephone || 'Non spécifié'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Informations fiscales</h6>
                        <p><strong>Zone:</strong> ${unite.zone ? unite.zone.nom : 'Non assignée'}</p>
                        <p><strong>Taxe:</strong> ${unite.taxe ? unite.taxe.nom : 'Non définie'}</p>
                        <p><strong>Montant taxe:</strong> ${unite.taxe ? formatCurrency(unite.taxe.montant) : 'Non défini'}</p>
                        <p><strong>Statut paiement:</strong> 
                            <span class="status-badge status-${unite.statut_paiement}">
                                ${getStatutLabel(unite.statut_paiement)}
                            </span>
                        </p>
                        <p><strong>Montant dû:</strong> ${formatCurrency(unite.montant_du)}</p>
                        <p><strong>Nombre de paiements:</strong> ${unite.nombre_paiements}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('details-content').innerHTML = content;
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        });
}

function exportMapAsImage() {
    console.log('🖼️ Début de l\'export image...');
    
    // Sauvegarder l'état actuel de la carte
    const currentZoom = map.getZoom();
    const currentCenter = map.getCenter();
    
    // Préparer la carte pour l'export (tous les points visibles)
    const allMarkers = prepareMapForExport();
    
    // Attendre que la carte se stabilise
    setTimeout(() => {
        console.log('📸 Capture de l\'image...');
        
        // Créer un conteneur temporaire pour la capture
        const mapContainer = document.getElementById('thematic-map');
        const originalStyle = mapContainer.style.cssText;
        
        // Forcer l'affichage complet + style export
        mapContainer.classList.add('exporting');
        mapContainer.style.height = '700px';
        mapContainer.style.width = '100%';
        mapContainer.style.overflow = 'visible';
        
        // Afficher les overlays pour export
        showExportOverlays();

        // Mettre la légende au premier plan
        const legendPane = document.querySelector('.leaflet-top.leaflet-right');
        const prevLegendZ = legendPane ? legendPane.style.zIndex : '';
        if (legendPane) legendPane.style.zIndex = '6000';

        // Mettre la légende au premier plan
        const legendPane2 = document.querySelector('.leaflet-top.leaflet-right');
        const prevLegendZ2 = legendPane2 ? legendPane2.style.zIndex : '';
        if (legendPane2) legendPane2.style.zIndex = '6000';

        html2canvas(mapContainer, {
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            scale: 2, // Améliorer la qualité
            logging: true,
            width: mapContainer.offsetWidth,
            height: mapContainer.offsetHeight,
            scrollX: 0,
            scrollY: 0,
            windowWidth: mapContainer.offsetWidth,
            windowHeight: mapContainer.offsetHeight
        }).then(canvas => {
            console.log('✅ Image capturée avec succès');
            // Masquer overlays après capture
            hideExportOverlays();
            
            // Restaurer l'état de la carte
            restoreMapState(allMarkers);
            map.setView(currentCenter, currentZoom);
            mapContainer.classList.remove('exporting');
            mapContainer.style.cssText = originalStyle;
            
            // Restaurer z-index de la légende
            if (legendPane) legendPane.style.zIndex = prevLegendZ;
            // Télécharger l'image
            const link = document.createElement('a');
            link.download = `carte-thematique-sicosa-${new Date().toISOString().split('T')[0]}.png`;
            link.href = canvas.toDataURL('image/png', 1.0);
            link.click();
            
            console.log('📥 Image téléchargée');
        }).catch(error => {
            console.error('❌ Erreur lors de l\'export image:', error);
            // Restaurer z-index de la légende
            if (legendPane) legendPane.style.zIndex = prevLegendZ;
            // Restaurer l'état de la carte en cas d'erreur
            restoreMapState(allMarkers);
            map.setView(currentCenter, currentZoom);
            mapContainer.classList.remove('exporting');
            mapContainer.style.cssText = originalStyle;
            hideExportOverlays();
        });
    }, 2000); // Augmenter le délai pour la stabilisation
}

function exportMapAsPDF() {
    console.log('📄 Début de l\'export PDF...');
    
    // Sauvegarder l'état actuel de la carte
    const currentZoom = map.getZoom();
    const currentCenter = map.getCenter();
    
    // Préparer la carte pour l'export (tous les points visibles)
    const allMarkers = prepareMapForExport();
    
    // Attendre que la carte se stabilise
    setTimeout(() => {
        console.log('📸 Capture pour PDF...');
        
        // Créer un conteneur temporaire pour la capture
        const mapContainer = document.getElementById('thematic-map');
        const originalStyle = mapContainer.style.cssText;
        
        // Forcer l'affichage complet + style export
        mapContainer.classList.add('exporting');
        mapContainer.style.height = '700px';
        mapContainer.style.width = '100%';
        mapContainer.style.overflow = 'visible';
        
        // Afficher les overlays pour export
        showExportOverlays();

        html2canvas(mapContainer, {
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            scale: 2, // Améliorer la qualité
            logging: true,
            width: mapContainer.offsetWidth,
            height: mapContainer.offsetHeight,
            scrollX: 0,
            scrollY: 0,
            windowWidth: mapContainer.offsetWidth,
            windowHeight: mapContainer.offsetHeight
        }).then(canvas => {
            console.log('✅ Image capturée pour PDF');
            
            // Restaurer l'état de la carte
            restoreMapState(allMarkers);
            map.setView(currentCenter, currentZoom);
            mapContainer.classList.remove('exporting');
            mapContainer.style.cssText = originalStyle;
            
            // Restaurer z-index de la légende
            if (legendPane2) legendPane2.style.zIndex = prevLegendZ2;
            // Créer le PDF
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('landscape', 'mm', 'a4');
            
            const imgData = canvas.toDataURL('image/png', 1.0);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            const imgWidth = pdfWidth;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            
            pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
            pdf.save(`carte-thematique-sicosa-${new Date().toISOString().split('T')[0]}.pdf`);
            
            console.log('📥 PDF téléchargé');
            hideExportOverlays();
        }).catch(error => {
            console.error('❌ Erreur lors de l\'export PDF:', error);
            // Restaurer z-index de la légende
            if (legendPane2) legendPane2.style.zIndex = prevLegendZ2;
            // Restaurer l'état de la carte en cas d'erreur
            restoreMapState(allMarkers);
            map.setView(currentCenter, currentZoom);
            mapContainer.classList.remove('exporting');
            mapContainer.style.cssText = originalStyle;
            hideExportOverlays();
        });
    }, 2000); // Augmenter le délai pour la stabilisation
}

// Fonction pour forcer l'affichage de tous les points avant export
function prepareMapForExport() {
    console.log('🔧 Préparation de la carte pour l\'export...');
    
    // Désactiver le clustering temporairement pour voir tous les points
    map.removeLayer(clusterGroup);
    
    // Recréer les marqueurs sans clustering
    const allMarkers = [];
    currentData.forEach(unite => {
        if (unite.coordinates && unite.coordinates.lat && unite.coordinates.lng) {
            const marker = createMarker(unite);
            allMarkers.push(marker);
            map.addLayer(marker);
        }
    });
    
    console.log(`📍 ${allMarkers.length} marqueurs ajoutés pour l'export`);
    
    // Zoomer pour voir tous les points
    if (allMarkers.length > 0) {
        const group = L.featureGroup(allMarkers);
        const bounds = group.getBounds();
        map.fitBounds(bounds, { padding: [20, 20] });
        
        // Attendre un peu pour que la carte se stabilise
        setTimeout(() => {
            map.invalidateSize();
        }, 500);
    }
    
    return allMarkers;
}

// Fonction pour restaurer l'état normal de la carte
function restoreMapState(markers) {
    console.log('🔄 Restauration de l\'état de la carte...');
    
    // Supprimer les marqueurs individuels
    markers.forEach(marker => map.removeLayer(marker));
    
    // Restaurer le clustering
    map.addLayer(clusterGroup);
    
    console.log('✅ État de la carte restauré');
}

function getStatutLabel(statut) {
    const labels = {
        'paye': 'Payé',
        'impaye': 'Impayé',
        'partiel': 'Partiel',
        'en_retard': 'En retard'
    };
    return labels[statut] || statut;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF'
    }).format(amount);
}

// Fonction pour tester le chargement des tuiles
function testTileLoading(style) {
    console.log(`🧪 Test de chargement des tuiles pour le style: ${style}`);
    
    if (!mapStyles[style]) {
        console.error(`❌ Style ${style} non trouvé`);
        return;
    }
    
    // Créer une tuile de test
    const testTile = L.tileLayer(mapStyles[style].url, {
        attribution: mapStyles[style].attribution,
        maxZoom: 18,
        minZoom: 1
    });
    
    // Tester le chargement
    testTile.on('tileload', function(e) {
        console.log(`✅ Tuile de test chargée: ${e.coords.x}, ${e.coords.y}, ${e.coords.z}`);
    });
    
    testTile.on('tileerror', function(e) {
        console.error(`❌ Erreur de tuile de test: ${e.coords.x}, ${e.coords.y}, ${e.coords.z}`);
    });
    
    // Ajouter temporairement à la carte pour tester
    testTile.addTo(map);
    
    // Retirer après 3 secondes
    setTimeout(() => {
        map.removeLayer(testTile);
        console.log('🧪 Test terminé');
    }, 3000);
}

// Fonction pour ajouter les écouteurs d'événements pour les styles de carte
function addMapStyleListeners() {
    // Écouter les changements dans le modal de la carte
    document.addEventListener('change', function(e) {
        if (e.target.name === 'style_carte') {
            changeMapStyle(e.target.value);
        }
    });
    
    // Écouter les erreurs de la carte
    if (map) {
        map.on('tileerror', function(e) {
            console.warn('⚠️ Erreur de tuile détectée, tentative de rechargement...');
            setTimeout(() => {
                if (currentTileLayer) {
                    currentTileLayer.redraw();
                }
            }, 1000);
        });
    }
}

// Fonction pour recharger la carte en cas de problème
function reloadMap() {
    console.log('🔄 Rechargement de la carte...');
    
    if (map) {
        // Sauvegarder l'état actuel
        const currentCenter = map.getCenter();
        const currentZoom = map.getZoom();
        
        // Supprimer la carte actuelle
        map.remove();
        
        // Réinitialiser la carte
        setTimeout(() => {
            initializeMap();
            map.setView(currentCenter, currentZoom);
            console.log('✅ Carte rechargée avec succès');
        }, 500);
    }
}

// Fonction pour changer le style de la carte
function changeMapStyle(style) {
    if (map && mapStyles[style]) {
        console.log(`🔄 Changement du style de carte vers: ${style}`);
        
        // Supprimer la couche actuelle
        if (currentTileLayer) {
            map.removeLayer(currentTileLayer);
        }
        
        // Configuration spécifique selon le style
        let tileOptions = {
            attribution: mapStyles[style].attribution,
            maxZoom: 18,
            minZoom: 1,
            errorTileUrl: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
        };
        
        // Ajouter des options spécifiques selon le style
        if (style === 'standard' || style === 'dark') {
            tileOptions.subdomains = 'abc';
        } else if (style === 'satellite') {
            // Utiliser la première source satellite
            currentSatelliteSource = 0;
            const source = satelliteSources[currentSatelliteSource];
            tileOptions.attribution = source.attribution;
            tileOptions.maxZoom = 19;
        } else if (style === 'terrain') {
            tileOptions.subdomains = 'abc';
            tileOptions.maxZoom = 17;
        }
        
        // Ajouter la nouvelle couche avec gestion d'erreur
        if (style === 'satellite') {
            const source = satelliteSources[currentSatelliteSource];
            currentTileLayer = L.tileLayer(source.url, tileOptions).addTo(map);
        } else {
            currentTileLayer = L.tileLayer(mapStyles[style].url, tileOptions).addTo(map);
        }
        
        // Gérer les erreurs de chargement des tuiles
        currentTileLayer.on('tileerror', function(e) {
            console.warn('⚠️ Erreur de chargement de tuile:', e);
            
            if (style === 'satellite') {
                // Essayer la source satellite suivante
                setTimeout(tryNextSatelliteSource, 1000);
            } else {
                // Essayer de recharger la tuile
                setTimeout(() => {
                    currentTileLayer.redraw();
                }, 2000);
            }
        });
        
        // Gérer le succès de chargement
        currentTileLayer.on('tileload', function(e) {
            console.log('✅ Tuile chargée avec succès');
        });
        
        console.log(`✅ Style de carte changé vers: ${style}`);
        
        // Forcer le rafraîchissement de la carte
        setTimeout(() => {
            map.invalidateSize();
            currentTileLayer.redraw();
        }, 200);
    } else {
        console.error(`❌ Style de carte non trouvé: ${style}`);
    }
}

// ---------- Overlays d'export: titre dynamique, QR, nord ----------
function buildExportTitleText(filters = {}) {
    const statut = (filters.statut || document.getElementById('statut-filter')?.value || 'tous');
    const type = (filters.type_commerce || document.getElementById('type-commerce-filter')?.value || '');
    const zoneSelect = document.getElementById('zone-filter');
    const zoneId = (filters.zone_id || zoneSelect?.value || '');
    const zoneLabel = (zoneId && zoneSelect) ? (zoneSelect.options[zoneSelect.selectedIndex].text) : 'Toutes les zones';

    const parts = [];
    // Domaine
    if (type) {
        parts.push(`Types: ${type}`);
    } else {
        parts.push('Toutes activités');
    }
    // Statut de paiement
    const statutLabel = { paye: 'payants', impaye: 'non payants', tous: 'payants et non payants' }[statut] || statut;
    parts.push(statutLabel);
    // Zone
    parts.push(`— ${zoneLabel}`);

    return `Rapport cartographique — ${parts.join(' ')}`;
}

function updateExportTitle(filters = {}) {
    const el = document.getElementById('export-title');
    if (el) {
        el.textContent = buildExportTitleText(filters);
    }
}

function ensureQRCode() {
    const qrDiv = document.getElementById('qr-code');
    if (!qrDiv) return;
    // Nettoyer
    qrDiv.innerHTML = '';
    const url = window.location.origin + window.location.pathname + '?' + new URLSearchParams({
        statut: document.getElementById('statut-filter')?.value || 'tous',
        zone_id: document.getElementById('zone-filter')?.value || '',
        type_commerce: document.getElementById('type-commerce-filter')?.value || ''
    }).toString();
    try {
        // Générer QR (96px)
        new QRCode(qrDiv, { text: url, width: 96, height: 96, correctLevel: QRCode.CorrectLevel.M });
    } catch (e) {
        console.warn('QR code error:', e);
    }
}

function showExportOverlays() {
    updateExportTitle({});
    ensureQRCode();
    const header = document.getElementById('export-header');
    const title = document.getElementById('export-title');
    const north = document.getElementById('north-arrow');
    const qr = document.getElementById('qr-container');
    if (header) header.style.display = 'block';
    if (title) title.style.display = 'block';
    if (north) north.style.display = 'block';
    if (qr) qr.style.display = 'block';
}

function hideExportOverlays() {
    const header = document.getElementById('export-header');
    const title = document.getElementById('export-title');
    const north = document.getElementById('north-arrow');
    const qr = document.getElementById('qr-container');
    if (header) header.style.display = 'none';
    if (title) title.style.display = 'none';
    if (north) north.style.display = 'none';
    if (qr) qr.style.display = 'none';
}

// Mode rapport ON/OFF à l'écran
function toggleReportMode() {
    const btn = document.getElementById('reportModeBtn');
    const visible = document.getElementById('export-header').style.display !== 'none';
    if (visible) {
        hideExportOverlays();
        if (btn) btn.innerHTML = '<i class="fas fa-layer-group"></i> Mode rapport: OFF';
    } else {
        showExportOverlays();
        if (btn) btn.innerHTML = '<i class="fas fa-layer-group"></i> Mode rapport: ON';
    }
}


</script>
@endpush

 