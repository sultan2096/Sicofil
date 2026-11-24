<!-- Modal Rapport Nature + Type Commerce -->
<div class="modal fade" id="modalNatureTypeCommerce" tabindex="-1" aria-labelledby="modalNatureTypeCommerceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalNatureTypeCommerceLabel">
                    <i class="fas fa-layer-group me-2"></i>
                    Rapport combiné Nature et Type de Commerce
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNatureTypeCommerce" method="GET" action="{{ route('rapports.nature-type-commerce') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_debut_nature_type" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de début
                                </label>
                                <input type="date" class="form-control" id="date_debut_nature_type" name="date_debut"
                                       value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_fin_nature_type" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Date de fin
                                </label>
                                <input type="date" class="form-control" id="date_fin_nature_type" name="date_fin"
                                       value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nature_nature_type" class="form-label">
                                    <i class="fas fa-industry me-1"></i>
                                    Nature (optionnel)
                                </label>
                                <select class="form-select" id="nature_nature_type" name="nature">
                                    <option value="">Toutes les natures</option>
                                    @foreach(App\Models\UniteCommerciale::select('nature')->distinct()->whereNotNull('nature')->pluck('nature') as $n)
                                        <option value="{{ $n }}">{{ $n }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type_commerce_nature_type" class="form-label">
                                    <i class="fas fa-shopping-cart me-1"></i>
                                    Type de commerce (optionnel)
                                </label>
                                <select class="form-select" id="type_commerce_nature_type" name="type_commerce">
                                    <option value="">Tous les types</option>
                                    @foreach(App\Models\UniteCommerciale::select('type_commerce')->distinct()->whereNotNull('type_commerce')->pluck('type_commerce') as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="zone_id_nature_type" class="form-label">
                                    <i class="fas fa-map-marked-alt me-1"></i>
                                    Zone fiscale (optionnel)
                                </label>
                                <select class="form-select" id="zone_id_nature_type" name="zone_id">
                                    <option value="">Toutes les zones</option>
                                    @foreach(App\Models\ZoneFiscale::all() as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Rapport Nature + Type :</strong> Analyse croisée des recettes par nature et type de commerce,
                        avec décompte des unités, paiements et montants collectés.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <button type="button" class="btn btn-secondary" onclick="generateNatureTypeReport('html')">
                    <i class="fas fa-file-alt me-1"></i>
                    Générer le rapport
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function generateNatureTypeReport(format) {
    const form = document.getElementById('formNatureTypeCommerce');
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

    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNatureTypeCommerce'));
    if (modal) modal.hide();
}
</script>
