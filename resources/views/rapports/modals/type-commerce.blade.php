<!-- Modal Rapport par Type de Commerce -->
<div class="modal fade" id="modalTypeCommerce" tabindex="-1" aria-labelledby="modalTypeCommerceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTypeCommerceLabel">
                    <i class="fas fa-tags me-2"></i>
                    Rapport par Type de Commerce
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rapports.type-commerce') }}" method="GET" target="_blank">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_debut_type" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Date de début
                            </label>
                            <input type="date" class="form-control" id="date_debut_type" name="date_debut" 
                                   value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_fin_type" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Date de fin
                            </label>
                            <input type="date" class="form-control" id="date_fin_type" name="date_fin" 
                                   value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type_specifique" class="form-label">
                                <i class="fas fa-filter me-1"></i>
                                Type spécifique (optionnel)
                            </label>
                            <select class="form-select" id="type_specifique" name="type_commerce">
                                <option value="">Tous les types</option>
                                @if(\App\Models\TypeCommerce::count() > 0)
                                    @foreach(\App\Models\TypeCommerce::orderBy('nom')->get() as $type)
                                        <option value="{{ $type->nom }}">
                                            @if($type->is_transport)
                                                🚗 {{ $type->nom }}
                                            @else
                                                🏪 {{ $type->nom }}
                                            @endif
                                        </option>
                                    @endforeach
                                @else
                                    <option value="Alimentation générale">🏪 Alimentation générale</option>
                                    <option value="Restaurant/Gargotte">🍽️ Restaurant/Gargotte</option>
                                    <option value="Salon de coiffure">💇 Salon de coiffure</option>
                                    <option value="Couture/Tailleur">✂️ Couture/Tailleur</option>
                                    <option value="Mécanique/Garage">🔧 Mécanique/Garage</option>
                                    <option value="Pharmacie">💊 Pharmacie</option>
                                    <option value="Boulangerie">🥖 Boulangerie</option>
                                    <option value="Quincaillerie">🔨 Quincaillerie</option>
                                    <option value="Télécentre">📞 Télécentre</option>
                                    <option value="Transport">🚗 Transport</option>
                                    <option value="Vêtements">👕 Vêtements</option>
                                    <option value="Électronique">📱 Électronique</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="zone_type" class="form-label">
                                <i class="fas fa-map-marked-alt me-1"></i>
                                Zone fiscale (optionnel)
                            </label>
                            <select class="form-select" id="zone_type" name="zone_id">
                                <option value="">Toutes les zones</option>
                                @foreach(\App\Models\ZoneFiscale::orderBy('nom')->get() as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="categorie_type" class="form-label">
                                <i class="fas fa-layer-group me-1"></i>
                                Catégorie d'activité
                            </label>
                            <select class="form-select" id="categorie_type" name="categorie">
                                <option value="">Toutes les catégories</option>
                                <option value="activite_commerciale">Activités commerciales</option>
                                <option value="point_interet">Points d'intérêt</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="is_transport_type" class="form-label">
                                <i class="fas fa-truck me-1"></i>
                                Filtre transport
                            </label>
                            <select class="form-select" id="is_transport_type" name="is_transport">
                                <option value="">Tous (transport et non-transport)</option>
                                <option value="1">Activités de transport uniquement</option>
                                <option value="0">Activités non-transport uniquement</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="format_type" class="form-label">
                                <i class="fas fa-file me-1"></i>
                                Format de sortie
                            </label>
                            <select class="form-select" id="format_type" name="format">
                                <option value="html">Aperçu HTML</option>
                                <option value="pdf">Document PDF</option>
                                <option value="excel">Fichier Excel</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="niveau_detail_type" class="form-label">
                                <i class="fas fa-layer-group me-1"></i>
                                Niveau de détail
                            </label>
                            <select class="form-select" id="niveau_detail_type" name="niveau_detail">
                                <option value="standard">Standard</option>
                                <option value="detaille">Détaillé</option>
                                <option value="complet">Complet avec graphiques</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Contenu du rapport
                        </h6>
                        <ul class="mb-0 small">
                            <li>Répartition des unités commerciales par type de commerce</li>
                            <li>Recettes générées par type spécifique</li>
                            <li>Analyse comparative transport vs non-transport</li>
                            <li>Performance par catégorie d'activité</li>
                            <li>Évolution temporelle des différents types</li>
                            <li>Graphiques de distribution et tendances</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer" style="display: flex !important; justify-content: space-between !important; padding: 1rem !important; border-top: 1px solid #dee2e6 !important; background-color: #f8f9fa !important;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.5rem 1rem !important; font-size: 1rem !important; font-weight: 600 !important; border: 1px solid #6c757d !important; border-radius: 0.375rem !important; background-color: #6c757d !important; color: white !important; cursor: pointer !important;">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.5rem 1rem !important; font-size: 1rem !important; font-weight: 600 !important; border: 1px solid #0d6efd !important; border-radius: 0.375rem !important; background-color: #0d6efd !important; color: white !important; cursor: pointer !important; box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3) !important;">
                        <i class="fas fa-chart-line me-1"></i>
                        Générer le rapport
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 