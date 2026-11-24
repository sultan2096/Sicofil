@extends('layouts.app')

@section('title', 'Admin SICOFIL - Audit')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-shield-alt"></i> Journal d'audit national</h3>
        <a href="{{ route('admin.sicofil.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Commune</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($audit->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $audit->commune_nom ?? 'N/A' }}</td>
                        <td>{{ $audit->user_name ?? 'Systeme' }}</td>
                        <td>{{ $audit->action ?? $audit->event ?? 'N/A' }}</td>
                        <td class="text-muted">{{ $audit->details ?? $audit->metadata ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Aucun audit</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $audits->links() }}
        </div>
    </div>
</div>
@endsection
