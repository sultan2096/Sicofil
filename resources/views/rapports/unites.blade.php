@extends('layouts.app')

@section('title', 'Rapport Unités Commerciales - SICOSA')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    .rapport-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

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

    .header-content {
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
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

    /* Export Buttons */
    .export-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .export-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        color: white;
    }

    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .export-btn.pdf {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
    }

    .export-btn.excel {
        background: linear-gradient(135deg, #27ae60, #229954);
    }

    .export-btn.print {
        background: linear-gradient(135deg, #34495e, #2c3e50);
    }

    /* Stats Cards */
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

    .stat-card.total { border-top-color: #667eea; }
    .stat-card.actives { border-top-color: #48bb78; }
    .stat-card.inactives { border-top-color: #f56565; }
    .stat-card.paiement { border-top-color: #ed8936; }

    .stat-number {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .stat-number.total { color: #667eea; }
    .stat-number.actives { color: #48bb78; }
    .stat-number.inactives { color: #f56565; }
    .stat-number.paiement { color: #ed8936; }

    .stat-label {
        color: #718096;
        font-size: 14px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Table Section */
    .table-section {
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
        content: '🏪';
        font-size: 28px;
    }

    .table-responsive {
        overflow-x: auto;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    }

    .unites-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        min-width: 800px;
    }

    .unites-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .unites-table th {
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .unites-table td {
        padding: 16px 15px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
        vertical-align: middle;
    }

    .unites-table tbody tr:hover {
        background: #f7fafc;
        transition: all 0.2s ease;
    }

    .nom-commercial {
        font-weight: 600;
        color: #2d3748;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nom-commercial::before {
        content: '🏪';
        font-size: 16px;
    }

    .proprietaire {
        color: #4a5568;
        font-weight: 500;
    }

    .zone-badge {
        background: #4299e1;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 12px;
        display: inline-block;
        text-align: center;
    }

    .activite {
        color: #718096;
        font-style: italic;
    }

    .statut-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 12px;
        text-align: center;
        display: inline-block;
    }

    .statut-badge.actif {
        background: #48bb78;
        color: white;
    }

    .statut-badge.inactif {
        background: #f56565;
        color: white;
    }

    .telephone {
        color: #667eea;
        font-weight: 500;
        font-family: monospace;
    }

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

    /* Footer */
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

    /* Responsive */
    @media (max-width: 1200px) {
        .header-content {
            flex-direction: column;
            gap: 20px;
            align-items: flex-start;
        }

        .export-buttons {
            width: 100%;
            justify-content: center;
        }
    }

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

        .export-btn {
            flex: 1;
            justify-content: center;
        }

        .unites-table th,
        .unites-table td {
            padding: 12px 8px;
            font-size: 12px;
        }
    }

    /* Print Styles */
    @media print {
        body {
            background: white !important;
        }
        
        .rapport-container {
            max-width: none !important;
            padding: 0 !important;
        }
        
        .export-buttons {
            display: none !important;
        }
        
        .header-rapport,
        .table-section,
        .footer-rapport {
            box-shadow: none !important;
            border: 1px solid #e2e8f0;
        }
    }
</style>

<div class="rapport-container">
    <!-- Header avec Logo SICOSA -->
    <div class="header-rapport">
        <img src="{{ asset('images/logos/snd.jpeg') }}" alt="Logo SICOSA" class="logo-sicosa">
        <div class="header-content">
            <div class="header-text">
                <h1>RAPPORT UNITÉS COMMERCIALES</h1>
                <h2>SYSTÈME D'INFORMATION COMMERCIALE DE SANDIARA</h2>
                <div class="header-info">
                    <p><strong>Zone :</strong> {{ $stats['zone'] ?? 'Toutes les zones' }}</p>
                    <p><strong>Période :</strong> {{ $stats['periode'] ?? 'Non définie' }}</p>
                    @if(isset($stats['montant_total_collecte']))
                        <p><strong>Montant total collecté :</strong> {{ number_format($stats['montant_total_collecte'], 0, ',', ' ') }} FCFA</p>
                    @endif
                    <p><strong>Généré le :</strong> {{ now()->format('d/m/Y à H:i') }} par {{ auth()->user()->name ?? 'Système' }}</p>
                </div>
            </div>
            
            <!-- Boutons d'export -->
            <div class="export-buttons">
                <button class="export-btn pdf" onclick="exportToPDF()">
                    📄 Export PDF
                </button>
                <button class="export-btn excel" onclick="exportToExcel()">
                    📊 Export Excel
                </button>
                <button class="export-btn print" onclick="window.print()">
                    🖨️ Imprimer
                </button>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-number total">{{ $stats['total_unites'] ?? 0 }}</div>
            <div class="stat-label">Total unités</div>
        </div>
        
        <div class="stat-card actives">
            <div class="stat-number actives">{{ $stats['unites_actives'] ?? 0 }}</div>
            <div class="stat-label">Unités actives</div>
        </div>
        
        <div class="stat-card inactives">
            <div class="stat-number inactives">{{ $stats['unites_inactives'] ?? 0 }}</div>
            <div class="stat-label">Unités inactives</div>
        </div>
        
        <div class="stat-card paiement">
            <div class="stat-number paiement">{{ $stats['avec_paiement_periode'] ?? 0 }}</div>
            <div class="stat-label">Avec paiement sur la période</div>
        </div>
    </div>

    <!-- Liste des Unités Commerciales -->
    <div class="table-section">
        <h2 class="section-title">Liste des Unités Commerciales</h2>
        
        @forelse($unites ?? [] as $unite)
            @if($loop->first)
                <div class="table-responsive">
                    <table class="unites-table">
                        <thead>
                            <tr>
                                <th>Nom Commercial</th>
                                <th>Propriétaire</th>
                                <th>Zone</th>
                                <th>Activité</th>
                                <th>Statut</th>
                                <th>Téléphone</th>
                            </tr>
                        </thead>
                        <tbody>
            @endif
            
            <tr>
                <td>
                    <div class="nom-commercial">{{ $unite->nom_commercial ?? 'N/A' }}</div>
                </td>
                <td>
                    <div class="proprietaire">{{ $unite->nom_proprietaire ?? 'N/A' }}</div>
                </td>
                <td>
                    <span class="zone-badge">{{ $unite->zone->nom ?? 'N/A' }}</span>
                </td>
                <td>
                    <div class="activite">{{ $unite->type_activite ?? 'N/A' }}</div>
                </td>
                <td>
                    @if($unite->actif ?? false)
                        <span class="statut-badge actif">Actif</span>
                    @else
                        <span class="statut-badge inactif">Inactif</span>
                    @endif
                </td>
                <td>
                    <div class="telephone">{{ $unite->telephone ?? 'N/A' }}</div>
                </td>
            </tr>
            
            @if($loop->last)
                        </tbody>
                    </table>
                </div>
            @endif
            
        @empty
            <div class="empty-state">
                <div class="icon">🏪</div>
                <h3>Aucune unité commerciale trouvée</h3>
                <p>Il n'y a actuellement aucune unité commerciale enregistrée dans cette zone.</p>
            </div>
        @endforelse
    </div>

    <!-- Footer -->
    <div class="footer-rapport">
        <p><strong>SICOSA</strong> - Système d'Information Commerciale de Sandiara</p>
        <p>Rapport généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</div>

<script>
    function exportToPDF() {
        // Simulation d'export PDF
        alert('🔄 Export PDF en cours...\n\nFonctionnalité en développement.\nUtilisez "Imprimer" puis "Enregistrer en PDF" pour le moment.');
    }

    function exportToExcel() {
        // Simulation d'export Excel
        const table = document.querySelector('.unites-table');
        if (table) {
            alert('📊 Export Excel en cours...\n\nFonctionnalité en développement.\nTableau détecté avec ' + (table.querySelectorAll('tbody tr').length) + ' unités.');
        } else {
            alert('❌ Aucune donnée à exporter.');
        }
    }

    // Print optimization
    window.addEventListener('beforeprint', function() {
        document.body.style.background = 'white';
    });

    window.addEventListener('afterprint', function() {
        document.body.style.background = '';
    });
</script>
@endsection
