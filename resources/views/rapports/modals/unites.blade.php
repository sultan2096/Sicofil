<!-- Modal Rapport Unités Commerciales -->
<div class="modal fade" id="modalUnites" tabindex="-1" aria-labelledby="modalUnitesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalUnitesLabel">
                    <i class="fas fa-store me-2"></i>
                    Générer un Rapport des Unités Commerciales
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rapports.unites') }}" method="GET" target="_blank">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_debut_unites" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Date de début (pour les paiements)
                            </label>
                            <input type="date" class="form-control" id="date_debut_unites" name="date_debut" 
                                   value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_fin_unites" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Date de fin (pour les paiements)
                            </label>
                            <input type="date" class="form-control" id="date_fin_unites" name="date_fin" 
                                   value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="zone_id_unites" class="form-label">
                                <i class="fas fa-map-marked-alt me-1"></i>
                                Zone fiscale (optionnel)
                            </label>
                            <select class="form-select" id="zone_id_unites" name="zone_id">
                                <option value="">Toutes les zones</option>
                                @foreach(App\Models\ZoneFiscale::all() as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="statut_unites" class="form-label">
                                <i class="fas fa-toggle-on me-1"></i>
                                Statut des unités
                            </label>
                            <select class="form-select" id="statut_unites" name="statut">
                                <option value="">Toutes les unités</option>
                                <option value="payantes">Unités payantes uniquement</option>
                                <option value="non_payantes">Unités non payantes uniquement</option>
                            </select>
                        </div>
                    </div>
                    <!-- Format PDF par défaut -->
                    <input type="hidden" name="format" value="pdf">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Contenu du rapport :</strong><br>
                        • Liste complète des unités commerciales<br>
                        • Informations propriétaires et activités<br>
                        • Statut de paiement sur la période sélectionnée<br>
                        • Répartition par zone fiscale et type d'activité<br>
                        • Montants collectés par unité<br>
                        • Statistiques détaillées par zone et activité
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-building me-1"></i>
                        Générer le rapport
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 