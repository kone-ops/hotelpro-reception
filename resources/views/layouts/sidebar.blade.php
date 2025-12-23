<div class="sidebar" id="sidebar">
    <div class="sidebar-header" style="flex-shrink: 0;">
        <div></div>
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Basculer le menu latéral" title="Basculer le menu latéral"><i class="bi bi-list"></i></button>
    </div>

    <div class="sidebar-logo" style="padding: 20px 15px; text-align: center; flex-shrink: 0;">
        <img src="{{ asset('Template/logo.jpg') }}" alt="HotelPro Logo" class="logo-img" style="max-width: 80px; width: 80px; height: 80px; border-radius: 50%; object-fit: cover; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: 3px solid rgba(255,255,255,0.2); transition: all 0.3s ease;" loading="eager" />
        <div class="logo-text" style="margin-top: 10px; font-size: 1rem; font-weight: 600; color: rgba(255,255,255,0.95); letter-spacing: 0.5px;">HotelPro</div>
    </div>

    <div class="sidebar-menu" style="flex: 1; min-height: 0; overflow-y: auto;">
        <ul class="list-unstyled" id="nav-list">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link" data-name="Tableau de bord">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-text">Tableau de bord</span>
                </a>
            </li>
        </ul>
        
        <div class="accordion" id="navAccordion">
            @auth
                @if(auth()->user()->hasRole('super-admin'))
                    <!-- Section Super Admin -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHotels" data-name="Hôtels">
                                <i class="bi bi-building"></i><span class="nav-text">Hôtels</span>
                            </button>
                        </h2>
                        <div id="collapseHotels" class="accordion-collapse collapse" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('super.hotels.index') }}" class="nav-link" data-name="Liste des hôtels" data-parent="Hôtels"><i class="bi bi-building"></i><span class="nav-text">Liste des hôtels</span></a></li>
                                    <li class="nav-item"><a href="{{ route('super.hotel-data.index') }}" class="nav-link" data-name="Données des hôtels" data-parent="Hôtels"><i class="bi bi-database"></i><span class="nav-text">Données des hôtels</span></a></li>
                                    <li class="nav-item"><a href="{{ route('super.database.index') }}" class="nav-link" data-name="Gestion globale BD" data-parent="Hôtels"><i class="bi bi-database-fill"></i><span class="nav-text">Gestion globale BD</span></a></li>
                                    <li class="nav-item"><a href="{{ route('super.reservations.index') }}" class="nav-link" data-name="Réservations" data-parent="Hôtels"><i class="bi bi-calendar-check"></i><span class="nav-text">Réservations</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUsers" data-name="Utilisateurs">
                                <i class="bi bi-people-fill"></i><span class="nav-text">Utilisateurs</span>
                            </button>
                        </h2>
                        <div id="collapseUsers" class="accordion-collapse collapse" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('super.users.index') }}" class="nav-link" data-name="Gestion utilisateurs" data-parent="Utilisateurs"><i class="bi bi-people"></i><span class="nav-text">Gestion utilisateurs</span></a></li>
                                    <li class="nav-item"><a href="{{ route('super.activity') }}" class="nav-link" data-name="Journal d'activité" data-parent="Utilisateurs"><i class="bi bi-journal-text"></i><span class="nav-text">Journal d'activité</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSettings" data-name="Paramètres">
                                <i class="bi bi-sliders"></i><span class="nav-text">Paramètres</span>
                            </button>
                        </h2>
                        <div id="collapseSettings" class="accordion-collapse collapse" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('super.ui-settings.index') }}" class="nav-link" data-name="Personnalisation interface" data-parent="Paramètres"><i class="bi bi-palette"></i><span class="nav-text">Personnalisation interface</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                @elseif(auth()->user()->hasRole('hotel-admin'))
                    <!-- Section Admin Hôtel -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReservations" data-name="Réservations">
                                <i class="bi bi-calendar-check"></i><span class="nav-text">Réservations</span>
                            </button>
                        </h2>
                        <div id="collapseReservations" class="accordion-collapse collapse" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('hotel.dashboard') }}" class="nav-link" data-name="Tableau de bord" data-parent="Réservations"><i class="bi bi-speedometer2"></i><span class="nav-text">Tableau de bord</span></a></li>
                                    <li class="nav-item"><a href="{{ route('hotel.calendar') }}" class="nav-link" data-name="Calendrier" data-parent="Réservations"><i class="bi bi-calendar3"></i><span class="nav-text">Calendrier</span></a></li>
                                    {{-- <li class="nav-item"><a href="{{ route('hotel.reservations.index') }}" class="nav-link" data-name="Toutes les réservations" data-parent="Réservations"><i class="bi bi-list-ul"></i><span class="nav-text">Toutes les réservations</span></a></li> --}}
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRooms" data-name="Chambres">
                                <i class="bi bi-door-open"></i><span class="nav-text">Chambres</span>
                            </button>
                        </h2>
                        <div id="collapseRooms" class="accordion-collapse collapse" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('hotel.room-types.index') }}" class="nav-link" data-name="Types de Chambres" data-parent="Chambres"><i class="bi bi-door-closed"></i><span class="nav-text">Types de Chambres</span></a></li>
                                    <li class="nav-item"><a href="{{ route('hotel.rooms.index') }}" class="nav-link" data-name="Gestion des Chambres" data-parent="Chambres"><i class="bi bi-door-open"></i><span class="nav-text">Toutes les Chambres</span></a></li>
                                    <li class="nav-item"><a href="{{ route('hotel.rooms.bulk-create') }}" class="nav-link" data-name="Création en Lot" data-parent="Chambres"><i class="bi bi-plus-square"></i><span class="nav-text">Création en Lot</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm" data-name="Formulaire">
                                <i class="bi bi-file-earmark-text"></i><span class="nav-text">Formulaire</span>
                            </button>
                        </h2>
                        <div id="collapseForm" class="accordion-collapse collapse" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    {{-- Fonctionnalité de configuration des champs (à implémenter)
                                    <li class="nav-item"><a href="{{ route('hotel.fields.index') }}" class="nav-link" data-name="Champs du formulaire" data-parent="Formulaire"><i class="bi bi-input-cursor-text"></i><span class="nav-text">Champs du formulaire</span></a></li>
                                    --}}
                                    <li class="nav-item"><a href="{{ route('hotel.qr') }}" class="nav-link" data-name="QR Code" data-parent="Formulaire"><i class="bi bi-qr-code"></i><span class="nav-text">QR Code</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReports" data-name="Impressions">
                                <i class="bi bi-printer"></i><span class="nav-text">Impressions</span>
                            </button>
                        </h2>
                        <div id="collapseReports" class="accordion-collapse collapse" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('hotel.reservations.index', ['status' => 'validated']) }}" class="nav-link" data-name="Fiches de police" data-parent="Impressions"><i class="bi bi-printer-fill"></i><span class="nav-text">Fiches de police</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                @elseif(auth()->user()->hasRole('receptionist'))
                    <!-- Section Réceptionniste -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReception" data-name="Réservations">
                                <i class="bi bi-calendar-check"></i><span class="nav-text">Réservations</span>
                            </button>
                        </h2>
                        <div id="collapseReception" class="accordion-collapse collapse" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('reception.dashboard') }}" class="nav-link" data-name="Tableau de bord" data-parent="Réservations"><i class="bi bi-speedometer2"></i><span class="nav-text">Tableau de bord</span></a></li>
                                    <li class="nav-item"><a href="{{ route('reception.reservations.index') }}" class="nav-link" data-name="Toutes les réservations" data-parent="Réservations"><i class="bi bi-list-ul"></i><span class="nav-text">Toutes les réservations</span></a></li>
                                    <li class="nav-item"><a href="{{ route('reception.guests.staying') }}" class="nav-link" data-name="Clients en séjour" data-parent="Réservations"><i class="bi bi-person-check"></i><span class="nav-text">Clients en séjour</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReceptionRooms" data-name="Chambres">
                                <i class="bi bi-door-open"></i><span class="nav-text">Chambres</span>
                            </button>
                        </h2>
                        <div id="collapseReceptionRooms" class="accordion-collapse collapse" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('reception.rooms.index') }}" class="nav-link" data-name="État des chambres" data-parent="Chambres"><i class="bi bi-door-open"></i><span class="nav-text">Toutes les chambres</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer l'URL actuelle
    const currentUrl = window.location.href;
    const currentPath = window.location.pathname;
    
    // Parcourir tous les liens de navigation
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        // Retirer la classe active de tous les liens
        link.classList.remove('active');
        
        const href = link.getAttribute('href');
        
        // Vérifier si le lien correspond à l'URL actuelle
        if (href && (currentUrl === href || currentPath === new URL(href, window.location.origin).pathname)) {
            link.classList.add('active');
            
            // Si c'est dans un accordion, ouvrir l'accordion parent
            const accordionCollapse = link.closest('.accordion-collapse');
            if (accordionCollapse && !accordionCollapse.classList.contains('show')) {
                accordionCollapse.classList.add('show');
                
                // Modifier le bouton de l'accordion
                const accordionButton = accordionCollapse.previousElementSibling?.querySelector('.accordion-button');
                if (accordionButton) {
                    accordionButton.classList.remove('collapsed');
                }
            }
        }
    });
    
    // Gestion du hover sur le logo
    const logoImg = document.querySelector('.sidebar-logo .logo-img');
    if (logoImg) {
        logoImg.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(5deg)';
        });
        logoImg.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    }
});
</script>

