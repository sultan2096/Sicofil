<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facturation SaaS - SICOFIL</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e3a8a; font-size: 18px; }
        .header .sub { color: #64748b; font-size: 11px; }
        .stats { margin-bottom: 15px; display: table; width: 100%; }
        .stats-row { display: table-row; }
        .stats-cell { display: table-cell; padding: 6px 12px; background: #f8fafc; border: 1px solid #e2e8f0; width: 25%; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table.data th { background: #1e3a8a; color: white; font-size: 9px; }
        table.data tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; font-size: 8px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SICOFIL — Facturation SaaS</h1>
        <p class="sub">Liste des factures d'abonnement • Export du {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stats-row">
            <div class="stats-cell">En attente : <strong>{{ $statsFactures['en_attente'] }}</strong></div>
            <div class="stats-cell">Payées : <strong>{{ $statsFactures['payees'] }}</strong></div>
            <div class="stats-cell">En retard : <strong>{{ $statsFactures['en_retard'] }}</strong></div>
            <div class="stats-cell">Montant attendu : <strong>{{ number_format($statsFactures['montant_total_attendu'], 0, ',', ' ') }} FCFA</strong></div>
        </div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Commune</th>
                <th>Numéro</th>
                <th>Période</th>
                <th>Émission</th>
                <th>Échéance</th>
                <th class="text-right">Montant (FCFA)</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($factures as $f)
            <tr>
                <td>{{ $f->commune_nom }}</td>
                <td>{{ $f->numero_facture }}</td>
                <td>{{ ucfirst($f->periode) }}</td>
                <td>{{ \Carbon\Carbon::parse($f->date_emission)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($f->date_echeance)->format('d/m/Y') }}</td>
                <td class="text-right">{{ number_format($f->montant_total, 0, ',', ' ') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $f->statut)) }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;">Aucune facture</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Document généré par SICOFIL — Système d'Information Commercial pour la Fiscalité Locale
    </div>
</body>
</html>
