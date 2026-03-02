<x-app-layout>
    <x-slot name="header">Chambres à nettoyer</x-slot>

    <div class="mb-4">
        <h4 class="mb-0"><i class="bi bi-door-open me-2"></i>Service des étages – Chambres</h4>
        <p class="text-muted small mb-0">{{ $hotel->name }}</p>
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

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-hourglass-split text-warning me-3" style="font-size: 2rem;"></i>
                    <div>
                        <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                        <span class="text-muted">En attente</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-brush text-info me-3" style="font-size: 2rem;"></i>
                    <div>
                        <h4 class="mb-0">{{ $stats['in_progress'] }}</h4>
                        <span class="text-muted">En cours</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">État</label>
                    <select name="state" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous (attente + en cours)</option>
                        <option value="pending" {{ request('state') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="in_progress" {{ request('state') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                    </select>
                </div>
                @if($floors->isNotEmpty())
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Étage</label>
                    <select name="floor" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous</option>
                        @foreach($floors as $f)
                            <option value="{{ $f }}" {{ request('floor') == $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-4">
                    <a href="{{ route('housekeeping.rooms.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Grille des chambres -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <i class="bi bi-grid-3x3-gap me-2"></i>Chambres ({{ $rooms->count() }})
        </div>
        <div class="card-body">
            @if($rooms->count() > 0)
                <div class="row g-3">
                    @foreach($rooms as $room)
                        <div class="col-md-4 col-lg-3">
                            <div class="card border h-100 {{ $room->cleaning_state === 'in_progress' ? 'border-info' : 'border-warning' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">Chambre {{ $room->room_number }}</h5>
                                        <span class="badge {{ $room->cleaning_state === 'in_progress' ? 'bg-info' : 'bg-warning text-dark' }}">
                                            {{ $room->cleaning_state === 'in_progress' ? 'En cours' : 'En attente' }}
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-2">{{ $room->roomType->name ?? '-' }} @if($room->floor)– {{ $room->floor }} @endif</p>
                                    <div class="d-flex gap-2">
                                        @if($room->cleaning_state === 'pending')
                                            <form action="{{ route('housekeeping.rooms.start-cleaning', $room) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">Démarrer</button>
                                            </form>
                                        @endif
                                        @if($room->cleaning_state === 'in_progress')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#completeModal{{ $room->id }}">Terminer</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Terminer nettoyage -->
                        <div class="modal fade" id="completeModal{{ $room->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Terminer le nettoyage – Chambre {{ $room->room_number }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('housekeeping.rooms.complete-cleaning', $room) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <label class="form-label">Notes (optionnel)</label>
                                            <textarea name="notes" class="form-control" rows="2" placeholder="Remarques éventuelles..."></textarea>
                                            <div class="mt-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="client_linen_flagged" value="1" id="clientLinen{{ $room->id }}" data-room-id="{{ $room->id }}">
                                                    <label class="form-check-label" for="clientLinen{{ $room->id }}">Linge client oublié dans la chambre</label>
                                                </div>
                                                <div class="client-linen-desc mt-2" id="clientLinenDesc{{ $room->id }}" style="display: none;">
                                                    <label class="form-label small">Description du linge</label>
                                                    <textarea name="client_linen_description" class="form-control form-control-sm" rows="2" placeholder="Ex: chemise, pantalon..."></textarea>
                                                    <small class="text-muted">La buanderie sera notifiée.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-success">Valider – Chambre disponible</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3 mb-0">Aucune chambre à nettoyer pour le moment.</p>
                    <a href="{{ route('housekeeping.dashboard') }}" class="btn btn-outline-primary mt-3">Retour au tableau de bord</a>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.querySelectorAll('[name="client_linen_flagged"]').forEach(function(cb) {
            var id = cb.getAttribute('data-room-id');
            var desc = document.getElementById('clientLinenDesc' + (id ? id : ''));
            if (!desc) return;
            cb.addEventListener('change', function() {
                desc.style.display = this.checked ? 'block' : 'none';
            });
        });
    </script>
</x-app-layout>
