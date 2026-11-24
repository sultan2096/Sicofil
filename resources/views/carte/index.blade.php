@extends('layouts.app')

@section('title', 'Carte des Zones Fiscales')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-map-marked-alt"></i>
                        Carte des Zones Fiscales - Sandiara
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-info btn-sm" onclick="refreshMap()">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="fitBounds()">
                            <i class="fas fa-expand-arrows-alt"></i> Ajuster la vue
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 600px; width: 100%;"></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Légende des zones :</h6>
                            <div class="d-flex flex-wrap">
                                <span class="badge badge-primary mr-2 mb-1" style="background-color: #007bff;">
                                    <i class="fas fa-map-marker-alt"></i> Sandiara Centre
                                </span>
                                <span class="badge badge-success mr-2 mb-1" style="background-color: #28a745;">
                                    <i class="fas fa-map-marker-alt"></i> Ndioukhthiorokh
                                </span>
                                <span class="badge badge-warning mr-2 mb-1" style="background-color: #ffc107; color: #000;">
                                    <i class="fas fa-map-marker-alt"></i> Loulibentegne
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>Villages par zone :</h6>
                            <div class="villages-info">
                                <small class="text-muted">
                                    <strong>Sandiara Centre:</strong> Garage Diakher, Soukhem, Faylar 1, Ndioukh Fissel, Faylar 2, Sandiara Wolof, Sandiara Serere, Godaguene<br>
                                    <strong>Ndioukhthiorokh:</strong> Ndollor, Samane, Keur Diba, Soussane, Ndioukh Thiorokh, Louli Ndia<br>
                                    <strong>Loulibentegne:</strong> Kouthie Wolof, Louli Ngogom, Louli Mbafay, Ngomene, Kibik, Louli Sindiane, Louli Bentegne, Mbourokh, Nianiar
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>Informations :</h6>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Cliquez sur une zone pour voir ses détails. Utilisez la molette pour zoomer.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les détails de zone -->
<div class="modal fade" id="zoneModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de la zone</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="zoneDetails">
                    <!-- Contenu dynamique -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map;
let zoneLayers = [];

// Couleurs pour chaque zone
const zoneColors = {
    'sandiaracentre': '#007bff',
    'ndioukhthiorokh': '#28a745', 
    'loulibentegne': '#ffc107'
};

// Noms d'affichage pour chaque zone
const zoneNames = {
    'sandiaracentre': 'Sandiara Centre',
    'ndioukhthiorokh': 'Ndioukhthiorokh',
    'loulibentegne': 'Loulibentegne'
};

// Villages par zone
const zoneVillages = {
    'sandiaracentre': 'Garage Diakher, Soukhem, Faylar 1, Ndioukh Fissel, Faylar 2, Sandiara Wolof, Sandiara Serere, Godaguene',
    'ndioukhthiorokh': 'Ndollor, Samane, Keur Diba, Soussane, Ndioukh Thiorokh, Louli Ndia',
    'loulibentegne': 'Kouthie Wolof, Louli Ngogom, Louli Mbafay, Ngomene, Kibik, Louli Sindiane, Louli Bentegne, Mbourokh, Nianiar'
};

// Initialiser la carte
function initMap() {
    // Centre de Sandiara
    map = L.map('map').setView([14.43, -16.79], 13);
    
    // Ajouter la couche de tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Charger les zones fiscales
    loadZones();
}

