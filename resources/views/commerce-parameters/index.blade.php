@extends('layouts.app')

@section('title', 'Gestion des Paramètres de Commerce')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-primary">
                    <i class="fas fa-cogs"></i> Gestion des Paramètres de Commerce
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('gestionnaire.dashboard') }}">Tableau de bord</a></li>
                        <li class="breadcrumb-item active">Paramètres Commerce</li>
                    </ol>
                </nav>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Navigation par onglets -->
            <ul class="nav nav-tabs" id="parametersTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="natures-tab" data-bs-toggle="tab" data-bs-target="#natures" type="button" role="tab">
                        <i class="fas fa-layer-group"></i> Natures d'Activité
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="types-tab" data-bs-toggle="tab" data-bs-target="#types" type="button" role="tab">
                        <i class="fas fa-tags"></i> Types de Commerce
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="parametersTabContent">
                <!-- Onglet Natures d'Activité -->
                <div class="tab-pane fade show active" id="natures" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-layer-group"></i> Natures d'Activité</h5>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addNatureModal">
                                <i class="fas fa-plus"></i> Ajouter une Nature
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Nom</th>
                                            <th>Catégorie</th>
                                            <th>Description</th>
                                            <th>Types liés</th>
                                            <th>Ordre</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($natures as $nature)
                                        <tr>
                                            <td><strong>{{ $nature->nom }}</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $nature->categorie == 'activite_commerciale' ? 'primary' : 'info' }}">
                                                    {{ $nature->categorie == 'activite_commerciale' ? 'Activité Commerciale' : 'Point d\'Intérêt' }}
                                                </span>
                                            </td>
                                            <td>{{ $nature->description ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $nature->typesCommerce->count() }} types</span>
                                            </td>
                                            <td>{{ $nature->ordre }}</td>
                                            <td>
                                                <span class="badge bg-{{ $nature->actif ? 'success' : 'danger' }}">
                                                    {{ $nature->actif ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="editNature({{ $nature->id }}, '{{ $nature->nom }}', '{{ $nature->categorie }}', '{{ $nature->description }}', {{ $nature->ordre }}, {{ $nature->actif ? 'true' : 'false' }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if($nature->typesCommerce->count() == 0 && $nature->unitesCommerciales->count() == 0)
                                                <form method="POST" action="{{ route('commerce-parameters.nature.destroy', $nature) }}" style="display: inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette nature ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Aucune nature d'activité trouvée</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Types de Commerce -->
                <div class="tab-pane fade" id="types" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-tags"></i> Types de Commerce</h5>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                                <i class="fas fa-plus"></i> Ajouter un Type
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Nom</th>
                                            <th>Nature</th>
                                            <th>Description</th>
                                            <th>Transport</th>
                                            <th>Ordre</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($types as $type)
                                        <tr>
                                            <td><strong>{{ $type->nom }}</strong></td>
                                            <td>
                                                @if($type->natureActivite)
                                                <span class="badge bg-primary">{{ $type->natureActivite->nom }}</span>
                                                @else
                                                <span class="badge bg-warning">Non définie</span>
                                                @endif
                                            </td>
                                            <td>{{ $type->description ?? '-' }}</td>
                                            <td>
                                                @if($type->is_transport)
                                                <span class="badge bg-warning"><i class="fas fa-truck"></i> Transport</span>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $type->ordre }}</td>
                                            <td>
                                                <span class="badge bg-{{ $type->actif ? 'success' : 'danger' }}">
                                                    {{ $type->actif ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="editType({{ $type->id }}, '{{ $type->nom }}', {{ $type->nature_activite_id ?? 'null' }}, '{{ $type->description }}', {{ $type->is_transport ? 'true' : 'false' }}, {{ $type->ordre }}, {{ $type->actif ? 'true' : 'false' }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if($type->unites->count() == 0)
                                                <form method="POST" action="{{ route('commerce-parameters.type.destroy', $type) }}" style="display: inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce type ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Aucun type de commerce trouvé</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une nature -->
<div class="modal fade" id="addNatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Ajouter une Nature d'Activité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('commerce-parameters.nature.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nature_nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nature_nom" name="nom" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="nature_categorie" class="form-label">Catégorie <span class="text-danger">*</span></label>
                        <select class="form-select" id="nature_categorie" name="categorie" required>
                            <option value="">-- Sélectionner --</option>
                            <option value="activite_commerciale">Activité Commerciale</option>
                            <option value="point_interet">Point d'Intérêt</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nature_description" class="form-label">Description</label>
                        <textarea class="form-control" id="nature_description" name="description" rows="3" maxlength="255"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="nature_ordre" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="nature_ordre" name="ordre" min="0" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour modifier une nature -->
<div class="modal fade" id="editNatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier la Nature d'Activité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editNatureForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nature_nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nature_nom" name="nom" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="edit_nature_categorie" class="form-label">Catégorie <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_nature_categorie" name="categorie" required>
                            <option value="activite_commerciale">Activité Commerciale</option>
                            <option value="point_interet">Point d'Intérêt</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nature_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_nature_description" name="description" rows="3" maxlength="255"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nature_ordre" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="edit_nature_ordre" name="ordre" min="0">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_nature_actif" name="actif">
                        <label class="form-check-label" for="edit_nature_actif">
                            Nature active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un type -->
<div class="modal fade" id="addTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Ajouter un Type de Commerce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('commerce-parameters.type.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type_nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="type_nom" name="nom" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="type_nature" class="form-label">Nature d'Activité <span class="text-danger">*</span></label>
                        <select class="form-select" id="type_nature" name="nature_activite_id" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($natures->where('actif', true) as $nature)
                            <option value="{{ $nature->id }}">{{ $nature->nom }} ({{ ucfirst($nature->categorie) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="type_description" class="form-label">Description</label>
                        <textarea class="form-control" id="type_description" name="description" rows="3" maxlength="255"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="type_ordre" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="type_ordre" name="ordre" min="0" value="0">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="type_transport" name="is_transport">
                        <label class="form-check-label" for="type_transport">
                            Lié au transport
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour modifier un type -->
<div class="modal fade" id="editTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier le Type de Commerce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editTypeForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_type_nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_type_nom" name="nom" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="edit_type_nature" class="form-label">Nature d'Activité <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_type_nature" name="nature_activite_id" required>
                            @foreach($natures->where('actif', true) as $nature)
                            <option value="{{ $nature->id }}">{{ $nature->nom }} ({{ ucfirst($nature->categorie) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_type_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_type_description" name="description" rows="3" maxlength="255"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_type_ordre" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="edit_type_ordre" name="ordre" min="0">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="edit_type_transport" name="is_transport">
                        <label class="form-check-label" for="edit_type_transport">
                            Lié au transport
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_type_actif" name="actif">
                        <label class="form-check-label" for="edit_type_actif">
                            Type actif
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editNature(id, nom, categorie, description, ordre, actif) {
    document.getElementById('edit_nature_nom').value = nom;
    document.getElementById('edit_nature_categorie').value = categorie;
    document.getElementById('edit_nature_description').value = description || '';
    document.getElementById('edit_nature_ordre').value = ordre;
    document.getElementById('edit_nature_actif').checked = actif;
    
    document.getElementById('editNatureForm').action = `/commerce-parameters/nature/${id}`;
    
    new bootstrap.Modal(document.getElementById('editNatureModal')).show();
}

function editType(id, nom, natureId, description, isTransport, ordre, actif) {
    document.getElementById('edit_type_nom').value = nom;
    document.getElementById('edit_type_nature').value = natureId || '';
    document.getElementById('edit_type_description').value = description || '';
    document.getElementById('edit_type_transport').checked = isTransport;
    document.getElementById('edit_type_ordre').value = ordre;
    document.getElementById('edit_type_actif').checked = actif;
    
    document.getElementById('editTypeForm').action = `/commerce-parameters/type/${id}`;
    
    new bootstrap.Modal(document.getElementById('editTypeModal')).show();
}
</script>
@endsection 