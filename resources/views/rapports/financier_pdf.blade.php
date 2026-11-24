<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Financier Municipal - SICOSA</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .title {
            color: #2c3e50;
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .subtitle {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .section {
            background: white;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .section-header {
            background: #3498db;
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: bold;
        }
        
        .section-content {
            padding: 20px;
        }
        
        .zones-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .zones-table th {
            background: #ecf0f1;
            color: #2c3e50;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #bdc3c7;
        }
        
        .zones-table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .zones-table tr:hover {
            background: #f8f9fa;
        }
        
        .taux-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        
        .taux-excellent { background: #27ae60; }
        .taux-bon { background: #f39c12; }
        .taux-faible { background: #e74c3c; }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            font-size: 12px;
            color: #7f8c8d;
        }
        
        @media print {
            body { margin: 0; background: white; }
            .section { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <!-- En-tête avec logo -->
    <div class="header">
        <img src="/images/logos/snd.jpeg" alt="Logo SICOSA" class="logo">
        <div class="title">Rapport Financier Municipal</div>
        <div class="subtitle">Système d'Information Commerciale de Sandiara (SICOSA)</div>
        <div class="subtitle">{{ date('d/m/Y à H:i') }}</div>
    </div>

    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" style="color: #3498db;">12</div>
            <div class="stat-label">Unités commerciales</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number" style="color: #e74c3c;">0 FCFA</div>
            <div class="stat-label">Recettes du jour</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number" style="color: #27ae60;">285 884 FCFA</div>
            <div class="stat-label">Recettes du mois</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number" style="color: #9b59b6;">5</div>
            <div class="stat-label">Collecteurs actifs</div>
        </div>
    </div>

    <!-- Évolution des recettes -->
    <div class="section">
        <div class="section-header">
            📈 Évolution des recettes municipales - 12 derniers mois
        </div>
        <div class="section-content">
            <p style="color: #7f8c8d; margin: 0;">
                Les recettes municipales ont montré une progression constante au cours des derniers mois, 
                avec une collecte totale de <strong style="color: #27ae60;">285 884 FCFA</strong> pour le mois en cours.
            </p>
        </div>
    </div>

    <!-- Collecte par zone -->
    <div class="section">
        <div class="section-header">
            🗺️ Collecte par zone - Juin 2025
        </div>
        <div class="section-content">
            <table class="zones-table">
                <thead>
                    <tr>
                        <th>Zone fiscale</th>
                        <th>Unités payantes</th>
                        <th>Montant collecté</th>
                        <th>Taux de recouvrement</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Zone Centre-Ville</strong></td>
                        <td>3</td>
                        <td><strong style="color: #27ae60;">160 129 FCFA</strong></td>
                        <td><span class="taux-badge taux-bon">60.0%</span></td>
                    </tr>
                    <tr>
                        <td><strong>Zone RN1</strong></td>
                        <td>1</td>
                        <td><strong style="color: #f39c12;">28 900 FCFA</strong></td>
                        <td><span class="taux-badge taux-faible">33.3%</span></td>
                    </tr>
                    <tr>
                        <td><strong>Zone Marché Hebdomadaire</strong></td>
                        <td>2</td>
                        <td><strong style="color: #27ae60;">60 494 FCFA</strong></td>
                        <td><span class="taux-badge taux-excellent">100.0%</span></td>
                    </tr>
                    <tr>
                        <td><strong>Zone Périphérique Est</strong></td>
                        <td>1</td>
                        <td><strong style="color: #27ae60;">36 361 FCFA</strong></td>
                        <td><span class="taux-badge taux-excellent">100.0%</span></td>
                    </tr>
                    <tr>
                        <td><strong>Zone Périphérique Ouest</strong></td>
                        <td>0</td>
                        <td><strong style="color: #e74c3c;">0 FCFA</strong></td>
                        <td><span class="taux-badge taux-faible">0.0%</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>SICOSA</strong> - Système d'Information Commerciale de Sandiara<br>
            Rapport généré automatiquement le {{ date('d/m/Y à H:i') }}<br>
            Pour toute question, contactez l'administration municipale
        </p>
    </div>
</body>
</html> 