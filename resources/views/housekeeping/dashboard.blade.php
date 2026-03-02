<x-app-layout>
    <x-slot name="header">Service des étages</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-brush me-2"></i>Tableau de bord Housekeeping</h4>
            <p class="text-muted small mb-0">{{ $hotel->name }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('housekeeping.rooms.index') }}" class="btn btn-primary">
                <i class="bi bi-door-open me-1"></i>Chambres à nettoyer
            </a>
            <a href="{{ route('housekeeping.client-linen.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-basket me-1"></i>Linge client (dépôt)
            </a>
        </div>
    </div>

    <!-- Action rapide : Linge client trouvé en chambre -->
    <div class="card border-0 shadow-sm mb-4 border-primary border-2">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-basket text-primary me-3" style="font-size: 2rem;"></i>
                <div>
                    <h5 class="mb-1">Linge client – Dépôt (trouvé en chambre)</h5>
                    <p class="text-muted small mb-0">Enregistrer du linge client trouvé en chambre (numéro de chambre, nom client, description). La buanderie sera notifiée.</p>
                </div>
            </div>
            <a href="{{ route('housekeeping.client-linen.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Enregistrer un dépôt de linge client
            </a>
        </div>
    </div>

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
                    <i class="bi bi-brush text-info" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['in_progress'] }}</h3>
                    <p class="mb-0 text-muted">En cours</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-day text-secondary" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['total_tasks_today'] }}</h3>
                    <p class="mb-0 text-muted">Tâches aujourd'hui</p>
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

    <div class="row">
        <!-- Chambres en attente -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>À nettoyer ({{ $roomsPending->count() }})</h5>
                    @if($roomsPending->count() > 0)
                        <a href="{{ route('housekeeping.rooms.index', ['state' => 'pending']) }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                    @endif
                </div>
                <div class="card-body">
                    @if($roomsPending->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($roomsPending->take(10) as $room)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Chambre {{ $room->room_number }}</strong>
                                        <span class="text-muted ms-2">{{ $room->roomType->name ?? '-' }}</span>
                                        @if($room->floor)<span class="badge bg-light text-dark ms-1">{{ $room->floor }}</span>@endif
                                    </div>
                                    <form action="{{ route('housekeeping.rooms.start-cleaning', $room) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Démarrer</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Aucune chambre en attente de nettoyage.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Chambres en cours -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-brush me-2"></i>En cours ({{ $roomsInProgress->count() }})</h5>
                    @if($roomsInProgress->count() > 0)
                        <a href="{{ route('housekeeping.rooms.index', ['state' => 'in_progress']) }}" class="btn btn-sm btn-outline-info">Voir tout</a>
                    @endif
                </div>
                <div class="card-body">
                    @if($roomsInProgress->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($roomsInProgress->take(10) as $room)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Chambre {{ $room->room_number }}</strong>
                                        <span class="text-muted ms-2">{{ $room->roomType->name ?? '-' }}</span>
                                    </div>
                                    <form action="{{ route('housekeeping.rooms.complete-cleaning', $room) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Terminer</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Aucune chambre en cours de nettoyage.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <p class="text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Les chambres libérées par la réception (check-out) apparaissent ici en « À nettoyer ». Après avoir terminé le nettoyage, la chambre redevient disponible pour l'enregistrement.
    </p>
</x-app-layout>
