@extends('layouts.login')

@section('title', 'Connexion ' . $commune->display_acronyme . ' - ' . $commune->nom)

@push('styles')
<style>
    .portal-container { min-height: 100vh; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #fef3c7 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem 1rem; }
    .portal-card { background: white; border-radius: 20px; box-shadow: 0 20px 50px rgba(15,23,42,0.12); padding: 2rem; max-width: 720px; width: 100%; }
    .portal-logo-wrap { display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
    .portal-logo { max-height: 80px; max-width: 80px; width: auto; height: auto; object-fit: contain; border-radius: 10px; }
    .portal-logo-fallback-box { width: 80px; height: 80px; border-radius: 10px; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; color: #94a3b8; }
    .portal-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; }
    .portal-subtitle { color: #64748b; margin-bottom: 1.5rem; }
    .portal-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
    .portal-link { display: block; padding: 1rem 1.25rem; border-radius: 14px; text-align: center; font-weight: 600; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; color: #fff; }
    .portal-link:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); color: #fff; }
    .portal-link.maire { background: linear-gradient(135deg, #eab308, #ca8a04); }
    .portal-link.gestionnaire { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .portal-link.collecteur { background: linear-gradient(135deg, #22c55e, #16a34a); }
</style>
@endpush

@section('content')
<div class="portal-container">
    <div class="portal-card">
        <div class="text-center mb-4">
            @if($commune->logo_url)
                <div class="portal-logo-wrap">
                    <img src="{{ $commune->logo_url }}" alt="Logo {{ $commune->nom }}" class="portal-logo" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                    <span class="portal-logo-fallback-box" style="display: none;"><i class="fas fa-city fa-2x"></i></span>
                </div>
            @else
                <div class="portal-logo-wrap">
                    <span class="portal-logo-fallback-box"><i class="fas fa-city fa-2x"></i></span>
                </div>
            @endif
            <h1 class="portal-title">{{ $commune->display_acronyme }} — {{ $commune->nom }}</h1>
            <p class="portal-subtitle mb-0">Choisissez votre profil pour vous connecter. Interface {{ $commune->display_acronyme }} (données de {{ $commune->nom }} uniquement).</p>
        </div>

        <div class="portal-links">
            <a href="{{ $maireLogin }}" class="portal-link maire">
                <i class="fas fa-crown d-block mb-2" style="font-size: 1.5rem;"></i>
                Maire
            </a>
            <a href="{{ $gestionnaireLogin }}" class="portal-link gestionnaire">
                <i class="fas fa-user-cog d-block mb-2" style="font-size: 1.5rem;"></i>
                Gestionnaire
            </a>
            <a href="{{ $collecteurLogin }}" class="portal-link collecteur">
                <i class="fas fa-mobile-alt d-block mb-2" style="font-size: 1.5rem;"></i>
                Collecteur
            </a>
        </div>
        {{-- Pas de lien "Retour à l'accueil" : les utilisateurs commune ne doivent pas accéder à la page SICOFIL --}}
    </div>
</div>
@endsection