<style>
/* Logo responsive */
.sidebar-logo .logo-img {
    transition: all 0.3s ease;
}

.sidebar-collapsed .sidebar-logo {
    padding: 15px 10px !important;
}

.sidebar-collapsed .sidebar-logo .logo-img {
    max-width: 50px !important;
    width: 50px !important;
    height: 50px !important;
}

.sidebar-collapsed .sidebar-logo .logo-text {
    display: none;
}

/* Style pour les liens actifs */
.sidebar .nav-link.active {
    background: rgba(255, 255, 255, 0.15) !important;
    border-left: 4px solid var(--brand-yellow);
    font-weight: 600;
    color: white !important;
}

.sidebar .nav-link.active i {
    color: var(--brand-yellow);
}

/* Animation au hover */
.sidebar .nav-link:hover {
    transform: translateX(5px);
}

/* ============================================
   TOOLTIPS POUR MODE COMPACT (Tablette/Mobile)
   ============================================ */

/* Tooltip au survol pour les écrans compacts */
@media (max-width: 991px) {
    .sidebar .nav-link,
    .sidebar .accordion-button {
        position: relative;
    }
    
    .sidebar .nav-link:hover::before,
    .sidebar .accordion-button:hover::before {
        content: attr(data-name);
        position: absolute;
        left: calc(100% + 15px);
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        white-space: nowrap;
        font-size: 0.85rem;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        pointer-events: none;
        opacity: 0;
        animation: tooltipFadeIn 0.2s ease forwards;
    }
    
    .sidebar .accordion-button:hover::before {
        content: attr(data-name);
    }
    
    @keyframes tooltipFadeIn {
        from {
            opacity: 0;
            transform: translateY(-50%) translateX(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }
    }
    
    /* Tooltip pour les sous-menus */
    .accordion-body .nav-link:hover::before {
        content: attr(data-name);
    }
}

/* Transitions fluides */
.sidebar,
.sidebar .nav-link,
.sidebar .nav-link i,
.sidebar .accordion-button,
.sidebar-logo .logo-img,
.sidebar-logo .logo-text {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Amélioration de l'accessibilité sur mobile */
@media (hover: none) and (pointer: coarse) {
    .sidebar .nav-link:hover {
        transform: none;
    }
    
    .sidebar .nav-link:active {
        background: rgba(255, 255, 255, 0.1) !important;
        transform: scale(0.95);
    }
    
    /* Désactiver les tooltips sur tactile */
    .sidebar .nav-link:hover::before,
    .sidebar .accordion-button:hover::before {
        display: none;
    }
}

/* Mode sombre automatique */
@media (prefers-color-scheme: dark) {
    .sidebar .nav-link:hover::before,
    .sidebar .accordion-button:hover::before {
        background: rgba(255, 255, 255, 0.95);
        color: #000;
    }
}

/* Alignement vertical parfait des icônes */
.sidebar .nav-link,
.sidebar .accordion-button {
    display: flex;
    align-items: center;
}

.sidebar .nav-link i,
.sidebar .accordion-button i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.5rem;
}

/* Optimisation de l'espacement sur petits écrans */
@media (max-width: 768px) {
    .sidebar-header {
        padding: 8px 10px;
    }
    
    .sidebar-toggle {
        font-size: 1.2rem;
        padding: 6px 10px;
    }
}

@media (max-width: 600px) {
    .sidebar-header {
        padding: 6px 5px;
        justify-content: center;
    }
    
    .sidebar-toggle {
        font-size: 1.1rem;
        padding: 5px 8px;
    }
    
    /* Masquer le premier div vide dans le header */
    .sidebar-header > div:first-child {
        display: none;
    }
}

@media (max-width: 480px) {
    .sidebar-header {
        padding: 5px 3px;
    }
    
    .sidebar-toggle {
        font-size: 1rem;
        padding: 4px 6px;
    }
}

/* Amélioration des accordéons sur petits écrans */
@media (max-width: 600px) {
    .accordion-item {
        border: none;
    }
    
    .accordion-header {
        margin-bottom: 2px;
    }
    
    .accordion-collapse {
        border-top: 1px solid rgba(255,255,255,0.1);
    }
}

/* Scroll optimisé pour petits écrans */
@media (max-width: 991px) {
    .sidebar {
        overflow: visible; /* Permettre le scroll sur mobile */
    }
    
    .sidebar-menu {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        -webkit-overflow-scrolling: touch; /* Scroll fluide iOS */
        max-height: calc(100vh - 140px);
        flex: 1;
        min-height: 0; /* Important pour flexbox scroll */
    }
    
    .sidebar-menu::-webkit-scrollbar {
        width: 4px;
        display: block !important;
    }
    
    .sidebar-menu::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.05);
    }
    
    .sidebar-menu::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.3);
        border-radius: 2px;
    }
    
    .sidebar-menu::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.5);
    }
}

