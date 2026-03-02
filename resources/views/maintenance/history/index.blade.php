<x-app-layout>
    <x-slot name="header">Historique des interventions</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historique des interventions techniques</h4>
            <p class="text-muted small mb-0">{{ $hotel->name }} — Changements d'état technique des chambres.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('maintenance.pannes.index', ['status' => 'résolue']) }}" class="btn btn-outline-success">Pannes résolues</a>
            <a href="{{ route('maintenance.dashboard') }}" class="btn btn-outline-secondary">Tableau de bord</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Interventions</h5>
            <form method="get" class="d-inline">
                <input type="hidden" name="service" value="maintenance">
                <button type="submit" class="btn btn-sm btn-outline-primary">Service technique uniquement</button>
            </form>
        </div>
        <div class="card-body">
            @if($history->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Historique des interventions">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><i class="bi bi-calendar-event me-1 text-muted"></i>Date / Heure</th>
                                <th scope="col"><i class="bi bi-door-open me-1 text-muted"></i>Chambre</th>
                                <th scope="col"><i class="bi bi-tag me-1 text-muted"></i>Type</th>
                                <th scope="col" class="d-none d-lg-table-cell"><i class="bi bi-arrow-left me-1 text-muted"></i>Ancien état</th>
                                <th scope="col"><i class="bi bi-arrow-right me-1 text-muted"></i>Nouvel état</th>
                                <th scope="col"><i class="bi bi-person me-1 text-muted"></i>Par</th>
                                <th scope="col" class="d-none d-xl-table-cell"><i class="bi bi-chat-text me-1 text-muted"></i>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $h)
                                <tr>
                                    <td>{{ $h->changed_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <strong>{{ $h->room->room_number ?? '-' }}</strong>
                                        @if($h->room && $h->room->roomType)
                                            <span class="text-muted small">({{ $h->room->roomType->name }})</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $h->service ?? '-' }}</span></td>
                                    <td class="d-none d-lg-table-cell"><span class="badge bg-light text-dark">{{ $stateLabels[$h->previous_value] ?? $h->previous_value }}</span></td>
                                    <td><span class="badge bg-primary">{{ $stateLabels[$h->new_value] ?? $h->new_value }}</span></td>
                                    <td>{{ $h->user->name ?? 'Système' }}</td>
                                    <td class="small text-muted d-none d-xl-table-cell">{{ \Illuminate\Support\Str::limit($h->notes ?? '', 40) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $history->links() }}
                </div>
            @else
                <x-super.empty-table
                    icon="bi-clock-history"
                    title="Aucune intervention"
                    message="Aucune intervention technique enregistrée. Les changements d'état (problème signalé, maintenance, remise en service, panne résolue) apparaîtront ici."
                />
            @endif
        </div>
    </div>
</x-app-layout>
