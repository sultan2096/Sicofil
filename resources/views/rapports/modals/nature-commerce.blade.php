<!-- Modal Rapport par Nature de Commerce -->
<div class="modal fade" id="modalNatureCommerce" tabindex="-1" aria-labelledby="modalNatureCommerceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-height: 90vh !important; margin: 20px auto !important;">
        <div class="modal-content" style="max-height: 85vh !important;">
            <div class="modal-header bg-success text-white" style="padding: 10px 15px !important;">
                <h5 class="modal-title" id="modalNatureCommerceLabel" style="font-size: 1.1rem !important; margin: 0 !important;">
                    <i class="fas fa-industry me-2"></i>
                    Rapport par Nature de Commerce
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rapports.nature-commerce') }}" method="GET" target="_blank">
                <div class="modal-body" style="max-height: 60vh !important; overflow-y: auto !important; padding: 15px !important;">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="date_debut_nature" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Date de début
                            </label>
                            <input type="date" class="form-control form-control-sm" id="date_debut_nature" name="date_debut" 
                                   value="{{ now()->startOfMonth()->format('Y-m-d') }}" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="date_fin_nature" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Date de fin
                            </label>
                            <input type="date" class="form-control form-control-sm" id="date_fin_nature" name="date_fin" 
                                   value="{{ now()->format('Y-m-d') }}" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="nature_specifique" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                <i class="fas fa-filter me-1"></i>
                                Nature spécifique (optionnel)
                            </label>
                            <select class="form-select form-select-sm" id="nature_specifique" name="nature" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                                <option value="">Toutes les natures</option>
                                <option value="Boutique">Boutique</option>
                                <option value="Salon">Salon</option>
                                <option value="Marché">Marché</option>
                                <option value="Restaurant">Restaurant</option>
                                <option value="Atelier">Atelier</option>
                                <option value="Pharmacie">Pharmacie</option>
                                <option value="Station">Station</option>
                                <option value="Boulangerie">Boulangerie</option>
                                <option value="Épicerie">Épicerie</option>
                                <option value="Quincaillerie">Quincaillerie</option>
                                <option value="Cybercafé">Cybercafé</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="zone_nature" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                <i class="fas fa-map-marked-alt me-1"></i>
                                Zone fiscale (optionnel)
                            </label>
                            <select class="form-select form-select-sm" id="zone_nature" name="zone_id" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                                <option value="">Toutes les zones</option>
                                @foreach(\App\Models\ZoneFiscale::orderBy('nom')->get() as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="format_nature" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                <i class="fas fa-file me-1"></i>
                                Format de sortie
                            </label>
                            <select class="form-select form-select-sm" id="format_nature" name="format" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                                <option value="html">Aperçu HTML</option>
                                <option value="pdf">Document PDF</option>
                                <option value="excel">Fichier Excel</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="niveau_detail_nature" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                <i class="fas fa-layer-group me-1"></i>
                                Niveau de détail
                            </label>
                            <select class="form-select form-select-sm" id="niveau_detail_nature" name="niveau_detail" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                                <option value="standard">Standard</option>
                                <option value="detaille">Détaillé</option>
                                <option value="complet">Complet avec graphiques</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3" style="padding: 8px 12px !important; font-size: 0.85rem !important; margin-bottom: 10px !important;">
                        <h6 class="alert-heading" style="font-size: 0.9rem !important;">
                            <i class="fas fa-info-circle me-2"></i>
                            Contenu du rapport
                        </h6>
                        <ul class="mb-0 small" style="font-size: 0.8rem !important;">
                            <li>Répartition des unités commerciales par nature d'activité</li>
                            <li>Recettes générées par type de nature</li>
                            <li>Analyse comparative des performances</li>
                            <li>Taux de recouvrement par nature d'activité</li>
                            <li>Graphiques de répartition et tendances</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer" style="display: flex !important; justify-content: space-between !important; padding: 10px 15px !important; border-top: 1px solid #dee2e6 !important; background-color: #f8f9fa !important; position: sticky !important; bottom: 0 !important; z-index: 1000 !important;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 6px 12px !important; font-size: 0.9rem !important; font-weight: 600 !important; border: 1px solid #6c757d !important; border-radius: 0.375rem !important; background-color: #6c757d !important; color: white !important; cursor: pointer !important;">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-success btn-sm" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 6px 12px !important; font-size: 0.9rem !important; font-weight: 600 !important; border: 1px solid #198754 !important; border-radius: 0.375rem !important; background-color: #198754 !important; color: white !important; cursor: pointer !important; box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3) !important;">
                        <i class="fas fa-chart-bar me-1"></i>
                        Générer le rapport
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 