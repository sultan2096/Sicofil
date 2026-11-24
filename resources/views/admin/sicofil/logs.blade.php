@extends('layouts.app')

@section('title', 'Admin SICOFIL - Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><i class="fas fa-history"></i> Logs nationaux</h3>
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
                    <a href="{{ route('admin.sicofil.logs') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Commune</th>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Action</th>
                        <th>IP</th>
                        <th>Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->commune?->nom ?? 'N/A' }}</td>
                            <td>{{ $log->user?->name ?? $log->user?->identifiant ?? 'Système' }}</td>
                            <td>{{ $log->user?->role ?? 'N/A' }}</td>
                            <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                            <td><code>{{ $log->ip_address }}</code></td>
                            <td class="small text-muted" style="max-width: 420px; white-space: normal;">
                                @if(is_array($log->details))
                                    <pre class="mb-0 small">{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    {{ $log->details }}
                                @endif
                                @if($log->commentaire)
                                    <div>{{ $log->commentaire }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucun log trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
