@extends('layouts.app')

@section('title', 'Suivi des Collecteurs - ' . (isset($commune) ? $commune->nom : 'Temps Réel'))

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary mb-1">
                        <i class="fas fa-map-marked-alt"></i> 
                        Suivi des Collecteurs en Temps Réel
                    </h2>
                    <p class="text-muted mb-0">Surveillance des itinéraires et positions — {{ isset($commune) ? $commune->nom : 'Commune' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success" onclick="refreshPositions()">
                        <i class="fas fa-sync-alt"></i> Actualiser
                    </button>
                    <button class="btn btn-info" onclick="toggleAutoRefresh()">
                        <i class="fas fa-play" id="auto-refresh-icon"></i> 
                        <span id="auto-refresh-text">Auto-actualisation</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre de contrôles carte (comme /map) -->
    <div class="row mb-2">
        <div class="col-md-8">
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="centerTrackingMap()">
                    <i class="fas fa-crosshairs"></i> Recentrer
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-zones-suivi" onclick="toggleTrackingBoundaries()">
                    <i class="fas fa-layer-group"></i> Zones fiscales
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-commune-suivi" onclick="toggleTrackingBoundaries()">
                    <i class="fas fa-border-all"></i> Limites communales
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="fitTrackingZonesToView()">
                    <i class="fas fa-expand-arrows-alt"></i> Voir les zones
                </button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row g-2">
                <div class="col-6">
                    <div class="card border-primary">
                        <div class="card-body text-center p-2">
                            <i class="fas fa-users text-primary"></i>
                            <div class="fw-bold">{{ count($collectors) }}</div>
                            <small>Collecteurs</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-success">
                        <div class="card-body text-center p-2">
                            <i class="fas fa-map-marked-alt text-success"></i>
                            <div class="fw-bold">{{ isset($zones) ? count($zones) : 0 }}</div>
                            <small>Zones fiscales</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Carte -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-map"></i> 
                        Carte de Suivi
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="tracking-map" style="height: 600px;"></div>
                </div>
            </div>
        </div>

        <!-- Liste des collecteurs -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> 
                        Collecteurs Actifs
                    </h5>
                </div>
                <div class="card-body">
                    <div id="collectors-list">
                        @foreach($collectors as $collector)
                        <div class="collector-item mb-3 p-3 border rounded" data-collector-id="{{ $collector->id }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $collector->name }}</h6>
                                    <small class="text-muted">{{ $collector->zone->nom ?? 'Zone non assignée' }}</small>
                                </div>
                                <div class="status-indicator">
                                    <span class="badge bg-secondary" id="status-{{ $collector->id }}">Hors ligne</span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    Dernière position: <span id="last-position-{{ $collector->id }}">Jamais</span>
                                </small>
                            </div>
                            <div class="mt-1">
                                <button class="btn btn-sm btn-outline-primary" onclick="showRoute({{ $collector->id }})">
                                    <i class="fas fa-route"></i> Voir l'itinéraire
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS et JS pour Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
let trackingMap = null;
let collectorMarkers = {};
let routeLayers = {};
let boundaryLayersGroup = null;
let autoRefreshInterval = null;
let isAutoRefresh = false;
const trackingCommuneId = {{ (int) (auth()->user()->commune_id ?? 0) }};
const trackingMapCenter = @json(isset($mapCenter) ? $mapCenter : [14.72, -17.45]);
const trackingMapBounds = @json(isset($mapBounds) ? $mapBounds : null);
const trackingBoundaryLayers = ['zones'];
const reverbKey = "{{ env('REVERB_APP_KEY') }}";
const reverbHost = "{{ env('REVERB_PUBLIC_HOST', 'localhost') }}";
const reverbPort = "{{ env('REVERB_PUBLIC_PORT', 8082) }}";

// Couleurs pour les différents statuts
const statusColors = {
    'online': '#28a745',
    'offline': '#6c757d',
    'collecting': '#007bff',
    'moving': '#ffc107',
    'idle': '#17a2b8'
};

document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    refreshPositions();
    initRealtimeTracking();
});

