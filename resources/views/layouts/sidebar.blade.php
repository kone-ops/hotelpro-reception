<div class="sidebar" id="sidebar">
    <header class="sidebar-header">
        <div class="sidebar-header-spacer"></div>
        <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Basculer le menu latéral" title="Basculer le menu latéral"><i class="bi bi-list" aria-hidden="true"></i></button>
    </header>

    <div class="sidebar-brand">
        <div class="sidebar-logo">
            <div class="sidebar-logo__wrapper">
                <img src="{{ asset('images/logo.jpg') }}" alt="HotelPro Logo" class="sidebar-logo__img logo-img" width="64" height="64" loading="eager" />
            </div>
            <span class="sidebar-logo__text logo-text">HotelPro</span>
        </div>
    </div>

    <nav class="sidebar-menu" aria-label="Navigation principale">
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
                                    <li class="nav-item"><a href="{{ route('super.notifications.index') }}" class="nav-link" data-name="Notifications client" data-parent="Hôtels"><i class="bi bi-envelope-check"></i><span class="nav-text">Notifications client</span></a></li>
                                    <li class="nav-item"><a href="{{ route('super.modules.index') }}" class="nav-link" data-name="Activation des modules" data-parent="Hôtels"><i class="bi bi-puzzle"></i><span class="nav-text">Activation des modules</span></a></li>
                                    <li class="nav-item"><a href="{{ route('super.hotel-data.index') }}" class="nav-link" data-name="Données des hôtels" data-parent="Hôtels"><i class="bi bi-database"></i><span class="nav-text">Données des hôtels</span></a></li>
                                    <li class="nav-item"><a href="{{ route('super.database.index') }}" class="nav-link" data-name="Gestion globale BD" data-parent="Hôtels"><i class="bi bi-database-fill"></i><span class="nav-text">Gestion globale BD</span></a></li>
                                    <li class="nav-item"><a href="{{ route('super.reservations.index') }}" class="nav-link" data-name="Enregistrements" data-parent="Hôtels"><i class="bi bi-calendar-check"></i><span class="nav-text">Enregistrements</span></a></li>
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
                                    <li class="nav-item"><a href="{{ route('super.optimization.index') }}" class="nav-link" data-name="Optimisation système" data-parent="Paramètres"><i class="bi bi-speedometer2"></i><span class="nav-text">Optimisation système</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                @elseif(auth()->user()->hasRole('hotel-admin'))
                    <!-- Section Admin Hôtel -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReservations" data-name="Enregistrements">
                                <i class="bi bi-calendar-check"></i><span class="nav-text">Enregistrements</span>
                            </button>
                        </h2>
                        <div id="collapseReservations" class="accordion-collapse collapse show" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('hotel.dashboard') }}" class="nav-link" data-name="Tableau de bord" data-parent="Enregistrements"><i class="bi bi-speedometer2"></i><span class="nav-text">Tableau de bord</span></a></li>
                                    <li class="nav-item"><a href="{{ route('hotel.calendar') }}" class="nav-link" data-name="Calendrier" data-parent="Enregistrements"><i class="bi bi-calendar3"></i><span class="nav-text">Calendrier</span></a></li>
                                    <li class="nav-item"><a href="{{ route('reception.client-linen.index') }}" class="nav-link" data-name="Linge client" data-parent="Enregistrements"><i class="bi bi-basket"></i><span class="nav-text">Linge client</span></a></li>
                                    {{-- <li class="nav-item"><a href="{{ route('hotel.reservations.index') }}" class="nav-link" data-name="Tous les enregistrements" data-parent="Enregistrements"><i class="bi bi-list-ul"></i><span class="nav-text">Tous les enregistrements</span></a></li> --}}
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
                                    <li class="nav-item"><a href="{{ route('hotel.areas.index') }}" class="nav-link" data-name="Espaces" data-parent="Chambres"><i class="bi bi-building"></i><span class="nav-text">Espaces</span></a></li>
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
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReception" data-name="Enregistrements">
                                <i class="bi bi-calendar-check"></i><span class="nav-text">Enregistrements</span>
                            </button>
                        </h2>
                        <div id="collapseReception" class="accordion-collapse collapse show" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('reception.dashboard') }}" class="nav-link" data-name="Tableau de bord" data-parent="Enregistrements"><i class="bi bi-speedometer2"></i><span class="nav-text">Tableau de bord</span></a></li>
                                    <li class="nav-item"><a href="{{ route('reception.reservations.index') }}" class="nav-link" data-name="Tous les enregistrements" data-parent="Enregistrements"><i class="bi bi-list-ul"></i><span class="nav-text">Tous les enregistrements</span></a></li>
                                    <li class="nav-item"><a href="{{ route('reception.guests.staying') }}" class="nav-link" data-name="Clients en séjour" data-parent="Enregistrements"><i class="bi bi-person-check"></i><span class="nav-text">Clients en séjour</span></a></li>
                                    <li class="nav-item"><a href="{{ route('reception.client-linen.index') }}" class="nav-link" data-name="Linge client" data-parent="Enregistrements"><i class="bi bi-basket"></i><span class="nav-text">Linge client</span></a></li>
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
                                    <li class="nav-item"><a href="{{ route('reception.areas.index') }}" class="nav-link" data-name="Espaces" data-parent="Chambres"><i class="bi bi-building"></i><span class="nav-text">Espaces</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReceptionImpressions" data-name="Impressions">
                                <i class="bi bi-printer"></i><span class="nav-text">Impressions</span>
                            </button>
                        </h2>
                        <div id="collapseReceptionImpressions" class="accordion-collapse collapse" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('reception.reservations.index', ['status' => 'validated']) }}" class="nav-link" data-name="Fiches de police" data-parent="Impressions"><i class="bi bi-printer-fill"></i><span class="nav-text">Fiches de police</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                @elseif(auth()->user()->hasRole('housekeeping'))
                    <!-- Section Service des étages (Housekeeping) -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHousekeeping" data-name="Service des étages">
                                <i class="bi bi-brush"></i><span class="nav-text">Service des étages</span>
                            </button>
                        </h2>
                        <div id="collapseHousekeeping" class="accordion-collapse collapse show" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('housekeeping.dashboard') }}" class="nav-link" data-name="Tableau de bord" data-parent="Service des étages"><i class="bi bi-speedometer2"></i><span class="nav-text">Tableau de bord</span></a></li>
                                    <li class="nav-item"><a href="{{ route('housekeeping.rooms.index') }}" class="nav-link" data-name="Chambres à nettoyer" data-parent="Service des étages"><i class="bi bi-door-open"></i><span class="nav-text">Chambres à nettoyer</span></a></li>
                                    <li class="nav-item"><a href="{{ route('housekeeping.client-linen.create') }}" class="nav-link" data-name="Linge client (dépôt)" data-parent="Service des étages"><i class="bi bi-basket"></i><span class="nav-text">Linge client (dépôt)</span></a></li>
                                    <li class="nav-item"><a href="{{ route('housekeeping.history.index') }}" class="nav-link" data-name="Mes activités" data-parent="Service des étages"><i class="bi bi-clock-history"></i><span class="nav-text">Mes activités</span></a></li>
                                    <li class="nav-item"><a href="{{ route('housekeeping.areas.index') }}" class="nav-link" data-name="Espaces" data-parent="Service des étages"><i class="bi bi-building"></i><span class="nav-text">Espaces</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @elseif(auth()->user()->hasRole('laundry'))
                    <!-- Section Buanderie (Laundry) -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLaundry" data-name="Buanderie">
                                <i class="bi bi-bucket"></i><span class="nav-text">Buanderie</span>
                            </button>
                        </h2>
                        <div id="collapseLaundry" class="accordion-collapse collapse show" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('laundry.dashboard') }}" class="nav-link" data-name="Tableau de bord" data-parent="Buanderie"><i class="bi bi-speedometer2"></i><span class="nav-text">Tableau de bord</span></a></li>
                                    <li class="nav-item"><a href="{{ route('laundry.collections.index') }}" class="nav-link" data-name="Collectes" data-parent="Buanderie"><i class="bi bi-collection"></i><span class="nav-text">Collectes de linge</span></a></li>
                                    <li class="nav-item"><a href="{{ route('laundry.client-linen.index', ['source' => 'reception']) }}" class="nav-link" data-name="Linge client" data-parent="Buanderie"><i class="bi bi-basket"></i><span class="nav-text">Linge client</span></a></li>
                                    <li class="nav-item"><a href="{{ route('laundry.item-types.index') }}" class="nav-link" data-name="Types de linge" data-parent="Buanderie"><i class="bi bi-tags"></i><span class="nav-text">Types de linge</span></a></li>
                                    <li class="nav-item"><a href="{{ route('laundry.history.index') }}" class="nav-link" data-name="Mes activités" data-parent="Buanderie"><i class="bi bi-clock-history"></i><span class="nav-text">Mes activités</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @elseif(auth()->user()->hasRole('maintenance'))
                    <!-- Section Service technique (Maintenance) -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMaintenance" data-name="Service technique">
                                <i class="bi bi-tools"></i><span class="nav-text">Service technique</span>
                            </button>
                        </h2>
                        <div id="collapseMaintenance" class="accordion-collapse collapse show" data-bs-parent="#navAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled">
                                    <li class="nav-item"><a href="{{ route('maintenance.dashboard') }}" class="nav-link" data-name="Tableau de bord" data-parent="Service technique"><i class="bi bi-speedometer2"></i><span class="nav-text">Tableau de bord</span></a></li>
                                    <li class="nav-item"><a href="{{ route('maintenance.rooms.index') }}" class="nav-link" data-name="Chambres (état technique)" data-parent="Service technique"><i class="bi bi-door-open"></i><span class="nav-text">Chambres (état technique)</span></a></li>
                                    <li class="nav-item"><a href="{{ route('maintenance.areas.index') }}" class="nav-link" data-name="Espaces" data-parent="Service technique"><i class="bi bi-building"></i><span class="nav-text">Espaces</span></a></li>
                                    <li class="nav-item"><a href="{{ route('maintenance.history.index') }}" class="nav-link" data-name="Historique des interventions" data-parent="Service technique"><i class="bi bi-clock-history"></i><span class="nav-text">Historique des interventions</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth
        </div>
    </nav>
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
    
    // Hover logo : légère mise en avant (scale uniquement, pro)
    const logoImg = document.querySelector('.sidebar-logo__img, .sidebar-logo .logo-img');
    if (logoImg) {
        logoImg.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        logoImg.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    }
});
</script>

