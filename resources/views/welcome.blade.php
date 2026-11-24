@extends('layouts.app')

@section('title', 'Accueil - SICOFIL')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex flex-col justify-center items-center p-4 overflow-hidden">
    <div class="w-full max-w-7xl mx-auto flex flex-col justify-center items-center min-h-screen">
        <!-- Header principal — interface SICOFIL uniquement -->
        <div class="text-center mb-8 lg:mb-12 relative">
            <div class="absolute inset-0 flex items-center justify-center opacity-10 pointer-events-none">
                <img src="{{ asset('images/logos/police.PNG') }}" alt="" class="w-96 h-96 lg:w-[500px] lg:h-[500px] object-contain">
            </div>
            <div class="relative inline-block mb-6 z-10">
                <img src="{{ asset('images/logos/police.PNG') }}" alt="Symbole du Sénégal" 
                     class="logo-police-main w-20 h-20 lg:w-32 lg:h-32">
                <div class="absolute -top-2 -right-2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-sm"></i>
                </div>
            </div>
            <h1 class="text-4xl lg:text-6xl font-bold text-blue-900 mb-3 tracking-tight relative z-10">SICOFIL</h1>
            <p class="text-base lg:text-lg text-slate-600 mb-1 relative z-10">Système d'Information Commercial pour la Fiscalité Locale</p>
            <h2 class="text-lg lg:text-xl text-blue-700 mb-4 font-medium relative z-10">Gestion multi-communes</h2>
            <p class="text-sm lg:text-base text-blue-600 font-semibold tracking-wide mb-6 relative z-10">— NOUS NE GAGNONS QUE SI LA COMMUNE GAGNE —</p>
            <div class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-full shadow-lg mb-6 relative z-10">
                <i class="fas fa-globe mr-3 text-yellow-300"></i>
                <span class="text-sm lg:text-base font-semibold tracking-wide">Connexion administrateur plateforme</span>
            </div>
        </div>

        <!-- Deux cartes : Admin SICOFIL + Accéder par commune -->
        <div class="w-full max-w-2xl mx-auto mb-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="group">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl transform group-hover:scale-105 group-hover:-translate-y-2 transition-all duration-300 h-full flex flex-col">
                    <div class="p-8 flex flex-col flex-1">
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-globe text-4xl text-white"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">ADMIN SICOFIL</h3>
                            <p class="text-purple-100 text-base">Multi-communes</p>
                        </div>
                        <ul class="space-y-2 text-base mb-6 text-purple-100 flex-1">
                            <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-purple-200"></i> Gestion nationale</li>
                            <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-purple-200"></i> Toutes les communes</li>
                            <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-purple-200"></i> Carte globale</li>
                        </ul>
                        <a href="/multi-communes/login" 
                           class="bg-white text-purple-600 px-6 py-3 rounded-xl font-semibold text-center hover:bg-purple-50 transform hover:scale-105 transition-all duration-300 shadow-lg block">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Se connecter
                        </a>
                    </div>
                </div>
            </div>
            <div class="group">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl transform group-hover:scale-105 group-hover:-translate-y-2 transition-all duration-300 h-full flex flex-col">
                    <div class="p-8 flex flex-col flex-1">
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-city text-4xl text-white"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">ACCÉDER PAR COMMUNE</h3>
                            <p class="text-blue-100 text-base">Maire, Gestionnaire, Collecteur</p>
                        </div>
                        <ul class="space-y-2 text-base mb-6 text-blue-100 flex-1">
                            <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-blue-200"></i> Choisir votre commune</li>
                            <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-blue-200"></i> Connexion par rôle</li>
                            <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-blue-200"></i> Données de votre commune uniquement</li>
                        </ul>
                        <a href="{{ route('communes.list-public') }}" 
                           class="bg-white text-blue-600 px-6 py-3 rounded-xl font-semibold text-center hover:bg-blue-50 transform hover:scale-105 transition-all duration-300 shadow-lg block">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            Choisir une commune
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message d'assistance -->
        <div class="text-center mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl px-6 py-4 shadow-lg">
                <p class="text-gray-600 text-sm lg:text-base">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                    Pour toute assistance, contactez l'administration municipale
                </p>
            </div>
        </div>

        <!-- Footer -->
        <footer class="w-full bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-2xl shadow-2xl overflow-hidden relative">
            <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none">
                <img src="{{ asset('images/logos/police.PNG') }}" alt="" class="w-64 h-64 lg:w-80 lg:h-80 object-contain">
            </div>
            <div class="p-6 lg:p-8 relative z-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                    <div class="text-center md:text-left">
                        <div class="flex items-center justify-center md:justify-start space-x-4 mb-4">
                            <img src="{{ asset('images/logos/police.PNG') }}" alt="SICOFIL" 
                                 class="w-12 h-12 lg:w-16 lg:h-16 rounded-xl border-2 border-yellow-400 shadow-lg">
                            <div>
                                <h3 class="text-xl font-bold">SICOFIL</h3>
                                <p class="text-sm opacity-75">Gestion multi-communes</p>
                                <p class="text-sm opacity-75">Plateforme nationale</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="flex items-center justify-center space-x-3 mb-4">
                            <img src="{{ asset('images/logos/police.PNG') }}" alt="Symbole du Sénégal" 
                                 class="w-10 h-10 lg:w-12 lg:h-12 rounded-lg border-2 border-green-500 shadow-lg">
                            <div>
                                <h4 class="text-lg lg:text-xl font-semibold">République du Sénégal</h4>
                                <p class="text-blue-200 text-sm lg:text-base">Un Peuple - Un But - Une Foi</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center md:text-right">
                        <h4 class="text-lg lg:text-xl font-semibold mb-2">SICOFIL</h4>
                        <p class="text-blue-200 text-sm lg:text-base mb-1">Plateforme multi-communes</p>
                        <p class="text-blue-200 text-sm lg:text-base">&copy; 2025 - Tous droits réservés</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<style>
.logo-police-main { border: 4px solid #10b981; border-radius: 1rem; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3); transition: all 0.3s ease; position: relative; z-index: 10; }
.logo-police-main:hover { transform: scale(1.05); box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4); }
</style>
@endsection
