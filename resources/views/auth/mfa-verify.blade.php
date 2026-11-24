<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification OTP - SICOFIL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-3">Vérification OTP</h4>
                        <p class="text-muted">Saisissez le code OTP reçu pour finaliser la connexion.</p>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('mfa.verify') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Code OTP</label>
                                <input type="text" name="otp" class="form-control" maxlength="6" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Valider</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="text-decoration-none">Retour</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
