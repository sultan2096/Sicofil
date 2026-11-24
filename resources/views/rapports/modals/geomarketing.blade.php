<!-- Modal Géomarketing -->
<div class="modal fade" id="modalGeomarketing" tabindex="-1" aria-labelledby="modalGeomarketingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-height: 90vh !important; margin: 20px auto !important;">
        <div class="modal-content" style="max-height: 85vh !important;">
            <div class="modal-header bg-primary text-white" style="padding: 10px 15px !important;">
                <h5 class="modal-title" id="modalGeomarketingLabel" style="font-size: 1.1rem !important; margin: 0 !important;">
                    <i class="fas fa-chart-line me-2"></i>
                    Rapport Géomarketing - Analyse Géospatiale
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 60vh !important; overflow-y: auto !important; padding: 15px !important;">
                <form id="formGeomarketing" method="GET" action="{{ route('rapports.geomarketing') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="date_debut_geomarketing" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de début
                                </label>
                                <input type="date" class="form-control form-control-sm" id="date_debut_geomarketing" name="date_debut" 
                                       value="{{ now()->startOfMonth()->format('Y-m-d') }}" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="date_fin_geomarketing" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de fin
                                </label>
                                <input type="date" class="form-control form-control-sm" id="date_fin_geomarketing" name="date_fin" 
                                       value="{{ now()->endOfMonth()->format('Y-m-d') }}" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="zone_geomarketing" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                    <i class="fas fa-map-marked-alt me-1"></i>
                                    Zone d'analyse
                                </label>
                                <select class="form-select form-select-sm" id="zone_geomarketing" name="zone_id" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                                    <option value="">Toutes les zones</option>
                                    @foreach(App\Models\ZoneFiscale::all() as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="nature_activite_geomarketing" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                    <i class="fas fa-industry me-1"></i>
                                    Nature d'activité
                                </label>
                                <select class="form-select form-select-sm" id="nature_activite_geomarketing" name="nature_id" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                                    <option value="">Toutes les natures</option>
                                    @foreach(App\Models\NatureActivite::all() as $nature)
                                        <option value="{{ $nature->id }}">{{ $nature->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="type_commerce_geomarketing" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                    <i class="fas fa-shopping-cart me-1"></i>
                                    Type de commerce
                                </label>
                                <select class="form-select form-select-sm" id="type_commerce_geomarketing" name="type_id" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                                    <option value="">Tous les types</option>
                                    @foreach(App\Models\TypeCommerce::all() as $type)
                                        <option value="{{ $type->id }}">{{ $type->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label for="seuil_score_geomarketing" class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Seuil de score minimum
                                </label>
                                <select class="form-select form-select-sm" id="seuil_score_geomarketing" name="seuil_score" style="padding: 6px 10px !important; font-size: 0.9rem !important;">
                                    <option value="0">Tous les scores</option>
                                    <option value="75">Excellence (75-100)</option>
                                    <option value="60">Prometteur (60-74)</option>
                                    <option value="45">Modéré (45-59)</option>
                                    <option value="0">Faible (0-44)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                            <i class="fas fa-layer-group me-1"></i>
                            Couches d'analyse
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="population" id="geomarketing_population" name="couches[]" checked>
                                    <label class="form-check-label" for="geomarketing_population" style="font-size: 0.85rem !important;">
                                        Analyse de population
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="routes" id="geomarketing_routes" name="couches[]" checked>
                                    <label class="form-check-label" for="geomarketing_routes" style="font-size: 0.85rem !important;">
                                        Réseau routier
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="bati" id="geomarketing_bati" name="couches[]" checked>
                                    <label class="form-check-label" for="geomarketing_bati" style="font-size: 0.85rem !important;">
                                        Bâti et infrastructure
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="heatmap" id="geomarketing_heatmap" name="couches[]">
                                    <label class="form-check-label" for="geomarketing_heatmap" style="font-size: 0.85rem !important;">
                                        Heatmap de densité
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="opportunites" id="geomarketing_opportunites" name="couches[]" checked>
                                    <label class="form-check-label" for="geomarketing_opportunites" style="font-size: 0.85rem !important;">
                                        Zones d'opportunités
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="checkbox" value="propositions" id="geomarketing_propositions" name="couches[]" checked>
                                    <label class="form-check-label" for="geomarketing_propositions" style="font-size: 0.85rem !important;">
                                        Propositions d'investissement
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                            <i class="fas fa-chart-pie me-1"></i>
                            Type de rapport
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="executif" id="rapport_executif" name="type_rapport" checked>
                                    <label class="form-check-label" for="rapport_executif" style="font-size: 0.85rem !important;">
                                        Rapport exécutif
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="detail" id="rapport_detail" name="type_rapport">
                                    <label class="form-check-label" for="rapport_detail" style="font-size: 0.85rem !important;">
                                        Rapport détaillé
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="carte" id="rapport_carte" name="type_rapport">
                                    <label class="form-check-label" for="rapport_carte" style="font-size: 0.85rem !important;">
                                        Carte interactive
                                    </label>
                                </div>
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="complet" id="rapport_complet" name="type_rapport">
                                    <label class="form-check-label" for="rapport_complet" style="font-size: 0.85rem !important;">
                                        Rapport complet
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.9rem !important; margin-bottom: 4px !important;">
                            <i class="fas fa-file-export me-1"></i>
                            Format d'export
                        </label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="pdf" id="format_pdf" name="format" checked>
                                    <label class="form-check-label" for="format_pdf" style="font-size: 0.85rem !important;">
                                        PDF
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="excel" id="format_excel" name="format">
                                    <label class="form-check-label" for="format_excel" style="font-size: 0.85rem !important;">
                                        Excel
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check" style="margin-bottom: 4px !important;">
                                    <input class="form-check-input" type="radio" value="html" id="format_html" name="format">
                                    <label class="form-check-label" for="format_html" style="font-size: 0.85rem !important;">
                                        HTML
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info" style="font-size: 0.85rem !important; padding: 8px 12px !important; margin-bottom: 15px !important;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Analyse Géomarketing :</strong> Ce rapport analyse les opportunités d'investissement basées sur la densité commerciale, 
                        l'accessibilité routière, le potentiel bâti et la population de la commune de Sandiara.
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="padding: 10px 15px !important;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="font-size: 0.9rem !important;">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <button type="submit" form="formGeomarketing" class="btn btn-primary btn-sm" style="font-size: 0.9rem !important;">
                    <i class="fas fa-chart-line me-1"></i>
                    Générer le rapport
                </button>
            </div>
        </div>
    </div>
</div>



