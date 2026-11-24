@extends('layouts.app')

@section('title', 'Tableau de Bord Géomarketing')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/MarkerCluster.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/MarkerCluster.Default.css" />
    <style>
        #map {
            height: 600px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        #mapLoader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.9);
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            display: none;
            z-index: 1000;
        }
        .error-box {
            display: none;
            margin-bottom: 10px;
        }
        .filters-card {
            margin-bottom: 20px;
        }
        .legend {
            padding: 10px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            line-height: 1.5;
        }
        .legend i {
            width: 18px;
            height: 18px;
            float: left;
            margin-right: 8px;
            opacity: 0.7;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Analyse Géomarketing</h1>

    <div class="card shadow mb-4 filters-card">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtres d'Analyse</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="natureActivite">Nature de l'activité</label>
                        <select id="natureActivite" class="form-control">
                            <option value="">Toutes les natures</option>
                            @foreach($naturesActivites as $nature)
                                <option value="{{ $nature->id }}">{{ $nature->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="typeCommerce">Type de commerce</label>
                        <select id="typeCommerce" class="form-control">
                            <option value="">Tous les types</option>
                             @foreach($typesCommerce as $type)
                                <option value="{{ $type->id }}" data-nature="{{ $type->nature_activite_id }}">{{ $type->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="togglePopulation" checked>
                            <label class="form-check-label" for="togglePopulation"><i class="fas fa-users me-1"></i>Population</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="toggleRoads" checked>
                            <label class="form-check-label" for="toggleRoads"><i class="fas fa-road me-1"></i>Routes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="toggleBati" checked>
                            <label class="form-check-label" for="toggleBati"><i class="fas fa-city me-1"></i>Bâti</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="toggleHeatmap">
                            <label class="form-check-label" for="toggleHeatmap"><i class="fas fa-fire me-1"></i>Heatmap population</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="toggleCombined">
                            <label class="form-check-label" for="toggleCombined"><i class="fas fa-layer-group me-1"></i>Carte multi-critères</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Carte de Densité Commerciale</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger error-box" role="alert" id="mapError">
                        Une erreur est survenue lors du chargement des données. Veuillez réessayer.
                    </div>
                    <div id="mapLoader">
                        <i class="fas fa-spinner fa-spin"></i> Chargement des données…
                    </div>
                    <div id="map"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistiques et Opportunités</h6>
                </div>
                <div class="card-body" id="stats-container">
                    <p class="text-center">Sélectionnez des filtres pour analyser la densité et découvrir des opportunités.</p>
                    <hr>
                    <h6 class="m-0 font-weight-bold text-primary mb-2">Top zones (unités)</h6>
                    <canvas id="zoneChart" height="220"></canvas>
                    <hr>
                    <h6 class="m-0 font-weight-bold text-primary mb-2">Propositions d'investissement</h6>
                    <ul class="list-group small" id="recoList">
                        <li class="list-group-item text-muted">Activez des filtres pour voir les propositions.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/leaflet.markercluster.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet-easyprint@2.1.9/dist/bundle.js"></script>
    <script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialisation de la carte
            const map = L.map('map').setView([14.44, -16.79], 13); // Centre de Sandiara

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            let geojsonLayer;
            let legend = L.control({position: 'bottomright'});
            // Définir des limites maximales correspondant à la commune de Sandiara (approx.)
            const sandiaraBounds = L.latLngBounds([[14.425, -16.805],[14.455, -16.775]]);
            map.setMaxBounds(sandiaraBounds);
            map.fitBounds(sandiaraBounds);
            const mapError = document.getElementById('mapError');
            const mapLoader = document.getElementById('mapLoader');
            let zoneChart = null;
            // Overlays
            let populationLayer = null;
            let roadsLayer = null;
            let batiLayer = null;
            let heatmapLayer = null;
            let combinedLayer = null;
            let combinedLegend = null;
            let batiLegend = null;
            // Contrôle des couches
            const layersCtrl = L.control.layers({}, {}, { collapsed: false }).addTo(map);

            legend.onAdd = function (map) {
                var div = L.DomUtil.create('div', 'info legend'),
                    grades = [0, 10, 20, 50, 100, 200, 500, 1000],
                    labels = [];

                div.innerHTML += '<strong>Nombre d\'unités</strong><br>';
                for (var i = 0; i < grades.length; i++) {
                    div.innerHTML +=
                        '<i style="background:' + getColor(grades[i] + 1) + '"></i> ' +
                        grades[i] + (grades[i + 1] ? '&ndash;' + grades[i + 1] + '<br>' : '+');
                }

                return div;
            };

            legend.addTo(map);

            // Contrôle d'export (image/PDF)
            L.easyPrint({
                title: 'Exporter la carte',
                position: 'topleft',
                sizeModes: ['A4Landscape','A4Portrait'],
                filename: 'carte-geomarketing'
            }).addTo(map);

            function getColor(d) {
                return d > 1000 ? '#800026' :
                       d > 500  ? '#BD0026' :
                       d > 200  ? '#E31A1C' :
                       d > 100  ? '#FC4E2A' :
                       d > 50   ? '#FD8D3C' :
                       d > 20   ? '#FEB24C' :
                       d > 10   ? '#FED976' :
                                  '#FFEDA0';
            }

            function style(feature) {
                return {
                    fillColor: getColor(feature.properties.nombre_unites),
                    weight: 2,
                    opacity: 1,
                    color: 'white',
                    dashArray: '3',
                    fillOpacity: 0.7
                };
            }

            function highlightFeature(e) {
                var layer = e.target;
                layer.setStyle({
                    weight: 5,
                    color: '#666',
                    dashArray: '',
                    fillOpacity: 0.7
                });
                if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                    layer.bringToFront();
                }
                updateStats(layer.feature.properties);
            }

            function resetHighlight(e) {
                geojsonLayer.resetStyle(e.target);
                updateStats(); // Clear stats
            }

            function zoomToFeature(e) {
                map.fitBounds(e.target.getBounds());
            }

            function onEachFeature(feature, layer) {
                layer.on({
                    mouseover: highlightFeature,
                    mouseout: resetHighlight,
                    click: zoomToFeature
                });
            }

            function updateMap(data) {
                if (geojsonLayer) {
                    map.removeLayer(geojsonLayer);
                }

                const features = data.map(zone => ({
                    type: 'Feature',
                    properties: {
                        id: zone.id,
                        nom: zone.nom,
                        nombre_unites: zone.nombre_unites
                    },
                    geometry: zone.geometrie
                }));

                geojsonLayer = L.geoJSON(features, {style: style, onEachFeature: onEachFeature}).addTo(map);
                // Ajuster la carte sur les limites des zones et limiter la navigation à ces limites
                const bounds = geojsonLayer.getBounds();
                if (bounds && bounds.isValid()) {
                    map.fitBounds(bounds.pad(0.05));
                    map.setMaxBounds(bounds.pad(0.15));
                }
                updateChart(features);
            }

            function updateStats(properties) {
                const container = document.getElementById('stats-container');
                if (properties) {
                    container.innerHTML = `<h5>${properties.nom}</h5>
                                         <p><strong>Unités commerciales:</strong> ${properties.nombre_unites}</p>
                                         <hr>
                                         <h6>Analyse:</h6>
                                         <p>${getOpportunityText(properties.nombre_unites)}</p>`;
                } else {
                    container.innerHTML = '<p class="text-center">Survolez une zone pour voir les détails.</p>';
                }
            }
            
            function getOpportunityText(count) {
                if (count < 10) {
                    return "<span class='text-success'>Forte opportunité :</span> Zone sous-développée avec un grand potentiel d'investissement pour de nouveaux commerces.";
                } else if (count < 50) {
                    return "<span class='text-info'>Opportunité modérée :</span> Potentiel de croissance pour des commerces spécialisés ou complémentaires.";
                } else if (count < 200) {
                    return "<span class='text-warning'>Zone compétitive :</span> Le marché est bien desservi. Une analyse de niche est recommandée avant tout investissement.";
                } else {
                    return "<span class='text-danger'>Zone saturée :</span> Très forte concurrence. L'implantation de nouveaux commerces est risquée sans une offre très différenciée.";
                }
            }

            function fetchData() {
                const natureId = document.getElementById('natureActivite').value;
                const typeId = document.getElementById('typeCommerce').value;
                
                let url = `{{ route('web.geomarketing.data') }}?`;
                if (typeId) {
                    url += `type_commerce_id=${typeId}`;
                } else if (natureId) {
                    url += `nature_activite_id=${natureId}`;
                }
                // Afficher loader et masquer erreurs
                mapError.style.display = 'none';
                mapLoader.style.display = 'block';

                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest' // Important pour que Laravel reconnaisse la requête comme AJAX
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(json => {
                    updateMap(json.data);
                    updateStats(); // Clear stats on new data load
                    mapLoader.style.display = 'none';
                    // Charger recommandations après la MAJ de la carte
                    fetchRecommendations();
                })
                .catch(error => {
                    console.error('Erreur de chargement des données:', error);
                    mapLoader.style.display = 'none';
                    mapError.style.display = 'block';
                });
            }
            
            // Gestion des filtres
            const natureSelect = document.getElementById('natureActivite');
            const typeSelect = document.getElementById('typeCommerce');
            const typeOptions = Array.from(typeSelect.options);

            natureSelect.addEventListener('change', function() {
                const selectedNature = this.value;
                typeSelect.value = ''; // Reset type filter

                // Filtrer les options de type en fonction de la nature
                typeOptions.forEach(option => {
                    if (option.value === '') {
                        option.style.display = 'block';
                        return;
                    }
                    const natureOfOption = option.getAttribute('data-nature');
                    if (!selectedNature || natureOfOption === selectedNature) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                fetchData();
            });

            typeSelect.addEventListener('change', fetchData);

            // Chargement initial des données + overlays
            fetchData();
            document.getElementById('togglePopulation').dispatchEvent(new Event('change'));
            document.getElementById('toggleRoads').dispatchEvent(new Event('change'));
            document.getElementById('toggleBati').dispatchEvent(new Event('change'));
            // Heatmap off by default; you can enable it by default by uncommenting:
            // document.getElementById('toggleHeatmap').checked = true; document.getElementById('toggleHeatmap').dispatchEvent(new Event('change'));
            // Combined density off by default
            // document.getElementById('toggleCombined').checked = true; document.getElementById('toggleCombined').dispatchEvent(new Event('change'));

            function updateChart(features) {
                try {
                    const ctx = document.getElementById('zoneChart');
                    if (!ctx) return;
                    const rows = features
                        .map(f => ({ nom: f.properties.nom, count: f.properties.nombre_unites }))
                        .sort((a,b) => b.count - a.count)
                        .slice(0, 10);
                    const labels = rows.map(r => r.nom);
                    const data = rows.map(r => r.count);

                    const cfg = {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Unités par zone',
                                data,
                                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                                borderColor: 'rgba(37, 99, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { ticks: { color: '#1f2937', font: { size: 10 } } },
                                y: { beginAtZero: true, ticks: { color: '#1f2937' } }
                            },
                            plugins: { legend: { display: false } }
                        }
                    };

                    if (zoneChart) {
                        zoneChart.destroy();
                    }
                    zoneChart = new Chart(ctx, cfg);
                } catch (e) {
                    console.warn('Chart update skipped:', e);
                }
            }

            // ===== Overlays: population, roads, bati =====
            function fetchHeatmap() {
                const url = `{{ route('web.geomarketing.heatmap') }}`;
                return fetch(url, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }})
                    .then(r => r.json())
                    .then(json => {
                        if (heatmapLayer) { map.removeLayer(heatmapLayer); heatmapLayer = null; }
                        if (!json.points || json.points.length === 0) return;
                        // json.points: [lat, lng, intensity]
                        heatmapLayer = L.heatLayer(json.points, { radius: 25, blur: 15, maxZoom: 17, minOpacity: 0.3 }).addTo(map);
                        layersCtrl.addOverlay(heatmapLayer, 'Heatmap population');
                    })
                    .catch(e => console.warn('Heatmap overlay error', e));
            }

            function colorForScore(s) {
                // s is 0..100
                if (s >= 80) return '#800026';
                if (s >= 60) return '#BD0026';
                if (s >= 45) return '#E31A1C';
                if (s >= 30) return '#FC4E2A';
                if (s >= 20) return '#FD8D3C';
                if (s >= 10) return '#FEB24C';
                if (s > 0)   return '#FED976';
                return '#FFEDA0';
            }

            function ensureCombinedLegend() {
                if (combinedLegend) return combinedLegend;
                combinedLegend = L.control({ position: 'bottomright' });
                combinedLegend.onAdd = function(map){
                    const div = L.DomUtil.create('div', 'info legend');
                    const grades = [0,10,20,30,45,60,80];
                    div.innerHTML += '<strong>Score multi-critères</strong><br>';
                    for (let i = 0; i < grades.length; i++) {
                        const from = grades[i];
                        const to = grades[i + 1];
                        div.innerHTML +=
                            '<i style="background:'+ colorForScore(from + 0.1) +'"></i> ' +
                            from + (to ? '&ndash;' + to + '<br>' : '+');
                    }
                    return div;
                };
                return combinedLegend;
            }

            function ensureBatiLegend() {
                if (batiLegend) return batiLegend;
                batiLegend = L.control({ position: 'bottomright' });
                batiLegend.onAdd = function(map){
                    const div = L.DomUtil.create('div', 'info legend');
                    div.innerHTML = '<strong>Bâti</strong><br>' +
                        '<i style="background:#34d399;border:1px solid #10b981"></i> Emprise bâtimentaire';
                    return div;
                };
                return batiLegend;
            }

            function fetchCombinedDensity() {
                const url = `{{ route('web.geomarketing.combinedDensity') }}`;
                return fetch(url, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }})
                    .then(r => r.json())
                    .then(json => {
                        if (combinedLayer) { layersCtrl.removeLayer(combinedLayer); map.removeLayer(combinedLayer); combinedLayer = null; }
                        if (!json.features || json.features.length === 0) return;
                        combinedLayer = L.geoJSON(json.features, {
                            style: f => ({
                                color: '#555', weight: 0.5, fillOpacity: 0.6, fillColor: colorForScore(f.properties.score || 0)
                            }),
                            onEachFeature: (f, layer) => {
                                const p = f.properties || {};
                                layer.bindPopup(
                                    `<strong>Score:</strong> ${p.score ?? 0}<br>`+
                                    `<strong>Population (norm):</strong> ${p.pop ?? 0}%<br>`+
                                    `<strong>Bâti (norm):</strong> ${p.bati ?? 0}%<br>`+
                                    `<strong>Access (norm):</strong> ${p.access ?? 0}%`
                                );
                            }
                        }).addTo(map);
                        layersCtrl.addOverlay(combinedLayer, 'Carte multi-critères');
                        ensureCombinedLegend().addTo(map);
                    })
                    .catch(e => console.warn('Combined density overlay error', e));
            }
            function fetchPopulation() {
                const url = `{{ route('web.geomarketing.population') }}`;
                return fetch(url, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }})
                    .then(r => r.json())
                    .then(json => {
                        if (populationLayer) { layersCtrl.removeLayer(populationLayer); map.removeLayer(populationLayer); }
                        if (!json.features || json.features.length === 0) return;
                        populationLayer = L.geoJSON(json.features, {
                            style: f => ({
                                color: '#1d4ed8', weight: 1, fillOpacity: 0.25,
                                fillColor: '#60a5fa'
                            }),
                            onEachFeature: (f, l) => l.bindPopup(`<strong>${f.properties.nom||'Zone'}</strong><br/>Population: ${f.properties.population||'N/A'}`)
                        }).addTo(map);
                        layersCtrl.addOverlay(populationLayer, 'Population');
                    })
                    .catch(e => console.warn('Population overlay error', e));
            }

            function fetchRoads() {
                const url = `{{ route('web.geomarketing.roads') }}`;
                return fetch(url, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }})
                    .then(r => r.json())
                    .then(json => {
                        if (roadsLayer) { layersCtrl.removeLayer(roadsLayer); map.removeLayer(roadsLayer); }
                        if (!json.features || json.features.length === 0) return;
                        roadsLayer = L.geoJSON(json.features, {
                            style: f => ({ color: '#ef4444', weight: (f.properties && f.properties.importance==='primary')?3:1.5 })
                        }).addTo(map);
                        layersCtrl.addOverlay(roadsLayer, 'Routes');
                    })
                    .catch(e => console.warn('Roads overlay error', e));
            }

            function fetchBati() {
                const url = `{{ route('web.geomarketing.bati') }}`;
                return fetch(url, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }})
                    .then(r => r.json())
                    .then(json => {
                        if (batiLayer) { layersCtrl.removeLayer(batiLayer); map.removeLayer(batiLayer); }
                        if (!json.features || json.features.length === 0) return;
                        batiLayer = L.geoJSON(json.features, {
                            style: { color: '#10b981', weight: 1, fillOpacity: 0.3, fillColor: '#34d399' },
                            onEachFeature: (f, l) => {
                                const p = f.properties || {};
                                l.bindPopup(
                                    `<strong>${p.nom || 'Bâtiment'}</strong><br/>`+
                                    (p.usage ? `Usage: ${p.usage}<br/>` : '')+
                                    (p.surface_m2 ? `Surface: ${p.surface_m2} m²` : '')
                                );
                            }
                        }).addTo(map);
                        layersCtrl.addOverlay(batiLayer, 'Bâti');
                        ensureBatiLegend().addTo(map);
                    })
                    .catch(e => console.warn('Bati overlay error', e));
            }

            // Toggle handlers
            document.getElementById('togglePopulation').addEventListener('change', function(){
                if (this.checked) fetchPopulation(); else if (populationLayer) { map.removeLayer(populationLayer); populationLayer = null; }
            });
            document.getElementById('toggleRoads').addEventListener('change', function(){
                if (this.checked) fetchRoads(); else if (roadsLayer) { map.removeLayer(roadsLayer); roadsLayer = null; }
            });
            document.getElementById('toggleBati').addEventListener('change', function(){
                if (this.checked) {
                    fetchBati();
                } else {
                    if (batiLayer) { map.removeLayer(batiLayer); layersCtrl.removeLayer(batiLayer); batiLayer = null; }
                    if (batiLegend) { map.removeControl(batiLegend); }
                }
            });
            document.getElementById('toggleHeatmap').addEventListener('change', function(){
                if (this.checked) fetchHeatmap(); else if (heatmapLayer) { map.removeLayer(heatmapLayer); heatmapLayer = null; }
            });
            document.getElementById('toggleCombined').addEventListener('change', function(){
                if (this.checked) {
                    fetchCombinedDensity();
                } else {
                    if (combinedLayer) { map.removeLayer(combinedLayer); layersCtrl.removeLayer(combinedLayer); combinedLayer = null; }
                    if (combinedLegend) { map.removeControl(combinedLegend); }
                }
            });

            // ===== Recommandations =====
            function fetchRecommendations() {
                const natureId = document.getElementById('natureActivite').value;
                const typeId = document.getElementById('typeCommerce').value;
                let url = `{{ route('web.geomarketing.recommendations') }}?`;
                if (typeId) url += `type_commerce_id=${typeId}`; else if (natureId) url += `nature_activite_id=${natureId}`;
                fetch(url, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }})
                    .then(r => r.json())
                    .then(json => updateRecommendations(json.data || []))
                    .catch(e => console.warn('Recommandations error', e));
            }

            function updateRecommendations(items) {
                const ul = document.getElementById('recoList');
                if (!ul) return;
                if (!items.length) {
                    ul.innerHTML = '<li class="list-group-item text-muted">Aucune suggestion disponible pour les filtres actuels.</li>';
                    return;
                }
                ul.innerHTML = items.slice(0, 8).map(it => (
                    `<li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${it.zone}</strong><br/>
                            <small>${it.reason}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">${it.score}</span>
                    </li>`
                )).join('');
            }
        });
    </script>
@endpush
