@extends('layouts.app')

@section('title', 'Carte multi-communes - SICOFIL')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/MarkerCluster.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/MarkerCluster.Default.css" />
    <style>
        .card-soft {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.10);
        }
        .card-soft .card-header {
            background: #fff;
            border-bottom: 1px solid rgba(148,163,184,.35);
            padding: .9rem 1rem;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }
        .card-soft .card-body { padding: 1rem 1rem; }

        /* Contenir la carte dans la colonne (éviter débordement header/sidebar) */
        #sicofil-map-wrapper {
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid rgba(148,163,184,.35);
            width: 100%;
            max-width: 100%;
            min-height: 400px;
            height: calc(100vh - 220px);
        }
        #sicofil-map-page {
            width: 100% !important;
            max-width: 100% !important;
            height: 100% !important;
            min-height: 400px;
            border-radius: 16px;
            overflow: hidden;
            box-sizing: border-box;
        }
        .communes-map-col {
            overflow: hidden;
            max-width: 100%;
        }
        .communes-map-col .card-soft .card-body {
            overflow: hidden;
            max-width: 100%;
        }
        #sicofil-map-wrapper .leaflet-container {
            max-width: 100% !important;
            min-height: 100%;
        }

        .legend-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: .45rem;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .88rem;
            color: rgba(15,23,42,.78);
        }
        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }

        .map-hint {
            background: rgba(79,70,229,.08);
            border: 1px solid rgba(79,70,229,.16);
            border-radius: 14px;
            padding: .75rem .85rem;
            color: rgba(15,23,42,.78);
        }

        @media (max-width: 576px) {
            #sicofil-map-wrapper {
                height: calc(100vh - 200px);
                min-height: 360px;
            }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1 text-primary fw-bold">
                <i class="fas fa-map me-2"></i>Carte multi-communes
            </h1>
            <p class="text-muted mb-0">
                Unités commerciales (points), centres des communes et limite commune (SHP) si disponible.
            </p>
            <div class="small text-muted mt-1">
                <i class="fas fa-clock me-1"></i>
                Mis à jour le {{ ($generationDate ?? now())->format('d/m/Y à H:i') }}
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('communes.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-pie me-1"></i>Dashboard
            </a>
            <a href="{{ route('map.import-zones') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-layer-group me-1"></i>Ajouter des couches de zones (GeoJSON/SHP)
            </a>
            <a href="{{ route('communes.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i>Ajouter une commune
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-3">
            <div class="card card-soft">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="fw-bold"><i class="fas fa-sliders me-2 text-primary"></i>Filtres</div>
                    <button class="btn btn-sm btn-outline-secondary" id="btnReset" type="button" title="Réinitialiser">
                        <i class="fas fa-crosshairs"></i>
                    </button>
                </div>
                <div class="card-body">
                    <label class="form-label small text-muted mb-1">Commune</label>
                    <select id="communeFilter" class="form-select form-select-sm">
                        <option value="all">Toutes les communes</option>
                        @foreach(($communesMap ?? []) as $c)
                            <option value="{{ $c['id'] }}">{{ $c['nom'] }}</option>
                        @endforeach
                    </select>

                    <label class="form-label small text-muted mb-1 mt-3">Rechercher une unité</label>
                    <input id="uniteSearch" type="text" class="form-control form-control-sm" placeholder="Nom, nature, type…">

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="toggleUnites" checked>
                        <label class="form-check-label" for="toggleUnites">
                            Unités commerciales (points) <span class="text-muted">({{ $stats['unites_geolocalisees'] ?? 0 }})</span>
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="toggleMarkers" checked>
                        <label class="form-check-label" for="toggleMarkers">
                            Centres des communes
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="toggleZones" checked>
                        <label class="form-check-label" for="toggleZones">
                            Zones fiscales (par commune)
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="toggleShp">
                        <label class="form-check-label" for="toggleShp">
                            Limite commune (SHP) (si dispo)
                        </label>
                    </div>

                    <hr class="my-3">

                    <div class="map-hint small">
                        <div class="fw-semibold mb-1">Astuce</div>
                        - Cochez <strong>« Unités commerciales (points) »</strong> pour afficher les unités sur la carte.<br>
                        - Cliquez un <strong>point</strong> pour voir l'unité commerciale.<br>
                        - Filtrez par <strong>commune</strong> pour zoomer automatiquement.
                    </div>

                    <hr class="my-3">

                    <div class="small text-muted">
                        <div class="fw-semibold text-dark mb-2"><i class="fas fa-circle-info me-2"></i>Légende</div>
                        <div class="legend-grid">
                            @foreach(($communesMap ?? []) as $c)
                                <div class="legend-item">
                                    <span class="legend-dot" style="background: {{ $c['color'] ?? '#64748b' }}"></span>
                                    <span class="text-truncate">{{ $c['nom'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-soft mt-3">
                <div class="card-header">
                    <div class="fw-bold"><i class="fas fa-list me-2 text-indigo-600"></i>Détails</div>
                </div>
                <div class="card-body">
                    <div id="mapInfo" class="small text-muted">
                        Cliquez un point ou un centre de commune.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9 communes-map-col">
            <div class="card card-soft">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="fw-bold">
                        <i class="fas fa-layer-group me-2 text-primary"></i>Carte
                    </div>
                    <div class="small text-muted">
                        Communes: <strong>{{ $stats['total_communes'] ?? 0 }}</strong> •
                        UC: <strong>{{ number_format($stats['total_unites'] ?? 0, 0, ',', ' ') }}</strong>
                        <span class="text-muted">(géoloc: {{ number_format($stats['unites_geolocalisees'] ?? 0, 0, ',', ' ') }})</span> •
                        Recettes: <strong>{{ number_format($stats['total_recettes'] ?? 0, 0, ',', ' ') }}</strong> FCFA
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="sicofil-map-wrapper">
                        <div id="sicofil-map-page"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/leaflet.markercluster.js"></script>

    <script>
        (function () {
            const BASE_URL = @json(rtrim(url('/'), '/'));
            const communes = @json($communesMap ?? []);
            const unitesPoints = @json($unitesPoints ?? []);
            const zonesGeoJson = @json(isset($zonesGeoJson) ? $zonesGeoJson : ['type' => 'FeatureCollection', 'features' => []]);
            const communeBaseUrl = @json(url('/multi-communes/communes'));

            const formatFcfa = (n) => {
                try { return new Intl.NumberFormat('fr-FR').format(Number(n || 0)); } catch (e) { return String(n || 0); }
            };

            const map = L.map('sicofil-map-page', {
                zoomControl: true,
                scrollWheelZoom: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19,
            }).addTo(map);

            let markersLayer = null;
            let unitesLayer = null;
            let zonesLayer = null;
            let shpLayer = null;
            let shpLoaded = false;

            const mapInfoEl = document.getElementById('mapInfo');
            const communeFilterEl = document.getElementById('communeFilter');
            const uniteSearchEl = document.getElementById('uniteSearch');
            const toggleUnitesEl = document.getElementById('toggleUnites');
            const toggleMarkersEl = document.getElementById('toggleMarkers');
            const toggleZonesEl = document.getElementById('toggleZones');
            const toggleShpEl = document.getElementById('toggleShp');
            const btnReset = document.getElementById('btnReset');

            const communeColorById = {};
            communes.forEach((c) => { communeColorById[String(c.id)] = c.color || '#64748b'; });

            const zonePalette = ['#22c55e', '#3b82f6', '#ef4444', '#f59e0b', '#a855f7', '#14b8a6', '#f97316', '#64748b', '#0ea5e9', '#4f46e5'];
            const zoneColor = (zoneId) => {
                const n = Number(zoneId || 0);
                const idx = Math.max(0, (isFinite(n) ? n : 0) - 1);
                return zonePalette[idx % zonePalette.length];
            };

            const unitePopupHtml = (u) => {
                const commune = u.commune || '—';
                const zone = u.zone || '—';
                const nom = u.nom || '—';
                const nature = u.nature || '';
                const type = u.type_commerce || '';
                const cat = u.categorie || '';

                return `
                    <div style="min-width:240px;">
                        <div style="font-weight:800;margin-bottom:4px;">${nom}</div>
                        <div style="font-size:.92rem;color:#334155;">
                            <div><strong>Commune:</strong> ${commune}</div>
                            <div><strong>Zone:</strong> ${zone}</div>
                            ${nature ? `<div><strong>Nature:</strong> ${nature}</div>` : ``}
                            ${type ? `<div><strong>Type:</strong> ${type}</div>` : ``}
                            ${cat ? `<div><strong>Catégorie:</strong> ${cat}</div>` : ``}
                        </div>
                    </div>
                `;
            };

            const updateInfo = (html) => {
                if (!mapInfoEl) return;
                mapInfoEl.innerHTML = html;
            };

            const makeUniteIcon = (color) => L.divIcon({
                className: '',
                html: `<div style="
                    width: 12px;
                    height: 12px;
                    border-radius: 999px;
                    background: ${color};
                    border: 2px solid rgba(255,255,255,.95);
                    box-shadow: 0 2px 8px rgba(0,0,0,.22);
                "></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6],
                popupAnchor: [0, -8],
            });

            const createUnitesLayer = (communeId, query) => {
                const id = communeId === 'all' ? null : Number(communeId);
                const q = String(query || '').toLowerCase().trim();

                const cluster = L.markerClusterGroup({
                    chunkedLoading: true,
                    showCoverageOnHover: false,
                    spiderfyOnMaxZoom: true,
                    maxClusterRadius: 42,
                });

                unitesPoints.forEach((u) => {
                    if (!u || typeof u.lat !== 'number' || typeof u.lng !== 'number') return;
                    if (id && Number(u.commune_id || 0) !== id) return;

                    if (q) {
                        const hay = `${u.nom || ''} ${u.nature || ''} ${u.type_commerce || ''} ${u.categorie || ''} ${u.zone || ''} ${u.commune || ''}`.toLowerCase();
                        if (!hay.includes(q)) return;
                    }

                    const color = u.zone_id ? zoneColor(u.zone_id) : (communeColorById[String(u.commune_id)] || '#0ea5e9');
                    const marker = L.marker([u.lat, u.lng], { icon: makeUniteIcon(color) });
                    const html = unitePopupHtml(u);
                    marker.bindPopup(html);
                    marker.on('click', () => updateInfo(html));
                    cluster.addLayer(marker);
                });

                return cluster;
            };

            const createMarkersLayer = (communeId) => {
                const id = communeId === 'all' ? null : Number(communeId);
                const group = L.layerGroup();

                communes.forEach((c) => {
                    if (!c.center) return;
                    if (id && Number(c.id) !== id) return;

                    const marker = L.circleMarker([c.center.lat, c.center.lng], {
                        radius: 7,
                        color: c.color || '#64748b',
                        fillColor: c.color || '#64748b',
                        fillOpacity: 0.96,
                        weight: 2,
                    });

                    const url = `${communeBaseUrl}/${c.id}`;
                    const html = `
                        <div style="min-width:240px;">
                            <div style="font-weight:800;margin-bottom:4px;">${c.nom}</div>
                            <div style="font-size:.92rem;color:#334155;">
                                <div><strong>Zones:</strong> ${c.zones_count}</div>
                                <div><strong>UC:</strong> ${c.unites_count}</div>
                                <div><strong>Utilisateurs:</strong> ${c.users_count}</div>
                                <div><strong>Recettes:</strong> ${formatFcfa(c.total_recettes)} FCFA</div>
                                <div class="mt-2"><a href="${url}" class="btn btn-sm btn-primary">Voir</a></div>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(html);
                    marker.on('click', () => updateInfo(html));
                    marker.addTo(group);
                });

                return group;
            };

            const fitToLayers = () => {
                const groups = [];
                if (zonesLayer && typeof zonesLayer.getBounds === 'function') {
                    try {
                        const b = zonesLayer.getBounds();
                        if (b.isValid()) groups.push(zonesLayer);
                    } catch (e) {}
                }
                if (unitesLayer && typeof unitesLayer.getLayers === 'function' && unitesLayer.getLayers().length > 0) {
                    try { groups.push(unitesLayer); } catch (e) {}
                }
                if (markersLayer && typeof markersLayer.getLayers === 'function' && markersLayer.getLayers().length > 0) {
                    try {
                        const fg = L.featureGroup(markersLayer.getLayers());
                        groups.push(fg);
                    } catch (e) {}
                }
                if (groups.length > 0) {
                    try {
                        const combined = L.featureGroup(groups);
                        map.fitBounds(combined.getBounds(), { padding: [18, 18] });
                        return;
                    } catch (e) {}
                }
                map.setView([14.45, -16.85], 11);
            };

            const createZonesLayer = (communeId) => {
                const id = communeId === 'all' ? null : Number(communeId);
                const fc = Array.isArray(zonesGeoJson.features) ? zonesGeoJson : { type: 'FeatureCollection', features: [] };
                const features = (fc.features || []).filter((f) => {
                    if (!id) return true;
                    const cid = f.properties && f.properties.commune_id;
                    return Number(cid) === id;
                });
                if (features.length === 0) return null;
                const layer = L.geoJSON({ type: 'FeatureCollection', features }, {
                    style: (feature) => ({
                        color: feature.properties && feature.properties.color ? feature.properties.color : '#64748b',
                        weight: 2,
                        fillColor: feature.properties && feature.properties.color ? feature.properties.color : '#64748b',
                        fillOpacity: 0.25,
                    }),
                    onEachFeature: (feature, layer) => {
                        const props = feature.properties || {};
                        const nom = props.nom || 'Zone';
                        const commune = props.commune || '—';
                        const popup = `<div style="min-width:180px;"><strong>${nom}</strong><br><span class="text-muted">${commune}</span></div>`;
                        layer.bindPopup(popup);
                    },
                });
                return layer;
            };

            const render = () => {
                const communeId = communeFilterEl ? communeFilterEl.value : 'all';
                const query = uniteSearchEl ? uniteSearchEl.value : '';

                if (zonesLayer) { map.removeLayer(zonesLayer); zonesLayer = null; }
                if (markersLayer) { map.removeLayer(markersLayer); markersLayer = null; }
                if (unitesLayer) { map.removeLayer(unitesLayer); unitesLayer = null; }

                if (toggleZonesEl?.checked) {
                    const zl = createZonesLayer(communeId);
                    if (zl) { zonesLayer = zl; zonesLayer.addTo(map); }
                }
                if (toggleUnitesEl?.checked) {
                    unitesLayer = createUnitesLayer(communeId, query);
                    unitesLayer.addTo(map);
                }
                if (toggleMarkersEl?.checked) {
                    markersLayer = createMarkersLayer(communeId);
                    markersLayer.addTo(map);
                }

                fitToLayers();
            };

            const loadOrToggleShp = async () => {
                if (!toggleShpEl?.checked) {
                    if (shpLayer) { map.removeLayer(shpLayer); }
                    return;
                }

                if (shpLoaded) {
                    if (shpLayer) { shpLayer.addTo(map); }
                    return;
                }

                try {
                    const res = await fetch(BASE_URL + '/api/map/boundaries?layer=commune&simplify=0.0001', { credentials: 'same-origin' });
                    const json = await res.json();
                    const fc = json?.data;
                    const features = fc?.features || [];
                    if (!fc || !Array.isArray(features) || features.length === 0) {
                        updateInfo('<div class="text-muted small">Aucune limite SHP disponible pour le moment.</div>');
                        toggleShpEl.checked = false;
                        return;
                    }

                    shpLayer = L.geoJSON(fc, {
                        style: () => ({
                            color: '#111827',
                            weight: 2,
                            dashArray: '6 6',
                            fillOpacity: 0,
                        }),
                    });
                    shpLayer.addTo(map);
                    shpLoaded = true;
                } catch (e) {
                    updateInfo('<div class="text-muted small">Erreur lors du chargement des limites SHP.</div>');
                    toggleShpEl.checked = false;
                }
            };

            // Init : recalculer la taille de la carte après mise en page (éviter débordement)
            render();
            const refreshMapSize = () => {
                if (map && typeof map.invalidateSize === 'function') map.invalidateSize();
            };
            setTimeout(refreshMapSize, 100);
            setTimeout(refreshMapSize, 400);
            window.addEventListener('resize', refreshMapSize);

            communeFilterEl?.addEventListener('change', render);
            uniteSearchEl?.addEventListener('input', render);
            toggleUnitesEl?.addEventListener('change', render);
            toggleMarkersEl?.addEventListener('change', render);
            toggleZonesEl?.addEventListener('change', render);
            toggleShpEl?.addEventListener('change', loadOrToggleShp);

            btnReset?.addEventListener('click', () => {
                if (communeFilterEl) communeFilterEl.value = 'all';
                if (uniteSearchEl) uniteSearchEl.value = '';
                if (toggleUnitesEl) toggleUnitesEl.checked = true;
                if (toggleMarkersEl) toggleMarkersEl.checked = true;
                if (toggleZonesEl) toggleZonesEl.checked = true;
                if (toggleShpEl) toggleShpEl.checked = false;
                if (shpLayer) map.removeLayer(shpLayer);
                updateInfo('<div class="small text-muted">Cliquez un point ou un centre de commune.</div>');
                render();
            });
        })();
    </script>
@endpush

