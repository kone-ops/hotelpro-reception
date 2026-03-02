<x-app-layout>
    <x-slot name="header">Linge client</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-basket me-2"></i>Linge client</h4>
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

    <!-- Onglets Réception / Chambre -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $source === 'reception' ? 'active' : '' }}" href="{{ route('laundry.client-linen.index', ['source' => 'reception'] + request()->only(['status', 'date_from', 'date_to'])) }}">
                <i class="bi bi-receipt me-1"></i>Réception
                @if($statsReception['pending_pickup'] > 0)
                    <span class="badge bg-warning ms-1">{{ $statsReception['pending_pickup'] }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $source === 'room' ? 'active' : '' }}" href="{{ route('laundry.client-linen.index', ['source' => 'room'] + request()->only(['status', 'date_from', 'date_to'])) }}">
                <i class="bi bi-door-open me-1"></i>Chambre
                @if($statsRoom['pending_pickup'] > 0)
                    <span class="badge bg-warning ms-1">{{ $statsRoom['pending_pickup'] }}</span>
                @endif
            </a>
        </li>
    </ul>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="source" value="{{ $source }}">
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach(\App\Modules\Laundry\Models\ClientLinen::statusLabels() as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
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
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary me-2">Filtrer</button>
                    <a href="{{ route('laundry.client-linen.index', ['source' => $source]) }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <strong>{{ $source === 'reception' ? 'Linge client – Réception' : 'Linge client – Chambre' }}</strong> ({{ $items->total() }})
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Linge client">
                    <thead class="table-light">
                        <tr>
                            <th scope="col"><i class="bi bi-calendar-event me-1 text-muted"></i>Date / Reçu par</th>
                            @if($source === 'room')
                                <th scope="col"><i class="bi bi-door-open me-1 text-muted"></i>Chambre</th>
                            @endif
                            <th scope="col"><i class="bi bi-person-lines-fill me-1 text-muted"></i>Client / Description</th>
                            <th scope="col"><i class="bi bi-tag me-1 text-muted"></i>Statut</th>
                            <th scope="col" class="text-end" style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <span>{{ $item->received_at->format('d/m/Y H:i') }}</span>
                                    <br><small class="text-muted">{{ $item->receivedByUser->name ?? '-' }}</small>
                                </td>
                                @if($source === 'room')
                                    <td>
                                        @if($item->room)
                                            <strong>{{ $item->room->room_number }}</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    @if($item->client_name)
                                        <strong>{{ $item->client_name }}</strong><br>
                                    @endif
                                    <span class="text-muted">{{ Str::limit($item->description, 60) }}</span>
                                </td>
                                <td>
                                    <span class="badge
                                        @if($item->status === 'pending_pickup') bg-warning text-dark
                                        @elseif($item->status === 'at_laundry') bg-info
                                        @elseif($item->status === 'ready_for_pickup') bg-primary
                                        @elseif($item->status === 'picked_up') bg-success
                                        @else bg-secondary
                                        @endif
                                    ">{{ $item->status_label }}</span>
                                </td>
                                <td class="text-end">
                                    @if($item->status !== 'picked_up')
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Changer statut</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @foreach(\App\Modules\Laundry\Models\ClientLinen::statusLabels() as $value => $label)
                                                    @if($value !== $item->status)
                                                        <li>
                                                            <form action="{{ route('laundry.client-linen.update-status', $item) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="{{ $value }}">
                                                                <button type="submit" class="dropdown-item">{{ $label }}</button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <small class="text-muted">Récupéré {{ $item->picked_up_at?->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $source === 'room' ? 5 : 4 }}">
                                    <x-super.empty-table
                                        icon="bi-basket"
                                        title="Aucun linge client"
                                        :message="'Aucun linge client ' . ($source === 'reception' ? 'déposé à la réception' : 'signalé en chambre') . ' pour le moment.'"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
            <div class="card-footer bg-transparent">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
