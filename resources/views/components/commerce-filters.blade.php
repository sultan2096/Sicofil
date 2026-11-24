{{-- Composant pour les filtres de commerce avec listes déroulantes synchronisées --}}
<script src="{{ asset('js/commerce-filters.js') }}"></script>

<style>
.filter-selected {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

.commerce-filter-help {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 6px;
    padding: 8px 12px;
    margin-top: 8px;
    font-size: 0.875rem;
    color: #1565c0;
}

.commerce-filter-help i {
    color: #2196f3;
}

.form-select.filter-active {
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23198754' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 8l4 4 8-8'/%3E%3C/svg%3E"), url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3E%3C/svg%3E");
    background-position: right 0.75rem center, right 2rem center;
    background-size: 16px 12px, 16px 12px;
}
</style>

@if(isset($showHelp) && $showHelp)
<div class="commerce-filter-help">
    <i class="fas fa-lightbulb"></i>
    <strong>Aide :</strong> 
    <span>Choisissez d'abord la catégorie, puis la nature pour filtrer automatiquement les types de commerce disponibles. Vous pouvez aussi sélectionner directement un type pour voir sa nature.</span>
</div>
@endif

<script>
// S'assurer que le script de filtrage est chargé
if (typeof initCommerceFilters === 'undefined') {
    console.warn('Script de filtrage commerce non chargé - chargement dynamique...');
    
    // Charger le script dynamiquement si nécessaire
    const script = document.createElement('script');
    script.src = '/js/commerce-filters.js';
    script.onload = function() {
        console.log('✅ Script de filtrage commerce chargé dynamiquement');
        if (typeof initCommerceFilters !== 'undefined') {
            initCommerceFilters();
        }
    };
    document.head.appendChild(script);
} else {
    // Le script est déjà chargé, initialiser directement
    document.addEventListener('DOMContentLoaded', function() {
        initCommerceFilters();
    });
}
</script> 