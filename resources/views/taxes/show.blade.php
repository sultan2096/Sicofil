@extends('layouts.app')

@section('title', 'Détails de la Taxe')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(!$taxe || !$taxe->id)
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h4 class="text-warning">Taxe introuvable</h4>
                        <p class="text-muted">La taxe demandée n'existe pas ou a été supprimée.</p>
                        <a href="{{ route('taxes.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            Retour à la liste des taxes
                        </a>
                    </div>
                </div>
            @else
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave"></i>
                        {{ $taxe->nom }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('taxes.edit', $taxe) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                            Modifier
                        </a>
                        <a href="{{ route('taxes.index') }}" class="btn btn-secondary">
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
                                        Informations de la Taxe
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Nom :</strong>
                                                <p class="text-muted">{{ $taxe->nom }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Montant :</strong>
                                                <p class="h4 text-primary">{{ number_format($taxe->montant, 0, ',', ' ') }} FCFA</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Périodicité :</strong>
                                                <p>
                                                    <span class="badge bg-{{ 
                                                        $taxe->periodicite === 'mensuelle' ? 'success' : 
                                                        ($taxe->periodicite === 'trimestrielle' ? 'info' : 'warning') 
                                                    }} fs-6">
                                                        {{ ucfirst($taxe->periodicite) }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        @if($taxe->nature)
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong>Nature :</strong>
                                                    <p class="text-muted">{{ $taxe->nature }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($taxe->description)
                                        <div class="mb-3">
                                            <strong>Description :</strong>
                                            <p class="text-muted">{{ $taxe->description }}</p>
                                        </div>
                                    @endif

                                    <!-- Informations supplémentaires -->
                                    @if($taxe->base_calcul || $taxe->taux || $taxe->date_debut || $taxe->date_fin)
                                        <hr>
                                        <h6><i class="fas fa-cog"></i> Informations complémentaires</h6>
                                        <div class="row">
                                            @if($taxe->base_calcul)
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <strong>Base de calcul :</strong>
                                                        <p class="text-muted">{{ $taxe->base_calcul }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($taxe->taux)
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <strong>Taux :</strong>
                                                        <p class="text-muted">{{ $taxe->taux }}%</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        @if($taxe->date_debut || $taxe->date_fin)
                                            <div class="row">
                                                @if($taxe->date_debut)
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <strong>Date de début :</strong>
                                                            <p class="text-muted">{{ \Carbon\Carbon::parse($taxe->date_debut)->format('d/m/Y') }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($taxe->date_fin)
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <strong>Date de fin :</strong>
                                                            <p class="text-muted">{{ \Carbon\Carbon::parse($taxe->date_fin)->format('d/m/Y') }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endif

                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Créée le :</strong>
                                                <p class="text-muted">{{ $taxe->created_at->format('d/m/Y à H:i') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Dernière modification :</strong>
                                                <p class="text-muted">{{ $taxe->updated_at->format('d/m/Y à H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-bar"></i>
                                        Statistiques
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h3 class="text-primary">{{ $taxe->unitesCommerciales->count() }}</h3>
                                        <p class="text-muted">Unités commerciales assignées</p>
                                    </div>
                                    
                                    @if($taxe->unitesCommerciales->count() > 0)
                                        <div class="mb-3">
                                            <strong>Montant total potentiel :</strong>
                                            <p class="h5 text-success">
                                                {{ number_format($taxe->montant * $taxe->unitesCommerciales->count(), 0, ',', ' ') }} FCFA
                                            </p>
                                        </div>

                                        <!-- Répartition par zone -->
                                        <div class="mb-3">
                                            <strong>Répartition par zone :</strong>
                                            @php
                                                $repartitionZones = $taxe->unitesCommerciales->groupBy('zone_fiscale.nom');
                                            @endphp
                                            @foreach($repartitionZones as $zone => $unites)
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">{{ $zone ?? 'Non assignée' }} :</span>
                                                    <span class="badge bg-secondary">{{ $unites->count() }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Actions rapides -->
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('unites.index', ['taxe_id' => $taxe->id]) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-store"></i>
                                            Voir les unités
                                        </a>
                                        @if($taxe->unitesCommerciales->count() > 0)
                                            <a href="{{ route('paiements.index', ['taxe_id' => $taxe->id]) }}" 
                                               class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-money-check"></i>
                                                Voir les paiements
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-cogs"></i>
                                        Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('taxes.edit', $taxe) }}" 
                                           class="btn btn-warning">
                                            <i class="fas fa-edit"></i>
                                            Modifier la taxe
                                        </a>
                                        
                                        @if((auth()->user()->role === 'maire' || auth()->user()->role === 'admin') && $taxe->unitesCommerciales->count() === 0)
                                            <form method="POST" 
                                                  action="{{ route('taxes.destroy', $taxe) }}" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette taxe ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                    Supprimer
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des unités commerciales assignées -->
                    @if($taxe->unitesCommerciales->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-store"></i>
                                            Unités Commerciales Assignées ({{ $taxe->unitesCommerciales->count() }})
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Nom du commerce</th>
                                                        <th>Propriétaire</th>
                                                        <th>Zone fiscale</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($taxe->unitesCommerciales->take(10) as $unite)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $unite->nom_commerce }}</strong><br>
                                                                <small class="text-muted">{{ $unite->nature }}</small>
                                                            </td>
                                                            <td>{{ $unite->nom_proprietaire }}</td>
                                                            <td>
                                                                @if($unite->zoneFiscale)
                                                                    <span class="badge bg-info">{{ $unite->zoneFiscale->nom }}</span>
                                                                @else
                                                                    <span class="badge bg-secondary">Non assignée</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $unite->statut === 'actif' ? 'success' : 'warning' }}">
                                                                    {{ ucfirst($unite->statut) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('unites.show', $unite) }}" 
                                                                   class="btn btn-sm btn-outline-info">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        @if($taxe->unitesCommerciales->count() > 10)
                                            <div class="text-center mt-3">
                                                <a href="{{ route('unites.index', ['taxe_id' => $taxe->id]) }}" 
                                                   class="btn btn-primary">
                                                    Voir toutes les unités ({{ $taxe->unitesCommerciales->count() }})
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 