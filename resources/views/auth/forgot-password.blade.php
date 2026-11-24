@extends('layouts.login')

@section('title', $commune ? "Mot de passe oublié - {$commune->display_acronyme}" : 'Mot de passe oublié - SICOFIL')

@section('content')
@php
    $logoUrl = ($commune && $commune->logo_url) ? $commune->logo_url : asset('images/logos/sicofil.png');
    $brandName = $commune ? $commune->display_acronyme : 'SICOFIL';
@endphp
<div class="sicofil-login-page">
    <div class="sicofil-login-card">
        <div class="sicofil-header">
            <div class="sicofil-brand">
                <div class="sicofil-logo">
                    <img src="{{ $logoUrl }}" alt="Logo {{ $brandName }}"
                         onerror="this.onerror=null;this.src='{{ asset('images/logos/sicofil.png') }}';" />
                </div>
                <div class="sicofil-brand-text">
                    <h1 class="sicofil-title">{{ $brandName }}</h1>
                    <p class="sicofil-subtitle">Réinitialisation du mot de passe</p>
                    <p class="sicofil-desc">Saisissez votre identifiant</p>
                </div>
            </div>
        </div>

        <div class="sicofil-body">
            <h2 class="sicofil-form-title">Mot de passe oublié</h2>

            @if (session('success'))
                <div class="sicofil-success sicofil-success-block">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Demande enregistrée</strong>
                        <p class="mb-0 mt-1">{{ session('success') }}</p>
                        <p class="mt-2 mb-0"><strong>Pour vous connecter à nouveau :</strong> un administrateur (gestionnaire de votre commune ou SICOFIL) doit réinitialiser votre mot de passe dans « Gestion des utilisateurs » puis vous communiquer le nouveau mot de passe.</p>
                        <a href="{{ $backUrl ?? route('login') }}" class="sicofil-back-link mt-3 d-inline-block">
                            <i class="fas fa-arrow-left"></i> Retour à la page de connexion
                        </a>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="sicofil-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first('identifiant') ?: 'Veuillez saisir votre identifiant.' }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.forgot.submit') }}" class="sicofil-form">
                @csrf
                <div class="sicofil-field">
                    <label for="identifiant">Identifiant</label>
                    <div class="sicofil-input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" id="identifiant" name="identifiant" value="{{ old('identifiant') }}" placeholder="Identifiant" required>
                    </div>
                </div>
                <div class="sicofil-actions">
                    <button type="submit" class="sicofil-btn">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer la demande
                    </button>
                    <a href="{{ $backUrl ?? route('login') }}" class="sicofil-link">
                        <i class="fas fa-arrow-left"></i>
                        Retour
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.sicofil-login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1.5rem;
    background: #eef2f7;
}
.sicofil-login-card {
    width: 100%;
    max-width: 460px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
    overflow: hidden;
}
.sicofil-header {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #fff;
    padding: 2rem 2rem 1.75rem;
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
    width: 72px;
    height: 72px;
    background: #ffffff;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    box-shadow: 0 10px 24px rgba(0,0,0,0.18);
}
.sicofil-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 10px;
}
.sicofil-title {
    font-size: 1.6rem;
    font-weight: 700;
    margin: 0;
    letter-spacing: 0.03em;
}
.sicofil-subtitle {
    font-size: 0.95rem;
    opacity: 0.92;
    margin: 0;
}
.sicofil-desc {
    font-size: 0.85rem;
    opacity: 0.88;
    margin: 0;
}
.sicofil-body {
    padding: 2rem 2.25rem 2.25rem;
}
.sicofil-form-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 1.25rem 0;
}
.sicofil-success {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #ecfdf3;
    color: #047857;
    border-radius: 8px;
    font-size: 0.9rem;
    margin-bottom: 1.25rem;
}
.sicofil-success-block p { font-size: 0.9rem; line-height: 1.4; }
.sicofil-back-link { color: #047857; font-weight: 600; text-decoration: none; }
.sicofil-back-link:hover { text-decoration: underline; color: #065f46; }
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
    gap: 1.1rem;
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
    border-radius: 12px;
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
.sicofil-input-wrap input:focus { outline: none; }
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
    padding: 0.85rem 1.6rem;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.sicofil-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(79, 70, 229, 0.35); }
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
.sicofil-link:hover { background: #f1f5f9; color: #475569; }
</style>
@endsection
