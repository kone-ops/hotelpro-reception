<div class="top-navbar d-flex justify-content-between align-items-center">
    <!-- Barre de recherche -->
    <form class="d-flex align-items-center topbar-search topbar-search-form">
        <input class="form-control form-control-sm me-2" type="search" disabled placeholder="HotelPro Logiciel de Gestion de l'Hôtellerie" aria-label="Rechercher">
    </form>

    <div class="d-flex align-items-center ms-2 topbar-actions">
        {{-- <!-- Icône Ajouter -->
        <a href="#" class="top-bar-icon me-3" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ajouter">
            <i class="bi bi-plus-lg"></i>
        </a> --}}
        
        <!-- Bouton de rafraîchissement automatique -->
        <div class="position-relative me-3">
            <a href="#" class="top-bar-icon" id="auto-refresh-toggle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Auto-refresh désactivé">
                <i class="bi bi-arrow-clockwise" id="auto-refresh-icon"></i>
            </a>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill auto-refresh-indicator" id="auto-refresh-indicator">
            </span>
        </div>
        
        <!-- Notifications avec badge -->
        <div class="position-relative me-3" id="notifications-container">
            <a href="#" class="top-bar-icon" id="notifications-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notifications">
                <i class="bi bi-bell"></i>
            </a>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill notification-badge bg-danger" id="notification-badge">
                0
            </span>
            <ul class="dropdown-menu dropdown-menu-end notifications-dropdown" id="notifications-dropdown">
                <li>
                    <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <button class="btn btn-sm btn-link p-0 text-decoration-none text-xs" id="mark-all-read-btn">
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
        
        {{-- Sélecteur de langue : l'utilisateur change la langue directement depuis la topbar --}}
        <div class="dropdown me-3">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" id="localeDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('common.language') }}">
                <i class="bi bi-globe2 me-1" aria-hidden="true"></i>
                <span class="d-none d-md-inline">{{ __('common.language') }} :</span>
                <strong class="ms-1">{{ app()->getLocale() === 'fr' ? __('common.french') : __('common.english') }}</strong>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="localeDropdown">
                @foreach(config('app.supported_locales', ['fr', 'en']) as $loc)
                    <li>
                        <a class="dropdown-item {{ app()->getLocale() === $loc ? 'active' : '' }}" href="{{ route('locale.switch', $loc) }}">
                            {{ $loc === 'fr' ? __('common.french') : __('common.english') }}
                            @if(app()->getLocale() === $loc)<i class="bi bi-check-lg ms-2 text-success"></i>@endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Profil utilisateur -->
        <div class="dropdown">
            <div class="d-flex align-items-center user-profile-toggle gap-1" data-bs-toggle="dropdown">
                <div class="rounded-circle bg-secondary user-avatar avatar-md d-flex align-items-center justify-content-center text-white fw-semibold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <span class="user-name fw-normal">{{ auth()->user()->name }}</span>
                <i class="bi bi-chevron-down icon-sm"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">{{ auth()->user()->name }}</h6></li>
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil</a></li>
                <li><a class="dropdown-item" href="{{ route('sessions.index') }}"><i class="bi bi-shield-check me-2"></i>Sessions actives</a></li>
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
/* Responsive Topbar - compact */
@media (max-width: 991px) {
    .topbar-search-form { max-width: 220px; }
}
@media (max-width: 767px) {
    .topbar-search-form { max-width: 160px; }
    .user-name { display: none; }
}
@media (max-width: 575px) {
    .topbar-search-form { max-width: 120px; }
    .user-avatar { width: var(--avatar-sm) !important; height: var(--avatar-sm) !important; font-size: 0.75rem !important; }
    .notifications-dropdown { width: 300px !important; max-height: 360px !important; }
}
</style>