@media (max-width: 600px) {
    .sidebar-menu {
        max-height: calc(100vh - 120px);
    }
    
    .sidebar-menu::-webkit-scrollbar {
        width: 3px;
    }
}

@media (max-width: 480px) {
    .sidebar-menu {
        max-height: calc(100vh - 100px);
    }
}

/* Alignement parfait et propreté */
.sidebar .nav-link {
    position: relative;
}

.accordion-button {
    position: relative;
}

/* Supprimer les marges par défaut sur petits écrans */
@media (max-width: 600px) {
    .accordion-item:first-of-type {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }
    
    .accordion-item:last-of-type {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border: none;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: transparent;
        box-shadow: none;
    }
}

/* Optimisation des espacements internes */
@media (max-width: 600px) {
    .sidebar .accordion-item {
        margin-bottom: 1px;
    }
    
    .sidebar .accordion-body ul li .nav-link {
        border-left: 2px solid transparent;
    }
    
    .sidebar .accordion-body ul li .nav-link.active {
        border-left: 2px solid var(--brand-yellow);
    }
}

@media (max-width: 480px) {
    .sidebar .accordion-item {
        margin-bottom: 0;
    }
}

/* Amélioration visuelle du toggle button sur mobile */
@media (max-width: 600px) {
    .sidebar-toggle {
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.1);
    }
    
    .sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.2);
    }
}

/* Optimisation pour très petits écrans */
@media (max-width: 400px) {
    .sidebar {
        width: 50px;
    }
    
    .main-content {
        margin-left: 50px;
    }
    
    .sidebar-logo .logo-img {
        max-width: 32px !important;
        width: 32px !important;
        height: 32px !important;
    }
    
    .sidebar .nav-link i,
    .sidebar .accordion-button i {
        font-size: 0.95rem;
    }
    
    .sidebar .nav-link,
    .sidebar .accordion-button {
        padding: 8px 0;
    }
}

/* Garantir que le contenu ne déborde pas */
.sidebar * {
    box-sizing: border-box;
}

/* Centrage parfait pour mode icône */
@media (max-width: 991px) {
    .sidebar #navAccordion {
        width: 100%;
    }
    
    .sidebar .accordion-item .accordion-button,
    .sidebar .nav-item .nav-link {
        width: 100%;
        margin: 0 auto;
    }
}
</style>
