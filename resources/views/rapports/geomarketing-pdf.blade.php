<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Géomarketing - SICOSA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .stats-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            flex: 1;
            margin: 0 5px;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 24px;
            color: #007bff;
        }
        .stat-card small {
            color: #666;
            font-size: 12px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            font-size: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-warning {
            background: #ffc107;
            color: #212529;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        .badge-secondary {
            background: #6c757d;
            color: white;
        }
        .proposition-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
        }
        .proposition-header {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
        }
        .proposition-body {
            padding: 15px;
        }
        .proposition-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .proposition-item {
            margin-bottom: 8px;
        }
        .proposition-item small {
            color: #666;
            font-size: 12px;
        }
        .proposition-item strong {
            color: #007bff;
        }
        .recommendations {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
        }
        .recommendations h3 {
            color: #007bff;
            margin-top: 0;
            font-size: 16px;
        }
        .recommendations ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .recommendations li {
            margin-bottom: 5px;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport Géomarketing</h1>
        <p>Analyse des opportunités d'investissement - SICOSA</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
        @if(isset($filtres_appliques))
        <div style="margin-top: 15px; font-size: 12px; color: #666;">
            <strong>Filtres appliqués :</strong><br>
            Période : {{ $filtres_appliques['date_debut'] }} au {{ $filtres_appliques['date_fin'] }}<br>
            Zone : {{ $filtres_appliques['zone_selectionnee'] }}<br>
            Nature d'activité : {{ $filtres_appliques['nature_selectionnee'] }}<br>
            Type de commerce : {{ $filtres_appliques['type_selectionne'] }}<br>
            Seuil de score : {{ $filtres_appliques['seuil_score'] > 0 ? $filtres_appliques['seuil_score'].'+' : 'Tous' }}<br>
            Type de rapport : {{ $filtres_appliques['type_rapport'] }}
        </div>
        @endif
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>{{ $stats['total_zones'] }}</h3>
            <small>Zones analysées</small>
        </div>
        <div class="stat-card">
            <h3>{{ number_format($stats['score_moyen'], 1) }}</h3>
            <small>Score moyen</small>
        </div>
        <div class="stat-card">
            <h3>{{ $stats['total_unites'] }}</h3>
            <small>Unités commerciales</small>
        </div>
        <div class="stat-card">
            <h3>{{ count($propositions_investissement) }}</h3>
            <small>Propositions</small>
        </div>
    </div>

    <div class="section">
        <h2>Zones d'opportunités</h2>
        <table>
            <thead>
                <tr>
                    <th>Zone</th>
                    <th>Score</th>
                    <th>Unités</th>
                    <th>Recommandation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($zones_analyse as $zone)
                <tr>
                    <td><strong>{{ $zone['nom'] }}</strong></td>
                    <td>
                        @if($zone['score'] >= 75)
                            <span class="badge badge-success">{{ $zone['score'] }}/100</span>
                        @elseif($zone['score'] >= 60)
                            <span class="badge badge-warning">{{ $zone['score'] }}/100</span>
                        @elseif($zone['score'] >= 45)
                            <span class="badge badge-danger">{{ $zone['score'] }}/100</span>
                        @else
                            <span class="badge badge-secondary">{{ $zone['score'] }}/100</span>
                        @endif
                    </td>
                    <td>{{ $zone['unites_count'] }} unités</td>
                    <td><small>{{ $zone['recommandation'] }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Propositions d'investissement</h2>
        @foreach($propositions_investissement as $proposition)
        <div class="proposition-card">
            <div class="proposition-header">
                {{ $proposition['type_investissement'] }} - {{ $proposition['zone'] }}
            </div>
            <div class="proposition-body">
                <div class="proposition-grid">
                    <div class="proposition-item">
                        <small>Score:</small><br>
                        <strong class="text-success">{{ $proposition['score'] }}/100</strong>
                    </div>
                    <div class="proposition-item">
                        <small>ROI:</small><br>
                        <strong class="text-success">{{ $proposition['roi_estime'] }}%</strong>
                    </div>
                    <div class="proposition-item">
                        <small>Investissement:</small><br>
                        <strong>{{ number_format($proposition['cout_investissement']) }} FCFA</strong>
                    </div>
                    <div class="proposition-item">
                        <small>Délai:</small><br>
                        <strong>{{ $proposition['delai'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="section">
        <div class="recommendations">
            <h3>Recommandations</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <h4 style="color: #28a745; margin-top: 0;">Points forts:</h4>
                    <ul>
                        <li>Zone Centre-Ville: excellent potentiel (85/100)</li>
                        <li>Diversité des opportunités identifiées</li>
                        <li>ROI attractif sur les investissements</li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: #ffc107; margin-top: 0;">Actions prioritaires:</h4>
                    <ul>
                        <li>Concentrer les investissements sur la Zone Centre-Ville</li>
                        <li>Développer les services de proximité</li>
                        <li>Améliorer l'accessibilité des zones périphériques</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Rapport généré automatiquement par le Système d'Information Commerciale de Sandiara (SICOSA)</p>
        <p>© {{ date('Y') }} SICOSA - Tous droits réservés</p>
    </div>
</body>
</html>
