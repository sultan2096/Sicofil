@extends('layouts.app')

@section('title', 'Demandes de Correction')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Demandes de Correction des Paiements
                    </h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($correctionRequests->count() > 0)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>{{ $correctionRequests->count() }} demande(s) de correction</strong> en attente de traitement.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Paiement</th>
                                        <th>Collecteur</th>
                                        <th>Erreur signalée</th>
                                        <th>Date signalement</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($correctionRequests as $paiement)
                                        @php
                                            $errorLog = $errorLogs->where('details->paiement_id', $paiement->id)->first();
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $paiement->unite->nom_proprietaire ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">
                                                    {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA
                                                    • {{ $paiement->date_paiement->format('d/m/Y') }}
                                                </small><br>
                                                <code>{{ $paiement->reference }}</code>
                                            </td>
                                            <td>
                                                {{ $paiement->collecteur->name ?? 'N/A' }}<br>
                                                @if($paiement->collecteur && $paiement->collecteur->zone)
                                                    <small class="text-muted">{{ $paiement->collecteur->zone->nom }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($errorLog)
                                                    @php
                                                        $details = json_decode($errorLog->details, true);
                                                        $errorTypes = [
                                                            'wrong_amount' => 'Montant incorrect',
                                                            'wrong_unit' => 'Mauvaise unité',
                                                            'wrong_date' => 'Date incorrecte',
                                                            'duplicate' => 'Paiement en double',
                                                            'other' => 'Autre erreur'
                                                        ];
                                                    @endphp
                                                    <span class="badge bg-warning">
                                                        {{ $errorTypes[$details['error_type']] ?? $details['error_type'] }}
                                                    </span><br>
                                                    <small class="text-muted mt-1">{{ $details['description'] ?? 'N/A' }}</small>
                                                    @if(isset($details['proposed_correction']) && $details['proposed_correction'])
                                                        <br><small class="text-info">
                                                            <i class="fas fa-lightbulb"></i> {{ $details['proposed_correction'] }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Détails non disponibles</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($errorLog)
                                                    {{ $errorLog->created_at->format('d/m/Y H:i') }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="viewDetails({{ $paiement->id }})"
                                                            title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="approveCorrection({{ $paiement->id }})"
                                                            title="Approuver la correction">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning" 
                                                            onclick="rejectCorrection({{ $paiement->id }})"
                                                            title="Rejeter la demande">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deletePayment({{ $paiement->id }})"
                                                            title="Supprimer le paiement">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h4 class="text-muted">Aucune demande de correction</h4>
                            <p class="text-muted">Toutes les demandes de correction ont été traitées.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les détails -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">
                    <i class="fas fa-info-circle"></i> Détails de la demande de correction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <!-- Le contenu sera chargé dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour approuver la correction -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check text-success"></i> Approuver et corriger le paiement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i>
                        <strong>Correction du paiement :</strong> Vous pouvez modifier les détails incorrects avant d'approuver.
                    </div>
                    
                    <!-- Informations actuelles du paiement -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Informations actuelles du paiement</h6>
                        </div>
                        <div class="card-body" id="currentPaymentInfo">
                            <!-- Sera rempli dynamiquement -->
                        </div>
                    </div>
                    
                    <!-- Champs de correction -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="corrected_amount" class="form-label">Montant corrigé (FCFA)</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="corrected_amount" 
                                       name="corrected_amount" 
                                       min="0" 
                                       step="1"
                                       placeholder="Nouveau montant si différent">
                                <div class="form-text">Laissez vide pour conserver le montant actuel</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="corrected_date" class="form-label">Date corrigée</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="corrected_date" 
                                       name="corrected_date">
                                <div class="form-text">Laissez vide pour conserver la date actuelle</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="correction_note" class="form-label">Note de correction</label>
                        <textarea class="form-control" id="correction_note" name="correction_note" rows="3" 
                                  placeholder="Décrivez les corrections apportées..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approuver et corriger
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour rejeter la correction -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times text-warning"></i> Rejeter la demande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        En rejetant cette demande, le paiement sera remis en statut "Validé" et le collecteur sera notifié.
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Motif du rejet</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" 
                                  placeholder="Expliquez pourquoi cette demande est rejetée..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-times"></i> Rejeter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewDetails(paymentId) {
    // Ici vous pourriez faire un appel AJAX pour récupérer les détails
    // Pour l'instant, on affiche juste un message
    document.getElementById('detailsContent').innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
            <p>Chargement des détails...</p>
        </div>
    `;
    
    var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    modal.show();
    
    // Simuler le chargement des détails
    setTimeout(() => {
        document.getElementById('detailsContent').innerHTML = `
            <div class="alert alert-info">
                <strong>Détails complets du paiement #${paymentId}</strong>
            </div>
            <p>Cette fonctionnalité sera implémentée pour afficher tous les détails du paiement et de l'erreur signalée.</p>
        `;
    }, 1000);
}

function approveCorrection(paymentId) {
    // Récupérer les informations du paiement depuis le tableau
    const row = document.querySelector(`button[onclick="approveCorrection(${paymentId})"]`).closest('tr');
    const cells = row.querySelectorAll('td');
    
    // Extraire les informations
    const paymentInfo = cells[0].innerHTML;
    const collectorInfo = cells[1].innerHTML;
    const errorInfo = cells[2].innerHTML;
    
    // Remplir les informations actuelles
    document.getElementById('currentPaymentInfo').innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <strong>Unité :</strong><br>
                ${paymentInfo}
            </div>
            <div class="col-md-4">
                <strong>Collecteur :</strong><br>
                ${collectorInfo}
            </div>
            <div class="col-md-4">
                <strong>Erreur signalée :</strong><br>
                ${errorInfo}
            </div>
        </div>
    `;
    
    // Configurer l'action du formulaire
    document.getElementById('approveForm').action = `/paiements/${paymentId}/approve-correction`;
    
    // Afficher le modal
    var modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectCorrection(paymentId) {
    document.getElementById('rejectForm').action = `/paiements/${paymentId}/reject-correction`;
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function deletePayment(paymentId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer définitivement ce paiement ? Cette action est irréversible.')) {
        // Créer un formulaire pour la suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/paiements/${paymentId}/delete-erroneous`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

@endsection 