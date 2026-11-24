@extends('layouts.app')

@section('title', 'Connexion - SICOSA')

@section('content')
<div class="sicosa-login-page sicosa-fade-in" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="card-sicosa">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="{{ asset('images/logos/snd.jpeg') }}" alt="Logo SICOSA" class="mb-3 sicosa-login-logo" style="height: 80px; width: auto; border-radius: 12px; border: 3px solid var(--sicosa-accent); box-shadow: var(--sicosa-shadow);">
                            <h2 class="h3 mb-3 text-sicosa-primary fw-bold sicosa-login-title">SICOSA</h2>
                            <p class="text-sicosa-blue sicosa-login-subtitle">
                                <span class="d-none d-sm-inline">Système d'Information Commerciale de Sandiara pour la Fiscalité Locale</span>
                                <span class="d-sm-none">Système d'Information Commerciale de Sandiara</span>
                            </p>
                            <p class="text-muted small">Commune de Sandiara</p>
                        </div>
                        
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="identifiant" class="form-label">Identifiant</label>
                                <input type="text" 
                                       class="form-control @error('identifiant') is-invalid @enderror" 
                                       id="identifiant" 
                                       name="identifiant" 
                                       value="{{ old('identifiant') }}" 
                                       required 
                                       autofocus>
                                @error('identifiant')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="remember" 
                                       name="remember" 
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-sicosa-primary sicosa-login-btn">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <span class="d-none d-sm-inline">Se connecter</span>
                                    <span class="d-sm-none">Connexion</span>
                                </button>
                            </div>
                            <div class="mt-3 text-center">
                                <a href="{{ route('password.forgot') }}" class="text-sicosa-blue fw-semibold text-decoration-none">
                                    Mot de passe oublié ?
                                </a>
                            </div>
                        </form>
                        
                        <div class="mt-4 text-center">
                            <div class="alert alert-sicosa rounded-pill py-2">
                                <small>
                                    <i class="fas fa-info-circle text-sicosa-blue"></i> 
                                    Utilisez votre identifiant et mot de passe fournis par l'administration
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <small class="text-sicosa-primary fw-bold">&copy; 2025 Commune de Sandiara - SICOSA</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 