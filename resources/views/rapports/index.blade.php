@extends('layouts.app')

@section('title', 'Rapports - ' . (auth()->user()->commune?->display_acronyme ?? 'SICOSA'))

@section('content')
@php
    $rapportCommune = auth()->user()->commune;
    $rapportLogoUrl = $rapportCommune?->logo_url ?? asset('images/logos/snd.jpeg');
    $rapportCommuneNom = $rapportCommune?->nom ?? 'Sandiara';
    $rapportAcronyme = $rapportCommune?->display_acronyme ?? 'SICOSA';
@endphp
<!-- Bouton flottant carte thématique supprimé à la demande -->

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <img src="{{ $rapportLogoUrl }}" alt="Logo {{ $rapportCommuneNom }}" 
                                 class="me-3" style="height: 40px; width: 40px; border-radius: 50%; object-fit: contain;" onerror="this.src='{{ asset('images/logos/snd.jpeg') }}';">
                            <div>
                                <h4 class="mb-0">
                                    <i class="fas fa-chart-bar"></i> 
                                    Rapports et Statistiques
                                </h4>
                                <small class="opacity-75">Système d'Information Commerciale de {{ $rapportCommuneNom }}</small>
                                <div class="mt-1">
                                    <small class="opacity-75">
                                        <i class="fas fa-calendar-alt"></i> 
                                        Généré le {{ $generationDate->format('d/m/Y à H:i') }} | 
                                        Période: {{ $dateRange['start']->format('d/m/Y') }} - {{ $dateRange['end']->format('d/m/Y') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Rapport 1: Collecteurs -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-users fa-2x text-primary"></i>
                                    </div>
                                    <h5 class="card-title">1. Collecteurs</h5>
                                    <p class="card-text">Analyse des performances des collecteurs</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCollecteurs">
                                        <i class="fas fa-chart-line"></i> Générer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport 2: Unités -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-store fa-2x text-success"></i>
                                    </div>
                                    <h5 class="card-title">2. Unités</h5>
                                    <p class="card-text">Liste des unités commerciales</p>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalUnites">
                                        <i class="fas fa-list"></i> Voir
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport 3: Synthèse -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-file-alt fa-2x text-warning"></i>
                                    </div>
                                    <h5 class="card-title">3. Synthèse</h5>
                                    <p class="card-text">Synthèse exécutive générale</p>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalSynthese">
                                        <i class="fas fa-chart-pie"></i> Générer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport 4: Zones -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-map-marked-alt fa-2x text-info"></i>
                                    </div>
                                    <h5 class="card-title">4. Zones</h5>
                                    <p class="card-text">Analyse par zones fiscales</p>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalZones">
                                        <i class="fas fa-chart-area"></i> Générer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport 5: Taxes -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-money-bill-wave fa-2x text-danger"></i>
                                    </div>
                                    <h5 class="card-title">5. Taxes</h5>
                                    <p class="card-text">Analyse des taxes et recettes</p>
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalTaxes">
                                        <i class="fas fa-chart-bar"></i> Générer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport 6: Mensuel -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-calendar-alt fa-2x text-secondary"></i>
                                    </div>
                                    <h5 class="card-title">6. Mensuel</h5>
                                    <p class="card-text">Rapport mensuel détaillé</p>
                                    <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMensuel">
                                        <i class="fas fa-calendar-check"></i> Générer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport 7: Carte Thématique -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-map-marked-alt fa-2x text-success"></i>
                                    </div>
                                    <h5 class="card-title">7. Carte</h5>
                                    <p class="card-text">Carte thématique géospatiale</p>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCarte" onclick="forceOpenModal('modalCarte')">
                                        <i class="fas fa-map"></i> Générer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport 8: Comparatif -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-balance-scale fa-2x text-primary"></i>
                                    </div>
                                    <h5 class="card-title">8. Comparatif</h5>
                                    <p class="card-text">Comparaison des données</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalComparatif">
                                        <i class="fas fa-chart-line"></i> Générer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport 9: Nature -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-industry fa-2x text-warning"></i>
                                    </div>
                                    <h5 class="card-title">9. Nature</h5>
                                    <p class="card-text">Par nature d'activité</p>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalNatureCommerce">
                                        <i class="fas fa-chart-pie"></i> Générer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport 10: Type Commerce -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-shopping-cart fa-2x text-info"></i>
                                    </div>
                                    <h5 class="card-title">10. Type</h5>
                                    <p class="card-text">Par type de commerce</p>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalTypeCommerce">
                                        <i class="fas fa-chart-bar"></i> Générer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Rapport 11: Géomarketing -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-chart-line fa-2x text-primary"></i>
                                    </div>
                                    <h5 class="card-title">11. Géomarketing</h5>
                                    <p class="card-text">Analyse géospatiale et opportunités</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGeomarketing">
                                        <i class="fas fa-map-marked-alt"></i> Générer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques générales -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-center mb-3">
                                        <i class="fas fa-chart-line"></i> Statistiques Générales
                                    </h6>
                                    <div class="row text-center">
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="stat-item">
                                                <h5 class="text-primary">{{ number_format($stats['total_unites'] ?? 0) }}</h5>
                                                <small class="text-muted">Total Unités</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="stat-item">
                                                <h5 class="text-success">{{ number_format($stats['payees'] ?? 0) }}</h5>
                                                <small class="text-muted">Payées</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="stat-item">
                                                <h5 class="text-danger">{{ number_format($stats['impayees'] ?? 0) }}</h5>
                                                <small class="text-muted">Impayées</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="stat-item">
                                                <h5 class="text-info">{{ number_format($stats['total_montant'] ?? 0) }} FCFA</h5>
                                                <small class="text-muted">Montant Total</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inclure tous les modals -->
@include('rapports.modals.collecteurs')
@include('rapports.modals.unites')
@include('rapports.modals.synthese')
@include('rapports.modals.zones')
@include('rapports.modals.taxes')
@include('rapports.modals.mensuel')
@include('rapports.modals.carte')
@include('rapports.modals.comparatif')
@include('rapports.modals.nature-commerce')
@include('rapports.modals.type-commerce')
@include('rapports.modals.geomarketing')

@endsection 