<style>
/* ============================================
   SIDEBAR — Style moderne et professionnel
   (Couleurs du template inchangées)
   ============================================ */

.sidebar {
    --sidebar-brand-padding: 0.75rem 0.75rem;
    --sidebar-logo-size: 48px;
    --sidebar-logo-radius: 10px;
    --sidebar-item-gap: 0.5rem;
}

.sidebar * {
    box-sizing: border-box;
}

/* --- Header : barre du haut avec toggle --- */
.sidebar-header {
    flex-shrink: 0;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 0.75rem 0 0.875rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}
[data-theme="dark"] .sidebar-header {
    border-bottom-color: var(--border-color);
}
.sidebar-header-spacer {
    width: 1px;
    min-width: 1px;
}
.sidebar-toggle {
    padding: 0.5rem;
    border-radius: 8px;
    transition: background-color 0.2s ease, color 0.2s ease;
}
.sidebar-toggle:hover {
    background-color: rgba(255, 255, 255, 0.12);
}
.sidebar-toggle:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.25);
}

/* --- Brand / Logo : bloc propre et lisible --- */
.sidebar-brand {
    flex-shrink: 0;
}
.sidebar-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: var(--sidebar-brand-padding);
    text-align: center;
}
.sidebar-logo__wrapper {
    width: var(--sidebar-logo-size);
    height: var(--sidebar-logo-size);
    flex-shrink: 0;
    border-radius: var(--sidebar-logo-radius);
    overflow: hidden;
    background: rgba(255, 255, 255, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}
.sidebar-logo__img,
.sidebar-logo .logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.25s ease;
}
.sidebar-logo__text,
.sidebar-logo .logo-text {
    margin-top: 0.375rem;
    font-size: var(--font-size-md, 0.875rem);
    font-weight: 600;
    color: rgba(255, 255, 255, 0.95);
    letter-spacing: 0.02em;
    line-height: 1.3;
}
[data-theme="dark"] .sidebar-logo__text,
[data-theme="dark"] .sidebar-logo .logo-text {
    color: var(--text-dark);
}

