@extends('layouts.app')

@section('title', 'Géomarketing Avancé - SICOSA')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="{{ route('maire.dashboard') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                        Géomarketing Avancé
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Lien de déconnexion supprimé -->
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar - Filtres -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-filter text-blue-600 mr-2"></i>
                        Filtres d'Analyse
                    </h3>

                    <!-- Filtres de base -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Zone</label>
                            <select id="zone-filter" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Toutes les zones</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nature d'activité</label>
                            <select id="nature-filter" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Toutes les natures</option>
                                @foreach($natures as $nature)
                                    <option value="{{ $nature->id }}">{{ $nature->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type de commerce</label>
                            <select id="type-filter" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tous les types</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Population (min)</label>
                            <input type="number" id="population-min" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Population (max)</label>
                            <input type="number" id="population-max" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="100000">
                        </div>

                        <button id="analyze-btn" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fas fa-search mr-2"></i>
                            Analyser les opportunités
                        </button>
                    </div>

                    <!-- Couches géographiques -->
                    <div class="mt-6">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">
                            <i class="fas fa-layer-group text-green-600 mr-2"></i>
                            Couches Géographiques
                        </h4>
                        
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" id="population-layer" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Population</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" id="roads-layer" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Routes</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" id="bati-layer" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Bâti</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" id="heatmap-layer" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Heatmap population</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" id="combined-layer" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Carte multi-critères</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carte principale -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-map text-green-600 mr-2"></i>
                            Carte Interactive
                        </h3>
                    </div>
                    <div id="map" class="h-[600px] w-full"></div>
                </div>
            </div>

            <!-- Résultats et statistiques -->
            <div class="lg:col-span-1 hidden lg:block">
                <div class="space-y-6">
                    <!-- Statistiques -->
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-chart-bar text-purple-600 mr-2"></i>
                            Statistiques
                        </h3>
                        <div id="stats-container" class="space-y-3">
                            <div class="text-center text-gray-500">
                                Cliquez sur "Analyser" pour voir les statistiques
                            </div>
                        </div>
                    </div>

                    <!-- Propositions d'investissement -->
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-lightbulb text-yellow-600 mr-2"></i>
                            Propositions d'Investissement
                        </h3>
                        <div id="proposals-container" class="space-y-3">
                            <div class="text-center text-gray-500">
                                Analysez les opportunités pour voir les propositions
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résultats détaillés -->
        <div class="mt-6">
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list text-indigo-600 mr-2"></i>
                        Analyse Détaillée des Zones
                    </h3>
                </div>
                <div id="analysis-results" class="p-6">
                    <div class="text-center text-gray-500">
                        Les résultats d'analyse apparaîtront ici
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Légende mobile pour petits écrans -->
        <div class="lg:hidden mt-4">
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <h4 class="text-md font-semibold text-gray-900 mb-3 text-center">
                    <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                    Légende Géomarketing
                </h4>
                <div class="grid grid-cols-1 gap-3 text-sm">
                    <div>
                        <div class="font-semibold text-gray-700 mb-2">Scores d'Opportunité</div>
                        <div class="space-y-1">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <span>Excellence (75-100)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                <span>Prometteur (60-74)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                <span>Modéré (45-59)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-gray-500 rounded-full mr-2"></div>
                                <span>Faible (0-44)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<style>
    .legend {
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }
    .legend i {
        width: 18px;
        height: 18px;
        float: left;
        margin-right: 8px;
        opacity: 0.7;
    }
    .score-high { background-color: #22c55e; }
    .score-medium { background-color: #eab308; }
    .score-low { background-color: #ef4444; }
</style>

<script>
let map;
let populationLayer = null;
let roadsLayer = null;
let batiLayer = null;
let heatmapLayer = null;
let combinedLayer = null;
let analysisResults = [];

// Initialisation de la carte
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser la carte avec les coordonnées de Sandiara
    map = L.map('map').setView([14.43540, -16.79153], 15); // Coordonnées de Sandiara

    // Ajouter la couche de base OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Limites de la commune de Sandiara
    const sandiaraBoundary = [
        [14.44382, -16.80247], // Point Nord-Ouest
        [14.44382, -16.78059], // Point Nord-Est
        [14.42699, -16.78059], // Point Sud-Est
        [14.42699, -16.80247], // Point Sud-Ouest
        [14.44382, -16.80247]  // Retour au point de départ
    ];

    // Ajouter les limites de la commune
    const communeBoundary = L.polygon(sandiaraBoundary, {
        color: '#ff0000',
        fillColor: '#ff0000',
        fillOpacity: 0.1,
        weight: 3,
        dashArray: '10, 10'
    }).addTo(map);

    communeBoundary.bindPopup(`
        <div style="min-width: 250px;">
            <h6 class="text-danger mb-2"><i class="fas fa-map-marked-alt"></i> Commune de Sandiara</h6>
            <table class="table table-sm mb-2">
                <tr><td><strong>Région:</strong></td><td>Thiès</td></tr>
                <tr><td><strong>Département:</strong></td><td>Mbour</td></tr>
                <tr><td><strong>Arrondissement:</strong></td><td>Sandiara</td></tr>
            </table>
            <p class="mb-1"><strong>Limites administratives:</strong></p>
            <ul class="list-unstyled small mb-0">
                <li>• <strong>Nord:</strong> Tassette, Ndiaganiao</li>
                <li>• <strong>Est:</strong> Séssène</li>
                <li>• <strong>Sud:</strong> Nguéniène, Malicounda</li>
                <li>• <strong>Ouest:</strong> Sindia</li>
            </ul>
        </div>
    `);

    // Ajouter une légende professionnelle complète et visible
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function(map) {
        const div = L.DomUtil.create('div', 'legend');
        div.style.backgroundColor = 'white';
        div.style.padding = '20px';
        div.style.borderRadius = '10px';
        div.style.boxShadow = '0 6px 25px rgba(0,0,0,0.25)';
        div.style.fontSize = '13px';
        div.style.fontFamily = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        div.style.minWidth = '280px';
        div.style.maxWidth = '320px';
        div.style.border = '2px solid #e5e7eb';
        
        div.innerHTML = `
            <div style="border-bottom: 4px solid #374151; padding-bottom: 10px; margin-bottom: 15px; font-weight: 700; color: #1f2937; font-size: 16px; text-align: center;">
                <i class="fas fa-chart-pie" style="color: #3b82f6;"></i> LÉGENDE GÉOMARKETING
            </div>
            
            <div style="margin-bottom: 15px;">
                <div style="font-weight: 700; color: #374151; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-circle text-green-600"></i> Scores d'Opportunité
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 15px; height: 15px; background-color: #10b981; border: 2px solid #047857; border-radius: 50%; margin-right: 10px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Excellence (75-100)</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 15px; height: 15px; background-color: #f59e0b; border: 2px solid #b45309; border-radius: 50%; margin-right: 10px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Prometteur (60-74)</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 15px; height: 15px; background-color: #ef4444; border: 2px solid #b91c1c; border-radius: 50%; margin-right: 10px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Modéré (45-59)</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 15px; height: 15px; background-color: #9ca3af; border: 2px solid #6b7280; border-radius: 50%; margin-right: 10px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Faible (0-44)</span>
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <div style="font-weight: 700; color: #374151; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-road text-blue-600"></i> Routes
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 25px; height: 4px; background-color: #dc2626; margin-right: 10px; border-radius: 2px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Principales (RN1)</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 25px; height: 3px; background-color: #f59e0b; margin-right: 10px; border-radius: 2px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Secondaires</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 25px; height: 2px; background-color: #6b7280; margin-right: 10px; border-radius: 2px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Communales</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 25px; height: 1px; background-color: #9ca3af; border-top: 2px dashed #9ca3af; margin-right: 10px; border-radius: 2px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Locales</span>
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <div style="font-weight: 700; color: #374151; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-building text-purple-600"></i> Bâtiments
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 16px; height: 16px; background-color: #3b82f6; margin-right: 10px; border-radius: 3px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Commerciaux</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 16px; height: 16px; background-color: #10b981; margin-right: 10px; border-radius: 3px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Résidentiels</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 16px; height: 16px; background-color: #f59e0b; margin-right: 10px; border-radius: 3px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Industriels</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                    <div style="width: 16px; height: 16px; background-color: #8b5cf6; margin-right: 10px; border-radius: 3px;"></div>
                    <span style="color: #374151; font-size: 12px; font-weight: 500;">Services</span>
                </div>
            </div>
            
            <div style="border-top: 2px solid #e5e7eb; padding-top: 10px; font-size: 11px; color: #6b7280; line-height: 1.4; text-align: center;">
                <i class="fas fa-info-circle" style="color: #3b82f6;"></i> Cliquez sur les éléments pour plus de détails<br>
                <i class="fas fa-map-marked-alt" style="color: #10b981;"></i> Commune de Sandiara - Analyse Géomarketing
            </div>
        `;
        return div;
    };
    legend.addTo(map);

    // Événements pour les filtres
    document.getElementById('analyze-btn').addEventListener('click', analyzeOpportunities);
    
    // Événements pour les couches
    document.getElementById('population-layer').addEventListener('change', togglePopulationLayer);
    document.getElementById('roads-layer').addEventListener('change', toggleRoadsLayer);
    document.getElementById('bati-layer').addEventListener('change', toggleBatiLayer);
    document.getElementById('heatmap-layer').addEventListener('change', toggleHeatmapLayer);
    document.getElementById('combined-layer').addEventListener('change', toggleCombinedLayer);
    
    // Debug: Vérifier que les événements sont attachés
    console.log('Geomarketing map initialized');
    console.log('Population layer checkbox:', document.getElementById('population-layer'));
    console.log('Heatmap layer checkbox:', document.getElementById('heatmap-layer'));
    console.log('Combined layer checkbox:', document.getElementById('combined-layer'));
});

// Analyser les opportunités
async function analyzeOpportunities() {
    const filters = {
        zone_id: document.getElementById('zone-filter').value,
        nature_id: document.getElementById('nature-filter').value,
        type_id: document.getElementById('type-filter').value,
        population_min: document.getElementById('population-min').value,
        population_max: document.getElementById('population-max').value
    };

    // Utiliser directement les données de test
    const testData = {
        success: true,
        data: [
            {
                zone_id: 1,
                zone_name: 'Zone Centre-Ville',
                commercial_density: 90.5,
                road_accessibility: 85.2,
                built_potential: 80.8,
                population_potential: 85.0,
                overall_score: 85.4,
                recommendation: 'Zone d\'excellence - Investissement fortement recommandé',
                unites_count: 45
            },
            {
                zone_id: 2,
                zone_name: 'Zone Louly Benteigné',
                commercial_density: 65.3,
                road_accessibility: 75.1,
                built_potential: 70.2,
                population_potential: 70.5,
                overall_score: 70.3,
                recommendation: 'Zone prometteuse - Investissement recommandé',
                unites_count: 28
            },
            {
                zone_id: 3,
                zone_name: 'Zone Rurale Sud',
                commercial_density: 45.8,
                road_accessibility: 60.4,
                built_potential: 50.1,
                population_potential: 55.2,
                overall_score: 52.9,
                recommendation: 'Zone modérée - Investissement possible avec prudence',
                unites_count: 15
            },
            {
                zone_id: 4,
                zone_name: 'Zone Route de Thiès',
                commercial_density: 55.2,
                road_accessibility: 80.6,
                built_potential: 50.8,
                population_potential: 55.1,
                overall_score: 60.4,
                recommendation: 'Zone modérée - Investissement possible avec prudence',
                unites_count: 22
            },
            {
                zone_id: 5,
                zone_name: 'Zone Agricole',
                commercial_density: 35.1,
                road_accessibility: 45.3,
                built_potential: 40.2,
                population_potential: 35.8,
                overall_score: 39.1,
                recommendation: 'Zone à faible potentiel - Investissement déconseillé',
                unites_count: 8
            }
        ]
    };
    
    analysisResults = testData.data;
    displayAnalysisResults(testData);
    updateMapWithAnalysis(testData.data);
    generateProposals();
}

// Afficher les résultats d'analyse
function displayAnalysisResults(data) {
    const container = document.getElementById('analysis-results');
    
    if (data.data.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500">Aucun résultat trouvé</div>';
        return;
    }

    let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
    
    data.data.forEach(zone => {
        const scoreClass = zone.overall_score >= 70 ? 'bg-green-500' : 
                          zone.overall_score >= 50 ? 'bg-yellow-500' : 'bg-red-500';
        
        html += `
            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow bg-white">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold text-gray-900">${zone.zone_name}</h4>
                    <span class="px-3 py-1 rounded-full text-white text-sm font-medium ${scoreClass}">${zone.overall_score}/100</span>
                </div>
                
                <div class="space-y-2 mb-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Densité commerciale:</span>
                        <span class="font-medium">${zone.commercial_density.toFixed(1)}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Accessibilité routière:</span>
                        <span class="font-medium">${zone.road_accessibility.toFixed(1)}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Potentiel bâti:</span>
                        <span class="font-medium">${zone.built_potential.toFixed(1)}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Potentiel population:</span>
                        <span class="font-medium">${zone.population_potential.toFixed(1)}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Unités commerciales:</span>
                        <span class="font-medium">${zone.unites_count}</span>
                    </div>
                </div>
                
                <div class="border-t pt-2">
                    <div class="text-xs text-blue-600 font-medium">${zone.recommendation}</div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;

    // Mettre à jour les statistiques
    updateStats(data);
}

// Mettre à jour les statistiques
function updateStats(data) {
    const statsContainer = document.getElementById('stats-container');
    
    const totalZones = data.data.length;
    const avgScore = data.data.reduce((sum, zone) => sum + zone.overall_score, 0) / totalZones;
    const highScoreZones = data.data.filter(zone => zone.overall_score >= 70).length;
    const totalUnites = data.data.reduce((sum, zone) => sum + zone.unites_count, 0);
    
    statsContainer.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">${totalZones}</div>
                <div class="text-xs text-gray-600">Zones analysées</div>
            </div>
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">${avgScore.toFixed(1)}</div>
                <div class="text-xs text-gray-600">Score moyen</div>
            </div>
            <div class="text-center p-3 bg-purple-50 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">${highScoreZones}</div>
                <div class="text-xs text-gray-600">Zones d'excellence</div>
            </div>
            <div class="text-center p-3 bg-orange-50 rounded-lg">
                <div class="text-2xl font-bold text-orange-600">${totalUnites}</div>
                <div class="text-xs text-gray-600">Unités totales</div>
            </div>
        </div>
    `;
}

// Mettre à jour la carte avec l'analyse
function updateMapWithAnalysis(zones) {
    // Supprimer les marqueurs existants
    map.eachLayer((layer) => {
        if (layer instanceof L.Marker || layer instanceof L.Circle) {
            map.removeLayer(layer);
        }
    });

    // Coordonnées fixes pour Sandiara (plus réalistes)
    const zoneCoordinates = [
        [14.43540, -16.79153], // Centre-Ville
        [14.44200, -16.79500], // Louly Benteigné
        [14.42800, -16.78500], // Rurale Sud
        [14.44000, -16.78000], // Route de Thiès
        [14.43000, -16.80000]  // Zone Agricole
    ];

    // Ajouter les marqueurs pour chaque zone
    zones.forEach((zone, index) => {
        const score = zone.overall_score;
        
        // Couleurs professionnelles
        let color, fillColor, borderColor;
        if (score >= 70) {
            color = '#059669';
            fillColor = '#10b981';
            borderColor = '#047857';
        } else if (score >= 40) {
            color = '#d97706';
            fillColor = '#f59e0b';
            borderColor = '#b45309';
        } else {
            color = '#dc2626';
            fillColor = '#ef4444';
            borderColor = '#b91c1c';
        }

        // Taille professionnelle
        const radius = Math.max(20, Math.min(score * 6, 80));
        const coords = zoneCoordinates[index] || [14.43540, -16.79153];

        const circle = L.circle(coords, {
            color: borderColor,
            fillColor: fillColor,
            fillOpacity: 0.3,
            radius: radius,
            weight: 2,
            opacity: 0.7
        }).addTo(map);

        // Marqueur central
        const marker = L.circleMarker(coords, {
            color: color,
            fillColor: color,
            fillOpacity: 0.9,
            radius: 5,
            weight: 1
        }).addTo(map);

        // Popup professionnel
        const popupContent = `
            <div style="min-width: 200px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                <div style="border-bottom: 2px solid ${color}; padding-bottom: 6px; margin-bottom: 8px;">
                    <h3 style="margin: 0; color: #1f2937; font-size: 13px; font-weight: 600;">${zone.zone_name}</h3>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; font-size: 11px; margin-bottom: 8px;">
                    <div style="background: #f3f4f6; padding: 3px; border-radius: 3px;">
                        <strong style="color: #374151;">Score:</strong> ${score}/100
                    </div>
                    <div style="background: #f3f4f6; padding: 3px; border-radius: 3px;">
                        <strong style="color: #374151;">Unités:</strong> ${zone.unites_count}
                    </div>
                </div>
                <div style="font-size: 10px; color: #6b7280; line-height: 1.3;">
                    <strong>Recommandation:</strong><br>
                    ${zone.recommendation}
                </div>
            </div>
        `;

        circle.bindPopup(popupContent);
        marker.bindPopup(`
            <div style="min-width: 150px; text-align: center;">
                <div style="font-weight: 600; color: #1f2937; margin-bottom: 3px; font-size: 12px;">${zone.zone_name}</div>
                <div style="font-size: 16px; font-weight: bold; color: ${color};">${score}/100</div>
                <div style="font-size: 10px; color: #6b7280;">Score d'opportunité</div>
            </div>
        `);
    });
}

// Générer les propositions d'investissement
async function generateProposals() {
    // Utiliser directement les données de test
    const testProposals = [
        {
            zone_name: 'Zone Centre-Ville',
            investment_type: 'Supermarché',
            score: 85,
            estimated_revenue: 150000000,
            investment_cost: 80000000,
            roi_estimate: 87.5,
            timeline: '6-12 mois',
            risks: ['Concurrence modérée', 'Coût d\'installation élevé']
        },
        {
            zone_name: 'Zone Louly Benteigné',
            investment_type: 'Boutique générale',
            score: 70,
            estimated_revenue: 80000000,
            investment_cost: 25000000,
            roi_estimate: 220.0,
            timeline: '3-6 mois',
            risks: ['Risques faibles']
        },
        {
            zone_name: 'Zone Route de Thiès',
            investment_type: 'Station-service',
            score: 60,
            estimated_revenue: 120000000,
            investment_cost: 100000000,
            roi_estimate: 20.0,
            timeline: '8-15 mois',
            risks: ['Investissement important', 'Autorisations nécessaires']
        },
        {
            zone_name: 'Zone Centre-Ville',
            investment_type: 'Restaurant',
            score: 80,
            estimated_revenue: 90000000,
            investment_cost: 40000000,
            roi_estimate: 125.0,
            timeline: '4-8 mois',
            risks: ['Concurrence existante']
        },
        {
            zone_name: 'Zone Rurale Sud',
            investment_type: 'Atelier mécanique',
            score: 55,
            estimated_revenue: 60000000,
            investment_cost: 35000000,
            roi_estimate: 71.4,
            timeline: '5-10 mois',
            risks: ['Population limitée', 'Accessibilité réduite']
        }
    ];
    
    displayProposals(testProposals);
}

// Afficher les propositions
function displayProposals(proposals) {
    const container = document.getElementById('proposals-container');
    
    if (proposals.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500">Aucune proposition disponible</div>';
        return;
    }

    let html = '<div class="space-y-3">';
    
    proposals.slice(0, 3).forEach(proposal => {
        const roiColor = proposal.roi_estimate >= 100 ? 'text-green-600' : 
                        proposal.roi_estimate >= 50 ? 'text-blue-600' : 'text-orange-600';
        
        html += `
            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="font-semibold text-sm text-gray-900">${proposal.zone_name}</div>
                    <span class="px-2 py-1 rounded text-white text-xs ${proposal.score >= 70 ? 'bg-green-500' : proposal.score >= 50 ? 'bg-yellow-500' : 'bg-red-500'}">${proposal.score}/100</span>
                </div>
                <div class="text-sm font-medium text-blue-600 mb-1">${proposal.investment_type}</div>
                <div class="space-y-1 text-xs text-gray-600">
                    <div>Revenus estimés: ${proposal.estimated_revenue.toLocaleString()} FCFA</div>
                    <div>Coût d'investissement: ${proposal.investment_cost.toLocaleString()} FCFA</div>
                    <div class="font-semibold ${roiColor}">ROI: ${proposal.roi_estimate}%</div>
                    <div>Délai: ${proposal.timeline}</div>
                </div>
                <div class="mt-2">
                    <div class="text-xs text-gray-500 mb-1">Risques:</div>
                    <div class="text-xs text-red-600">
                        ${Array.isArray(proposal.risks) ? proposal.risks.join(', ') : proposal.risks}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Toggle couche population
function togglePopulationLayer() {
    const checkbox = document.getElementById('population-layer');
    
    if (checkbox.checked) {
        // Utiliser directement les données de test pour Sandiara
        const points = [
            [14.43540, -16.79153, 5000], // Centre-ville - haute densité
            [14.44200, -16.79500, 3000], // Nord - Louly Benteigné
            [14.42800, -16.78500, 2500], // Sud - Zone rurale
            [14.44000, -16.78000, 2000], // Est - Route de Thiès
            [14.43000, -16.80000, 1800], // Ouest - Zone agricole
            [14.43800, -16.78800, 3500], // Centre commercial
            [14.44500, -16.79000, 1500], // Nord-Est
            [14.43200, -16.78300, 1200], // Sud-Est
            [14.43600, -16.79300, 2800], // Zone intermédiaire
            [14.44100, -16.78700, 2200], // Zone résidentielle
        ];
        
        populationLayer = L.heatLayer(points, {radius: 50, blur: 25, maxZoom: 18}).addTo(map);
        console.log('Population layer activated with', points.length, 'points');
    } else {
        if (populationLayer) {
            map.removeLayer(populationLayer);
            populationLayer = null;
            console.log('Population layer deactivated');
        }
    }
}

// Toggle couche routes
function toggleRoadsLayer() {
    const checkbox = document.getElementById('roads-layer');
    
    if (checkbox.checked) {
        // Utiliser directement les données de test
        roadsLayer = L.layerGroup();
        
        // Routes complètes de Sandiara - numérisation professionnelle
        const sandiaraRoads = [
            // Routes principales (RN1 et axes majeurs)
            {
                name: 'Route Nationale 1 (RN1)',
                coordinates: [[14.44382, -16.80247], [14.44382, -16.78059]],
                importance: 'primary',
                color: '#dc2626',
                weight: 10,
                description: 'Axe principal Dakar-Thiès'
            },
            {
                name: 'Route de Thiès (N1)',
                coordinates: [[14.43540, -16.79153], [14.44000, -16.78000], [14.44382, -16.78059]],
                importance: 'primary',
                color: '#dc2626',
                weight: 8,
                description: 'Branche vers Thiès'
            },
            {
                name: 'Route de Mbour (N1)',
                coordinates: [[14.43540, -16.79153], [14.43000, -16.80000], [14.42699, -16.80247]],
                importance: 'primary',
                color: '#dc2626',
                weight: 8,
                description: 'Branche vers Mbour'
            },
            
            // Routes secondaires (départementales)
            {
                name: 'Route Départementale D1',
                coordinates: [[14.43540, -16.79153], [14.43800, -16.78800], [14.44000, -16.78500]],
                importance: 'secondary',
                color: '#f59e0b',
                weight: 6,
                description: 'Axe Centre-Ville - Zone Est'
            },
            {
                name: 'Route Départementale D2',
                coordinates: [[14.43540, -16.79153], [14.43200, -16.79400], [14.42800, -16.79600]],
                importance: 'secondary',
                color: '#f59e0b',
                weight: 6,
                description: 'Axe Centre-Ville - Zone Sud'
            },
            {
                name: 'Route Départementale D3',
                coordinates: [[14.43540, -16.79153], [14.44200, -16.79500], [14.44500, -16.79800]],
                importance: 'secondary',
                color: '#f59e0b',
                weight: 6,
                description: 'Axe Centre-Ville - Louly Benteigné'
            },
            
            // Routes tertiaires (communales)
            {
                name: 'Route Communale C1 - Centre Commercial',
                coordinates: [[14.43540, -16.79153], [14.43590, -16.79203], [14.43640, -16.79253]],
                importance: 'tertiary',
                color: '#6b7280',
                weight: 4,
                description: 'Accès Centre Commercial'
            },
            {
                name: 'Route Communale C2 - Marché Central',
                coordinates: [[14.43590, -16.79203], [14.43640, -16.79153], [14.43690, -16.79103]],
                importance: 'tertiary',
                color: '#6b7280',
                weight: 4,
                description: 'Accès Marché Central'
            },
            {
                name: 'Route Communale C3 - Zone Administrative',
                coordinates: [[14.43540, -16.79153], [14.43640, -16.79053], [14.43740, -16.78953]],
                importance: 'tertiary',
                color: '#6b7280',
                weight: 4,
                description: 'Accès Mairie et Services'
            },
            {
                name: 'Route Communale C4 - Quartier Nord',
                coordinates: [[14.44200, -16.79500], [14.44250, -16.79450], [14.44300, -16.79400]],
                importance: 'tertiary',
                color: '#6b7280',
                weight: 4,
                description: 'Accès Quartier Résidentiel Nord'
            },
            {
                name: 'Route Communale C5 - Zone Industrielle',
                coordinates: [[14.42900, -16.78600], [14.43000, -16.78500], [14.43100, -16.78400]],
                importance: 'tertiary',
                color: '#6b7280',
                weight: 4,
                description: 'Accès Zone Industrielle'
            },
            {
                name: 'Route Communale C6 - Quartier Sud',
                coordinates: [[14.42800, -16.78500], [14.42900, -16.78400], [14.43000, -16.78300]],
                importance: 'tertiary',
                color: '#6b7280',
                weight: 4,
                description: 'Accès Quartier Résidentiel Sud'
            },
            {
                name: 'Route Communale C7 - Zone Commerciale Est',
                coordinates: [[14.44000, -16.78000], [14.44100, -16.77900], [14.44200, -16.77800]],
                importance: 'tertiary',
                color: '#6b7280',
                weight: 4,
                description: 'Accès Zone Commerciale Est'
            },
            {
                name: 'Route Communale C8 - Zone Agricole',
                coordinates: [[14.43000, -16.80000], [14.43100, -16.79900], [14.43200, -16.79800]],
                importance: 'tertiary',
                color: '#6b7280',
                weight: 4,
                description: 'Accès Zone Agricole'
            },
            
            // Routes de desserte locale
            {
                name: 'Route Locale L1 - Marché Louma',
                coordinates: [[14.43640, -16.79153], [14.43690, -16.79103], [14.43740, -16.79053]],
                importance: 'local',
                color: '#9ca3af',
                weight: 3,
                description: 'Desserte Marché Hebdomadaire'
            },
            {
                name: 'Route Locale L2 - École Primaire',
                coordinates: [[14.43540, -16.79153], [14.43590, -16.79053], [14.43640, -16.78953]],
                importance: 'local',
                color: '#9ca3af',
                weight: 3,
                description: 'Desserte École Primaire'
            },
            {
                name: 'Route Locale L3 - Dispensaire',
                coordinates: [[14.43540, -16.79153], [14.43490, -16.79203], [14.43440, -16.79253]],
                importance: 'local',
                color: '#9ca3af',
                weight: 3,
                description: 'Desserte Dispensaire'
            },
            {
                name: 'Route Locale L4 - Zone de Services',
                coordinates: [[14.43800, -16.78800], [14.43850, -16.78750], [14.43900, -16.78700]],
                importance: 'local',
                color: '#9ca3af',
                weight: 3,
                description: 'Desserte Services Publics'
            },
            {
                name: 'Route Locale L5 - Station-Service',
                coordinates: [[14.44000, -16.78000], [14.44050, -16.78050], [14.44100, -16.78100]],
                importance: 'local',
                color: '#9ca3af',
                weight: 3,
                description: 'Desserte Station-Service'
            }
        ];
        
        sandiaraRoads.forEach(road => {
            const polyline = L.polyline(road.coordinates, {
                color: road.color,
                weight: road.weight,
                opacity: 0.9,
                dashArray: road.importance === 'local' ? '5, 5' : null
            }).addTo(roadsLayer);
            
            polyline.bindPopup(`
                <div style="min-width: 250px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                    <div style="border-bottom: 2px solid ${road.color}; padding-bottom: 6px; margin-bottom: 8px;">
                        <h6 style="margin: 0; color: #1f2937; font-size: 13px; font-weight: 600;">${road.name}</h6>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; font-size: 11px; margin-bottom: 8px;">
                        <div style="background: #f3f4f6; padding: 3px; border-radius: 3px;">
                            <strong style="color: #374151;">Type:</strong> ${road.importance}
                        </div>
                        <div style="background: #f3f4f6; padding: 3px; border-radius: 3px;">
                            <strong style="color: #374151;">Largeur:</strong> ${road.weight}m
                        </div>
                    </div>
                    <div style="font-size: 10px; color: #6b7280; line-height: 1.3;">
                        <strong>Description:</strong><br>
                        ${road.description}
                    </div>
                </div>
            `);
        });
        
        roadsLayer.addTo(map);
        console.log('Roads layer activated with', sandiaraRoads.length, 'roads');
    } else {
        if (roadsLayer) {
            map.removeLayer(roadsLayer);
            roadsLayer = null;
            console.log('Roads layer deactivated');
        }
    }
}

// Toggle couche bâti
function toggleBatiLayer() {
    const checkbox = document.getElementById('bati-layer');
    
    if (checkbox.checked) {
        // Utiliser directement les données de test
        batiLayer = L.layerGroup();
        
        // Bâtiments complets de Sandiara - numérisation professionnelle
        const sandiaraBuildings = [
            // Bâtiments administratifs et publics
            {
                name: 'Hôtel de Ville de Sandiara',
                coordinates: [[14.43540, -16.79153], [14.43640, -16.79053]],
                usage: 'administrative',
                color: '#1e40af',
                description: 'Mairie et services municipaux',
                surface: '800 m²',
                etat: 'Excellent'
            },
            {
                name: 'Centre de Santé',
                coordinates: [[14.43490, -16.79203], [14.43590, -16.79103]],
                usage: 'healthcare',
                color: '#dc2626',
                description: 'Dispensaire et soins primaires',
                surface: '600 m²',
                etat: 'Bon'
            },
            {
                name: 'École Primaire',
                coordinates: [[14.43590, -16.79053], [14.43690, -16.78953]],
                usage: 'education',
                color: '#059669',
                description: 'École primaire publique',
                surface: '1200 m²',
                etat: 'Bon'
            },
            {
                name: 'Poste de Police',
                coordinates: [[14.43440, -16.79253], [14.43540, -16.79153]],
                usage: 'security',
                color: '#7c3aed',
                description: 'Commissariat de police',
                surface: '400 m²',
                etat: 'Bon'
            },
            
            // Bâtiments commerciaux
            {
                name: 'Centre Commercial Principal',
                coordinates: [[14.43640, -16.79253], [14.43740, -16.79153]],
                usage: 'commercial',
                color: '#3b82f6',
                description: 'Centre commercial avec boutiques',
                surface: '2000 m²',
                etat: 'Excellent'
            },
            {
                name: 'Marché Central (Louma)',
                coordinates: [[14.43590, -16.79203], [14.43690, -16.79103]],
                usage: 'market',
                color: '#f59e0b',
                description: 'Marché hebdomadaire et boutiques',
                surface: '1500 m²',
                etat: 'Bon'
            },
            {
                name: 'Supermarché',
                coordinates: [[14.43740, -16.79253], [14.43840, -16.79153]],
                usage: 'commercial',
                color: '#3b82f6',
                description: 'Supermarché moderne',
                surface: '800 m²',
                etat: 'Excellent'
            },
            {
                name: 'Station-Service',
                coordinates: [[14.44000, -16.78000], [14.44100, -16.77900]],
                usage: 'fuel',
                color: '#f59e0b',
                description: 'Station-service avec boutique',
                surface: '500 m²',
                etat: 'Bon'
            },
            {
                name: 'Boutiques Centre-Ville',
                coordinates: [[14.43540, -16.79153], [14.43640, -16.79053]],
                usage: 'commercial',
                color: '#3b82f6',
                description: 'Ensemble de boutiques',
                surface: '600 m²',
                etat: 'Bon'
            },
            
            // Bâtiments résidentiels
            {
                name: 'Quartier Résidentiel Nord',
                coordinates: [[14.44100, -16.79600], [14.44300, -16.79400]],
                usage: 'residential',
                color: '#10b981',
                description: 'Zone résidentielle Louly Benteigné',
                surface: '5000 m²',
                etat: 'Bon'
            },
            {
                name: 'Quartier Résidentiel Sud',
                coordinates: [[14.42800, -16.78500], [14.43200, -16.78300]],
                usage: 'residential',
                color: '#10b981',
                description: 'Zone résidentielle sud',
                surface: '4000 m²',
                etat: 'Moyen'
            },
            {
                name: 'Logements Sociaux',
                coordinates: [[14.43690, -16.79053], [14.43790, -16.78953]],
                usage: 'residential',
                color: '#10b981',
                description: 'Logements sociaux et HLM',
                surface: '3000 m²',
                etat: 'Bon'
            },
            
            // Bâtiments industriels et techniques
            {
                name: 'Zone Industrielle',
                coordinates: [[14.42900, -16.78600], [14.43100, -16.78400]],
                usage: 'industrial',
                color: '#f59e0b',
                description: 'Zone d\'activités économiques',
                surface: '8000 m²',
                etat: 'Bon'
            },
            {
                name: 'Atelier Mécanique',
                coordinates: [[14.43000, -16.78500], [14.43100, -16.78400]],
                usage: 'industrial',
                color: '#f59e0b',
                description: 'Atelier de réparation automobile',
                surface: '400 m²',
                etat: 'Bon'
            },
            {
                name: 'Entrepôt Logistique',
                coordinates: [[14.43100, -16.78600], [14.43200, -16.78500]],
                usage: 'warehouse',
                color: '#6b7280',
                description: 'Entrepôt de stockage',
                surface: '1000 m²',
                etat: 'Bon'
            },
            
            // Bâtiments de services
            {
                name: 'Banque',
                coordinates: [[14.43540, -16.79153], [14.43640, -16.79053]],
                usage: 'financial',
                color: '#8b5cf6',
                description: 'Agence bancaire',
                surface: '300 m²',
                etat: 'Excellent'
            },
            {
                name: 'Bureau de Poste',
                coordinates: [[14.43640, -16.79153], [14.43740, -16.79053]],
                usage: 'postal',
                color: '#8b5cf6',
                description: 'Bureau de poste et télécommunications',
                surface: '250 m²',
                etat: 'Bon'
            },
            {
                name: 'Restaurant',
                coordinates: [[14.43590, -16.79053], [14.43690, -16.78953]],
                usage: 'restaurant',
                color: '#ef4444',
                description: 'Restaurant local',
                surface: '200 m²',
                etat: 'Bon'
            },
            {
                name: 'Hôtel',
                coordinates: [[14.43740, -16.79153], [14.43840, -16.79053]],
                usage: 'hotel',
                color: '#8b5cf6',
                description: 'Hôtel de passage',
                surface: '600 m²',
                etat: 'Bon'
            },
            
            // Bâtiments agricoles
            {
                name: 'Zone Agricole Ouest',
                coordinates: [[14.43000, -16.80000], [14.43200, -16.79800]],
                usage: 'agricultural',
                color: '#059669',
                description: 'Zone agricole et rurale',
                surface: '10000 m²',
                etat: 'Moyen'
            },
            {
                name: 'Coopérative Agricole',
                coordinates: [[14.43100, -16.79900], [14.43200, -16.79800]],
                usage: 'agricultural',
                color: '#059669',
                description: 'Coopérative agricole',
                surface: '800 m²',
                etat: 'Bon'
            },
            
            // Bâtiments religieux
            {
                name: 'Mosquée Centrale',
                coordinates: [[14.43540, -16.79153], [14.43640, -16.79053]],
                usage: 'religious',
                color: '#8b5cf6',
                description: 'Mosquée principale',
                surface: '500 m²',
                etat: 'Excellent'
            },
            {
                name: 'Église',
                coordinates: [[14.43690, -16.79103], [14.43790, -16.79003]],
                usage: 'religious',
                color: '#8b5cf6',
                description: 'Église catholique',
                surface: '400 m²',
                etat: 'Bon'
            }
        ];
        
        sandiaraBuildings.forEach(building => {
            const rectangle = L.rectangle(building.coordinates, {
                color: building.color,
                fillColor: building.color,
                fillOpacity: 0.6,
                weight: 2,
                opacity: 0.8
            }).addTo(batiLayer);
            
            rectangle.bindPopup(`
                <div style="min-width: 280px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                    <div style="border-bottom: 2px solid ${building.color}; padding-bottom: 6px; margin-bottom: 8px;">
                        <h6 style="margin: 0; color: #1f2937; font-size: 13px; font-weight: 600;">${building.name}</h6>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; font-size: 11px; margin-bottom: 8px;">
                        <div style="background: #f3f4f6; padding: 3px; border-radius: 3px;">
                            <strong style="color: #374151;">Usage:</strong> ${building.usage}
                        </div>
                        <div style="background: #f3f4f6; padding: 3px; border-radius: 3px;">
                            <strong style="color: #374151;">Surface:</strong> ${building.surface}
                        </div>
                        <div style="background: #f3f4f6; padding: 3px; border-radius: 3px;">
                            <strong style="color: #374151;">État:</strong> ${building.etat}
                        </div>
                        <div style="background: #f3f4f6; padding: 3px; border-radius: 3px;">
                            <strong style="color: #374151;">Type:</strong> ${building.usage}
                        </div>
                    </div>
                    <div style="font-size: 10px; color: #6b7280; line-height: 1.3;">
                        <strong>Description:</strong><br>
                        ${building.description}
                    </div>
                </div>
            `);
        });
        
        batiLayer.addTo(map);
        console.log('Bati layer activated with', sandiaraBuildings.length, 'buildings');
    } else {
        if (batiLayer) {
            map.removeLayer(batiLayer);
            batiLayer = null;
            console.log('Bati layer deactivated');
        }
    }
}

// Toggle heatmap
function toggleHeatmapLayer() {
    const checkbox = document.getElementById('heatmap-layer');
    
    if (checkbox.checked) {
        // Créer des points de chaleur pour Sandiara - données plus visibles
        const points = [
            [14.43540, -16.79153, 85], // Centre-ville - haute densité
            [14.44200, -16.79500, 70], // Louly Benteigné - densité moyenne
            [14.42800, -16.78500, 55], // Zone rurale - densité faible
            [14.44000, -16.78000, 60], // Route de Thiès - densité moyenne
            [14.43000, -16.80000, 40], // Zone agricole - densité très faible
            [14.43800, -16.78800, 75], // Centre commercial - haute densité
            [14.44500, -16.79000, 50], // Nord-Est - densité faible
            [14.43200, -16.78300, 45], // Sud-Est - densité faible
            [14.43600, -16.79300, 65], // Zone intermédiaire
            [14.44100, -16.78700, 55], // Zone résidentielle
            [14.43700, -16.78900, 80], // Zone commerciale dense
            [14.43300, -16.78400, 35], // Zone périphérique
        ];
        
        heatmapLayer = L.heatLayer(points, {
            radius: 60, 
            blur: 30, 
            maxZoom: 18,
            gradient: {
                0.4: 'blue',
                0.6: 'lime',
                0.8: 'orange',
                1.0: 'red'
            }
        }).addTo(map);
        
        console.log('Heatmap layer activated with', points.length, 'points');
    } else {
        if (heatmapLayer) {
            map.removeLayer(heatmapLayer);
            heatmapLayer = null;
            console.log('Heatmap layer deactivated');
        }
    }
}

// Toggle carte multi-critères
function toggleCombinedLayer() {
    const checkbox = document.getElementById('combined-layer');
    
    if (checkbox.checked) {
        // Créer une couche combinée avec les zones de Sandiara
        combinedLayer = L.layerGroup();
        
        // Zones d'analyse multi-critères professionnelles
        const zones = [
            {
                name: 'Zone Centre-Ville',
                lat: 14.43540,
                lng: -16.79153,
                score: 85,
                density: 90.5,
                accessibility: 85.2,
                built: 80.8,
                population: 85.0,
                opportunities: ['Supermarché', 'Restaurant', 'Banque', 'Services'],
                risks: ['Concurrence élevée', 'Coûts élevés'],
                recommendation: 'Zone d\'excellence - Investissement fortement recommandé'
            },
            {
                name: 'Zone Louly Benteigné',
                lat: 14.44200,
                lng: -16.79500,
                score: 70,
                density: 65.3,
                accessibility: 75.1,
                built: 70.2,
                population: 70.5,
                opportunities: ['Boutique générale', 'Pharmacie', 'École privée'],
                risks: ['Concurrence modérée'],
                recommendation: 'Zone prometteuse - Investissement recommandé'
            },
            {
                name: 'Zone Rurale Sud',
                lat: 14.42800,
                lng: -16.78500,
                score: 55,
                density: 45.8,
                accessibility: 60.4,
                built: 50.1,
                population: 55.2,
                opportunities: ['Atelier mécanique', 'Boutique rurale'],
                risks: ['Population limitée', 'Accessibilité réduite'],
                recommendation: 'Zone modérée - Investissement possible avec prudence'
            },
            {
                name: 'Zone Route de Thiès',
                lat: 14.44000,
                lng: -16.78000,
                score: 60,
                density: 55.2,
                accessibility: 80.6,
                built: 50.8,
                population: 55.1,
                opportunities: ['Station-service', 'Restaurant routier', 'Hôtel'],
                risks: ['Investissement important', 'Autorisations nécessaires'],
                recommendation: 'Zone modérée - Investissement possible avec prudence'
            },
            {
                name: 'Zone Agricole',
                lat: 14.43000,
                lng: -16.80000,
                score: 40,
                density: 35.1,
                accessibility: 45.3,
                built: 40.2,
                population: 35.8,
                opportunities: ['Coopérative agricole', 'Entrepôt'],
                risks: ['Population très limitée', 'Accessibilité faible'],
                recommendation: 'Zone à faible potentiel - Investissement déconseillé'
            },
            {
                name: 'Zone Commerciale Est',
                lat: 14.44100,
                lng: -16.77900,
                score: 65,
                density: 60.2,
                accessibility: 75.8,
                built: 55.4,
                population: 60.1,
                opportunities: ['Centre commercial', 'Boutiques spécialisées'],
                risks: ['Concurrence modérée'],
                recommendation: 'Zone prometteuse - Investissement recommandé'
            },
            {
                name: 'Zone de Services',
                lat: 14.43850,
                lng: -16.78750,
                score: 75,
                density: 70.8,
                accessibility: 80.3,
                built: 75.6,
                population: 72.4,
                opportunities: ['Services publics', 'Bureau de poste', 'Clinique'],
                risks: ['Réglementation stricte'],
                recommendation: 'Zone d\'excellence - Investissement fortement recommandé'
            },
            {
                name: 'Zone Industrielle',
                lat: 14.43000,
                lng: -16.78600,
                score: 50,
                density: 40.5,
                accessibility: 65.2,
                built: 85.0,
                population: 30.1,
                opportunities: ['Atelier industriel', 'Entrepôt logistique'],
                risks: ['Population limitée', 'Zone d\'emploi'],
                recommendation: 'Zone spécialisée - Investissement industriel uniquement'
            },
            {
                name: 'Zone Résidentielle Nord',
                lat: 14.44300,
                lng: -16.79400,
                score: 68,
                density: 58.3,
                accessibility: 72.1,
                built: 68.2,
                population: 78.5,
                opportunities: ['École privée', 'Pharmacie', 'Boutique de proximité'],
                risks: ['Concurrence modérée'],
                recommendation: 'Zone prometteuse - Investissement recommandé'
            }
        ];
        
        zones.forEach(zone => {
            // Couleur basée sur le score global avec des couleurs professionnelles
            let color, fillColor, borderColor, textColor;
            if (zone.score >= 75) {
                color = '#059669'; // Vert excellence
                fillColor = '#10b981';
                borderColor = '#047857';
                textColor = '#065f46';
            } else if (zone.score >= 60) {
                color = '#d97706'; // Orange prometteur
                fillColor = '#f59e0b';
                borderColor = '#b45309';
                textColor = '#92400e';
            } else if (zone.score >= 45) {
                color = '#dc2626'; // Rouge modéré
                fillColor = '#ef4444';
                borderColor = '#b91c1c';
                textColor = '#991b1b';
            } else {
                color = '#6b7280'; // Gris faible
                fillColor = '#9ca3af';
                borderColor = '#6b7280';
                textColor = '#374151';
            }
            
            // Taille professionnelle basée sur le score et la densité
            const radius = Math.max(20, Math.min(zone.score * 6 + zone.density * 0.3, 80));
            
            const circle = L.circle([zone.lat, zone.lng], {
                color: borderColor,
                fillColor: fillColor,
                fillOpacity: 0.35,
                radius: radius,
                weight: 2,
                opacity: 0.8
            }).addTo(combinedLayer);
            
            // Marqueur central professionnel
            const marker = L.circleMarker([zone.lat, zone.lng], {
                color: color,
                fillColor: color,
                fillOpacity: 0.9,
                radius: 6,
                weight: 1
            }).addTo(combinedLayer);
            
            // Popup professionnel détaillé
            const popupContent = `
                <div style="min-width: 300px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                    <div style="border-bottom: 3px solid ${color}; padding-bottom: 8px; margin-bottom: 12px;">
                        <h3 style="margin: 0; color: #1f2937; font-size: 14px; font-weight: 600;">${zone.name}</h3>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; font-size: 11px; margin-bottom: 12px;">
                        <div style="background: #f3f4f6; padding: 4px; border-radius: 4px; border-left: 3px solid ${color};">
                            <strong style="color: #374151;">Score Global:</strong><br>
                            <span style="color: ${textColor}; font-weight: bold; font-size: 12px;">${zone.score}/100</span>
                        </div>
                        <div style="background: #f3f4f6; padding: 4px; border-radius: 4px;">
                            <strong style="color: #374151;">Densité:</strong><br>
                            <span style="color: #374151;">${zone.density}</span>
                        </div>
                        <div style="background: #f3f4f6; padding: 4px; border-radius: 4px;">
                            <strong style="color: #374151;">Accessibilité:</strong><br>
                            <span style="color: #374151;">${zone.accessibility}</span>
                        </div>
                        <div style="background: #f3f4f6; padding: 4px; border-radius: 4px;">
                            <strong style="color: #374151;">Potentiel Bâti:</strong><br>
                            <span style="color: #374151;">${zone.built}</span>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 12px;">
                        <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">
                            <strong>Population:</strong> ${zone.population}
                        </div>
                        <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">
                            <strong>Rayon d'influence:</strong> ${radius.toFixed(0)}m
                        </div>
                    </div>
                    
                    <div style="border-top: 1px solid #e5e7eb; padding-top: 8px;">
                        <div style="font-size: 10px; color: #6b7280; line-height: 1.4;">
                            <strong style="color: ${textColor};">Recommandation:</strong><br>
                            ${zone.recommendation}
                        </div>
                    </div>
                </div>
            `;
            
            circle.bindPopup(popupContent);
            
            // Popup simplifié pour le marqueur central
            marker.bindPopup(`
                <div style="min-width: 200px; text-align: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 6px; font-size: 12px;">${zone.name}</div>
                    <div style="font-size: 20px; font-weight: bold; color: ${textColor}; margin-bottom: 4px;">${zone.score}/100</div>
                    <div style="font-size: 10px; color: #6b7280; margin-bottom: 8px;">Score d'opportunité</div>
                    <div style="font-size: 9px; color: #6b7280; line-height: 1.3;">
                        <strong>Opportunités:</strong><br>
                        ${zone.opportunities.slice(0, 2).join(', ')}
                    </div>
                </div>
            `);
        });
        
        combinedLayer.addTo(map);
        console.log('Combined layer activated with', zones.length, 'zones');
    } else {
        if (combinedLayer) {
            map.removeLayer(combinedLayer);
            combinedLayer = null;
            console.log('Combined layer deactivated');
        }
    }
}
</script>
@endsection
