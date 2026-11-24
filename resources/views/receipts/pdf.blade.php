<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de Paiement - SICOFIL</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; max-width: 700px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #1a5f7a; }
        .title { font-size: 18px; font-weight: bold; margin-top: 10px; }
        .receipt-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .receipt-info div { flex: 1; }
        .receipt-number { font-size: 16px; font-weight: bold; color: #1a5f7a; }
        .date { font-size: 14px; color: #666; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 14px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        .row { display: flex; margin-bottom: 8px; }
        .label { width: 150px; font-weight: bold; color: #555; }
        .value { flex: 1; }
        .amount { font-size: 18px; font-weight: bold; color: #1a5f7a; }
        .amount-cfa { font-size: 14px; color: #666; }
        .footer { margin-top: 40px; text-align: center; color: #999; font-size: 10px; }
        .signature { margin-top: 30px; text-align: right; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin-top: 40px; padding-top: 5px; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin: 20px 0; border-radius: 4px; font-size: 11px; }
        .status-valid { color: #28a745; font-weight: bold; }
        .status-attente { color: #ffc107; font-weight: bold; }
        .status-echoue { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏛️ SICOFIL</div>
            <div class="title">REÇU DE PAIEMENT</div>
        </div>

        <div class="receipt-info">
            <div>
                <div class="receipt-number">N° {{ $numero_recu }}</div>
                <div class="date">Émis le {{ $date_emission }}</div>
            </div>
            <div style="text-align: right;">
                <div class="receipt-number">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</div>
                <div class="date">Statut: <span class="status-{{ $paiement->statut }}">{{ strtoupper($paiement->statut) }}</span></div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">DÉTAILS DU PAIEMENT</div>
            <div class="row">
                <div class="label">Date paiement:</div>
                <div class="value">{{ $paiement->date_paiement->format('d/m/Y H:i') }}</div>
            </div>
            <div class="row">
                <div class="label">Mode paiement:</div>
                <div class="value">{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</div>
            </div>
            @if($paiement->reference_transaction)
            <div class="row">
                <div class="label">Référence:</div>
                <div class="value">{{ $paiement->reference_transaction }}</div>
            </div>
            @endif
            @if($paiement->commentaire)
            <div class="row">
                <div class="label">Commentaire:</div>
                <div class="value">{{ $paiement->commentaire }}</div>
            </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">UNITÉ COMMERCIALE</div>
            <div class="row">
                <div class="label">Propriétaire:</div>
                <div class="value">{{ $paiement->unite->nom_complet_proprietaire }}</div>
            </div>
            <div class="row">
                <div class="label">Nature:</div>
                <div class="value">{{ $paiement->unite->nature }}</div>
            </div>
            <div class="row">
                <div class="label">Type commerce:</div>
                <div class="value">{{ $paiement->unite->type_commerce }}</div>
            </div>
            @if($paiement->unite->adresse)
            <div class="row">
                <div class="label">Adresse:</div>
                <div class="value">{{ $paiement->unite->adresse }}</div>
            </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">ZONE FISCALE</div>
            <div class="row">
                <div class="label">Zone:</div>
                <div class="value">{{ $paiement->unite->zone->nom ?? 'Non définie' }}</div>
            </div>
            @if($paiement->unite->zone->commune)
            <div class="row">
                <div class="label">Commune:</div>
                <div class="value">{{ $paiement->unite->zone->commune->nom }}</div>
            </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">COLLECTEUR</div>
            <div class="row">
                <div class="label">Nom:</div>
                <div class="value">{{ $paiement->collecteur->name }}</div>
            </div>
            <div class="row">
                <div class="label">Identifiant:</div>
                <div class="value">{{ $paiement->collecteur->identifiant }}</div>
            </div>
        </div>

        @if($paiement->montant_avance > 0 || $paiement->montant_restant > 0)
        <div class="warning">
            <strong>PAIEMENT PARTIEL</strong><br>
            Montant calculé pour ce paiement: {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA<br>
            Avance payée: {{ number_format($paiement->montant_avance ?? $paiement->montant, 0, ',', ' ') }} FCFA<br>
            Reste à payer: {{ number_format($paiement->montant_restant, 0, ',', ' ') }} FCFA
        </div>
        @endif

        <div class="signature">
            <div>{{ $signature['signataire'] }}</div>
            <div>{{ $signature['titre'] }}</div>
            <div class="signature-line">Signature</div>
            <div style="font-size: 10px; margin-top: 10px;">{{ $signature['date_signature'] }}</div>
        </div>

        <div class="footer">
            <p>Ce reçu est électroniquement signé et constitue une preuve de paiement.</p>
            <p>Pour vérifier l'authenticité, scannez le QR code sur le ticket ou visitez:</p>
            <p>{{ url('/paiements/' . $paiement->id . '/verify') }}</p>
            <p style="margin-top: 20px;">SICOFIL - Système Intégré de Collecte des Fiscales Locales</p>
        </div>
    </div>
</body>
</html>
