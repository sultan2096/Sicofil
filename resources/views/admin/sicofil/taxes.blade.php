@extends('layouts.app')

@section('title', 'Admin SICOFIL - Taxes par commune')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><i class="fas fa-coins"></i> Taxes appliquées par commune</h3>
        <a href="{{ route('admin.sicofil.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Commune</label>
                    <select name="commune_id" class="form-select">
                        <option value="">Toutes les communes</option>
                        @foreach($communes as $commune)
                            <option value="{{ $commune->id }}" {{ (int) $selectedCommuneId === (int) $commune->id ? 'selected' : '' }}>{{ $commune->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrer</button>
                    <a href="{{ route('admin.sicofil.taxes') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Commune</th>
                        <th>Taxe</th>
                        <th>Nature</th>
                        <th>Périodicité</th>
                        <th>Montant</th>
                        <th>Unités liées</th>
                        <th>Dernière MAJ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxes as $taxe)
                        <tr>
                            <td>{{ $taxe->commune?->nom ?? 'N/A' }}</td>
                            <td><strong>{{ $taxe->nom }}</strong><br><small class="text-muted">{{ $taxe->description }}</small></td>
                            <td>{{ $taxe->nature ?? 'N/A' }}</td>
                            <td>{{ $taxe->periodicite_display ?? $taxe->periodicite }}</td>
                            <td>
                                @if($taxe->is_fonciere)
                                    {{ number_format($taxe->montant_par_m2 ?? 0, 0, ',', ' ') }} FCFA / m²
                                @else
                                    {{ number_format($taxe->montant ?? 0, 0, ',', ' ') }} FCFA
                                @endif
                            </td>
                            <td>{{ number_format($taxe->unites_commerciales_count ?? 0, 0, ',', ' ') }}</td>
                            <td>{{ optional($taxe->updated_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucune taxe trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $taxes->links() }}
        </div>
    </div>
</div>
@endsection
