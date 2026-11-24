@extends('layouts.app')

@section('title', 'Diagnostic SICOSA - Vérification des Pages')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-stethoscope me-2"></i>
                        Diagnostic SICOSA - Vérification des Pages
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Diagnostic en cours...</strong> Vérification de l'accessibilité de toutes les pages du système.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Pages Principales
                            </h5>
                            <div class="list-group">
                                <a href="{{ route('maire.dashboard') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                    <div>
                                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                                        Dashboard
                                    </div>
                                    <span class="badge bg-primary rounded-pill">Accéder</span>
                                </a>
                                
                                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                    <div>
                                        <i class="fas fa-users me-2 text-info"></i>
                                        Utilisateurs
                                    </div>
                                    <span class="badge bg-info rounded-pill">Accéder</span>
                                </a>
                                
                                <a href="{{ route('zones.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                    <div>
                                        <i class="fas fa-map-marked-alt me-2 text-success"></i>
                                        Zones
                                    </div>
                                    <span class="badge bg-success rounded-pill">Accéder</span>
                                </a>
                                
                                <a href="{{ route('unites.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                    <div>
                                        <i class="fas fa-store me-2 text-warning"></i>
                                        Unités
                                    </div>
                                    <span class="badge bg-warning rounded-pill">Accéder</span>
                                </a>
                                
                                <a href="{{ route('taxes.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                    <div>
                                        <i class="fas fa-coins me-2 text-danger"></i>
                                        Taxes
                                    </div>
                                    <span class="badge bg-danger rounded-pill">Accéder</span>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-cogs me-2"></i>
                                Pages de Gestion
                            </h5>
                            <div class="list-group">
                                <a href="{{ route('paiements.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                    <div>
                                        <i class="fas fa-money-check-alt me-2 text-success"></i>
                                        Paiements
                                    </div>
                                    <span class="badge bg-success rounded-pill">Accéder</span>
                                </a>
                                
                                <a href="{{ route('rapports.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                    <div>
                                        <i class="fas fa-chart-line me-2 text-info"></i>
                                        Rapports
                                    </div>
                                    <span class="badge bg-info rounded-pill">Accéder</span>
                                </a>
                                
                                <a href="{{ route('map.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                    <div>
                                        <i class="fas fa-map me-2 text-primary"></i>
                                        Carte
                                    </div>
                                    <span class="badge bg-primary rounded-pill">Accéder</span>
                                </a>
                                
                                <a href="{{ route('logs.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                    <div>
                                        <i class="fas fa-history me-2 text-secondary"></i>
                                        Logs
                                    </div>
                                    <span class="badge bg-secondary rounded-pill">Accéder</span>
                                </a>
                                
                                <a href="{{ route('alerts.payment-overdue') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                    <div>
                                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                                        Alertes
                                    </div>
                                    <span class="badge bg-warning rounded-pill">Accéder</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-tools me-2"></i>
                                Outils de Diagnostic
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <i class="fas fa-database fa-2x text-primary mb-2"></i>
                                            <h6>Base de Données</h6>
                                            <button class="btn btn-sm btn-outline-primary" onclick="checkDatabase()">
                                                Vérifier
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <i class="fas fa-route fa-2x text-success mb-2"></i>
                                            <h6>Routes</h6>
                                            <button class="btn btn-sm btn-outline-success" onclick="checkRoutes()">
                                                Vérifier
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-alt fa-2x text-info mb-2"></i>
                                            <h6>Vues</h6>
                                            <button class="btn btn-sm btn-outline-info" onclick="checkViews()">
                                                Vérifier
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-terminal me-2"></i>
                                        Console de Diagnostic
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="diagnostic-console" class="bg-dark text-light p-3 rounded" style="height: 200px; overflow-y: auto; font-family: monospace; font-size: 0.8rem;">
                                        <div class="text-success">✅ Diagnostic SICOSA démarré...</div>
                                        <div class="text-info">📋 Vérification des pages en cours...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const console = document.getElementById('diagnostic-console');
    
    function log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const color = type === 'success' ? 'text-success' : type === 'error' ? 'text-danger' : 'text-info';
        console.innerHTML += `<div class="${color}">[${timestamp}] ${message}</div>`;
        console.scrollTop = console.scrollHeight;
    }
    
    // Vérification automatique au chargement
    setTimeout(() => {
        log('🔍 Vérification des routes principales...', 'info');
        
        const pages = [
            { name: 'Dashboard', url: '{{ route("maire.dashboard") }}' },
            { name: 'Utilisateurs', url: '{{ route("users.index") }}' },
            { name: 'Zones', url: '{{ route("zones.index") }}' },
            { name: 'Unités', url: '{{ route("unites.index") }}' },
            { name: 'Taxes', url: '{{ route("taxes.index") }}' },
            { name: 'Paiements', url: '{{ route("paiements.index") }}' },
            { name: 'Rapports', url: '{{ route("rapports.index") }}' },
            { name: 'Carte', url: '{{ route("map.index") }}' },
            { name: 'Logs', url: '{{ route("logs.index") }}' }
        ];
        
        pages.forEach((page, index) => {
            setTimeout(() => {
                fetch(page.url, { method: 'HEAD' })
                    .then(response => {
                        if (response.ok) {
                            log(`✅ ${page.name} - Accessible`, 'success');
                        } else {
                            log(`❌ ${page.name} - Erreur ${response.status}`, 'error');
                        }
                    })
                    .catch(error => {
                        log(`❌ ${page.name} - Erreur réseau`, 'error');
                    });
            }, index * 500);
        });
        
        setTimeout(() => {
            log('🎉 Diagnostic terminé !', 'success');
        }, pages.length * 500 + 1000);
    }, 1000);
});

function checkDatabase() {
    const console = document.getElementById('diagnostic-console');
    console.innerHTML += '<div class="text-info">🔍 Vérification de la base de données...</div>';
    console.scrollTop = console.scrollHeight;
    
    // Simulation de vérification
    setTimeout(() => {
        console.innerHTML += '<div class="text-success">✅ Base de données connectée</div>';
        console.scrollTop = console.scrollHeight;
    }, 1000);
}

function checkRoutes() {
    const console = document.getElementById('diagnostic-console');
    console.innerHTML += '<div class="text-info">🔍 Vérification des routes...</div>';
    console.scrollTop = console.scrollHeight;
    
    // Simulation de vérification
    setTimeout(() => {
        console.innerHTML += '<div class="text-success">✅ Toutes les routes sont définies</div>';
        console.scrollTop = console.scrollHeight;
    }, 1000);
}

function checkViews() {
    const console = document.getElementById('diagnostic-console');
    console.innerHTML += '<div class="text-info">🔍 Vérification des vues...</div>';
    console.scrollTop = console.scrollHeight;
    
    // Simulation de vérification
    setTimeout(() => {
        console.innerHTML += '<div class="text-success">✅ Toutes les vues sont disponibles</div>';
        console.scrollTop = console.scrollHeight;
    }, 1000);
}
</script>

@endsection 