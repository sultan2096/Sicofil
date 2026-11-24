// Système de filtrage dynamique pour les types de commerce
// Les données sont chargées depuis la base de données via API

let commerceData = null;

/**
 * Charge les données de filtrage depuis l'API
 */
async function loadCommerceData() {
    try {
        const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '';
        const response = await fetch(baseUrl + '/api/commerce-filters');
        if (!response.ok) {
            throw new Error('Erreur lors du chargement des données');
        }
        commerceData = await response.json();
        console.log('Données de commerce chargées:', commerceData);
    } catch (error) {
        console.error('Erreur lors du chargement des données de commerce:', error);
        // Fallback vers données vides si API non disponible
        commerceData = {
            activite_commerciale: {},
            point_interet: {}
        };
    }
}

/**
 * Initialise le système de filtrage pour les formulaires de commerce
 */
async function initCommerceFilters() {
    // Charger les données si pas déjà fait
    if (!commerceData) {
        await loadCommerceData();
    }

    const categorieSelect = document.getElementById('categorie');
    const natureSelect = document.getElementById('nature');
    const typeCommerceSelect = document.getElementById('type_commerce');

    if (!categorieSelect || !natureSelect || !typeCommerceSelect) {
        console.warn('Elements de commerce non trouvés - filtrage désactivé');
        return;
    }

    // Fonction pour peupler les natures selon la catégorie
    function populateNatures(categorie) {
        natureSelect.innerHTML = '<option value="">-- Sélectionner la nature --</option>';
        typeCommerceSelect.innerHTML = '<option value="">-- Sélectionner le type --</option>';
        
        if (categorie && commerceData[categorie]) {
            Object.keys(commerceData[categorie]).forEach(nature => {
                const option = document.createElement('option');
                option.value = nature;
                option.textContent = nature;
                natureSelect.appendChild(option);
            });
        }
        
        // Déclencher événement pour mise à jour externe
        natureSelect.dispatchEvent(new Event('change'));
    }

    // Fonction pour peupler les types selon la nature
    function populateTypes(nature) {
        typeCommerceSelect.innerHTML = '<option value="">-- Sélectionner le type --</option>';
        
        if (nature) {
            // Rechercher dans quelle catégorie se trouve cette nature
            let types = [];
            for (const categorie in commerceData) {
                if (commerceData[categorie][nature]) {
                    types = commerceData[categorie][nature];
                    break;
                }
            }
            
            types.forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                typeCommerceSelect.appendChild(option);
            });
        }
        
        // Déclencher événement pour mise à jour externe
        typeCommerceSelect.dispatchEvent(new Event('change'));
    }

    // Fonction pour sélectionner automatiquement la nature basée sur le type
    function selectNatureByType(type) {
        if (!type) return;
        
        for (const categorie in commerceData) {
            for (const nature in commerceData[categorie]) {
                if (commerceData[categorie][nature].includes(type)) {
                    // Sélectionner la catégorie
                    categorieSelect.value = categorie;
                    populateNatures(categorie);
                    
                    // Sélectionner la nature
                    natureSelect.value = nature;
                    populateTypes(nature);
                    
                    // Sélectionner le type
                    typeCommerceSelect.value = type;
                    return;
                }
            }
        }
    }

    // Gestionnaires d'événements
    categorieSelect.addEventListener('change', function() {
        populateNatures(this.value);
    });

    natureSelect.addEventListener('change', function() {
        populateTypes(this.value);
    });

    typeCommerceSelect.addEventListener('change', function() {
        // Si un type est sélectionné et qu'aucune nature n'est sélectionnée,
        // essayer de sélectionner automatiquement la nature
        if (this.value && !natureSelect.value) {
            selectNatureByType(this.value);
        }
    });

    // Initialisation avec les valeurs existantes si le formulaire est en édition
    const initialCategorie = categorieSelect.value;
    const initialNature = natureSelect.value;
    const initialType = typeCommerceSelect.value;

    if (initialCategorie) {
        populateNatures(initialCategorie);
        
        if (initialNature) {
            setTimeout(() => {
                natureSelect.value = initialNature;
                populateTypes(initialNature);
                
                if (initialType) {
                    setTimeout(() => {
                        typeCommerceSelect.value = initialType;
                    }, 100);
                }
            }, 100);
        }
    } else if (initialType) {
        // Si seulement le type est sélectionné, retrouver la catégorie et nature
        selectNatureByType(initialType);
    }

    console.log('Système de filtrage de commerce initialisé');
}

/**
 * Fonction utilitaire pour recharger les données de commerce
 */
async function refreshCommerceData() {
    commerceData = null;
    await loadCommerceData();
    // Réinitialiser les filtres si ils existent
    if (document.getElementById('categorie')) {
        await initCommerceFilters();
    }
}

/**
 * Obtenir tous les types d'une nature spécifique
 */
function getTypesByNature(nature) {
    if (!commerceData) return [];
    
    for (const categorie in commerceData) {
        if (commerceData[categorie][nature]) {
            return commerceData[categorie][nature];
        }
    }
    return [];
}

/**
 * Obtenir la catégorie d'un type spécifique
 */
function getCategorieByType(type) {
    if (!commerceData) return null;
    
    for (const categorie in commerceData) {
        for (const nature in commerceData[categorie]) {
            if (commerceData[categorie][nature].includes(type)) {
                return categorie;
            }
        }
    }
    return null;
}

/**
 * Obtenir la nature d'un type spécifique
 */
function getNatureByType(type) {
    if (!commerceData) return null;
    
    for (const categorie in commerceData) {
        for (const nature in commerceData[categorie]) {
            if (commerceData[categorie][nature].includes(type)) {
                return nature;
            }
        }
    }
    return null;
}

// Initialisation automatique quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si nous sommes sur une page avec des filtres de commerce
    if (document.getElementById('categorie') || document.getElementById('nature') || document.getElementById('type_commerce')) {
        initCommerceFilters();
    }
});

// Exposer les fonctions utilitaires globalement
window.commerceFilters = {
    init: initCommerceFilters,
    refresh: refreshCommerceData,
    getTypesByNature: getTypesByNature,
    getCategorieByType: getCategorieByType,
    getNatureByType: getNatureByType,
    getData: () => commerceData
}; 
} 
} 