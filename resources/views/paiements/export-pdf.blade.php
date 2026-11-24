<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export des Paiements</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        .header { margin-bottom: 20px; }
        .header p { margin: 2px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Liste des Paiements</h2>
        <p><strong>Commune :</strong> {{ $commune->nom ?? 'Non définie' }}</p>
        <p><strong>Date d'export :</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Unité</th>
                <th>Propriétaire</th>
                <th>Montant payé</th>
                <th>Montant avance</th>
                <th>Montant restant</th>
                <th>Mode</th>
                <th>Collecteur</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalMontant = 0;
                $totalAvance = 0;
                $totalRestant = 0;
            @endphp
            @forelse($paiements as $p)
            @php
                $totalMontant += $p->montant;
                $totalAvance += $p->montant_avance ?? 0;
                $totalRestant += $p->montant_restant ?? 0;
            @endphp
            <tr>
                <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                <td>{{ $p->unite->nature ?? '' }}</td>
                <td>{{ $p->unite->nom_complet_proprietaire ?? '' }}</td>
                <td>{{ number_format($p->montant, 0, ',', ' ') }} FCFA</td>
                <td>{{ number_format($p->montant_avance ?? 0, 0, ',', ' ') }} FCFA</td>
                <td>{{ number_format($p->montant_restant ?? 0, 0, ',', ' ') }} FCFA</td>
                <td>{{ $p->mode_paiement }}</td>
                <td>{{ $p->collecteur->name ?? '' }}</td>
                <td>{{ $p->statut }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;">Aucun paiement trouvé</td>
            </tr>
            @endforelse
            @if($paiements->count() > 0)
            <tr style="font-weight:bold; background:#e0e0e0;">
                <td colspan="3" style="text-align:right;">TOTAL BILAN</td>
                <td>{{ number_format($totalMontant, 0, ',', ' ') }} FCFA</td>
                <td>{{ number_format($totalAvance, 0, ',', ' ') }} FCFA</td>
                <td>{{ number_format($totalRestant, 0, ',', ' ') }} FCFA</td>
                <td colspan="3"></td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
