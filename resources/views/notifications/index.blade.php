<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <i class="bi bi-bell me-2"></i>Notifications
            </h2>
            @if($stats['unread'] > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                    @csrf
                    @php
                        $canMarkAllRead = !isset($userFilter) || $userFilter == Auth::id() || !$userFilter;
                    @endphp
                    @if(!$canMarkAllRead)
                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Vous ne pouvez marquer comme lues que vos propres notifications">
                            <i class="bi bi-lock me-1"></i>Lecture seule
                        </button>
                    @else
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-check-all me-1"></i>Tout marquer comme lu
                    </button>
                    @endif
                </form>
            @endif
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-3 mb-4">
            <!-- Filtres -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Filtres</h6>
                </div>
                <div class="card-body">
                    <!-- Filtre par utilisateur (seulement pour les admins) -->
                    @if(isset($receptionists) && $receptionists->count() > 0)
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Filtrer par utilisateur</label>
                            <select class="form-select form-select-sm" id="user-filter" onchange="window.location.href='{{ route('notifications.index') }}?filter={{ $filter }}&user_filter=' + this.value">
                                <option value="">Tous les utilisateurs</option>
                                <option value="{{ Auth::id() }}" {{ $userFilter == Auth::id() ? 'selected' : '' }}>Mes notifications</option>
                                @foreach($receptionists as $receptionist)
                                    <option value="{{ $receptionist->id }}" {{ $userFilter == $receptionist->id ? 'selected' : '' }}>
                                        {{ $receptionist->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <hr>
                    @endif
                    
                    <div class="list-group list-group-flush">
                        <a href="{{ route('notifications.index', array_merge(['filter' => 'all'], $userFilter ? ['user_filter' => $userFilter] : [])) }}" 
                           class="list-group-item list-group-item-action {{ $filter === 'all' ? 'active' : '' }}">
                            <i class="bi bi-list-ul me-2"></i>Toutes
                            <span class="badge bg-secondary float-end">{{ $stats['total'] }}</span>
                        </a>
                        <a href="{{ route('notifications.index', array_merge(['filter' => 'unread'], $userFilter ? ['user_filter' => $userFilter] : [])) }}" 
                           class="list-group-item list-group-item-action {{ $filter === 'unread' ? 'active' : '' }}">
                            <i class="bi bi-envelope me-2"></i>Non lues
                            <span class="badge bg-primary float-end">{{ $stats['unread'] }}</span>
                        </a>
                        <a href="{{ route('notifications.index', array_merge(['filter' => 'read'], $userFilter ? ['user_filter' => $userFilter] : [])) }}" 
                           class="list-group-item list-group-item-action {{ $filter === 'read' ? 'active' : '' }}">
                            <i class="bi bi-envelope-open me-2"></i>Lues
                            <span class="badge bg-success float-end">{{ $stats['read'] }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">Statistiques</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total</span>
                        <strong>{{ $stats['total'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Non lues</span>
                        <strong class="text-primary">{{ $stats['unread'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Lues</span>
                        <strong class="text-success">{{ $stats['read'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Liste des notifications -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="notifications-list" id="notifications-list">
                            @foreach($notifications as $notification)
                                @php
                                    $isOwnNotification = $notification->user_id == Auth::id();
                                    $ownerName = $notification->user->name ?? 'Utilisateur';
                                @endphp
                                <div class="notification-item {{ !$notification->read ? 'unread' : '' }} {{ !$isOwnNotification ? 'notification-external' : '' }}" 
                                     data-notification-id="{{ $notification->id }}"
                                     data-user-id="{{ $notification->user_id }}">
                                    <a href="{{ $notification->action_url ?: '#' }}" 
                                       class="notification-link"
                                       @if($notification->action_url && $isOwnNotification)
                                           onclick="markAsRead({{ $notification->id }}, this, {{ $isOwnNotification ? 'true' : 'false' }})"
                                       @elseif($notification->action_url && !$isOwnNotification)
                                           onclick="event.preventDefault(); logNotificationView({{ $notification->id }}, {{ $notification->user_id }});"
                                       @else
                                           onclick="event.preventDefault(); {{ $isOwnNotification ? 'markAsRead(' . $notification->id . ', this, true)' : 'logNotificationView(' . $notification->id . ', ' . $notification->user_id . ')' }};"
                                       @endif>
                                        <div class="d-flex align-items-start p-3">
                                            <div class="notification-icon me-3">
                                                <div class="icon-wrapper" style="background-color: {{ $notification->color ?? '#6c757d' }}20;">
                                                    @php
                                                        $iconClass = match($notification->icon) {
                                                            'info' => 'bi-info-circle',
                                                            'success' => 'bi-check-circle',
                                                            'warning' => 'bi-exclamation-triangle',
                                                            'error' => 'bi-x-circle',
                                                            default => 'bi-bell',
                                                        };
                                                    @endphp
                                                    <i class="bi {{ $iconClass }}" 
                                                       style="color: {{ $notification->color ?? '#6c757d' }};"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <h6 class="mb-0 notification-title">{{ $notification->title }}</h6>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        @if(!$isOwnNotification)
                                                            <span class="badge bg-info text-dark rounded-pill" style="font-size: 0.65rem;" title="Notification de {{ $ownerName }}">
                                                                <i class="bi bi-person me-1"></i>{{ $ownerName }}
                                                            </span>
                                                        @endif
                                                        @if(!$notification->read && $isOwnNotification)
                                                        <span class="badge bg-primary rounded-pill" style="font-size: 0.65rem;">Nouveau</span>
                                                        @elseif(!$notification->read && !$isOwnNotification)
                                                            <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.65rem;" title="Lecture seule">Non lue</span>
                                                        @endif
                                                        @if(!$isOwnNotification)
                                                            <span class="badge bg-secondary rounded-pill" style="font-size: 0.6rem;" title="Vous ne pouvez pas modifier cette notification">
                                                                <i class="bi bi-eye"></i> Lecture seule
                                                            </span>
                                                    @endif
                                                    </div>
                                                </div>
                                                <p class="notification-message mb-1">{{ $notification->message }}</p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                                        @if(!$isOwnNotification)
                                                            <span class="ms-2">
                                                                <i class="bi bi-person me-1"></i>Notification de {{ $ownerName }}
                                                            </span>
                                                        @endif
                                                    </small>
                                                    @if($notification->action_url)
                                                        <span class="text-primary small">
                                                            <i class="bi bi-arrow-right me-1"></i>{{ $notification->action_text ?? 'Voir' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @if(!$loop->last)
                                    <hr class="my-0">
                                @endif
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="p-3 border-top">
                            {{ $notifications->links() }}
                        </div>

                        <!-- Bouton Voir plus (chargement AJAX) -->
                        @if($notifications->hasMorePages())
                            <div class="text-center p-3 border-top">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="load-more-btn" 
                                        data-page="{{ $notifications->currentPage() + 1 }}" 
                                        data-filter="{{ $filter }}"
                                        data-user-filter="{{ $userFilter ?? '' }}">
                                    <i class="bi bi-arrow-down me-1"></i>Voir plus
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">Aucune notification</h5>
                            <p class="text-muted">
                                @if($filter === 'unread')
                                    Vous n'avez aucune notification non lue.
                                @elseif($filter === 'read')
                                    Vous n'avez aucune notification lue.
                                @else
                                    Vous n'avez aucune notification pour le moment.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
.notification-item {
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e7f3ff;
    border-left: 3px solid #0d6efd;
}

.notification-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.notification-link:hover {
    color: inherit;
}

.notification-icon .icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.notification-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #212529;
}

.notification-message {
    font-size: 0.875rem;
    color: #6c757d;
    line-height: 1.5;
}

.notification-item.unread .notification-title {
    color: #0d6efd;
    font-weight: 700;
}

/* Style pour les notifications d'autres utilisateurs (lecture seule) */
.notification-item.notification-external {
    background-color: #f8f9fa;
    border-left: 3px solid #6c757d;
    opacity: 0.95;
}

.notification-item.notification-external:hover {
    background-color: #e9ecef;
}

.notification-item.notification-external .notification-link {
    cursor: default;
}
</style>

<script>
// Logger la consultation d'une notification d'un autre utilisateur (lecture seule)
function logNotificationView(notificationId, viewedUserId) {
    // Le log d'audit sera créé côté serveur lors de la consultation
    // Cette fonction est principalement pour éviter que le clic ne marque la notification comme lue
    console.debug(`Consultation de notification ${notificationId} de l'utilisateur ${viewedUserId} (lecture seule)`);
}

function markAsRead(notificationId, element, isOwnNotification = true) {
    // Vérifier que c'est bien la notification de l'utilisateur
    if (!isOwnNotification) {
        console.warn('Tentative de marquer comme lue une notification qui ne vous appartient pas');
        return;
    }
    
    fetch(`/api/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur lors du marquage comme lu');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Retirer la classe unread
            const item = element.closest('.notification-item');
            if (item) {
                item.classList.remove('unread');
                // Retirer le badge "Nouveau"
                const badge = item.querySelector('.badge.bg-primary');
                if (badge) {
                    badge.remove();
                }
            }
            // Mettre à jour le compteur dans la cloche
            updateNotificationBadge();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

function updateNotificationBadge() {
    fetch('/api/notifications/unread-count', {
        headers: {
            'Accept': 'application/json',
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
    });
}

// Charger plus de notifications
document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const page = this.dataset.page;
            const filter = this.dataset.filter;
            const btn = this;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Chargement...';
            
            fetch(`/notifications/load-more?page=${page}&filter=${filter}`, {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.notifications && data.notifications.length > 0) {
                    const list = document.getElementById('notifications-list');
                    const hr = document.createElement('hr');
                    hr.className = 'my-0';
                    list.appendChild(hr);
                    
                    data.notifications.forEach(notif => {
                        const item = createNotificationItem(notif);
                        list.appendChild(item);
                        const hr2 = document.createElement('hr');
                        hr2.className = 'my-0';
                        list.appendChild(hr2);
                    });
                    
                    if (!data.has_more) {
                        btn.remove();
                    } else {
                        btn.dataset.page = parseInt(page) + 1;
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-arrow-down me-1"></i>Voir plus';
                    }
                } else {
                    btn.remove();
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-down me-1"></i>Voir plus';
            });
        });
    }
});

function createNotificationItem(notification) {
    const div = document.createElement('div');
    div.className = `notification-item ${!notification.read ? 'unread' : ''}`;
    div.setAttribute('data-notification-id', notification.id);
    
    const unreadBadge = !notification.read 
        ? '<span class="badge bg-primary rounded-pill" style="font-size: 0.65rem;">Nouveau</span>' 
        : '';
    
    const actionLink = notification.action_url || '#';
    const actionText = notification.action_text ? 
        `<span class="text-primary small"><i class="bi bi-arrow-right me-1"></i>${notification.action_text}</span>` : '';
    
    div.innerHTML = `
        <a href="${actionLink}" 
           class="notification-link"
           ${notification.action_url ? '' : 'onclick="event.preventDefault(); markAsRead(' + notification.id + ', this);"'}>
            <div class="d-flex align-items-start p-3">
                <div class="notification-icon me-3">
                    <div class="icon-wrapper" style="background-color: ${notification.color || '#6c757d'}20;">
                        <i class="bi bi-bell" style="color: ${notification.color || '#6c757d'};"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="mb-0 notification-title">${escapeHtml(notification.title)}</h6>
                        ${unreadBadge}
                    </div>
                    <p class="notification-message mb-1">${escapeHtml(notification.message)}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>${formatTimeAgo(notification.created_at)}
                        </small>
                        ${actionText}
                    </div>
                </div>
            </div>
        </a>
    `;
    
    if (notification.action_url) {
        div.querySelector('.notification-link').addEventListener('click', function(e) {
            markAsRead(notification.id, this);
        });
    }
    
    return div;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'À l\'instant';
    if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
    if (diff < 604800) return `Il y a ${Math.floor(diff / 86400)} j`;
    return date.toLocaleDateString('fr-FR');
}

// Fonction helper pour obtenir l'icône
function getIconClass(icon) {
    const icons = {
        'info': 'bi-info-circle',
        'success': 'bi-check-circle',
        'warning': 'bi-exclamation-triangle',
        'error': 'bi-x-circle',
        'bell': 'bi-bell',
    };
    return icons[icon] || 'bi-bell';
}
</script>

