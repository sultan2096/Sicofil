@extends('layouts.app')

@section('title', 'Tableau de Bord de Sécurité - SICOSA')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">
                        <i class="fas fa-shield-alt me-2"></i>
                        Centre de Sécurité SICOSA
                    </h1>
                    <p class="card-text">
                        Surveillance et protection du système d'information commerciale
                    </p>
                    <small>
                        <i class="fas fa-clock me-1"></i>
                        Dernière mise à jour : {{ now()->format('d/m/Y à H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ number_format($securityStats['total_violations_24h']) }}</h3>
                    <p class="card-text small">Violations (24h)</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ number_format($securityStats['total_violations_7d']) }}</h3>
                    <p class="card-text small">Violations (7j)</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ number_format($securityStats['failed_logins_24h']) }}</h3>
                    <p class="card-text small">Échecs connexion (24h)</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ number_format($securityStats['successful_logins_24h']) }}</h3>
                    <p class="card-text small">Connexions réussies (24h)</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ number_format($securityStats['unique_ips_24h']) }}</h3>
                    <p class="card-text small">IPs uniques (24h)</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-dark">
                <div class="card-body text-center">
                    <h3 class="text-dark">{{ number_format(count($blockedIPs)) }}</h3>
                    <p class="card-text small">IPs bloquées</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Threats -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Menaces de Sécurité Récentes
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentThreats->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date/Heure</th>
                                        <th>Type</th>
                                        <th>IP</th>
                                        <th>Détails</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentThreats as $threat)
                                    <tr>
                                        <td>{{ $threat->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td>
                                            @if($threat->action === 'security_violation')
                                                <span class="badge bg-danger">Violation</span>
                                            @elseif($threat->action === 'login_failed')
                                                <span class="badge bg-warning">Échec connexion</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($threat->action) }}</span>
                                            @endif
                                        </td>
                                        <td><code>{{ $threat->ip_address }}</code></td>
                                        <td>
                                            @if($threat->details)
                                                @php $details = is_string($threat->details) ? json_decode($threat->details, true) : $threat->details; @endphp
                                                @if(isset($details['user_agent']))
                                                    <div>{{ Str::limit($details['user_agent'], 50) }}</div>
                                                @endif
                                                @if(isset($details['requested_url']))
                                                    <small class="text-muted">URL: {{ Str::limit($details['requested_url'], 40) }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Aucun détail</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(in_array($threat->ip_address, $blockedIPs))
                                                <span class="badge bg-danger">Bloquée</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                            <h5 class="text-success">Aucune menace détectée</h5>
                            <p class="text-muted">Le système est sécurisé</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Blocked IPs -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-ban text-danger me-2"></i>
                        Adresses IP Bloquées
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($blockedIPs) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($blockedIPs as $ip)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <code>{{ $ip }}</code>
                                <span class="badge bg-danger">Bloquée automatiquement</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shield-alt text-success fa-3x mb-3"></i>
                            <h6 class="text-success">Aucune IP bloquée</h6>
                            <p class="text-muted small">Toutes les connexions sont autorisées</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt text-primary me-2"></i>
                        Actions Rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('logs.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-list me-2"></i>
                                Voir tous les logs
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <button onclick="location.reload()" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-sync-alt me-2"></i>
                                Actualiser les données
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Activity Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line text-info me-2"></i>
                        Activité de Sécurité (7 derniers jours)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="securityChart" width="400" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('securityChart').getContext('2d');
    
    const securityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'Violations',
                data: [12, 19, 3, 5, 2, 3, 7],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }, {
                label: 'Échecs de connexion',
                data: [8, 15, 2, 3, 1, 2, 4],
                borderColor: 'rgb(255, 205, 86)',
                backgroundColor: 'rgba(255, 205, 86, 0.1)',
                tension: 0.1
            }, {
                label: 'Connexions réussies',
                data: [45, 52, 38, 42, 35, 28, 41],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Évolution de l\'activité de sécurité'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
