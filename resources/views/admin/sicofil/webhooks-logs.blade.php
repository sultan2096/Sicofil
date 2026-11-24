@extends('layouts.app')

@section('title', 'Admin SICOFIL - Webhooks')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-webhook"></i> Logs Webhooks</h3>
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
                        <th>Provider</th>
                        <th>Statut</th>
                        <th>Commune</th>
                        <th>Référence</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($webhooks as $webhook)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($webhook->webhook_received_at ?? $webhook->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $webhook->provider ?? 'N/A' }}</td>
                        <td>{{ ucfirst($webhook->statut ?? 'N/A') }}</td>
                        <td>{{ $webhook->commune_nom ?? 'N/A' }}</td>
                        <td>{{ $webhook->reference ?? $webhook->reference_externe ?? 'N/A' }}</td>
                        <td class="text-muted">{{ $webhook->message ?? $webhook->error ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <p class="mb-0">Aucun log</p>
                            <small class="text-muted">C'est normal tant qu'aucun paiement Mobile Money (Wave, Orange Money, etc.) n'a été reçu via webhook. Les logs s'afficheront automatiquement lorsque des callbacks seront enregistrés.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $webhooks->links() }}
        </div>
    </div>
</div>
@endsection
