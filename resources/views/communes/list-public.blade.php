@extends('layouts.app')

@section('title', 'Accéder par commune - SICOFIL')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-blue-900 mb-2">Accéder par commune</h1>
            <p class="text-slate-600">Choisissez votre commune pour accéder au portail de connexion (Maire, Gestionnaire, Collecteur).</p>
            <a href="{{ url('/') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mt-4">
                <i class="fas fa-arrow-left mr-2"></i> Retour à l'accueil
            </a>
        </div>

        @if($communes->isEmpty())
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <i class="fas fa-city text-4xl text-slate-300 mb-4"></i>
                <p class="text-slate-600">Aucune commune disponible pour le moment.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($communes as $commune)
                    @php
                        $portalCode = $commune->code ?: $commune->id;
                        $portalUrl = route('commune.portal', ['code' => $portalCode]);
                    @endphp
                    <a href="{{ $portalUrl }}" class="block bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 text-center border border-slate-100 hover:border-blue-200">
                        @if($commune->logo_url)
                            <div class="commune-card-logo-wrap mb-3">
                                <img src="{{ $commune->logo_url }}" alt="Logo {{ $commune->nom }}" class="commune-card-logo" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span class="commune-card-logo-fallback" style="display: none;"><i class="fas fa-city"></i></span>
                            </div>
                        @else
                            <div class="commune-card-logo-wrap commune-card-logo-fallback mb-3">
                                <i class="fas fa-city text-2xl text-slate-400"></i>
                            </div>
                        @endif
                        <h2 class="font-semibold text-lg text-blue-900">{{ $commune->display_acronyme }}</h2>
                        <p class="text-sm text-slate-600">{{ $commune->nom }}</p>
                        @if($commune->region)
                            <p class="text-xs text-slate-400 mt-1">{{ $commune->region }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
<style>
    /* Affichage du logo comme dans la liste des communes (capture 4) */
    .commune-card-logo-wrap {
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: auto;
        margin-right: auto;
    }
    .commune-card-logo {
        max-height: 64px;
        max-width: 80px;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 8px;
    }
    .commune-card-logo-fallback {
        width: 64px;
        height: 64px;
        border-radius: 8px;
        background: #f1f5f9;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
