@extends('layouts.app')

@section('title', 'Tableau de bord - Collecteur')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/collecteur.css') }}">
<style>
/* Responsive Design pour Collecteur - Page entière visible avec scroll */
.collecteur-mode {
    max-width: 100%;
    margin: 0 auto;
    min-height: 100vh;
    overflow: visible;
}

/* En-tête responsive */
@media (max-width: 768px) {
    .collecteur-mode .d-flex {
        flex-direction: column !important;
        text-align: center;
    }
    
    .collecteur-mode h1 {
        font-size: 1.25rem !important;
    }
    
    .collecteur-mode .btn-toolbar {
        margin-top: 0.5rem;
    }
}

/* Grille de statistiques responsive */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 1rem;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    min-height: 100px;
}

.stat-card:hover {
    transform: translateY(-3px);
}

.stat-icon {
    font-size: 2rem;
    color: #667eea;
    margin-bottom: 0.5rem;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: #718096;
    font-size: 0.8rem;
}

/* Bouton de recensement responsive */
.btn-recenser {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    border-radius: 20px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 60px;
}

.btn-recenser:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    color: white;
}

@media (max-width: 768px) {
    .btn-recenser {
        min-height: 50px;
        font-size: 0.9rem;
    }
    
    .btn-recenser i {
        font-size: 1.25rem !important;
    }
}

/* Actions rapides responsive */
@media (max-width: 768px) {
    .card-body {
        padding: 0.75rem !important;
    }
    
    .btn {
        padding: 0.5rem !important;
        font-size: 0.8rem;
    }
    
    .btn i {
        font-size: 1.25rem !important;
        margin-bottom: 0.25rem !important;
    }
}

/* Tableau responsive */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.75rem;
    }
    
    .table th,
    .table td {
        padding: 0.25rem 0.125rem;
    }
    
    .badge {
        font-size: 0.7rem;
    }
}

/* Cartes responsive */
@media (max-width: 768px) {
    .card {
        margin-bottom: 0.5rem;
    }
    
    .card-header {
        padding: 0.5rem;
        font-size: 0.8rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
}

/* Progress bar responsive */
@media (max-width: 768px) {
    .progress {
        height: 10px !important;
    }
}

/* Boutons d'action responsive */
@media (max-width: 768px) {
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
    }
    
    .btn-group .btn i {
        margin-right: 0.125rem;
    }
}

