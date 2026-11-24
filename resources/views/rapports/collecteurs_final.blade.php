@extends('layouts.app')

@section('title', 'Rapport Collecteurs - SICOSA')

@section('content')
<style>
    /* ============ RESET ET BASE ============ */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
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

    /* ============ BOUTONS EXPORT RÉELS ============ */
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
        position: relative;
        overflow: hidden;
    }

    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    }

    .export-btn:active {
        transform: translateY(0);
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

    .export-btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255,255,255,0.3);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.3s, height 0.3s;
    }

    .export-btn:hover::after {
        width: 300px;
        height: 300px;
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
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        transition: left 0.5s;
    }

    .stat-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }

    .stat-card:hover::before {
        left: 100%;
    }

    .stat-card.collecteurs { border-top-color: #667eea; }
    .stat-card.paiements { border-top-color: #48bb78; }
    .stat-card.montant { border-top-color: #ed8936; }
    .stat-card.moyenne { border-top-color: #9f7aea; }

    .stat-number {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 8px;
        position: relative;
        z-index: 2;
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
        position: relative;
        z-index: 2;
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

    .collecteurs-table tbody tr {
        transition: all 0.2s ease;
    }

    .collecteurs-table tbody tr:hover {
        background: #f7fafc;
        transform: scale(1.01);
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
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
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

    /* ============ ÉTAT VIDE STYLÉ ============ */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #718096;
    }

    .empty-state .icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
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

    /* ============ LOADING ANIMATION ============ */
    .loading {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        color: white;
        z-index: 9999;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 20px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ============ RESPONSIVE ============ */
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

        .collecteurs-table th,
        .collecteurs-table td {
            padding: 12px 10px;
            font-size: 13px;
        }
    }

    /* ============ PRINT STYLES POUR PDF ============ */
    @media print {
        body {
            background: white !important;
            font-size: 12px;
        }
        
        .rapport-container {
            max-width: none !important;
            padding: 0 !important;
        }
        
        .export-buttons {
            display: none !important;
        }
        
        .header-rapport,
        .performance-section,
        .footer-rapport {
            box-shadow: none !important;
            border: 1px solid #e2e8f0;
            break-inside: avoid;
        }

        .stat-card {
            break-inside: avoid;
        }

        .collecteurs-table {
            break-inside: avoid;
        }
    }
</style>

<!-- ============ LOADING OVERLAY ============ -->
<div class="loading" id="loadingOverlay">
    <div class="spinner"></div>
    <p>Génération du rapport en cours...</p>
</div>

<!-- ============ CONTENU PRINCIPAL ============ -->
<div class="rapport-container" id="rapportContent">
    
    <!-- ============ HEADER AVEC LOGO ============ -->
    <div class="header-rapport">
        <img src="{{ asset('images/logos/snd.jpeg') }}" alt="Logo SICOSA" class="logo-sicosa">
        <div class="header-content">
            <div class="header-text">
                <h1>RAPPORT COLLECTEURS</h1>
                <h2>SYSTÈME D'INFORMATION COMMERCIALE DE SANDIARA</h2>
                <div class="header-info">
                    <p><strong>Période :</strong> {{ $stats['periode'] ?? 'Toutes périodes' }}</p>
                    <p><strong>Généré le :</strong> {{ now()->format('d/m/Y à H:i') }} par {{ auth()->user()->name ?? 'Système' }}</p>
                </div>
            </div>
            
            <!-- ============ BOUTONS EXPORT FONCTIONNELS ============ -->
            <div class="export-buttons">
                <button class="export-btn pdf" onclick="exportToPDF()" id="pdfBtn">
                    <span style="position: relative; z-index: 3;">📄 Export PDF</span>
                </button>
                <button class="export-btn excel" onclick="exportToExcel()" id="excelBtn">
                    <span style="position: relative; z-index: 3;">📊 Export Excel</span>
                </button>
                <button class="export-btn print" onclick="window.print()" id="printBtn">
                    <span style="position: relative; z-index: 3;">🖨️ Imprimer</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============ CARTES STATISTIQUES ANIMÉES ============ -->
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
        
        {{-- ✅ LOGIQUE BLADE CORRECTE --}}
        @forelse($stats_collecteurs ?? [] as $stat)
            @if($loop->first)
                <div class="table-responsive">
                    <table class="collecteurs-table" id="collecteursTable">
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

<!-- ============ JAVASCRIPT POUR EXPORT RÉEL ============ -->
<script>
    // ============ FONCTIONS EXPORT RÉELLES ============
    
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }
    
    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    // ✅ EXPORT PDF QUI MARCHE VRAIMENT
    function exportToPDF() {
        showLoading();
        
        // Animation du bouton
        const btn = document.getElementById('pdfBtn');
        btn.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            btn.style.transform = 'scale(1)';
            
            // Configuration d'impression optimisée pour PDF
            const originalTitle = document.title;
            document.title = 'Rapport_Collecteurs_SICOSA_' + new Date().toISOString().split('T')[0];
            
            // Masquer les boutons d'export pour l'impression
            document.querySelector('.export-buttons').style.display = 'none';
            
            // Lancer l'impression (qui permet de sauvegarder en PDF)
            window.print();
            
            // Restaurer après impression
            setTimeout(() => {
                document.querySelector('.export-buttons').style.display = 'flex';
                document.title = originalTitle;
                hideLoading();
                
                // Notification de succès
                showNotification('✅ Export PDF prêt ! Utilisez "Enregistrer en PDF" dans la boîte de dialogue d\'impression.', 'success');
            }, 1000);
            
        }, 300);
    }

    // ✅ EXPORT EXCEL QUI MARCHE
    function exportToExcel() {
        showLoading();
        
        const btn = document.getElementById('excelBtn');
        btn.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            btn.style.transform = 'scale(1)';
            
            const table = document.getElementById('collecteursTable');
            if (table) {
                // Créer le contenu CSV
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Collecteur,Paiements,Montant Total,Moyenne\n";
                
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cols = row.querySelectorAll('td');
                    const rowData = [];
                    cols.forEach(col => {
                        // Nettoyer le texte (enlever emoji et formater)
                        let text = col.textContent.trim().replace(/[^\w\s,-]/gi, '');
                        rowData.push(text);
                    });
                    csvContent += rowData.join(',') + '\n';
                });
                
                // Créer le lien de téléchargement
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', 'Rapport_Collecteurs_SICOSA_' + new Date().toISOString().split('T')[0] + '.csv');
                document.body.appendChild(link);
                
                // Déclencher le téléchargement
                link.click();
                document.body.removeChild(link);
                
                hideLoading();
                showNotification('✅ Export Excel terminé ! Fichier téléchargé avec succès.', 'success');
            } else {
                hideLoading();
                showNotification('❌ Aucune donnée à exporter.', 'error');
            }
        }, 300);
    }

    // ✅ SYSTÈME DE NOTIFICATIONS
    function showNotification(message, type = 'info') {
        // Créer la notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        `;
        
        // Couleur selon le type
        if (type === 'success') {
            notification.style.background = 'linear-gradient(135deg, #48bb78, #38a169)';
        } else if (type === 'error') {
            notification.style.background = 'linear-gradient(135deg, #f56565, #e53e3e)';
        } else {
            notification.style.background = 'linear-gradient(135deg, #4299e1, #3182ce)';
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Suppression automatique
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 4000);
    }

    // ✅ OPTIMISATION IMPRESSION
    window.addEventListener('beforeprint', function() {
        document.body.style.background = 'white';
        showNotification('📄 Prêt pour l\'impression/PDF !', 'info');
    });

    window.addEventListener('afterprint', function() {
        document.body.style.background = '';
    });

    // ✅ ANIMATIONS AU CHARGEMENT
    document.addEventListener('DOMContentLoaded', function() {
        // Animation d'entrée des cartes
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Message de bienvenue
        setTimeout(() => {
            showNotification('🎉 Rapport collecteurs chargé avec succès !', 'success');
        }, 1000);
    });
</script>
@endsection 