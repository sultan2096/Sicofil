<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Reçu - SICOSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px 0;
        }

        .verification-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .verification-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .verification-header .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 3px solid white;
        }

        .verification-status {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 15px;
            padding: 20px;
            margin: 30px;
            text-align: center;
        }

        .verification-status.invalid {
            background: #f8d7da;
            border-color: #dc3545;
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 30px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            color: #212529;
            font-weight: 500;
        }

        .amount-highlight {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 30px;
        }

        .verification-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }

        .btn-verify {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }

        .btn-verify:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }

        .security-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 30px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .verification-container {
                margin: 0 10px;
            }
            
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    @php
        $verifyCommune = $paiement->commune ?? auth()->user()?->commune;
        $verifyLogoUrl = $verifyCommune?->logo_url ?? asset('images/logos/snd.jpeg');
        $verifyAcronyme = $verifyCommune?->display_acronyme ?? 'SICOSA';
        $verifyCommuneNom = $verifyCommune?->nom ?? 'Sandiara';
    @endphp
    <div class="verification-container">
        <!-- En-tête -->
        <div class="verification-header">
            <img src="{{ $verifyLogoUrl }}" alt="Logo {{ $verifyAcronyme }}" class="logo" onerror="this.src='{{ asset('images/logos/snd.jpeg') }}';">
            <h1 class="h3 mb-2">VÉRIFICATION DE REÇU</h1>
            <p class="mb-0">Système d'Information Commerciale de {{ $verifyCommuneNom }}</p>
        </div>

        <!-- Statut de vérification -->
        @if($paiement->statut === 'valide')
            <div class="verification-status">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4 class="text-success mb-2">✅ REÇU AUTHENTIQUE</h4>
                <p class="mb-0">Ce reçu a été vérifié et est authentique. Le paiement a été validé par SICOSA.</p>
            </div>
        @elseif($paiement->statut === 'annule')
            <div class="verification-status invalid">
                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                <h4 class="text-danger mb-2">❌ REÇU ANNULÉ</h4>
                <p class="mb-0">Ce reçu correspond à un paiement qui a été annulé. Il n'est plus valide.</p>
            </div>
        @else
            <div class="verification-status">
                <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                <h4 class="text-warning mb-2">⏳ EN ATTENTE</h4>
                <p class="mb-0">Ce reçu est en attente de validation. Veuillez contacter SICOSA pour plus d'informations.</p>
            </div>
        @endif

        <!-- Informations du reçu -->
        <div class="info-card">
            <h5 class="mb-3">
                <i class="fas fa-receipt me-2"></i>
                Détails du Reçu N° {{ str_pad($paiement->id, 6, '0', STR_PAD_LEFT) }}
            </h5>
            
            <div class="info-row">
                <span class="info-label">Date de paiement :</span>
                <span class="info-value">{{ $paiement->date_paiement->format('d/m/Y à H:i') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Mode de paiement :</span>
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

        <!-- Montant -->
        <div class="amount-highlight">
            <h4 class="mb-1">MONTANT PAYÉ</h4>
            <h2 class="mb-0">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</h2>
        </div>

        <!-- Informations de l'unité commerciale -->
        <div class="info-card">
            <h5 class="mb-3">
                <i class="fas fa-store me-2"></i>
                Unité Commerciale
            </h5>
            
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
                <span class="info-label">Zone fiscale :</span>
                <span class="info-value">{{ $paiement->unite->zone->nom ?? 'Non définie' }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Collecteur :</span>
                <span class="info-value">{{ $paiement->collecteur->name }}</span>
            </div>
        </div>

        <!-- Informations de sécurité -->
        <div class="security-info">
            <i class="fas fa-shield-alt me-2"></i>
            <strong>Informations de sécurité :</strong><br>
            • Ce reçu a été généré par le système officiel SICOSA<br>
            • La vérification se fait en temps réel depuis notre base de données<br>
            • En cas de doute, contactez la commune de Sandiara<br>
            • Conservez ce reçu comme preuve de paiement
        </div>

        <!-- Footer -->
        <div class="verification-footer">
            <a href="{{ route('paiements.ticket', $paiement) }}" class="btn-verify" target="_blank">
                <i class="fas fa-receipt me-2"></i>
                Voir le reçu complet
            </a>
            
            <div class="mt-3">
                <small class="text-muted">
                    <strong>SICOSA</strong> - Système d'Information Commerciale de Sandiara<br>
                    Vérification effectuée le {{ now()->format('d/m/Y à H:i') }}
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>