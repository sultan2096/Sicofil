@extends('layouts.app')

@section('title', 'Admin SICOFIL - Notifications')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-bell"></i> Notifications système</h3>
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
                        <th>Canal</th>
                        <th>Type</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($notification->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $notification->commune_nom ?? 'N/A' }}</td>
                        <td>{{ $notification->canal ?? 'N/A' }}</td>
                        <td>{{ $notification->type ?? 'N/A' }}</td>
                        <td>{{ ucfirst($notification->statut ?? 'N/A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <p class="mb-0">Aucune notification</p>
                            <small class="text-muted">Les notifications (SMS, email, etc.) apparaîtront ici lorsqu'elles seront créées et envoyées par le système (reçus de paiement, rappels, alertes).</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
