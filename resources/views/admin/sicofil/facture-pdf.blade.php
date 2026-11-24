<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #334155; }
        .invoice { max-width: 700px; margin: 0 auto; }
        .header { display: table; width: 100%; margin-bottom: 30px; border-bottom: 3px solid #1e3a8a; padding-bottom: 15px; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        .logo-text { font-size: 22px; font-weight: bold; color: #1e3a8a; }
        .subtitle { font-size: 10px; color: #64748b; margin-top: 2px; }
        .invoice-title { font-size: 24px; color: #1e3a8a; margin: 0; }
        .invoice-num { font-size: 14px; font-weight: bold; margin-top: 5px; }
        .block { margin-bottom: 20px; }
        .block-title { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px; }
        .client-name { font-size: 14px; font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; margin: 25px 0; }
        table.items th { background: #1e3a8a; color: white; padding: 10px 12px; text-align: left; font-size: 10px; }
        table.items td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; }
        table.items .text-right { text-align: right; }
        .totals { margin-top: 20px; text-align: right; }
        .totals table { margin-left: auto; width: 280px; }
        .totals td { padding: 6px 12px; }
        .totals .label { text-align: left; color: #64748b; }
        .totals .total-row { font-size: 14px; font-weight: bold; background: #f1f5f9; }
        .footer-note { margin-top: 35px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #64748b; }
        .statut-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 10px; }
        .statut-en_attente { background: #fef3c7; color: #92400e; }
        .statut-payee { background: #d1fae5; color: #065f46; }
        .statut-en_retard { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <div class="header-left">
                <div class="logo-text">SICOFIL</div>
                <div class="subtitle">Système d'Information Commercial pour la Fiscalité Locale</div>
                <div class="subtitle">Facturation SaaS — Abonnements communes</div>
            </div>
            <div class="header-right">
                <div class="invoice-title">FACTURE</div>
                <div class="invoice-num">{{ $facture->numero_facture }}</div>
                <div style="margin-top:8px;">
                    <span class="statut-badge statut-{{ $facture->statut }}">{{ ucfirst(str_replace('_', ' ', $facture->statut)) }}</span>
                </div>
            </div>
        </div>

        <div style="display:table; width:100%;">
            <div style="display:table-cell; width:50%; padding-right:20px;">
                <div class="block">
                    <div class="block-title">Facturé à</div>
                    <div class="client-name">{{ $facture->commune_nom }}</div>
                    @if(!empty($facture->commune_code))<div>{{ $facture->commune_code }}</div>@endif
                    @if(!empty($facture->region))<div>{{ $facture->region }} / {{ $facture->departement ?? '' }}</div>@endif
                </div>
            </div>
            <div style="display:table-cell; width:50%;">
                <div class="block">
                    <div class="block-title">Période</div>
                    <div><strong>{{ ucfirst($facture->periode) }}</strong></div>
                </div>
                <div class="block">
                    <div class="block-title">Date d'émission</div>
                    <div>{{ \Carbon\Carbon::parse($facture->date_emission)->format('d/m/Y') }}</div>
                </div>
                <div class="block">
                    <div class="block-title">Date d'échéance</div>
                    <div>{{ \Carbon\Carbon::parse($facture->date_echeance)->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class="text-right">Montant (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @if((float)($facture->montant_fixe ?? 0) > 0)
                <tr>
                    <td>Part fixe abonnement ({{ $facture->periode }})</td>
                    <td class="text-right">{{ number_format($facture->montant_fixe, 0, ',', ' ') }}</td>
                </tr>
                @endif
                @if((float)($facture->montant_variable ?? 0) > 0)
                <tr>
                    <td>Part variable (% recettes)</td>
                    <td class="text-right">{{ number_format($facture->montant_variable, 0, ',', ' ') }}</td>
                </tr>
                @endif
                <tr>
                    <td><strong>Total TTC</strong></td>
                    <td class="text-right"><strong>{{ number_format($facture->montant_total, 0, ',', ' ') }} FCFA</strong></td>
                </tr>
            </tbody>
        </table>

        @if($facture->statut === 'payee' && $facture->date_paiement)
        <div class="block">
            <div class="block-title">Paiement reçu</div>
            <div>Le {{ \Carbon\Carbon::parse($facture->date_paiement)->format('d/m/Y') }}@if($facture->mode_paiement) — {{ $facture->mode_paiement }}@endif</div>
        </div>
        @endif

        @if(!empty($facture->notes))
        <div class="block">
            <div class="block-title">Notes</div>
            <div>{{ $facture->notes }}</div>
        </div>
        @endif

        <div class="footer-note">
            Document émis par SICOFIL. Ce document fait foi pour le suivi des abonnements SaaS des communes.
            Référence : {{ $facture->numero_facture }} — {{ $facture->commune_nom }}.
        </div>
    </div>
</body>
</html>
