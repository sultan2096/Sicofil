// Données de correspondance Nature / Type de commerce basées sur le tableau SICOSA
const commerceData = {
    activite_commerciale: {
        "Boutique ou Magasin": [
            "Alimentation", "Grossiste", "Mercerie", "Quincaillerie", 
            "Shopping", "Cosmétique", "Pharmacie", "Bijouterie", "Librairie"
        ],
        "Atelier": [
            "Atelier mécanicien", "Atelier de couture", "Atelier de coiffure", 
            "Atelier de forgeron", "Atelier de cordonnerie", "Atelier de menuiserie", 
            "Boulangerie", "Atelier de soudure", "Atelier de réparation"
        ],
        "Agriculture": [
            "Chevaux", "Ânes", "Ovins", "Caprins", "Bovins", 
            "Vaches", "Moutons", "Chèvres", "Volailles", "Porcins"
        ],
        "Transport": [
            "Bus", "Camion-citerne de carburant", "Camionnette", "Calèche", 
            "Camion gros porteur", "Taxi clando", "Moto Jakarta", "Car rapide",
            "Taxi officiel", "Transport en commun"
        ],
        "Étal": [
            "Vente sur étal", "Étal de fruits", "Étal de légumes", 
            "Étal de poissons", "Étal de viande"
        ],
        "Table ou Tente commerciale": [
            "Vente sur table", "Vente sous tente", "Stand mobile", 
            "Commerce ambulant"
        ],
        "Cantine ou Kiosque": [
            "Restaurant", "Gargote", "Dibiterie", "Fast Food", 
            "Café", "Bar", "Kiosque", "Buvette"
        ],
        "Entreprise": [
            "Entrepôt", "Grande exploitation agricole", "Supermarché", 
            "Station-service", "Usine", "Bureau", "Entreprise de services",
            "Société de transport"
        ]
    },
    point_interet: {
        "Foncier": [
            "Location de terrain", "Location de bâtiments", "Location de véhicules",
            "Location d'équipements", "Location de matériels"
        ],
        "Publicité": [
            "Véhicules publicitaires", "Panneaux publicitaires", "Enseignes",
            "Banderoles", "Affichage digital"
        ],
        "Spectacles": [
            "Lieux d'événements", "Salles de spectacle", "Terrains de sport",
            "Espaces culturels", "Cinémas"
        ],
        "Papiers administratifs": [
            "Extrait de naissance", "Certificat de décès", "Acte de mariage", 
            "Photocopie", "Dactylographie", "Impression", "Scan de documents"
        ]
    }
};

/**
 * Initialise le système de filtrage pour les formulaires de commerce
 */
function initCommerceFilters() {
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
    function populateTypes(nature, categorie) {
        typeCommerceSelect.innerHTML = '<option value="">-- Sélectionner le type --</option>';
        
        if (nature && categorie && commerceData[categorie] && commerceData[categorie][nature]) {
            commerceData[categorie][nature].forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                typeCommerceSelect.appendChild(option);
            });
        }
        
        // Déclencher événement pour mise à jour externe
        typeCommerceSelect.dispatchEvent(new Event('change'));
    }

    // Fonction pour sélectionner la nature selon le type choisi (navigation inverse)
    function selectNatureByType(selectedType, categorie) {
        if (!selectedType || !categorie || !commerceData[categorie]) return;
        
        for (const [nature, types] of Object.entries(commerceData[categorie])) {
            if (types.includes(selectedType)) {
                natureSelect.value = nature;
                populateTypes(nature, categorie);
                break;
            }
        }
    }

    // Event listeners
    categorieSelect.addEventListener('change', function() {
        const selectedCategorie = this.value;
        populateNatures(selectedCategorie);
        
        // Ajouter une classe CSS pour indiquer l'état
        if (selectedCategorie) {
            this.classList.add('filter-selected');
        } else {
            this.classList.remove('filter-selected');
        }
    });

    natureSelect.addEventListener('change', function() {
        const categorie = categorieSelect.value;
        const selectedNature = this.value;
        populateTypes(selectedNature, categorie);
        
        // Ajouter une classe CSS pour indiquer l'état
        if (selectedNature) {
            this.classList.add('filter-selected');
        } else {
            this.classList.remove('filter-selected');
        }
    });

    typeCommerceSelect.addEventListener('change', function() {
        const categorie = categorieSelect.value;
        const selectedType = this.value;
        
        // Navigation inverse : si on choisit un type sans nature, trouver la nature
        if (selectedType && !natureSelect.value) {
            selectNatureByType(selectedType, categorie);
        }
        
        // Ajouter une classe CSS pour indiquer l'état
        if (selectedType) {
            this.classList.add('filter-selected');
        } else {
            this.classList.remove('filter-selected');
        }
    });

    // Initialisation au chargement de la page
    const initialCategorie = categorieSelect.value;
    if (initialCategorie) {
        populateNatures(initialCategorie);
        
        // Restaurer les valeurs si elles existent (mode édition)
        const savedNature = natureSelect.getAttribute('data-value');
        const savedType = typeCommerceSelect.getAttribute('data-value');
        
        setTimeout(() => {
            if (savedNature) {
                natureSelect.value = savedNature;
                populateTypes(savedNature, initialCategorie);
                
                setTimeout(() => {
                    if (savedType) {
                        typeCommerceSelect.value = savedType;
                    }
                }, 100);
            }
        }, 100);
    }

    console.log('✅ Système de filtrage commerce initialisé');
}

/**
 * Fonction utilitaire pour obtenir tous les types d'une catégorie
 */
function getAllTypesByCategorie(categorie) {
    if (!commerceData[categorie]) return [];
    
    const allTypes = [];
    Object.values(commerceData[categorie]).forEach(types => {
        allTypes.push(...types);
    });
    
    return allTypes.sort();
}

/**
 * Fonction utilitaire pour obtenir la nature d'un type
 */
function getNatureByType(type, categorie) {
    if (!commerceData[categorie]) return null;
    
    for (const [nature, types] of Object.entries(commerceData[categorie])) {
        if (types.includes(type)) {
            return nature;
        }
    }
    
    return null;
}

// CSS pour les états visuels
const filterStyles = `
<style>
.filter-selected {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

.form-select option:disabled {
    color: #6c757d;
    background-color: #f8f9fa;
}

.commerce-filter-info {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 8px;
    padding: 10px;
    margin-top: 10px;
    font-size: 0.875rem;
}

.commerce-filter-info .badge {
    margin-right: 5px;
}
</style>
`;

// Injecter les styles
if (typeof document !== 'undefined') {
    document.head.insertAdjacentHTML('beforeend', filterStyles);
}

// Auto-initialisation au chargement du DOM
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', initCommerceFilters);
}

// Export pour utilisation modulaire
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        commerceData,
        initCommerceFilters,
        getAllTypesByCategorie,
        getNatureByType
    };
} 