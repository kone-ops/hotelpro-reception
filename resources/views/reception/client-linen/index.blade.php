<x-app-layout>
    <x-slot name="header">Linge client – Réception</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-basket me-2"></i>Linge client déposé à la réception</h4>
            <p class="text-muted small mb-0">{{ $hotel->name }}</p>
        </div>
        <a href="{{ route('reception.client-linen.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Enregistrer un dépôt
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Liste des dépôts</h5>
            <a href="{{ route('reception.dashboard') }}" class="btn btn-outline-secondary btn-sm">Retour au tableau de bord</a>
        </div>
        <div class="card-body p-0">
            @if($items->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-basket empty-state-icon"></i>
                    <p class="mt-2 mb-0">Aucun linge client enregistré pour l’instant.</p>
                    <a href="{{ route('reception.client-linen.create') }}" class="btn btn-primary mt-3">Enregistrer un premier dépôt</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date dépôt</th>
                                <th>Client / Chambre</th>
                                <th>Description</th>
                                <th>Statut</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->received_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>
                                        @if($item->client_name)
                                            <strong>{{ $item->client_name }}</strong><br>
                                        @endif
                                        @if($item->room)
                                            <span class="text-muted small">Chambre {{ $item->room->room_number }}</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($item->description, 50) }}</td>
                                    <td>
                                        <span class="badge
                                            @if($item->status === \App\Modules\Laundry\Models\ClientLinen::STATUS_PICKED_UP) bg-secondary
                                            @elseif($item->status === \App\Modules\Laundry\Models\ClientLinen::STATUS_READY_FOR_PICKUP) bg-success
                                            @elseif($item->status === \App\Modules\Laundry\Models\ClientLinen::STATUS_AT_LAUNDRY) bg-info
                                            @else bg-warning text-dark
                                            @endif">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if(in_array($item->status, [\App\Modules\Laundry\Models\ClientLinen::STATUS_READY_FOR_PICKUP, \App\Modules\Laundry\Models\ClientLinen::STATUS_AT_LAUNDRY], true))
                                            <form action="{{ route('reception.client-linen.mark-picked-up', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Le client a bien récupéré son linge ?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check2 me-1"></i>Client a récupéré
                                                </button>
                                            </form>
                                        @elseif($item->status === \App\Modules\Laundry\Models\ClientLinen::STATUS_PICKED_UP)
                                            <span class="text-muted small">{{ $item->picked_up_at?->format('d/m/Y H:i') }}</span>
                                        @else
                                            <span class="text-muted small">En attente buanderie</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <p class="text-muted small mt-3">
        <i class="bi bi-info-circle me-1"></i>Lorsque le linge est prêt, la buanderie le marque « Prêt pour retrait client ». Quand le client repasse à la réception pour le récupérer, cliquez sur « Client a récupéré ».
    </p>
</x-app-layout>
