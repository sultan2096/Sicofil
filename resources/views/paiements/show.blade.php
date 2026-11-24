@extends('layouts.app')

@section('title', 'Détails du Paiement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-check-alt"></i>
                        Paiement {{ $paiement->reference }}
                        <span class="badge bg-{{ 
                            $paiement->statut === 'valide' ? 'success' : 
                            ($paiement->statut === 'en_attente' ? 'warning' : 'danger') 
                        }} ms-2">
                            {{ ucfirst($paiement->statut) }}
                        </span>
                    </h3>
                    <div class="card-tools">
                        @if(auth()->user()->role === 'maire' && $paiement->statut === 'en_attente')
                            <form method="POST" 
                                  action="{{ route('paiements.cancel', $paiement) }}" 
                                  class="d-inline me-2"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce paiement ?')">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-ban"></i>
                                    Annuler le paiement
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('paiements.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Informations principales -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle"></i>
                                        Informations du Paiement
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Référence :</strong>
                                                <p><code class="fs-6">{{ $paiement->reference }}</code></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Montant Total :</strong>
                                                <p class="h4 text-primary">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</p>
                                                
                                                @if($paiement->montant_avance > 0 || $paiement->montant_restant > 0)
                                                    <div class="mt-2">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <small class="text-muted">Déjà payé :</small>
                                                                <div class="text-success fw-bold">{{ number_format($paiement->montant_avance, 0, ',', ' ') }} FCFA</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted">Restant :</small>
                                                                <div class="text-warning fw-bold">{{ number_format($paiement->montant_restant, 0, ',', ' ') }} FCFA</div>
                                                            </div>
                                                        </div>
                                                        @if($paiement->montant_restant > 0)
                                                            <div class="mt-2">
                                                                <div class="progress" style="height: 8px;">
                                                                    @php
                                                                        $pourcentage = ($paiement->montant_avance / $paiement->montant) * 100;
                                                                    @endphp
                                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                                         style="width: {{ $pourcentage }}%" 
                                                                         aria-valuenow="{{ $pourcentage }}" 
                                                                         aria-valuemin="0" 
                                                                         aria-valuemax="100">
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted">{{ number_format($pourcentage, 1) }}% payé</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Date de paiement :</strong>
                                                <p class="text-muted">{{ $paiement->date_paiement->format('d/m/Y à H:i') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Mode de paiement :</strong>
                                                <p>
                                                    @switch($paiement->mode_paiement)
                                                        @case('especes')
                                                            <span class="badge bg-success fs-6">
                                                                <i class="fas fa-money-bill"></i> Espèces
                                                            </span>
                                                            @break
                                                        @case('cheque')
                                                            <span class="badge bg-info fs-6">
                                                                <i class="fas fa-money-check"></i> Chèque
                                                            </span>
                                                            @break
                                                        @case('virement')
                                                            <span class="badge bg-primary fs-6">
                                                                <i class="fas fa-university"></i> Virement
                                                            </span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary fs-6">{{ ucfirst($paiement->mode_paiement) }}</span>
                                                    @endswitch
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($paiement->numero_cheque || $paiement->numero_virement)
                                        <div class="row">
                                            @if($paiement->numero_cheque)
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <strong>Numéro de chèque :</strong>
                                                        <p class="text-muted">{{ $paiement->numero_cheque }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($paiement->numero_virement)
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <strong>Numéro de virement :</strong>
                                                        <p class="text-muted">{{ $paiement->numero_virement }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if($paiement->commentaire)
                                        <div class="mb-3">
                                            <strong>Commentaire :</strong>
                                            <p class="text-muted">{{ $paiement->commentaire }}</p>
                                        </div>
                                    @endif

                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Enregistré le :</strong>
                                                <p class="text-muted">{{ $paiement->created_at->format('d/m/Y à H:i') }}</p>
                                            </div>
                                        </div>
                                        @if($paiement->updated_at != $paiement->created_at)
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong>Dernière modification :</strong>
                                                    <p class="text-muted">{{ $paiement->updated_at->format('d/m/Y à H:i') }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Informations de l'unité commerciale -->
                            @if($paiement->unite)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-store"></i>
                                            Unité Commerciale
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong>Nature :</strong>
                                                    <p class="text-muted">{{ $paiement->unite->nature }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong>Propriétaire :</strong>
                                                    <p class="text-muted">{{ $paiement->unite->nom_proprietaire }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong>Type de commerce :</strong>
                                                    <p class="text-muted">{{ $paiement->unite->type_commerce }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong>Zone fiscale :</strong>
                                                    <p>
                                                        @if($paiement->unite->zone)
                                                            <span class="badge bg-info">{{ $paiement->unite->zone->nom }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">Non assignée</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            @if(auth()->user()->role === 'collecteur')
                                                <a href="{{ route('collecteur.unites.show', $paiement->unite) }}" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                    Voir l'unité commerciale
                                                </a>
                                            @else
                                                <a href="{{ route('unites.show', $paiement->unite) }}" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                    Voir l'unité commerciale
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="card mt-3">
                                    <div class="card-body text-center">
                                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                        <h5 class="text-warning">Unité commerciale supprimée</h5>
                                        <p class="text-muted">L'unité commerciale associée à ce paiement a été supprimée.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Informations latérales -->
                        <div class="col-md-4">
                            <!-- Statut et actions -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-flag"></i>
                                        Statut du Paiement
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    @switch($paiement->statut)
                                        @case('en_attente')
                                            <div class="mb-3">
                                                <i class="fas fa-clock fa-3x text-warning"></i>
                                                <h5 class="text-warning mt-2">En attente de validation</h5>
                                                <p class="text-muted">Le paiement est en cours de vérification</p>
                                            </div>
                                            @break
                                        @case('valide')
                                            <div class="mb-3">
                                                <i class="fas fa-check-circle fa-3x text-success"></i>
                                                <h5 class="text-success mt-2">Validé</h5>
                                                <p class="text-muted">Le paiement a été validé et comptabilisé</p>
                                            </div>
                                            @break
                                        @case('annule')
                                            <div class="mb-3">
                                                <i class="fas fa-times-circle fa-3x text-danger"></i>
                                                <h5 class="text-danger mt-2">Annulé</h5>
                                                <p class="text-muted">Le paiement a été annulé</p>
                                            </div>
                                            @break
                                    @endswitch

                                    @if(auth()->user()->role === 'maire' && $paiement->statut === 'en_attente')
                                        <div class="d-grid gap-2">
                                            <form method="POST" 
                                                  action="{{ route('paiements.cancel', $paiement) }}" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce paiement ?')">
                                                @csrf
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-ban"></i>
                                                    Annuler le paiement
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                    
                                    <!-- Bouton pour finaliser le paiement partiel -->
@if($paiement->statut === 'en_attente' && $paiement->montant_restant > 0 && in_array(auth()->user()->role, ['collecteur', 'gestionnaire']))
                                        <div class="d-grid mt-3">
                                            @if(auth()->user()->role === 'collecteur')
                                                <a href="{{ route('collecteur.paiements.add-payment', $paiement->id) }}" class="btn btn-warning btn-lg">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    Finaliser le Paiement
                                                </a>
                                                <small class="text-muted text-center mt-2">
                                                    Montant restant : <strong>{{ number_format($paiement->montant_restant, 0, ',', ' ') }} FCFA</strong>
                                                </small>
        @elseif(in_array(auth()->user()->role, ['gestionnaire']))
                                                <a href="{{ route('paiements.add-payment', $paiement->id) }}" class="btn btn-warning btn-lg">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    Finaliser le Paiement
                                                </a>
                                                <small class="text-muted text-center mt-2">
                                                    Montant restant : <strong>{{ number_format($paiement->montant_restant, 0, ',', ' ') }} FCFA</strong>
                                                </small>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <!-- Information si le paiement ne peut pas être finalisé -->
                                    @if($paiement->statut === 'en_attente' && $paiement->montant_restant > 0 && !in_array(auth()->user()->role, ['collecteur', 'gestionnaire', 'admin']))
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Paiement partiel en attente</strong><br>
                                            Montant restant : {{ number_format($paiement->montant_restant, 0, ',', ' ') }} FCFA<br>
                                            <small>Seuls les collecteurs, gestionnaires et admins peuvent finaliser ce paiement.</small>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Informations du collecteur -->
                            @if($paiement->collecteur)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-user-tie"></i>
                                            Collecteur
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="fas fa-user-circle fa-3x text-primary"></i>
                                            <h6 class="mt-2">{{ $paiement->collecteur->name }}</h6>
                                            <p class="text-muted">{{ $paiement->collecteur->identifiant }}</p>
                                        </div>
                                        
                                        @if($paiement->collecteur->zone)
                                            <div class="mb-3">
                                                <strong>Zone assignée :</strong>
                                                <p><span class="badge bg-info">{{ $paiement->collecteur->zone->nom }}</span></p>
                                            </div>
                                        @endif

                                        <div class="d-grid">
                                            @if(auth()->user()->role === 'collecteur')
                                                <button class="btn btn-outline-secondary" disabled>
                                                    <i class="fas fa-eye"></i>
                                                    Profil collecteur
                                                </button>
                                                <small class="text-muted mt-2 d-block">
                                                    Les collecteurs ne peuvent pas voir les profils d'autres utilisateurs
                                                </small>
                                            @else
                                                <a href="{{ route('users.show', $paiement->collecteur) }}" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                    Voir le profil
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Historique récent -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history"></i>
                                        Historique
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">Paiement enregistré</h6>
                                                <p class="timeline-description">{{ $paiement->created_at->format('d/m/Y à H:i') }}</p>
                                            </div>
                                        </div>
                                        
                                        @if($paiement->statut === 'valide')
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-primary"></div>
                                                <div class="timeline-content">
                                                    <h6 class="timeline-title">Paiement validé</h6>
                                                    <p class="timeline-description">{{ $paiement->updated_at->format('d/m/Y à H:i') }}</p>
                                                </div>
                                            </div>
                                        @elseif($paiement->statut === 'annule')
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-danger"></div>
                                                <div class="timeline-content">
                                                    <h6 class="timeline-title">Paiement annulé</h6>
                                                    <p class="timeline-description">{{ $paiement->updated_at->format('d/m/Y à H:i') }}</p>
                                                </div>
                                            </div>
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
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -25px;
    top: 0;
    bottom: -20px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-description {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 0;
}
</style>
@endsection