/* Espacement responsive */
@media (max-width: 768px) {
    .mb-4 {
        margin-bottom: 0.5rem !important;
    }
    
    .py-5 {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }
    
    .px-5 {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
}

/* Optimisation pour tablettes */
@media (min-width: 769px) and (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .btn-recenser {
        min-height: 55px;
    }
    
    .card-body {
        padding: 1rem !important;
    }
}
</style>
@endpush

@section('content')
<div class="collecteur-mode">
    <!-- En-tête avec informations de connexion -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-1 mb-2 border-bottom">
        <div>
            <h1 class="h3 text-primary">
                <i class="fas fa-user-check"></i> 
                Tableau de bord Collecteur
            </h1>
            <p class="text-muted mb-0 small">
                <i class="fas fa-map-marker-alt"></i> 
                Zone : <strong>{{ auth()->user()->zone ? auth()->user()->zone->nom : 'Non assignée' }}</strong>
                <span class="mx-2 d-none d-md-inline">|</span>
                <br class="d-md-none">
                <i class="fas fa-clock"></i> 
                {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="btn-toolbar mb-1 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-success btn-sm" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> 
                    <span class="d-none d-md-inline">Actualiser</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-store"></i>
            </div>
            <div class="stat-number">{{ number_format($unitesZone->count(), 0, ',', ' ') }}</div>
            <div class="stat-label">Unités dans ma zone</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-number">{{ number_format($paiementsJour, 0, ',', ' ') }}</div>
            <div class="stat-label">FCFA collectés aujourd'hui</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-number">{{ number_format($paiementsMois, 0, ',', ' ') }}</div>
            <div class="stat-label">FCFA collectés ce mois</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-number">{{ $unitesNonPayees->count() }}</div>
            <div class="stat-label">Unités non payées</div>
        </div>
    </div>

    <!-- Actions principales : Recensement + Mise à jour -->
    <div class="row mb-2">
        <div class="col-12 mb-2">
            <div class="card border-primary shadow-lg" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);">
                <div class="card-header bg-primary text-white py-2">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Actions Principales
                    </h6>
                </div>
                <div class="card-body text-center py-3">
                    @php
                        $communeCollecteur = auth()->user()->commune;
                        $peutRecenser = !$communeCollecteur || $communeCollecteur->collecteurs_peuvent_recenser;
                        $peutMettreAJour = !$communeCollecteur || $communeCollecteur->collecteurs_peuvent_mettre_a_jour;
                    @endphp
                    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3">
                        <!-- Bouton Recenser -->
                        <div class="text-center">
                            @if($peutRecenser)
                            <a href="{{ route('unites.create') }}" class="btn btn-recenser btn-lg px-4 py-3 shadow">
                                <i class="fas fa-plus-circle me-2" style="font-size: 2rem;"></i>
                                <div class="d-inline-block">
                                    <div style="font-size: 1.4em; font-weight: 900;">📋 RECENSER</div>
                                    <div style="font-size: 0.8em; font-weight: 500; margin-top: 4px;">Nouvelle unité commerciale</div>
                                </div>
                            </a>
                            <p class="text-muted mt-2 mb-0 small">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Enregistrer un nouveau commerce</strong>
                            </p>
                            @else
                            <button class="btn btn-secondary btn-lg px-4 py-3 shadow" disabled title="Recensement désactivé par le gestionnaire">
                                <i class="fas fa-ban me-2" style="font-size: 2rem;"></i>
                                <div class="d-inline-block">
                                    <div style="font-size: 1.4em; font-weight: 900;">🚫 RECENSER</div>
                                    <div style="font-size: 0.8em; font-weight: 500; margin-top: 4px;">Désactivé par le gestionnaire</div>
                                </div>
                            </button>
                            <p class="text-muted mt-2 mb-0 small">
                                <i class="fas fa-lock me-2"></i>
                                <strong>Le recensement est temporairement désactivé.</strong>
                            </p>
                            @endif
                        </div>

                        <!-- Bouton Mise à jour -->
                        <div class="text-center">
                            @if($peutMettreAJour)
                            <a href="{{ route('collecteur.unites.update-form') }}" class="btn btn-warning btn-lg px-4 py-3 shadow" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; color: white;">
                                <i class="fas fa-edit me-2" style="font-size: 2rem;"></i>
                                <div class="d-inline-block">
                                    <div style="font-size: 1.4em; font-weight: 900;">✏️ METTRE À JOUR</div>
                                    <div style="font-size: 0.8em; font-weight: 500; margin-top: 4px;">Modifier une unité existante</div>
                                </div>
                            </a>
                            <p class="text-muted mt-2 mb-0 small">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Modifier les informations d'un commerce existant</strong>
                            </p>
                            @else
                            <button class="btn btn-secondary btn-lg px-4 py-3 shadow" disabled title="Mise à jour désactivée par le gestionnaire">
                                <i class="fas fa-ban me-2" style="font-size: 2rem;"></i>
                                <div class="d-inline-block">
                                    <div style="font-size: 1.4em; font-weight: 900;">🚫 METTRE À JOUR</div>
                                    <div style="font-size: 0.8em; font-weight: 500; margin-top: 4px;">Désactivé par le gestionnaire</div>
                                </div>
                            </button>
                            <p class="text-muted mt-2 mb-0 small">
                                <i class="fas fa-lock me-2"></i>
                                <strong>La mise à jour est temporairement désactivée.</strong>
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions secondaires -->
    <div class="row mb-2">
        <div class="col-lg-8">
            <div class="card shadow-lg h-100" style="border-radius: 20px;">
                <div class="card-header text-white bg-info-gradient py-2">
                    <i class="fas fa-bolt text-white"></i> 
                    <strong>Actions rapides</strong>
                </div>
                <div class="card-body" style="padding: 1rem;">
                    <div class="row">
                        <div class="col-md-4 col-6 mb-2">
                            <a href="{{ route('collecteur.paiements.create') }}" class="btn btn-success w-100 py-3 shadow-sm" style="border-radius: 15px; transition: all 0.3s ease;">
                                <i class="fas fa-cash-register fa-lg mb-2"></i>
                                <div><strong style="font-size: 1rem;">Paiement</strong></div>
                                <small style="opacity: 0.9;">Enregistrer</small>
                            </a>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <a href="{{ route('unites.index') }}" class="btn btn-info w-100 py-3 shadow-sm" style="border-radius: 15px; transition: all 0.3s ease;">
                                <i class="fas fa-list fa-lg mb-2"></i>
                                <div><strong style="font-size: 1rem;">Mes unités</strong></div>
                                <small style="opacity: 0.9;">Consulter</small>
                            </a>
                        </div>
                        <div class="col-md-4 col-12 mb-2">
                            <a href="{{ route('collecteur.paiements.index') }}" class="btn btn-secondary w-100 py-3 shadow-sm" style="border-radius: 15px; transition: all 0.3s ease;">
                                <i class="fas fa-history fa-lg mb-2"></i>
                                <div><strong style="font-size: 1rem;">Historique</strong></div>
                                <small style="opacity: 0.9;">Voir</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-lg h-100" style="border-radius: 20px;">
                <div class="card-header text-white bg-success-gradient py-2">
                    <i class="fas fa-chart-pie"></i> Résumé du mois
                </div>
                <div class="card-body" style="padding: 1rem;">
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="font-weight: 500; font-size: 0.9rem;">Unités payées :</span>
                            <strong class="badge bg-success">{{ $unitesZone->count() - $unitesNonPayees->count() }}</strong>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="font-weight: 500; font-size: 0.9rem;">Unités en retard :</span>
                            <strong class="badge bg-warning">{{ $unitesNonPayees->count() }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="font-weight: 500; font-size: 0.9rem;">Taux de collecte :</span>
                            <strong class="badge bg-info">
                                {{ $unitesZone->count() > 0 ? round((($unitesZone->count() - $unitesNonPayees->count()) / $unitesZone->count()) * 100, 1) : 0 }}%
                            </strong>
                        </div>
                    </div>
                    <div class="progress" style="height: 12px; border-radius: 10px; background: rgba(0,0,0,0.1);">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="border-radius: 10px; width: {{ $unitesZone->count() > 0 ? (($unitesZone->count() - $unitesNonPayees->count()) / $unitesZone->count()) * 100 : 0 }}%;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unités non payées -->
    @if($unitesNonPayees->count() > 0)
    <div class="card mb-2">
        <div class="card-header py-2">
            <i class="fas fa-exclamation-triangle text-warning"></i> 
            Unités commerciales non payées ce mois ({{ $unitesNonPayees->count() }})
        </div>
        <div class="card-body p-2" style="overflow-y: auto; height: calc(100% - 50px);">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Propriétaire</th>
                            <th>Commerce</th>
                            <th>Adresse</th>
                            <th>Téléphone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unitesNonPayees->take(5) as $unite)
                        <tr>
                            <td>
                                <strong>{{ $unite->nom_proprietaire }}</strong>
                                @if($unite->prenom_proprietaire)
                                    <br><small class="text-muted">{{ $unite->prenom_proprietaire }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $unite->nature }}</span>
                                @if($unite->nom_commerce)
                                    <br><small>{{ $unite->nom_commerce }}</small>
                                @endif
                            </td>
                            <td>
                                <small>{{ $unite->adresse }}</small>
                                @if($unite->quartier)
                                    <br><small class="text-muted">{{ $unite->quartier }}</small>
                                @endif
                            </td>
                            <td>
                                @if($unite->telephone)
                                    <a href="tel:{{ $unite->telephone }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-phone"></i> {{ $unite->telephone }}
                                    </a>
                                @else
                                    <span class="text-muted">Non renseigné</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('collecteur.unites.show', $unite->id) }}" class="btn btn-sm btn-info" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('collecteur.paiements.create', ['unite_id' => $unite->id]) }}" class="btn btn-sm btn-success" title="Enregistrer un paiement">
                                        <i class="fas fa-cash-register"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($unitesNonPayees->count() > 5)
                <div class="text-center mt-2">
                    <a href="{{ route('unites.index') }}?statut=non_paye" class="btn btn-warning btn-sm">
                        Voir toutes les unités non payées ({{ $unitesNonPayees->count() }})
                    </a>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Boutons d'actions flottants pour mobile -->
    <div class="floating-actions d-md-none">
        <a href="{{ route('unites.create') }}" class="btn btn-primary btn-lg shadow" title="Recenser une unité">
            <i class="fas fa-plus fa-lg"></i>
            <div class="small">Recenser</div>
        </a>
        <a href="{{ route('collecteur.paiements.create') }}" class="btn btn-success btn-lg shadow" title="Enregistrer un paiement">
            <i class="fas fa-cash-register fa-lg"></i>
            <div class="small">Paiement</div>
        </a>
    </div>
