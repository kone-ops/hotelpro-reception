<x-app-layout>
    <x-slot name="header">Détail de la panne</x-slot>

    <div class="mb-4">
        <a href="{{ route('maintenance.pannes.index') }}" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i>Retour aux pannes</a>
        <h4 class="mb-0">Panne #{{ $panne->id }}</h4>
        <p class="text-muted small mb-0">{{ $panne->reported_at->format('d/m/Y à H:i') }}</p>
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

    @php
        $statusLabels = ['signalée' => 'Signalée', 'en_cours' => 'En cours de maintenance', 'résolue' => 'Résolue'];
        $statusBadge = match($panne->status) {
            'signalée' => 'bg-warning text-dark',
            'en_cours' => 'bg-info',
            'résolue' => 'bg-success',
            default => 'bg-secondary',
        };
    @endphp

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="badge {{ $statusBadge }} fs-6">{{ $statusLabels[$panne->status] ?? $panne->status }}</span>
                    @if($panne->status === 'signalée')
                        <form action="{{ route('maintenance.pannes.start', $panne) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm">Démarrer l'intervention</button>
                        </form>
                    @endif
                    @if(in_array($panne->status, ['signalée', 'en_cours']))
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#resolveModal">Résoudre</button>
                    @endif
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Localisation</dt>
                        <dd class="col-sm-9">{{ $panne->location_label }}</dd>
                        <dt class="col-sm-3">Type de panne</dt>
                        <dd class="col-sm-9">{{ $panne->panneType->name ?? '-' }}</dd>
                        <dt class="col-sm-3">Catégorie</dt>
                        <dd class="col-sm-9">{{ $panne->panneCategory->name ?? '-' }}</dd>
                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $panne->description }}</dd>
                        <dt class="col-sm-3">Signalée par</dt>
                        <dd class="col-sm-9">
                            {{ $panne->reporter->name ?? '-' }}
                            @if($panne->reporter)
                                <span class="text-muted small">({{ $panne->reporter->email }})</span>
                            @endif
                        </dd>
                        <dt class="col-sm-3">Date et heure</dt>
                        <dd class="col-sm-9">{{ $panne->reported_at->format('d/m/Y H:i') }}</dd>
                        @if($panne->resolved_at)
                            <dt class="col-sm-3">Résolue par</dt>
                            <dd class="col-sm-9">{{ $panne->resolver->name ?? '-' }}</dd>
                            <dt class="col-sm-3">Date de résolution</dt>
                            <dd class="col-sm-9">{{ $panne->resolved_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">Historique des interventions</div>
                <div class="card-body">
                    @forelse($panne->interventions as $inter)
                        <div class="d-flex border-bottom pb-2 mb-2">
                            <div class="me-3">
                                <span class="badge bg-light text-dark">{{ $inter->action ?? 'note' }}</span>
                            </div>
                            <div class="flex-grow-1">
                                <span class="small text-muted">{{ $inter->created_at->format('d/m/Y H:i') }} – {{ $inter->user->name ?? '-' }}</span>
                                @if($inter->notes)<p class="mb-0 small">{{ $inter->notes }}</p>@endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Aucune intervention enregistrée.</p>
                    @endforelse
                    @if(in_array($panne->status, ['signalée', 'en_cours']))
                        <form action="{{ route('maintenance.pannes.note', $panne) }}" method="POST" class="mt-3">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="notes" class="form-control" placeholder="Ajouter une note..." required maxlength="1000">
                                <button type="submit" class="btn btn-outline-primary">Ajouter</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resolveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('maintenance.pannes.resolve', $panne) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Marquer comme résolue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Note (optionnel)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Commentaire de clôture..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Résoudre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
