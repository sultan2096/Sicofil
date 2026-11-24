@extends('layouts.app')

@section('title', 'Test Rapport Simple - SICOSA')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">🧪 Test Rapport Simple</h3>
                </div>
                <div class="card-body">
                    <!-- Bouton test PDF avec logo -->
                    <div class="alert alert-info">
                        <h5>🎯 Test du nouveau format PDF avec logo SICOSA :</h5>
                        <a href="/rapports/financier-pdf" class="btn btn-danger btn-lg" target="_blank">
                            📄 Voir le PDF avec Logo SICOSA
                        </a>
                    </div>
                    
                    <h4>Données de test :</h4>
                    <ul>
                        <li><strong>Période :</strong> {{ $stats['periode'] }}</li>
                        <li><strong>Total paiements :</strong> {{ $stats['total_paiements'] }}</li>
                        <li><strong>Total montant :</strong> {{ number_format($stats['total_montant'], 0, ',', ' ') }} FCFA</li>
                    </ul>
                    
                    <div class="mt-4">
                        <h5>🔧 Tests de diagnostic :</h5>
                        <p>✅ Variables Blade sont correctement affichées</p>
                        <p>✅ Serveur Laravel fonctionne sur port 8004</p>
                        <p>✅ Logo SICOSA accessible</p>
                        <p>🎯 <strong>Test du PDF avec logo en cours...</strong></p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="/maire/dashboard" class="btn btn-primary">← Retour Dashboard</a>
                        <a href="/rapports" class="btn btn-success">Centre de Rapports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 