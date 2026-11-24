@extends('layouts.app')

@section('title', 'Gestion des Taxes - SICOSA')

@push('styles')
<style>
.search-highlight {
    background-color: #fff3cd !important;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: bold;
}

.advanced-search-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.voice-recording {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.filter-active {
    background-color: #d4edda !important;
    border-color: #c3e6cb !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-coins me-2"></i>
                @if(auth()->user()->role === 'maire')
                    Consultation des Taxes
                @else
                        Gestion des Taxes
                @endif
            </h1>
            <div class="text-muted small ms-1">
                @if(auth()->user()->role === 'maire')
                    Consultation des taxes appliquées aux unités commerciales (lecture seule)
                @else
                    Liste des taxes appliquées aux unités commerciales
                @endif
                    </div>
                </div>
        @if(auth()->user()->role === 'gestionnaire')
            <a href="{{ route('taxes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvelle taxe
            </a>
        @endif
    </div>
    <div class="card shadow-sm border-0">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Barre de recherche -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="searchTaxes" 
                                       placeholder="Rechercher une taxe par nom, montant ou périodicité..."
                                       autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle text-primary"></i>
                                Tapez pour rechercher en temps réel parmi les taxes
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="filterPeriodicite">
                                <option value="">Toutes les périodicités</option>
                                <option value="jour">Journalier</option>
                                <option value="mois">Mensuel</option>
                                <option value="an">Annuel</option>
                                <option value="non_periodique">Non périodique</option>
                            </select>
                            <div class="form-text text-center">
                                <button class="btn btn-link btn-sm p-0" id="resetFilters">
                                    <i class="fas fa-undo text-muted"></i> Réinitialiser
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des taxes -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-tag me-1"></i>Nom de la taxe</th>
                                    <th><i class="fas fa-coins me-1"></i>Montant</th>
                                    <th><i class="fas fa-calendar me-1"></i>Périodicité</th>
                                    <th><i class="fas fa-briefcase me-1"></i>Activité/Produit</th>
                                    @if(auth()->user()->role === 'gestionnaire')
                                        <th class="text-center"><i class="fas fa-cogs me-1"></i>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="taxesTableBody">
                                @forelse($taxes as $taxe)
                                    <tr class="taxe-row">
                                        <td>
                                            <strong>{{ $taxe->nom }}</strong>
                                            @if($taxe->is_fonciere ?? false)
                                                <span class="badge bg-warning text-dark ms-2" title="Taxe foncière calculée selon la superficie">
                                                    <i class="fas fa-ruler-combined"></i> Foncière
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($taxe->is_fonciere ?? false)
                                                <span class="badge bg-info">
                                                    {{ number_format($taxe->montant_par_m2 ?? 0, 0, ',', ' ') }} FCFA/m²
                                                </span>
                                                <small class="text-muted d-block mt-1">
                                                    <i class="fas fa-info-circle"></i> Calculée selon la superficie
                                                </small>
                                            @else
                                                <span class="badge bg-success">
                                                    {{ number_format($taxe->montant, 0, ',', ' ') }} FCFA
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $periodicites = [
                                                    'jour' => ['badge' => 'warning', 'icon' => 'sun', 'text' => 'Quotidienne'],
                                                    'mois' => ['badge' => 'info', 'icon' => 'calendar-alt', 'text' => 'Mensuelle'],
                                                    'an' => ['badge' => 'primary', 'icon' => 'calendar', 'text' => 'Annuelle'],
                                                    'non_periodique' => ['badge' => 'secondary', 'icon' => 'clock', 'text' => 'Ponctuelle']
                                                ];
                                                $periodicite = $periodicites[$taxe->periodicite] ?? ['badge' => 'light', 'icon' => 'question', 'text' => ucfirst($taxe->periodicite)];
                                            @endphp
                                            <span class="badge bg-{{ $periodicite['badge'] }}">
                                                <i class="fas fa-{{ $periodicite['icon'] }} me-1"></i>
                                                {{ $periodicite['text'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $taxe->activite_produit ?? 'Non spécifié' }}</small>
                                        </td>
                                        @if(auth()->user()->role === 'gestionnaire')
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('taxes.edit', $taxe->id) }}" class="btn btn-outline-primary" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('taxes.destroy', $taxe->id) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" 
                                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette taxe ?')"
                                                                title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->role === 'gestionnaire' ? '5' : '4' }}" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-receipt fa-3x mb-3"></i>
                                                <h4>Aucune taxe trouvée</h4>
                                                <p>Commencez par créer des taxes pour votre commune</p>
                                                @if(auth()->user()->role === 'gestionnaire')
                                                    <a href="{{ route('taxes.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus me-2"></i>Créer la première taxe
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTaxes');
    const clearSearchBtn = document.getElementById('clearSearch');
    const filterPeriodicite = document.getElementById('filterPeriodicite');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const tableBody = document.getElementById('taxesTableBody');
    const allRows = Array.from(tableBody.querySelectorAll('.taxe-row'));
    
    // Fonction de recherche et filtrage
    function filterTaxes() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedPeriodicite = filterPeriodicite.value;
        let visibleCount = 0;
        
        allRows.forEach(row => {
            const taxeName = row.querySelector('td:first-child').textContent.toLowerCase();
            const montant = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const periodicite = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            
            // Test de recherche textuelle
            const matchesSearch = !searchTerm || 
                taxeName.includes(searchTerm) || 
                montant.includes(searchTerm) || 
                periodicite.includes(searchTerm);
            
            // Test de filtre périodicité
            const matchesPeriodicite = !selectedPeriodicite || 
                periodicite.includes(getPeriodiciteText(selectedPeriodicite).toLowerCase());
            
            // Afficher/masquer la ligne
            if (matchesSearch && matchesPeriodicite) {
                row.style.display = '';
                visibleCount++;
                
                // Surligner les termes de recherche
                if (searchTerm) {
                    highlightSearchTerm(row, searchTerm);
                } else {
                    removeHighlight(row);
                }
            } else {
                row.style.display = 'none';
                removeHighlight(row);
            }
        });
        
        // Afficher message si aucun résultat
        updateNoResultsMessage(visibleCount);
    }
    
    // Fonction pour convertir la valeur du filtre en texte affiché dans le tableau
    function getPeriodiciteText(value) {
        const mapping = {
            'jour': 'quotidienne',        // Correspond à l'accesseur du modèle
            'mois': 'mensuelle',         // Correspond à l'accesseur du modèle
            'an': 'annuelle',            // Correspond à l'accesseur du modèle
            'non_periodique': 'ponctuelle' // Correspond à l'accesseur du modèle
        };
        return mapping[value] || value;
    }
    
    // Fonction pour surligner les termes de recherche
    function highlightSearchTerm(row, searchTerm) {
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
            const originalHTML = cell.getAttribute('data-original-html') || cell.innerHTML;
            cell.setAttribute('data-original-html', originalHTML);
            
            const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            cell.innerHTML = originalHTML.replace(regex, '<span class="search-highlight">$1</span>');
        });
    }
    
    // Fonction pour supprimer le surlignage
    function removeHighlight(row) {
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
            const originalHTML = cell.getAttribute('data-original-html');
            if (originalHTML) {
                cell.innerHTML = originalHTML;
            }
        });
    }
    
    // Fonction pour afficher/masquer le message "aucun résultat"
    function updateNoResultsMessage(visibleCount) {
        let noResultsRow = tableBody.querySelector('.no-results-row');
        
        if (visibleCount === 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = `
                    <td colspan="4" class="text-center py-4">
                        <i class="fas fa-search fa-2x text-muted mb-2"></i>
                        <h5 class="text-muted">Aucun résultat trouvé</h5>
                        <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                    </td>
                `;
                tableBody.appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }
    
    // Event listeners
    searchInput.addEventListener('input', function() {
        filterTaxes();
        
        // Montrer/masquer le bouton clear
        clearSearchBtn.style.display = this.value ? 'block' : 'none';
    });
    
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterTaxes();
        this.style.display = 'none';
        searchInput.focus();
    });
    
    filterPeriodicite.addEventListener('change', filterTaxes);
    
    resetFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterPeriodicite.value = '';
        clearSearchBtn.style.display = 'none';
        filterTaxes();
        searchInput.focus();
    });
    
    // Focus automatique sur la recherche
    searchInput.focus();
    
    // Initialiser l'état du bouton clear
    clearSearchBtn.style.display = searchInput.value ? 'block' : 'none';
});
</script>

<!-- Inclusion du système de recherche avancée -->
<script src="{{ asset('js/advanced-tax-search.js') }}"></script>

@endsection