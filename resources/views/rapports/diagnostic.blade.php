<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔧 Diagnostic SICOSA</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; background: #d4edda; padding: 10px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; margin: 10px 0; }
        .info { color: blue; background: #d1ecf1; padding: 10px; margin: 10px 0; }
        .logo-test { text-align: center; margin: 20px 0; }
        .logo-test img { border: 3px solid green; border-radius: 10px; }
    </style>
</head>
<body>
    <h1>🔧 Diagnostic SICOSA - Test Complet</h1>
    
    <div class="info">
        <h3>📋 Informations système :</h3>
        <ul>
            <li><strong>Date/Heure :</strong> {{ now()->format('d/m/Y H:i:s') }}</li>
            <li><strong>Utilisateur connecté :</strong> {{ auth()->user()->name ?? 'Non connecté' }}</li>
            <li><strong>Rôle :</strong> {{ auth()->user()->role ?? 'Aucun' }}</li>
            <li><strong>URL actuelle :</strong> {{ url()->current() }}</li>
        </ul>
    </div>

    <div class="success">
        <h3>✅ Test du rendu Blade :</h3>
        <p>Si vous voyez cette date formatée : <strong>{{ now()->format('d/m/Y') }}</strong>, alors Blade fonctionne !</p>
        <p>Variable de test : <strong>{{ 'SICOSA fonctionne parfaitement !' }}</strong></p>
    </div>

    <div class="logo-test">
        <h3>🏢 Test du logo SICOSA :</h3>
        <img src="{{ asset('images/logos/snd.jpeg') }}" alt="Logo SICOSA" style="width: 150px; height: 150px;">
        <p>Si le logo s'affiche ci-dessus, alors l'image est accessible !</p>
    </div>

    <div class="info">
        <h3>🎯 Test des statistiques :</h3>
        @if(isset($stats))
            <ul>
                <li><strong>Période :</strong> {{ $stats['periode'] }}</li>
                <li><strong>Total paiements :</strong> {{ number_format($stats['total_paiements']) }}</li>
                <li><strong>Total montant :</strong> {{ number_format($stats['total_montant'], 0, ',', ' ') }} FCFA</li>
                <li><strong>Zone :</strong> {{ $stats['zone'] }}</li>
            </ul>
        @else
            <p style="color: orange;">⚠️ Aucune donnée $stats fournie</p>
        @endif
    </div>

    <div class="success">
        <h3>🔗 Liens de test :</h3>
        <p><a href="/rapports/financier-pdf" target="_blank">📄 PDF avec Logo SICOSA</a></p>
        <p><a href="/rapports/simple" target="_blank">🧪 Test Simple</a></p>
        <p><a href="/rapports" target="_blank">📊 Centre de Rapports</a></p>
        <p><a href="/maire/dashboard" target="_blank">🏠 Dashboard Maire</a></p>
    </div>

    <div class="info">
        <h3>📊 Test complet des paiements :</h3>
        @if(isset($paiements) && $paiements->count() > 0)
            <table style="border-collapse: collapse; width: 100%; border: 1px solid #ddd;">
                <tr style="background: #f8f9fa;">
                    <th style="border: 1px solid #ddd; padding: 8px;">Date</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Unité</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Montant</th>
                </tr>
                @foreach($paiements as $paiement)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $paiement->unite->nom_commercial ?? 'N/A' }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </table>
        @else
            <p style="color: orange;">⚠️ Aucune donnée $paiements fournie</p>
        @endif
    </div>
</body>
</html> 