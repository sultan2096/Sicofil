<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portail Contribuable - SICOFIL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .portal-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); padding: 2.5rem; max-width: 480px; width: 100%; text-align: center; }
        .portal-icon { font-size: 4rem; color: #667eea; margin-bottom: 1rem; }
        .btn-portal { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 10px; padding: 0.75rem 2rem; font-weight: 600; color: white; transition: transform 0.2s; }
        .btn-portal:hover { transform: translateY(-2px); color: white; }
    </style>
</head>
<body>
    <div class="portal-card">
        <div class="portal-icon"><i class="fas fa-store"></i></div>
        <h2 class="mb-2">Portail Contribuable</h2>
        <p class="text-muted mb-4">Consultez vos taxes, payez en ligne et téléchargez vos reçus.</p>
        <a href="{{ route('contribuable.login') }}" class="btn btn-portal d-block w-100 mb-3">
            <i class="fas fa-sign-in-alt me-2"></i> Connexion / Inscription
        </a>
        <small class="text-muted">Connexion sécurisée par code OTP envoyé sur votre téléphone.</small>
        <hr class="my-3">
        <div class="d-flex justify-content-center gap-3 text-muted small">
            <span><i class="fas fa-shield-alt text-success"></i> Sécurisé</span>
            <span><i class="fas fa-mobile-alt text-primary"></i> Mobile</span>
            <span><i class="fas fa-file-invoice text-info"></i> Reçus PDF</span>
        </div>
    </div>
</body>
</html>
