<x-app-layout>
    <x-slot name="header">Gestion des Chambres</x-slot>

    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0"><i class="bi bi-key icon-lg me-2"></i>Chambres de l'Hôtel</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('hotel.rooms.bulk-create') }}" class="btn btn-success btn-modern">
                <i class="bi bi-magic"></i> Création en Lot
            </a>
            <a href="{{ route('hotel.rooms.create') }}" class="btn btn-primary btn-modern">
                <i class="bi bi-plus-circle"></i> Nouvelle Chambre
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="stat-card">
                <i class="bi bi-key stat-icon icon-primary"></i>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <i class="bi bi-check-circle stat-icon icon-success"></i>
                <div class="stat-value">{{ $stats['available'] }}</div>
                <div class="stat-label">Disponibles</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <i class="bi bi-person-fill stat-icon icon-danger"></i>
                <div class="stat-value">{{ $stats['occupied'] }}</div>
                <div class="stat-label">Occupées</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <i class="bi bi-calendar-check stat-icon icon-warning"></i>
                <div class="stat-value">{{ $stats['reserved'] }}</div>
                <div class="stat-label">Réservées</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <i class="bi bi-tools stat-icon icon-secondary"></i>
                <div class="stat-value">{{ $stats['maintenance'] }}</div>
                <div class="stat-label">Maintenance</div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="modern-card mb-4">
        <div class="card-body">
            @if(request()->hasAny(['search', 'room_type_id', 'status']))
                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-filter-circle-fill me-2"></i>
                    <strong>Filtres actifs :</strong>
                    @if(request('search'))
                        <span class="badge bg-primary ms-1">Recherche: {{ request('search') }}</span>
                    @endif
                    @if(request('room_type_id'))
                        <span class="badge bg-primary ms-1">Type: {{ $roomTypes->find(request('room_type_id'))->name ?? 'N/A' }}</span>
                    @endif
                    @if(request('status'))
                        <span class="badge bg-primary ms-1">Statut: 
                            @if(request('status') == 'available') Disponible
                            @elseif(request('status') == 'occupied') Occupée
                            @elseif(request('status') == 'reserved') Réservée
                            @else Maintenance
                            @endif
                        </span>
                    @endif
                </div>
            @endif
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">
                        <i class="bi bi-search icon-sm me-1"></i>Rechercher
                    </label>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Numéro de chambre..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">
                        <i class="bi bi-door-open icon-sm me-1"></i>Type
                    </label>
                    <select name="room_type_id" class="form-select">
                        <option value="">Tous les types</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}" {{ request('room_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">
                        <i class="bi bi-toggles icon-sm me-1"></i>Statut
                    </label>
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponible</option>
                        <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Occupée</option>
                        <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Réservée</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-funnel"></i> Filtrer
                        </button>
                        <a href="{{ route('hotel.rooms.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des chambres -->
    <div class="modern-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul"></i> Liste des Chambres</span>
            <span class="badge bg-primary">{{ $rooms->total() }} chambre(s)</span>
        </div>
        <div class="card-body p-0">
            @if($rooms->count() > 0)
                <div class="table-responsive">
                    <table class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> Numéro</th>
                                <th><i class="bi bi-door-open"></i> Type</th>
                                <th><i class="bi bi-building"></i> Étage</th>
                                <th><i class="bi bi-currency-euro"></i> Prix</th>
                                <th><i class="bi bi-toggles"></i> Statut</th>
                                <th class="text-end"><i class="bi bi-gear"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rooms as $room)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $room->room_number }}</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-container icon-sm bg-info-soft me-2">
                                                <i class="bi bi-door-closed"></i>
                                            </div>
                                            <span>{{ $room->roomType->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($room->floor)
                                            <i class="bi bi-layers"></i> {{ $room->floor }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ number_format($room->roomType->price, 0, ',', ' ') }} FCFA
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm dropdown-toggle
                                                @if($room->status == 'available') btn-success
                                                @elseif($room->status == 'occupied') btn-danger
                                                @elseif($room->status == 'reserved') btn-warning
                                                @else btn-secondary
                                                @endif" 
                                                type="button" 
                                                data-bs-toggle="dropdown">
                                                <i class="bi bi-circle-fill icon-xs me-1"></i>
                                                @if($room->status == 'available') Disponible
                                                @elseif($room->status == 'occupied') Occupée
                                                @elseif($room->status == 'reserved') Réservée
                                                @else Maintenance
                                                @endif
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" 
                                                       href="#" 
                                                       onclick="changeStatus({{ $room->id }}, 'available'); return false;">
                                                        <i class="bi bi-check-circle text-success me-2"></i>Disponible
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" 
                                                       href="#" 
                                                       onclick="changeStatus({{ $room->id }}, 'occupied'); return false;">
                                                        <i class="bi bi-person-fill text-danger me-2"></i>Occupée
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" 
                                                       href="#" 
                                                       onclick="changeStatus({{ $room->id }}, 'reserved'); return false;">
                                                        <i class="bi bi-calendar-check text-warning me-2"></i>Réservée
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" 
                                                       href="#" 
                                                       onclick="changeStatus({{ $room->id }}, 'maintenance'); return false;">
                                                        <i class="bi bi-tools text-secondary me-2"></i>Maintenance
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('hotel.rooms.edit', $room) }}" 
                                               class="btn btn-outline-warning"
                                               title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('hotel.rooms.destroy', $room) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Supprimer la chambre {{ $room->room_number }} ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="p-3">
                    {{ $rooms->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="icon-container icon-xl bg-primary-soft mb-3 mx-auto" style="width: 80px; height: 80px;">
                        <i class="bi bi-key icon-xxl"></i>
                    </div>
                    <h5 class="text-muted">Aucune chambre</h5>
                    <p class="text-muted">Créez vos premières chambres pour commencer.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('hotel.rooms.bulk-create') }}" class="btn btn-success btn-modern">
                            <i class="bi bi-magic"></i> Création Rapide en Lot
                        </a>
                        <a href="{{ route('hotel.rooms.create') }}" class="btn btn-primary btn-modern">
                            <i class="bi bi-plus-circle"></i> Créer une Chambre
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

