@extends('layouts.login')

@section('title', 'Gestion multi-communes - SICOFIL')

@section('content')
<div class="sicofil-login-page">
    <div class="sicofil-login-card">
        <div class="sicofil-header">
            <div class="sicofil-brand">
                <div class="sicofil-logo">
                    <img src="{{ asset('images/logos/sicofil.png') }}" alt="Logo SICOFIL"
                         onerror="this.onerror=null;this.src='{{ asset('images/logos/police.PNG') }}';" />
                </div>
                <div class="sicofil-brand-text">
                    <h1 class="sicofil-title">SICOFIL</h1>
                    <p class="sicofil-subtitle">Système d'Information Commercial pour la Fiscalité Locale</p>
                    <p class="sicofil-desc">Gestion multi-communes • Tableau de bord • Toutes les communes</p>
                    <p class="sicofil-slogan">— NOUS NE GAGNONS QUE SI LA COMMUNE GAGNE —</p>
                </div>
            </div>
        </div>

        <div class="sicofil-body">
            <h2 class="sicofil-form-title">Accès administrateur SICOFIL</h2>

            @if ($errors->any())
                <div class="sicofil-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first('identifiant') ?: $errors->first('password') ?: 'Une erreur est survenue. Veuillez réessayer.' }}</span>
                </div>
            @endif

            <form method="POST" action="/multi-communes/login" class="sicofil-form">
                @csrf
                <div class="sicofil-field">
                    <label for="identifiant">Identifiant</label>
                    <div class="sicofil-input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" id="identifiant" name="identifiant" value="{{ old('identifiant') }}" placeholder="Identifiant" required autofocus>
                    </div>
                </div>
                <div class="sicofil-field">
                    <label for="password">Mot de passe</label>
                    <div class="sicofil-input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                    </div>
                </div>
                <div class="sicofil-actions">
                    <button type="submit" class="sicofil-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Se connecter
                    </button>
                </div>
                <div class="sicofil-forgot">
                    <a href="{{ route('password.forgot') }}">Mot de passe oublié ?</a>
                </div>
            </form>
            <p class="text-center mt-3">
                <a href="{{ url('/') }}" class="sicofil-forgot a">← Retour à l'accueil</a>
            </p>
        </div>
    </div>
</div>

<style>
.sicofil-login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1.5rem;
    background: #eef2f7;
}
.sicofil-login-card {
    width: 100%;
    max-width: 420px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.12);
    overflow: hidden;
}
.sicofil-header {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #fff;
    padding: 1.5rem 1.75rem 1.25rem;
}
.sicofil-brand {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.sicofil-brand-text {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.sicofil-logo {
    width: 60px;
    height: 60px;
    background: #ffffff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.16);
}
.sicofil-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 10px;
}
.sicofil-title {
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0;
    letter-spacing: 0.03em;
}
.sicofil-subtitle {
    font-size: 0.9rem;
    opacity: 0.92;
    margin: 0;
}
.sicofil-desc {
    font-size: 0.8rem;
    opacity: 0.88;
    margin: 0;
}
.sicofil-slogan {
    font-size: 0.75rem;
    opacity: 0.95;
    margin: 0.35rem 0 0 0;
    font-weight: 600;
    letter-spacing: 0.02em;
}
.sicofil-body {
    padding: 1.75rem 2rem 2rem;
}
.sicofil-form-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 1rem 0;
}
.sicofil-error {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #fef2f2;
    color: #b91c1c;
    border-radius: 8px;
    font-size: 0.9rem;
    margin-bottom: 1.25rem;
}
.sicofil-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.sicofil-field label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.4rem;
}
.sicofil-input-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.sicofil-input-wrap:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
}
.sicofil-input-wrap i {
    color: #94a3b8;
    font-size: 1rem;
}
.sicofil-input-wrap input {
    flex: 1;
    border: none;
    background: none;
    padding: 0.9rem 0;
    font-size: 1rem;
    color: #1e293b;
}
.sicofil-input-wrap input::placeholder {
    color: #94a3b8;
}
.sicofil-input-wrap input:focus {
    outline: none;
}
.sicofil-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.25rem;
}
.sicofil-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem 1.4rem;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.sicofil-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(79, 70, 229, 0.35);
}
.sicofil-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.85rem 1rem;
    color: #64748b;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    border-radius: 10px;
    transition: background 0.2s, color 0.2s;
}
.sicofil-link:hover {
    background: #f1f5f9;
    color: #475569;
}
.sicofil-forgot {
    margin-top: 0.85rem;
    text-align: center;
}
.sicofil-forgot a {
    color: #4f46e5;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.9rem;
}
.sicofil-forgot a:hover {
    text-decoration: underline;
}
@media (max-width: 480px) {
    .sicofil-login-page { padding: 1rem; }
    .sicofil-header, .sicofil-body { padding: 1.5rem; }
}
</style>
@endsection
