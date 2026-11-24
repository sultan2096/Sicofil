<!-- Modal Rapport Zones -->
<div class="modal fade" id="modalZones" tabindex="-1" aria-labelledby="modalZonesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalZonesLabel">
                    <i class="fas fa-map-marked-alt me-2"></i>
                    Rapport des Zones Fiscales
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formZones" method="GET" action="{{ route('rapports.zones') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_debut_zones" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de début
                                </label>
                                <input type="date" class="form-control" id="date_debut_zones" name="date_debut" 
                                       value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_fin_zones" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de fin
                                </label>
                                <input type="date" class="form-control" id="date_fin_zones" name="date_fin" 
                                       value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="zone_specifique" class="form-label">
                                    <i class="fas fa-map-marked-alt me-1"></i>
                                    Zone spécifique (optionnel)
                                </label>
                                <select class="form-select" id="zone_specifique" name="zone_id">
                                    <option value="">Toutes les zones</option>
                                    @foreach(App\Models\ZoneFiscale::all() as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type_comparaison" class="form-label">
                                    <i class="fas fa-balance-scale me-1"></i>
                                    Type de comparaison
                                </label>
                                <select class="form-select" id="type_comparaison" name="type_comparaison">
                                    <option value="performance">Performance relative</option>
                                    <option value="evolution">Évolution temporelle</option>
                                    <option value="objectifs">Comparaison aux objectifs</option>
                                    <option value="densité">Densité de collecte</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-chart-bar me-1"></i>
                            Analyses à inclure
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="recettes_zone" id="anal_recettes" name="analyses[]" checked>
                                    <label class="form-check-label" for="anal_recettes">
                                        Recettes par zone
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="unites_zone" id="anal_unites" name="analyses[]" checked>
                                    <label class="form-check-label" for="anal_unites">
                                        Unités par zone
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="taux_recouvrement" id="anal_taux" name="analyses[]" checked>
                                    <label class="form-check-label" for="anal_taux">
                                        Taux de recouvrement
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="collecteurs_zone" id="anal_collecteurs" name="analyses[]" checked>
                                    <label class="form-check-label" for="anal_collecteurs">
                                        Collecteurs par zone
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="types_activites" id="anal_activites" name="analyses[]" checked>
                                    <label class="form-check-label" for="anal_activites">
                                        Types d'activités
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="cartographie" id="anal_carte" name="analyses[]">
                                    <label class="form-check-label" for="anal_carte">
                                        Cartographie (si disponible)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-secondary">
                        <i class="fas fa-map-marked-alt me-2"></i>
                        <strong>Rapport Zones :</strong> Analyse détaillée de la performance de chaque zone fiscale 
                        avec comparaisons, tendances et recommandations d'optimisation territoriale.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <button type="button" class="btn btn-secondary" onclick="generateZonesReport('html')">
                    <i class="fas fa-file-alt me-1"></i>
                    Générer le rapport
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function generateZonesReport(format) {
    const form = document.getElementById('formZones');
    const formData = new FormData(form);
    formData.append('format', format);
    
    // Convertir FormData en query string
    const params = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        params.append(key, value);
    }
    
    const url = form.action + '?' + params.toString();
    
    if (format === 'html') {
        window.open(url, '_blank');
    } else {
        window.location.href = url;
    }
    
    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalZones'));
    modal.hide();
}
</script> 