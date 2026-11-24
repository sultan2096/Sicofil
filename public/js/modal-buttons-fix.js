// CORRECTION SIMPLE POUR LES BOUTONS DES MODALS
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 SICOSA - Correction des boutons modals activée');
    
    // Fonction pour forcer l'affichage des boutons dans les modals
    function fixModalButtons() {
        // Forcer l'affichage de tous les boutons dans les modals
        const modalButtons = document.querySelectorAll('.modal-footer .btn, .modal-footer button');
        modalButtons.forEach(btn => {
            btn.style.display = 'inline-block !important';
            btn.style.visibility = 'visible !important';
            btn.style.opacity = '1 !important';
            btn.style.position = 'relative !important';
            btn.style.zIndex = '1000 !important';
            btn.style.padding = '6px 12px !important';
            btn.style.fontSize = '0.9rem !important';
            btn.style.fontWeight = '600 !important';
            btn.style.borderRadius = '0.375rem !important';
            btn.style.cursor = 'pointer !important';
        });
        
        // Forcer l'affichage des footers de modals
        const modalFooters = document.querySelectorAll('.modal-footer');
        modalFooters.forEach(footer => {
            footer.style.display = 'flex !important';
            footer.style.visibility = 'visible !important';
            footer.style.opacity = '1 !important';
            footer.style.position = 'relative !important';
            footer.style.zIndex = '1000 !important';
            footer.style.justifyContent = 'space-between !important';
            footer.style.padding = '10px 15px !important';
            footer.style.borderTop = '1px solid #dee2e6 !important';
            footer.style.backgroundColor = '#f8f9fa !important';
        });
        
        // Forcer l'affichage des modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'block !important';
            modal.style.visibility = 'visible !important';
            modal.style.opacity = '1 !important';
        });
        
        console.log('✅ Boutons modals corrigés');
    }
    
    // Appliquer immédiatement
    fixModalButtons();
    
    // Réappliquer après 500ms
    setTimeout(fixModalButtons, 500);
    
    // Réappliquer après 1 seconde
    setTimeout(fixModalButtons, 1000);
    
    // Réappliquer après 2 secondes
    setTimeout(fixModalButtons, 2000);
    
    // Surveiller l'ouverture des modals
    document.addEventListener('shown.bs.modal', function(event) {
        setTimeout(fixModalButtons, 100);
        setTimeout(fixModalButtons, 500);
    });
    
    // Surveiller les changements dans le DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                setTimeout(fixModalButtons, 100);
            }
        });
    });
    
    // Observer les changements dans le body
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Fonction pour forcer l'ouverture d'un modal spécifique
    window.forceOpenModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            setTimeout(fixModalButtons, 100);
        }
    };
    
    // Fonction pour forcer l'affichage de tous les éléments
    window.forceShowAllElements = function() {
        const allElements = document.querySelectorAll('*');
        allElements.forEach(element => {
            element.style.display = 'block !important';
            element.style.visibility = 'visible !important';
            element.style.opacity = '1 !important';
        });
        fixModalButtons();
    };
}); 