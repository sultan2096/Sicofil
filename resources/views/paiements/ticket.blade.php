<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Paiement - SICOSA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 20px;
        }

        .ticket {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border: 2px dashed #666;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: block;
            border: 2px solid #007bff;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .receipt-number {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin-top: 10px;
        }

        .section {
            margin: 15px 0;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 10px;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 13px;
        }

        .info-label {
            color: #666;
            font-weight: bold;
        }

        .info-value {
            color: #333;
            text-align: right;
            max-width: 60%;
            word-wrap: break-word;
        }

        .amount-section {
            background: #f8f9fa;
            padding: 15px;
            border: 2px solid #007bff;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }

        .amount-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .amount-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .amount-words {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            font-size: 12px;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 40px;
            margin-bottom: 5px;
        }

        .thank-you {
            font-size: 12px;
            color: #666;
            margin: 10px 0;
        }

        .qr-code {
            text-align: center;
            margin: 15px 0;
        }

        .print-buttons {
            text-align: center;
            margin: 20px 0;
        }

        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .ticket {
                box-shadow: none;
                border: 1px solid #333;
                max-width: none;
                margin: 0;
            }
            
            .print-buttons {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-buttons">
        <button class="btn" onclick="window.print()">
            🖨️ Imprimer le Reçu
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            ❌ Fermer
        </button>
        <a href="{{ route('paiements.index') }}" class="btn btn-secondary">
            🔙 Retour aux Paiements
        </a>
    </div>

    @php
        $ticketCommune = $paiement->commune ?? auth()->user()?->commune;
        $ticketLogoUrl = $ticketCommune?->logo_url ?? asset('images/logos/snd.jpeg');
        $ticketAcronyme = $ticketCommune?->display_acronyme ?? 'SICOSA';
        $ticketCommuneNom = $ticketCommune?->nom ?? 'Sandiara';
    @endphp
    <div class="ticket">
        <!-- En-tête -->
        <div class="header">
            <img src="{{ $ticketLogoUrl }}" alt="Logo {{ $ticketAcronyme }}" class="logo" onerror="this.src='{{ asset('images/logos/snd.jpeg') }}';">
            <div class="title">{{ $ticketAcronyme }}</div>
            <div class="subtitle">Système d'Information Commerciale</div>
            <div class="subtitle">Commune de {{ $ticketCommuneNom }}</div>
            <div class="receipt-number">REÇU N° {{ str_pad($paiement->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        <!-- Informations du paiement -->
        <div class="section">
            <div class="section-title">📅 Détails du Paiement</div>
            <div class="info-row">
                <span class="info-label">Date :</span>
                <span class="info-value">{{ $paiement->date_paiement->format('d/m/Y à H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Mode :</span>
                <span class="info-value">
                    @switch($paiement->mode_paiement)
                        @case('cash')
                            💰 Espèces
                            @break
                        @case('wave')
                            📱 Wave
                            @break
                        @case('orange_money')
                            🍊 Orange Money
                            @break
                        @default
                            {{ ucfirst($paiement->mode_paiement) }}
                    @endswitch
                </span>
            </div>
            @if($paiement->reference_transaction)
                <div class="info-row">
                    <span class="info-label">Référence :</span>
                    <span class="info-value">{{ $paiement->reference_transaction }}</span>
                </div>
            @endif
        </div>

        <!-- Informations de l'unité commerciale -->
        <div class="section">
            <div class="section-title">🏪 Unité Commerciale</div>
            <div class="info-row">
                <span class="info-label">Propriétaire :</span>
                <span class="info-value">{{ $paiement->unite->nom_proprietaire }}</span>
            </div>
            @if($paiement->unite->nom_commercial)
                <div class="info-row">
                    <span class="info-label">Commerce :</span>
                    <span class="info-value">{{ $paiement->unite->nom_commercial }}</span>
                </div>
            @endif
            <div class="info-row">
                <span class="info-label">Activité :</span>
                <span class="info-value">{{ $paiement->unite->type_activite ?? 'Non spécifié' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Zone :</span>
                <span class="info-value">{{ $paiement->unite->zone->nom ?? 'Non définie' }}</span>
            </div>
            @if($paiement->unite->adresse)
                <div class="info-row">
                    <span class="info-label">Adresse :</span>
                    <span class="info-value">{{ $paiement->unite->adresse }}</span>
                </div>
            @endif
        </div>

        <!-- Montant -->
        <div class="amount-section">
            <div class="amount-label">MONTANT PAYÉ</div>
            <div class="amount-value">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</div>
            <div class="amount-words">
                {{ ucfirst(\Illuminate\Support\Str::words(number_format($paiement->montant, 0, ',', ' '), 10)) }} francs CFA
            </div>
        </div>

        <!-- Informations du collecteur -->
        <div class="section">
            <div class="section-title">👤 Collecteur</div>
            <div class="info-row">
                <span class="info-label">Nom :</span>
                <span class="info-value">{{ $paiement->collecteur->name ?? 'Paiement en ligne' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Zone :</span>
                <span class="info-value">{{ $paiement->collecteur->zone->nom ?? 'Non définie' }}</span>
            </div>
        </div>

        @if($paiement->commentaire)
            <div class="section">
                <div class="section-title">💬 Commentaire</div>
                <div class="info-value">{{ $paiement->commentaire }}</div>
            </div>
        @endif

        <!-- Code QR -->
        <div class="qr-code">
            <div style="text-align: center; margin: 15px 0;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&format=png&ecc=M&data={{ urlencode($qrData) }}" 
                     alt="QR Code Reçu" 
                     style="border: 2px solid #ddd; border-radius: 8px;">
                <div style="font-size: 10px; color: #666; margin-top: 5px;">
                    <strong>QR Code - Reçu {{ str_pad($paiement->id, 6, '0', STR_PAD_LEFT) }}</strong><br>
                    <span style="font-size: 8px;">Scannez pour vérifier l'authenticité</span>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Signature Collecteur</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>Signature Payeur</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">
                ✅ <strong>Merci pour votre paiement !</strong>
            </div>
            <div style="font-size: 10px; color: #999; margin-top: 10px;">
                Ce reçu fait foi de paiement<br>
                Conservez-le précieusement<br>
                <strong>SICOSA</strong> - Sandiara, {{ now()->format('Y') }}
            </div>
        </div>
    </div>

    <script>
        // Auto-focus pour impression
        window.onload = function() {
            // Auto-print si demandé via URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === '1') {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        };
    </script>
</body>
</html> 