</div>

<style>
/* Styles spécifiques pour l'interface collecteur */
.collecteur-mode .card {
    border-radius: 15px;
}

.collecteur-mode .btn {
    border-radius: 10px;
    transition: all 0.3s ease;
}

.collecteur-mode .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.collecteur-mode .btn-lg {
    font-size: 1rem;
    font-weight: 600;
}

.floating-actions {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.floating-actions .btn {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}

.floating-actions .btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(0,0,0,0.4);
}

.floating-actions .btn .small {
    font-size: 0.7rem;
    margin-top: 2px;
}

/* Amélioration des stats cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
}

.stat-card .stat-icon {
    font-size: 2rem;
    margin-bottom: 10px;
    opacity: 0.8;
}

.stat-card .stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-card .stat-label {
    font-size: 0.8rem;
    opacity: 0.9;
}

/* Responsive design */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .stat-card {
        padding: 15px 10px;
    }
    
    .stat-card .stat-number {
        font-size: 1.25rem;
    }
    
    .collecteur-mode .btn-lg {
        font-size: 0.9rem;
        padding: 10px 15px;
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .floating-actions {
        bottom: 15px;
        right: 15px;
    }
    
    .floating-actions .btn {
        width: 60px;
        height: 60px;
    }
}

/* Optimisation pour éviter le défilement */
body {
    overflow-y: auto !important;
}