/* Menu : zone scrollable (flex pour laisser la place au brand) */
.sidebar-menu {
    flex: 1 1 0;
    min-height: 0;
    height: auto !important;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 0.75rem 0;
    -webkit-overflow-scrolling: touch;
}

/* Liens actifs */
.sidebar .nav-link.active {
    background: rgba(255, 255, 255, 0.15) !important;
    border-left: 3px solid var(--brand-yellow);
    font-weight: 600;
    color: white !important;
}
.sidebar .nav-link.active i {
    color: var(--brand-yellow);
}
[data-theme="dark"] .sidebar .nav-link.active {
    color: var(--primary-blue) !important;
}

/* Hover discret (pro) */
.sidebar .nav-link:hover {
    transform: translateX(4px);
}

/* Transitions */
.sidebar,
.sidebar .nav-link,
.sidebar .nav-link i,
.sidebar .accordion-button,
.sidebar-logo__img,
.sidebar-logo .logo-img,
.sidebar-logo__text,
.sidebar-logo .logo-text {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Alignement icône + texte */
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
    flex-shrink: 0;
}

/* --- Mode replié (collapsed) --- */
.sidebar-collapsed .sidebar-brand,
.sidebar-collapsed .sidebar-logo {
    padding: 0.75rem 0.5rem !important;
}
.sidebar-collapsed .sidebar-logo__wrapper,
.sidebar-collapsed .sidebar-logo .logo-img {
    width: 44px !important;
    height: 44px !important;
    max-width: 44px !important;
}
.sidebar-collapsed .sidebar-logo__text,
.sidebar-collapsed .sidebar-logo .logo-text {
    display: none;
}

