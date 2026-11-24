<!-- Modal Carte Thématique -->
<div class="modal fade" id="modalCarte" tabindex="-1" aria-labelledby="modalCarteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-height: 90vh !important; margin: 20px auto !important;">
        <div class="modal-content" style="max-height: 85vh !important;">
            <div class="modal-header bg-success text-white" style="padding: 10px 15px !important;">
                <h5 class="modal-title" id="modalCarteLabel" style="font-size: 1.1rem !important; margin: 0 !important;">
                    <i class="fas fa-map-marked-alt me-2"></i>
                    Carte Thématique Géospatiale
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 60vh !important; overflow-y: auto !important; padding: 15px !important;">
                <form id="formCarte" method="GET" action="{{ route('rapports.carte') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="date_debut_carte" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de début
                                </label>
                                <input type="date" class="form-control form-control-sm" id="date_debut_carte" name="date_debut" 
                                       value="{{ now()->startOfMonth()->format('Y-m-d') }}" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="date_fin_carte" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de fin
                                </label>
                                <input type="date" class="form-control form-control-sm" id="date_fin_carte" name="date_fin" 
                                       value="{{ now()->endOfMonth()->format('Y-m-d') }}" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="zone_carte" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                    <i class="fas fa-map-marked-alt me-1"></i>
                                    Zone fiscale (optionnel)
                                </label>
                                <select class="form-select form-select-sm" id="zone_carte" name="zone_id" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                                    <option value="">Toutes les zones</option>
                                    @foreach(App\Models\ZoneFiscale::all() as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="statut_paiement_carte" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                    <i class="fas fa-money-check-alt me-1"></i>
                                    Statut de paiement
                                </label>
                                <select class="form-select form-select-sm" id="statut_paiement_carte" name="statut_paiement" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                                    <option value="tous">Tous les statuts</option>
                                    <option value="payant">Payant</option>
                                    <option value="non_payant">Non payant</option>
                                    <option value="partiel">Paiement partiel</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                            <i class="fas fa-layer-group me-1"></i>
                            Couches de la carte
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="unites_commerciales" id="carte_unites" name="couches[]" checked>
                                    <label class="form-check-label" for="carte_unites" style="font-size: 0.85rem !important;">
                                        Unités commerciales
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="zones_fiscales" id="carte_zones" name="couches[]" checked>
                                    <label class="form-check-label" for="carte_zones" style="font-size: 0.85rem !important;">
                                        Zones fiscales
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="collecteurs" id="carte_collecteurs" name="couches[]">
                                    <label class="form-check-label" for="carte_collecteurs" style="font-size: 0.85rem !important;">
                                        Collecteurs
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="statut_paiement" id="carte_statut" name="couches[]" checked>
                                    <label class="form-check-label" for="carte_statut" style="font-size: 0.85rem !important;">
                                        Statut de paiement
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="types_commerce" id="carte_types" name="couches[]">
                                    <label class="form-check-label" for="carte_types" style="font-size: 0.85rem !important;">
                                        Types de commerce
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="densite" id="carte_densite" name="couches[]">
                                    <label class="form-check-label" for="carte_densite" style="font-size: 0.85rem !important;">
                                        Densité commerciale
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                            <i class="fas fa-palette me-1"></i>
                            Style de la carte
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="standard" id="style_standard" name="style_carte" checked>
                                    <label class="form-check-label" for="style_standard" style="font-size: 0.85rem !important;">
                                        <i class="fas fa-map me-1"></i>
                                        Standard
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="satellite" id="style_satellite" name="style_carte">
                                    <label class="form-check-label" for="style_satellite" style="font-size: 0.85rem !important;">
                                        <i class="fas fa-satellite me-1"></i>
                                        Satellite
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="terrain" id="style_terrain" name="style_carte">
                                    <label class="form-check-label" for="style_terrain" style="font-size: 0.85rem !important;">
                                        <i class="fas fa-mountain me-1"></i>
                                        Terrain
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="dark" id="style_dark" name="style_carte">
                                    <label class="form-check-label" for="style_dark" style="font-size: 0.85rem !important;">
                                        <i class="fas fa-moon me-1"></i>
                                        Mode sombre
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                            <i class="fas fa-download me-1"></i>
                            Format d'export
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="html" id="format_html" name="format" checked>
                                    <label class="form-check-label" for="format_html" style="font-size: 0.85rem !important;">
                                        <i class="fas fa-globe me-1"></i>
                                        Vue interactive (HTML)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="pdf" id="format_pdf" name="format">
                                    <label class="form-check-label" for="format_pdf" style="font-size: 0.85rem !important;">
                                        <i class="fas fa-file-pdf me-1"></i>
                                        Document PDF
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info" style="padding: 8px 12px !important; font-size: 0.85rem !important; margin-bottom: 10px !important;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Fonctionnalités de la carte :</strong>
                        <ul class="mb-0 mt-2" style="font-size: 0.8rem !important;">
                            <li>Visualisation géospatiale des unités commerciales</li>
                            <li>Statut de paiement par couleur (vert = payant, rouge = non payant)</li>
                            <li>Filtres par zone et type de commerce</li>
                            <li>Informations détaillées au clic sur les marqueurs</li>
                            <li>Export en PDF avec légende complète</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="display: flex !important; justify-content: space-between !important; padding: 10px 15px !important; border-top: 1px solid #dee2e6 !important; background-color: #f8f9fa !important; position: sticky !important; bottom: 0 !important; z-index: 1000 !important;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 6px 12px !important; font-size: 0.9rem !important; font-weight: 600 !important; border: 1px solid #6c757d !important; border-radius: 0.375rem !important; background-color: #6c757d !important; color: white !important; cursor: pointer !important;">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <button type="submit" form="formCarte" class="btn btn-success btn-sm" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 6px 12px !important; font-size: 0.9rem !important; font-weight: 600 !important; border: 1px solid #198754 !important; border-radius: 0.375rem !important; background-color: #198754 !important; color: white !important; cursor: pointer !important; box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3) !important;">
                    <i class="fas fa-map-marked-alt me-1"></i>
                    Générer la carte
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Script pour gérer les styles de carte dans le modal
document.addEventListener('DOMContentLoaded', function() {
    // Écouter les changements de style dans le modal
    const styleInputs = document.querySelectorAll('input[name="style_carte"]');
    styleInputs.forEach(input => {
        input.addEventListener('change', function() {
            console.log('Style sélectionné:', this.value);
            // Le style sera appliqué quand la carte sera générée
        });
    });
    
    // Écouter la soumission du formulaire
    const formCarte = document.getElementById('formCarte');
    if (formCarte) {
        formCarte.addEventListener('submit', function(e) {
            // Récupérer le style sélectionné
            const selectedStyle = document.querySelector('input[name="style_carte"]:checked').value;
            console.log('Génération de la carte avec le style:', selectedStyle);
            
            // Ajouter le style au formulaire si pas déjà présent
            let styleInput = formCarte.querySelector('input[name="style"]');
            if (!styleInput) {
                styleInput = document.createElement('input');
                styleInput.type = 'hidden';
                styleInput.name = 'style';
                formCarte.appendChild(styleInput);
            }
            styleInput.value = selectedStyle;
        });
    }
});
</script> 