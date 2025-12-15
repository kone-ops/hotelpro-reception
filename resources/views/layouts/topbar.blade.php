<div class="top-navbar d-flex justify-content-between align-items-center">
    <!-- Barre de recherche -->
    <form class="d-flex align-items-center topbar-search" style="max-width: 380px; flex: 1;">
        <input class="form-control me-2" type="search" disabled placeholder="HotelPro Logiciel de Gestion de l'Hôtellerie" aria-label="Rechercher" style="min-width: 0;">
    </form>
    
    <div class="d-flex align-items-center ms-3 topbar-actions">
        {{-- <!-- Icône Ajouter -->
        <a href="#" class="top-bar-icon me-3" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ajouter">
            <i class="bi bi-plus-lg"></i>
        </a> --}}
        
        <!-- Notifications avec badge -->
        <div class="position-relative me-3" id="notifications-container">
            <a href="#" class="top-bar-icon" id="notifications-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notifications">
                <i class="bi bi-bell"></i>
            </a>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill notification-badge" 
                  id="notification-badge" 
                  style="background: #dc3545; font-size: 0.75rem; display: none;">
                0
            </span>
            <ul class="dropdown-menu dropdown-menu-end" id="notifications-dropdown" style="width: 400px; max-height: 500px; overflow-y: auto;">
                <li>
                    <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <button class="btn btn-sm btn-link p-0 text-decoration-none" id="mark-all-read-btn" style="font-size: 0.75rem;">
                            Tout marquer comme lu
                        </button>
                    </h6>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li id="notifications-list">
                    <div class="text-center p-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-center" href="{{ route('notifications.index') }}" id="view-all-notifications">
                        <small><i class="bi bi-arrow-right me-1"></i>Voir toutes les notifications</small>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Profil utilisateur -->
        <div class="dropdown">
            <div class="d-flex align-items-center user-profile-toggle" style="gap: 0.5rem; cursor: pointer;" data-bs-toggle="dropdown">
                <div class="rounded-circle bg-secondary user-avatar" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:1rem;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <span class="user-name" style="font-weight:250;">{{ auth()->user()->name }}</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">{{ auth()->user()->name }}</h6></li>
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
/* Responsive Topbar */
@media (max-width: 991px) {
    .topbar-search {
        max-width: 250px !important;
    }
    
    .topbar-search input {
        font-size: 0.85rem;
    }
}

@media (max-width: 767px) {
    .topbar-search {
        max-width: 180px !important;
    }
    
    .topbar-search input {
        font-size: 0.8rem;
        padding: 0.3rem 0.5rem;
    }
    
    .user-name {
        display: none;
    }
    
    .topbar-actions {
        gap: 0.5rem;
    }
}

@media (max-width: 575px) {
    .topbar-search {
        max-width: 140px !important;
    }
    
    .topbar-search input {
        font-size: 0.75rem;
        padding: 0.25rem 0.4rem;
    }
    
    .top-bar-icon {
        width: 32px;
        height: 32px;
    }
    
    .user-avatar {
        width: 32px !important;
        height: 32px !important;
        font-size: 0.85rem !important;
    }
    
    .topbar-actions {
        gap: 0.25rem;
    }
    
    #notifications-dropdown {
        width: 320px !important;
        max-height: 400px !important;
    }
}
</style>