/* --- Tooltips en mode compact (tablette / mobile) --- */
@media (max-width: 991px) {
    .sidebar .nav-link,
    .sidebar .accordion-button {
        position: relative;
    }
    .sidebar .nav-link:hover::before,
    .sidebar .accordion-button:hover::before {
        content: attr(data-name);
        position: absolute;
        left: calc(100% + 12px);
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.88);
        color: #fff;
        padding: 6px 10px;
        border-radius: 6px;
        white-space: nowrap;
        font-size: 0.8125rem;
        font-weight: 500;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        pointer-events: none;
        opacity: 0;
        animation: sidebarTooltipIn 0.2s ease forwards;
    }
    .sidebar .accordion-button:hover::before {
        content: attr(data-name);
    }
    .accordion-body .nav-link:hover::before {
        content: attr(data-name);
    }
    @keyframes sidebarTooltipIn {
        from {
            opacity: 0;
            transform: translateY(-50%) translateX(-4px);
        }
        to {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }
    }
}
@media (prefers-color-scheme: dark) {
    .sidebar .nav-link:hover::before,
    .sidebar .accordion-button:hover::before {
        background: rgba(255, 255, 255, 0.95);
        color: #000;
    }
}

/* Touch : pas de translate au hover, feedback au tap */
@media (hover: none) and (pointer: coarse) {
    .sidebar .nav-link:hover {
        transform: none;
    }
    .sidebar .nav-link:active {
        background: rgba(255, 255, 255, 0.12) !important;
        transform: scale(0.98);
    }
    .sidebar .nav-link:hover::before,
    .sidebar .accordion-button:hover::before {
        display: none;
    }
}

