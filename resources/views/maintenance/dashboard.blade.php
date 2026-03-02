<x-app-layout>
    <x-slot name="header">Service technique</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-tools me-2"></i>Tableau de bord – Service technique</h4>
            <p class="text-muted small mb-0">{{ $hotel->name }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('maintenance.pannes.index') }}" class="btn btn-primary">
                <i class="bi bi-exclamation-octagon me-1"></i>Pannes
            </a>
            <a href="{{ route('maintenance.rooms.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-door-open me-1"></i>Chambres (état technique)
            </a>
            <a href="{{ route('maintenance.areas.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-building me-1"></i>Espaces (publics, techniques, etc.)
            </a>
            <a href="{{ route('maintenance.history.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-clock-history me-1"></i>Historique des interventions
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

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['issue'] }}</h3>
                    <p class="mb-0 text-muted">Pannes signalées</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-wrench text-info" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['maintenance'] }}</h3>
                    <p class="mb-0 text-muted">En maintenance</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-slash-circle text-danger" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['out_of_service'] }}</h3>
                    <p class="mb-0 text-muted">Hors service</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['total'] }}</h3>
                    <p class="mb-0 text-muted">Pannes résolues</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm border-primary border-2 mb-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="bi bi-exclamation-octagon me-2"></i>Pannes</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('maintenance.pannes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Signaler une panne</a>
                <a href="{{ route('maintenance.panne-types.index') }}" class="btn btn-outline-secondary">Types et catégories</a>
                <a href="{{ route('maintenance.pannes.index') }}" class="btn btn-outline-secondary">Voir tout</a>
            </div>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('maintenance.pannes.index', ['status' => 'signalée']) }}">
                        Signalées <span class="badge bg-warning text-dark">{{ $pannesCounts['signalée'] ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('maintenance.pannes.index', ['status' => 'en_cours']) }}">
                        En cours <span class="badge bg-info">{{ $pannesCounts['en_cours'] ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('maintenance.pannes.index', ['status' => 'résolue']) }}">
                        Résolues <span class="badge bg-success">{{ $pannesCounts['résolue'] ?? 0 }}</span>
                    </a>
                </li>
            </ul>

            @if(isset($pannesRecentes) && $pannesRecentes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Localisation</th>
                                <th>Type / Catégorie</th>
                                <th>Description</th>
                                <th>Signalé par</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pannesRecentes as $panne)
                                <tr>
                                    <td>{{ $panne->location_label }}</td>
                                    <td>
                                        <span class="fw-medium">{{ $panne->panneType->name ?? '-' }}</span>
                                        <br><span class="text-muted small">{{ $panne->panneCategory->name ?? '-' }}</span>
                                    </td>
                                    <td>{{ \Str::limit($panne->description, 50) }}</td>
                                    <td>{{ $panne->reporter->name ?? '-' }}</td>
                                    <td>{{ $panne->reported_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('maintenance.pannes.show', $panne) }}" class="btn btn-sm btn-outline-primary">Détail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    <a href="{{ route('maintenance.pannes.index') }}">Voir la liste complète des pannes</a>
                </p>
            @else
                <p class="text-muted text-center py-4 mb-0">
                    Aucune panne enregistrée.
                    <a href="{{ route('maintenance.pannes.create') }}">Signaler une panne</a>
                </p>
            @endif
        </div>
    </div>

    <p class="text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Les chambres en pannes signalées, en maintenance ou hors service ne sont pas proposées à l'enregistrement.
        Pour changer l'état d'une chambre : <strong>Chambres (état technique)</strong>. Pour signaler un problème (avec type et description) : <strong>Pannes → Signaler une panne</strong>. Pour voir les <a href="{{ route('maintenance.pannes.index', ['status' => 'résolue']) }}">pannes résolues</a>, utilisez l'onglet Résolues dans <strong>Pannes</strong>. Les espaces se gèrent dans <strong>Espaces</strong>.
    </p>
</x-app-layout>
