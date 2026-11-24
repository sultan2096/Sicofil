/**
 * Système de recherche avancée pour les taxes SICOSA
 * Fonctionnalités: Recherche textuelle, vocale, QR code, filtres multiples
 */

class AdvancedTaxSearch {
    constructor() {
        this.searchInput = document.getElementById('searchTaxes');
        this.tableBody = document.getElementById('taxesTableBody');
        this.allRows = [];
        this.filteredRows = [];
        this.isVoiceRecording = false;
        this.recognition = null;
        
        this.filters = {
            search: '',
            periodicite: '',
            montantMin: '',
            montantMax: '',
            nature: '',
            qrCode: ''
        };
        
        this.init();
    }
    
    init() {
        this.setupElements();
        this.setupEventListeners();
        this.setupVoiceSearch();
        this.setupQRCodeSearch();
        this.loadTaxRows();
        this.setupAdvancedFilters();
        
        console.log('🔍 Système de recherche avancée des taxes initialisé');
    }
    
    setupElements() {
        // Injecter l'interface de recherche avancée
        const searchContainer = this.searchInput?.parentElement?.parentElement;
        if (!searchContainer) return;
        
        const advancedUI = this.createAdvancedSearchUI();
        searchContainer.insertAdjacentHTML('afterend', advancedUI);
        
        // Actualiser les références
        this.voiceBtn = document.getElementById('voiceSearchBtn');
        this.qrBtn = document.getElementById('qrSearchBtn');
        this.advancedToggle = document.getElementById('advancedToggle');
        this.advancedFilters = document.getElementById('advancedFilters');
        this.clearFiltersBtn = document.getElementById('clearAllFilters');
        this.exportBtn = document.getElementById('exportResults');
    }
    
    createAdvancedSearchUI() {
        return `
        <div class="card mt-3" id="advancedSearchUI">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-search-plus text-primary"></i>
                        Recherche Avancée
                    </h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-success btn-sm" id="voiceSearchBtn" title="Recherche vocale">
                            <i class="fas fa-microphone"></i>
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" id="qrSearchBtn" title="Scanner QR Code">
                            <i class="fas fa-qrcode"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="advancedToggle" title="Filtres avancés">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-3" id="advancedFilters" style="display: none;">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="filterMontantMin" class="form-label">
                            <i class="fas fa-coins text-warning"></i> Montant minimum
                        </label>
                        <input type="number" class="form-control" id="filterMontantMin" placeholder="Ex: 1000">
                    </div>
                    <div class="col-md-4">
                        <label for="filterMontantMax" class="form-label">
                            <i class="fas fa-coins text-warning"></i> Montant maximum
                        </label>
                        <input type="number" class="form-control" id="filterMontantMax" placeholder="Ex: 50000">
                    </div>
                    <div class="col-md-4">
                        <label for="filterNature" class="form-label">
                            <i class="fas fa-tag text-info"></i> Nature de la taxe
                        </label>
                        <select class="form-select" id="filterNature">
                            <option value="">Toutes les natures</option>
                            <option value="produits_domaniaux">Produits Domaniaux</option>
                            <option value="taxes_municipales">Taxes Municipales</option>
                            <option value="produits_exploitation">Produits d'Exploitation</option>
                        </select>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="filterActivite" class="form-label">
                            <i class="fas fa-briefcase text-primary"></i> Activité/Produit
                        </label>
                        <input type="text" class="form-control" id="filterActivite" placeholder="Transport, Commerce, etc.">
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end align-items-end h-100">
                            <button type="button" class="btn btn-danger me-2" id="clearAllFilters">
                                <i class="fas fa-times"></i> Effacer
                            </button>
                            <button type="button" class="btn btn-success" id="exportResults">
                                <i class="fas fa-download"></i> Exporter
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Statistiques de recherche -->
                <div class="mt-3 p-2 bg-light rounded" id="searchStats">
                    <small class="text-muted">
                        <i class="fas fa-chart-bar"></i>
                        <span id="resultsCount">0</span> résultat(s) trouvé(s) sur <span id="totalCount">0</span> taxes
                    </small>
                </div>
            </div>
        </div>`;
    }
    
