@extends('layouts.login')

@php
    $loginLogo = ($commune && $commune->logo_url) ? $commune->logo_url : null;
    $loginMairie = $commune ? 'MAIRIE DE ' . strtoupper($commune->nom) : 'MAIRIE DE SANDIARA';
    $loginAcronyme = $commune ? $commune->display_acronyme : 'SICOSA';
    $loginSubtitle = $commune ? 'Système d\'Information Commerciale de ' . $commune->nom : 'Système d\'Information Commerciale de Sandiara';
@endphp
@section('title', 'Connexion Maire - ' . $loginAcronyme)

@section('content')
<div class="sicosa-login-container">
    @if($loginLogo)
    <!-- Background avec logo commune (affiché uniquement si la commune a un logo) -->
    <div class="background-logo">
        <img src="{{ $loginLogo }}" alt="Logo" class="background-snd-logo">
    </div>
    @endif
    
    <!-- Background avec formes abstraites -->
    <div class="background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="shape shape-5"></div>
    </div>

    <!-- Formulaire de connexion -->
    <div class="login-form-container">
        <div class="login-form">
            <!-- En-tête bleu foncé avec logo -->
            <div class="login-header">
                <div class="header-content">
                    <!-- Logo (si la commune en a un) et informations de gauche -->
                    <div class="header-left">
                        <div class="logo-container">
                            @if($loginLogo)
                            <div class="logo-icon">
                                <img src="{{ $loginLogo }}" alt="Logo {{ $loginMairie }}" class="snd-logo">
                            </div>
                            @endif
                            <div class="logo-text">
                                <div class="logo-title">{{ $loginMairie }}</div>
                                <div class="logo-subtitle">Système Municipal</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Titre central -->
                    <div class="header-center">
                        <div class="system-title">{{ $loginAcronyme }}</div>
                        <div class="system-subtitle">{{ $loginSubtitle }}</div>
                    </div>
                </div>
            </div>

            <!-- Corps du formulaire -->
            <div class="login-body">
                <!-- Titre de l'interface centré -->
                <div class="interface-title-container">
                    <div class="interface-title">ESPACE MAIRE</div>
                </div>
                
                <!-- Messages d'erreur -->
                @if ($errors->any())
                    <div class="error-message">
                        <div class="error-icon">
                            <i class="fas fa-exclamation"></i>
                        </div>
                        <div class="error-text">
                            {{ $errors->first('identifiant') ?: $errors->first('password') ?: 'Une erreur est survenue. Veuillez réessayer.' }}
                        </div>
                    </div>
                @endif

                <form method="POST" action="/maire/login">
                    @csrf
                    
                    <div class="form-group">
                        <label for="identifiant" class="form-label">NOM D'UTILISATEUR</label>
                        <div class="input-container">
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <input type="text" 
                                   class="form-input @error('identifiant') is-invalid @enderror" 
                                   id="identifiant" 
                                   name="identifiant" 
                                   value="{{ old('identifiant', 'maire2025') }}" 
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">MOT DE PASSE</label>
                        <div class="input-container">
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" 
                                   class="form-input @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Mot de passe"
                                   required
                                   autocomplete="current-password">
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="action-buttons">
                        <button type="submit" class="login-button">
                            <div class="button-icon">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <span class="button-text">SE CONNECTER</span>
                        </button>
                    </div>
                    <div class="forgot-password">
                        <a href="{{ route('password.forgot', $commune ? ['commune' => $commune->code ?? $commune->id] : []) }}">Mot de passe oublié ?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-focus sur le champ identifiant
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('identifiant').focus();
});
</script>

<style>
/* Reset et base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #ffffff;
    overflow-x: hidden;
}

/* Container principal */
.sicosa-login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    padding: 2rem;
}

/* Logo SND en arrière-plan */
.background-logo {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.3;
}

.background-snd-logo {
    width: 80%;
    height: 80%;
    object-fit: contain;
    filter: blur(2px);
}

/* Formes de fond abstraites */
.background-shapes {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    overflow: hidden;
}

.shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.1;
}

