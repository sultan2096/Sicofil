@extends('layouts.app')

@section('title', $commune->nom . ' - SICOFIL')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('communes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
        @if($commune->logo_url)
            <img src="{{ $commune->logo_url }}" alt="Logo {{ $commune->nom }}" class="rounded" style="height: 42px; width: auto;">
        @else
            <i class="fas fa-city me-2 text-primary"></i>
        @endif
        <h1 class="h4 mb-0">{{ $commune->nom }}</h1>
        @if(!$commune->actif)<span class="badge bg-secondary">Désactivée</span>@endif
        <form method="POST" action="{{ route('communes.scope.set', $commune) }}" class="ms-auto">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm">
                <i class="fas fa-layer-group me-1"></i> Activer le scope
            </button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted small">Zones fiscales</h6>
                    <h3 class="mb-0">{{ $commune->zones_fiscales_count }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted small">Unités commerciales</h6>
                    <h3 class="mb-0">{{ $commune->unites_commerciales_count }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted small">Recettes (FCFA)</h6>
                    <h3 class="mb-0 text-success">{{ number_format($commune->total_recettes ?? 0, 0, ',', ' ') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted small">Taxes</h6>
                    <h3 class="mb-0">{{ $commune->taxes_count }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Informations</h6></div>
                <div class="card-body">
                    @if($commune->logo_url)
                        <p class="mb-2"><strong>Logo :</strong><br><img src="{{ $commune->logo_url }}" alt="Logo" class="rounded mt-1" style="max-height: 60px;"></p>
                    @endif
                    <p class="mb-1"><strong>Acronyme (nom du système) :</strong> {{ $commune->acronyme ?? '—' }}</p>
                    <p class="mb-1"><strong>Code :</strong> {{ $commune->code ?? '—' }}</p>
                    <p class="mb-1"><strong>Région :</strong> {{ $commune->region ?? '—' }}</p>
                    <p class="mb-1"><strong>Département :</strong> {{ $commune->departement ?? '—' }}</p>
                    @if($commune->description)<p class="mb-0 mt-2">{{ $commune->description }}</p>@endif
                    <hr>
                    <a href="{{ route('communes.edit', $commune) }}" class="btn btn-outline-primary btn-sm">Modifier</a>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Utilisateurs</h6></div>
                <div class="card-body">
                    @if($maires->count() || $gestionnaires->count() || $collecteurs->count())
                        @foreach($maires as $u)<span class="badge bg-primary me-1">{{ $u->name }} (Maire)</span>@endforeach
                        @foreach($gestionnaires as $u)<span class="badge bg-info me-1">{{ $u->name }} (Gestionnaire)</span>@endforeach
                        @foreach($collecteurs as $u)<span class="badge bg-secondary me-1">{{ $u->name }} (Collecteur)</span>@endforeach
                    @else
                        <p class="text-muted mb-0">Aucun utilisateur affecté. Créez un Maire ou un Gestionnaire pour que la commune puisse se connecter.</p>
                    @endif
                    <hr class="my-2">
                    <a href="{{ route('communes.users.create', $commune) }}" class="btn btn-sm btn-primary"><i class="fas fa-user-plus me-1"></i>Créer un utilisateur pour cette commune</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