    setupEventListeners() {
        // Recherche textuelle en temps réel
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.filters.search = e.target.value.toLowerCase();
                this.applyFilters();
            });
        }
        
        // Toggle filtres avancés
        if (this.advancedToggle) {
            this.advancedToggle.addEventListener('click', () => {
                const isVisible = this.advancedFilters.style.display !== 'none';
                this.advancedFilters.style.display = isVisible ? 'none' : 'block';
                this.advancedToggle.classList.toggle('active', !isVisible);
            });
        }
        
        // Effacer tous les filtres
        if (this.clearFiltersBtn) {
            this.clearFiltersBtn.addEventListener('click', () => {
                this.clearAllFilters();
            });
        }
        
        // Exporter les résultats
        if (this.exportBtn) {
            this.exportBtn.addEventListener('click', () => {
                this.exportResults();
            });
        }
        
        // Filtres avancés
        const filterElements = ['filterMontantMin', 'filterMontantMax', 'filterNature', 'filterActivite'];
        filterElements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', () => this.updateAdvancedFilters());
            }
        });
    }
    
    setupVoiceSearch() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            if (this.voiceBtn) {
                this.voiceBtn.disabled = true;
                this.voiceBtn.title = 'Recherche vocale non supportée par ce navigateur';
            }
            return;
        }
        
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();
        
        this.recognition.lang = 'fr-FR';
        this.recognition.continuous = false;
        this.recognition.interimResults = false;
        
        this.recognition.onstart = () => {
            this.isVoiceRecording = true;
            if (this.voiceBtn) {
                this.voiceBtn.innerHTML = '<i class="fas fa-stop text-danger"></i>';
                this.voiceBtn.classList.add('btn-outline-danger');
                this.voiceBtn.classList.remove('btn-outline-success');
            }
            this.showNotification('🎤 Parlez maintenant...', 'info');
        };
        
        this.recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            if (this.searchInput) {
                this.searchInput.value = transcript;
                this.filters.search = transcript.toLowerCase();
                this.applyFilters();
            }
            this.showNotification(`🔍 Recherche: "${transcript}"`, 'success');
        };
        
        this.recognition.onend = () => {
            this.isVoiceRecording = false;
            if (this.voiceBtn) {
                this.voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
                this.voiceBtn.classList.add('btn-outline-success');
                this.voiceBtn.classList.remove('btn-outline-danger');
            }
        };
        
        this.recognition.onerror = (event) => {
            this.showNotification('❌ Erreur de reconnaissance vocale', 'error');
            console.error('Erreur reconnaissance vocale:', event.error);
        };
        
        if (this.voiceBtn) {
            this.voiceBtn.addEventListener('click', () => {
                if (this.isVoiceRecording) {
                    this.recognition.stop();
                } else {
                    this.recognition.start();
                }
            });
        }
    }
    
    setupQRCodeSearch() {
        // Simuler un scanner QR Code (nécessiterait une vraie librairie en production)
        if (this.qrBtn) {
            this.qrBtn.addEventListener('click', () => {
                this.showQRCodeModal();
            });
        }
    }
    
    showQRCodeModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-qrcode"></i> Scanner QR Code
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div id="qr-scanner" style="width: 100%; height: 200px; background: #f8f9fa; border: 2px dashed #dee2e6; display: flex; align-items: center; justify-content: center;">
                            <div>
                                <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Scanner QR Code<br>
                                <small>(Fonctionnalité en développement)</small></p>
                                <button class="btn btn-primary" onclick="this.closest('.modal').querySelector('[data-bs-dismiss]').click()">
                                    Simuler scan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }
    
    loadTaxRows() {
        if (!this.tableBody) return;
        
        this.allRows = Array.from(this.tableBody.querySelectorAll('tr.taxe-row'));
        this.filteredRows = [...this.allRows];
        this.updateStats();
    }
    
    updateAdvancedFilters() {
        this.filters.montantMin = document.getElementById('filterMontantMin')?.value || '';
        this.filters.montantMax = document.getElementById('filterMontantMax')?.value || '';
        this.filters.nature = document.getElementById('filterNature')?.value || '';
        this.filters.activite = document.getElementById('filterActivite')?.value.toLowerCase() || '';
        
        this.applyFilters();
    }
    
    applyFilters() {
        if (!this.allRows.length) return;
        
        this.filteredRows = this.allRows.filter(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length < 4) return false;
            
            const nom = cells[0].textContent.toLowerCase();
            const montantText = cells[1].textContent.replace(/[^\d]/g, '');
            const montant = parseInt(montantText) || 0;
            const periodicite = cells[2].textContent.toLowerCase();
            const activite = cells[3]?.textContent.toLowerCase() || '';
            
            // Filtre de recherche textuelle
            if (this.filters.search) {
                const searchTerms = this.filters.search.split(' ');
                const textContent = `${nom} ${periodicite} ${activite}`.toLowerCase();
                
                if (!searchTerms.every(term => textContent.includes(term))) {
                    return false;
                }
            }
            
            // Filtre montant minimum
            if (this.filters.montantMin && montant < parseInt(this.filters.montantMin)) {
                return false;
            }
            
            // Filtre montant maximum
            if (this.filters.montantMax && montant > parseInt(this.filters.montantMax)) {
                return false;
            }
            
            // Filtre activité
            if (this.filters.activite && !activite.includes(this.filters.activite)) {
                return false;
            }
            
            return true;
        });
        
        this.displayFilteredResults();
        this.updateStats();
    }
    
    displayFilteredResults() {
        // Masquer toutes les lignes
        this.allRows.forEach(row => {
            row.style.display = 'none';
            this.removeHighlight(row);
        });
        
        // Afficher les résultats filtrés
        this.filteredRows.forEach(row => {
            row.style.display = '';
            if (this.filters.search) {
                this.highlightSearchTerm(row, this.filters.search);
            }
        });
        
        this.updateNoResultsMessage();
    }
    
    highlightSearchTerm(row, searchTerm) {
        const cells = row.querySelectorAll('td');
        const searchTerms = searchTerm.split(' ');
        
        cells.forEach(cell => {
            const originalHTML = cell.getAttribute('data-original-html') || cell.innerHTML;
            cell.setAttribute('data-original-html', originalHTML);
            
            let highlightedHTML = originalHTML;
            searchTerms.forEach(term => {
                if (term.trim()) {
                    const regex = new RegExp(`(${term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                    highlightedHTML = highlightedHTML.replace(regex, '<mark class="bg-warning">$1</mark>');
                }
            });
            
            cell.innerHTML = highlightedHTML;
        });
    }
    
    removeHighlight(row) {
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
            const originalHTML = cell.getAttribute('data-original-html');
            if (originalHTML) {
                cell.innerHTML = originalHTML;
            }
        });
    }
    
    updateNoResultsMessage() {
        let noResultsRow = this.tableBody.querySelector('.no-results-row');
        
        if (this.filteredRows.length === 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = `
                    <td colspan="5" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <h4>Aucun résultat trouvé</h4>
                            <p>Essayez de modifier vos critères de recherche</p>
                            <button class="btn btn-outline-primary" onclick="advancedTaxSearch.clearAllFilters()">
                                <i class="fas fa-times"></i> Effacer les filtres
                            </button>
                        </div>
                    </td>
                `;
                this.tableBody.appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }
    
    updateStats() {
        const resultsCount = document.getElementById('resultsCount');
        const totalCount = document.getElementById('totalCount');
        
        if (resultsCount) resultsCount.textContent = this.filteredRows.length;
        if (totalCount) totalCount.textContent = this.allRows.length;
    }
    
    clearAllFilters() {
        // Réinitialiser tous les filtres
        this.filters = {
            search: '',
            periodicite: '',
            montantMin: '',
            montantMax: '',
            nature: '',
            activite: ''
        };
        
        // Vider les champs
        if (this.searchInput) this.searchInput.value = '';
        
        const filterInputs = ['filterMontantMin', 'filterMontantMax', 'filterNature', 'filterActivite'];
        filterInputs.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = '';
        });
        
        this.applyFilters();
        this.showNotification('🧹 Filtres effacés', 'success');
    }
    
    exportResults() {
        const csvData = this.generateCSV();
        const blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        link.href = URL.createObjectURL(blob);
        link.download = `taxes_sicosa_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        
        this.showNotification(`📊 Export CSV généré (${this.filteredRows.length} taxes)`, 'success');
    }
    
    generateCSV() {
        const headers = ['Nom', 'Montant (FCFA)', 'Périodicité', 'Activité/Produit'];
        let csv = headers.join(',') + '\n';
        
        this.filteredRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const rowData = Array.from(cells).slice(0, 4).map(cell => {
                return `"${cell.textContent.trim().replace(/"/g, '""')}"`;
            });
            csv += rowData.join(',') + '\n';
        });
        
        return csv;
    }
    
    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        // Créer le conteneur toast s'il n'existe pas
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.appendChild(toast);
        
        const bootstrapToast = new bootstrap.Toast(toast);
        bootstrapToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toastContainer.removeChild(toast);
        });
    }
}

// Initialisation globale
let advancedTaxSearch;

document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si nous sommes sur la page des taxes
    if (document.getElementById('searchTaxes') && document.getElementById('taxesTableBody')) {
        advancedTaxSearch = new AdvancedTaxSearch();
    }
});

// Exporter pour utilisation globale
window.AdvancedTaxSearch = AdvancedTaxSearch; 