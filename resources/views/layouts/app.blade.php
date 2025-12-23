<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HotelPro') }}</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1a4b8c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Hotel Pro">
    <link rel="apple-touch-icon" href="{{ asset('Template/logo.jpg') }}">
    
    <!-- Bootstrap & Icons -->
    <link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <!-- Optimisation des polices Google Fonts avec font-display: swap -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Les polices sont maintenant chargées de manière asynchrone via preload plus bas -->
    
    <!-- DataTables -->
    <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/datatables/responsive.bootstrap5.min.css') }}" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}">
    
    <!-- Design System -->
    <link href="{{ asset('css/design-system.css') }}" rel="stylesheet">
    
    <!-- Dark Mode -->
    <link href="{{ asset('css/dark-mode.css') }}" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        /* Styles pour les notifications style Facebook */
        .notification-item {
            transition: background-color 0.2s ease;
            border-radius: 4px;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa !important;
        }
        
        .notification-item.notification-unread {
            background-color: #e7f3ff !important;
            border-left: 3px solid #0d6efd;
        }
        
        .notification-icon-wrapper {
            flex-shrink: 0;
        }
        
        #notifications-dropdown .dropdown-item {
            padding: 0;
        }
        
        #notifications-dropdown .notification-item {
            padding: 0.5rem;
        }
        
        /* Styles pour les notifications modernes */
        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modern-notification-popup {
            border-radius: 16px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
            padding: 0 !important;
            overflow: hidden;
        }
        
        .modern-notification-title {
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            color: #2c3e50 !important;
            margin-bottom: 0 !important;
            padding: 20px 24px 0 24px !important;
        }
        
        .modern-notification-content {
            padding: 0 24px 20px 24px !important;
        }
        
        .modern-notification-button {
            border-radius: 8px !important;
            padding: 10px 24px !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
        }
        
        .modern-notification-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }
        
        .modern-notification-button-cancel {
            border-radius: 8px !important;
            padding: 10px 24px !important;
            font-weight: 500 !important;
        }
        
        .swal2-timer-progress-bar {
            background: linear-gradient(90deg, #0d6efd, #0a58ca) !important;
        }
        :root {
            /* Variables UI Settings injectées dynamiquement */
            @php
                try {
                    $uiSettings = \App\Models\UiSetting::getCssVariables();
                    echo $uiSettings;
                } catch (\Exception $e) {
                    // En cas d'erreur (table non créée, etc.), on continue sans les variables
                }
            @endphp
            --primary-blue: #1a4b8c;
            --content-bg: #EBF2FA;
            --text-dark: #2c3e50;
            --brand-yellow: #e19f32;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --border-color: #e9ecef;
            --card-bg: #ffffff;
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 80px;
            --sidebar-width-tablet: 70px;
            --sidebar-width-mobile: 60px;
            
            /* Tailles d'icônes standardisées */
            --icon-xs: 0.875rem;      /* 14px */
            --icon-sm: 1rem;          /* 16px */
            --icon-md: 1.25rem;       /* 20px */
            --icon-lg: 1.5rem;        /* 24px */
            --icon-xl: 2rem;          /* 32px */
            --icon-xxl: 3rem;         /* 48px */
        }

        [data-theme="dark"] {
            --primary-blue: #8ab4f8;
            --content-bg: #1a1a1a;
            --text-dark: #e8eaed;
            --brand-yellow: #fdd835;
            --white: #1e1e1e;
            --light-gray: #2d2d2d;
            --border-color: #4a4a4a;
            --card-bg: #2d2d2d;
        }
        
        /* Uniformisation des textes en mode sombre */
        [data-theme="dark"] .card,
        [data-theme="dark"] .card-body,
        [data-theme="dark"] .card-header,
        [data-theme="dark"] .card-footer,
        [data-theme="dark"] .list-group-item {
            color: var(--text-dark);
        }
        
        [data-theme="dark"] .card *:not(.badge):not(.btn):not(.alert):not(.text-muted),
        [data-theme="dark"] .list-group-item *:not(.badge):not(.btn):not(.alert):not(.text-muted) {
            color: var(--text-dark);
        }

        body {
            background-color: var(--content-bg);
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-blue);
        }
        
        h1 { font-size: var(--font-size-h1, var(--font-size-3xl, 2.25rem)); }
        h2 { font-size: var(--font-size-h2, var(--font-size-2xl, 1.875rem)); }
        h3 { font-size: var(--font-size-h3, var(--font-size-xl, 1.5rem)); }
        h4 { font-size: var(--font-size-h4, var(--font-size-lg, 1.25rem)); }
        h5 { font-size: var(--font-size-h5, var(--font-size-md, 1.125rem)); }
        h6 { font-size: var(--font-size-base, 1rem); }
        
        body {
            font-size: var(--font-size-base, var(--font-size-base, 1rem));
        }

        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-blue);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1030;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        [data-theme="dark"] .sidebar {
            background-color: var(--white);
            border-right: 1px solid var(--border-color);
        }

        .top-navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height, 60px);
            background-color: rgba(255, 255, 255, 0.7);
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
            z-index: 1020;
            padding: 0 var(--spacing-xl, 2rem);
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        [data-theme="dark"] .top-navbar {
            background-color: rgba(30, 30, 30, 0.7);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: var(--main-padding, var(--spacing-xl, 2rem));
            padding-top: calc(var(--topbar-height, 60px) + var(--spacing-lg, 1.5rem));
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-collapsed .sidebar {
            width: var(--sidebar-width-collapsed);
        }
        .sidebar-collapsed .top-navbar,
        .sidebar-collapsed .main-content {
            margin-left: var(--sidebar-width-collapsed);
            left: var(--sidebar-width-collapsed);
        }
        .sidebar-collapsed .nav-text,
        .sidebar-collapsed .sidebar-brand span,
        .sidebar-collapsed .accordion-button .nav-text,
        .sidebar-collapsed .accordion-body,
        .sidebar-collapsed .accordion-button::after {
            display: none;
        }
        .sidebar-collapsed .accordion-button,
        .sidebar-collapsed .nav-link {
            justify-content: center;
        }

        .sidebar-header {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem 0 1.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        [data-theme="dark"] .sidebar-header {
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar-brand {
            font-family: 'Playfair Display', serif;
            color: var(--brand-yellow);
            font-size: var(--font-size-h2, var(--font-size-2xl, 1.875rem));
            font-weight: 700;
            text-decoration: none;
        }
        .sidebar-toggle {
            background: none; border: none; color: var(--white); font-size: 1.5rem; cursor: pointer;
        }
        [data-theme="dark"] .sidebar-toggle { color: var(--text-dark); }

        .sidebar-menu { 
            padding: 1rem 0;
            height: calc(100vh - 60px);
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
            -webkit-overflow-scrolling: touch; /* Scroll fluide sur iOS */
        }
        .sidebar-menu::-webkit-scrollbar { display: none; }
        
        /* Activer le scroll visible sur mobile */
        @media (max-width: 991px) {
            .sidebar-menu {
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
                scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
                -webkit-scrollbar-width: thin;
                -webkit-scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
            }
            .sidebar-menu::-webkit-scrollbar {
                display: block;
                width: 4px;
            }
            .sidebar-menu::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.05);
            }
            .sidebar-menu::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.3);
                border-radius: 2px;
            }
            .sidebar-menu::-webkit-scrollbar-thumb:hover {
                background: rgba(255, 255, 255, 0.5);
            }
        }

        .nav-link, .accordion-button {
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.8rem 1rem;
            margin: 0 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            white-space: nowrap;
            text-decoration: none;
            position: relative;
        }
        [data-theme="dark"] .nav-link, [data-theme="dark"] .accordion-button { color: var(--text-dark); }
            
        .nav-link:hover, .accordion-button:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        [data-theme="dark"] .nav-link:hover, [data-theme="dark"] .accordion-button:hover {
            background-color: var(--light-gray);
        }

        #nav-list > .nav-item > .nav-link.active {
            background-color: rgba(0,0,0,0.2);
        }
        [data-theme="dark"] #nav-list > .nav-item > .nav-link.active {
            background-color: var(--light-gray);
        }

        .accordion-body { padding: 0; }
        .accordion-body .nav-link { 
            padding-left: 2.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .accordion-body .nav-link .bi {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            flex-shrink: 0;
        }

        .accordion-body .nav-link.active {
            background-color: var(--content-bg);
            color: var(--primary-blue) !important;
            font-weight: 600;
            margin-right: 0;
            border-radius: 8px 0 0 8px;
        }
        [data-theme="dark"] .accordion-body .nav-link.active { color: var(--primary-blue) !important; }

        .accordion-body .nav-link.active::before,
        .accordion-body .nav-link.active::after {
            content: '';
            position: absolute;
            right: -20px;
            width: 20px;
            height: 20px;
            background: transparent;
            transition: box-shadow 0.3s ease;
        }
        .accordion-body .nav-link.active::before {
            top: -20px;
            box-shadow: 10px 10px 0 10px var(--content-bg);
            border-bottom-left-radius: 20px;
        }
        .accordion-body .nav-link.active::after {
            bottom: -20px;
            box-shadow: 10px -10px 0 10px var(--content-bg);
            border-top-left-radius: 20px;
        }

        .accordion-item, .accordion-header, .accordion-button { background: transparent; border: none; box-shadow: none; }
        .accordion-button:not(.collapsed) { background: none; color: var(--white); }
        [data-theme="dark"] .accordion-button:not(.collapsed) { color: var(--primary-blue); }
        .accordion-button::after { filter: brightness(0) invert(1); }
        [data-theme="dark"] .accordion-button::after { filter: none; }

        .top-bar-icon {
            background-color: var(--light-gray);
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .top-bar-icon:hover { background-color: var(--border-color); }
        [data-theme="dark"] .top-bar-icon { background-color: var(--white); color: var(--text-dark); }
        [data-theme="dark"] .top-bar-icon:hover { background-color: var(--light-gray); }

        .breadcrumb { background-color: transparent; padding: 0; margin-bottom: 1.5rem; }
        .breadcrumb-item a { text-decoration: none; color: var(--primary-blue); }
        .breadcrumb-item.active { color: var(--text-dark); }

        .content-frame {
            background-color: var(--white);
            padding: var(--content-frame-padding, var(--spacing-xl, 2rem));
            border-radius: var(--radius-xl, 1rem);
            box-shadow: var(--shadow-md, 0 4px 6px rgba(0,0,0,0.1));
            min-height: 70vh;
        }
        [data-theme="dark"] .content-frame { border: 1px solid var(--border-color); }

        .theme-switch {
            position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px;
            border-radius: 50%; background-color: var(--white); color: var(--primary-blue);
            border: 1px solid var(--border-color); font-size: 1.5rem; display: flex;
            align-items: center; justify-content: center; cursor: pointer; z-index: 1100;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .top-bar-icon .badge { pointer-events: none; }
        
        /* ==========================================
           RESPONSIVE PROGRESSIF - WHATSAPP STYLE
           ========================================== */
        
        /* Desktop Large (> 1400px) - Sidebar complète */
        @media (min-width: 1400px) {
            :root {
                --sidebar-width: 260px;
            }
        }
        
        /* Desktop Medium (1200px - 1399px) - Sidebar complète mais légèrement réduite */
        @media (max-width: 1399px) and (min-width: 1200px) {
            :root {
                --sidebar-width: 240px;
            }
        }
        
        /* Desktop Small / Large Tablet (992px - 1199px) - Sidebar avec icônes + texte réduit */
        @media (max-width: 1199px) and (min-width: 992px) {
            :root {
                --sidebar-width: 200px;
            }
            .sidebar .nav-text,
            .sidebar .logo-text {
                font-size: 0.85rem;
            }
            .sidebar .nav-link,
            .sidebar .accordion-button {
                padding: 0.7rem 0.8rem;
            }
        }
        
        /* Tablet (768px - 991px) - Sidebar mode icônes avec texte au survol */
        @media (max-width: 991px) and (min-width: 768px) {
            :root {
                --sidebar-width: var(--sidebar-width-tablet);
            }
            .sidebar {
                overflow: visible !important; /* Permettre le scroll */
            }
            .sidebar .nav-text,
            .sidebar .logo-text,
            .sidebar .accordion-button .nav-text,
            .sidebar .accordion-button::after {
                display: none;
            }
            .sidebar .nav-link,
            .sidebar .accordion-button {
                justify-content: center;
                padding: 0.8rem 0.5rem;
            }
            .sidebar-logo {
                padding: 15px 10px !important;
            }
            .sidebar-logo .logo-img {
                max-width: 50px !important;
                width: 50px !important;
                height: 50px !important;
            }
            .top-navbar {
                padding: 0 1rem;
            }
            .top-navbar form {
                max-width: 200px;
            }
            .top-navbar form input {
                font-size: 0.85rem;
            }
            .main-content {
                padding: 1.5rem;
            }
        }
        
        /* Mobile Large (576px - 767px) - Sidebar compacte */
        @media (max-width: 767px) and (min-width: 576px) {
            :root {
                --sidebar-width: var(--sidebar-width-mobile);
            }
            .sidebar {
                overflow: visible !important; /* Permettre le scroll */
            }
            .sidebar .nav-text,
            .sidebar .logo-text,
            .sidebar .accordion-button .nav-text,
            .sidebar .accordion-button::after {
                display: none;
            }
            .sidebar .nav-link,
            .sidebar .accordion-button {
                justify-content: center;
                padding: 0.7rem 0.3rem;
            }
            .sidebar-logo {
                padding: 12px 8px !important;
            }
            .sidebar-logo .logo-img {
                max-width: 45px !important;
                width: 45px !important;
                height: 45px !important;
            }
            .top-navbar {
                padding: 0 0.75rem;
                height: 55px;
            }
            .top-navbar form {
                max-width: 150px;
                flex: 0 0 auto;
            }
            .top-navbar form input {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
            .top-navbar .d-flex.align-items-center span {
                display: none;
            }
            .main-content {
                padding: 1rem;
                padding-top: 75px;
            }
            #page-title {
                font-size: 1rem !important;
            }
        }
        
        /* Mobile Small (< 576px) - Sidebar très compacte */
        @media (max-width: 575px) {
            :root {
                --sidebar-width: 55px;
            }
            .sidebar {
                overflow: visible !important; /* Permettre le scroll */
            }
            .sidebar .nav-text,
            .sidebar .logo-text,
            .sidebar .accordion-button .nav-text,
            .sidebar .accordion-button::after {
                display: none;
            }
            .sidebar .nav-link,
            .sidebar .accordion-button {
                justify-content: center;
                padding: 0.65rem 0.25rem;
                margin: 0 0.5rem;
            }
            .sidebar-logo {
                padding: 10px 5px !important;
            }
            .sidebar-logo .logo-img {
                max-width: 40px !important;
                width: 40px !important;
                height: 40px !important;
            }
            .top-navbar {
                padding: 0 0.5rem;
                height: 50px;
            }
            .top-navbar form {
                max-width: 120px;
                flex: 0 0 auto;
            }
            .top-navbar form input {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }
            .top-navbar .d-flex.align-items-center span {
                display: none;
            }
            .top-bar-icon {
                width: 35px;
                height: 35px;
            }
            .main-content {
                padding: 0.75rem;
                padding-top: 70px;
            }
            #page-title {
                font-size: 0.9rem !important;
            }
            .content-frame {
                padding: 1rem;
            }
        }
        
        /* Ajustements pour le mode collapsed manuel */
        .sidebar-collapsed .sidebar {
            width: var(--sidebar-width-collapsed);
        }
        .sidebar-collapsed .top-navbar,
        .sidebar-collapsed .main-content {
            margin-left: var(--sidebar-width-collapsed);
            left: var(--sidebar-width-collapsed);
        }
        
        /* DataTables Styling */
        .dataTables_wrapper { font-family: 'Poppins', sans-serif; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate { color: var(--text-dark); }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { color: #999 !important; }
        table.dataTable thead th { border-bottom: 2px solid var(--primary-blue) !important; }
        table.dataTable.no-footer { border-bottom: 1px solid var(--border-color); }
        .dataTables_wrapper .dataTables_filter input { border-radius: 8px; border: 1px solid var(--border-color); padding: 0.375rem 0.75rem; }
        .dataTables_wrapper .dataTables_length select { border-radius: 8px; border: 1px solid var(--border-color); padding: 0.375rem 0.75rem; }
        .dt-buttons { margin-bottom: 1rem; }
        .dt-buttons .btn { margin-right: 0.5rem; border-radius: 8px; }
        [data-theme="dark"] table.dataTable thead th { border-bottom-color: var(--primary-blue) !important; }
        [data-theme="dark"] .dataTables_wrapper .dataTables_filter input,
        [data-theme="dark"] .dataTables_wrapper .dataTables_length select { 
            background-color: var(--white); 
            color: var(--text-dark); 
            border-color: var(--border-color);
        }

        /* ==========================================
           SYSTÈME D'ICÔNES MODERNE ET UNIFORME
           ========================================== */
        
        /* Tailles d'icônes standardisées */
        .icon-xs, .bi.icon-xs { font-size: var(--icon-xs) !important; }
        .icon-sm, .bi.icon-sm { font-size: var(--icon-sm) !important; }
        .icon-md, .bi.icon-md { font-size: var(--icon-md) !important; }
        .icon-lg, .bi.icon-lg { font-size: var(--icon-lg) !important; }
        .icon-xl, .bi.icon-xl { font-size: var(--icon-xl) !important; }
        .icon-xxl, .bi.icon-xxl { font-size: var(--icon-xxl) !important; }
        
        /* Icônes dans les boutons - taille adaptée */
        .btn .bi { 
            font-size: var(--icon-sm);
            vertical-align: middle;
            margin-right: 0.375rem;
        }
        .btn-sm .bi { font-size: var(--icon-xs); margin-right: 0.25rem; }
        .btn-lg .bi { font-size: var(--icon-md); margin-right: 0.5rem; }
        
        /* Icônes dans les alertes */
        .alert .bi {
            font-size: var(--icon-lg);
            vertical-align: middle;
            margin-right: 0.5rem;
        }
        
        /* Icônes dans les titres */
        h1 .bi { font-size: var(--icon-xl); }
        h2 .bi { font-size: var(--icon-lg); }
        h3 .bi { font-size: var(--icon-md); }
        h4 .bi, h5 .bi, h6 .bi { font-size: var(--icon-sm); }
        
        /* Icônes dans les badges */
        .badge .bi {
            font-size: calc(var(--icon-xs) * 0.875);
            vertical-align: baseline;
        }
        
        /* Icônes dans les cartes - headers */
        .card-header .bi {
            font-size: var(--icon-md);
            margin-right: 0.5rem;
        }
        
        /* Icônes dans la sidebar */
        .sidebar .nav-link .bi,
        .sidebar .accordion-button .bi {
            font-size: var(--icon-md);
            min-width: var(--icon-md);
            text-align: center;
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Garantir la visibilité des icônes sur mobile */
        @media (max-width: 991px) {
            .sidebar .nav-link .bi,
            .sidebar .accordion-button .bi {
                display: inline-block !important;
                visibility: visible !important;
                opacity: 1 !important;
                font-size: 1.25rem !important;
                min-width: 1.25rem !important;
            }
            
            .accordion-body .nav-link .bi {
                display: inline-block !important;
                visibility: visible !important;
                opacity: 1 !important;
                font-size: 1.1rem !important;
                margin-right: 0.5rem;
            }
        }
        
        /* Icônes dans la topbar */
        .top-navbar .bi {
            font-size: var(--icon-md);
        }
        
        /* Conteneurs d'icônes modernes */
        .icon-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .icon-container.icon-sm {
            width: 32px;
            height: 32px;
            padding: 6px;
        }
        
        .icon-container.icon-md {
            width: 40px;
            height: 40px;
            padding: 8px;
        }
        
        .icon-container.icon-lg {
            width: 56px;
            height: 56px;
            padding: 12px;
        }
        
        .icon-container.icon-xl {
            width: 72px;
            height: 72px;
            padding: 16px;
        }
        
        /* Couleurs d'icônes */
        .icon-primary { color: var(--primary-blue) !important; }
        .icon-secondary { color: #6c757d !important; }
        .icon-success { color: #198754 !important; }
        .icon-danger { color: #dc3545 !important; }
        .icon-warning { color: #ffc107 !important; }
        .icon-info { color: #0dcaf0 !important; }
        
        /* Conteneurs d'icônes avec backgrounds */
        .icon-container.bg-primary-soft { 
            background-color: rgba(26, 75, 140, 0.1); 
            color: var(--primary-blue);
        }
        .icon-container.bg-success-soft { 
            background-color: rgba(25, 135, 84, 0.1); 
            color: #198754;
        }
        .icon-container.bg-danger-soft { 
            background-color: rgba(220, 53, 69, 0.1); 
            color: #dc3545;
        }
        .icon-container.bg-warning-soft { 
            background-color: rgba(255, 193, 7, 0.1); 
            color: #ffc107;
        }
        .icon-container.bg-info-soft { 
            background-color: rgba(13, 202, 240, 0.1); 
            color: #0dcaf0;
        }
        
        /* Effets hover pour les conteneurs d'icônes */
        .icon-container:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Cartes modernes avec icônes */
        .modern-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .modern-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        
        .modern-card .card-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #2563a8 100%);
            color: white;
            border: none;
            padding: 1.25rem;
        }
        
        .modern-card .card-header .bi {
            font-size: var(--icon-lg);
        }
        
        /* Statistiques avec icônes */
        .stat-card {
            border-radius: 16px;
            padding: 1.5rem;
            background: var(--card-bg);
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-blue);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        
        .stat-card .stat-icon {
            font-size: var(--icon-xxl);
            opacity: 0.15;
            position: absolute;
            right: 1rem;
            bottom: 1rem;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.25rem;
        }
        
        .stat-card .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Boutons modernes avec icônes */
        .btn-modern {
            border-radius: 12px;
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-modern .bi {
            font-size: var(--icon-sm);
            margin: 0;
        }
        
        /* Liste moderne avec icônes */
        .modern-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .modern-list-item {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }
        
        .modern-list-item:hover {
            background: var(--light-gray);
            border-color: var(--primary-blue);
            transform: translateX(4px);
        }
        
        .modern-list-item .bi {
            font-size: var(--icon-md);
            margin-right: 0.75rem;
            color: var(--primary-blue);
        }
        
        /* Amélioration des modales */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            border-radius: 16px 16px 0 0;
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
        }
        
        .modal-header .bi {
            font-size: var(--icon-lg);
            margin-right: 0.75rem;
        }
        
        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }
        
        /* Tables modernes */
        .modern-table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .modern-table thead {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #2563a8 100%);
            color: white;
        }
        
        .modern-table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }
        
        .modern-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .modern-table tbody tr:hover {
            background-color: rgba(26, 75, 140, 0.05);
            transform: scale(1.01);
        }
        
        .modern-table td .bi {
            font-size: var(--icon-sm);
        }
        
        /* Breadcrumb avec icônes */
        .breadcrumb .bi {
            font-size: var(--icon-sm);
        }
        
        /* Input groups avec icônes */
        .input-group-text .bi {
            font-size: var(--icon-sm);
        }
        
        /* Améliorations responsives */
        @media (max-width: 768px) {
            :root {
                --icon-xs: 0.75rem;
                --icon-sm: 0.875rem;
                --icon-md: 1rem;
                --icon-lg: 1.25rem;
                --icon-xl: 1.5rem;
                --icon-xxl: 2rem;
            }
            
            .stat-card .stat-value {
                font-size: 1.5rem;
            }
            
            .icon-container.icon-lg {
                width: 48px;
                height: 48px;
            }
        }
    </style>
</head>
<body>
    @include('layouts.sidebar')
    @include('layouts.topbar')
    
    <div class="main-content" id="mainContent">
        <!-- Notifications globales -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <nav aria-label="breadcrumb" class="d-flex align-items-center justify-content-between" style="margin-bottom: 1.5rem;">
            <ol class="breadcrumb mb-0" id="breadcrumb-container" style="flex:1;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" aria-label="Accueil" title="Accueil"><i class="bi bi-house-door-fill"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $header ?? 'Tableau de bord' }}</li>
            </ol>
            <span id="page-title" class="mx-auto" style="font-size:1.2rem;font-weight:500;color:var(--primary-blue);flex:1;text-align:center;font-family:'Poppins',sans-serif;">
                {{ $header ?? 'Tableau de bord' }}
            </span>
        </nav>

        <div class="content-frame">
            {{ $slot }}
        </div>
    </div>

    <button class="theme-switch" id="themeSwitch" aria-label="Basculer entre mode clair et mode sombre" title="Basculer entre mode clair et mode sombre"><i class="bi bi-moon-fill"></i></button>

    <!-- Auto-hide notifications after 5 seconds -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
    
    <!-- Scripts critiques chargés en premier (jQuery doit être synchrone pour DataTables) -->
    <script src="{{ asset('assets/vendor/jquery/jquery-3.7.0.min.js')}}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
    
    <!-- Précharger les polices Google Fonts pour améliorer les performances -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet"></noscript>
    
    <!-- Scripts non-critiques chargés en différé (après jQuery) -->
    <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js')}}" defer></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap5.min.js')}}" defer></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.buttons.min.js')}}" defer></script>
    <script src="{{ asset('assets/vendor/datatables/buttons.bootstrap5.min.js')}}" defer></script>
    <script src="{{ asset('assets/vendor/datatables/buttons.html5.min.js')}}" defer></script>
    <script src="{{ asset('assets/vendor/datatables/buttons.print.min.js')}}" defer></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.responsive.min.js')}}" defer></script>
    <script src="{{ asset('assets/vendor/datatables/responsive.bootstrap5.min.js')}}" defer></script>
    <script src="{{ asset('assets/vendor/jquery/jszip.min.js')}}" defer></script>
    <script src="{{ asset('assets/vendor/jquery/pdfmake.min.js')}}" defer></script>
    <script src="{{ asset('assets/vendor/jquery/vfs_fonts.js')}}" defer></script>
    
    <!-- SweetAlert2 -->
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}" defer></script>
    
    <!-- Notifications System -->
    <script>
        // Système de notifications en temps réel
        class NotificationSystem {
            constructor() {
                this.pollInterval = 30000; // 30 secondes pour rafraîchissement automatique (optimisé pour performance)
                this.basePollInterval = 30000;
                this.maxPollInterval = 120000; // 2 minutes max en cas d'erreur
                this.pollTimer = null;
                this.lastNotificationId = null;
                this.lastDataHash = null; // Hash pour détecter les changements de données
                this.notificationPermission = null;
                this.consecutiveErrors = 0;
                this.maxConsecutiveErrors = 5;
                this.isPolling = false;
                this.init();
            }

            async init() {
                // Demander la permission pour les notifications système
                if ('Notification' in window) {
                    this.notificationPermission = Notification.permission;
                    if (this.notificationPermission === 'default') {
                        // Demander la permission au démarrage
                        Notification.requestPermission().then(permission => {
                            this.notificationPermission = permission;
                        });
                    }
                }

                // Charger les notifications au démarrage
                await this.loadNotifications();
                
                // Démarrer le polling
                this.startPolling();
                
                // Écouter les clics sur les notifications
                this.setupEventListeners();
                
                // Vérifier les opérations en attente toutes les heures (pour la vérification à 6h)
                this.startOperationsCheck();
            }
            
            startOperationsCheck() {
                // Vérifier toutes les heures si on est entre 6h et 7h
                setInterval(async () => {
                    const now = new Date();
                    const hour = now.getHours();
                    
                    // Vérifier entre 6h et 7h pour la notification quotidienne
                    if (hour === 6) {
                        await this.checkPendingOperations();
                    }
                }, 3600000); // Toutes les heures
            }
            
            async checkPendingOperations() {
                try {
                    const response = await fetch('{{ route("api.notifications.pending-operations") }}', {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (!response.ok) return;
                    
                    let text = await response.text();
                    if (text.charCodeAt(0) === 0xFEFF) {
                        text = text.slice(1);
                    }
                    text = text.trim();
                    
                    const data = JSON.parse(text);
                    
                    if (data.has_operations && data.operations.length > 0 && data.is_morning_check) {
                        // Afficher les notifications matinales
                        setTimeout(() => {
                            if (typeof showPendingOperationsNotifications === 'function') {
                                showPendingOperationsNotifications(data.operations, true);
                            }
                        }, 2000);
                    }
                } catch (error) {
                    console.error('Erreur vérification opérations:', error);
                }
            }

            async loadNotifications() {
                try {
                    const response = await fetch('{{ route("api.notifications.index") }}', {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    });
                    
                    // Gérer les erreurs 429
                    if (response.status === 429) {
                        const retryAfter = response.headers.get('Retry-After') || 60;
                        console.warn(`Rate limit atteint. Réessai dans ${retryAfter} secondes`);
                        this.updateNotificationBadge(0);
                        this.renderNotifications([]);
                        return;
                    }
                    
                    if (!response.ok) throw new Error('Erreur lors du chargement');
                    
                    // Get response text and remove BOM if present
                    let text = await response.text();
                    // Remove BOM (Byte Order Mark) if present
                    if (text.charCodeAt(0) === 0xFEFF) {
                        text = text.slice(1);
                    }
                    // Remove any leading whitespace
                    text = text.trim();
                    
                    const data = JSON.parse(text);
                    this.updateNotificationBadge(data.unread_count);
                    this.renderNotifications(data.notifications);
                    
                    // Stocker l'ID de la dernière notification
                    if (data.notifications.length > 0) {
                        this.lastNotificationId = data.notifications[0].id;
                    }
                } catch (error) {
                    console.error('Erreur chargement notifications:', error);
                    this.updateNotificationBadge(0);
                    this.renderNotifications([]);
                }
            }

            renderNotifications(notifications) {
                const container = document.getElementById('notifications-list');
                
                if (notifications.length === 0) {
                    container.innerHTML = '<div class="text-center p-3 text-muted"><small>Aucune notification</small></div>';
                    return;
                }

                container.innerHTML = notifications.map(notif => {
                    const iconClass = this.getIconClass(notif.icon);
                    const timeAgo = this.getTimeAgo(notif.created_at);
                    const readClass = notif.read ? '' : 'notification-unread';
                    const badgeColor = notif.color || '#6c757d';
                    
                    return `
                        <li>
                            <a class="dropdown-item notification-item ${readClass}" 
                               href="${notif.action_url || '{{ route("notifications.index") }}'}" 
                               data-notification-id="${notif.id}">
                                <div class="d-flex align-items-start p-2">
                                    <div class="notification-icon-wrapper me-2" style="background-color: ${badgeColor}20; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi ${iconClass}" style="color: ${badgeColor}; font-size: 1.1rem;"></i>
                                    </div>
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div class="fw-bold small" style="font-size: 0.875rem; line-height: 1.3;">${this.escapeHtml(notif.title)}</div>
                                            ${!notif.read ? '<span class="badge bg-primary rounded-pill" style="font-size: 0.6rem; margin-left: 4px;">Nouveau</span>' : ''}
                                        </div>
                                        <div class="text-muted small mb-1" style="font-size: 0.8rem; line-height: 1.4; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${this.escapeHtml(notif.message)}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            <i class="bi bi-clock me-1"></i>${timeAgo}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    `;
                }).join('');

                // Ajouter les event listeners pour marquer comme lu
                container.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', (e) => {
                        const notificationId = item.dataset.notificationId;
                        if (notificationId) {
                            this.markAsRead(notificationId);
                        }
                    });
                });
            }

            async markAsRead(notificationId) {
                try {
                    const response = await fetch(`{{ route("api.notifications.read", ":id") }}`.replace(':id', notificationId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (response.ok) {
                        // Recharger les notifications
                        await this.loadNotifications();
                    }
                } catch (error) {
                    console.error('Erreur marquage comme lu:', error);
                }
            }

            async markAllAsRead() {
                try {
                    const response = await fetch('{{ route("api.notifications.read-all") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (response.ok) {
                        await this.loadNotifications();
                    }
                } catch (error) {
                    console.error('Erreur marquage tout comme lu:', error);
                }
            }

            updateNotificationBadge(count) {
                const badge = document.getElementById('notification-badge');
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }

            startPolling() {
                this.pollTimer = setInterval(async () => {
                    await this.checkNewNotifications();
                }, this.pollInterval);
            }

            stopPolling() {
                if (this.pollTimer) {
                    clearInterval(this.pollTimer);
                    this.pollTimer = null;
                }
            }

            async checkNewNotifications() {
                try {
                    const response = await fetch('{{ route("api.notifications.index") }}?limit=5', {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    });
                    
                    // Gérer les erreurs 429 (Too Many Requests)
                    if (response.status === 429) {
                        this.consecutiveErrors++;
                        console.warn('Rate limit atteint, augmentation de l\'intervalle de polling');
                        
                        // Backoff exponentiel : doubler l'intervalle jusqu'à max
                        this.pollInterval = Math.min(
                            this.pollInterval * 2,
                            this.maxPollInterval
                        );
                        
                        // Arrêter temporairement si trop d'erreurs
                        if (this.consecutiveErrors >= this.maxConsecutiveErrors) {
                            this.stopPolling();
                            console.warn('Polling arrêté temporairement à cause de trop d\'erreurs 429');
                            
                            // Reprendre après 5 minutes
                            setTimeout(() => {
                                this.consecutiveErrors = 0;
                                this.pollInterval = this.basePollInterval;
                                this.startPolling();
                                console.log('Polling repris');
                            }, 300000); // 5 minutes
                        } else {
                            // Redémarrer avec le nouvel intervalle
                            this.stopPolling();
                            this.startPolling();
                        }
                        return;
                    }
                    
                    if (!response.ok) {
                        this.consecutiveErrors++;
                        if (this.consecutiveErrors >= this.maxConsecutiveErrors) {
                            this.stopPolling();
                        }
                        return;
                    }
                    
                    // Réinitialiser le compteur d'erreurs en cas de succès
                    this.consecutiveErrors = 0;
                    this.pollInterval = this.basePollInterval;
                    
                    // Get response text and remove BOM if present
                    let text = await response.text();
                    // Remove BOM (Byte Order Mark) if present
                    if (text.charCodeAt(0) === 0xFEFF) {
                        text = text.slice(1);
                    }
                    // Remove any leading whitespace
                    text = text.trim();
                    
                    const data = JSON.parse(text);
                    this.updateNotificationBadge(data.unread_count);
                    
                    // Vérifier s'il y a de nouvelles notifications
                    if (data.notifications.length > 0) {
                        const newestId = data.notifications[0].id;
                        if (this.lastNotificationId && newestId > this.lastNotificationId) {
                            // Nouvelles notifications détectées
                            const newNotifications = data.notifications.filter(n => n.id > this.lastNotificationId);
                            this.handleNewNotifications(newNotifications);
                            this.lastNotificationId = newestId;
                        } else if (!this.lastNotificationId) {
                            this.lastNotificationId = newestId;
                        }
                    }
                } catch (error) {
                    console.error('Erreur vérification notifications:', error);
                    this.consecutiveErrors++;
                    if (this.consecutiveErrors >= this.maxConsecutiveErrors) {
                        this.stopPolling();
                    }
                }
            }

            handleNewNotifications(notifications) {
                // Afficher les notifications Windows
                notifications.forEach(notif => {
                    this.showDesktopNotification(notif);
                });
                
                // Afficher des popups SweetAlert2 pour les nouvelles notifications
                notifications.forEach(notif => {
                    this.showNotificationPopup(notif);
                });
                
                // Recharger la liste
                this.loadNotifications();
            }

            showDesktopNotification(notification) {
                if (this.notificationPermission === 'granted') {
                    const notificationObj = new Notification(notification.title, {
                        body: notification.message,
                        icon: '/favicon.ico',
                        badge: '/favicon.ico',
                        tag: `notification-${notification.id}`,
                        requireInteraction: false,
                    });

                    notificationObj.onclick = () => {
                        window.focus();
                        if (notification.action_url) {
                            window.location.href = notification.action_url;
                        }
                        notificationObj.close();
                    };

                    // Fermer automatiquement après 5 secondes
                    setTimeout(() => notificationObj.close(), 5000);
                }
            }
            
            showNotificationPopup(notification) {
                if (typeof Swal === 'undefined') {
                    console.warn('SweetAlert2 non disponible');
                    return;
                }
                
                // Déterminer le type d'icône selon le type de notification
                let icon = 'info';
                let title = 'Nouvelle notification';
                let confirmButtonColor = '#3085d6';
                
                if (notification.type) {
                    switch(notification.type.toLowerCase()) {
                        case 'success':
                        case 'validated':
                            icon = 'success';
                            title = 'Action réussie';
                            confirmButtonColor = '#28a745';
                            break;
                        case 'error':
                        case 'rejected':
                            icon = 'error';
                            title = 'Action requise';
                            confirmButtonColor = '#dc3545';
                            break;
                        case 'warning':
                        case 'pending':
                            icon = 'warning';
                            title = 'Attention requise';
                            confirmButtonColor = '#ffc107';
                            break;
                        case 'info':
                        default:
                            icon = 'info';
                            title = 'Information';
                            confirmButtonColor = '#17a2b8';
                    }
                }
                
                // Détecter les actions à effectuer dans le message
                let actionText = '';
                if (notification.message) {
                    const message = notification.message.toLowerCase();
                    if (message.includes('nouvelle réservation') || message.includes('nouvelle demande')) {
                        actionText = 'Vérifiez les nouvelles réservations en attente de validation.';
                    } else if (message.includes('arrivée') || message.includes('check-in')) {
                        actionText = 'Un client arrive aujourd\'hui. Préparez l\'accueil.';
                    } else if (message.includes('départ') || message.includes('check-out')) {
                        actionText = 'Un client part aujourd\'hui. Préparez le départ.';
                    } else if (message.includes('chambre')) {
                        actionText = 'Vérifiez le statut des chambres.';
                    }
                }
                
                Swal.fire({
                    icon: icon,
                    title: title,
                    html: `
                        <div style="text-align: left;">
                            <p style="margin-bottom: 10px; font-size: 15px;">${this.escapeHtml(notification.message || notification.title)}</p>
                            ${actionText ? `<p style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 14px; color: #495057;"><strong>💡 Action :</strong> ${actionText}</p>` : ''}
                        </div>
                    `,
                    confirmButtonText: 'Voir les détails',
                    confirmButtonColor: confirmButtonColor,
                    showCancelButton: true,
                    cancelButtonText: 'Fermer',
                    cancelButtonColor: '#6c757d',
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                    backdrop: true,
                    focusConfirm: false,
                    focusCancel: true,
                    timer: 10000, // Auto-fermeture après 10 secondes
                    timerProgressBar: true
                }).then((result) => {
                    if (result.isConfirmed && notification.action_url) {
                        window.location.href = notification.action_url;
                    }
                });
            }

            setupEventListeners() {
                // Marquer tout comme lu
                const markAllBtn = document.getElementById('mark-all-read-btn');
                if (markAllBtn) {
                    markAllBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.markAllAsRead();
                    });
                }

                // Arrêter le polling quand la page n'est pas visible
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        this.stopPolling();
                    } else {
                        this.startPolling();
                        this.loadNotifications();
                    }
                });
            }

            getIconClass(icon) {
                const icons = {
                    'success': 'bi-check-circle-fill',
                    'error': 'bi-x-circle-fill',
                    'warning': 'bi-exclamation-triangle-fill',
                    'info': 'bi-info-circle-fill',
                };
                return icons[icon] || 'bi-bell-fill';
            }

            getTimeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000);
                
                if (diff < 60) return 'À l\'instant';
                if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
                if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
                if (diff < 604800) return `Il y a ${Math.floor(diff / 86400)} j`;
                return date.toLocaleDateString('fr-FR');
            }

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        }

        // Initialiser le système de notifications
        let notificationSystem;
        document.addEventListener('DOMContentLoaded', function() {
            notificationSystem = new NotificationSystem();
            
            // Vérifier les opérations en attente au chargement
            checkPendingOperationsOnLoad();
        });
        
        // Système de vérification des opérations en attente
        async function checkPendingOperationsOnLoad() {
            try {
                // Vérifier si les notifications ont déjà été affichées aujourd'hui
                const today = new Date().toDateString();
                const lastNotificationDate = localStorage.getItem('last_notification_date');
                const lastNotificationTime = localStorage.getItem('last_notification_time');
                
                // Ne pas afficher si déjà affichées aujourd'hui (sauf si c'est 6h du matin)
                const now = new Date();
                const currentHour = now.getHours();
                const isMorningCheck = currentHour >= 6 && currentHour < 7;
                
                // Vérifier si c'est la première connexion aujourd'hui ou la vérification matinale
                const isFirstCheckToday = !lastNotificationDate || lastNotificationDate !== today;
                
                // Ne vérifier que si première connexion aujourd'hui OU si c'est 6h du matin
                if (!isFirstCheckToday && !isMorningCheck) {
                    return; // Ne pas afficher les notifications
                }
                
                const response = await fetch('{{ route("api.notifications.pending-operations") }}', {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) return;
                
                let text = await response.text();
                if (text.charCodeAt(0) === 0xFEFF) {
                    text = text.slice(1);
                }
                text = text.trim();
                
                const data = JSON.parse(text);
                
                // Ne vérifier que si c'est la première connexion aujourd'hui OU si c'est 6h du matin
                if (data.has_operations && data.operations.length > 0 && (data.is_first_check_today || data.is_morning_check)) {
                    // Marquer comme affichées
                    localStorage.setItem('last_notification_date', today);
                    localStorage.setItem('last_notification_time', now.getTime().toString());
                    
                    // Attendre un peu pour que la page soit complètement chargée
                    setTimeout(() => {
                        showPendingOperationsNotifications(data.operations, data.is_morning_check);
                    }, 1500);
                }
            } catch (error) {
                console.error('Erreur vérification opérations:', error);
            }
        }
        
        // Afficher les notifications pour les opérations en attente
        function showPendingOperationsNotifications(operations, isMorningCheck) {
            if (typeof Swal === 'undefined') {
                console.warn('SweetAlert2 non disponible');
                return;
            }
            
            // Trier par priorité (critical > high > medium > low)
            const priorityOrder = { 'critical': 4, 'high': 3, 'medium': 2, 'low': 1 };
            operations.sort((a, b) => (priorityOrder[b.priority] || 0) - (priorityOrder[a.priority] || 0));
            
            // Afficher les popups séquentiellement (une à la fois)
            let currentIndex = 0;
            
            function showNextPopup() {
                if (currentIndex >= operations.length) {
                    return; // Toutes les notifications ont été affichées
                }
                
                const operation = operations[currentIndex];
                const isLast = currentIndex === operations.length - 1;
                
                showOperationPopup(operation, isMorningCheck, isLast, () => {
                    // Callback appelé quand le popup est fermé ou que le timer expire
                    currentIndex++;
                    // Afficher le popup suivant après un court délai
                    setTimeout(showNextPopup, 500);
                });
            }
            
            // Démarrer l'affichage séquentiel
            showNextPopup();
        }
        
        // Afficher un popup pour une opération (design moderne)
        function showOperationPopup(operation, isMorningCheck, isLast, onClose) {
            const iconMap = {
                'warning': 'warning',
                'info': 'info',
                'error': 'error',
                'success': 'success'
            };
            
            const icon = iconMap[operation.icon] || 'info';
            const title = isMorningCheck ? '📅 Vérification quotidienne - ' + operation.title : operation.title;
            
            // Son de notification (seulement pour les priorités élevées)
            if (operation.priority === 'critical' || operation.priority === 'high') {
                playNotificationSound(operation.priority);
            }
            
            // Notification système
            showSystemNotification(operation);
            
            // Design moderne avec animation
            Swal.fire({
                icon: icon,
                title: title,
                html: `
                    <div style="text-align: left; padding: 10px 0;">
                        <div style="display: flex; align-items: start; margin-bottom: 15px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: ${getPriorityColor(operation.priority)}; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                                <i class="bi ${getPriorityIcon(operation.priority)}" style="font-size: 24px; color: #fff;"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="margin: 0; font-size: 15px; font-weight: 500; line-height: 1.5; color: #2c3e50;">${escapeHtml(operation.message)}</p>
                            </div>
                        </div>
                        <div style="background: linear-gradient(135deg, ${getPriorityGradientStart(operation.priority)}, ${getPriorityGradientEnd(operation.priority)}); padding: 14px; border-radius: 10px; margin-top: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <p style="margin: 0; font-size: 13px; color: #fff; font-weight: 500; display: flex; align-items: center;">
                                <i class="bi bi-lightbulb me-2" style="font-size: 16px;"></i>
                                <span>${escapeHtml(operation.action_text)}</span>
                            </p>
                        </div>
                        ${operation.count > 1 ? `
                            <div style="margin-top: 12px; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid ${getPriorityBorderColor(operation.priority)};">
                                <p style="margin: 0; font-size: 12px; color: #6c757d; display: flex; align-items: center;">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>${operation.count}</strong> élément(s) à traiter
                                </p>
                            </div>
                        ` : ''}
                    </div>
                `,
                confirmButtonText: '<i class="bi bi-arrow-right me-1"></i>Voir les détails',
                confirmButtonColor: getPriorityButtonColor(operation.priority),
                showCancelButton: true,
                cancelButtonText: 'Fermer',
                cancelButtonColor: '#6c757d',
                allowOutsideClick: true,
                allowEscapeKey: true,
                focusConfirm: false,
                focusCancel: true,
                timer: 15000, // 15 secondes pour chaque popup
                timerProgressBar: true,
                backdrop: true,
                customClass: {
                    popup: 'modern-notification-popup',
                    title: 'modern-notification-title',
                    htmlContainer: 'modern-notification-content',
                    confirmButton: 'modern-notification-button',
                    cancelButton: 'modern-notification-button-cancel'
                },
                didOpen: () => {
                    // Animation d'entrée
                    const popup = document.querySelector('.swal2-popup');
                    if (popup) {
                        popup.style.animation = 'slideInDown 0.3s ease-out';
                    }
                    
                    // Vibrer si supporté (mobile)
                    if (navigator.vibrate) {
                        navigator.vibrate([200, 100, 200]);
                    }
                }
            }).then((result) => {
                // Appeler le callback pour afficher le popup suivant
                if (typeof onClose === 'function') {
                    onClose();
                }
                
                if (result.isConfirmed) {
                    // Rediriger vers la page notifications
                    window.location.href = '{{ route("notifications.index") }}';
                }
            });
        }
        
        // Fonctions utilitaires pour le design moderne
        function getPriorityIcon(priority) {
            const icons = {
                'critical': 'bi-exclamation-triangle-fill',
                'high': 'bi-exclamation-circle-fill',
                'medium': 'bi-info-circle-fill',
                'low': 'bi-bell-fill'
            };
            return icons[priority] || 'bi-info-circle-fill';
        }
        
        function getPriorityGradientStart(priority) {
            const colors = {
                'critical': '#dc3545',
                'high': '#ffc107',
                'medium': '#17a2b8',
                'low': '#6c757d'
            };
            return colors[priority] || '#17a2b8';
        }
        
        function getPriorityGradientEnd(priority) {
            const colors = {
                'critical': '#c82333',
                'high': '#e0a800',
                'medium': '#138496',
                'low': '#5a6268'
            };
            return colors[priority] || '#138496';
        }
        
        // Fonctions utilitaires
        function getPriorityColor(priority) {
            const colors = {
                'critical': 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)',
                'high': 'linear-gradient(135deg, #ffc107 0%, #e0a800 100%)',
                'medium': 'linear-gradient(135deg, #17a2b8 0%, #138496 100%)',
                'low': 'linear-gradient(135deg, #6c757d 0%, #5a6268 100%)'
            };
            return colors[priority] || colors['medium'];
        }
        
        function getPriorityBorderColor(priority) {
            const colors = {
                'critical': '#a71e2a',
                'high': '#d39e00',
                'medium': '#117a8b',
                'low': '#545b62'
            };
            return colors[priority] || colors['medium'];
        }
        
        function getPriorityButtonColor(priority) {
            const colors = {
                'critical': '#dc3545',
                'high': '#ffc107',
                'medium': '#17a2b8',
                'low': '#6c757d'
            };
            return colors[priority] || colors['medium'];
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Jouer un son de notification selon la priorité
        function playNotificationSound(priority) {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                
                // Fréquences différentes selon la priorité
                const frequencies = {
                    'critical': 800, // Son plus aigu pour urgence
                    'high': 600,
                    'medium': 400,
                    'low': 300
                };
                
                const frequency = frequencies[priority] || 400;
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = frequency;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
                
                // Son supplémentaire pour les priorités critiques
                if (priority === 'critical') {
                    setTimeout(() => {
                        const oscillator2 = audioContext.createOscillator();
                        const gainNode2 = audioContext.createGain();
                        oscillator2.connect(gainNode2);
                        gainNode2.connect(audioContext.destination);
                        oscillator2.frequency.value = frequency + 200;
                        oscillator2.type = 'sine';
                        gainNode2.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                        oscillator2.start(audioContext.currentTime);
                        oscillator2.stop(audioContext.currentTime + 0.3);
                    }, 300);
                }
            } catch (error) {
                console.warn('Impossible de jouer le son:', error);
            }
        }
        
        // Afficher une notification système
        function showSystemNotification(operation) {
            if ('Notification' in window && Notification.permission === 'granted') {
                const notification = new Notification(operation.title, {
                    body: operation.message + '\n\n' + operation.action_text,
                    icon: '/favicon.ico',
                    badge: '/favicon.ico',
                    tag: `operation-${operation.type}`,
                    requireInteraction: operation.priority === 'critical',
                    priority: operation.priority === 'critical' ? 'high' : 'default',
                    vibrate: operation.priority === 'critical' ? [200, 100, 200, 100, 200] : [200, 100, 200]
                });
                
                notification.onclick = () => {
                    window.focus();
                    if (operation.url && operation.url !== '#') {
                        window.location.href = operation.url;
                    }
                    notification.close();
                };
                
                // Fermer automatiquement après 10 secondes (sauf si critical)
                if (operation.priority !== 'critical') {
                    setTimeout(() => notification.close(), 10000);
                }
            } else if ('Notification' in window && Notification.permission === 'default') {
                // Demander la permission
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        showSystemNotification(operation);
                    }
                });
            }
        }
    </script>
    
    <!-- SweetAlert pour remplacer les alertes de session -->
    <script>
        // Remplacer les alertes Bootstrap par SweetAlert
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: '{{ session('success') }}',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: '{{ session('error') }}',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true
                });
            @endif

            @if(session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Attention',
                    text: '{{ session('warning') }}',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
            @endif

            @if(session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Information',
                    text: '{{ session('info') }}',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
            @endif
        });
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const themeSwitch = document.getElementById('themeSwitch');
            const body = document.body;

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            // Sidebar Toggle - Gestionnaire simple (sera remplacé par le gestionnaire responsive plus bas)
            // Ne rien faire ici, le gestionnaire responsive gère tout

            // Theme Switch
            if (themeSwitch) {
                themeSwitch.addEventListener('click', () => {
                    if (body.hasAttribute('data-theme')) {
                        body.removeAttribute('data-theme');
                        localStorage.setItem('theme', 'light');
                        themeSwitch.innerHTML = '<i class="bi bi-moon-fill"></i>';
                    } else {
                        body.setAttribute('data-theme', 'dark');
                        localStorage.setItem('theme', 'dark');
                        themeSwitch.innerHTML = '<i class="bi bi-sun-fill"></i>';
                    }
                });
            }

            // Apply saved theme on load
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                body.setAttribute('data-theme', 'dark');
                if (themeSwitch) {
                    themeSwitch.innerHTML = '<i class="bi bi-sun-fill"></i>';
                }
            }
        });
    </script>
    
    <!-- Système de rafraîchissement automatique global -->
    <script>
        class AutoRefreshSystem {
            constructor() {
                this.refreshInterval = 60000; // 60 secondes (optimisé pour performance)
                this.refreshTimer = null;
                this.isRefreshing = false;
                this.lastPageHash = null;
                this.autoRefreshEnabled = false; // Désactivé par défaut pour améliorer les performances
                this.init();
            }
            
            init() {
                // Démarrer le rafraîchissement automatique seulement si activé
                // Désactivé par défaut pour améliorer les performances
                // if (this.autoRefreshEnabled) {
                //     this.startAutoRefresh();
                // }
                
                // Rafraîchir quand la page redevient visible (seulement si activé)
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden && !this.isRefreshing && this.autoRefreshEnabled) {
                        this.refreshPageData();
                    }
                });
            }
            
            startAutoRefresh() {
                this.refreshTimer = setInterval(() => {
                    if (!document.hidden && !this.isRefreshing) {
                        this.refreshPageData();
                    }
                }, this.refreshInterval);
            }
            
            stopAutoRefresh() {
                if (this.refreshTimer) {
                    clearInterval(this.refreshTimer);
                    this.refreshTimer = null;
                }
            }
            
            async refreshPageData() {
                if (this.isRefreshing) return;
                
                this.isRefreshing = true;
                
                try {
                    // Rafraîchir les statistiques si présentes
                    await this.refreshStatistics();
                    
                    // Rafraîchir les tableaux DataTables
                    this.refreshDataTables();
                    
                    // Rafraîchir les listes de réservations
                    await this.refreshReservations();
                    
                    // Rafraîchir les chambres si sur la page des chambres
                    if (window.location.pathname.includes('/rooms')) {
                        await this.refreshRooms();
                    }
                    
                } catch (error) {
                    console.error('Erreur lors du rafraîchissement:', error);
                } finally {
                    this.isRefreshing = false;
                }
            }
            
            async refreshStatistics() {
                // Rafraîchir les statistiques dans les cards
                const statCards = document.querySelectorAll('.stat-card, .card-body.text-center');
                if (statCards.length > 0) {
                    // Les statistiques sont généralement chargées côté serveur
                    // On peut déclencher un événement personnalisé pour que les pages spécifiques se rafraîchissent
                    window.dispatchEvent(new CustomEvent('refreshStatistics'));
                }
            }
            
            refreshDataTables() {
                // Rafraîchir tous les tableaux DataTables qui utilisent AJAX
                if (typeof $.fn.DataTable !== 'undefined') {
                    $('.dataTable').each(function() {
                        if ($.fn.DataTable.isDataTable(this)) {
                            const table = $(this).DataTable();
                            // Vérifier de manière stricte si AJAX est vraiment configuré
                            try {
                                // Vérifier si table.ajax existe ET a une URL valide (pas null, pas undefined, pas vide)
                                const ajaxConfig = table.settings()[0].ajax;
                                if (ajaxConfig && typeof ajaxConfig === 'string' && ajaxConfig.length > 0) {
                                    // Il y a une URL AJAX configurée
                                    if (table.ajax && typeof table.ajax.reload === 'function') {
                                        table.ajax.reload(null, false);
                                    }
                                } else if (ajaxConfig && typeof ajaxConfig === 'object' && ajaxConfig.url && ajaxConfig.url.length > 0) {
                                    // Configuration AJAX avec objet
                                    if (table.ajax && typeof table.ajax.reload === 'function') {
                                        table.ajax.reload(null, false);
                                    }
                                }
                                // Si aucune des conditions n'est remplie, c'est un DataTable client-side, on ne fait rien
                            } catch (e) {
                                // Ignorer silencieusement - probablement un DataTable sans AJAX
                                console.debug('DataTable sans configuration AJAX, ignore le reload');
                            }
                        }
                    });
                }
            }
            
            async refreshReservations() {
                // Rafraîchir les listes de réservations si présentes
                if (window.location.pathname.includes('/reservations')) {
                    window.dispatchEvent(new CustomEvent('refreshReservations'));
                }
            }
            
            async refreshRooms() {
                // Rafraîchir les chambres
                window.dispatchEvent(new CustomEvent('refreshRooms'));
            }
        }
        
        // Initialiser le système de rafraîchissement automatique
        let autoRefreshSystem;
        document.addEventListener('DOMContentLoaded', function() {
            autoRefreshSystem = new AutoRefreshSystem();
        });
    </script>
    
    <!-- Responsive Sidebar Management -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const topNavbar = document.querySelector('.top-navbar');
            const mainContent = document.getElementById('mainContent');
            const body = document.body;
            
            // Fonction pour détecter la taille de l'écran et appliquer le mode approprié
            function handleResponsiveSidebar() {
                const width = window.innerWidth;
                
                // Désactiver le mode collapsed manuel sur petits écrans
                if (width <= 991) {
                    body.classList.remove('sidebar-collapsed');
                }
                
                // Ajuster dynamiquement les tooltips
                const navLinks = document.querySelectorAll('.sidebar .nav-link, .sidebar .accordion-button');
                navLinks.forEach(link => {
                    if (width <= 991) {
                        // Ajouter data-name si absent pour les tooltips
                        if (!link.getAttribute('data-name')) {
                            const textElement = link.querySelector('.nav-text');
                            if (textElement) {
                                link.setAttribute('data-name', textElement.textContent.trim());
                            }
                        }
                    }
                });
            }
            
            // Initialiser au chargement
            handleResponsiveSidebar();
            
            // Améliorer les transitions lors du changement de taille
            const observer = new ResizeObserver(entries => {
                for (let entry of entries) {
                    // Forcer un reflow pour une transition fluide
                    if (sidebar) {
                        sidebar.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                    }
                    if (topNavbar) {
                        topNavbar.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                    }
                    if (mainContent) {
                        mainContent.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                    }
                }
            });
            
            if (sidebar) observer.observe(sidebar);
            if (topNavbar) observer.observe(topNavbar);
            if (mainContent) observer.observe(mainContent);
            
            // Gérer le toggle manuel avec gestion correcte du redimensionnement
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                // Supprimer tous les anciens gestionnaires d'événements
                const newToggle = sidebarToggle.cloneNode(true);
                sidebarToggle.parentNode.replaceChild(newToggle, sidebarToggle);
                
                newToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const currentWidth = window.innerWidth;
                    const sidebarEl = document.getElementById('sidebar');
                    
                    if (currentWidth > 991) {
                        // Sur grand écran : toggle collapsed/expanded
                        body.classList.toggle('sidebar-collapsed');
                        localStorage.setItem('sidebarCollapsed', body.classList.contains('sidebar-collapsed'));
                        // S'assurer que la sidebar est visible
                        if (sidebarEl) {
                            sidebarEl.style.display = '';
                        }
                    } else {
                        // Sur petit écran : toggle affichage/masquage
                        if (sidebarEl) {
                            const isHidden = sidebarEl.style.display === 'none' || 
                                           (sidebarEl.style.display === '' && window.getComputedStyle(sidebarEl).display === 'none');
                            sidebarEl.style.display = isHidden ? 'flex' : 'none';
                            // Sauvegarder l'état pour petits écrans
                            localStorage.setItem('sidebarMobileVisible', !isHidden);
                        }
                    }
                });
            }
            
            // Fonction pour restaurer l'état de la sidebar selon la taille de l'écran
            function restoreSidebarState() {
                const width = window.innerWidth;
                const sidebarEl = document.getElementById('sidebar');
                
                if (width > 991) {
                    // Grand écran : restaurer l'état collapsed
                    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    if (sidebarCollapsed) {
                        body.classList.add('sidebar-collapsed');
                    } else {
                        body.classList.remove('sidebar-collapsed');
                    }
                    // S'assurer que la sidebar est visible sur grand écran
                    if (sidebarEl) {
                        sidebarEl.style.display = '';
                    }
                } else {
                    // Petit écran : restaurer l'état visible/masqué
                    const sidebarMobileVisible = localStorage.getItem('sidebarMobileVisible') !== 'false';
                    if (sidebarEl) {
                        sidebarEl.style.display = sidebarMobileVisible ? 'flex' : 'none';
                    }
                    // Retirer la classe collapsed sur petits écrans
                    body.classList.remove('sidebar-collapsed');
                }
            }
            
            // Restaurer l'état au chargement
            restoreSidebarState();
            
            // Gérer le redimensionnement de la fenêtre avec debounce (une seule déclaration de resizeTimer)
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    handleResponsiveSidebar();
                    restoreSidebarState();
                }, 150);
            });
            
            // Améliorer l'expérience tactile sur mobile
            if ('ontouchstart' in window) {
                const navLinks = document.querySelectorAll('.sidebar .nav-link, .sidebar .accordion-button');
                navLinks.forEach(link => {
                    link.addEventListener('touchstart', function() {
                        this.style.transform = 'scale(0.95)';
                    });
                    link.addEventListener('touchend', function() {
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 150);
                    });
                });
            }
        });
    </script>
    <!-- Service Worker Registration for PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('Service Worker enregistré avec succès:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('Échec de l\'enregistrement du Service Worker:', error);
                    });
            });
        }
    </script>
</body>
</html>