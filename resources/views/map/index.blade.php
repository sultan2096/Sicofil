@extends('layouts.app')

@section('title', 'Carte des unités commerciales' . ($commune ? ' - Commune de ' . $commune->nom : ''))

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: calc(100vh - 200px);
        width: 100%;
        min-height: 500px;
    }
    .leaflet-control-attribution { display: none !important; }
    .legend {
        background: white;
        padding: 6px 10px;
        border-radius: 6px;
        box-shadow: 0 1px 5px rgba(0,0,0,0.2);
        max-width: 160px;
        font-size: 12px;
    }
    .legend h4 {
        margin: 0 0 6px;
        font-size: 12px;
    }
    .legend .legend-item {
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .legend i {
        width: 14px;
        height: 14px;
        float: left;
        margin-right: 6px;
        opacity: 0.8;
    }
    .map-info {
        background: rgba(255,255,255,0.9);
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    .commune-boundary {
        stroke: #ff0000;
        stroke-width: 3;
        fill: rgba(255,0,0,0.1);
    }
    
    /* Styles pour les popups personnalisées */
    .custom-popup .leaflet-popup-content-wrapper {
        border-radius: 10px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        padding: 0;
        overflow: hidden;
    }
    
    .custom-popup .leaflet-popup-content {
        margin: 0;
        line-height: 1.4;
    }
    
    .custom-popup .leaflet-popup-tip {
        background: white;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    /* Styles pour les icônes personnalisées */
    .custom-div-icon {
        background: rgba(255, 255, 255, 0.9);
        border: 2px solid rgba(0,0,0,0.1);
        border-radius: 50%;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .custom-div-icon:hover {
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    
    /* Animations pour les markers */
    @keyframes markerPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .marker-pulse {
        animation: markerPulse 2s infinite;
    }
    
    /* Styles pour les boutons dans les popups */
    .btn-group-sm .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* Amélioration de la lisibilité des badges */
    .badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
    }
    
    /* Style pour les alertes dans les popups */
    .alert {
        border: none;
        border-radius: 6px;
    }
    
    .alert-light {
        background-color: #f8f9fa;
        border-left: 3px solid #17a2b8;
    }

</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-map-marked-alt text-primary"></i> 
        Carte des unités commerciales{{ $commune ? ' - Commune de ' . $commune->nom : '' }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="centerMap()">
                <i class="fas fa-crosshairs"></i> Recentrer
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleZones()">
                <i class="fas fa-layer-group"></i> Zones fiscales
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCommune()">
                <i class="fas fa-border-all"></i> Limites communales
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="fitZonesToView()">
                <i class="fas fa-expand-arrows-alt"></i> Voir les zones
            </button>
            <a href="{{ route('map.import-zones') }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-upload"></i> Importer des zones (GeoJSON)
            </a>
        </div>
    </div>
</div>

@if(session('success') || session('info'))
<div class="alert alert-{{ session('success') ? 'success' : 'info' }} alert-dismissible fade show mb-3" role="alert">
    {{ session('success') ?? session('info') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
@endif

<!-- Informations sur la commune -->
<div class="map-info">
    <div class="row">
        <div class="col-md-8">
            <h5><i class="fas fa-info-circle text-info"></i> {{ $commune ? 'Commune de ' . $commune->nom : 'Carte' }}</h5>
            @if($commune)
            <p class="mb-1">
                <strong>Région :</strong> {{ $commune->region ?? '—' }} | <strong>Département :</strong> {{ $commune->departement ?? '—' }}
            </p>
            <p class="mb-0">
                <strong>Données affichées :</strong> zones fiscales et unités commerciales de la commune de {{ $commune->nom }} uniquement.
            </p>
            @if(auth()->user() && strtolower(auth()->user()->role ?? '') === 'admin_sicofil')
            <p class="mb-0 mt-1">
                <a href="{{ route('communes.index') }}" class="text-primary small"><i class="fas fa-city me-1"></i>Changer de commune (scope)</a>
            </p>
            @endif
            @else
            <p class="mb-0">Sélectionnez une commune (scope) pour afficher sa carte, ou consultez les zones importées.</p>
            @endif
        </div>
        <div class="col-md-4 text-end">
            <div class="row g-2">
                <div class="col-6">
                    <div class="card border-primary">
                        <div class="card-body text-center p-2">
                            <i class="fas fa-store text-primary"></i>
                            <div class="fw-bold">{{ count($unites) }}</div>
                            <small>Unités commerciales</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-success">
                        <div class="card-body text-center p-2">
                            <i class="fas fa-map-marked-alt text-success"></i>
                            <div class="fw-bold">{{ count($zones) }}</div>
                            <small>Zones fiscales</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="row mb-3">
    <div class="col-md-3">
        <select class="form-select form-select-sm" id="filter-zone">
            <option value="">Toutes les zones fiscales</option>
            @foreach($zones as $zone)
                <option value="{{ $zone['id'] }}">{{ $zone['nom'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select form-select-sm" id="filter-categorie">
            <option value="">Toutes les catégories</option>
            <option value="activite_commerciale">Activités commerciales</option>
            <option value="point_interet">Points d'intérêt</option>
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select form-select-sm" id="filter-nature">
            <option value="">Toutes les natures</option>
            <option value="Boutique">Boutique</option>
            <option value="Salon">Salon</option>
            <option value="Marché">Marché</option>
            <option value="Restaurant">Restaurant</option>
            <option value="Atelier">Atelier</option>
            <option value="Pharmacie">Pharmacie</option>
            <option value="Station">Station</option>
            <option value="Boulangerie">Boulangerie</option>
            <option value="Épicerie">Épicerie</option>
            <option value="Quincaillerie">Quincaillerie</option>
            <option value="Cybercafé">Cybercafé</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" class="form-control form-control-sm" id="search-box" placeholder="Rechercher une unité commerciale...">
    </div>
    <div class="col-md-2">
        <button class="btn btn-sm btn-success w-100" onclick="resetMap()">
            <i class="fas fa-sync"></i> Réinitialiser
        </button>
    </div>
</div>

<!-- Carte -->
<div id="map"></div>

<!-- Modal pour la prise de photo -->
<div id="photoModal" style="display:none; position:fixed; top:10%; left:50%; transform:translateX(-50%); z-index:9999; background:white; padding:20px; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.2);">
  <h5>Prendre une photo pour ce point</h5>
  <video id="video" width="320" height="240" autoplay style="border-radius:8px;"></video><br>
  <button id="snap" class="btn btn-primary btn-sm mt-2">Prendre la photo</button>
  <canvas id="canvas" width="320" height="240" style="display:none;"></canvas><br>
  <button id="savePhoto" class="btn btn-success btn-sm mt-2" style="display:none;">Enregistrer sur la carte</button>
  <button id="closePhotoModal" class="btn btn-secondary btn-sm mt-2">Annuler</button>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Données
const zones = JSON.parse(@json(json_encode($zones)));
const unites = JSON.parse(@json(json_encode($unites)));
const mapCenter = @json($mapCenter ?? [14.41, -16.77]);
const mapBounds = @json($mapBounds);
const communeId = @json($commune->id ?? null);
const communeNom = @json($commune->nom ?? '');

// Initialisation de la carte (centre selon la commune / zones)
const map = L.map('map', { attributionControl: false }).setView(mapCenter, 12);
if (mapBounds && mapBounds.length >= 2) {
    try {
        const [[south, west], [north, east]] = mapBounds;
        map.fitBounds([[south, west], [north, east]], { padding: [30, 30], maxZoom: 14 });
    } catch (e) { map.setView(mapCenter, 12); }
}

// Couche de base
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);

// Groupes de couches
const communeLayer = L.layerGroup().addTo(map);
const zonesLayer = L.layerGroup().addTo(map);
const unitesLayer = L.layerGroup().addTo(map);
// Ordre: commune (fond), loulibentegne, ndioukhthiorokh, sandiaracentre (premier plan)
const boundaryLayers = {
    zones: L.layerGroup().addTo(map),       // Zones plateforme (toutes communes, SHP importés)
    commune: L.layerGroup().addTo(map),
    loulibentegne: L.layerGroup().addTo(map),
    ndioukhthiorokh: L.layerGroup().addTo(map),
    sandiaracentre: L.layerGroup().addTo(map),
};
const boundaryCache = {}; // cache GeoJSON par layer
// Villages/amonts par zone fournis par le client
const zoneVillages = {
    loulibentegne: [
        'Kouthie Wolof','Kibik','Ngomène','Louly Mbafay','Louly Benteigné',
        'Louly Sindiane','Mbourokh','Nianiar','Louly Sindiane','Louly Ngogom'
    ],
    sandiaracentre: [
        'Faylar 1','Faylar 2','Sandiara Sérère','Sandiara Wolof',
        'Garage Diakher','Soukhem','Godaguène','Ndiouk Fissel'
    ],
    ndioukhthiorokh: [
        'Ndioukh Thiorokh','Samane','Louly Ndia','Keur Diba','Soussane','Ndollor'
    ]
};

// Ancienne limite commune (désactivée, on utilisera la couche GeoJSON 'commune')
const DRAW_OLD_COMMUNE = false;

// Palette de couleurs pour les zones (chaque commune voit ses propres zones avec des couleurs distinctes)
const ZONE_PALETTE = ['#90EE90', '#FFB6C1', '#87CEEB', '#DDA0DD', '#F0E68C', '#98FB98', '#FFA07A', '#B0E0E6', '#D8BFD8', '#F5DEB3'];
const zoneColorMap = {}; // nom de zone -> couleur (rempli à partir des zones de la commune)
zones.forEach((z, i) => {
    zoneColorMap[z.nom] = ZONE_PALETTE[i % ZONE_PALETTE.length];
    if (z.id) zoneColorMap[z.id] = zoneColorMap[z.nom];
});
// Rétrocompatibilité pour ancien code
const zoneColors = Object.assign({ 'Loulibentegne': '#90EE90', 'Sandiara Centre': '#FFB6C1', 'Keur Diba': '#87CEEB' }, zoneColorMap);

// Vérifier qu'une zone est dans les bornes de la commune (si mapBounds défini)
function isWithinCommuneBoundary(coordinates) {
    if (!mapBounds || mapBounds.length < 2) return true;
    const [[minLat, minLng], [maxLat, maxLng]] = mapBounds;
    for (const coord of coordinates) {
        const lat = Array.isArray(coord) ? (coord[1] ?? coord[0]) : coord;
        const lng = Array.isArray(coord) ? (coord[0] ?? coord[1]) : coord;
        if (lat < minLat || lat > maxLat || lng < minLng || lng > maxLng) return false;
    }
    return true;
}

// Ajouter les zones fiscales depuis PostGIS
let showZones = true;

// Charger les zones depuis l'API (ancien flux) — désactivé pour afficher uniquement les couches réimportées
const LOAD_OLD_ZONES = false;
if (LOAD_OLD_ZONES) {
    console.log('Chargement des zones depuis API PostGIS (ancien flux)...');
    fetch(BASE_URL + '/api/zones-fiscales')
      .then(res => res.json())
      .then(data => {
        console.log('Réponse API:', data);
        if (data.status === 'success' && data.zones) {
            console.log('Zones trouvées:', data.zones.length);
            
            data.zones.forEach((zone, index) => {
                console.log('Traitement zone:', zone.zone_name);

                try {
                    // La géométrie est maintenant déjà un objet JSON depuis l'API corrigée
                    const geometry = typeof zone.geometry === 'string' ? JSON.parse(zone.geometry) : zone.geometry;

                    // Mapper les couleurs
                    let color = '#CCCCCC';
                    let displayName = zone.zone_name;
                    if (zone.zone_name === 'sandiaracentre') {
                        color = zoneColors['Sandiara Centre'];
                        displayName = 'Sandiara Centre';
                    } else if (zone.zone_name === 'keurdiba') {
                        color = zoneColors['Keur Diba'];
                        displayName = 'Keur Diba';
                    } else if (zone.zone_name === 'loulibentegne') {
                        color = zoneColors['Loulibentegne'];
                        displayName = 'Loulibentegne';
                    }

                    // Fonction utilitaire: créé un L.polygon stylé
                    const makePolygon = (coords) => {
                        // coords est un anneau [ [lng,lat], ... ]
                        const latlngs = coords.map(c => [c[1], c[0]]);
                        
                        if (!isWithinCommuneBoundary(coords)) {
                            console.warn(`Zone ${zone.zone_name} hors bornes commune`);
                        }
                        
                        const poly = L.polygon(latlngs, {
                            color: '#2C3E50',
                            fillColor: color,
                            weight: 2,
                            fillOpacity: 0.6,
                            opacity: 0.8
                        });

                        // Propriétés nécessaires
                        poly.feature = poly.feature || { type: 'Feature', properties: {} };
                        poly.feature.properties = poly.feature.properties || {};
                        poly.feature.properties.zone_name = zone.zone_name;
                        poly.feature.properties.display_name = displayName;
                        poly._popupContent = `
                            <div style="min-width: 300px;">
                                <h6><strong>${displayName}</strong></h6>
                                <p>Superficie: ${Number(zone.area_km2 || 0).toFixed(2)} km²</p>
                                <button class="btn btn-sm btn-primary" onclick="map.fitBounds(this.closest('.leaflet-pane') ? L.polygon(${JSON.stringify(latlngs)}).getBounds() : map.getBounds())">
                                    Zoomer sur cette zone
                                </button>
                            </div>
                        `;

                        // Effets hover
                        poly.on('mouseover', function() {
                            this.setStyle({ weight: 4, fillOpacity: 0.8 });
                        });
                        poly.on('mouseout', function() {
                            this.setStyle({ weight: 2, fillOpacity: 0.6 });
                        });
                        // Click: popup normale
                        poly.on('click', function(e) {
                            this.bindPopup(this._popupContent).openPopup();
                        });
                        return poly;
                    };

                        // Ajout selon le type de géométrie
                        if (geometry.type === 'Polygon') {
                            // outer ring only (premier anneau)
                            const rings = geometry.coordinates && geometry.coordinates[0] ? [geometry.coordinates[0]] : [];
                            rings.forEach(ring => {
                                const poly = makePolygon(ring);
                                poly.zoneId = zone.zone_name; // Ajouter l'ID de la zone
                                poly.displayName = displayName; // Ajouter le nom d'affichage
                                allZonePolygons.push(poly); // Stocker pour le filtrage
                                zonesLayer.addLayer(poly);
                            });
                        } else if (geometry.type === 'MultiPolygon') {
                            const polys = geometry.coordinates || [];
                            polys.forEach(single => {
                                // single est [ [ring], [hole] ... ] → prendre le premier anneau
                                if (single && single[0]) {
                                    const poly = makePolygon(single[0]);
                                    poly.zoneId = zone.zone_name; // Ajouter l'ID de la zone
                                    poly.displayName = displayName; // Ajouter le nom d'affichage
                                    allZonePolygons.push(poly); // Stocker pour le filtrage
                                    zonesLayer.addLayer(poly);
                                }
                            });
                        }

                    console.log(`Zone ${displayName} ajoutée avec succès`);

                } catch (error) {
                    console.error('Erreur traitement zone:', zone.zone_name, error);
                }
            });
            
            console.log('Toutes les zones ont été traitées');
            
            // Ajuster automatiquement la vue pour voir toutes les zones
            setTimeout(() => {
                if (zonesLayer.getLayers().length > 0) {
                    const group = new L.featureGroup(zonesLayer.getLayers());
                    map.fitBounds(group.getBounds(), { padding: [20, 20] });
                    console.log('Vue ajustée pour voir toutes les zones');
                }
            }, 1000);
            
        } else {
            console.error('Aucune zone trouvée dans la réponse API');
        }
      })
      .catch(error => {
          console.error('Erreur chargement zones:', error);
      });
}

// Fonction pour ajouter une zone à la carte
function addZoneToMap(nom, geometrie, color, area_km2) {
    // Traiter les géométries MultiPolygon de PostGIS
    let coordinates = [];
    if (geometrie.type === 'MultiPolygon') {
        // Pour MultiPolygon, prendre le premier polygone
        coordinates = geometrie.coordinates[0][0].map(coord => [coord[1], coord[0]]);
    } else if (geometrie.type === 'Polygon') {
        // Pour Polygon simple
        coordinates = geometrie.coordinates[0].map(coord => [coord[1], coord[0]]);
    }
    
    if (coordinates.length > 0) {
        const polygon = L.polygon(coordinates, {
            color: '#2C3E50',
            fillColor: color,
            fillOpacity: 0.6,
            weight: 2,
            opacity: 0.8
        }).addTo(zonesLayer);
        
        // Ajouter des effets de survol
        polygon.on('mouseover', function() {
            this.setStyle({
                weight: 4,
                fillOpacity: 0.8,
                color: '#1A252F'
            });
        });
        
        polygon.on('mouseout', function() {
            this.setStyle({
                weight: 2,
                fillOpacity: 0.6,
                color: '#2C3E50'
            });
        });
        
        // Nom d'affichage
        const displayName = nom === 'sandiaracentre' ? 'Sandiara Centre' : 
                           nom === 'ndioukhthiorokh' ? 'Ndioukhthiorokh' : 
                           nom === 'loulibentegne' ? 'Loulibentegne' : nom;
        
        polygon.bindPopup(`
            <div style="min-width: 350px; max-width: 400px;">
                <div class="card border-0 shadow-sm">
                    <div class="card-header text-white p-3" style="background: linear-gradient(135deg, ${color}, ${color}dd);">
                        <h6 class="mb-0">
                            <i class="fas fa-map-marked-alt"></i> ${displayName}
                        </h6>
                        ${area_km2 ? `<small class="text-light"><i class="fas fa-ruler-combined"></i> ${area_km2.toFixed(2)} km²</small>` : ''}
                    </div>
                    <div class="card-body p-3">
                        <p class="mb-3 text-muted small">Zone fiscale de la commune de ${communeNom || '—'}</p>
                        <div class="text-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="map.fitBounds(L.geoJSON(${JSON.stringify(geometrie)}).getBounds())">
                                <i class="fas fa-search-plus"></i> Zoomer sur cette zone
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        console.log(`Zone ${displayName} ajoutée à la carte avec ${coordinates.length} coordonnées`);
    }
}

zonesLayer.addTo(map);

// Icônes personnalisées pour les unités commerciales selon leur nature
const getCustomIcon = (unite) => {
    let iconClass = 'fas fa-store';
    let color = '#28a745';
    
    // Icônes spécifiques selon la nature
    switch(unite.nature.toLowerCase()) {
        case 'boutique':
        case 'boutique générale':
            iconClass = 'fas fa-shopping-bag';
            color = '#28a745';
            break;
        case 'restaurant':
        case 'restaurant traditionnel':
            iconClass = 'fas fa-utensils';
            color = '#fd7e14';
            break;
        case 'salon de coiffure':
            iconClass = 'fas fa-cut';
            color = '#e83e8c';
            break;
        case 'atelier':
        case 'atelier mécanique':
        case 'atelier de couture':
            iconClass = 'fas fa-tools';
            color = '#6f42c1';
            break;
        case 'pharmacie':
            iconClass = 'fas fa-prescription-bottle-alt';
            color = '#dc3545';
            break;
        case 'station-service':
            iconClass = 'fas fa-gas-pump';
            color = '#ffc107';
            break;
        case 'boulangerie':
            iconClass = 'fas fa-bread-slice';
            color = '#d63384';
            break;
        case 'épicerie':
            iconClass = 'fas fa-shopping-cart';
            color = '#198754';
            break;
        case 'quincaillerie':
            iconClass = 'fas fa-hammer';
            color = '#495057';
            break;
        case 'cybercafé':
            iconClass = 'fas fa-wifi';
            color = '#0dcaf0';
            break;
        case 'marché':
        case 'marché hebdomadaire':
            iconClass = 'fas fa-store-alt';
            color = '#fd7e14';
            break;
        default:
            if (unite.categorie === 'point_interet') {
                iconClass = 'fas fa-map-pin';
                color = '#dc3545';
            }
    }
    
    return L.divIcon({
        html: `<i class="${iconClass}" style="color: ${color}; font-size: 18px; text-shadow: 1px 1px 1px rgba(0,0,0,0.5);"></i>`,
        iconSize: [30, 30],
        className: 'custom-div-icon',
        iconAnchor: [15, 15]
    });
};

// Ajouter les unités commerciales avec informations détaillées
const markers = [];
unites.forEach(unite => {
    if (unite.coordinates && unite.coordinates.lat && unite.coordinates.lng) {
        const marker = L.marker([unite.coordinates.lat, unite.coordinates.lng], {
            icon: getCustomIcon(unite)
        });
        
        // Popup avec toutes les informations de recensement
        const popupContent = `
            <div style="min-width: 320px; max-width: 400px;">
                <div class="card border-0">
                    <div class="card-header bg-primary text-white p-2">
                        <h6 class="mb-0">
                            <i class="fas fa-store"></i> 
                            ${unite.nom_proprietaire}
                            ${unite.prenom_proprietaire ? ' ' + unite.prenom_proprietaire : ''}
                        </h6>
                        ${unite.nom_commerce ? `<small class="text-light"><i class="fas fa-tag"></i> ${unite.nom_commerce}</small>` : ''}
                    </div>
                    <div class="card-body p-2">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <div class="text-center p-1 bg-light rounded">
                                    <i class="fas fa-store text-primary"></i><br>
                                    <small><strong>${unite.nature}</strong></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-1 bg-light rounded">
                                    <i class="fas fa-layer-group text-success"></i><br>
                                    <small><strong>${unite.zone_nom || 'Non assignée'}</strong></small>
                                </div>
                            </div>
                        </div>
                        
                        <table class="table table-sm table-borderless mb-2">
                            <tr>
                                <td class="p-1"><i class="fas fa-tag text-muted"></i> <small>Type:</small></td>
                                <td class="p-1"><small><span class="badge bg-secondary">${unite.type_commerce}</span></small></td>
                            </tr>
                            <tr>
                                <td class="p-1"><i class="fas fa-layer-group text-muted"></i> <small>Catégorie:</small></td>
                                <td class="p-1"><small><span class="badge bg-info">${unite.categorie === 'activite_commerciale' ? 'Commerce' : 'Point d\'intérêt'}</span></small></td>
                            </tr>
                            <tr>
                                <td class="p-1"><i class="fas fa-file-invoice-dollar text-muted"></i> <small>Taxe:</small></td>
                                <td class="p-1"><small><span class="badge bg-success">${unite.taxe_nom || 'Non définie'}</span></small></td>
                            </tr>
                            ${unite.telephone ? `
                            <tr>
                                <td class="p-1"><i class="fas fa-phone text-muted"></i> <small>Téléphone:</small></td>
                                <td class="p-1"><small><a href="tel:${unite.telephone}" class="text-decoration-none">${unite.telephone}</a></small></td>
                            </tr>` : ''}
                            ${unite.email ? `
                            <tr>
                                <td class="p-1"><i class="fas fa-envelope text-muted"></i> <small>Email:</small></td>
                                <td class="p-1"><small><a href="mailto:${unite.email}" class="text-decoration-none">${unite.email}</a></small></td>
                            </tr>` : ''}
                            ${unite.adresse ? `
                            <tr>
                                <td class="p-1"><i class="fas fa-map-marker-alt text-muted"></i> <small>Adresse:</small></td>
                                <td class="p-1"><small>${unite.adresse}</small></td>
                            </tr>` : ''}
                            ${unite.quartier ? `
                            <tr>
                                <td class="p-1"><i class="fas fa-home text-muted"></i> <small>Quartier:</small></td>
                                <td class="p-1"><small>${unite.quartier}</small></td>
                            </tr>` : ''}
                            ${unite.nombre_employes ? `
                            <tr>
                                <td class="p-1"><i class="fas fa-users text-muted"></i> <small>Employés:</small></td>
                                <td class="p-1"><small>${unite.nombre_employes} personne(s)</small></td>
                            </tr>` : ''}
                            ${unite.chiffre_affaires_mensuel ? `
                            <tr>
                                <td class="p-1"><i class="fas fa-money-bill-wave text-muted"></i> <small>CA mensuel:</small></td>
                                <td class="p-1"><small>${new Intl.NumberFormat('fr-FR').format(unite.chiffre_affaires_mensuel)} FCFA</small></td>
                            </tr>` : ''}
                        </table>
                        
                        ${unite.description ? `
                        <div class="alert alert-light p-2 mb-2">
                            <small><i class="fas fa-info-circle text-info"></i> <strong>Description:</strong><br>
                            ${unite.description}</small>
                        </div>` : ''}
                        
                        ${unite.observations ? `
                        <div class="alert alert-warning p-2 mb-2">
                            <small><i class="fas fa-sticky-note text-warning"></i> <strong>Observations:</strong><br>
                            ${unite.observations}</small>
                        </div>` : ''}
                        
                        <div class="border-top pt-2">
                            <div class="row g-1">
                                <div class="col-6">
                                    <div class="text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-user-tie"></i><br>
                                            <strong>Collecteur</strong><br>
                                            ${unite.collecteur_nom || 'Non renseigné'}
                                        </small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i><br>
                                            <strong>Recensé le</strong><br>
                                            ${unite.date_recensement ? new Date(unite.date_recensement).toLocaleDateString('fr-FR') : 'Non renseigné'}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-top pt-2 mt-2">
                            <div class="row g-1">
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-crosshairs"></i> 
                                        ${unite.coordinates.lat.toFixed(6)}
                                    </small>
                                </div>
                                <div class="col-6 text-end">
                                    <small class="text-muted">
                                        ${unite.coordinates.lng.toFixed(6)}
                                        <i class="fas fa-crosshairs"></i>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-2">
                            <div class="btn-group btn-group-sm">
                                <a href="/{{ auth()->user()->role }}/unites/${unite.id}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> Voir détails
                                </a>
                                ${unite.telephone ? `
                                <a href="tel:${unite.telephone}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-phone"></i> Appeler
                                </a>` : ''}
                                <button class="btn btn-outline-info btn-sm" onclick="centerOnMarker(${unite.coordinates.lat}, ${unite.coordinates.lng})">
                                    <i class="fas fa-crosshairs"></i> Centrer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent, {
            maxWidth: 400,
            className: 'custom-popup'
        });
        
        marker.unite = unite;
        markers.push(marker);
        marker.addTo(unitesLayer);
    }
});

// Variables de contrôle
let showCommune = true;
let allZonePolygons = []; // Stocker toutes les zones pour le filtrage

// Fonctions de contrôle
function centerMap() {
    map.setView(mapCenter, 12);
}

function toggleZones() {
    showZones = !showZones;
    if (showZones) {
        map.addLayer(zonesLayer);
    } else {
        map.removeLayer(zonesLayer);
    }
}

function toggleCommune() {
    showCommune = !showCommune;
    if (showCommune) {
        map.addLayer(communeLayer);
    } else {
        map.removeLayer(communeLayer);
    }
}

function fitZonesToView() {
    console.log('Ajustement de la vue pour voir les zones...');
    console.log('Nombre de couches dans zonesLayer:', zonesLayer.getLayers().length);
    
    if (zonesLayer.getLayers().length > 0) {
        const group = new L.featureGroup(editableGroup.getLayers());
        const bounds = group.getBounds();
        console.log('Limites des zones:', bounds);
        map.fitBounds(bounds, { padding: [50, 50] });
        console.log('Vue ajustée avec succès');
    } else {
        console.log('Aucune zone trouvée, centrage sur Sandiara');
        map.setView([14.4100, -16.7700], 12);
    }
}

function resetMap() {
    // Réinitialiser tous les filtres
    document.getElementById('filter-zone').value = '';
    document.getElementById('filter-categorie').value = '';
    document.getElementById('filter-nature').value = '';
    document.getElementById('search-box').value = '';
    
    // Réafficher toutes les zones
    zonesLayer.clearLayers();
    allZonePolygons.forEach(polygon => {
        zonesLayer.addLayer(polygon);
    });
    
    // Réafficher toutes les unités
    unitesLayer.clearLayers();
    markers.forEach(marker => {
        marker.addTo(unitesLayer);
    });
    
    // Mettre à jour le compteur
    document.querySelector('.map-info .fw-bold').textContent = markers.length;
    
    // Recentrer la carte sur Sandiara
    map.setView(mapCenter, 12);
}

// Filtrage amélioré
function applyFilters() {
    const zoneId = document.getElementById('filter-zone').value;
    const categorie = document.getElementById('filter-categorie').value;
    const nature = document.getElementById('filter-nature').value;
    const search = document.getElementById('search-box').value.toLowerCase();
    
    // Filtrer les zones d'abord
    zonesLayer.clearLayers();
    allZonePolygons.forEach(polygon => {
        let showZone = true;
        
        // Si une zone spécifique est sélectionnée, ne montrer que cette zone
        if (zoneId) {
            const selectedZone = zones.find(z => z.id == zoneId);
            if (selectedZone) {
                // Créer un mapping entre les noms d'affichage et les zoneId de l'API
                const zoneMapping = {
                    'Loulibentegne': 'loulibentegne',
                    'Sandiara Centre': 'sandiaracentre', 
                    'Keur Diba': 'keurdiba'
                };
                
                const expectedZoneId = zoneMapping[selectedZone.nom];
                showZone = polygon.zoneId === expectedZoneId;
            }
        }
        
        if (showZone) {
            zonesLayer.addLayer(polygon);
        }
    });
    
    // Filtrer les unités commerciales
    unitesLayer.clearLayers();
    let visibleCount = 0;
    
    markers.forEach(marker => {
        let show = true;
        
        if (zoneId && marker.unite.zone_nom !== zones.find(z => z.id == zoneId)?.nom) {
            show = false;
        }
        
        if (categorie && marker.unite.categorie !== categorie) {
            show = false;
        }
        
        if (nature && marker.unite.nature !== nature) {
            show = false;
        }
        
        if (search && 
            !marker.unite.nom_proprietaire.toLowerCase().includes(search) &&
            !marker.unite.type_commerce.toLowerCase().includes(search) &&
            !marker.unite.adresse.toLowerCase().includes(search)) {
            show = false;
        }
        
        if (show) {
            marker.addTo(unitesLayer);
            visibleCount++;
        }
    });
    
    // Mettre à jour le compteur
    document.querySelector('.map-info .fw-bold').textContent = visibleCount;
    
    // Ajuster la vue si une zone spécifique est sélectionnée
    if (zoneId && zonesLayer.getLayers().length > 0) {
        setTimeout(() => {
            const group = new L.featureGroup(zonesLayer.getLayers());
            map.fitBounds(group.getBounds(), { padding: [50, 50] });
        }, 100);
    }
}

// Événements de filtrage
document.getElementById('filter-zone').addEventListener('change', applyFilters);
document.getElementById('filter-categorie').addEventListener('change', applyFilters);
document.getElementById('filter-nature').addEventListener('change', applyFilters);
document.getElementById('search-box').addEventListener('input', applyFilters);

// Légende dynamique : uniquement les zones de la commune courante
const legendItemsHtml = zones.map(z => {
    const color = zoneColorMap[z.nom] || '#cccccc';
    return `<div class="legend-item">
        <span style="display:inline-block;width:14px;height:10px;background:${color};border:1px solid #2C3E50;margin-right:6px;vertical-align:middle;"></span>
        <span>${escapeHtml(z.nom)}</span>
    </div>`;
}).join('');
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
const legend = L.control({position: 'bottomright'});
legend.onAdd = function() {
    const div = L.DomUtil.create('div', 'legend');
    div.innerHTML = `
        <h4><i class="fas fa-map"></i> Légende</h4>
        ${legendItemsHtml || '<div class="legend-item text-muted small">Aucune zone (importez des zones GeoJSON/SHP)</div>'}
        <hr style="margin:6px 0;border:0;border-top:1px solid #eee;">
        <div class="legend-item"><i class="fas fa-store" style="color:#28a745;"></i> Unités commerciales</div>
        <div class="legend-item"><i class="fas fa-map-pin" style="color:#dc3545;"></i> Points d'intérêt</div>
    `;
    return div;
};
legend.addTo(map);

// Ajuster la taille de la carte au redimensionnement
window.addEventListener('resize', function() {
    map.invalidateSize();
});

// Initialiser l'affichage
console.log(`Carte initialisée: ${unites.length} unités commerciales dans ${zones.length} zones fiscales`);
console.log('Zones récupérées:', zones);

// Charger par défaut: zones plateforme (zones_fiscales, SHP importés toute commune) puis SHP PostGIS
const defaultBoundaryLayers = ['zones', 'commune', 'loulibentegne', 'ndioukhthiorokh', 'sandiaracentre'];
Promise.all(defaultBoundaryLayers.map(k => loadBoundaryLayer(k))).then(() => {
    // Vérifier si au moins une couche SHP a des données
    const totalFeatures = defaultBoundaryLayers.reduce((s, k) => {
        const fc = boundaryCache[k];
        return s + (fc && fc.features ? fc.features.length : 0);
    }, 0);
    if (totalFeatures === 0) {
        fetch(BASE_URL + '/api/map/boundaries-status', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(st => {
                const missing = Object.entries(st.layers || {}).filter(([_, v]) => !v.exists).map(([k]) => k);
                const importScript = (st.host === 'postgres') ? 'import_shp_sandiara_docker.bat' : 'import_shp_sandiara.bat';
                const msg = missing.length === 4
                    ? 'Aucune table SHP trouvée en base. L\'app utilise la base "' + (st.db || '?') + '" sur ' + (st.host || '?') + '. Importez avec .\\' + importScript
                    : 'Tables SHP manquantes: ' + missing.join(', ') + '. Exécutez .\\' + importScript;
                const alert = document.createElement('div');
                alert.className = 'alert alert-warning alert-dismissible fade show position-absolute top-0 start-50 translate-middle-x mt-3';
                alert.style.cssText = 'z-index: 2000; max-width: 90%;';
                alert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>Limites SHP absentes :</strong> ' + msg +
                    ' <a href="' + BASE_URL + '/api/map/boundaries-status" target="_blank" class="alert-link">Diagnostic</a>' +
                    ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.querySelector('.map-info')?.appendChild(alert);
            })
            .catch(() => {});
    }
});

// Redéfinir les boutons pour agir sur les nouvelles couches
function toggleCommune() {
    const group = boundaryLayers['commune'];
    if (map.hasLayer(group)) {
        map.removeLayer(group);
    } else {
        map.addLayer(group);
        fitToBoundaryIfAlone();
    }
}

function toggleZones() {
    const keys = ['sandiaracentre', 'ndioukhthiorokh', 'loulibentegne'];
    const anyOn = keys.some(k => map.hasLayer(boundaryLayers[k]) && boundaryLayers[k].getLayers().length > 0);
    if (anyOn) {
        keys.forEach(k => map.removeLayer(boundaryLayers[k]));
    } else {
        keys.forEach(k => {
            if (boundaryLayers[k].getLayers().length === 0) {
                loadBoundaryLayer(k);
            } else {
                map.addLayer(boundaryLayers[k]);
            }
        });
        fitToBoundaryIfAlone();
    }
}

// Debug: vérifier si les zones ont des géométries
zones.forEach((zone, index) => {
    console.log(`Zone ${index + 1}:`, zone.nom, 'Géométrie:', zone.geometrie);
});

// Fonction pour centrer la carte sur un marker
function centerOnMarker(lat, lng) {
    map.setView([lat, lng], 18);
}

// --- Prise de photo sur la carte ---
let lastLatLng = null;
let videoStream = null;

// Charger les markers photos déjà enregistrés
fetch(BASE_URL + '/map/photos')
    .then(response => response.json())
    .then(photos => {
        photos.forEach(photo => {
            var marker = L.marker([photo.lat, photo.lng]).addTo(map);
            marker.bindPopup('<img src="/storage/' + photo.image_path + '" width="200" style="border-radius:8px;"/>');
        });
    });

map.on('click', function(e) {
    lastLatLng = e.latlng;
    document.getElementById('photoModal').style.display = 'block';
    document.getElementById('canvas').style.display = 'none';
    document.getElementById('savePhoto').style.display = 'none';
    // Lance la webcam
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            videoStream = stream;
            document.getElementById('video').srcObject = stream;
        });
});

document.getElementById('snap').onclick = function() {
    var video = document.getElementById('video');
    var canvas = document.getElementById('canvas');
    canvas.getContext('2d').drawImage(video, 0, 0, 320, 240);
    canvas.style.display = 'block';
    document.getElementById('savePhoto').style.display = 'inline-block';
};

document.getElementById('savePhoto').onclick = function() {
    var canvas = document.getElementById('canvas');
    var imgData = canvas.toDataURL('image/png');
    fetch(BASE_URL + '/map/photo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            image: imgData,
            lat: lastLatLng.lat,
            lng: lastLatLng.lng
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            var marker = L.marker([data.photo.lat, data.photo.lng]).addTo(map);
            marker.bindPopup('<img src="/storage/' + data.photo.image_path + '" width="200" style="border-radius:8px;"/>').openPopup();
        }
        document.getElementById('photoModal').style.display = 'none';
        if(videoStream) videoStream.getTracks().forEach(track => track.stop());
        canvas.style.display = 'none';
        document.getElementById('savePhoto').style.display = 'none';
    });
};

document.getElementById('closePhotoModal').onclick = function() {
    document.getElementById('photoModal').style.display = 'none';
    if(videoStream) videoStream.getTracks().forEach(track => track.stop());
};

/**
 * Chargement d'une couche shapefile depuis l'endpoint GeoJSON
 */
function loadBoundaryLayer(layerKey) {
    const simplifyInput = document.getElementById('simplify-input');
    const simplify = simplifyInput ? (parseFloat(simplifyInput.value || '0') || 0) : 0;
    const url = BASE_URL + `/api/map/boundaries?layer=${encodeURIComponent(layerKey)}${simplify > 0 ? `&simplify=${simplify}` : ''}`;
    console.log('Chargement couche', layerKey, '→', url);
    return fetch(url, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(json => {
            if (!json.success) {
                throw new Error(json.error || 'Réponse invalide');
            }
            boundaryCache[layerKey] = json.data;
            const features = json.data && json.data.features ? json.data.features : [];
            const style = getBoundaryStyle(layerKey);
            const layer = L.geoJSON(json.data, { 
                style,
                onEachFeature: (feature, lyr) => {
                    const displayName = layerKey === 'zones' && feature.properties && feature.properties.nom
                        ? feature.properties.nom
                        : layerKey === 'sandiaracentre' ? 'Sandiara Centre'
                        : layerKey === 'ndioukhthiorokh' ? 'Ndioukh Thiorokh'
                        : layerKey === 'loulibentegne' ? 'Louly Benteigné'
                        : layerKey === 'commune' ? (communeNom ? 'Commune de ' + communeNom : 'Limites communales')
                        : (feature.properties && feature.properties.nom) || 'Zone';
                    const villages = zoneVillages[layerKey] || [];
                    const villagesHtml = villages.length ? `
                        <div class="mt-2">
                            <small class="text-muted"><i class="fas fa-list-ul"></i> Villages et amonts:</small>
                            <ul class="mb-0 mt-1" style="padding-left: 18px; max-height: 120px; overflow:auto;">
                                ${villages.map(v => `<li style="font-size: 12px;">${v}</li>`).join('')}
                            </ul>
                        </div>
                    ` : '';
                    lyr.bindPopup(`
                        <div style="min-width:280px;">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-layer-group me-2 text-secondary"></i>
                                <strong>${displayName}</strong>
                            </div>
                            ${villagesHtml}
                        </div>
                    `);
                }
            }).addTo(boundaryLayers[layerKey]);
            boundaryLayers[layerKey].__geojson = layer; // référence interne
            if (features.length > 0) {
                fitToBoundaryIfAlone();
            }
        })
        .catch(e => {
            console.error('Erreur chargement couche', layerKey, e);
            alert(`Erreur chargement couche ${layerKey}: ${e.message}`);
        });
}

function clearBoundaryLayer(layerKey) {
    boundaryLayers[layerKey].clearLayers();
}

function toggleBoundaryLayer(layerKey, visible) {
    if (visible) {
        if (!boundaryCache[layerKey]) {
            loadBoundaryLayer(layerKey);
        } else {
            const style = getBoundaryStyle(layerKey);
            const layer = L.geoJSON(boundaryCache[layerKey], { style }).addTo(boundaryLayers[layerKey]);
            boundaryLayers[layerKey].__geojson = layer;
            fitToBoundaryIfAlone();
        }
    } else {
        clearBoundaryLayer(layerKey);
    }
}

function reloadAllBoundaryLayers() {
    Object.keys(boundaryLayers).forEach(k => {
        const checked = document.getElementById(`layer-${k}`).checked;
        if (checked) {
            clearBoundaryLayer(k);
            loadBoundaryLayer(k);
        }
    });
}

function getBoundaryStyle(layerKey) {
    switch (layerKey) {
        case 'zones':
            return function(feature) {
                const nom = (feature.properties && feature.properties.nom) || '';
                const fillColor = zoneColorMap[nom] || '#818cf8';
                return { color: '#2C3E50', weight: 2, fillColor: fillColor, fillOpacity: 0.45 };
            };
        case 'commune':
            return { color: '#ff0000', weight: 3, fillColor: '#ff0000', fillOpacity: 0.08, dashArray: '6,6' };
        case 'sandiaracentre':
            return { color: '#2C3E50', weight: 2, fillColor: '#FFB6C1', fillOpacity: 0.45 };
        case 'ndioukhthiorokh':
            return { color: '#2C3E50', weight: 2, fillColor: '#87CEEB', fillOpacity: 0.45 };
        case 'loulibentegne':
            return { color: '#2C3E50', weight: 2, fillColor: '#90EE90', fillOpacity: 0.45 };
        default:
            return { color: '#555', weight: 2, fillOpacity: 0.2 };
    }
}

function fitToBoundaryIfAlone() {
    const active = Object.keys(boundaryLayers).filter(k => boundaryLayers[k].getLayers().length > 0);
    if (active.length === 1) {
        const k = active[0];
        const group = new L.featureGroup(boundaryLayers[k].getLayers());
        if (group.getLayers().length === 0) return;
        try {
            const bounds = group.getBounds();
            if (bounds && bounds.isValid && bounds.isValid()) {
                map.fitBounds(bounds, { padding: [20, 20] });
            }
        } catch (e) {
            console.warn('fitToBoundaryIfAlone: bounds invalides pour', k, e.message);
        }
    }
}
</script>
@endpush
@endsection