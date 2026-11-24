<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $layoutRole = auth()->check() ? strtolower((string) auth()->user()->role) : null;
        $layoutIsSicofil = $layoutRole === 'admin_sicofil';
        $layoutCommune = (!$layoutIsSicofil && auth()->check()) ? auth()->user()->commune : null;
        $layoutBrand = $layoutIsSicofil ? 'SICOFIL' : ($layoutCommune ? $layoutCommune->display_acronyme : (auth()->check() ? 'SICOSA' : 'SICOFIL'));
        $layoutTitleDefault = $layoutBrand . ' - Système d\'Information Commercial pour la Fiscalité Locale';
    @endphp
    <title>@yield('title', $layoutTitleDefault)</title>
    
    <!-- Favicon : SICOFIL pour admin / invité, logo commune pour maire/gestionnaire/collecteur -->
    @php
        $isSicofilFavicon = !auth()->check() || $layoutIsSicofil;
        $faviconUrl = $isSicofilFavicon ? asset('images/logos/sicofil.png') : (($layoutCommune && $layoutCommune->logo_url) ? $layoutCommune->logo_url : asset('images/logos/snd.jpeg'));
        $faviconType = $isSicofilFavicon ? 'image/png' : 'image/jpeg';
    @endphp
    <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sicosa-primary': '#1e3a8a',
                        'sicosa-secondary': '#1e40af',
                        'sicosa-accent': '#f59e0b',
                        'sicosa-blue': '#3b82f6',
                        'sicosa-green': '#10b981',
                        'sicosa-orange': '#f97316',
                        'sicosa-red': '#ef4444',
                        'sicosa-purple': '#8b5cf6',
                        'sicosa-teal': '#14b8a6',
                    },
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Bootstrap CSS (pour compatibilité) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SICOSA Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/sicosa.css') }}">
    
    <!-- Responsive CSS Global -->
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    
    <!-- SICOSA Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/sicosa-responsive.css') }}">
    
    <!-- Navigation Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/navigation-responsive.css') }}">
    
    <!-- CORRECTION GLOBALE POUR LE SCROLL -->
    <link rel="stylesheet" href="{{ asset('css/global-scroll-fix.css') }}">
    
            <!-- CORRECTION DE LA TAILLE DES POLICES -->
        <link rel="stylesheet" href="{{ asset('css/text-size-fix.css') }}">
        
        <!-- SCRIPT DE CORRECTION D'AFFICHAGE -->
        <script src="{{ asset('js/page-display-fix.js') }}"></script>
        
        <!-- SCRIPT DE CORRECTION DES BOUTONS MODALS -->
        <script src="{{ asset('js/modal-buttons-fix.js') }}"></script>
    
    <!-- Mobile-specific optimizations -->
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $layoutBrand }}">
    
    <!-- Leaflet CSS -->
    @stack('styles')
    
    <style>
        /* CORRECTION GLOBALE POUR LE SCROLL - APPLIQUÉ À TOUT LE PROJET */
        html, body {
            height: 100%;
            min-height: 100vh;
            width: 100%;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: #f8fafc;
        }
        
        .main-content, .container-fluid {
            flex: 1 0 auto;
            width: 100%;
            min-height: 1px;
            padding-bottom: 2rem;
            overflow: visible !important;
        }
        
        .card {
            overflow: visible !important;
        }
        
        /* Styles personnalisés pour la navigation */
        .nav-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        }
        
        .nav-link-hover {
            transition: all 0.3s ease;
            padding: 0.35rem 0.6rem !important;
            font-size: 0.8125rem !important;
            white-space: nowrap;
        }
        
        .nav-link-hover:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        
        .nav-link-active {
            background-color: #f59e0b !important;
            color: #1e3a8a !important;
            font-weight: 600;
        }
        
        .mobile-nav {
            background: rgba(30, 58, 138, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .dropdown-menu-custom {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            padding: 0.5rem 0;
        }
        
        .dropdown-item-custom {
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 0 0.5rem;
        }
        
        .dropdown-item-custom:hover {
            background-color: #f8fafc;
            transform: translateX(5px);
        }
        
        /* Animation pour le menu mobile */
        .mobile-menu-enter {
            transform: translateX(-100%);
            opacity: 0;
        }
        
        .mobile-menu-enter-active {
            transform: translateX(0);
            opacity: 1;
            transition: all 0.3s ease;
        }
        
        .mobile-menu-exit {
            transform: translateX(0);
            opacity: 1;
        }
        
        .mobile-menu-exit-active {
            transform: translateX(-100%);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        /* Responsive utilities */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0.5rem 1rem;
            }
            
            .nav-brand {
                font-size: 1.1rem;
            }
            
            .nav-logo {
                width: 22px;
                height: 22px;
            }
            
            .mobile-nav-item {
                padding: 1rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
            }
            
            .mobile-nav-item:hover {
                background-color: rgba(255, 255, 255, 0.1);
            }
            
            .mobile-nav-item:last-child {
                border-bottom: none;
            }
        }
        
        /* Optimisations pour les petits écrans */
        @media (max-width: 480px) {
            .nav-container {
                padding: 0.25rem 0.5rem;
            }
            
            .nav-brand {
                font-size: 1rem;
            }
            
            .nav-logo {
                width: 20px;
                height: 20px;
            }
        }
        
        /* CORRECTION GLOBALE POUR LE SCROLL - APPLIQUÉ À TOUT LE PROJET */
        html, body {
            height: auto !important;
            min-height: 100vh;
            width: 100%;
            overflow-x: hidden;
            overflow-y: auto !important;
        }
        
        body {
            display: block !important;
            min-height: 100vh;
            background: #f8fafc;
            position: relative;
        }
        
        /* CORRECTION DU LAYOUT PRINCIPAL */
        .main-content {
            width: 100% !important;
            max-width: none;
            padding: 0 1rem;
            min-height: auto !important;
            height: auto !important;
            overflow: visible !important;
            position: relative;
            display: block !important;
        }
        
        /* Assurer que toutes les pages sont visibles */
        .container-fluid {
            width: 100% !important;
            max-width: none;
            padding: 0 1rem;
            min-height: auto !important;
            height: auto !important;
            overflow: visible !important;
            position: relative;
            display: block !important;
        }
        
        /* CORRECTION DES CARTES */
        .card {
            overflow: visible !important;
            height: auto !important;
            min-height: auto !important;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-body {
            overflow: visible !important;
            height: auto !important;
            min-height: auto !important;
        }
        
        /* CORRECTION DES TABLEAUX */
        .table-responsive {
            overflow-x: auto;
            overflow-y: visible !important;
            height: auto !important;
            min-height: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        
        /* CORRECTION DES ROWS ET COLS */
        .row {
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
        }
        
        .col, .col-1, .col-2, .col-3, .col-4, .col-5, .col-6, 
        .col-7, .col-8, .col-9, .col-10, .col-11, .col-12,
        .col-sm, .col-md, .col-lg, .col-xl {
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
        }
        
        /* FORCER L'AFFICHAGE COMPLET */
        * {
            max-height: none !important;
        }

        /* Masquer tous les tooltips Bootstrap (supprime le badge jaune flottant "Carte Thématique") */
        .tooltip { display: none !important; }
        
        /* Navigation plus compacte */
        .nav-link-hover {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.85rem;
            padding: 0.4rem 0.6rem !important;
        }
        
        /* Menu déroulant amélioré */
        .group:hover .group-hover\:opacity-100 {
            opacity: 1;
        }
        
        .group:hover .group-hover\:visible {
            visibility: visible;
        }
        
        /* Correction pour les tableaux */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Assurer que les cartes sont visibles */
        .card {
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Navigation améliorée */
        .nav-link-hover {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        @media (min-width: 768px) {
            .main-content {
                padding: 0 2rem;
            }
            .container-fluid {
                padding: 0 2rem;
            }
        }
        
        @media (min-width: 1024px) {
            .main-content {
                padding: 0 3rem;
            }
            .container-fluid {
                padding: 0 3rem;
            }
        }
        
        @media (min-width: 1280px) {
            .main-content {
                padding: 0 4rem;
            }
            .container-fluid {
                padding: 0 4rem;
            }
        }
        
        /* Correction pour les petits écrans */
        @media (max-width: 767px) {
            .container-fluid {
                padding: 0 0.5rem;
            }
            .main-content {
                padding: 0 0.5rem;
            }
            .nav-link-hover {
                font-size: 0.8rem !important;
                padding: 0.25rem 0.5rem !important;
            }
        }
    </style>
</head>
<body class="font-inter bg-gray-50">
    @auth
    <!-- Navigation principale améliorée -->
    <nav class="nav-gradient shadow-lg sticky top-0 z-50">
        @php
            $roleLower = strtolower((string) (auth()->user()->role ?? ''));
            $isSicofil = $roleLower === 'admin_sicofil';
            $userCommune = $isSicofil ? null : auth()->user()->commune;
            $brandText = $isSicofil ? 'SICOFIL' : ($userCommune ? $userCommune->display_acronyme : 'SICOSA');
            $brandLogoUrl = $isSicofil ? asset('images/logos/sicofil.png') : (($userCommune && $userCommune->logo_url) ? $userCommune->logo_url : asset('images/logos/snd.jpeg'));
            $brandAvatarUrl = $brandLogoUrl;
        @endphp
        <div class="nav-container w-full px-3 sm:px-4 lg:px-6">
            <div class="flex justify-between items-center h-11">
            <!-- Logo et titre -->
                <div class="flex items-center shrink-0">
                    <a href="{{ $isSicofil ? route('admin.sicofil.dashboard') : route($roleLower . '.dashboard') }}" class="flex items-center space-x-1.5 text-white hover:text-yellow-300 transition-colors duration-300">
                        <img src="{{ $brandLogoUrl }}" alt="{{ $brandText }}" class="nav-logo rounded border border-yellow-400 shadow" style="width: 22px; height: 22px;" onerror="this.onerror=null; this.src='{{ $isSicofil ? asset('images/logos/police.PNG') : asset('images/logos/snd.jpeg') }}';">
                        <span class="nav-brand font-semibold text-sm hidden sm:block">{{ $brandText }}</span>
                        <span class="nav-brand font-semibold text-sm sm:hidden">{{ $brandText }}</span>
                    </a>
                </div>
                
                <!-- Navigation desktop (barre réduite) -->
                <div class="hidden lg:flex items-center space-x-0.5 flex-1 justify-center min-w-0 overflow-x-auto">
                    @if(auth()->user()->role === 'maire')
                        <a href="{{ route('maire.dashboard') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white whitespace-nowrap {{ request()->routeIs('maire.dashboard') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-tachometer-alt mr-1"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('users.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-users mr-1"></i>
                            <span>Utilisateurs</span>
                        </a>
                        <a href="{{ route('zones.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('zones.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-map-marked-alt mr-1"></i>
                            <span>Zones</span>
                        </a>
                        <a href="{{ route('taxes.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('taxes.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-coins mr-1"></i>
                            <span>Taxes</span>
                        </a>
                        <a href="{{ route('logs.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('logs.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-history mr-1"></i>
                            <span>Logs</span>
                        </a>
                    @elseif(auth()->user()->role === 'gestionnaire')
                        <a href="{{ route('gestionnaire.dashboard') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white whitespace-nowrap {{ request()->routeIs('gestionnaire.dashboard') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-tachometer-alt mr-1"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('collecteurs.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('collecteurs.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-users mr-1"></i>
                            <span>Collecteurs</span>
                        </a>
                        <a href="{{ route('zones.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('zones.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-map-marked-alt mr-1"></i>
                            <span>Zones</span>
                        </a>
                        <a href="{{ route('unites.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('unites.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-store mr-1"></i>
                            <span>Unités</span>
                        </a>
                        <a href="{{ route('taxes.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('taxes.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-coins mr-1"></i>
                            <span>Taxes</span>
                        </a>
                        @if(auth()->user()->role === 'collecteur')
                            <a href="{{ route('collecteur.paiements.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('collecteur.paiements.*') ? 'nav-link-active' : '' }}">
                                <i class="fas fa-money-check-alt mr-1"></i>
                                <span>Paiements</span>
                            </a>
                        @else
                            <a href="{{ route('paiements.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('paiements.*') ? 'nav-link-active' : '' }}">
                                <i class="fas fa-money-check-alt mr-1"></i>
                                <span>Paiements</span>
                            </a>
                        @endif
                        <a href="{{ route('map.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('map.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-map mr-1"></i>
                            <span>Carte</span>
                        </a>
                        <a href="{{ route('logs.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('logs.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-history mr-1"></i>
                            <span>Logs</span>
                        </a>
                    @elseif(strtolower(auth()->user()->role ?? '') === 'admin_sicofil')
                        <a href="{{ route('admin.sicofil.dashboard') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white whitespace-nowrap {{ request()->routeIs('admin.sicofil.dashboard') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-crown mr-1"></i>
                            <span>Dashboard SICOFIL</span>
                        </a>
                        <a href="{{ route('communes.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white whitespace-nowrap {{ (request()->routeIs('communes.*') && !request()->routeIs('communes.create') && !request()->routeIs('communes.map')) ? 'nav-link-active' : '' }}">
                            <i class="fas fa-city mr-1"></i>
                            <span>Gestion multi-communes</span>
                        </a>
                        <a href="{{ route('communes.map') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white whitespace-nowrap {{ request()->routeIs('communes.map') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-map mr-1"></i>
                            <span>Carte</span>
                        </a>
                        <a href="{{ route('communes.create') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('communes.create') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-plus mr-1"></i>
                            <span>Ajouter une commune</span>
                        </a>
                        <a href="{{ url('/admin-sicofil/abonnements') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white whitespace-nowrap {{ request()->routeIs('admin.sicofil.abonnements.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-receipt mr-1"></i>
                            <span>Abonnements</span>
                        </a>
                        <a href="{{ url('/admin-sicofil/factures') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white whitespace-nowrap {{ request()->routeIs('admin.sicofil.factures.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-file-invoice mr-1"></i>
                            <span>Facturation</span>
                        </a>
                    @else {{-- Collecteur --}}
                        <a href="{{ route('collecteur.dashboard') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white whitespace-nowrap {{ request()->routeIs('collecteur.dashboard') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-tachometer-alt mr-1"></i>
                            <span>Dashboard</span>
                        </a>
                        @if(auth()->user()->role === 'collecteur')
                            <a href="{{ route('collecteur.paiements.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('collecteur.paiements.*') ? 'nav-link-active' : '' }}">
                                <i class="fas fa-money-check-alt mr-1"></i>
                                <span>Paiements</span>
                            </a>
                        @else
                            <a href="{{ route('paiements.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('paiements.*') ? 'nav-link-active' : '' }}">
                                <i class="fas fa-money-check-alt mr-1"></i>
                                <span>Paiements</span>
                            </a>
                        @endif
                        <a href="{{ route('unites.index') }}" class="nav-link-hover px-3 py-2 rounded-lg text-white {{ request()->routeIs('unites.*') ? 'nav-link-active' : '' }}">
                            <i class="fas fa-store mr-1"></i>
                            <span>Unités</span>
                        </a>
                    @endif
                </div>
                
                <!-- Menu utilisateur et bouton mobile -->
                <div class="flex items-center space-x-3">
                    <!-- Menu utilisateur desktop -->
                    <div class="hidden lg:block relative group">
                        <div class="flex items-center space-x-2 cursor-pointer">
                            <img src="{{ $brandAvatarUrl }}" alt="Avatar" class="w-6 h-6 rounded-full border border-yellow-400" onerror="this.onerror=null; this.src='{{ $isSicofil ? asset('images/logos/police.PNG') : asset('images/logos/snd.jpeg') }}';">
                            <div class="text-white">
                                <div class="text-xs font-medium">{{ auth()->user()->name }}</div>
                                <div class="text-xs opacity-75">{{ strtolower(auth()->user()->role ?? '') === 'admin_sicofil' ? 'Multi-communes' : ucfirst(auth()->user()->role) }}</div>
                            </div>
                            <div class="text-white hover:text-yellow-300 transition-colors duration-300">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </div>
                        
                        <!-- Dropdown menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-right z-50">
                            @if(!in_array(strtolower(auth()->user()->role ?? ''), ['collecteur', 'admin_sicofil']))
                            <a href="{{ route('profil.edit') }}" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-t-lg text-sm border-b border-gray-100">
                                <i class="fas fa-user-cog mr-2 text-blue-600"></i>Mon profil
                            </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-b-lg text-sm transition-colors duration-200">
                                    <i class="fas fa-sign-out-alt mr-2 text-red-500"></i>Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Bouton menu mobile -->
                    <button id="mobile-menu-button" class="lg:hidden text-white hover:text-yellow-300 transition-colors duration-300">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Menu mobile -->
        <div id="mobile-menu" class="mobile-nav lg:hidden hidden">
            <div class="px-2 pt-2 pb-3 space-y-1">
                @if(auth()->user()->role === 'maire')
                    <a href="{{ route('maire.dashboard') }}" class="mobile-nav-item block text-white {{ request()->routeIs('maire.dashboard') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                    <a href="{{ route('users.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('users.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-users mr-3"></i>Utilisateurs
                    </a>
                    <a href="{{ route('zones.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('zones.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-map-marked-alt mr-3"></i>Zones
                    </a>
                    <a href="{{ route('taxes.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('taxes.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-coins mr-3"></i>Taxes
                    </a>
                    <a href="{{ route('logs.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('logs.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-history mr-3"></i>Logs
                    </a>
                    <a href="{{ route('diagnostic') }}" class="mobile-nav-item block text-white {{ request()->routeIs('diagnostic') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-stethoscope mr-3"></i>Diagnostic
                    </a>
                @elseif(auth()->user()->role === 'gestionnaire')
                    <a href="{{ route('gestionnaire.dashboard') }}" class="mobile-nav-item block text-white {{ request()->routeIs('gestionnaire.dashboard') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                    <a href="{{ route('collecteurs.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('collecteurs.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-users mr-3"></i>Collecteurs
                    </a>
                    <a href="{{ route('zones.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('zones.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-map-marked-alt mr-3"></i>Zones
                    </a>
                    <a href="{{ route('unites.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('unites.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-store mr-3"></i>Unités
                    </a>
                    <a href="{{ route('taxes.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('taxes.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-coins mr-3"></i>Taxes
                    </a>
                    <a href="{{ route('paiements.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('paiements.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-money-check-alt mr-3"></i>Paiements
                    </a>
                    <a href="{{ route('map.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('map.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-map mr-3"></i>Carte
                    </a>
                    <a href="{{ route('logs.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('logs.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-history mr-3"></i>Logs
                    </a>
                    <a href="{{ route('alerts.payment-overdue') }}" class="mobile-nav-item block text-white {{ request()->routeIs('alerts.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-exclamation-triangle text-yellow-300 mr-3"></i>Alertes
                    </a>
                    <a href="{{ route('diagnostic') }}" class="mobile-nav-item block text-white {{ request()->routeIs('diagnostic') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-stethoscope mr-3"></i>Diagnostic
                    </a>
                @elseif(strtolower(auth()->user()->role ?? '') === 'admin_sicofil')
                    <a href="{{ route('admin.sicofil.dashboard') }}" class="mobile-nav-item block text-white {{ request()->routeIs('admin.sicofil.dashboard') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-crown mr-3"></i>Dashboard SICOFIL
                    </a>
                    <a href="{{ route('communes.index') }}" class="mobile-nav-item block text-white {{ (request()->routeIs('communes.*') && !request()->routeIs('communes.create') && !request()->routeIs('communes.map')) ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-city mr-3"></i>Gestion multi-communes
                    </a>
                    <a href="{{ route('communes.map') }}" class="mobile-nav-item block text-white {{ request()->routeIs('communes.map') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-map mr-3"></i>Carte
                    </a>
                    <a href="{{ route('communes.create') }}" class="mobile-nav-item block text-white {{ request()->routeIs('communes.create') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-plus mr-3"></i>Ajouter une commune
                    </a>
                    <a href="{{ url('/admin-sicofil/abonnements') }}" class="mobile-nav-item block text-white {{ request()->routeIs('admin.sicofil.abonnements.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-receipt mr-3"></i>Abonnements
                    </a>
                    <a href="{{ url('/admin-sicofil/factures') }}" class="mobile-nav-item block text-white {{ request()->routeIs('admin.sicofil.factures.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-file-invoice mr-3"></i>Facturation
                    </a>
                @else {{-- Collecteur --}}
                    <a href="{{ route('collecteur.dashboard') }}" class="mobile-nav-item block text-white {{ request()->routeIs('collecteur.dashboard') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                    <a href="{{ route('collecteur.paiements.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('collecteur.paiements.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-money-check-alt mr-3"></i>Paiements
                    </a>
                    <a href="{{ route('unites.index') }}" class="mobile-nav-item block text-white {{ request()->routeIs('unites.*') ? 'bg-yellow-400 text-blue-900' : '' }}">
                        <i class="fas fa-store mr-3"></i>Unités
                    </a>
                @endif
                
                <!-- Menu utilisateur mobile -->
                <div class="border-t border-gray-600 pt-4 mt-4">
                    <div class="flex items-center px-4 py-2">
                        <img src="{{ $brandAvatarUrl }}" alt="Avatar" class="w-8 h-8 rounded-full border-2 border-yellow-400 mr-3" onerror="this.onerror=null; this.src='{{ $isSicofil ? asset('images/logos/police.PNG') : asset('images/logos/snd.jpeg') }}';">
                        <div class="text-white">
                            <div class="text-sm font-medium">{{ auth()->user()->name }}</div>
                            <div class="text-xs opacity-75">{{ strtolower(auth()->user()->role ?? '') === 'admin_sicofil' ? 'Multi-communes' : ucfirst(auth()->user()->role) }}</div>
                        </div>
                    </div>
                    @if(!in_array(strtolower(auth()->user()->role ?? ''), ['collecteur', 'admin_sicofil']))
                    <a href="{{ route('profil.edit') }}" class="mobile-nav-item block text-white">
                        <i class="fas fa-user-cog mr-3 text-blue-300"></i>Mon profil
                    </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                        <button type="submit" class="mobile-nav-item w-full text-left text-white hover:bg-red-600/20">
                            <i class="fas fa-sign-out-alt mr-3 text-red-300"></i>Déconnexion
                                    </button>
                                </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth
    
    <!-- Contenu principal -->
    <main class="min-h-screen bg-gray-50 main-content" id="main-content" role="main">
        @php
            $fullWidthRoutes = [
                'dashboard', 'maire.dashboard', 'gestionnaire.dashboard', 'collecteur.dashboard',
                'users', 'zones', 'unites', 'taxes', 'paiements', 'rapports', 'map', 'logs', 'profil', 'welcome',
                'communes.list-public'
            ];
            $currentRoute = request()->route()->getName();
            $isFullWidth = false;
            foreach ($fullWidthRoutes as $route) {
                if (str_contains($currentRoute ?? '', $route)) {
                    $isFullWidth = true;
                    break;
                }
            }
        @endphp
        
        @if($isFullWidth)
            <div class="w-full {{ (request()->routeIs('maire.dashboard') || request()->routeIs('gestionnaire.dashboard') || request()->routeIs('collecteur.dashboard')) ? 'dashboard-layout' : '' }}">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative m-4" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="absolute top-0 right-0 mt-3 mr-4 text-green-700 hover:text-green-900" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-4" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="absolute top-0 right-0 mt-3 mr-4 text-red-700 hover:text-red-900" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
            @yield('content')
            </div>
        @else
            <div class="w-full py-8">
                    @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="absolute top-0 right-0 mt-3 mr-4 text-green-700 hover:text-green-900" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                        </div>
                    @endif
                    @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="absolute top-0 right-0 mt-3 mr-4 text-red-700 hover:text-red-900" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                        </div>
                    @endif
                    @yield('content')
            </div>
        @endif
    </main>
    

    
    <!-- Bootstrap JS (pour compatibilité) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Navigation JavaScript -->
    <script src="{{ asset('js/navigation.js') }}"></script>
    
    <!-- Base URL pour les requêtes AJAX (support sous-dossier XAMPP) -->
    <script>
        const BASE_URL = "{{ url('/') }}";
    </script>

    @stack('scripts')
    
    @auth
    <!-- Bouton de navigation rapide flottant -->
    <div id="quick-nav-button" class="fixed bottom-4 right-4 z-50">
        <button onclick="toggleQuickNavigation()" class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-3 shadow-lg transition-all duration-300">
            <i class="fas fa-th-large"></i>
        </button>
    </div>
    
    <!-- Menu de navigation rapide -->
    <div id="quick-nav-menu" class="fixed bottom-20 right-4 z-50 bg-white rounded-lg shadow-xl p-4 hidden" style="min-width: 250px;">
        <h6 class="font-bold text-gray-800 mb-3 border-b pb-2">
            <i class="fas fa-rocket mr-2"></i>Navigation Rapide
        </h6>
        <div class="grid grid-cols-2 gap-2">
            <a href="{{ (strtolower(auth()->user()->role ?? '') === 'admin_sicofil') ? route('admin.sicofil.dashboard') : route(auth()->user()->role . '.dashboard') }}" class="block p-2 text-sm bg-blue-50 hover:bg-blue-100 text-blue-700 rounded transition-colors">
                <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
            </a>
            <a href="{{ route('users.index') }}" class="block p-2 text-sm bg-green-50 hover:bg-green-100 text-green-700 rounded transition-colors">
                <i class="fas fa-users mr-1"></i>Utilisateurs
            </a>
            <a href="{{ route('zones.index') }}" class="block p-2 text-sm bg-purple-50 hover:bg-purple-100 text-purple-700 rounded transition-colors">
                <i class="fas fa-map-marked-alt mr-1"></i>Zones
            </a>
            <a href="{{ route('unites.index') }}" class="block p-2 text-sm bg-orange-50 hover:bg-orange-100 text-orange-700 rounded transition-colors">
                <i class="fas fa-store mr-1"></i>Unités
            </a>
            <a href="{{ route('taxes.index') }}" class="block p-2 text-sm bg-red-50 hover:bg-red-100 text-red-700 rounded transition-colors">
                <i class="fas fa-coins mr-1"></i>Taxes
            </a>
            <a href="{{ auth()->user()->role === 'collecteur' ? route('collecteur.paiements.index') : route('paiements.index') }}" class="block p-2 text-sm bg-yellow-50 hover:bg-yellow-100 text-yellow-700 rounded transition-colors">
                <i class="fas fa-money-check-alt mr-1"></i>Paiements
            </a>
            <a href="{{ route('rapports.index') }}" class="block p-2 text-sm bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded transition-colors">
                <i class="fas fa-chart-line mr-1"></i>Rapports
            </a>
            <a href="{{ $isSicofil ? route('communes.map') : route('map.index') }}" class="block p-2 text-sm bg-teal-50 hover:bg-teal-100 text-teal-700 rounded transition-colors">
                <i class="fas fa-map mr-1"></i>Carte
            </a>
            @if(in_array(strtolower(auth()->user()->role ?? ''), ['maire', 'admin_sicofil']))
            <a href="{{ route('geoserver.index') }}" class="block p-2 text-sm bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded transition-colors">
                <i class="fas fa-globe mr-1"></i>GeoServer (SIG)
            </a>
            @endif
            <!-- Géomarketing supprimé de la navigation rapide -->
            <a href="{{ route('logs.index') }}" class="block p-2 text-sm bg-gray-50 hover:bg-gray-100 text-gray-700 rounded transition-colors">
                <i class="fas fa-history mr-1"></i>Logs
            </a>
            <a href="{{ route('diagnostic') }}" class="block p-2 text-sm bg-pink-50 hover:bg-pink-100 text-pink-700 rounded transition-colors">
                <i class="fas fa-stethoscope mr-1"></i>Diagnostic
            </a>
        </div>
    </div>
    @endauth
    
    <script>
    // Amélioration de la navigation
    document.addEventListener('DOMContentLoaded', function() {
        // S'assurer que toutes les pages sont visibles
        const mainContent = document.querySelector('#main-content') || document.querySelector('.main-content') || document.querySelector('.container-fluid');
        if (mainContent) {
            mainContent.style.minHeight = 'calc(100vh - 60px)';
        }
        // Sur une page tableau de bord, afficher le haut de la page (hero + stats)
        if (document.body.classList.contains('dashboard-layout') || document.querySelector('.dashboard-layout')) {
            window.scrollTo(0, 0);
        }
        
        // Améliorer la navigation mobile
        const navLinks = document.querySelectorAll('.nav-link-hover');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Ajouter un indicateur de chargement
                this.innerHTML += ' <i class="fas fa-spinner fa-spin"></i>';
                
                // Retirer l'indicateur après 2 secondes
                setTimeout(() => {
                    this.innerHTML = this.innerHTML.replace(' <i class="fas fa-spinner fa-spin"></i>', '');
                }, 2000);
            });
        });
        
        // Vérifier que toutes les routes sont accessibles
        console.log('Navigation SICOSA chargée');
        console.log('Pages disponibles:', {
            'Dashboard': '{{ route("maire.dashboard") }}',
            'Utilisateurs': '{{ route("users.index") }}',
            'Zones': '{{ route("zones.index") }}',
            'Unités': '{{ route("unites.index") }}',
            'Taxes': '{{ route("taxes.index") }}',
            'Paiements': '{{ route("paiements.index") }}',
            'Rapports': '{{ route("rapports.index") }}',
            'Carte': '{{ route("map.index") }}',
            'Logs': '{{ route("logs.index") }}'
        });
    });
    
    // Fonction pour basculer la navigation rapide
    function toggleQuickNav() {
        const nav = document.querySelector('.nav-link-hover');
        if (nav) {
            nav.classList.toggle('show');
        }
    }
    
    // Fonction pour basculer le menu de navigation rapide
    function toggleQuickNavigation() {
        const menu = document.getElementById('quick-nav-menu');
        const button = document.getElementById('quick-nav-button');
        if (!menu || !button) return;
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            button.innerHTML = '<i class="fas fa-times"></i>';
            button.classList.add('bg-red-600', 'hover:bg-red-700');
        } else {
            menu.classList.add('hidden');
            button.innerHTML = '<i class="fas fa-th-large"></i>';
            button.classList.remove('bg-red-600', 'hover:bg-red-700');
        }
    }
    
    // Fermer le menu si on clique ailleurs
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('quick-nav-menu');
        const button = document.getElementById('quick-nav-button');
        if (!menu || !button) return;
        if (!menu.contains(event.target) && !button.contains(event.target)) {
            menu.classList.add('hidden');
            button.innerHTML = '<i class="fas fa-th-large"></i>';
            button.classList.remove('bg-red-600', 'hover:bg-red-700');
        }
    });
    
    // Fonction pour vérifier l'accessibilité des pages
    function checkPageAccessibility() {
        const pages = [
            '{{ route("maire.dashboard") }}',
            '{{ route("users.index") }}',
            '{{ route("zones.index") }}',
            '{{ route("unites.index") }}',
            '{{ route("taxes.index") }}',
            '{{ route("paiements.index") }}',
            '{{ route("rapports.index") }}',
            '{{ route("map.index") }}',
            '{{ route("logs.index") }}'
        ];
        
        pages.forEach(page => {
            fetch(page, { method: 'HEAD' })
                .then(response => {
                    if (response.ok) {
                        console.log('✅ Page accessible:', page);
                    } else {
                        console.log('❌ Page inaccessible:', page);
                    }
                })
                .catch(error => {
                    console.log('❌ Erreur page:', page, error);
                });
        });
    }
    
    // Vérifier l'accessibilité au chargement
    window.addEventListener('load', checkPageAccessibility);
    </script>
</body>
</html> 