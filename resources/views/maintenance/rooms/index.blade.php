<x-app-layout>
    <x-slot name="header">Chambres – État technique</x-slot>

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="bi bi-tools me-2"></i>Service technique – Chambres</h4>
            <p class="text-muted small mb-0">{{ $hotel->name }}</p>
            <p class="small text-muted mb-0 mt-1">Pour signaler un problème (type, catégorie, description) sur une chambre ou un espace : <a href="{{ route('maintenance.pannes.create') }}">Signaler une panne</a>. Pour voir les <a href="{{ route('maintenance.pannes.index', ['status' => 'résolue']) }}">pannes résolues</a>.</p>
        </div>
        <a href="{{ route('maintenance.dashboard') }}" class="btn btn-outline-secondary btn-sm">Tableau de bord</a>
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
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle text-warning me-3" style="font-size: 2rem;"></i>
                    <div>
                        <h4 class="mb-0">{{ $stats['issue'] }}</h4>
                        <span class="text-muted">Pannes signalées</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-wrench text-info me-3" style="font-size: 2rem;"></i>
                    <div>
                        <h4 class="mb-0">{{ $stats['maintenance'] }}</h4>
                        <span class="text-muted">En maintenance</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-slash-circle text-danger me-3" style="font-size: 2rem;"></i>
                    <div>
                        <h4 class="mb-0">{{ $stats['out_of_service'] }}</h4>
                        <span class="text-muted">Hors service</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-check-circle text-success me-3" style="font-size: 2rem;"></i>
                    <div>
                        <h4 class="mb-0">{{ $stats['pannes_resolues'] ?? 0 }}</h4>
                        <span class="text-muted">Pannes résolues</span>
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
                    <label class="form-label small fw-bold">État technique</label>
                    <select name="state" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous (issue + maintenance + hors service)</option>
                        <option value="issue" {{ request('state') === 'issue' ? 'selected' : '' }}>Pannes signalées</option>
                        <option value="maintenance" {{ request('state') === 'maintenance' ? 'selected' : '' }}>En maintenance</option>
                        <option value="out_of_service" {{ request('state') === 'out_of_service' ? 'selected' : '' }}>Hors service</option>
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
                    <a href="{{ route('maintenance.rooms.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des chambres -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <i class="bi bi-table me-2"></i>Chambres ({{ $rooms->count() }})
        </div>
        <div class="card-body p-0">
            @if($rooms->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Chambre</th>
                                <th>Type</th>
                                <th>Étage</th>
                                <th>Statut</th>
                                <th>Raison / Période / Mis HS par</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rooms as $room)
                                @php
                                    $stateBadge = match($room->technical_state) {
                                        'issue' => ['bg-warning text-dark', 'Pannes signalées'],
                                        'maintenance' => ['bg-info', 'En maintenance'],
                                        'out_of_service' => ['bg-danger', 'Hors service'],
                                        default => ['bg-secondary', $room->technical_state ?? 'Normal'],
                                    };
                                @endphp
                                <tr>
                                    <td><strong>Chambre {{ $room->room_number }}</strong></td>
                                    <td>{{ $room->roomType->name ?? '-' }}</td>
                                    <td>{{ $room->floor ?? '-' }}</td>
                                    <td><span class="badge {{ $stateBadge[0] }}">{{ $stateBadge[1] }}</span></td>
                                    <td class="small text-muted">
                                        @if($room->technical_state === 'out_of_service')
                                            @if($room->out_of_service_reason){{ Str::limit($room->out_of_service_reason, 50) }}<br>@endif
                                            @if($room->out_of_service_from || $room->out_of_service_until)
                                                {{ $room->out_of_service_from?->format('d/m/Y') ?? '–' }} → {{ $room->out_of_service_until?->format('d/m/Y') ?? '–' }}<br>
                                            @endif
                                            @if($room->outOfServiceByUser)Par {{ $room->outOfServiceByUser->name }}@endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end flex-wrap">
                                            <form action="{{ route('maintenance.rooms.update-technical-state', $room) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="technical_state" value="normal">
                                                <button type="submit" class="btn btn-sm btn-success" title="Remettre en service">Remettre en service</button>
                                            </form>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <form action="{{ route('maintenance.rooms.update-technical-state', $room) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="technical_state" value="maintenance">
                                                    <button type="submit" class="btn btn-outline-info" title="En maintenance"><i class="bi bi-wrench"></i></button>
                                                </form>
                                                <form action="{{ route('maintenance.rooms.update-technical-state', $room) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="technical_state" value="out_of_service">
                                                    <button type="submit" class="btn btn-outline-danger" title="Hors service"><i class="bi bi-slash-circle"></i></button>
                                                </form>
                                            </div>
                                            <a href="{{ route('maintenance.pannes.create') }}" class="btn btn-sm btn-outline-warning" title="Problème → Signaler une panne"><i class="bi bi-exclamation-octagon"></i></a>
                                            <a href="{{ route('maintenance.pannes.index', ['status' => 'résolue']) }}" class="btn btn-sm btn-outline-success" title="Pannes résolues"><i class="bi bi-check-circle"></i></a>
                                            <button type="button" class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="modal" data-bs-target="#notesModal{{ $room->id }}" title="Changer l'état avec note">Ajouter une note</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @foreach($rooms as $room)
                        <!-- Modal note optionnelle -->
                        <div class="modal fade" id="notesModal{{ $room->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Changer l'état – Chambre {{ $room->room_number }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('maintenance.rooms.update-technical-state', $room) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <label class="form-label">Nouvel état</label>
                                            <select name="technical_state" class="form-select mb-3" required id="techState{{ $room->id }}">
                                                <option value="normal">Normal (remise en service)</option>
                                                <option value="maintenance">En maintenance</option>
                                                <option value="out_of_service">Hors service</option>
                                            </select>
                                            <p class="small text-muted mb-0">Pour marquer « Pannes signalées », utilisez <a href="{{ route('maintenance.pannes.create') }}" target="_blank">Signaler une panne</a>.</p>
                                            <div id="outOfServiceFields{{ $room->id }}" class="mb-3 d-none">
                                                <label class="form-label">Raison (optionnel)</label>
                                                <input type="text" name="out_of_service_reason" class="form-control mb-2" placeholder="Ex: travaux, panne climatisation">
                                                <label class="form-label">Période (optionnel)</label>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <input type="date" name="out_of_service_from" class="form-control" placeholder="Début">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="date" name="out_of_service_until" class="form-control" placeholder="Fin">
                                                    </div>
                                                </div>
                                            </div>
                                            <label class="form-label">Note (optionnel)</label>
                                            <textarea name="notes" class="form-control" rows="3" placeholder="Ex: climatisation en panne, réparation prévue demain..."></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
            @else
                <div class="text-center py-5">
                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3 mb-0">Aucune chambre en problème, en maintenance ou hors service.</p>
                    <a href="{{ route('maintenance.dashboard') }}" class="btn btn-outline-primary mt-3">Retour au tableau de bord</a>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.querySelectorAll('select[name=technical_state]').forEach(function(sel) {
            sel.addEventListener('change', function() {
                var id = sel.id.replace('techState', '');
                var block = document.getElementById('outOfServiceFields' + id);
                if (block) block.classList.toggle('d-none', this.value !== 'out_of_service');
            });
        });
        document.querySelectorAll('select[name=technical_state]').forEach(function(sel) {
            var id = sel.id.replace('techState', '');
            var block = document.getElementById('outOfServiceFields' + id);
            if (block) block.classList.toggle('d-none', sel.value !== 'out_of_service');
        });
    </script>
</x-app-layout>
