<x-app-layout>
    <x-slot name="header">Tableau des Chambres</x-slot>

    <div class="mb-4">
        <h4 class="mb-0"><i class="bi bi-grid-3x3-gap icon-lg me-2"></i>État des Chambres en Temps Réel</h4>
        <p class="text-muted">Cliquez sur une chambre pour changer rapidement son statut</p>
    </div>

    <!-- Statistiques en temps réel -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="stat-card">
                <i class="bi bi-key stat-icon icon-primary"></i>
                <div class="stat-value" id="stat-total">{{ $stats['total'] }}</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <i class="bi bi-check-circle stat-icon icon-success"></i>
                <div class="stat-value" id="stat-available">{{ $stats['available'] }}</div>
                <div class="stat-label">Disponibles</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <i class="bi bi-person-fill stat-icon icon-danger"></i>
                <div class="stat-value" id="stat-occupied">{{ $stats['occupied'] }}</div>
                <div class="stat-label">Occupées</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <i class="bi bi-calendar-check stat-icon icon-warning"></i>
                <div class="stat-value" id="stat-reserved">{{ $stats['reserved'] }}</div>
                <div class="stat-label">Réservées</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <i class="bi bi-tools stat-icon icon-secondary"></i>
                <div class="stat-value" id="stat-maintenance">{{ $stats['maintenance'] }}</div>
                <div class="stat-label">Maintenance</div>
            </div>
        </div>
    </div>

    <!-- Filtres Rapides -->
    <div class="modern-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end" id="filterForm">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">
                        <i class="bi bi-door-open icon-sm me-1"></i>Type de Chambre
                    </label>
                    <select name="room_type_id" class="form-select" onchange="this.form.submit()">
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
                        <i class="bi bi-building icon-sm me-1"></i>Étage
                    </label>
                    <select name="floor" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les étages</option>
                        @foreach($floors as $floor)
                            <option value="{{ $floor }}" {{ request('floor') == $floor ? 'selected' : '' }}>
                                {{ $floor }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">
                        <i class="bi bi-toggles icon-sm me-1"></i>Statut
                    </label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponible</option>
                        <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Occupée</option>
                        <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Réservée</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('reception.rooms.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Grille des Chambres - Vue Rapide -->
    <div class="modern-card">
        <div class="card-header">
            <i class="bi bi-grid-3x3-gap"></i> Vue en Grille
            <span class="badge bg-primary ms-2">{{ $rooms->count() }} chambres</span>
        </div>
        <div class="card-body">
            @if($rooms->count() > 0)
                <div class="row g-3">
                    @foreach($rooms as $room)
                        <div class="col-md-2 col-sm-3 col-6">
                            <div class="room-card 
                                @if($room->status == 'available') room-available
                                @elseif($room->status == 'occupied') room-occupied
                                @elseif($room->status == 'reserved') room-reserved
                                @else room-maintenance
                                @endif"
                                onclick="openStatusMenu({{ $room->id }}, '{{ $room->room_number }}', '{{ $room->status }}')"
                                data-room-id="{{ $room->id }}"
                                data-status="{{ $room->status }}">
                                <div class="room-number">{{ $room->room_number }}</div>
                                <div class="room-type">{{ $room->roomType->name }}</div>
                                @if($room->floor)
                                    <div class="room-floor">
                                        <i class="bi bi-layers icon-xs"></i> {{ $room->floor }}
                                    </div>
                                @endif
                                <div class="room-status-badge">
                                    @if($room->status == 'available')
                                        <i class="bi bi-check-circle"></i> Libre
                                    @elseif($room->status == 'occupied')
                                        <i class="bi bi-person-fill"></i> Occupée
                                    @elseif($room->status == 'reserved')
                                        <i class="bi bi-calendar-check"></i> Réservée
                                    @else
                                        <i class="bi bi-tools"></i> Maintenance
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox icon-xxl text-muted"></i>
                    <p class="text-muted mt-3">Aucune chambre à afficher avec les filtres actuels</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Légende -->
    <div class="mt-4">
        <div class="d-flex gap-3 flex-wrap justify-content-center">
            <div class="legend-item">
                <span class="legend-color bg-success"></span> Disponible
            </div>
            <div class="legend-item">
                <span class="legend-color bg-danger"></span> Occupée
            </div>
            <div class="legend-item">
                <span class="legend-color bg-warning"></span> Réservée
            </div>
            <div class="legend-item">
                <span class="legend-color bg-secondary"></span> Maintenance
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Modal de Changement de Statut -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-toggles icon-lg me-2"></i>
                    Chambre <span id="modalRoomNumber"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Sélectionnez le nouveau statut :</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" onclick="changeRoomStatus('available')">
                        <i class="bi bi-check-circle icon-md me-2"></i>
                        <strong>Disponible</strong> - Chambre libre
                    </button>
                    <button class="btn btn-danger btn-lg" onclick="changeRoomStatus('occupied')">
                        <i class="bi bi-person-fill icon-md me-2"></i>
                        <strong>Occupée</strong> - Client présent
                    </button>
                    <button class="btn btn-warning btn-lg" onclick="changeRoomStatus('reserved')">
                        <i class="bi bi-calendar-check icon-md me-2"></i>
                        <strong>Réservée</strong> - En attente d'arrivée
                    </button>
                    <button class="btn btn-secondary btn-lg" onclick="changeRoomStatus('maintenance')">
                        <i class="bi bi-tools icon-md me-2"></i>
                        <strong>Maintenance</strong> - Hors service
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.room-card {
    background: var(--card-bg, #ffffff);
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid;
    min-height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
}

.room-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.room-available {
    border-color: #198754;
    background: linear-gradient(135deg, var(--card-bg, #ffffff) 0%, #d1f2e4 100%);
}

.room-occupied {
    border-color: #dc3545;
    background: linear-gradient(135deg, var(--card-bg, #ffffff) 0%, #f8d7da 100%);
}

.room-reserved {
    border-color: #ffc107;
    background: linear-gradient(135deg, var(--card-bg, #ffffff) 0%, #fff3cd 100%);
}

.room-maintenance {
    border-color: #6c757d;
    background: linear-gradient(135deg, var(--card-bg, #ffffff) 0%, #e9ecef 100%);
}

.room-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary, #2c3e50);
    margin-bottom: 0.25rem;
}

.room-type {
    font-size: 0.75rem;
    color: var(--text-secondary, #6c757d);
    margin-bottom: 0.25rem;
}

.room-floor {
    font-size: 0.7rem;
    color: var(--text-secondary, #9ca3af);
}

.room-status-badge {
    font-size: 0.7rem;
    font-weight: 600;
    margin-top: 0.5rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    background: rgba(52, 120, 165, 0.8);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    display: inline-block;
}

/* Animation pour le changement de statut */
@keyframes statusChange {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.room-card.changing {
    animation: statusChange 0.5s ease-in-out;
}
</style>

<script>
let currentRoomId = null;
let currentRoomStatus = null;
let statusModal = null;

document.addEventListener('DOMContentLoaded', function() {
    statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
});

function openStatusMenu(roomId, roomNumber, currentStatus) {
    currentRoomId = roomId;
    currentRoomStatus = currentStatus;
    document.getElementById('modalRoomNumber').textContent = roomNumber;
    statusModal.show();
}

function changeRoomStatus(newStatus) {
    if (newStatus === currentRoomStatus) {
        statusModal.hide();
        return;
    }
    
    const roomCard = document.querySelector(`[data-room-id="${currentRoomId}"]`);
    roomCard.classList.add('changing');
    
    fetch(`/reception/rooms/${currentRoomId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusModal.hide();
            
            // Afficher notification de succès
            showToast(data.message, 'success');
            
            // Recharger la page pour actualiser les stats et l'affichage
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
    })
    .catch(error => {
        roomCard.classList.remove('changing');
        showToast('Erreur lors du changement de statut', 'danger');
        console.error('Error:', error);
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Raccourcis clavier
document.addEventListener('keydown', function(e) {
    if (!statusModal._isShown) return;
    
    if (e.key === 'd' || e.key === 'D') {
        changeRoomStatus('available');
    } else if (e.key === 'o' || e.key === 'O') {
        changeRoomStatus('occupied');
    } else if (e.key === 'r' || e.key === 'R') {
        changeRoomStatus('reserved');
    } else if (e.key === 'm' || e.key === 'M') {
        changeRoomStatus('maintenance');
    }
});
</script>

