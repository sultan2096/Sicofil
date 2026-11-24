<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test connexion - SICOFIL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5" style="max-width: 520px;">
        <div class="card shadow">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">Test de connexion</h1>
                <p class="text-muted small">Utilisez cette page si la connexion normale ne fonctionne pas. En local uniquement.</p>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('test.connexion.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Identifiant</label>
                        <input type="text" name="identifiant" class="form-control" value="{{ old('identifiant', 'maire2025') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" placeholder="sicosa2025" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Se connecter (test)</button>
                </form>

                <hr class="my-4">
                <p class="small text-muted mb-2">Comptes en base :</p>
                <ul class="small mb-0">
                    @foreach($users as $u)
                        <li><strong>{{ $u->identifiant }}</strong> — {{ $u->name }} ({{ $u->role }})</li>
                    @endforeach
                </ul>
                @if($users->isEmpty())
                    <p class="small text-warning">Aucun utilisateur. Exécutez : <code>php artisan db:seed --class=UserSeeder</code></p>
                @endif
                <p class="small mt-3"><a href="{{ url('/') }}">Retour à l'accueil</a></p>
            </div>
        </div>
    </div>
</body>
</html>
