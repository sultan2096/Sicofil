<!-- Modal Rapport Comparatif -->
<div class="modal fade" id="modalComparatif" tabindex="-1" aria-labelledby="modalComparatifLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(45deg, #6f42c1, #5a2d91);">
                <h5 class="modal-title" id="modalComparatifLabel">
                    <i class="fas fa-balance-scale me-2"></i>
                    Rapport Comparatif
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formComparatif" method="GET" action="{{ route('rapports.comparatif') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="periode_base" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>
                                    Période de base
                                </label>
                                <select class="form-select" id="periode_base" name="periode_base">
                                    <option value="mois_courant">Mois en cours</option>
                                    <option value="trimestre_courant">Trimestre en cours</option>
                                    <option value="annee_courante">Année en cours</option>
                                    <option value="12_derniers_mois">12 derniers mois</option>
                                    <option value="personnalisee">Période personnalisée</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="periode_comparaison" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>
                                    Comparer avec
                                </label>
                                <select class="form-select" id="periode_comparaison" name="periode_comparaison">
                                    <option value="periode_precedente">Période précédente</option>
                                    <option value="meme_periode_annee_precedente">Même période année précédente</option>
                                    <option value="moyenne_historique">Moyenne historique</option>
                                    <option value="objectifs_budgetaires">Objectifs budgétaires</option>
                                    <option value="personnalisee">Période personnalisée</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert" style="background-color: #f8f9fa; border-color: #6f42c1; color: #6f42c1;">
                        <i class="fas fa-chart-bar me-2"></i>
                        <strong>Rapport Comparatif :</strong> Analyse comparative approfondie entre différentes périodes, 
                        zones et indicateurs avec calculs d'écarts, taux de croissance et tendances.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <button type="button" class="btn text-white" style="background: linear-gradient(45deg, #6f42c1, #5a2d91);" onclick="generateComparatifReport('html')">
                    <i class="fas fa-file-alt me-1"></i>
                    Générer le rapport
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function generateComparatifReport(format) {
    const form = document.getElementById('formComparatif');
    const formData = new FormData(form);
    formData.append('format', format);
    
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
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalComparatif'));
    modal.hide();
}
</script> 