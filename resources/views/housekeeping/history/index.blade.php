<x-app-layout>
    <x-slot name="header">Mes activités</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historique de mes activités</h4>
            <p class="text-muted small mb-0">{{ $hotel->name }} — Filtrez par période pour voir vos nettoyages (début / fin).</p>
        </div>
        <a href="{{ route('housekeeping.dashboard') }}" class="btn btn-outline-secondary">Tableau de bord</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_fin" class="form-control" value="{{ $dateFin }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('housekeeping.history.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Activités ({{ $activities->count() }})</h5>
        </div>
        <div class="card-body">
            @if($activities->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Historique des activités">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><i class="bi bi-calendar-event me-1 text-muted"></i>Date / Heure</th>
                                <th scope="col"><i class="bi bi-tag me-1 text-muted"></i>Type d'action</th>
                                <th scope="col"><i class="bi bi-text-paragraph me-1 text-muted"></i>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @php
                                            $actionType = is_array($log->properties) ? ($log->properties['action_type'] ?? null) : null;
                                        @endphp
                                        <span class="badge bg-info">{{ $actionTypeLabels[$actionType] ?? $actionType }}</span>
                                    </td>
                                    <td>{{ $log->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-super.empty-table
                    icon="bi-clock-history"
                    title="Aucune activité"
                    message="Aucune activité sur la période sélectionnée. Modifiez les dates ou effectuez des nettoyages (début / fin) pour les voir apparaître ici."
                />
            @endif
        </div>
    </div>
</x-app-layout>
