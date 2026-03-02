<x-app-layout>
    <x-slot name="header">Collectes de linge</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-collection me-2"></i>Collectes de linge</h4>
            <p class="text-muted small mb-0">{{ $hotel->name }}</p>
        </div>
        <a href="{{ route('laundry.dashboard') }}" class="btn btn-outline-secondary">Tableau de bord</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="in_wash" {{ request('status') === 'in_wash' ? 'selected' : '' }}>En lavage</option>
                        <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Terminée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrer</button>
                    <a href="{{ route('laundry.collections.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col"><span class="badge bg-warning">{{ $stats['pending'] }} en attente</span></div>
        <div class="col"><span class="badge bg-info">{{ $stats['in_wash'] }} en lavage</span></div>
        <div class="col"><span class="badge bg-success">{{ $stats['done'] }} terminées</span></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Collectes de linge">
                    <thead class="table-light">
                        <tr>
                            <th scope="col"><i class="bi bi-door-open me-1 text-muted"></i>Chambre</th>
                            <th scope="col"><i class="bi bi-calendar-event me-1 text-muted"></i>Date collecte</th>
                            <th scope="col"><i class="bi bi-person me-1 text-muted"></i>Collecté par</th>
                            <th scope="col"><i class="bi bi-tag me-1 text-muted"></i>Statut</th>
                            <th scope="col" class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collections as $c)
                            <tr>
                                <td><strong>{{ $c->room->room_number }}</strong> <span class="text-muted">{{ $c->room->roomType->name ?? '-' }}</span></td>
                                <td>{{ $c->collected_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $c->collectedByUser->name ?? '-' }}</td>
                                <td>
                                    @if($c->status === 'pending')
                                        <span class="badge bg-warning">En attente</span>
                                    @elseif($c->status === 'in_wash')
                                        <span class="badge bg-info">En lavage</span>
                                    @else
                                        <span class="badge bg-success">Terminée</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('laundry.collections.show', $c) }}" class="btn btn-sm btn-outline-primary">Voir / Saisir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-super.empty-table icon="bi-collection" title="Aucune collecte" message="Aucune collecte ne correspond aux filtres." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($collections->hasPages())
            <div class="card-footer">
                {{ $collections->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