.collecteur-mode {
    min-height: 100vh;
    overflow: visible;
}

/* Optimisation pour les petits écrans */
@media (max-height: 600px) {
    .collecteur-mode {
        padding: 0.25rem;
    }
    
    .mb-2 {
        margin-bottom: 0.25rem !important;
    }
    
    .py-3 {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }
    
    .card-body {
        padding: 0.5rem !important;
    }
    
    .stat-card {
        padding: 10px 5px;
    }
    
    .stat-card .stat-number {
        font-size: 1rem;
    }
    
    .stat-card .stat-icon {
        font-size: 1.5rem;
    }
}
</style>

<script>
// Auto-refresh des statistiques toutes les 5 minutes
setInterval(function() {
    location.reload();
}, 300000);

// Gestion des boutons tactiles
document.addEventListener('DOMContentLoaded', function() {
    // Améliorer l'expérience tactile
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        button.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>

<style>
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn:hover {
    animation: none !important;
    transform: translateY(-3px) !important;
    box-shadow: 0 6px 25px rgba(0, 123, 255, 0.4) !important;
}

.floating-actions {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.floating-actions .btn {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
    border: none;
}

.floating-actions .btn .small {
    font-size: 0.7rem;
    margin-top: 2px;
    line-height: 1;
}

@media (max-width: 768px) {
    .floating-actions {
        display: block;
    }
}

@media (min-width: 769px) {
    .floating-actions {
        display: none;
    }
}

/* Styles pour le tracking */
.tracking-status {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    font-size: 12px;
    z-index: 1000;
}

.tracking-status.online {
    background: rgba(40, 167, 69, 0.9);
}

.tracking-status.error {
    background: rgba(220, 53, 69, 0.9);
}
</style>

<script>
// Système de tracking automatique pour les collecteurs
let trackingInterval = null;
let isTrackingActive = false;

document.addEventListener('DOMContentLoaded', function() {
    startTracking();
});

function startTracking() {
    if (!navigator.geolocation) {
        console.log('Géolocalisation non supportée');
        return;
    }

    // Vérifier les permissions
    navigator.geolocation.getCurrentPosition(
        function(position) {
            console.log('✅ Géolocalisation autorisée');
            sendPosition(position);
            startPeriodicTracking();
        },
        function(error) {
            console.log('❌ Erreur géolocalisation:', error.message);
            showTrackingStatus('Géolocalisation refusée', 'error');
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 30000
        }
    );
}

function startPeriodicTracking() {
    if (trackingInterval) {
        clearInterval(trackingInterval);
    }

    // Envoyer la position toutes les 30 secondes
    trackingInterval = setInterval(() => {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                sendPosition(position);
            },
            function(error) {
                console.log('Erreur tracking:', error.message);
                showTrackingStatus('Erreur de position', 'error');
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 10000
            }
        );
    }, 30000);

    isTrackingActive = true;
    showTrackingStatus('Suivi actif', 'online');
}

function sendPosition(position) {
    const data = {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
        altitude: position.coords.altitude,
        accuracy: position.coords.accuracy,
        activity: 'collecting', // Par défaut
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
            console.log('✅ Position envoyée');
            showTrackingStatus('Position mise à jour', 'online');
        }
    })
    .catch(error => {
        console.error('❌ Erreur envoi position:', error);
        showTrackingStatus('Erreur de connexion', 'error');
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

function showTrackingStatus(message, type) {
    // Supprimer l'ancien statut
    const oldStatus = document.querySelector('.tracking-status');
    if (oldStatus) {
        oldStatus.remove();
    }

    // Créer le nouveau statut
    const status = document.createElement('div');
    status.className = `tracking-status ${type}`;
    status.innerHTML = `
        <i class="fas fa-map-marker-alt"></i> ${message}
    `;
    
    document.body.appendChild(status);

    // Supprimer après 3 secondes
    setTimeout(() => {
        if (status.parentNode) {
            status.remove();
        }
    }, 3000);
}

// Arrêter le tracking quand la page se ferme
window.addEventListener('beforeunload', function() {
    if (trackingInterval) {
        clearInterval(trackingInterval);
    }
});
</script>

@endsection 