function initializeMap() {
    trackingMap = L.map('tracking-map').setView([trackingMapCenter[0], trackingMapCenter[1]], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(trackingMap);

    boundaryLayersGroup = L.layerGroup().addTo(trackingMap);
    loadBoundaryLayers();

    if (trackingMapBounds && trackingMapBounds.length >= 2) {
        try {
            var b = trackingMapBounds;
            trackingMap.fitBounds([b[0], b[1]], { padding: [30, 30] });
        } catch (e) { console.warn(e); }
    }
}

var trackingZoneColors = ['#FFB6C1', '#90EE90', '#87CEEB', '#DDA0DD', '#F0E68C', '#D2B48C'];
function getBoundaryStyle(layerKey, feature) {
    if (layerKey === 'zones' && feature && feature.properties && feature.properties.nom) {
        var nom = String(feature.properties.nom);
        var idx = 0;
        for (var i = 0; i < nom.length; i++) idx += nom.charCodeAt(i);
        var color = trackingZoneColors[Math.abs(idx) % trackingZoneColors.length];
        return { color: '#2C3E50', weight: 2, fillColor: color, fillOpacity: 0.5 };
    }
    if (layerKey === 'commune') return { color: '#ff0000', weight: 3, fillColor: '#ff0000', fillOpacity: 0.08, dashArray: '6,6' };
    return { color: '#2C3E50', weight: 2, fillOpacity: 0.3 };
}

function loadBoundaryLayers() {
    if (!trackingMap || !boundaryLayersGroup) return;
    trackingBoundaryLayers.forEach(function(layerKey) {
        fetch(BASE_URL + '/api/map/boundaries?layer=' + encodeURIComponent(layerKey))
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (json.success && json.data && json.data.features && json.data.features.length > 0) {
                    L.geoJSON(json.data, {
                        style: function(feature) { return getBoundaryStyle(layerKey, feature); }
                    }).addTo(boundaryLayersGroup);
                }
            })
            .catch(function(e) { console.warn('Boundary layer', layerKey, e); });
    });
}

function centerTrackingMap() {
    if (trackingMap) {
        trackingMap.setView([trackingMapCenter[0], trackingMapCenter[1]], 13);
        if (trackingMapBounds && trackingMapBounds.length >= 2) {
            try {
                var b = trackingMapBounds;
                trackingMap.fitBounds([b[0], b[1]], { padding: [30, 30] });
            } catch (e) {}
        }
    }
}
var trackingBoundariesVisible = true;
function toggleTrackingBoundaries() {
    if (!boundaryLayersGroup || !trackingMap) return;
    if (trackingBoundariesVisible) {
        trackingMap.removeLayer(boundaryLayersGroup);
    } else {
        boundaryLayersGroup.addTo(trackingMap);
    }
    trackingBoundariesVisible = !trackingBoundariesVisible;
}
function fitTrackingZonesToView() {
    if (!trackingMap || !boundaryLayersGroup) return;
    var layers = boundaryLayersGroup.getLayers();
    if (layers.length === 0) return;
    try {
        var group = L.featureGroup(layers);
        if (group.getBounds().isValid()) trackingMap.fitBounds(group.getBounds(), { padding: [30, 30] });
    } catch (e) { console.warn(e); }
}

function refreshPositions() {
    fetch(BASE_URL + '/tracking/positions')
        .then(response => response.json())
        .then(data => {
            updateCollectorPositions(data);
        })
        .catch(error => {
            console.error('Erreur lors du chargement des positions:', error);
        });
}

