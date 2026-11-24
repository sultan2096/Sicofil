<!-- Modal Synthèse Exécutive -->
<div class="modal fade" id="modalSynthese" tabindex="-1" aria-labelledby="modalSyntheseLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="modalSyntheseLabel">
                    <i class="fas fa-crown me-2"></i>
                    Synthèse Exécutive pour le Maire
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formSynthese" method="GET" action="{{ route('rapports.synthese') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="periode_synthese" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Période de synthèse
                                </label>
                                <select class="form-select" id="periode_synthese" name="periode">
                                    <option value="mois_courant">Mois en cours</option>
                                    <option value="trimestre_courant">Trimestre en cours</option>
                                    <option value="semestre_courant">Semestre en cours</option>
                                    <option value="annee_courante">Année en cours</option>
                                    <option value="12_derniers_mois">12 derniers mois</option>
                                    <option value="personnalisee">📅 Période personnalisée (choisir dates)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="niveau_detail" class="form-label">
                                    <i class="fas fa-layer-group me-1"></i>
                                    Niveau de détail
                                </label>
                                <select class="form-select" id="niveau_detail" name="niveau_detail">
                                    <option value="executif">Synthèse exécutive</option>
                                    <option value="detaille">Rapport détaillé</option>
                                    <option value="complet">Rapport complet</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="dates_personnalisees" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_debut_synthese" class="form-label">
                                    <i class="fas fa-calendar-day me-1"></i>
                                    Date de début
                                </label>
                                <input type="date" class="form-control" id="date_debut_synthese" name="date_debut" required>
                                <div class="form-text">Sélectionnez la date de début de la période</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_fin_synthese" class="form-label">
                                    <i class="fas fa-calendar-day me-1"></i>
                                    Date de fin
                                </label>
                                <input type="date" class="form-control" id="date_fin_synthese" name="date_fin" required>
                                <div class="form-text">Sélectionnez la date de fin de la période</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-chart-line me-1"></i>
                            Indicateurs clés à inclure
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="recettes_totales" id="ind_recettes" name="indicateurs[]" checked>
                                    <label class="form-check-label" for="ind_recettes">
                                        Recettes totales
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="evolution_mensuelle" id="ind_evolution" name="indicateurs[]" checked>
                                    <label class="form-check-label" for="ind_evolution">
                                        Évolution mensuelle
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="performance_zones" id="ind_zones" name="indicateurs[]" checked>
                                    <label class="form-check-label" for="ind_zones">
                                        Performance par zones
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="taux_recouvrement" id="ind_recouvrement" name="indicateurs[]" checked>
                                    <label class="form-check-label" for="ind_recouvrement">
                                        Taux de recouvrement
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="top_collecteurs" id="ind_collecteurs" name="indicateurs[]" checked>
                                    <label class="form-check-label" for="ind_collecteurs">
                                        Top collecteurs
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="repartition_taxes" id="ind_taxes" name="indicateurs[]" checked>
                                    <label class="form-check-label" for="ind_taxes">
                                        Répartition par taxes
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="tendances" id="ind_tendances" name="indicateurs[]" checked>
                                    <label class="form-check-label" for="ind_tendances">
                                        Tendances et projections
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="recommandations" id="ind_recommandations" name="indicateurs[]" checked>
                                    <label class="form-check-label" for="ind_recommandations">
                                        Recommandations
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-cog me-1"></i>
                            Options d'affichage
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="graphiques_executifs" id="opt_graphiques" name="options[]" checked>
                                    <label class="form-check-label" for="opt_graphiques">
                                        Graphiques exécutifs
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="tableau_bord" id="opt_tableau" name="options[]" checked>
                                    <label class="form-check-label" for="opt_tableau">
                                        Tableau de bord
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="comparaison_objectifs" id="opt_objectifs" name="options[]" checked>
                                    <label class="form-check-label" for="opt_objectifs">
                                        Comparaison aux objectifs
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="alertes" id="opt_alertes" name="options[]" checked>
                                    <label class="form-check-label" for="opt_alertes">
                                        Alertes et points d'attention
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-crown me-2"></i>
                        <strong>Synthèse Exécutive :</strong> Rapport de haut niveau destiné au maire et aux décideurs, 
                        avec indicateurs clés, tendances principales et recommandations stratégiques.
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="display: flex !important; justify-content: space-between !important; padding: 1rem !important; border-top: 1px solid #dee2e6 !important; background-color: #f8f9fa !important;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.5rem 1rem !important; font-size: 1rem !important; font-weight: 600 !important; border: 1px solid #6c757d !important; border-radius: 0.375rem !important; background-color: #6c757d !important; color: white !important; cursor: pointer !important;">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <button type="button" class="btn btn-warning" onclick="generateSyntheseReport('html')" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.5rem 1rem !important; font-size: 1rem !important; font-weight: 600 !important; border: 1px solid #ffc107 !important; border-radius: 0.375rem !important; background-color: #ffc107 !important; color: white !important; cursor: pointer !important; box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3) !important;">
                    <i class="fas fa-file-alt me-1"></i>
                    Générer le rapport
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion de l'affichage des dates personnalisées
document.getElementById('periode_synthese').addEventListener('change', function() {
    const datesDiv = document.getElementById('dates_personnalisees');
    const dateDebut = document.getElementById('date_debut_synthese');
    const dateFin = document.getElementById('date_fin_synthese');
    
    if (this.value === 'personnalisee') {
        datesDiv.style.display = 'block';
        
        // Définir des valeurs par défaut si elles sont vides
        if (!dateDebut.value) {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            dateDebut.value = firstDay.toISOString().split('T')[0];
        }
        
        if (!dateFin.value) {
            const today = new Date();
            dateFin.value = today.toISOString().split('T')[0];
        }
        
        // Validation des dates
        dateDebut.addEventListener('change', validateDates);
        dateFin.addEventListener('change', validateDates);
        
    } else {
        datesDiv.style.display = 'none';
        // Supprimer l'attribut required quand caché
        dateDebut.removeAttribute('required');
        dateFin.removeAttribute('required');
    }
});

function validateDates() {
    const dateDebut = document.getElementById('date_debut_synthese');
    const dateFin = document.getElementById('date_fin_synthese');
    
    if (dateDebut.value && dateFin.value) {
        if (new Date(dateDebut.value) > new Date(dateFin.value)) {
            dateFin.setCustomValidity('La date de fin doit être postérieure à la date de début');
        } else {
            dateFin.setCustomValidity('');
        }
    }
}

function generateSyntheseReport(format) {
    const form = document.getElementById('formSynthese');
    
    // Validation du formulaire
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
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
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalSynthese'));
    modal.hide();
}
</script> 