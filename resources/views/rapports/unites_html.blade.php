<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Unités Commerciales - SICOSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .rapport-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-rapport {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 25px;
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
            padding: 15px 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            margin-bottom: 20px;
        }

        .header-info p {
            margin: 5px 0;
            color: #2d3748;
            font-size: 14px;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
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
            color: white;
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

        .export-btn.back {
            background: linear-gradient(135deg, #6c757d, #495057);
        }

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

        .section-title i {
            color: #667eea;
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

        .nom-commercial i {
            color: #667eea;
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

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
            color: #cbd5e0;
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

        .footer-rapport strong {
            color: #667eea;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .logo-section {
                flex-direction: column;
                text-align: center;
            }

            .export-buttons {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .header-rapport {
                padding: 20px;
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

            .table-section {
                padding: 20px;
            }
        }

        /* Print Styles */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            
            .rapport-container {
                max-width: none !important;
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
</head>
<body>
    <div class="rapport-container">
        <!-- Header avec Logo SICOSA -->
        <div class="header-rapport">
            <div class="logo-section">
                <img src="{{ asset('images/logos/snd.jpeg') }}" alt="Logo SICOSA" class="logo-sicosa">
                <div class="header-text">
                    <h1>RAPPORT UNITÉS COMMERCIALES</h1>
                    <h2>SYSTÈME D'INFORMATION COMMERCIALE DE SANDIARA</h2>
                </div>
            </div>
            
            <div class="header-info">
                <p><strong><i class="fas fa-map-marker-alt"></i> Zone :</strong> {{ $stats['zone'] ?? 'Toutes les zones' }}</p>
                <p><strong><i class="fas fa-calendar-alt"></i> Période :</strong> {{ $stats['periode'] ?? 'Non définie' }}</p>
                @if(isset($stats['montant_total_collecte']))
                    <p><strong><i class="fas fa-coins"></i> Montant total collecté :</strong> {{ number_format($stats['montant_total_collecte'], 0, ',', ' ') }} FCFA</p>
                @endif
                <p><strong><i class="fas fa-clock"></i> Généré le :</strong> {{ now()->format('d/m/Y à H:i') }} par {{ auth()->user()->name ?? 'Système' }}</p>
            </div>
            
            <!-- Boutons d'export -->
            <div class="export-buttons">
                <button class="export-btn pdf" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button class="export-btn excel" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <button class="export-btn print" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimer
                </button>
                <a href="{{ route('rapports.index') }}" class="export-btn back">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
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
            <h2 class="section-title">
                <i class="fas fa-store"></i>
                Liste des Unités Commerciales
            </h2>
            
            @forelse($unites ?? [] as $unite)
                @if($loop->first)
                    <div class="table-responsive">
                        <table class="unites-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-store"></i> Nom Commercial</th>
                                    <th><i class="fas fa-user"></i> Propriétaire</th>
                                    <th><i class="fas fa-map-marker-alt"></i> Zone</th>
                                    <th><i class="fas fa-briefcase"></i> Activité</th>
                                    <th><i class="fas fa-toggle-on"></i> Statut</th>
                                    <th><i class="fas fa-phone"></i> Téléphone</th>
                                </tr>
                            </thead>
                            <tbody>
                @endif
                
                <tr>
                    <td>
                        <div class="nom-commercial">
                            <i class="fas fa-store"></i>
                            {{ $unite->nom_commercial ?? 'N/A' }}
                        </div>
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
                            <span class="statut-badge actif">
                                <i class="fas fa-check-circle"></i> Actif
                            </span>
                        @else
                            <span class="statut-badge inactif">
                                <i class="fas fa-times-circle"></i> Inactif
                            </span>
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
                    <i class="fas fa-store"></i>
                    <h3>Aucune unité commerciale trouvée</h3>
                    <p>Il n'y a actuellement aucune unité commerciale enregistrée dans cette zone.</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="footer-rapport">
            <p>
                <strong>SICOSA</strong> - Système d'Information Commerciale de Sandiara
            </p>
            <p>
                Rapport généré automatiquement le {{ now()->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToPDF() {
            // Masquer les boutons d'export avant l'impression
            const exportButtons = document.querySelector('.export-buttons');
            exportButtons.style.display = 'none';
            
            // Imprimer
            window.print();
            
            // Réafficher les boutons après l'impression
            setTimeout(() => {
                exportButtons.style.display = 'flex';
            }, 1000);
        }

        function exportToExcel() {
            // Récupérer les données du tableau
            const table = document.querySelector('.unites-table');
            if (!table) {
                alert('Aucune donnée à exporter');
                return;
            }

            let csv = '';
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('th, td');
                const rowData = [];
                cols.forEach(col => {
                    // Nettoyer le texte (enlever les icônes et espaces supplémentaires)
                    let text = col.textContent.trim();
                    text = text.replace(/[\n\r]+/g, ' ');
                    rowData.push('"' + text + '"');
                });
                csv += rowData.join(',') + '\n';
            });

            // Créer et télécharger le fichier
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'rapport_unites_commerciales_' + new Date().toISOString().slice(0,10) + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Auto-focus pour impression si demandé via URL
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === '1') {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        };
    </script>
</body>
</html> 
