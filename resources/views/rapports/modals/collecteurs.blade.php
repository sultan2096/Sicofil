<!-- Modal Rapport Collecteurs -->
<div class="modal fade" id="modalCollecteurs" tabindex="-1" aria-labelledby="modalCollecteursLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCollecteursLabel">
                    <i class="fas fa-users me-2"></i>
                    Générer un Rapport d'Activité des Collecteurs
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rapports.collecteurs') }}" method="GET" target="_blank">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_debut_collecteurs" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Date de début
                            </label>
                            <input type="date" class="form-control" id="date_debut_collecteurs" name="date_debut" 
                                   value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_fin_collecteurs" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Date de fin
                            </label>
                            <input type="date" class="form-control" id="date_fin_collecteurs" name="date_fin" 
                                   value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="collecteur_id" class="form-label">
                                <i class="fas fa-user me-1"></i>
                                Collecteur spécifique (optionnel)
                            </label>
                            <select class="form-select" id="collecteur_id" name="collecteur_id">
                                <option value="">Tous les collecteurs</option>
                                @foreach(App\Models\User::where('role', 'collecteur')->get() as $collecteur)
                                    <option value="{{ $collecteur->id }}">{{ $collecteur->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Format PDF par défaut -->
                        <input type="hidden" name="format" value="pdf">
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Contenu du rapport :</strong><br>
                        • Nombre de paiements collectés par collecteur<br>
                        • Montants totaux collectés<br>
                        • Moyenne par paiement<br>
                        • Zones d'affectation<br>
                        • Comparaison des performances
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-chart me-1"></i>
                        Générer le rapport
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 