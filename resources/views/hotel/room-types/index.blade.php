<x-app-layout>
    <x-slot name="header">Gestion des Types de Chambres</x-slot>

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-door-open icon-lg me-2"></i>Types de Chambres</h4>
        <a href="{{ route('hotel.room-types.create') }}" class="btn btn-primary btn-modern">
            <i class="bi bi-plus-circle"></i> Nouveau Type
        </a>
    </div>

    <!-- Statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-door-open stat-icon icon-primary"></i>
                <div class="stat-value">{{ $roomTypes->count() }}</div>
                <div class="stat-label">Types de Chambres</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-check-circle stat-icon icon-success"></i>
                <div class="stat-value">{{ $roomTypes->where('is_available', true)->count() }}</div>
                <div class="stat-label">Disponibles</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-key stat-icon icon-info"></i>
                <div class="stat-value">{{ $roomTypes->sum('rooms_count') }}</div>
                <div class="stat-label">Total Chambres</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-currency-euro stat-icon icon-warning"></i>
                <div class="stat-value">{{ number_format($roomTypes->avg('price'), 0, ',', ' ') }}</div>
                <div class="stat-label">Prix Moyen</div>
            </div>
        </div>
    </div>

    <!-- Liste des types -->
    <div class="modern-card">
        <div class="card-header">
            <i class="bi bi-list-ul"></i> Liste des Types de Chambres
        </div>
        <div class="card-body p-0">
            @if($roomTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped align-middle mb-0 modern-table app-table" aria-label="Liste des types de chambres">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><i class="bi bi-hash me-1 text-muted"></i>ID</th>
                                <th scope="col"><i class="bi bi-door-open me-1 text-muted"></i>Nom</th>
                                <th scope="col" class="d-none d-lg-table-cell"><i class="bi bi-currency-euro me-1 text-muted"></i>Prix/Nuit</th>
                                <th scope="col" class="d-none d-md-table-cell"><i class="bi bi-people me-1 text-muted"></i>Capacité</th>
                                <th scope="col"><i class="bi bi-key me-1 text-muted"></i>Chambres</th>
                                <th scope="col" class="table-cell-state"><i class="bi bi-toggle-on me-1 text-muted"></i>Statut</th>
                                <th scope="col" class="text-end table-actions-cell" style="width: 160px;"><i class="bi bi-gear me-1 text-muted"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roomTypes as $type)
                                <tr>
                                    <td class="fw-bold">#{{ $type->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-container icon-sm bg-primary-soft me-2">
                                                <i class="bi bi-door-closed"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $type->name }}</div>
                                                @if($type->description)
                                                    <small class="text-muted">{{ Str::limit($type->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ number_format($type->price, 0, ',', ' ') }} FCFA</span>
                                    </td>
                                    <td>
                                        @if($type->capacity)
                                            <i class="bi bi-person"></i> {{ $type->capacity }} pers.
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $type->rooms_count }} chambres</span>
                                    </td>
                                    <td class="table-cell-state">
                                        <form action="{{ route('hotel.room-types.toggle', $type) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $type->is_available ? 'btn-success' : 'btn-secondary' }}">
                                                <i class="bi bi-{{ $type->is_available ? 'check-circle' : 'x-circle' }}"></i>
                                                {{ $type->is_available ? 'Disponible' : 'Indisponible' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end table-actions-cell">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('hotel.room-types.show', $type) }}" 
                                               class="btn btn-outline-info" 
                                               title="Voir détails">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('hotel.room-types.edit', $type) }}" 
                                               class="btn btn-outline-warning"
                                               title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if($type->rooms_count == 0)
                                                <form action="{{ route('hotel.room-types.destroy', $type) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce type ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-outline-secondary" 
                                                        disabled 
                                                        title="Des chambres sont associées">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-super.empty-table
                    icon="bi-door-open"
                    title="Aucun type de chambre"
                    message="Créez votre premier type de chambre pour commencer."
                >
                    <x-slot:action>
                        <a href="{{ route('hotel.room-types.create') }}" class="btn btn-primary btn-modern">
                            <i class="bi bi-plus-circle"></i> Créer le premier type
                        </a>
                    </x-slot:action>
                </x-super.empty-table>
            @endif
        </div>
    </div>
</x-app-layout>

