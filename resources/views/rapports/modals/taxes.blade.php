<!-- Modal Rapport Taxes -->
<div class="modal fade" id="modalTaxes" tabindex="-1" aria-labelledby="modalTaxesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalTaxesLabel">
                    <i class="fas fa-receipt me-2"></i>
                    Rapport Taxes et Redevances
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTaxes" method="GET" action="{{ route('rapports.taxes') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_debut_taxes" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de début
                                </label>
                                <input type="date" class="form-control" id="date_debut_taxes" name="date_debut" 
                                       value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_fin_taxes" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de fin
                                </label>
                                <input type="date" class="form-control" id="date_fin_taxes" name="date_fin" 
                                       value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taxe_specifique" class="form-label">
                                    <i class="fas fa-receipt me-1"></i>
                                    Type de taxe (optionnel)
                                </label>
                                <select class="form-select" id="taxe_specifique" name="taxe_id">
                                    <option value="">Toutes les taxes</option>
                                    @foreach(App\Models\Taxe::all() as $taxe)
                                        <option value="{{ $taxe->id }}">{{ $taxe->nom }} - {{ number_format($taxe->montant) }} FCFA</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type_analyse_taxes" class="form-label">
                                    <i class="fas fa-chart-pie me-1"></i>
                                    Type d'analyse
                                </label>
                                <select class="form-select" id="type_analyse_taxes" name="type_analyse">
                                    <option value="revenus">Revenus par type</option>
                                    <option value="evolution">Évolution temporelle</option>
                                    <option value="repartition">Répartition géographique</option>
                                    <option value="efficacite">Efficacité de collecte</option>
                                    <option value="projections">Projections budgétaires</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-analytics me-1"></i>
                            Métriques à inclure
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="revenus_totaux" id="met_revenus" name="metriques[]" checked>
                                    <label class="form-check-label" for="met_revenus">
                                        Revenus totaux par taxe
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="nombre_paiements" id="met_paiements" name="metriques[]" checked>
                                    <label class="form-check-label" for="met_paiements">
                                        Nombre de paiements
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="montant_moyen" id="met_moyen" name="metriques[]" checked>
                                    <label class="form-check-label" for="met_moyen">
                                        Montant moyen par paiement
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="taux_perception" id="met_taux" name="metriques[]" checked>
                                    <label class="form-check-label" for="met_taux">
                                        Taux de perception
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="repartition_zones" id="met_zones" name="metriques[]" checked>
                                    <label class="form-check-label" for="met_zones">
                                        Répartition par zones
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="evolution_mensuelle" id="met_evolution" name="metriques[]" checked>
                                    <label class="form-check-label" for="met_evolution">
                                        Évolution mensuelle
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="comparaison_objectifs" id="met_objectifs" name="metriques[]">
                                    <label class="form-check-label" for="met_objectifs">
                                        Comparaison aux objectifs
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="projections" id="met_projections" name="metriques[]">
                                    <label class="form-check-label" for="met_projections">
                                        Projections futures
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-chart-bar me-1"></i>
                            Graphiques à inclure
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="camembert_revenus" id="graph_camembert" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_camembert">
                                        Camembert des revenus
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="barres_evolution" id="graph_barres" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_barres">
                                        Barres d'évolution
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="courbe_tendance" id="graph_courbe" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_courbe">
                                        Courbe de tendance
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="heatmap_zones" id="graph_heatmap" name="graphiques[]">
                                    <label class="form-check-label" for="graph_heatmap">
                                        Heatmap par zones
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-dark">
                        <i class="fas fa-coins me-2"></i>
                        <strong>Rapport Taxes :</strong> Analyse complète des revenus par type de taxe et redevance, 
                        avec évolution temporelle, répartition géographique et projections budgétaires.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <button type="button" class="btn btn-dark" onclick="generateTaxesReport('html')">
                    <i class="fas fa-file-alt me-1"></i>
                    Générer le rapport
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function generateTaxesReport(format) {
    const form = document.getElementById('formTaxes');
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
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalTaxes'));
    modal.hide();
}
</script> 