// Charger les zones depuis l'API
async function loadZones() {
    try {
        const response = await fetch(BASE_URL + '/api/carte-zones-data');
        const data = await response.json();
        
        if (data.status === 'success') {
            displayZones(data.zones);
        } else {
            console.error('Erreur lors du chargement des zones:', data.message);
            showAlert('Erreur lors du chargement des zones fiscales', 'danger');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur de connexion au serveur', 'danger');
    }
}

// Afficher les zones sur la carte
function displayZones(zones) {
    // Nettoyer les couches existantes
    zoneLayers.forEach(layer => map.removeLayer(layer));
    zoneLayers = [];
    
    let bounds = L.latLngBounds();
    
    zones.forEach(zone => {
        try {
            const geometry = JSON.parse(zone.geometry);
            const color = zoneColors[zone.zone_name] || '#6c757d';
            const name = zoneNames[zone.zone_name] || zone.zone_name;
            
            // Créer la couche GeoJSON avec des limites plus visibles
            const layer = L.geoJSON(geometry, {
                style: {
                    fillColor: color,
                    weight: 4,  // Augmenter l'épaisseur des bordures
                    opacity: 1,
                    color: '#000000',  // Bordures noires pour plus de contraste
                    fillOpacity: 0.3,  // Réduire l'opacité pour voir les limites
                    dashArray: '5, 5'  // Lignes pointillées pour les limites
                },
                onEachFeature: function(feature, layer) {
                    const villages = zoneVillages[zone.zone_name] || 'Non spécifié';
                    
                    // Ajouter un popup plus détaillé
                    const popupContent = `
                        <div class="text-center" style="min-width: 300px;">
                            <h6><strong>${name}</strong></h6>
                            <hr>
                            <p class="mb-2"><strong>Superficie:</strong> ${zone.area_km2 ? zone.area_km2.toFixed(2) : 'N/A'} km²</p>
                            <p class="mb-2"><strong>Villages concernés:</strong></p>
                            <small class="text-muted">${villages}</small>
                            <hr>
                            <button class="btn btn-sm btn-primary" onclick="showZoneDetails('${zone.zone_name}', '${name}', ${zone.area_km2 || 0}, '${villages}')">
                                <i class="fas fa-info-circle"></i> Détails complets
                            </button>
                        </div>
                    `;
                    layer.bindPopup(popupContent);
                    
                    // Ajouter un événement de clic
                    layer.on('click', function() {
                        showZoneDetails(zone.zone_name, name, zone.area_km2 || 0, villages);
                    });
                    
                    // Effet de survol
                    layer.on('mouseover', function() {
                        this.setStyle({
                            weight: 6,
                            fillOpacity: 0.5
                        });
                    });
                    
                    layer.on('mouseout', function() {
                        this.setStyle({
                            weight: 4,
                            fillOpacity: 0.3
                        });
                    });
                }
            });
            
            layer.addTo(map);
            zoneLayers.push(layer);
            
            // Étendre les limites de la carte
            bounds.extend(layer.getBounds());
            
        } catch (error) {
            console.error('Erreur lors du traitement de la zone:', zone.zone_name, error);
        }
    });
    
    // Ajuster la vue pour afficher toutes les zones
    if (zones.length > 0) {
        map.fitBounds(bounds, { padding: [20, 20] });
    }
}

// Afficher les détails d'une zone
function showZoneDetails(zoneName, displayName, area, villages) {
    const details = `
        <div class="text-center">
            <h5><strong>${displayName}</strong></h5>
            <hr>
            <div class="row">
                <div class="col-6">
                    <strong>Zone:</strong><br>
                    <span class="badge badge-primary">${zoneName}</span>
                </div>
                <div class="col-6">
                    <strong>Superficie:</strong><br>
                    <span class="text-info">${area.toFixed(2)} km²</span>
                </div>
            </div>
            <hr>
            <div class="text-left">
                <h6><strong>Villages concernés:</strong></h6>
                <p class="text-muted">${villages}</p>
            </div>
            <hr>
            <p class="text-muted">
                <i class="fas fa-map-marker-alt"></i>
                Zone fiscale de la commune de Sandiara
            </p>
        </div>
    `;
    
    document.getElementById('zoneDetails').innerHTML = details;
    $('#zoneModal').modal('show');
}

// Actualiser la carte
function refreshMap() {
    showAlert('Actualisation de la carte...', 'info');
    loadZones();
}

// Ajuster la vue pour afficher toutes les zones
function fitBounds() {
    if (zoneLayers.length > 0) {
        let bounds = L.latLngBounds();
        zoneLayers.forEach(layer => {
            bounds.extend(layer.getBounds());
        });
        map.fitBounds(bounds, { padding: [20, 20] });
    }
}

// Afficher une alerte
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Supprimer automatiquement après 3 secondes
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 3000);
}

// Initialiser la carte quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});
</script>
@endsection

@section('styles')
<style>
#map {
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.leaflet-popup-content {
    margin: 8px 12px;
    line-height: 1.4;
}

.leaflet-popup-content h6 {
    margin-bottom: 8px;
    color: #495057;
}

.badge {
    font-size: 0.75em;
}

.card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.modal-body {
    padding: 1.5rem;
}

.text-info {
    color: #17a2b8 !important;
}

.villages-info {
    max-height: 120px;
    overflow-y: auto;
    font-size: 0.8rem;
}

.leaflet-popup-content {
    margin: 8px 12px;
    line-height: 1.4;
    max-width: 350px;
}

.leaflet-popup-content h6 {
    margin-bottom: 8px;
    color: #495057;
    font-weight: bold;
}

/* Améliorer la visibilité des limites */
.leaflet-interactive {
    stroke-width: 4px !important;
    stroke: #000000 !important;
    stroke-dasharray: 5, 5 !important;
}
</style>
@endsection
