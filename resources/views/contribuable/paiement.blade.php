<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payer ma taxe - SICOFIL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card { border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border: none; margin-bottom: 1.5rem; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0 !important; padding: 1rem 1.5rem; font-weight: 600; }
        .stat-card { border-left: 4px solid #667eea; }
        .btn-pay { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; color: white; font-weight: 600; padding: 0.75rem 2rem; border-radius: 10px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('contribuable.dashboard') }}">
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
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-money-check-alt"></i> Payer ma taxe</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="{{ route('contribuable.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            @if(session('show_ticket_option') && session('paiement_id'))
            <div class="mt-2">
                <a href="{{ route('contribuable.paiements.receipt', session('paiement_id')) }}" target="_blank" class="btn btn-sm btn-primary">
                    <i class="fas fa-file-invoice"></i> Voir mon reçu
                </a>
            </div>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <strong>Le paiement n'a pas été enregistré.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-store me-2"></i>{{ $unite->nom_commercial ?? $unite->nom_complet_proprietaire }}</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('contribuable.paiement.store') }}" id="payment-form">
                            @csrf

                            <!-- Champs cachés géolocalisation -->
                            <input type="hidden" id="payment_latitude" name="payment_latitude" value="">
                            <input type="hidden" id="payment_longitude" name="payment_longitude" value="">
                            <input type="hidden" id="payment_accuracy" name="payment_accuracy" value="">

                            <!-- Section Géolocalisation -->
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-map-marker-alt text-danger"></i> Géolocalisation du paiement <span class="text-danger">*</span></label>
                                <div id="geo-status" class="alert alert-secondary py-2 mb-2" style="display: none;"></div>
                                <button type="button" class="btn btn-outline-success w-100" id="capture-gps-btn">
                                    <i class="fas fa-crosshairs"></i> Capturer ma position GPS
                                </button>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Positionnez-vous devant votre commerce avant de payer.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Unité commerciale</label>
                                <input type="text" class="form-control" value="{{ $unite->nom_commercial ?? $unite->nom_complet_proprietaire }} - {{ $unite->type_commerce }}" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="taxe_id" class="form-label">Type de taxe <span class="text-danger">*</span></label>
                                <select class="form-select @error('taxe_id') is-invalid @enderror" id="taxe_id" name="taxe_id" required>
                                    <option value="">-- Sélectionnez une taxe --</option>
                                    @foreach($taxes as $taxe)
                                        <option value="{{ $taxe->id }}" data-montant="{{ $taxe->montant }}" {{ old('taxe_id') == $taxe->id ? 'selected' : '' }}>
                                            {{ $taxe->nom }} - {{ number_format($taxe->montant, 0, ',', ' ') }} FCFA
                                        </option>
                                    @endforeach
                                </select>
                                @error('taxe_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="montant" class="form-label">Montant (FCFA) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('montant') is-invalid @enderror" id="montant" name="montant" value="{{ old('montant') }}" min="0" step="0.01" readonly style="background-color: #f8f9fa; font-weight: bold;" required>
                                @error('montant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-info">
                                    <i class="fas fa-info-circle"></i> Le montant est automatiquement calculé selon la taxe sélectionnée
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="date_paiement" class="form-label">Date du paiement <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_paiement') is-invalid @enderror" id="date_paiement" name="date_paiement" value="{{ old('date_paiement', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                                @error('date_paiement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="mode_paiement" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                                <select class="form-select @error('mode_paiement') is-invalid @enderror" id="mode_paiement" name="mode_paiement" required>
                                    <option value="">-- Sélectionnez un mode --</option>
                                    <option value="cash" {{ old('mode_paiement') == 'cash' ? 'selected' : '' }}>Cash (espèces)</option>
                                    <option value="wave" {{ old('mode_paiement') == 'wave' ? 'selected' : '' }}>Wave</option>
                                    <option value="orange_money" {{ old('mode_paiement') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                                </select>
                                @error('mode_paiement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3" id="reference-group" style="display: none;">
                                <label for="reference_transaction" class="form-label">Référence de transaction</label>
                                <input type="text" class="form-control @error('reference_transaction') is-invalid @enderror" id="reference_transaction" name="reference_transaction" value="{{ old('reference_transaction') }}" maxlength="255">
                                @error('reference_transaction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Pour Wave et Orange Money, notez la référence de transaction</div>
                            </div>

                            <div class="mb-3">
                                <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                                <textarea class="form-control @error('commentaire') is-invalid @enderror" id="commentaire" name="commentaire" rows="3" maxlength="500">{{ old('commentaire') }}</textarea>
                                @error('commentaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-success btn-lg" id="submit-payment-btn">
                                    <i class="fas fa-save"></i> Enregistrer le paiement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Informations</h6>
                    </div>
                    <div class="card-body">
                        <h6>Modes de paiement :</h6>
                        <ul>
                            <li><strong>Cash</strong> : Paiement en espèces</li>
                            <li><strong>Wave</strong> : Paiement mobile via Wave</li>
                            <li><strong>Orange Money</strong> : Paiement mobile via Orange</li>
                        </ul>
                        <hr>
                        <h6>Rappels :</h6>
                        <ul class="mb-0">
                            <li>Le montant correspond à la taxe applicable</li>
                            <li>Pour les paiements mobiles, attendez la confirmation</li>
                            <li>Un reçu est généré après chaque paiement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des paiements -->
        <div class="card shadow mt-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-history me-2"></i>Historique de mes paiements</h6>
            </div>
            <div class="card-body">
                @if($historique->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
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
                            @foreach($historique as $paiement)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                <td><code>{{ $paiement->reference ?? '#' . $paiement->id }}</code></td>
                                <td><strong>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</strong></td>
                                <td>
                                    @if($paiement->mode_paiement === 'wave')
                                        <span class="badge bg-info">Wave</span>
                                    @elseif($paiement->mode_paiement === 'orange_money')
                                        <span class="badge bg-warning text-dark">Orange Money</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($paiement->mode_paiement) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($paiement->statut === 'valide')
                                        <span class="badge bg-success">Payé</span>
                                    @elseif($paiement->statut === 'en_attente_validation')
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($paiement->statut) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($paiement->statut === 'valide')
                                        <a href="{{ route('contribuable.paiements.receipt', $paiement->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Télécharger le reçu">
                                            <i class="fas fa-file-invoice"></i> Reçu
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
                <div class="d-flex justify-content-center">
                    {{ $historique->links() }}
                </div>
                @else
                <p class="text-center text-muted py-3 mb-0">Aucun paiement enregistré</p>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const taxeSelect = document.getElementById('taxe_id');
            const montantInput = document.getElementById('montant');
            const modePaiement = document.getElementById('mode_paiement');
            const referenceGroup = document.getElementById('reference-group');

            function updateMontant() {
                const selectedOption = taxeSelect.options[taxeSelect.selectedIndex];
                if (selectedOption.value) {
                    const montant = parseFloat(selectedOption.dataset.montant) || 0;
                    montantInput.value = montant;
                    montantInput.style.backgroundColor = '#e8f5e8';
                    setTimeout(() => { montantInput.style.backgroundColor = '#f8f9fa'; }, 1500);
                } else {
                    montantInput.value = '';
                }
            }

            taxeSelect.addEventListener('change', updateMontant);

            modePaiement.addEventListener('change', function() {
                referenceGroup.style.display = (this.value === 'wave' || this.value === 'orange_money') ? 'block' : 'none';
            });

            if (taxeSelect.value) updateMontant();
            if (modePaiement.value) modePaiement.dispatchEvent(new Event('change'));

            // === Géolocalisation ===
            const captureBtn = document.getElementById('capture-gps-btn');
            const geoStatus = document.getElementById('geo-status');
            const latInput = document.getElementById('payment_latitude');
            const lngInput = document.getElementById('payment_longitude');
            const accInput = document.getElementById('payment_accuracy');

            function showGeoStatus(type, message) {
                geoStatus.style.display = 'block';
                geoStatus.className = 'alert py-2 mb-2 alert-' + (type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info');
                geoStatus.innerHTML = message;
            }

            if (captureBtn) {
                captureBtn.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        showGeoStatus('error', '❌ Géolocalisation non supportée par ce navigateur.');
                        return;
                    }

                    captureBtn.disabled = true;
                    captureBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Acquisition GPS en cours...';
                    showGeoStatus('info', '<i class="fas fa-satellite-dish fa-spin"></i> Recherche de satellites...');

                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            const acc = position.coords.accuracy;

                            latInput.value = lat;
                            lngInput.value = lng;
                            accInput.value = acc;

                            captureBtn.disabled = false;
                            captureBtn.innerHTML = '<i class="fas fa-check-circle"></i> Position capturée';
                            captureBtn.classList.remove('btn-outline-success');
                            captureBtn.classList.add('btn-success');

                            showGeoStatus('success',
                                '<i class="fas fa-check-circle"></i> <strong>Position capturée</strong><br>' +
                                'Lat: ' + lat.toFixed(6) + ' | Lng: ' + lng.toFixed(6) + '<br>' +
                                '<small>Précision: ~' + Math.round(acc) + ' mètres</small>'
                            );
                        },
                        function(error) {
                            captureBtn.disabled = false;
                            captureBtn.innerHTML = '<i class="fas fa-crosshairs"></i> Capturer ma position GPS';
                            let msg = 'Erreur de géolocalisation.';
                            switch(error.code) {
                                case error.PERMISSION_DENIED: msg = 'Vous avez refusé l\'accès à la position.'; break;
                                case error.POSITION_UNAVAILABLE: msg = 'Position non disponible. Activez le GPS.'; break;
                                case error.TIMEOUT: msg = 'Délai d\'attente dépassé. Réessayez.'; break;
                            }
                            showGeoStatus('error', '<i class="fas fa-times-circle"></i> ' + msg);
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                });
            }

            // Validation avant soumission
            const paymentForm = document.getElementById('payment-form');
            paymentForm.addEventListener('submit', function(e) {
                if (!latInput.value || !lngInput.value) {
                    e.preventDefault();
                    showGeoStatus('error', '<i class="fas fa-exclamation-triangle"></i> Veuillez d\'abord capturer votre position GPS.');
                    captureBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
            });
        });
    </script>
</body>
</html>

