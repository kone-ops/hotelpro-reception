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
                    <table class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> ID</th>
                                <th><i class="bi bi-door-open"></i> Nom</th>
                                <th><i class="bi bi-currency-euro"></i> Prix/Nuit</th>
                                <th><i class="bi bi-people"></i> Capacité</th>
                                <th><i class="bi bi-key"></i> Chambres</th>
                                <th><i class="bi bi-toggle-on"></i> Statut</th>
                                <th class="text-end"><i class="bi bi-gear"></i> Actions</th>
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
                                    <td>
                                        <form action="{{ route('hotel.room-types.toggle', $type) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $type->is_available ? 'btn-success' : 'btn-secondary' }}">
                                                <i class="bi bi-{{ $type->is_available ? 'check-circle' : 'x-circle' }}"></i>
                                                {{ $type->is_available ? 'Disponible' : 'Indisponible' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end">
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
                <div class="text-center py-5">
                    <div class="icon-container icon-xl bg-primary-soft mb-3 mx-auto" style="width: 80px; height: 80px;">
                        <i class="bi bi-door-open icon-xxl"></i>
                    </div>
                    <h5 class="text-muted">Aucun type de chambre</h5>
                    <p class="text-muted">Créez votre premier type de chambre pour commencer.</p>
                    <a href="{{ route('hotel.room-types.create') }}" class="btn btn-primary btn-modern">
                        <i class="bi bi-plus-circle"></i> Créer le Premier Type
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

