@extends('layouts.app')

@section('title', 'Rapport Collecteurs - SICOSA')

@section('content')
<style>
    /* ============ CSS MODERNE ET PROFESSIONNEL ============ */
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        margin: 0;
        padding: 0;
    }

    .rapport-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    /* ============ HEADER AVEC LOGO ============ */
    .header-rapport {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 25px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }

    .logo-sicosa {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #667eea;
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    }

    .header-text h1 {
        font-size: 36px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
    }

    .header-text h2 {
        font-size: 18px;
        color: #4a5568;
        margin-bottom: 15px;
        font-weight: 500;
    }

    .header-info {
        background: rgba(103, 126, 234, 0.1);
        padding: 12px 20px;
        border-radius: 10px;
        border-left: 4px solid #667eea;
    }

    .header-info p {
        margin: 0;
        color: #2d3748;
        font-size: 14px;
    }

    /* ============ CARTES STATISTIQUES ============ */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 35px;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        border-top: 4px solid;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }

    .stat-card.collecteurs { border-top-color: #667eea; }
    .stat-card.paiements { border-top-color: #48bb78; }
    .stat-card.montant { border-top-color: #ed8936; }
    .stat-card.moyenne { border-top-color: #9f7aea; }

    .stat-number {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .stat-number.collecteurs { color: #667eea; }
    .stat-number.paiements { color: #48bb78; }
    .stat-number.montant { color: #ed8936; }
    .stat-number.moyenne { color: #9f7aea; }

    .stat-label {
        color: #718096;
        font-size: 14px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* ============ SECTION TABLEAU ============ */
    .performance-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    .section-title {
        font-size: 24px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-title::before {
        content: '📊';
        font-size: 28px;
    }

    .table-responsive {
        overflow-x: auto;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    }

    .collecteurs-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
    }

    .collecteurs-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .collecteurs-table th {
        padding: 18px 20px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .collecteurs-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 15px;
        vertical-align: middle;
    }

    .collecteurs-table tbody tr:hover {
        background: #f7fafc;
        transition: all 0.2s ease;
    }

    .collecteur-name {
        font-weight: 600;
        color: #2d3748;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .collecteur-name::before {
        content: '👤';
        font-size: 18px;
    }

    .paiements-count {
        background: #48bb78;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 13px;
        display: inline-block;
    }

    .montant-total {
        font-weight: bold;
        color: #ed8936;
        font-size: 16px;
    }

    .moyenne-paiement {
        font-weight: 600;
        color: #9f7aea;
    }

    /* ============ ÉTAT VIDE ============ */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #718096;
    }

    .empty-state .icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 24px;
        margin-bottom: 10px;
        color: #4a5568;
    }

    .empty-state p {
        font-size: 16px;
        max-width: 400px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* ============ FOOTER ============ */
    .footer-rapport {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        color: #2d3748;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        font-size: 14px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 768px) {
        .header-rapport {
            flex-direction: column;
            text-align: center;
        }

        .header-text h1 {
            font-size: 28px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .collecteurs-table th,
        .collecteurs-table td {
            padding: 12px 10px;
            font-size: 13px;
        }
    }

    /* ============ PRINT STYLES ============ */
    @media print {
        body {
            background: white !important;
        }
        
        .rapport-container {
            max-width: none !important;
            padding: 0 !important;
        }
    }
</style>

<!-- ============ CONTENU HTML STRUCTURÉ ============ -->
<div class="rapport-container">
    
    <!-- ============ HEADER AVEC LOGO ============ -->
    <div class="header-rapport">
        <img src="{{ asset('images/logos/snd.jpeg') }}" alt="Logo SICOSA" class="logo-sicosa">
        <div class="header-text">
            <h1>RAPPORT COLLECTEURS</h1>
            <h2>SYSTÈME D'INFORMATION COMMERCIALE DE SANDIARA</h2>
            <div class="header-info">
                <p><strong>Période :</strong> {{ $stats['periode'] ?? 'Toutes périodes' }}</p>
                <p><strong>Généré le :</strong> {{ now()->format('d/m/Y à H:i') }} par {{ auth()->user()->name ?? 'Système' }}</p>
            </div>
        </div>
    </div>

    <!-- ============ CARTES STATISTIQUES ============ -->
    <div class="stats-grid">
        <div class="stat-card collecteurs">
            <div class="stat-number collecteurs">{{ $stats['total_collecteurs'] ?? 0 }}</div>
            <div class="stat-label">Collecteurs</div>
        </div>
        
        <div class="stat-card paiements">
            <div class="stat-number paiements">{{ $stats['total_paiements'] ?? 0 }}</div>
            <div class="stat-label">Paiements</div>
        </div>
        
        <div class="stat-card montant">
            <div class="stat-number montant">{{ number_format($stats['total_montant'] ?? 0, 0, ',', ' ') }}</div>
            <div class="stat-label">FCFA collectés</div>
        </div>
        
        <div class="stat-card moyenne">
            <div class="stat-number moyenne">
                {{ ($stats['total_paiements'] ?? 0) > 0 ? number_format(($stats['total_montant'] ?? 0) / $stats['total_paiements'], 0, ',', ' ') : 0 }}
            </div>
            <div class="stat-label">Moyenne</div>
        </div>
    </div>

    <!-- ============ TABLEAU PERFORMANCE COLLECTEURS ============ -->
    <div class="performance-section">
        <h2 class="section-title">Performance des Collecteurs</h2>
        
        {{-- ✅ LOGIQUE CORRECTE DU TABLEAU --}}
        @forelse($stats_collecteurs ?? [] as $stat)
            @if($loop->first)
                <div class="table-responsive">
                    <table class="collecteurs-table">
                        <thead>
                            <tr>
                                <th>Collecteur</th>
                                <th>Paiements</th>
                                <th>Montant Total</th>
                                <th>Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
            @endif
            
            <tr>
                <td>
                    <div class="collecteur-name">{{ $stat['collecteur']->name ?? 'N/A' }}</div>
                </td>
                <td>
                    <span class="paiements-count">{{ $stat['nombre_paiements'] ?? 0 }}</span>
                </td>
                <td>
                    <span class="montant-total">{{ number_format($stat['montant_total'] ?? 0, 0, ',', ' ') }} FCFA</span>
                </td>
                <td>
                    <span class="moyenne-paiement">{{ number_format($stat['moyenne_par_paiement'] ?? 0, 0, ',', ' ') }} FCFA</span>
                </td>
            </tr>
            
            @if($loop->last)
                        </tbody>
                    </table>
                </div>
            @endif
            
        @empty
            {{-- ✅ ÉTAT VIDE STYLÉ --}}
            <div class="empty-state">
                <div class="icon">📋</div>
                <h3>Aucun collecteur trouvé</h3>
                <p>Il n'y a actuellement aucune donnée de collecteur disponible pour la période sélectionnée.</p>
            </div>
        @endforelse
    </div>

    <!-- ============ FOOTER ============ -->
    <div class="footer-rapport">
        <p><strong>SICOSA</strong> - Système d'Information Commerciale de Sandiara</p>
        <p>Rapport généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
    
</div>
@endsection 