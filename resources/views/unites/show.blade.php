@extends('layouts.app')

@section('title', 'Détails de l\'unité commerciale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-store"></i>
                        {{ $unite->nom_proprietaire }}
                    </h3>
                    <div>
                        @if(!auth()->user()->isCollecteur())
                            <a href="{{ route('unites.edit', $unite) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                                Modifier
                            </a>
                        @endif
                        <a href="{{ route('unites.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Informations du propriétaire -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-user"></i> Propriétaire</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nom complet</label>
                                                <p class="form-control-plaintext">{{ $unite->nom_proprietaire }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Téléphone</label>
                                                <p class="form-control-plaintext">{{ $unite->telephone ?: 'Non renseigné' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Email</label>
                                                <p class="form-control-plaintext">{{ $unite->email ?: 'Non renseigné' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">NINEA</label>
                                                <p class="form-control-plaintext">{{ $unite->ninea ?: 'Non renseigné' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations du commerce -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-store"></i> Commerce</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nature</label>
                                                <p class="form-control-plaintext">{{ $unite->nature }}</p>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Catégorie</label>
                                                <p class="form-control-plaintext">
                                                    <span class="badge bg-{{ $unite->categorie === 'activite_commerciale' ? 'info' : 'warning' }} fs-6">
                                                        {{ $unite->categorie === 'activite_commerciale' ? 'Activité commerciale' : 'Point d\'intérêt' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Statut</label>
                                                <p class="form-control-plaintext">
                                                    <span class="badge bg-{{ $unite->actif ? 'success' : 'danger' }} fs-6">
                                                        {{ $unite->actif ? 'Actif' : 'Inactif' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Localisation -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Localisation</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Zone fiscale</label>
                                                <p class="form-control-plaintext">
                                                    @if($unite->zone)
                                                        <span class="badge bg-secondary fs-6">{{ $unite->zone->nom }}</span>
                                                    @else
                                                        <span class="text-muted">Non assignée</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Coordonnées</label>
                                                <p class="form-control-plaintext">{{ $unite->latitude }}, {{ $unite->longitude }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @if($unite->adresse)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Adresse</label>
                                            <p class="form-control-plaintext">{{ $unite->adresse }}</p>
                                        </div>
                                    @endif
                                    
                                    @if($unite->superficie)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-ruler-combined"></i> Superficie du terrain
                                            </label>
                                            <p class="form-control-plaintext">
                                                <span class="badge bg-info fs-6">
                                                    {{ number_format($unite->superficie, 2, ',', ' ') }} m²
                                                </span>
                                                @if(!empty($unite->geometrie_polygon))
                                                    <span class="badge bg-success ms-2">
                                                        <i class="fas fa-check-circle"></i> Polygone enregistré
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($unite->superficie && !empty($unite->geometrie_polygon))
                            <!-- Carte du terrain (polygone) -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-map"></i> Carte du terrain
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div id="terrain-map" style="height: 400px; width: 100%;"></div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <!-- Photo du commerce -->
                            @if($unite->photo)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-camera"></i> Photo du commerce</h5>
                                </div>
                                <div class="card-body text-center">
                                    <img src="{{ asset('storage/' . $unite->photo) }}" 
                                         alt="Photo du commerce" 
                                         class="img-fluid rounded shadow" 
                                         style="max-height: 300px; width: 100%; object-fit: cover;">
                                </div>
                            </div>
                            @endif

                            <!-- Statistiques -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Informations</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Taxe applicable</label>
                                        <p class="form-control-plaintext">
                                            @if($unite->taxe)
                                                {{ $unite->taxe->nom }}
                                                @if($unite->taxe->is_fonciere ?? false)
                                                    <span class="badge bg-warning text-dark ms-1">
                                                        <i class="fas fa-ruler-combined"></i> Foncière
                                                    </span>
                                                @endif
                                                <br>
                                                @if($unite->taxe->is_fonciere ?? false)
                                                    <strong>{{ number_format($unite->taxe->montant_par_m2 ?? 0, 0, ',', ' ') }} FCFA/m²</strong>
                                                    @if($unite->superficie)
                                                        <br><small class="text-muted">
                                                            Montant calculé : {{ number_format($unite->superficie * ($unite->taxe->montant_par_m2 ?? 0), 0, ',', ' ') }} FCFA
                                                            ({{ number_format($unite->superficie, 2, ',', ' ') }} m²)
                                                        </small>
                                                    @endif
                                                @else
                                                    <strong>{{ number_format($unite->taxe->montant, 0, ',', ' ') }} FCFA</strong>
                                                @endif
                                            @else
                                                <span class="text-muted">Aucune taxe assignée</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Créé le</label>
                                        <p class="form-control-plaintext">{{ $unite->created_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Modifié le</label>
                                        <p class="form-control-plaintext">{{ $unite->updated_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistiques de paiements -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Statistiques paiements</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $paiementsValides = $unite->paiements->where('statut', 'valide');
                                        $paiementsEnAttente = $unite->paiements->where('statut', 'en_attente');
                                        $totalPaye = $paiementsValides->sum('montant');
                                        $dernierPaiement = $unite->paiements->sortByDesc('date_paiement')->first();
                                    @endphp
                                    
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <h6 class="text-success mb-1">{{ number_format($totalPaye, 0, ',', ' ') }}</h6>
                                                <small class="text-muted">FCFA payés</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <h6 class="text-warning mb-1">{{ $paiementsEnAttente->count() }}</h6>
                                                <small class="text-muted">En attente</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($dernierPaiement)
                                        <hr>
                                        <div class="text-center">
                                            <small class="text-muted">Dernier paiement</small><br>
                                            <strong>{{ $dernierPaiement->date_paiement->format('d/m/Y') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Paiements récents -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-money-bill"></i> Paiements récents</h5>
                                </div>
                                <div class="card-body">
                                    @if($unite->paiements && $unite->paiements->count() > 0)
                                        @foreach($unite->paiements->sortByDesc('date_paiement')->take(3) as $paiement)
                                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                                <div>
                                                    <small class="fw-bold">{{ $paiement->date_paiement->format('d/m/Y') }}</small><br>
                                                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-{{ $paiement->statut === 'valide' ? 'success' : 'warning' }}">
                                                    {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA
                                                    </span><br>
                                                    <small class="text-muted">{{ ucfirst($paiement->statut) }}</small>
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        @if($unite->paiements->count() > 3)
                                            <div class="text-center mt-2">
                                                <button class="btn btn-sm btn-outline-primary" onclick="showAllPayments()">
                                                    Voir tous les paiements ({{ $unite->paiements->count() }})
                                                </button>
                                            </div>
                                        @endif
                                    @else
                                        <p class="text-muted mb-0">Aucun paiement enregistré</p>
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

<!-- Modal pour l'historique complet des paiements -->
<div class="modal fade" id="paymentsHistoryModal" tabindex="-1" aria-labelledby="paymentsHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentsHistoryModalLabel">
                    <i class="fas fa-history"></i> Historique des paiements - {{ $unite->nom_proprietaire }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($unite->paiements && $unite->paiements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Mode</th>
                                    <th>Collecteur</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unite->paiements->sortByDesc('date_paiement') as $paiement)
                                    <tr>
                                        <td>
                                            <strong>{{ $paiement->date_paiement->format('d/m/Y') }}</strong><br>
                                            <small class="text-muted">{{ $paiement->date_paiement->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $paiement->mode_paiement === 'cash' ? 'success' : ($paiement->mode_paiement === 'wave' ? 'info' : 'warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $paiement->collecteur ? $paiement->collecteur->name : 'N/A' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $paiement->statut === 'valide' ? 'success' : ($paiement->statut === 'en_attente' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($paiement->statut) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(auth()->user()->role === 'collecteur')
                                                <a href="{{ route('collecteur.paiements.show', $paiement->id) }}" class="btn btn-sm btn-outline-info" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('paiements.show', $paiement->id) }}" class="btn btn-sm btn-outline-info" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                            @if($paiement->statut === 'valide')
                                                @if(auth()->user()->role === 'collecteur')
                                                    <a href="{{ route('collecteur.paiements.ticket', $paiement->id) }}" class="btn btn-sm btn-outline-primary" title="Reçu" target="_blank">
                                                        <i class="fas fa-receipt"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('paiements.ticket', $paiement->id) }}" class="btn btn-sm btn-outline-primary" title="Reçu" target="_blank">
                                                        <i class="fas fa-receipt"></i>
                                                    </a>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Résumé -->
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="row text-center">
                            <div class="col-4">
                                <h6 class="text-primary">{{ $unite->paiements->count() }}</h6>
                                <small>Total paiements</small>
                            </div>
                            <div class="col-4">
                                <h6 class="text-success">{{ number_format($unite->paiements->where('statut', 'valide')->sum('montant'), 0, ',', ' ') }} FCFA</h6>
                                <small>Montant validé</small>
                            </div>
                            <div class="col-4">
                                <h6 class="text-warning">{{ $unite->paiements->where('statut', 'en_attente')->count() }}</h6>
                                <small>En attente</small>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun paiement</h5>
                        <p class="text-muted">Cette unité commerciale n'a encore effectué aucun paiement.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet pour afficher la carte du terrain -->
@if($unite->superficie && !empty($unite->geometrie_polygon))
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Afficher le polygone du terrain sur la carte
document.addEventListener('DOMContentLoaded', function() {
    const terrainMapDiv = document.getElementById('terrain-map');
    if (!terrainMapDiv) return;
    
    try {
        const geometrie = @json($unite->geometrie_polygon);
        const centerLat = {{ $unite->latitude }};
        const centerLng = {{ $unite->longitude }};
        
        // Initialiser la carte
        const map = L.map('terrain-map').setView([centerLat, centerLng], 17);
        
        // Ajouter les tuiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Ajouter un marqueur au centre
        L.marker([centerLat, centerLng], {
            icon: L.divIcon({
                className: 'custom-marker',
                html: '<div style="background-color: #dc3545; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 3px 6px rgba(0,0,0,0.3);"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            })
        }).addTo(map).bindPopup('Centre du terrain');
        
        // Ajouter le polygone
        if (geometrie && geometrie.type === 'Polygon' && geometrie.coordinates) {
            const coordinates = geometrie.coordinates[0].map(coord => [coord[1], coord[0]]);
            const polygon = L.polygon(coordinates, {
                color: '#97009c',
                fillColor: '#97009c',
                fillOpacity: 0.4,
                weight: 3
            }).addTo(map);
            
            // Ajuster la vue pour afficher tout le polygone
            map.fitBounds(polygon.getBounds());
            
            // Ajouter un popup avec les informations
            polygon.bindPopup(`
                <strong>Terrain foncier</strong><br>
                Superficie: {{ number_format($unite->superficie, 2, ',', ' ') }} m²<br>
                Propriétaire: {{ $unite->nom_proprietaire }}
            `);
        }
    } catch (error) {
        console.error('Erreur lors de l\'affichage de la carte:', error);
        terrainMapDiv.innerHTML = '<div class="alert alert-warning m-3">Erreur lors du chargement de la carte du terrain</div>';
    }
});
</script>
@endif

<script>
function showAllPayments() {
    var modal = new bootstrap.Modal(document.getElementById('paymentsHistoryModal'));
    modal.show();
}
</script>
@endsection 