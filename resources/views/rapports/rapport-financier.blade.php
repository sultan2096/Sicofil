<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titre }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin: 5px 0;
        }
        
        .periode-info {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .periode-info h2 {
            color: #2c3e50;
            font-size: 16px;
            margin: 0 0 5px 0;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background: #3498db;
            color: white;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            border-radius: 3px;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-cell {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px 10px;
            border: 1px solid #bdc3c7;
            background: #f8f9fa;
        }
        
        .stats-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            display: block;
        }
        
        .stats-label {
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th {
            background: #34495e;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        
        .table td {
            padding: 8px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 10px;
        }
        
        .table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-success {
            background: #27ae60;
            color: white;
        }
        
        .badge-warning {
            background: #f39c12;
            color: white;
        }
        
        .badge-danger {
            background: #e74c3c;
            color: white;
        }
        
        .progress-bar {
            background: #ecf0f1;
            height: 15px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            background: #3498db;
            height: 100%;
            border-radius: 8px;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .two-columns {
            display: table;
            width: 100%;
        }
        
        .column {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }
        
        .highlight-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .chart-placeholder {
            background: #f8f9fa;
            border: 2px dashed #bdc3c7;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7f8c8d;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <!-- En-tête du rapport -->
    <div class="header">
        <h1>{{ $titre }}</h1>
        <div class="subtitle">Système d'Information Commerciale de Sandiara</div>
        <div class="subtitle">Commune de Sandiara - République du Sénégal</div>
    </div>

    <!-- Informations sur la période -->
    <div class="periode-info">
        <h2>{{ $periode['libelle'] }}</h2>
        <div>Période analysée : {{ $periode['debut'] }} au {{ $periode['fin'] }}</div>
        @if($zone_selectionnee)
            <div style="margin-top: 5px;">
                <strong>Zone fiscale :</strong> {{ $zone_selectionnee->nom }}
            </div>
        @endif
        <div style="margin-top: 10px; font-size: 10px;">
            Rapport généré le {{ $date_generation }}
        </div>
    </div>

    <!-- Section 1: Résumé des recettes par période -->
    <div class="section">
        <div class="section-title">1. RÉSUMÉ DES RECETTES PAR PÉRIODE</div>
        
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <span class="stats-value">{{ number_format($recettes_periode['total'], 0, ',', ' ') }} FCFA</span>
                    <div class="stats-label">Recettes totales</div>
                </div>
                <div class="stats-cell">
                    <span class="stats-value">{{ number_format($recettes_periode['nombre_paiements']) }}</span>
                    <div class="stats-label">Nombre de paiements</div>
                </div>
                <div class="stats-cell">
                    <span class="stats-value">{{ number_format($recettes_periode['montant_moyen'], 0, ',', ' ') }} FCFA</span>
                    <div class="stats-label">Montant moyen</div>
                </div>
                <div class="stats-cell">
                    <span class="stats-value">{{ $recettes_periode['par_taxe']->count() }}</span>
                    <div class="stats-label">Types de taxes</div>
                </div>
            </div>
        </div>

        <h3 style="color: #2c3e50; font-size: 12px; margin-bottom: 10px;">Répartition par type de taxe</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Type de taxe</th>
                    <th>Taux</th>
                    <th class="text-right">Montant collecté</th>
                    <th class="text-right">% du total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recettes_periode['par_taxe'] as $taxe)
                    <tr>
                        <td>{{ $taxe->nom }}</td>
                        <td>{{ $taxe->taux }}%</td>
                        <td class="text-right">{{ number_format($taxe->total, 0, ',', ' ') }} FCFA</td>
                        <td class="text-right">
                            {{ $recettes_periode['total'] > 0 ? number_format(($taxe->total / $recettes_periode['total']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Section 2: Répartition par zone fiscale -->
    <div class="section">
        <div class="section-title">2. RÉPARTITION PAR ZONE FISCALE</div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Zone fiscale</th>
                    <th class="text-right">Montant collecté</th>
                    <th class="text-right">Nb paiements</th>
                    <th class="text-right">% du total</th>
                    <th class="text-center">Performance</th>
                </tr>
            </thead>
            <tbody>
                @php $totalZones = $recettes_zones->sum('paiements_sum_montant') @endphp
                @foreach($recettes_zones as $zone)
                    <tr>
                        <td>{{ $zone->nom }}</td>
                        <td class="text-right">{{ number_format($zone->paiements_sum_montant ?? 0, 0, ',', ' ') }} FCFA</td>
                        <td class="text-right">{{ $zone->paiements_count ?? 0 }}</td>
                        <td class="text-right">
                            {{ $totalZones > 0 ? number_format((($zone->paiements_sum_montant ?? 0) / $totalZones) * 100, 1) : 0 }}%
                        </td>
                        <td class="text-center">
                            @php 
                                $pourcentage = $totalZones > 0 ? (($zone->paiements_sum_montant ?? 0) / $totalZones) * 100 : 0;
                            @endphp
                            @if($pourcentage >= 30)
                                <span class="badge badge-success">Excellent</span>
                            @elseif($pourcentage >= 15)
                                <span class="badge badge-warning">Bon</span>
                            @else
                                <span class="badge badge-danger">À améliorer</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Saut de page -->
    <div class="page-break"></div>

    <!-- Section 3: Performance des collecteurs -->
    <div class="section">
        <div class="section-title">3. PERFORMANCE DES COLLECTEURS</div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Collecteur</th>
                    <th>Zone assignée</th>
                    <th class="text-right">Montant collecté</th>
                    <th class="text-right">Nb paiements</th>
                    <th class="text-right">Moyenne par paiement</th>
                    <th class="text-center">Classement</th>
                </tr>
            </thead>
            <tbody>
                @foreach($performance_collecteurs as $index => $collecteur)
                    <tr>
                        <td>{{ $collecteur->name }}</td>
                        <td>{{ $collecteur->zoneFiscale->nom ?? 'Non assignée' }}</td>
                        <td class="text-right">{{ number_format($collecteur->paiements_collectes_sum_montant ?? 0, 0, ',', ' ') }} FCFA</td>
                        <td class="text-right">{{ $collecteur->paiements_collectes_count ?? 0 }}</td>
                        <td class="text-right">
                            {{ ($collecteur->paiements_collectes_count ?? 0) > 0 ? number_format(($collecteur->paiements_collectes_sum_montant ?? 0) / $collecteur->paiements_collectes_count, 0, ',', ' ') : 0 }} FCFA
                        </td>
                        <td class="text-center">
                            @if($index == 0)
                                <span class="badge badge-success">1er</span>
                            @elseif($index == 1)
                                <span class="badge badge-warning">2ème</span>
                            @elseif($index == 2)
                                <span class="badge badge-warning">3ème</span>
                            @else
                                {{ $index + 1 }}ème
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Section 4: Statistiques détaillées -->
    <div class="section">
        <div class="section-title">4. STATISTIQUES DÉTAILLÉES ET ANALYSES</div>
        
        <div class="two-columns">
            <div class="column">
                <h3 style="color: #2c3e50; font-size: 12px; margin-bottom: 15px;">Indicateurs de performance</h3>
                
                <div class="highlight-box">
                    <strong>Taux de réussite des paiements</strong><br>
                    <div style="margin: 10px 0;">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $statistiques['taux_reussite'] }}%;"></div>
                        </div>
                    </div>
                    {{ number_format($statistiques['taux_reussite'], 1) }}% 
                    ({{ $statistiques['paiements_valides'] }}/{{ $statistiques['total_paiements'] }})
                </div>
                
                <div class="highlight-box">
                    <strong>Taux de participation des unités</strong><br>
                    <div style="margin: 10px 0;">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $statistiques['taux_participation'] }}%;"></div>
                        </div>
                    </div>
                    {{ number_format($statistiques['taux_participation'], 1) }}% 
                    ({{ $statistiques['unites_actives'] }}/{{ $statistiques['total_unites'] }})
                </div>
            </div>
            
            <div class="column">
                <h3 style="color: #2c3e50; font-size: 12px; margin-bottom: 15px;">Répartition des paiements</h3>
                
                <table class="table">
                    <tr>
                        <td><strong>Paiements validés</strong></td>
                        <td class="text-right">{{ $statistiques['paiements_valides'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Paiements annulés</strong></td>
                        <td class="text-right">{{ $statistiques['paiements_annules'] }}</td>
                    </tr>
                    <tr style="background: #ecf0f1;">
                        <td><strong>Total des paiements</strong></td>
                        <td class="text-right"><strong>{{ $statistiques['total_paiements'] }}</strong></td>
                    </tr>
                </table>
                
                <h3 style="color: #2c3e50; font-size: 12px; margin: 20px 0 15px 0;">Unités commerciales</h3>
                
                <table class="table">
                    <tr>
                        <td><strong>Unités actives</strong></td>
                        <td class="text-right">{{ $statistiques['unites_actives'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total des unités</strong></td>
                        <td class="text-right">{{ $statistiques['total_unites'] }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 5: Évolution des recettes -->
    <div class="section">
        <div class="section-title">5. ÉVOLUTION DES RECETTES</div>
        
        @if($evolution_recettes->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Période</th>
                        <th class="text-right">Montant collecté</th>
                        <th class="text-right">Nombre de paiements</th>
                        <th class="text-right">Moyenne par paiement</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evolution_recettes as $periode)
                        <tr>
                            <td>{{ $periode->periode }}</td>
                            <td class="text-right">{{ number_format($periode->total, 0, ',', ' ') }} FCFA</td>
                            <td class="text-right">{{ $periode->nombre }}</td>
                            <td class="text-right">
                                {{ $periode->nombre > 0 ? number_format($periode->total / $periode->nombre, 0, ',', ' ') : 0 }} FCFA
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="chart-placeholder">
                Aucune donnée disponible pour la période sélectionnée
            </div>
        @endif
    </div>

    <!-- Recommandations -->
    <div class="section">
        <div class="section-title">6. RECOMMANDATIONS</div>
        
        <div style="font-size: 11px; line-height: 1.6;">
            <h4 style="color: #2c3e50; font-size: 12px;">Analyse des performances :</h4>
            <ul>
                @if($statistiques['taux_reussite'] < 90)
                    <li><strong>Améliorer le taux de réussite :</strong> Le taux de réussite actuel ({{ number_format($statistiques['taux_reussite'], 1) }}%) peut être amélioré en renforçant la formation des collecteurs.</li>
                @endif
                
                @if($statistiques['taux_participation'] < 70)
                    <li><strong>Augmenter la participation :</strong> Seulement {{ number_format($statistiques['taux_participation'], 1) }}% des unités commerciales participent activement. Une campagne de sensibilisation est recommandée.</li>
                @endif
                
                @if($recettes_zones->where('paiements_sum_montant', 0)->count() > 0)
                    <li><strong>Zones à faible performance :</strong> Certaines zones fiscales montrent des performances faibles. Un accompagnement spécifique est nécessaire.</li>
                @endif
                
                <li><strong>Collecteurs performants :</strong> Capitaliser sur les bonnes pratiques des collecteurs les plus performants pour former les autres.</li>
                
                <li><strong>Suivi régulier :</strong> Mettre en place un système de suivi hebdomadaire pour identifier rapidement les problèmes.</li>
            </ul>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div>
            SICOSA - Système d'Information Commerciale de Sandiara | 
            Commune de Sandiara, République du Sénégal | 
            Rapport généré le {{ $date_generation }}
        </div>
    </div>
</body>
</html> 