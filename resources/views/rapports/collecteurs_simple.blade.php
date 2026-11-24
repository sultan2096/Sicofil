<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Collecteurs - SICOSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .container-main {
            max-width: 1200px;
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
        .progress {
            height: 25px;
            border-radius: 15px;
        }
        .progress-bar {
            border-radius: 15px;
            font-weight: bold;
        }
        .btn {
            border-radius: 10px;
            font-weight: 600;
        }
        @media print {
            body { background: white !important; }
            .btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="container-main">
        <!-- En-tête -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col-2">
                        <img src="/images/logos/snd.jpeg" alt="Logo SICOSA" 
                             class="img-fluid rounded-circle border border-white" 
                             style="max-height: 80px;">
                    </div>
                    <div class="col-10">
                        <h1 class="h2 mb-1">RAPPORT COLLECTEURS</h1>
                        <p class="mb-0">SYSTÈME D'INFORMATION COMMERCIALE DE SANDIARA</p>
                    </div>
                </div>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-6">
                        <strong>Période :</strong> 01/06/2025 au 24/06/2025
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted">Généré le 25/06/2025 à 16:30</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x mb-2"></i>
                        <h3>4</h3>
                        <p class="mb-0">Collecteurs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <i class="fas fa-money-check-alt fa-3x mb-2"></i>
                        <h3>17</h3>
                        <p class="mb-0">Paiements</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-coins fa-3x mb-2"></i>
                        <h3>466 469</h3>
                        <p class="mb-0">FCFA collectés</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <i class="fas fa-calculator fa-3x mb-2"></i>
                        <h3>27 439</h3>
                        <p class="mb-0">Moyenne</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance des Collecteurs -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Performance des Collecteurs
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Collecteur</th>
                                <th class="text-center">Paiements</th>
                                <th class="text-end">Montant Total</th>
                                <th class="text-end">Moyenne</th>
                                <th class="text-center">Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 45px; height: 45px; font-weight: bold; font-size: 18px;">
                                            A
                                        </div>
                                        <div>
                                            <strong>Ahmed Sow</strong>
                                            <br><small class="text-muted">ahmed.sow@sicosa.sn</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info fs-6">7</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">185 000 FCFA</strong>
                                </td>
                                <td class="text-end">
                                    <span class="text-primary">26 428 FCFA</span>
                                </td>
                                <td class="text-center">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%;">
                                            100.0%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 45px; height: 45px; font-weight: bold; font-size: 18px;">
                                            M
                                        </div>
                                        <div>
                                            <strong>Mamadou Ba</strong>
                                            <br><small class="text-muted">mamadou.ba@sicosa.sn</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info fs-6">5</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">142 000 FCFA</strong>
                                </td>
                                <td class="text-end">
                                    <span class="text-primary">28 400 FCFA</span>
                                </td>
                                <td class="text-center">
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 76.8%;">
                                            76.8%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 45px; height: 45px; font-weight: bold; font-size: 18px;">
                                            F
                                        </div>
                                        <div>
                                            <strong>Fatou Sow</strong>
                                            <br><small class="text-muted">fatou.sow@sicosa.sn</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info fs-6">3</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">89 000 FCFA</strong>
                                </td>
                                <td class="text-end">
                                    <span class="text-primary">29 666 FCFA</span>
                                </td>
                                <td class="text-center">
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 48.1%;">
                                            48.1%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 45px; height: 45px; font-weight: bold; font-size: 18px;">
                                            I
                                        </div>
                                        <div>
                                            <strong>Ibrahima Fall</strong>
                                            <br><small class="text-muted">ibrahima.fall@sicosa.sn</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info fs-6">2</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">50 469 FCFA</strong>
                                </td>
                                <td class="text-end">
                                    <span class="text-primary">25 234 FCFA</span>
                                </td>
                                <td class="text-center">
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 27.3%;">
                                            27.3%
                                        </div>
                                    </div>
                                </td>
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
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-1"></i>
                Imprimer ce rapport
            </button>
        </div>

        <!-- Footer -->
        <div class="card">
            <div class="card-body text-center bg-light">
                <p class="mb-1"><strong>SICOSA</strong> - Système d'Information Commerciale de Sandiara</p>
                <small class="text-muted">Rapport généré automatiquement le 25/06/2025 à 16:30</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 