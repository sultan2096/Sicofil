<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SICOSA - Système d\'Information Commerciale de Sandiara')</title>
    
    <!-- Favicon -->
    <!-- Favicon SICOFIL (page de connexion plateforme) -->
    <link rel="icon" type="image/png" href="{{ asset('images/logos/sicofil.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logos/sicofil.png') }}">
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CORRECTION GLOBALE POUR LE SCROLL -->
    <link rel="stylesheet" href="{{ asset('css/global-scroll-fix.css') }}">
    
    <!-- CORRECTION DE LA TAILLE DES POLICES -->
    <link rel="stylesheet" href="{{ asset('css/text-size-fix.css') }}">
    
    <!-- SCRIPT DE CORRECTION D'AFFICHAGE -->
    <script src="{{ asset('js/page-display-fix.js') }}"></script>
    
    <!-- Mobile-specific optimizations -->
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SICOSA">
    
    @stack('styles')
    
    <style>
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
        
        .main-content, .container-fluid, main {
            width: 100% !important;
            min-height: auto !important;
            height: auto !important;
            padding: 1rem;
            padding-bottom: 2rem;
            overflow: visible !important;
            position: relative;
            display: block !important;
        }
        
        .card {
            overflow: visible !important;
            height: auto !important;
            min-height: auto !important;
        }
        
        /* FORCER L'AFFICHAGE COMPLET */
        * {
            max-height: none !important;
        }
    </style>
</head>
<body class="font-inter bg-white">
    <!-- Contenu principal -->
    <main class="min-h-screen">
        @yield('content')
    </main>
    
    @stack('scripts')
</body>
</html> 