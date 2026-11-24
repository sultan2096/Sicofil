@extends('layouts.app')

@section('title', 'Détails de l\'Activité')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Détails de l'Activité #{{ $log->id }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Retour au journal
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
                                        <i class="fas fa-file-alt"></i>
                                        Informations de l'Activité
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Date et heure :</strong>
                                                <p class="text-muted">{{ $log->created_at->format('d/m/Y à H:i:s') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Action :</strong>
                                                <p>
                                                    @php
                                                        $actionConfig = [
                                                            'login' => ['icon' => 'sign-in-alt', 'color' => 'success', 'label' => 'Connexion'],
                                                            'logout' => ['icon' => 'sign-out-alt', 'color' => 'warning', 'label' => 'Déconnexion'],
                                                            'payment' => ['icon' => 'money-check-alt', 'color' => 'primary', 'label' => 'Paiement'],
                                                            'create_user' => ['icon' => 'user-plus', 'color' => 'info', 'label' => 'Création utilisateur'],
                                                            'update_user' => ['icon' => 'user-edit', 'color' => 'warning', 'label' => 'Modification utilisateur'],
                                                            'delete_user' => ['icon' => 'user-times', 'color' => 'danger', 'label' => 'Suppression utilisateur'],
                                                            'create_taxe' => ['icon' => 'plus-circle', 'color' => 'success', 'label' => 'Création taxe'],
                                                            'update_taxe' => ['icon' => 'edit', 'color' => 'warning', 'label' => 'Modification taxe'],
                                                            'delete_taxe' => ['icon' => 'trash', 'color' => 'danger', 'label' => 'Suppression taxe'],
                                                            'cancel_payment' => ['icon' => 'ban', 'color' => 'danger', 'label' => 'Annulation paiement'],
                                                        ];
                                                        $config = $actionConfig[$log->action] ?? ['icon' => 'info-circle', 'color' => 'secondary', 'label' => ucfirst(str_replace('_', ' ', $log->action))];
                                                    @endphp
                                                    <span class="badge bg-{{ $config['color'] }} fs-6">
                                                        <i class="fas fa-{{ $config['icon'] }}"></i>
                                                        {{ $config['label'] }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($log->description)
                                        <div class="mb-3">
                                            <strong>Description :</strong>
                                            <p class="text-muted">{{ $log->description }}</p>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Adresse IP :</strong>
                                                <p><code>{{ $log->ip_address ?? 'N/A' }}</code></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>User Agent :</strong>
                                                <p class="small text-muted">{{ $log->user_agent ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($log->details)
                                        <div class="mb-3">
                                            <strong>Détails techniques :</strong>
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    @php
                                                        $details = is_string($log->details) ? json_decode($log->details, true) : $log->details;
                                                    @endphp
                                                    @if(is_array($details))
                                                        @switch($log->action)
                                                            @case('payment')
                                                                <div class="row">
                                                                    @if(isset($details['paiement_id']))
                                                                        <div class="col-md-6">
                                                                            <strong>ID Paiement :</strong> {{ $details['paiement_id'] }}
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($details['unite_id']))
                                                                        <div class="col-md-6">
                                                                            <strong>ID Unité :</strong> {{ $details['unite_id'] }}
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($details['montant']))
                                                                        <div class="col-md-6">
                                                                            <strong>Montant :</strong> {{ number_format($details['montant'], 0, ',', ' ') }} FCFA
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($details['mode']))
                                                                        <div class="col-md-6">
                                                                            <strong>Mode :</strong> {{ ucfirst($details['mode']) }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                @break
                                                            @case('create_user')
                                                            @case('update_user')
                                                                <div class="row">
                                                                    @if(isset($details['user_id']))
                                                                        <div class="col-md-6">
                                                                            <strong>ID Utilisateur :</strong> {{ $details['user_id'] }}
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($details['user_name']))
                                                                        <div class="col-md-6">
                                                                            <strong>Nom :</strong> {{ $details['user_name'] }}
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($details['user_role']))
                                                                        <div class="col-md-6">
                                                                            <strong>Rôle :</strong> {{ ucfirst($details['user_role']) }}
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($details['user_identifiant']))
                                                                        <div class="col-md-6">
                                                                            <strong>Identifiant :</strong> {{ $details['user_identifiant'] }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                @break
                                                            @case('create_taxe')
                                                            @case('update_taxe')
                                                                <div class="row">
                                                                    @if(isset($details['taxe_id']))
                                                                        <div class="col-md-6">
                                                                            <strong>ID Taxe :</strong> {{ $details['taxe_id'] }}
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($details['taxe_nom']))
                                                                        <div class="col-md-6">
                                                                            <strong>Nom :</strong> {{ $details['taxe_nom'] }}
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($details['montant']))
                                                                        <div class="col-md-6">
                                                                            <strong>Montant :</strong> {{ number_format($details['montant'], 0, ',', ' ') }} FCFA
                                                                        </div>
                                                                    @endif
                                                                    @if(isset($details['ancien_montant']) && isset($details['nouveau_montant']))
                                                                        <div class="col-md-12">
                                                                            <strong>Modification :</strong> 
                                                                            {{ number_format($details['ancien_montant'], 0, ',', ' ') }} FCFA 
                                                                            → {{ number_format($details['nouveau_montant'], 0, ',', ' ') }} FCFA
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                @break
                                                            @default
                                                                <pre class="small">{{ json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        @endswitch
                                                    @else
                                                        <pre class="small">{{ $log->details }}</pre>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Informations utilisateur -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user"></i>
                                        Utilisateur
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($log->user)
                                        <div class="text-center mb-3">
                                            @switch($log->user->role)
                                                @case('maire')
                                                    <i class="fas fa-crown fa-3x text-warning"></i>
                                                    @break
                                                @case('admin')
                                                    <i class="fas fa-user-shield fa-3x text-primary"></i>
                                                    @break
                                                @case('collecteur')
                                                    <i class="fas fa-user-tie fa-3x text-success"></i>
                                                    @break
                                                @default
                                                    <i class="fas fa-user fa-3x text-secondary"></i>
                                            @endswitch
                                            <h6 class="mt-2">{{ $log->user->name }}</h6>
                                            <p class="text-muted">{{ ucfirst($log->user->role) }}</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong>Identifiant :</strong>
                                            <p class="text-muted">{{ $log->user->identifiant }}</p>
                                        </div>

                                        @if($log->user->zone)
                                            <div class="mb-3">
                                                <strong>Zone assignée :</strong>
                                                <p><span class="badge bg-info">{{ $log->user->zone->nom }}</span></p>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <strong>Membre depuis :</strong>
                                            <p class="text-muted">{{ $log->user->created_at->format('d/m/Y') }}</p>
                                        </div>

                                        <div class="d-grid">
                                            <a href="{{ route('users.show', $log->user) }}" 
                                               class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                                Voir le profil
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-center">
                                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">Utilisateur supprimé</h6>
                                            <p class="text-muted">L'utilisateur qui a effectué cette action n'existe plus.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Contexte temporel -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clock"></i>
                                        Contexte temporel
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Il y a :</strong>
                                        <p class="text-muted">{{ $log->created_at->diffForHumans() }}</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Jour de la semaine :</strong>
                                        <p class="text-muted">{{ $log->created_at->locale('fr')->isoFormat('dddd') }}</p>
                                    </div>

                                    <div class="mb-3">
                                        <strong>Timestamp :</strong>
                                        <p><code>{{ $log->created_at->timestamp }}</code></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Activités liées -->
                            @if($log->user)
                                @php
                                    $relatedLogs = App\Models\Log::where('user_id', $log->user_id)
                                        ->where('id', '!=', $log->id)
                                        ->where('created_at', '>=', $log->created_at->subHours(2))
                                        ->where('created_at', '<=', $log->created_at->addHours(2))
                                        ->orderBy('created_at', 'desc')
                                        ->limit(5)
                                        ->get();
                                @endphp
                                @if($relatedLogs->count() > 0)
                                    <div class="card mt-3">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <i class="fas fa-link"></i>
                                                Activités liées
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @foreach($relatedLogs as $relatedLog)
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <small class="text-muted">{{ $relatedLog->created_at->format('H:i:s') }}</small>
                                                        <br>
                                                        <span class="badge bg-secondary small">{{ ucfirst(str_replace('_', ' ', $relatedLog->action)) }}</span>
                                                    </div>
                                                    <a href="{{ route('logs.show', $relatedLog) }}" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
