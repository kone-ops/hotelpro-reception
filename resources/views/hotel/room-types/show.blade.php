<x-app-layout>
    <x-slot name="header">{{ $roomType->name }}</x-slot>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-md-8">
            <!-- Informations du type -->
            <div class="modern-card mb-4">
                <div class="card-header">
                    <i class="bi bi-door-open"></i> Informations du Type
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small text-muted fw-bold">Nom du Type</label>
                            <h4 class="mb-0">{{ $roomType->name }}</h4>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted fw-bold">Prix par Nuit</label>
                            <div class="fs-4 text-success fw-bold">
                                {{ number_format($roomType->price, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted fw-bold">Capacité</label>
                            <div class="fs-4 fw-semibold">
                                <i class="bi bi-people icon-md me-2"></i>{{ $roomType->capacity ?? 'N/A' }}
                            </div>
                        </div>
                        @if($roomType->description)
                            <div class="col-12">
                                <label class="small text-muted fw-bold">Description</label>
                                <p class="mb-0">{{ $roomType->description }}</p>
                            </div>
                        @endif
                        <div class="col-12">
                            <label class="small text-muted fw-bold">Statut</label>
                            <div>
                                @if($roomType->is_available)
                                    <span class="badge bg-success fs-6">
                                        <i class="bi bi-check-circle me-1"></i>Disponible pour enregistrement
                                    </span>
                                @else
                                    <span class="badge bg-secondary fs-6">
                                        <i class="bi bi-x-circle me-1"></i>Non disponible
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des chambres de ce type -->
            <div class="modern-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-key"></i> Chambres de ce Type
                    </span>
                    <span class="badge bg-primary">{{ $roomType->rooms->count() }} chambres</span>
                </div>
                <div class="card-body">
                    @if($roomType->rooms->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-striped align-middle mb-0 modern-table app-table" aria-label="Chambres de ce type">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col"><i class="bi bi-hash me-1 text-muted"></i>Numéro</th>
                                        <th scope="col"><i class="bi bi-building me-1 text-muted"></i>Étage</th>
                                        <th scope="col"><i class="bi bi-toggles me-1 text-muted"></i>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roomType->rooms as $room)
                                        <tr>
                                            <td><strong>{{ $room->room_number }}</strong></td>
                                            <td>{{ $room->floor ?? '—' }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($room->status == 'available') bg-success
                                                    @elseif($room->status == 'occupied') bg-danger
                                                    @elseif($room->status == 'reserved') bg-warning
                                                    @else bg-secondary
                                                    @endif">
                                                    @if($room->status == 'available') Disponible
                                                    @elseif($room->status == 'occupied') Occupée
                                                    @elseif($room->status == 'reserved') Réservée
                                                    @else Maintenance
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <x-super.empty-table
                            icon="bi-key"
                            title="Aucune chambre"
                            message="Aucune chambre n'a encore été créée pour ce type."
                        >
                            <x-slot:action>
                                <a href="{{ route('hotel.rooms.create') }}" class="btn btn-primary btn-modern">
                                    <i class="bi bi-plus-circle"></i> Créer une chambre
                                </a>
                            </x-slot:action>
                        </x-super.empty-table>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-md-4">
            <!-- Actions -->
            <div class="modern-card mb-4">
                <div class="card-header">
                    <i class="bi bi-lightning"></i> Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('hotel.room-types.edit', $roomType) }}" class="btn btn-warning btn-modern">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <form action="{{ route('hotel.room-types.toggle', $roomType) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn {{ $roomType->is_available ? 'btn-secondary' : 'btn-success' }} btn-modern w-100">
                                <i class="bi bi-toggle-{{ $roomType->is_available ? 'off' : 'on' }}"></i>
                                {{ $roomType->is_available ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                        @if($roomType->rooms->count() == 0)
                            <form action="{{ route('hotel.room-types.destroy', $roomType) }}" method="POST" onsubmit="return confirm('Supprimer ce type de chambre ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-modern w-100">
                                    <i class="bi bi-trash"></i> Supprimer
                                </button>
                            </form>
                        @endif
                        <hr>
                        <a href="{{ route('hotel.room-types.index') }}" class="btn btn-outline-secondary btn-modern w-100">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="stat-card mb-3">
                <i class="bi bi-key stat-icon icon-primary"></i>
                <div class="stat-value">{{ $roomType->rooms->count() }}</div>
                <div class="stat-label">Chambres Créées</div>
            </div>

            <div class="stat-card mb-3">
                <i class="bi bi-check-circle stat-icon icon-success"></i>
                <div class="stat-value">{{ $roomType->rooms->where('status', 'available')->count() }}</div>
                <div class="stat-label">Disponibles</div>
            </div>

            <div class="stat-card">
                <i class="bi bi-person-fill stat-icon icon-danger"></i>
                <div class="stat-value">{{ $roomType->rooms->where('status', 'occupied')->count() }}</div>
                <div class="stat-label">Occupées</div>
            </div>
        </div>
    </div>
</x-app-layout>