<script>
// Gérer le z-index des dropdowns dans le tableau
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.modern-table .dropdown');
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('show.bs.dropdown', function() {
            // Quand le dropdown s'ouvre, augmenter le z-index de la ligne parent
            const row = this.closest('tr');
            if (row) {
                row.style.zIndex = '1040';
                row.style.position = 'relative';
            }
        });
        
        dropdown.addEventListener('hide.bs.dropdown', function() {
            // Quand le dropdown se ferme, réinitialiser le z-index
            const row = this.closest('tr');
            if (row) {
                row.style.zIndex = '';
                row.style.position = '';
            }
        });
    });
});

function changeStatus(roomId, newStatus) {
    if (!confirm('Changer le statut de cette chambre ?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('❌ Erreur : Token CSRF introuvable. Rechargez la page.');
        return;
    }
    
    fetch(`/hotel/rooms/${roomId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || `Erreur HTTP ${response.status}`);
            }).catch(() => {
                throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Afficher un message de succès
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            alert('❌ Erreur : ' + (data.message || 'Échec du changement de statut'));
        }
    })
    .catch(error => {
        console.error('Erreur détaillée:', error);
        alert('❌ Erreur lors du changement de statut : ' + error.message);
    });
}
</script>

<style>
/* Fix pour les dropdowns dans les tableaux - Solution optimale */

/* Permettre au menu dropdown de s'afficher au-dessus */
.modern-table .dropdown-menu {
    position: absolute !important;
    z-index: 1050 !important;
}

/* Ligne du tableau avec dropdown ouvert */
.modern-table tbody tr {
    position: relative;
}

/* Solution 1: Garder overflow-x pour mobile mais permettre overflow-y */
@media (min-width: 768px) {
    .table-responsive {
        overflow: visible !important;
    }
}

/* Solution 2: Sur mobile, garder le scroll horizontal mais ajuster */
@media (max-width: 767px) {
    .table-responsive {
        overflow-x: auto;
        overflow-y: visible;
    }
    
    .modern-card .card-body {
        overflow-x: auto;
        overflow-y: visible;
    }
}

/* Cellule contenant le dropdown */
.modern-table td:has(.dropdown) {
    position: relative;
    overflow: visible;
}

/* Container moderne de la card */
.modern-card {
    overflow: visible !important;
}

/* Assurer que le menu est toujours au-dessus */
.dropdown-menu.show {
    z-index: 1050 !important;
}

/* Masquer toutes les icônes et SVG dans la pagination */
.pagination svg,
.pagination .page-link svg,
.pagination .page-item svg,
nav[role="navigation"] svg {
    display: none !important;
}

/* Ajouter du padding aux liens de pagination sans icônes */
.pagination .page-link {
    padding: 0.375rem 0.75rem !important;
}

/* ============================================
   OPTIMISATION TABLEAU - Colonnes et Boutons
   ============================================ */

/* Réduire l'espacement général des colonnes */
.modern-table th,
.modern-table td {
    padding: 0.75rem 0.5rem;
}

/* Colonnes Statut et Actions - Plus compactes */
.modern-table td:nth-last-child(2),
.modern-table td:nth-last-child(1),
.modern-table th:nth-last-child(2),
.modern-table th:nth-last-child(1) {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
    white-space: nowrap;
}

/* Largeurs fixes optimisées */
.modern-table th:nth-last-child(2),
.modern-table td:nth-last-child(2) {
    width: 140px; /* Colonne Statut */
}

.modern-table th:nth-last-child(1),
.modern-table td:nth-last-child(1) {
    width: 100px; /* Colonne Actions */
    text-align: right;
}

/* Harmoniser la taille des boutons d'action */
.modern-table .btn-group-sm .btn,
.modern-table .btn-group-sm button,
.modern-table .btn-group-sm a.btn {
    min-width: 36px !important;
    height: 32px !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    border-radius: 0.25rem;
}

/* Icônes dans les boutons d'action */
.modern-table .btn-group-sm .btn i {
    font-size: 1rem;
    line-height: 1;
    margin: 0;
}

/* Espacement entre les boutons du groupe */
.modern-table .btn-group-sm {
    gap: 4px;
    display: inline-flex;
}

/* Arrondir tous les boutons (pas de collage) */
.modern-table .btn-group-sm .btn:first-child,
.modern-table .btn-group-sm .btn:last-child,
.modern-table .btn-group-sm button {
    border-radius: 0.25rem !important;
}
</style>

