<!-- Modal Rapport Financier -->
<div class="modal fade" id="modalFinancier" tabindex="-1" aria-labelledby="modalFinancierLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalFinancierLabel">
                    <i class="fas fa-chart-pie me-2"></i>
                    Rapport Financier avec Diagrammes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formFinancier" method="GET" action="{{ route('rapports.financier') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_debut_financier" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de début
                                </label>
                                <input type="date" class="form-control" id="date_debut_financier" name="date_debut" 
                                       value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_fin_financier" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de fin
                                </label>
                                <input type="date" class="form-control" id="date_fin_financier" name="date_fin" 
                                       value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="zone_financier" class="form-label">
                                    <i class="fas fa-map-marked-alt me-1"></i>
                                    Zone fiscale
                                </label>
                                <select class="form-select" id="zone_financier" name="zone_id">
                                    <option value="">Toutes les zones</option>
                                    @foreach(App\Models\ZoneFiscale::all() as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type_analyse" class="form-label">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Type d'analyse
                                </label>
                                <select class="form-select" id="type_analyse" name="type_analyse">
                                    <option value="complete">Analyse complète</option>
                                    <option value="recettes">Évolution des recettes</option>
                                    <option value="zones">Performance par zones</option>
                                    <option value="taxes">Répartition par taxes</option>
                                    <option value="tendances">Tendances et projections</option>
                                </select>
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
                                    <input class="form-check-input" type="checkbox" value="evolution" id="graph_evolution" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_evolution">
                                        Évolution des recettes
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="zones" id="graph_zones" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_zones">
                                        Répartition par zones
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="taxes" id="graph_taxes" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_taxes">
                                        Camembert des taxes
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="collecteurs" id="graph_collecteurs" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_collecteurs">
                                        Performance collecteurs
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="unites" id="graph_unites" name="graphiques[]" checked>
                                    <label class="form-check-label" for="graph_unites">
                                        Unités par zone
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="comparatif" id="graph_comparatif" name="graphiques[]">
                                    <label class="form-check-label" for="graph_comparatif">
                                        Comparatif mensuel
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Rapport Financier :</strong> Ce rapport inclut des analyses visuelles complètes avec graphiques, 
                        camemberts et tableaux de bord pour une présentation professionnelle destinée au maire et aux décideurs.
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="display: flex !important; justify-content: space-between !important; padding: 1rem !important; border-top: 1px solid #dee2e6 !important; background-color: #f8f9fa !important;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.5rem 1rem !important; font-size: 1rem !important; font-weight: 600 !important; border: 1px solid #6c757d !important; border-radius: 0.375rem !important; background-color: #6c757d !important; color: white !important; cursor: pointer !important;">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <div class="btn-group" style="display: flex !important; gap: 0.5rem !important;">
                    <button type="button" class="btn btn-success" onclick="generateFinancierReport('html')" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.5rem 1rem !important; font-size: 1rem !important; font-weight: 600 !important; border: 1px solid #198754 !important; border-radius: 0.375rem !important; background-color: #198754 !important; color: white !important; cursor: pointer !important; box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3) !important;">
                        <i class="fas fa-eye me-1"></i>
                        Aperçu
                    </button>
                    <button type="button" class="btn btn-danger" onclick="generateFinancierReport('pdf')" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.5rem 1rem !important; font-size: 1rem !important; font-weight: 600 !important; border: 1px solid #dc3545 !important; border-radius: 0.375rem !important; background-color: #dc3545 !important; color: white !important; cursor: pointer !important; box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3) !important;">
                        <i class="fas fa-file-pdf me-1"></i>
                        PDF
                    </button>
                    <button type="button" class="btn btn-info" onclick="generateFinancierReport('excel')" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.5rem 1rem !important; font-size: 1rem !important; font-weight: 600 !important; border: 1px solid #0dcaf0 !important; border-radius: 0.375rem !important; background-color: #0dcaf0 !important; color: white !important; cursor: pointer !important; box-shadow: 0 2px 4px rgba(13, 202, 240, 0.3) !important;">
                        <i class="fas fa-file-excel me-1"></i>
                        Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateFinancierReport(format) {
    const form = document.getElementById('formFinancier');
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
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalFinancier'));
    modal.hide();
}
</script> 