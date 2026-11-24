<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export des Unités Commerciales</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h2 { text-align: center; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; vertical-align: middle; }
        th { background: #f0f0f0; font-size: 10px; }
        .header { margin-bottom: 15px; }
        .header p { margin: 2px 0; }
        .badge-actif { color: #155724; font-weight: bold; }
        .badge-inactif { color: #721c24; font-weight: bold; }
        .small { font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Liste des Unités Commerciales</h2>
        <p><strong>Commune :</strong> {{ $commune->nom ?? 'Non définie' }}</p>
        <p><strong>Date d'export :</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nom complet</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Type commerce</th>
                <th>Nature</th>
                <th>Zone</th>
                <th>Taxe</th>
                <th>NINEA/Matricule</th>
                <th>Statut</th>
                <th>Date recensement</th>
                <th>Collecteur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($unites as $index => $u)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $u->nom_complet_proprietaire ?? '-' }}</td>
                <td>{{ $u->telephone ?? '-' }}</td>
                <td>{{ $u->email ?? '-' }}</td>
                <td>{{ $u->type_commerce ?? '-' }}</td>
                <td>{{ $u->nature ?? '-' }}</td>
                <td>{{ $u->zone->nom ?? '-' }}</td>
                <td>
                    @if($u->taxe)
                        {{ $u->taxe->nom }}<br>
                        <span class="small">{{ number_format($u->taxe->montant, 0, ',', ' ') }} FCFA</span>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $u->ninea ?? '-' }}</td>
                <td class="{{ $u->actif ? 'badge-actif' : 'badge-inactif' }}">
                    {{ $u->actif ? 'Actif' : 'Inactif' }}
                </td>
                <td>{{ $u->date_recensement ? $u->date_recensement->format('d/m/Y') : '-' }}</td>
                <td>{{ $u->collecteur->name ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" style="text-align:center;">Aucune unité commerciale trouvée</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
