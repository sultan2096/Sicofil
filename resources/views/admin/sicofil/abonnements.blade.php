@extends('layouts.app')

@section('title', 'Admin SICOFIL - Abonnements')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-receipt"></i> Abonnements SaaS</h3>
        <div>
            <a href="{{ route('admin.sicofil.abonnements.create') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus"></i> Créer un abonnement
            </a>
            <a href="{{ route('admin.sicofil.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Commune</th>
                        <th>Formule</th>
                        <th>Statut</th>
                        <th>Expiration</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($abonnements as $abonnement)
                    <tr>
                        <td>{{ $abonnement->commune_nom }}</td>
                        <td><span class="badge bg-info">{{ ucfirst($abonnement->formule) }}</span></td>
                        <td>
                            @if($abonnement->statut === 'actif')
                                <span class="badge bg-success">Actif</span>
                            @elseif($abonnement->statut === 'suspendu')
                                <span class="badge bg-warning text-dark">Suspendu</span>
                            @else
                                <span class="badge bg-secondary">{{ $abonnement->statut }}</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($abonnement->date_expiration)->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.sicofil.abonnements.show', $abonnement->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($abonnement->statut === 'actif')
                            <form method="POST" action="{{ route('admin.sicofil.abonnements.suspend', $abonnement->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.sicofil.abonnements.activate', $abonnement->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-play"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Aucun abonnement</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $abonnements->links() }}
        </div>
    </div>
</div>
@endsection
