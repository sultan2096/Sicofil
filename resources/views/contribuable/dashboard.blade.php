<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mon Espace - SICOFIL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 1.5rem;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .stat-card {
            border-left: 4px solid #667eea;
        }
        .btn-pay {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 10px;
        }
        .badge-statut {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        .badge-valide { background-color: #28a745; color: white; }
        .badge-en_attente { background-color: #ffc107; color: #333; }
        .badge-impaye { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('images/logos/snd.jpeg') }}" width="40" height="40" class="rounded-circle me-2">
                SICOFIL - Mon Espace
            </a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3"><i class="fas fa-user"></i> {{ $contribuable->nom_complet }}</span>
                <a href="{{ route('contribuable.logout') }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        @if(request('status') === 'success')
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Paiement confirmé. Merci !
        </div>
        @elseif(request('status') === 'failed')
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i> Paiement échoué. Réessayez plus tard.
        </div>
        @elseif(request('status') === 'cancel')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Paiement annulé.
        </div>
        @elseif(request('status') === 'pending')
        <div class="alert alert-info">
            <i class="fas fa-hourglass-half"></i> Paiement en cours de validation.
        </div>
        @endif
        <!-- Informations Unité Commerciale -->
        @if($unite)
        <div class="card">
            <div class="card-header">
                <i class="fas fa-store"></i> Mon Commerce
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>{{ $unite->nom_commercial }}</h5>
                        <p class="text-muted mb-1"><i class="fas fa-map-marker-alt"></i> {{ $unite->adresse }}</p>
                        <p class="text-muted mb-1"><i class="fas fa-tag"></i> {{ $unite->typeCommerce->nom ?? 'Non défini' }}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h3 class="text-primary">{{ number_format($unite->taxe->montant ?? 0, 0, ',', ' ') }} FCFA</h3>
                        <p class="text-muted">Montant mensuel</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Montant dû -->
        @if($montantDu > 0)
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-danger mb-1">
                            <i class="fas fa-exclamation-triangle"></i> Montant à payer
                        </h5>
                        <h2 class="mb-0">{{ number_format($montantDu, 0, ',', ' ') }} FCFA</h2>
                    </div>
                    <button class="btn btn-pay" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="fas fa-credit-card"></i> Payer maintenant
                    </button>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Vous êtes à jour dans vos paiements !
        </div>
        @endif

        @else
        <div class="alert alert-warning">
            <i class="fas fa-info-circle"></i> Aucune unité commerciale liée à votre compte. 
            Contactez la mairie pour plus d'informations.
        </div>
        @endif

        <!-- Historique des paiements -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i> Historique des Paiements
            </div>
            <div class="card-body">
                @if($paiements->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Référence</th>
                                <th>Montant</th>
                                <th>Mode</th>
                                <th>Statut</th>
                                <th>Reçu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paiements as $paiement)
                            <tr>
                                <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                <td><code>{{ $paiement->reference ?? '#' . $paiement->id }}</code></td>
                                <td><strong>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</strong></td>
                                <td>
                                    @if($paiement->mode_paiement === 'wave')
                                    <span class="badge bg-info">Wave</span>
                                    @elseif($paiement->mode_paiement === 'orange_money')
                                    <span class="badge bg-warning">Orange Money</span>
                                    @else
                                    <span class="badge bg-secondary">{{ ucfirst($paiement->mode_paiement) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-statut badge-{{ $paiement->statut }}">
                                        {{ ucfirst(str_replace('_', ' ', $paiement->statut)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($paiement->statut === 'valide')
                                        <a href="{{ route('contribuable.paiements.receipt', $paiement->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $paiements->links() }}
                @else
                <p class="text-center text-muted py-3">Aucun paiement enregistré</p>
                @endif
            </div>
        </div>

        <!-- Contact Mairie -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-envelope"></i> Contacter la mairie
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('contribuable.contact.mairie') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Votre message</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Paiement -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payer ma taxe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="payment-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Montant à payer</label>
                            <input type="number" name="montant" class="form-control" value="{{ $montantDu }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mode de paiement</label>
                            <select name="mode_paiement" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                <option value="wave">Wave</option>
                                <option value="orange_money">Orange Money</option>
                                <option value="free_money">Free Money</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-lock"></i> Procéder au paiement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = "{{ url('/') }}";

        document.getElementById('payment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            fetch(BASE_URL + '/contribuable/initiate-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(response => {
                if (response.success && response.payment_url) {
                    window.location.href = response.payment_url;
                } else {
                    alert('Erreur lors de l\'initialisation du paiement');
                }
            })
            .catch(err => {
                alert('Erreur réseau');
            });
        });
    </script>
</body>
</html>
