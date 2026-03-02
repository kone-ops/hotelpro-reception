<x-app-layout>
    <x-slot name="header">Buanderie</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-bucket me-2"></i>Tableau de bord Buanderie</h4>
            <p class="text-muted small mb-0">{{ $hotel->name }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('laundry.collections.index') }}" class="btn btn-primary">
                <i class="bi bi-collection me-1"></i>Collectes de linge
            </a>
            <a href="{{ route('laundry.client-linen.index', ['source' => 'reception']) }}" class="btn btn-outline-primary">
                <i class="bi bi-basket me-1"></i>Linge client (réception / chambre)
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['pending'] }}</h3>
                    <p class="mb-0 text-muted">En attente</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-droplet text-info" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['in_wash'] }}</h3>
                    <p class="mb-0 text-muted">En lavage</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-day text-secondary" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['total_today'] }}</h3>
                    <p class="mb-0 text-muted">Collectes aujourd'hui</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['done_today'] }}</h3>
                    <p class="mb-0 text-muted">Terminées aujourd'hui</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Linge client : accès rapide -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-basket text-primary me-3" style="font-size: 2rem;"></i>
                <div>
                    <h5 class="mb-1">Linge client</h5>
                    <p class="text-muted small mb-0">Linges déposés à la réception ou signalés en chambre par le service des étages. Consulter et mettre à jour les statuts.</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('laundry.client-linen.index', ['source' => 'reception']) }}" class="btn btn-outline-primary">Réception</a>
                <a href="{{ route('laundry.client-linen.index', ['source' => 'room']) }}" class="btn btn-outline-secondary">Chambre</a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Collectes en attente ({{ $pendingCollections->count() }})</h5>
            @if($pendingCollections->count() > 0)
                <a href="{{ route('laundry.collections.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            @endif
        </div>
        <div class="card-body">
            @if($pendingCollections->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Collectes en attente">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><i class="bi bi-door-open me-1 text-muted"></i>Chambre</th>
                                <th scope="col"><i class="bi bi-calendar-event me-1 text-muted"></i>Date / Heure</th>
                                <th scope="col"><i class="bi bi-person me-1 text-muted"></i>Collecté par</th>
                                <th scope="col" class="text-end" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingCollections as $c)
                                <tr>
                                    <td><strong>{{ $c->room->room_number }}</strong> <span class="text-muted">{{ $c->room->roomType->name ?? '-' }}</span></td>
                                    <td>{{ $c->collected_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $c->collectedByUser->name ?? '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('laundry.collections.show', $c) }}" class="btn btn-sm btn-primary">Saisir quantités</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-super.empty-table
                    icon="bi-inbox"
                    title="Aucune collecte en attente"
                    message="Les collectes sont créées automatiquement à chaque fin de nettoyage de chambre (si le module est activé)."
                />
            @endif
        </div>
    </div>
</x-app-layout>
