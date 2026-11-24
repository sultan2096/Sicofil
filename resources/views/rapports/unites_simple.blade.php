<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Unités Commerciales - SICOSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .container-main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: none;
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
        }
        .btn {
            border-radius: 10px;
            font-weight: 600;
        }
        .table th {
            background-color: #495057;
            color: white;
            font-weight: 600;
            border: none;
        }
        .badge {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }
        @media print {
            body { background: white !important; }
            .btn, .export-buttons { display: none; }
        }
    </style>
</head>
<body>
    <div class="container-main">
        <!-- En-tête -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <div class="row align-items-center">
                    <div class="col-2">
                        <img src="/images/logos/snd.jpeg" alt="Logo SICOSA" 
                             class="img-fluid rounded-circle border border-white" 
                             style="max-height: 80px;">
                    </div>
                    <div class="col-10">
                        <h1 class="h2 mb-1">RAPPORT UNITÉS COMMERCIALES</h1>
                        <p class="mb-0">SYSTÈME D'INFORMATION COMMERCIALE DE SANDIARA</p>
                    </div>
                </div>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-8">
                        <div class="mb-2">
                            <strong>Zone :</strong> Toutes les zones
                        </div>
                        <div>
                            <strong>Généré le :</strong> 25/06/2025 à 16:45 par Maire SICOSA
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="export-buttons">
                            <button class="btn btn-danger btn-sm me-1" onclick="window.print()">
                                <i class="fas fa-file-pdf me-1"></i>Export PDF
                            </button>
                            <button class="btn btn-success btn-sm me-1" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-1"></i>Export Excel
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>Imprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-store fa-3x mb-2"></i>
                        <h3>15</h3>
                        <p class="mb-0">Total unités</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <h3>12</h3>
                        <p class="mb-0">Unités actives</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-pause-circle fa-3x mb-2"></i>
                        <h3>3</h3>
                        <p class="mb-0">Unités inactives</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <i class="fas fa-money-bill-wave fa-3x mb-2"></i>
                        <h3>8</h3>
                        <p class="mb-0">Avec paiement ce mois</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des Unités Commerciales -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h3 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Liste des Unités Commerciales
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="unitesTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-store me-1"></i>Nom Commercial</th>
                                <th><i class="fas fa-user me-1"></i>Propriétaire</th>
                                <th><i class="fas fa-map-marker-alt me-1"></i>Zone</th>
                                <th><i class="fas fa-briefcase me-1"></i>Activité</th>
                                <th><i class="fas fa-toggle-on me-1"></i>Statut</th>
                                <th><i class="fas fa-phone me-1"></i>Téléphone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Boutique Alpha</strong></td>
                                <td>Moussa Diop</td>
                                <td><span class="badge bg-primary">Zone Centre</span></td>
                                <td>Commerce général</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 123 45 67</td>
                            </tr>
                            <tr>
                                <td><strong>Pharmacie Wellness</strong></td>
                                <td>Dr. Fatou Kane</td>
                                <td><span class="badge bg-primary">Zone Nord</span></td>
                                <td>Pharmacie</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 234 56 78</td>
                            </tr>
                            <tr>
                                <td><strong>Restaurant Le Baobab</strong></td>
                                <td>Amadou Thiam</td>
                                <td><span class="badge bg-primary">Zone Centre</span></td>
                                <td>Restauration</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 345 67 89</td>
                            </tr>
                            <tr>
                                <td><strong>Atelier Méca-Auto</strong></td>
                                <td>Ibrahima Sarr</td>
                                <td><span class="badge bg-primary">Zone Sud</span></td>
                                <td>Mécanique automobile</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 456 78 90</td>
                            </tr>
                            <tr>
                                <td><strong>Épicerie Moderne</strong></td>
                                <td>Aïssa Ndiaye</td>
                                <td><span class="badge bg-primary">Zone Nord</span></td>
                                <td>Épicerie</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 567 89 01</td>
                            </tr>
                            <tr>
                                <td><strong>Salon de Coiffure Élégance</strong></td>
                                <td>Mariama Fall</td>
                                <td><span class="badge bg-primary">Zone Centre</span></td>
                                <td>Coiffure</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 678 90 12</td>
                            </tr>
                            <tr>
                                <td><strong>Boulangerie Traditionnelle</strong></td>
                                <td>Omar Ba</td>
                                <td><span class="badge bg-primary">Zone Sud</span></td>
                                <td>Boulangerie</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 789 01 23</td>
                            </tr>
                            <tr>
                                <td><strong>Quincaillerie Générale</strong></td>
                                <td>Cheikh Sow</td>
                                <td><span class="badge bg-primary">Zone Nord</span></td>
                                <td>Quincaillerie</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 890 12 34</td>
                            </tr>
                            <tr>
                                <td><strong>Cyber Café Connect</strong></td>
                                <td>Khadija Diallo</td>
                                <td><span class="badge bg-primary">Zone Centre</span></td>
                                <td>Services informatiques</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 901 23 45</td>
                            </tr>
                            <tr>
                                <td><strong>Magasin de Vêtements Mode</strong></td>
                                <td>Bineta Seck</td>
                                <td><span class="badge bg-primary">Zone Sud</span></td>
                                <td>Habillement</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 012 34 56</td>
                            </tr>
                            <tr>
                                <td><strong>Station-Service Total</strong></td>
                                <td>Mamadou Gueye</td>
                                <td><span class="badge bg-primary">Zone Nord</span></td>
                                <td>Carburant</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 123 34 45</td>
                            </tr>
                            <tr>
                                <td><strong>Librairie Papeterie Scolaire</strong></td>
                                <td>Souleymane Dieng</td>
                                <td><span class="badge bg-primary">Zone Centre</span></td>
                                <td>Librairie</td>
                                <td><span class="badge bg-success">Actif</span></td>
                                <td>77 234 45 56</td>
                            </tr>
                            <tr>
                                <td><strong>Boutique d'Artisanat Local</strong></td>
                                <td>Awa Mbaye</td>
                                <td><span class="badge bg-primary">Zone Sud</span></td>
                                <td>Artisanat</td>
                                <td><span class="badge bg-warning">Inactif</span></td>
                                <td>77 345 56 67</td>
                            </tr>
                            <tr>
                                <td><strong>Cabinet Dentaire Sourire</strong></td>
                                <td>Dr. Alioune Cissé</td>
                                <td><span class="badge bg-primary">Zone Nord</span></td>
                                <td>Soins dentaires</td>
                                <td><span class="badge bg-warning">Inactif</span></td>
                                <td>77 456 67 78</td>
                            </tr>
                            <tr>
                                <td><strong>Magasin d'Électronique Tech</strong></td>
                                <td>Modou Faye</td>
                                <td><span class="badge bg-primary">Zone Centre</span></td>
                                <td>Électronique</td>
                                <td><span class="badge bg-warning">Inactif</span></td>
                                <td>77 567 78 89</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="text-center mb-4">
            <a href="/maire/dashboard" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>
                Retour au dashboard
            </a>
            <button onclick="window.print()" class="btn btn-info">
                <i class="fas fa-print me-1"></i>
                Imprimer ce rapport
            </button>
        </div>

        <!-- Footer -->
        <div class="card">
            <div class="card-body text-center bg-light">
                <p class="mb-1"><strong>SICOSA</strong> - Système d'Information Commerciale de Sandiara</p>
                <small class="text-muted">Rapport généré automatiquement le 25/06/2025 à 16:45</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToExcel() {
            const table = document.getElementById('unitesTable');
            if (table) {
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Nom Commercial,Propriétaire,Zone,Activité,Statut,Téléphone\n";
                
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cols = row.querySelectorAll('td');
                    const rowData = [];
                    cols.forEach(col => {
                        let text = col.textContent.trim().replace(/[^\w\s,-]/gi, '');
                        rowData.push(text);
                    });
                    csvContent += rowData.join(',') + '\n';
                });
                
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', 'Rapport_Unites_SICOSA_' + new Date().toISOString().split('T')[0] + '.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                alert('✅ Export Excel terminé ! Fichier téléchargé avec succès.');
            }
        }
    </script>
</body>
</html> 