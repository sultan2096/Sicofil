/**
 * Navigation JavaScript - SICOSA
 * Améliore l'interactivité et la responsivité de la navigation
 */

class SICOSANavigation {
    constructor() {
        this.mobileMenuButton = document.getElementById('mobile-menu-button');
        this.mobileMenu = document.getElementById('mobile-menu');
        this.navbar = document.querySelector('.nav-gradient');
        this.isMobileMenuOpen = false;
        this.lastScrollTop = 0;
        
        this.init();
    }
    
    init() {
        this.setupMobileMenu();
        this.setupScrollEffects();
        this.setupKeyboardNavigation();
        this.setupTouchGestures();
        this.setupPerformanceOptimizations();
        this.setupAccessibility();
    }
    
    /**
     * Configuration du menu mobile
     */
    setupMobileMenu() {
        if (!this.mobileMenuButton || !this.mobileMenu) return;
        
        // Toggle du menu mobile
        this.mobileMenuButton.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleMobileMenu();
        });
        
        // Fermer le menu en cliquant sur un lien
        const mobileLinks = this.mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                this.closeMobileMenu();
            });
        });
        
        // Gestion du bouton de déconnexion mobile
        const logoutButton = this.mobileMenu.querySelector('button[type="submit"]');
        if (logoutButton) {
            logoutButton.addEventListener('click', (e) => {
                // Pas besoin de fermer le menu car on va être redirigé
                console.log('Déconnexion en cours...');
            });
        }
        
        // Fermer le menu en cliquant en dehors
        document.addEventListener('click', (e) => {
            if (!this.mobileMenuButton.contains(e.target) && 
                !this.mobileMenu.contains(e.target)) {
                this.closeMobileMenu();
            }
        });
        
        // Fermer le menu avec la touche Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMobileMenuOpen) {
                this.closeMobileMenu();
            }
        });
        
        // Gestion du redimensionnement de la fenêtre
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024 && this.isMobileMenuOpen) {
                this.closeMobileMenu();
            }
        });
    }
    
    /**
     * Toggle du menu mobile
     */
    toggleMobileMenu() {
        if (this.isMobileMenuOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    }
    
    /**
     * Ouvrir le menu mobile
     */
    openMobileMenu() {
        this.mobileMenu.classList.remove('hidden');
        this.mobileMenuButton.setAttribute('aria-expanded', 'true');
        this.isMobileMenuOpen = true;
        
        // Animation d'ouverture
        this.mobileMenu.style.animation = 'slideDown 0.3s ease';
        
        // Empêcher le scroll du body
        document.body.style.overflow = 'hidden';
        
        // Focus sur le premier lien du menu
        const firstLink = this.mobileMenu.querySelector('a');
        if (firstLink) {
            setTimeout(() => firstLink.focus(), 300);
        }
    }
    
    /**
     * Fermer le menu mobile
     */
    closeMobileMenu() {
        this.mobileMenu.classList.add('hidden');
        this.mobileMenuButton.setAttribute('aria-expanded', 'false');
        this.isMobileMenuOpen = false;
        
        // Réactiver le scroll du body
        document.body.style.overflow = '';
        
        // Retourner le focus sur le bouton
        this.mobileMenuButton.focus();
    }
    
    /**
     * Effets de scroll
     */
    setupScrollEffects() {
        if (!this.navbar) return;
        
        let ticking = false;
        
        const updateNavbar = () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Effet de transparence au scroll
            if (scrollTop > 50) {
                this.navbar.classList.add('nav-blur');
                this.navbar.style.background = 'rgba(30, 58, 138, 0.95)';
            } else {
                this.navbar.classList.remove('nav-blur');
                this.navbar.style.background = '';
            }
            
            // Effet de masquage/affichage au scroll
            if (scrollTop > this.lastScrollTop && scrollTop > 100) {
                // Scroll vers le bas - masquer la navbar
                this.navbar.style.transform = 'translateY(-100%)';
            } else {
                // Scroll vers le haut - afficher la navbar
                this.navbar.style.transform = 'translateY(0)';
            }
            
            this.lastScrollTop = scrollTop;
            ticking = false;
        };
        
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateNavbar);
                ticking = true;
            }
        });
    }
    
    /**
     * Navigation au clavier
     */
    setupKeyboardNavigation() {
        // Navigation dans le menu mobile avec les flèches
        if (this.mobileMenu) {
            this.mobileMenu.addEventListener('keydown', (e) => {
                const links = Array.from(this.mobileMenu.querySelectorAll('a'));
                const currentIndex = links.indexOf(document.activeElement);
                
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        const nextIndex = (currentIndex + 1) % links.length;
                        links[nextIndex].focus();
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        const prevIndex = currentIndex > 0 ? currentIndex - 1 : links.length - 1;
                        links[prevIndex].focus();
                        break;
                }
            });
        }
        
        // Navigation dans le menu desktop
        const desktopLinks = document.querySelectorAll('.nav-link-hover');
        desktopLinks.forEach((link, index) => {
            link.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    link.click();
                }
            });
        });
    }
    
    /**
     * Gestes tactiles
     */
    setupTouchGestures() {
        if (!this.mobileMenu) return;
        
        let startY = 0;
        let currentY = 0;
        
        this.mobileMenu.addEventListener('touchstart', (e) => {
            startY = e.touches[0].clientY;
        });
        
        this.mobileMenu.addEventListener('touchmove', (e) => {
            currentY = e.touches[0].clientY;
        });
        
        this.mobileMenu.addEventListener('touchend', (e) => {
            const diffY = startY - currentY;
            
            // Swipe vers le haut pour fermer le menu
            if (diffY > 50 && this.isMobileMenuOpen) {
                this.closeMobileMenu();
            }
        });
    }
    
    /**
     * Optimisations de performance
     */
    setupPerformanceOptimizations() {
        // Utilisation de Intersection Observer pour les animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('nav-fade-in');
                }
            });
        }, observerOptions);
        
        // Observer les éléments de navigation
        const navElements = document.querySelectorAll('.nav-link-hover, .mobile-nav-item');
        navElements.forEach(el => observer.observe(el));
        
        // Debounce pour les événements de resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 250);
        });
    }
    
    /**
     * Gestion du redimensionnement
     */
    handleResize() {
        const width = window.innerWidth;
        
        // Ajuster la navigation selon la taille d'écran
        if (width < 768) {
            document.body.classList.add('mobile-view');
            document.body.classList.remove('tablet-view', 'desktop-view');
        } else if (width < 1024) {
            document.body.classList.add('tablet-view');
            document.body.classList.remove('mobile-view', 'desktop-view');
        } else {
            document.body.classList.add('desktop-view');
            document.body.classList.remove('mobile-view', 'tablet-view');
        }
    }
    
    /**
     * Améliorations d'accessibilité
     */
    setupAccessibility() {
        // Ajouter les attributs ARIA
        if (this.mobileMenuButton) {
            this.mobileMenuButton.setAttribute('aria-expanded', 'false');
            this.mobileMenuButton.setAttribute('aria-controls', 'mobile-menu');
            this.mobileMenuButton.setAttribute('aria-label', 'Menu de navigation');
        }
        
        if (this.mobileMenu) {
            this.mobileMenu.setAttribute('aria-hidden', 'true');
        }
        
        // Gestion du menu utilisateur desktop
        const userMenu = document.querySelector('.group');
        if (userMenu) {
            const dropdown = userMenu.querySelector('.group-hover\\:opacity-100');
            if (dropdown) {
                // Ajouter un délai pour éviter la fermeture accidentelle
                let timeout;
                
                userMenu.addEventListener('mouseenter', () => {
                    clearTimeout(timeout);
                    dropdown.style.opacity = '1';
                    dropdown.style.visibility = 'visible';
                });
                
                userMenu.addEventListener('mouseleave', () => {
                    timeout = setTimeout(() => {
                        dropdown.style.opacity = '0';
                        dropdown.style.visibility = 'hidden';
                    }, 200);
                });
                
                // Gestion du clic sur le bouton de déconnexion
                const logoutButton = dropdown.querySelector('button[type="submit"]');
                if (logoutButton) {
                    logoutButton.addEventListener('click', (e) => {
                        e.preventDefault();
                        console.log('Déconnexion en cours...');
                        // Soumettre le formulaire
                        const form = logoutButton.closest('form');
                        if (form) {
                            form.submit();
                        }
                    });
                }
            }
        }
        
        // Améliorer la navigation au clavier
        const focusableElements = document.querySelectorAll(
            'a[href], button, input, textarea, select, [tabindex]:not([tabindex="-1"])'
        );
        
        focusableElements.forEach(element => {
            element.addEventListener('focus', () => {
                element.classList.add('focus-visible');
            });
            
            element.addEventListener('blur', () => {
                element.classList.remove('focus-visible');
            });
        });
        
        // Support des préférences de réduction de mouvement
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.body.classList.add('reduced-motion');
        }
    }
    
    /**
     * Utilitaires
     */
    static showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `nav-notification nav-notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    /**
     * Mettre à jour les indicateurs actifs
     */
    updateActiveIndicators() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link-hover, .mobile-nav-item');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.split('/')[1])) {
                link.classList.add('nav-link-active');
            } else {
                link.classList.remove('nav-link-active');
            }
        });
    }
}

// Initialisation quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    const navigation = new SICOSANavigation();
    
    // Exposer l'instance globalement pour les tests
    window.SICOSANavigation = navigation;
    
    // Mettre à jour les indicateurs actifs
    navigation.updateActiveIndicators();
    
    // Gestion des erreurs
    window.addEventListener('error', (e) => {
        console.error('Erreur de navigation:', e.error);
    });
});

// Support des modules ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SICOSANavigation;
} 