/* Scrollbar visible sur mobile/tablette */
@media (max-width: 991px) {
    .sidebar-menu {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        max-height: calc(100vh - 130px);
    }
    .sidebar-menu::-webkit-scrollbar {
        width: 4px;
        display: block !important;
    }
    .sidebar-menu::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.06);
    }
    .sidebar-menu::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.25);
        border-radius: 2px;
    }
    .sidebar-menu::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.4);
    }
    .sidebar #navAccordion {
        width: 100%;
    }
    .sidebar .accordion-item .accordion-button,
    .sidebar .nav-item .nav-link {
        width: 100%;
        margin: 0 auto;
    }
}

@media (max-width: 767px) {
    .sidebar-menu {
        max-height: calc(100vh - 115px);
    }
}
@media (max-width: 575px) {
    .sidebar-menu {
        max-height: calc(100vh - 100px);
    }
    .sidebar-menu::-webkit-scrollbar {
        width: 3px;
    }
}

/* Responsive : header et toggle */
@media (max-width: 768px) {
    .sidebar-header {
        height: 52px;
        padding: 0 0.75rem 0 1rem;
    }
    .sidebar-toggle {
        font-size: 1.25rem;
        padding: 0.4rem 0.5rem;
    }
}
@media (max-width: 600px) {
    .sidebar-header {
        height: 48px;
        padding: 0 0.5rem;
        justify-content: center;
    }
    .sidebar-header-spacer {
        display: none;
    }
    .sidebar-toggle {
        font-size: 1.125rem;
        padding: 0.35rem 0.5rem;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.1);
    }
    .sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.18);
    }
}
@media (max-width: 480px) {
    .sidebar-header {
        padding: 0 0.35rem;
    }
    .sidebar-toggle {
        font-size: 1rem;
        padding: 0.3rem 0.4rem;
    }
}

/* Responsive : logo (tailles déjà dans app.blade.php, on harmonise) */
@media (max-width: 991px) {
    .sidebar-logo {
        padding: 0.75rem 0.5rem !important;
    }
    .sidebar-logo__wrapper {
        width: 48px !important;
        height: 48px !important;
    }
    .sidebar-logo .logo-img {
        max-width: 48px !important;
        width: 48px !important;
        height: 48px !important;
    }
}
@media (max-width: 575px) {
    .sidebar-logo__wrapper,
    .sidebar-logo .logo-img {
        max-width: 40px !important;
        width: 40px !important;
        height: 40px !important;
    }
}
@media (max-width: 400px) {
    .sidebar-logo__wrapper,
    .sidebar-logo .logo-img {
        max-width: 36px !important;
        width: 36px !important;
        height: 36px !important;
    }
    .sidebar .nav-link,
    .sidebar .accordion-button {
        padding: 0.6rem 0.25rem;
    }
}

/* Accordéons : propre sur petits écrans */
@media (max-width: 600px) {
    .sidebar .accordion-item {
        border: none;
        margin-bottom: 1px;
    }
    .accordion-collapse {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    .sidebar .accordion-body ul li .nav-link {
        border-left: 2px solid transparent;
    }
    .sidebar .accordion-body ul li .nav-link.active {
        border-left: 2px solid var(--brand-yellow);
    }
    .accordion-button:focus {
        box-shadow: none;
    }
    .accordion-button:not(.collapsed) {
        background-color: transparent;
        box-shadow: none;
    }
}

.sidebar .nav-link {
    position: relative;
}
.accordion-button {
    position: relative;
}
</style>
