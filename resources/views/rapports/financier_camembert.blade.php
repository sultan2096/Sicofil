<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Financier SICOSA - Format PDF</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2c5282;
        }

        .logo-container {
            margin-right: 20px;
        }

        .logo-sicosa {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #2c5282;
        }

        .header-text {
            flex: 1;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }

        .year {
            font-size: 16px;
            color: #2c5282;
            font-weight: bold;
        }

        /* Sections */
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .section-header {
            background: #2c5282;
            color: white;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .section-header.depenses {
            background: #2c5282;
        }

        .section-header.recettes {
            background: #e53e3e;
        }

        /* Layout grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-section {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .chart-container {
            width: 200px;
            height: 200px;
            position: relative;
        }

        .chart-details {
            flex: 1;
        }

        .total-amount {
            font-size: 14px;
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 10px;
            text-align: center;
        }

        /* Table styles */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .details-table th {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            color: #2d3748;
        }

        .details-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: left;
        }

        .details-table .color-indicator {
            width: 15px;
            height: 15px;
            border-radius: 2px;
            display: inline-block;
            margin-right: 8px;
            vertical-align: middle;
        }

        .details-table .amount {
            text-align: right;
            font-weight: bold;
        }

        /* Footer summary */
        .summary {
            background: #f7fafc;
            padding: 15px;
            border: 2px solid #e2e8f0;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-item {
            text-align: center;
        }

        .summary-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #2c5282;
        }

        /* Print optimization */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .chart-container {
                width: 180px;
                height: 180px;
            }
        }

        /* Logo colors for charts */
        .depenses-colors {
            --color1: #4a90e2;
            --color2: #50c878;
            --color3: #ff6b6b;
            --color4: #ffa726;
            --color5: #ab47bc;
            --color6: #26a69a;
        }

        .recettes-colors {
            --color1: #e53e3e;
            --color2: #38a169;
            --color3: #3182ce;
            --color4: #d69e2e;
            --color5: #805ad5;
        }
    </style>
</head>
<body>
    <!-- Header avec logo SICOSA -->
    <div class="header">
        <div class="logo-container">
            <img src="{{ asset('images/logos/snd.jpeg') }}" alt="Logo SICOSA" class="logo-sicosa">
        </div>
        <div class="header-text">
            <h1 class="title">Rapport financier</h1>
            <p class="subtitle">SYSTÈME D'INFORMATION COMMERCIALE DE SANDIARA</p>
            <p class="year">{{ now()->year }}</p>
        </div>
    </div>

    <!-- Section Dépenses -->
    <div class="section">
        <div class="section-header depenses">Dépenses</div>
        
        <div class="chart-section">
            <div class="chart-container">
                <canvas id="depensesChart"></canvas>
            </div>
            
            <div class="chart-details">
                <div class="total-amount">Total des dépenses = réalisé : {{ number_format($stats['total_depenses'] ?? 3194070, 0, ' ', ' ') }} FCFA</div>
                
                <table class="details-table">
                    <thead>
                        <tr>
                            <th style="width: 15px;"></th>
                            <th>Activité</th>
                            <th>Réalisé</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($depenses as $depense)
                        <tr>
                            <td><span class="color-indicator" style="background: {{ $depense['couleur'] }};"></span></td>
                            <td>{{ $depense['activite'] }}</td>
                            <td class="amount">{{ number_format($depense['montant'], 0, ' ', ' ') }} FCFA</td>
                            <td>{{ $depense['pourcentage'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section Recettes -->
    <div class="section">
        <div class="section-header recettes">Recettes</div>
        
        <div class="chart-section">
            <div class="chart-container">
                <canvas id="recettesChart"></canvas>
            </div>
            
            <div class="chart-details">
                <div class="total-amount">Total des recettes : {{ number_format($stats['total_recettes'] ?? 2614854, 0, ' ', ' ') }} FCFA</div>
                
                <table class="details-table">
                    <thead>
                        <tr>
                            <th style="width: 15px;"></th>
                            <th>Activité</th>
                            <th>Réalisé</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recettes as $recette)
                        <tr>
                            <td><span class="color-indicator" style="background: {{ $recette['couleur'] }};"></span></td>
                            <td>{{ $recette['activite'] }}</td>
                            <td class="amount">{{ number_format($recette['montant'], 0, ' ', ' ') }} FCFA</td>
                            <td>{{ $recette['pourcentage'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Résumé final -->
    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Le SICOSA :</div>
            <div class="summary-value">{{ number_format(1465323, 0, ' ', ' ') }} FCFA</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">La Fondation :</div>
            <div class="summary-value">{{ number_format(1179659, 0, ' ', ' ') }} FCFA</div>
        </div>
    </div>

    <script>
        // Configuration des couleurs
        const depensesColors = ['#4a90e2', '#50c878', '#ff6b6b', '#ffa726', '#ab47bc', '#26a69a', '#78909c'];
        const recettesColors = ['#e53e3e', '#38a169', '#3182ce', '#d69e2e', '#805ad5', '#319795'];

        // Graphique des dépenses
        const depensesCtx = document.getElementById('depensesChart').getContext('2d');
        new Chart(depensesCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Collecte des taxes',
                    'Autres subventions', 
                    'Autres charges externes',
                    'Charges de personnel',
                    'Autres charges',
                    'Charges financières',
                    'Dotations aux amortissements'
                ],
                datasets: [{
                    data: [28, 21, 21, 17, 5, 6, 3],
                    backgroundColor: depensesColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '30%'
            }
        });

        // Graphique des recettes
        const recettesCtx = document.getElementById('recettesChart').getContext('2d');
        new Chart(recettesCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Ventes',
                    'Conseil départemental',
                    'CAF', 
                    'État',
                    'Conseil régional',
                    'Autres prestations'
                ],
                datasets: [{
                    data: [25, 45, 8, 5, 5, 13],
                    backgroundColor: recettesColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '30%'
            }
        });

        // Fonction pour l'impression
        window.print = function() {
            window.print();
        };
    </script>
</body>
</html> 