function updateCollectorPositions(trackingData) {
    // Supprimer les anciens marqueurs
    Object.values(collectorMarkers).forEach(marker => {
        trackingMap.removeLayer(marker);
    });
    collectorMarkers = {};

    // Ajouter les nouvelles positions
    Object.keys(trackingData).forEach(collectorId => {
        const positions = trackingData[collectorId];
        if (positions.length > 0) {
            const latestPosition = positions[0];
            const collector = latestPosition.collector;
            
            // Créer le marqueur
            const marker = L.marker([latestPosition.latitude, latestPosition.longitude], {
                icon: L.divIcon({
                    className: 'collector-marker',
                    html: `<div style="background-color: ${statusColors[latestPosition.activity] || statusColors.online}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            });

            // Ajouter le popup
            marker.bindPopup(`
                <div>
                    <h6>${collector.name}</h6>
                    <p><strong>Zone:</strong> ${collector.zone?.nom || 'Non assignée'}</p>
                    <p><strong>Statut:</strong> ${latestPosition.activity || 'Inconnu'}</p>
                    <p><strong>Dernière position:</strong> ${new Date(latestPosition.tracked_at).toLocaleString()}</p>
                    <p><strong>Précision:</strong> ${latestPosition.accuracy ? latestPosition.accuracy + 'm' : 'N/A'}</p>
                </div>
            `);

            marker.addTo(trackingMap);
            collectorMarkers[collectorId] = marker;

            // Mettre à jour le statut dans la liste
            updateCollectorStatus(collectorId, latestPosition);
        } else {
            // Pas de position récente
            updateCollectorStatus(collectorId, null);
        }
    });
}

function updateCollectorPositionRealtime(payload) {
    const collectorId = payload.collector_id;
    const lat = payload.lat;
    const lng = payload.lng;
    const timestamp = payload.timestamp;

    if (!trackingMap || !collectorId || !lat || !lng) return;

    // Retirer ancien marqueur
    if (collectorMarkers[collectorId]) {
        trackingMap.removeLayer(collectorMarkers[collectorId]);
    }

    const marker = L.marker([lat, lng], {
        icon: L.divIcon({
            className: 'collector-marker',
            html: `<div style="background-color: ${statusColors['online']}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        })
    });

    marker.addTo(trackingMap);
    collectorMarkers[collectorId] = marker;

    const statusElement = document.getElementById(`status-${collectorId}`);
    const lastPositionElement = document.getElementById(`last-position-${collectorId}`);
    if (statusElement) {
        statusElement.textContent = 'En ligne';
        statusElement.className = 'badge bg-success';
    }
    if (lastPositionElement) {
        lastPositionElement.textContent = new Date(timestamp).toLocaleString();
    }
}

function initRealtimeTracking() {
    if (!trackingCommuneId || !reverbKey) return;

    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
    });

    window.Echo.channel(`tracking.commune.${trackingCommuneId}`)
        .listen('.position.updated', (payload) => {
            updateCollectorPositionRealtime(payload);
        });
}

function updateCollectorStatus(collectorId, position) {
    const statusElement = document.getElementById(`status-${collectorId}`);
    const lastPositionElement = document.getElementById(`last-position-${collectorId}`);
    
    if (position) {
        const status = position.activity || 'online';
        statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        statusElement.className = `badge bg-${status === 'online' ? 'success' : status === 'collecting' ? 'primary' : status === 'moving' ? 'warning' : 'info'}`;
        
        lastPositionElement.textContent = new Date(position.tracked_at).toLocaleString();
    } else {
        statusElement.textContent = 'Hors ligne';
        statusElement.className = 'badge bg-secondary';
        lastPositionElement.textContent = 'Jamais';
    }
}

function showRoute(collectorId) {
    // Supprimer l'ancien itinéraire
    if (routeLayers[collectorId]) {
        trackingMap.removeLayer(routeLayers[collectorId]);
    }

    fetch(BASE_URL + `/tracking/collector/${collectorId}?hours=24`)
        .then(response => response.json())
        .then(route => {
            if (route.length > 1) {
                const coordinates = route.map(point => [point.latitude, point.longitude]);
                
                const polyline = L.polyline(coordinates, {
                    color: '#007bff',
                    weight: 3,
                    opacity: 0.7
                }).addTo(trackingMap);

                routeLayers[collectorId] = polyline;
                
                // Centrer la carte sur l'itinéraire
                trackingMap.fitBounds(polyline.getBounds());
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement de l\'itinéraire:', error);
        });
}

function toggleAutoRefresh() {
    if (isAutoRefresh) {
        clearInterval(autoRefreshInterval);
        isAutoRefresh = false;
        document.getElementById('auto-refresh-icon').className = 'fas fa-play';
        document.getElementById('auto-refresh-text').textContent = 'Auto-actualisation';
    } else {
        autoRefreshInterval = setInterval(refreshPositions, 30000); // 30 secondes
        isAutoRefresh = true;
        document.getElementById('auto-refresh-icon').className = 'fas fa-pause';
        document.getElementById('auto-refresh-text').textContent = 'Arrêter';
    }
}
</script>

<style>
.collector-marker {
    background: transparent !important;
    border: none !important;
}

.collector-item {
    transition: all 0.3s ease;
}

.collector-item:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.status-indicator .badge {
    font-size: 0.75rem;
}
</style>

@endsection
