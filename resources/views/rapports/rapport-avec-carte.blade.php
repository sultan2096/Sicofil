<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport avec Carte Thématique - SICOSA</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section-title {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .map-container {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            margin: 30px 0;
            min-height: 400px;
        }
        
        .map-placeholder {
            font-size: 1.2em;
            color: #6c757d;
        }
        
        .map-instructions {
            margin-top: 20px;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }
        
        .table-container {
            overflow-x: auto;
            margin: 20px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            background: #007bff;
            color: white;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-paye { background-color: #d4edda; color: #155724; }
        .status-impaye { background-color: #f8d7da; color: #721c24; }
        .status-partiel { background-color: #fff3cd; color: #856404; }
        
        .recommendations {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .recommendations h4 {
            color: #856404;
            margin-top: 0;
        }
        
        .recommendations ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .recommendations li {
            margin-bottom: 8px;
            color: #856404;
        }
        
        .footer {
            background: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .footer p {
            margin: 5px 0;
            opacity: 0.8;
        }
        
        @media print {
            body { background: white; }
            .container { box-shadow: none; }
            .map-container { border: 1px solid #ccc; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <h1><i class="fas fa-map-marked-alt"></i> Rapport avec Carte Thématique</h1>
            <p>Système d'Information Commerciale de Sandiara</p>
            <p>Période : {{ $periode }}</p>
            @if($zone)
                <p>Zone : {{ $zone->nom }}</p>
            @endif
        </div>

        <div class="content">
            <!-- Statistiques générales -->
            <div class="section">
                <h2 class="section-title">Statistiques Générales</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($statistiques['total_unites']) }}</div>
                        <div class="stat-label">Unités commerciales</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($statistiques['total_paiements']) }}</div>
                        <div class="stat-label">Paiements validés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($statistiques['total_recettes'], 0, ',', ' ') }}</div>
                        <div class="stat-label">FCFA de recettes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($statistiques['taux_recouvrement'], 1) }}%</div>
                        <div class="stat-label">Taux de recouvrement</div>
                    </div>
                </div>
            </div>

            <!-- Carte thématique -->
            <div class="section">
                <h2 class="section-title">Carte Thématique Géospatiale</h2>
                <div class="map-container">
                    <div class="map-placeholder">
                        <i class="fas fa-map-marked-alt" style="font-size: 3em; color: #007bff; margin-bottom: 20px;"></i>
                        <h3>Carte Interactive des Unités Commerciales</h3>
                        <p>Cette carte affiche la géolocalisation de toutes les unités commerciales avec leur statut de paiement.</p>
                    </div>
                    
                    <div class="map-instructions">
                        <h4><i class="fas fa-info-circle"></i> Instructions pour la carte interactive</h4>
                        <ul style="text-align: left;">
                            <li><strong>Marqueurs verts :</strong> Unités payées (à jour)</li>
                            <li><strong>Marqueurs rouges :</strong> Unités impayées (en retard)</li>
                            <li><strong>Marqueurs jaunes :</strong> Paiements partiels</li>
                            <li><strong>Cliquez sur un marqueur :</strong> Pour voir les détails de l'unité</li>
                            <li><strong>Utilisez les filtres :</strong> Pour afficher uniquement certains statuts ou zones</li>
                        </ul>
                        <p style="margin-top: 15px; font-weight: bold;">
                            <i class="fas fa-external-link-alt"></i> 
                            Pour accéder à la carte interactive complète, visitez : 
                            <a href="{{ route('carte.thematique') }}" target="_blank">Carte Thématique Interactive</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Données de la carte -->
            <div class="section">
                <h2 class="section-title">Données Géospatiales</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Propriétaire</th>
                                <th>Commerce</th>
                                <th>Zone</th>
                                <th>Statut</th>
                                <th>Montant dû</th>
                                <th>Coordonnées</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($donnees_carte->take(20) as $unite)
                            <tr>
                                <td>
                                    <strong>{{ $unite['nom_proprietaire'] }}</strong>
                                    @if($unite['prenom_proprietaire'])
                                        <br><small>{{ $unite['prenom_proprietaire'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $unite['nom_commerce'] ?: 'Non spécifié' }}
                                    <br><small class="text-muted">{{ $unite['type_commerce'] }}</small>
                                </td>
                                <td>{{ $unite['zone']['nom'] ?? 'Non assignée' }}</td>
                                <td>
                                    <span class="status-badge status-{{ $unite['statut_paiement'] }}">
                                        @switch($unite['statut_paiement'])
                                            @case('paye')
                                                Payé
                                                @break
                                            @case('impaye')
                                                Impayé
                                                @break
                                            @case('partiel')
                                                Partiel
                                                @break
                                            @default
                                                {{ ucfirst($unite['statut_paiement']) }}
                                        @endswitch
                                    </span>
                                </td>
                                <td>{{ number_format($unite['montant_du'], 0, ',', ' ') }} FCFA</td>
                                <td>
                                    <small>
                                        {{ number_format($unite['coordinates']['lat'], 6) }}, 
                                        {{ number_format($unite['coordinates']['lng'], 6) }}
                                    </small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($donnees_carte->count() > 20)
                        <p style="text-align: center; margin-top: 15px; color: #6c757d;">
                            <i class="fas fa-info-circle"></i> 
                            Affichage des 20 premières unités. 
                            Total : {{ $donnees_carte->count() }} unités commerciales.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Recettes par zone -->
            <div class="section">
                <h2 class="section-title">Recettes par Zone</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Zone</th>
                                <th>Nombre d'unités</th>
                                <th>Paiements</th>
                                <th>Montant total</th>
                                <th>Montant moyen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recettes_par_zone as $zone)
                            <tr>
                                <td><strong>{{ $zone->zone }}</strong></td>
                                <td>{{ number_format($zone->nombre_unites) }}</td>
                                <td>{{ number_format($zone->nombre_paiements) }}</td>
                                <td>{{ number_format($zone->total, 0, ',', ' ') }} FCFA</td>
                                <td>{{ number_format($zone->montant_moyen, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recommandations -->
            @if(!empty($recommandations))
            <div class="section">
                <h2 class="section-title">Recommandations</h2>
                <div class="recommendations">
                    <h4><i class="fas fa-lightbulb"></i> Actions recommandées</h4>
                    <ul>
                        @foreach($recommandations as $recommandation)
                            <li>{{ $recommandation }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <p><strong>Rapport généré le {{ now()->format('d/m/Y à H:i') }}</strong></p>
            <p>Par {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>
            <p>Système d'Information Commerciale de Sandiara - SICOSA</p>
        </div>
    </div>
</body>
</html> 