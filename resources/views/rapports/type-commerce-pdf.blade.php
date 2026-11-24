<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport par Type de Commerce</title>
    <style>
        body { margin: 0; padding: 20px; font-family: Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
        .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 20px; }
        .header img { max-height: 60px; margin-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #0d6efd; }
        .header p { margin: 5px 0 0; font-size: 11px; color: #666; }
        .info-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .info-box h3 { margin: 0 0 10px; font-size: 14px; color: #0d6efd; text-align: center; }
        .info-row { display: flex; justify-content: space-between; font-size: 11px; }
        .info-row p { margin: 3px 0; }
        .stats-grid { display: flex; gap: 10px; margin-bottom: 20px; }
        .stat-card { flex: 1; background: #0d6efd; color: white; padding: 12px; text-align: center; border-radius: 4px; }
        .stat-card.success { background: #198754; }
        .stat-card.warning { background: #ffc107; color: #333; }
        .stat-card.info { background: #17a2b8; }
        .stat-card h4 { margin: 5px 0; font-size: 16px; }
        .stat-card small { font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        th, td { padding: 8px; border: 1px solid #dee2e6; text-align: left; }
        th { background: #212529; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-primary { color: #0d6efd; }
        .text-success { color: #198754; }
        .badge { display: inline-block; padding: 2px 6px; font-size: 10px; border-radius: 3px; background: #6c757d; color: white; }
        .badge.success { background: #198754; }
        .badge.info { background: #17a2b8; }
        .badge.warning { background: #ffc107; color: #333; }
        .badge.primary { background: #0d6efd; }
        tfoot th { background: #e9ecef; font-weight: bold; }
        .progress { height: 16px; background: #e9ecef; border-radius: 3px; overflow: hidden; }
        .progress-bar { height: 100%; background: #17a2b8; color: white; font-size: 9px; display: flex; align-items: center; justify-content: center; }
        .analysis { display: flex; gap: 15px; margin-top: 15px; }
        .analysis-box { flex: 1; background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; border-radius: 4px; }
        .analysis-box h6 { margin: 0 0 8px; font-size: 11px; border-bottom: 1px solid #dee2e6; padding-bottom: 5px; }
        .top-item { margin-bottom: 8px; font-size: 11px; }
        .footer-note { text-align: center; margin-top: 20px; font-size: 10px; color: #666; border-top: 1px solid #dee2e6; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('images/logos/snd.jpeg') }}" alt="Logo SICOFIL">
        <h1>Rapport par Type de Commerce</h1>
        <p>Système d'Information Commerciale de Sandiara - République du Sénégal</p>
    </div>

    <div class="info-box">
        <h3>{{ $stats['periode'] }}</h3>
        <div class="info-row">
            <p><strong>Zone :</strong> {{ $stats['zone'] }}</p>
            <p><strong>Type filtré :</strong> {{ $stats['type_filtre'] }}</p>
            <p><strong>Catégorie :</strong> {{ $stats['categorie_filtree'] }}</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h4>{{ number_format($stats['total_unites']) }}</h4>
            <small>Unités commerciales</small>
        </div>
        <div class="stat-card success">
            <h4>{{ number_format($stats['total_paiements']) }}</h4>
            <small>Paiements validés</small>
        </div>
        <div class="stat-card warning">
            <h4>{{ number_format($stats['total_recettes'], 0, ',', ' ') }}</h4>
            <small>FCFA de recettes</small>
        </div>
        <div class="stat-card info">
            <h4>{{ $stats['nombre_types'] }}</h4>
            <small>Types de commerce</small>
        </div>
    </div>

    @if($donnees->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Type de Commerce</th>
                    <th class="text-center">Catégorie</th>
                    <th class="text-center">Nb. Unités</th>
                    <th class="text-center">Nb. Paiements</th>
                    <th class="text-end">Recettes Totales</th>
                    <th class="text-end">Montant Moyen</th>
                    <th class="text-center">% du Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($donnees as $index => $type)
                    @php
                        $pourcentage = $stats['total_recettes'] > 0 ? round(($type->total_recettes / $stats['total_recettes']) * 100, 2) : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong class="text-primary">{{ $type->type_commerce ?: 'Non spécifié' }}</strong>
                            @if($index < 3)
                                <span class="badge warning">Top {{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($type->categorie === 'activite_commerciale')
                                <span class="badge success">Commercial</span>
                            @elseif($type->categorie === 'point_interet')
                                <span class="badge info">Intérêt</span>
                            @else
                                <span class="badge">Non défini</span>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($type->nombre_unites) }}</td>
                        <td class="text-center">{{ number_format($type->nombre_paiements) }}</td>
                        <td class="text-end"><strong class="text-success">{{ number_format($type->total_recettes, 0, ',', ' ') }} FCFA</strong></td>
                        <td class="text-end">{{ number_format($type->montant_moyen, 0, ',', ' ') }} FCFA</td>
                        <td class="text-center">
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $pourcentage }}%">
                                    {{ $pourcentage }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">TOTAL</th>
                    <th class="text-center">{{ number_format($stats['total_unites']) }}</th>
                    <th class="text-center">{{ number_format($stats['total_paiements']) }}</th>
                    <th class="text-end">{{ number_format($stats['total_recettes'], 0, ',', ' ') }} FCFA</th>
                    <th class="text-end">
                        @if($stats['total_paiements'] > 0)
                            {{ number_format($stats['total_recettes'] / $stats['total_paiements'], 0, ',', ' ') }} FCFA
                        @else
                            0 FCFA
                        @endif
                    </th>
                    <th class="text-center">100%</th>
                </tr>
            </tfoot>
        </table>

        @if($donnees->where('categorie', '!=', null)->count() > 0)
            <div class="analysis">
                <div class="analysis-box">
                    <h6>Répartition par Catégorie</h6>
                    @php
                        $commercial = $donnees->where('categorie', 'activite_commerciale');
                        $interet = $donnees->where('categorie', 'point_interet');
                        $totalCommercial = $commercial->sum('total_recettes');
                        $totalInteret = $interet->sum('total_recettes');
                    @endphp
                    <div><strong>Activités commerciales :</strong> {{ number_format($totalCommercial, 0, ',', ' ') }} FCFA <small>({{ $commercial->sum('nombre_unites') }} unités)</small></div>
                    <div><strong>Points d'intérêt :</strong> {{ number_format($totalInteret, 0, ',', ' ') }} FCFA <small>({{ $interet->sum('nombre_unites') }} unités)</small></div>
                </div>
                <div class="analysis-box">
                    <h6>Top Performers</h6>
                    @foreach($donnees->take(3) as $index => $top)
                        <div class="top-item">
                            <span class="badge warning">{{ $index + 1 }}</span>
                            <strong>{{ $top->type_commerce }}</strong><br>
                            <small>{{ number_format($top->total_recettes, 0, ',', ' ') }} FCFA ({{ $top->nombre_paiements }} paiements)</small>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @else
        <div style="text-align: center; padding: 30px;">
            <h5 style="color: #666;">Aucune donnée trouvée</h5>
            <p style="color: #999;">Aucun paiement n'a été trouvé pour les critères sélectionnés sur la période {{ $stats['periode'] }}.</p>
        </div>
    @endif

    <div class="footer-note">
        Rapport généré le {{ now()->format('d/m/Y à H:i') }} | SICOFIL - Système d'Information Commerciale de Sandiara
    </div>

</body>
</html>
