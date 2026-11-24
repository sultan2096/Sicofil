@extends('layouts.app')

@section('title', 'Admin SICOFIL - Monitoring')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-heartbeat"></i> Monitoring centralisé</h3>
        <a href="{{ route('admin.sicofil.dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Dashboard</a>
    </div>

    <div class="row mb-4">
        @foreach($checks as $check)
            @php($color = $check['status'] === 'ok' ? 'success' : ($check['status'] === 'warning' ? 'warning' : 'danger'))
            <div class="col-md-4 mb-3">
                <div class="card border-{{ $color }} h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">{{ $check['label'] }}</p>
                                <h4 class="text-{{ $color }} mb-0">{{ strtoupper($check['status']) }}</h4>
                            </div>
                            <i class="fas fa-circle text-{{ $color }} fa-2x"></i>
                        </div>
                        <small class="text-muted d-block mt-3">{{ $check['details'] }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-md-3"><div class="card"><div class="card-body text-center"><h3>{{ $systemStats['failedJobs'] }}</h3><p class="text-muted mb-0">Jobs échoués</p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body text-center"><h3>{{ $systemStats['pendingNotifications'] }}</h3><p class="text-muted mb-0">Notifications en attente</p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body text-center"><h3>{{ $systemStats['failedWebhooks'] }}</h3><p class="text-muted mb-0">Webhooks échoués</p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body text-center"><h3>{{ $systemStats['recentErrors'] }}</h3><p class="text-muted mb-0">Erreurs 24h</p></div></div></div>
    </div>

    <div class="card mt-4">
        <div class="card-header"><h5 class="mb-0">Préparation déploiement centralisé</h5></div>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <strong>État :</strong> le monitoring applicatif est disponible. Le déploiement centralisé complet dépendra du serveur, du domaine HTTPS, du plan de sauvegarde, du scheduler Laravel et du superviseur de queues.
            </div>
        </div>
    </div>
</div>
@endsection