.shape-1 {
    width: 200px;
    height: 200px;
    background: linear-gradient(45deg, #3b82f6, #1d4ed8);
    top: -50px;
    left: -50px;
    filter: blur(20px);
}

.shape-2 {
    width: 150px;
    height: 150px;
    background: linear-gradient(45deg, #10b981, #059669);
    bottom: -30px;
    left: 10%;
    filter: blur(15px);
}

.shape-3 {
    width: 100px;
    height: 100px;
    background: linear-gradient(45deg, #f59e0b, #d97706);
    top: 20%;
    right: 10%;
    filter: blur(10px);
}

.shape-4 {
    width: 120px;
    height: 120px;
    background: linear-gradient(45deg, #ef4444, #dc2626);
    bottom: 20%;
    right: -30px;
    filter: blur(12px);
}

.shape-5 {
    width: 80px;
    height: 80px;
    background: linear-gradient(45deg, #8b5cf6, #7c3aed);
    top: 60%;
    left: 5%;
    filter: blur(8px);
}

/* Container du formulaire */
.login-form-container {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 450px;
}

.login-form {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.login-form:hover {
    transform: translateY(-5px);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
}

/* En-tête bleu foncé */
.login-header {
    background: #1e3a8a;
    padding: 2rem 2rem 1.5rem;
    text-align: center;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.header-left, .header-right {
    display: flex;
    align-items: center;
}

.header-center {
    text-align: center;
    flex-grow: 1;
}

.back-to-home {
    margin-left: 1rem;
}

.back-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid rgba(107, 114, 128, 0.2);
}

.back-button:hover {
    background: rgba(107, 114, 128, 0.2);
    color: #374151;
    text-decoration: none;
    transform: translateY(-1px);
}

.back-button i {
    font-size: 0.9rem;
}

.back-button span {
    font-weight: 600;
}

.logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.logo-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    overflow: hidden;
}

.snd-logo {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.logo-text {
    text-align: left;
}

.logo-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.2rem;
}

.logo-subtitle {
    font-size: 0.7rem;
    opacity: 0.8;
}

.system-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    letter-spacing: 2px;
}

.system-subtitle {
    font-size: 0.9rem;
    opacity: 0.9;
    line-height: 1.4;
}

.interface-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e40af;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0;
}

/* Corps du formulaire */
.login-body {
    padding: 2rem;
}

/* Titre de l'interface centré */
.interface-title-container {
    text-align: center;
    margin-bottom: 1.5rem;
}

/* Messages d'erreur */
.error-message {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    background: #ef4444;
    color: white;
}

.error-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    font-size: 0.8rem;
}

.error-text {
    font-size: 0.9rem;
    font-weight: 500;
}

/* Groupes de formulaire */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.input-container {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 1rem;
    color: #9ca3af;
    font-size: 1rem;
    z-index: 2;
}

.form-input {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 1rem;
    background: white;
    transition: all 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #1e3a8a;
    box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
}

.form-input.is-invalid {
    border-color: #ef4444;
}

/* Bouton de connexion */
.login-button {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(30, 64, 175, 0.3);
}

.login-button:hover {
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(30, 64, 175, 0.4);
}

.button-icon {
    margin-right: 0.75rem;
    font-size: 0.9rem;
}

.button-text {
    font-weight: 700;
}

/* Boutons d'action */
.action-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    gap: 1rem;
}
/* Mot de passe oublié */
.forgot-password {
    margin-top: 0.75rem;
    text-align: center;
}
.forgot-password a {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.9rem;
}
.forgot-password a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
    .sicosa-login-container {
        padding: 1rem;
    }
    
    .login-form {
        border-radius: 15px;
    }
    
    .login-header {
        padding: 1.5rem 1.5rem 1rem;
    }
    
    .system-title {
        font-size: 2rem;
    }
    
    .login-body {
        padding: 1.5rem;
    }
    
    .form-input {
        padding: 0.875rem 1rem 0.875rem 2.75rem;
        font-size: 0.95rem;
    }
    
    .login-button {
        padding: 0.875rem;
        font-size: 0.95rem;
    }
}

@media (max-width: 480px) {
    .logo-container {
        flex-direction: column;
        text-align: center;
    }
    
    .logo-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .system-title {
        font-size: 1.75rem;
    }
}
</style>
@endsection 