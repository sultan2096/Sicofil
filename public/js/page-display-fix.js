/**
 * CORRECTION GLOBALE POUR L'AFFICHAGE DES PAGES - SICOSA
 * Ce script force l'affichage complet de toutes les pages
 */

(function() {
    'use strict';
    
    // Fonction pour corriger l'affichage des pages
    function fixPageDisplay() {
        console.log('🔧 Application des corrections d\'affichage...');
        
        // Forcer l'affichage de tous les conteneurs
        const containers = document.querySelectorAll('.container-fluid, .main-content, .container, main');
        containers.forEach(container => {
            if (container) {
                container.style.height = 'auto';
                container.style.minHeight = 'auto';
                container.style.maxHeight = 'none';
                container.style.overflow = 'visible';
                container.style.display = 'block';
                container.style.position = 'relative';
            }
        });
        
        // Forcer l'affichage de toutes les cartes
        const cards = document.querySelectorAll('.card, .card-body');
        cards.forEach(card => {
            if (card) {
                card.style.height = 'auto';
                card.style.minHeight = 'auto';
                card.style.maxHeight = 'none';
                card.style.overflow = 'visible';
            }
        });
        
        // Forcer l'affichage de tous les tableaux
        const tables = document.querySelectorAll('.table-responsive, .table');
        tables.forEach(table => {
            if (table) {
                table.style.height = 'auto';
                table.style.minHeight = 'auto';
                table.style.maxHeight = 'none';
                table.style.overflow = 'visible';
            }
        });
        
        // Forcer l'affichage de toutes les rows et cols
        const gridElements = document.querySelectorAll('.row, .col, [class*="col-"]');
        gridElements.forEach(element => {
            if (element) {
                element.style.height = 'auto';
                element.style.minHeight = 'auto';
                element.style.maxHeight = 'none';
                element.style.overflow = 'visible';
            }
        });
        
        // Corriger le body et html
        document.body.style.height = 'auto';
        document.body.style.minHeight = '100vh';
        document.body.style.maxHeight = 'none';
        document.body.style.overflow = 'visible';
        document.body.style.display = 'block';
        
        document.documentElement.style.height = 'auto';
        document.documentElement.style.minHeight = '100vh';
        document.documentElement.style.maxHeight = 'none';
        document.documentElement.style.overflow = 'visible';
        
        console.log('✅ Corrections d\'affichage appliquées');
    }
    
    // Fonction pour vérifier si une page est vide
    function checkEmptyPage() {
        const mainContent = document.querySelector('.main-content, .container-fluid');
        if (mainContent && mainContent.children.length === 0) {
            console.log('⚠️ Page vide détectée, rechargement...');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    }
    
    // Fonction pour forcer le scroll
    function enableScroll() {
        document.body.style.overflowY = 'auto';
        document.documentElement.style.overflowY = 'auto';
        
        // Supprimer les styles qui bloquent le scroll
        const styleSheets = document.styleSheets;
        for (let i = 0; i < styleSheets.length; i++) {
            try {
                const rules = styleSheets[i].cssRules || styleSheets[i].rules;
                for (let j = 0; j < rules.length; j++) {
                    const rule = rules[j];
                    if (rule.style && rule.style.overflow === 'hidden') {
                        rule.style.overflow = 'visible';
                    }
                }
            } catch (e) {
                // Ignorer les erreurs CORS
            }
        }
    }
    
    // Fonction pour corriger les hauteurs
    function fixHeights() {
        const elements = document.querySelectorAll('*');
        elements.forEach(element => {
            if (element.style && element.style.maxHeight === '100vh') {
                element.style.maxHeight = 'none';
            }
            if (element.style && element.style.height === '100vh') {
                element.style.height = 'auto';
            }
        });
    }
    
    // Application des corrections au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(fixPageDisplay, 100);
            setTimeout(checkEmptyPage, 500);
            setTimeout(enableScroll, 200);
            setTimeout(fixHeights, 300);
        });
    } else {
        setTimeout(fixPageDisplay, 100);
        setTimeout(checkEmptyPage, 500);
        setTimeout(enableScroll, 200);
        setTimeout(fixHeights, 300);
    }
    
    // Application des corrections lors des changements de page (SPA)
    let currentUrl = window.location.href;
    setInterval(() => {
        if (window.location.href !== currentUrl) {
            currentUrl = window.location.href;
            setTimeout(fixPageDisplay, 100);
            setTimeout(enableScroll, 200);
            setTimeout(fixHeights, 300);
        }
    }, 1000);
    
    // Correction lors du redimensionnement
    window.addEventListener('resize', function() {
        setTimeout(fixPageDisplay, 100);
    });
    
    // Correction lors du scroll
    window.addEventListener('scroll', function() {
        // S'assurer que le scroll fonctionne
        document.body.style.overflowY = 'auto';
    });
    
    // Correction pour les clics sur les liens
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' || e.target.closest('a')) {
            setTimeout(fixPageDisplay, 500);
        }
    });
    
    // Correction pour les soumissions de formulaire
    document.addEventListener('submit', function() {
        setTimeout(fixPageDisplay, 500);
    });
    
    console.log('🚀 Script de correction d\'affichage chargé');
    
})(); 