<!-- Modal Rapport Mensuel -->
<div class="modal fade" id="modalMensuel" tabindex="-1" aria-labelledby="modalMensuelLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalMensuelLabel">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Rapport Mensuel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formMensuel" method="GET" action="{{ route('rapports.mensuel') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mois_rapport" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>
                                    Mois du rapport
                                </label>
                                <input type="month" class="form-control" id="mois_rapport" name="mois" 
                                       value="{{ now()->format('Y-m') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="comparaison_mois" class="form-label">
                                    <i class="fas fa-balance-scale me-1"></i>
                                    Comparaison avec
                                </label>
                                <select class="form-select" id="comparaison_mois" name="comparaison">
                                    <option value="mois_precedent">Mois précédent</option>
                                    <option value="meme_mois_annee_precedente">Même mois année précédente</option>
                                    <option value="moyenne_6_mois">Moyenne 6 derniers mois</option>
                                    <option value="objectif_mensuel">Objectif mensuel</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-chart-line me-1"></i>
                            Sections à inclure
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="resume_executif" id="sect_resume" name="sections[]" checked>
                                    <label class="form-check-label" for="sect_resume">
                                        Résumé exécutif
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="recettes_detaillees" id="sect_recettes" name="sections[]" checked>
                                    <label class="form-check-label" for="sect_recettes">
                                        Recettes détaillées
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="performance_zones" id="sect_zones" name="sections[]" checked>
                                    <label class="form-check-label" for="sect_zones">
                                        Performance par zones
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="performance_collecteurs" id="sect_collecteurs" name="sections[]" checked>
                                    <label class="form-check-label" for="sect_collecteurs">
                                        Performance collecteurs
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="analyse_taxes" id="sect_taxes" name="sections[]" checked>
                                    <label class="form-check-label" for="sect_taxes">
                                        Analyse par taxes
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="evolution_tendances" id="sect_evolution" name="sections[]" checked>
                                    <label class="form-check-label" for="sect_evolution">
                                        Évolution et tendances
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="objectifs_realises" id="sect_objectifs" name="sections[]" checked>
                                    <label class="form-check-label" for="sect_objectifs">
                                        Objectifs vs Réalisé
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="previsions_mois_suivant" id="sect_previsions" name="sections[]" checked>
                                    <label class="form-check-label" for="sect_previsions">
                                        Prévisions mois suivant
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-chart-bar me-1"></i>
                            Graphiques mensuels
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="evolution_quotidienne" id="graph_quotidien" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_quotidien">
                                        Évolution quotidienne
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="repartition_semaines" id="graph_semaines" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_semaines">
                                        Répartition par semaines
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="top_jours" id="graph_jours" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_jours">
                                        Top jours de collecte
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="comparaison_mensuelle" id="graph_comparaison" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_comparaison">
                                        Comparaison mensuelle
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="objectifs_gauge" id="graph_gauge" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_gauge">
                                        Jauge d'objectifs
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="heatmap_activite" id="graph_heatmap" name="graphiques[]">
                                    <label class="form-check-label" for="graph_heatmap">
                                        Heatmap d'activité
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger">
                        <i class="fas fa-calendar-check me-2"></i>
                        <strong>Rapport Mensuel :</strong> Bilan complet des activités du mois avec comparaisons, 
                        analyse des performances et prévisions pour le mois suivant.
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="display: flex !important; justify-content: space-between !important; padding: 1rem !important; border-top: 1px solid #dee2e6 !important; background-color: #f8f9fa !important;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.5rem 1rem !important; font-size: 1rem !important; font-weight: 600 !important; border: 1px solid #6c757d !important; border-radius: 0.375rem !important; background-color: #6c757d !important; color: white !important; cursor: pointer !important;">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <button type="button" class="btn btn-danger" onclick="generateMensuelReport('html')" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.5rem 1rem !important; font-size: 1rem !important; font-weight: 600 !important; border: 1px solid #dc3545 !important; border-radius: 0.375rem !important; background-color: #dc3545 !important; color: white !important; cursor: pointer !important; box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3) !important;">
                    <i class="fas fa-file-alt me-1"></i>
                    Générer le rapport
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function generateMensuelReport(format) {
    const form = document.getElementById('formMensuel');
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
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalMensuel'));
    modal.hide();
}
</script> 